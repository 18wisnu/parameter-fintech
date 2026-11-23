<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\NewCustomerNotification;
use App\Models\Business;
use App\Models\User;
use App\Models\Customer;
use App\Services\Mixradius\MixradiusClient;
use App\Services\Mixradius\MixradiusException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Data pelanggan. Jika tenant terhubung ke server jaringan, profil pelanggan
 * mengikuti struktur di server: pelanggan baru & perubahan profil dikirim ke server,
 * sedangkan paket, masa aktif dan status diatur oleh server (ditarik saat sinkron).
 */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->orderBy('name');

        if ($search = trim((string) $request->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mixradius_username', 'like', "%{$search}%");
            });
        }

        match ($request->status) {
            'active' => $query->where('status', '!=', 'inactive')->where('is_isolated', false)->where('status', '!=', 'isolated'),
            'isolated' => $query->where('status', '!=', 'inactive')->where(fn ($q) => $q->where('is_isolated', true)->orWhere('status', 'isolated')),
            'inactive' => $query->where('status', 'inactive'),
            default => null,
        };

        if ($request->filled('type') && array_key_exists($request->type, Customer::TYPES)) {
            $query->where('type', $request->type);
        }

        $customers = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => Customer::count(),
            'active' => Customer::where('status', '!=', 'inactive')->where('is_isolated', false)->where('status', '!=', 'isolated')->count(),
            'isolated' => Customer::where('status', '!=', 'inactive')->where(fn ($q) => $q->where('is_isolated', true)->orWhere('status', 'isolated'))->count(),
            'inactive' => Customer::where('status', 'inactive')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'counts') + ['network' => $this->business()->hasMixradius()]);
    }

    public function show(Customer $customer)
    {
        $customer->load('activatedBy');
        $invoices = $customer->invoices()->latest('period_date')->limit(12)->get();

        // Detail layanan langsung dari server (area, ODP, IP, online). Gagal → tampil data lokal saja.
        $remote = null;
        $remoteError = null;
        $business = $this->business();
        if ($customer->isLinked() && $business->hasMixradius()) {
            try {
                $remote = MixradiusClient::forBusiness($business)->get('/v1/customers/' . rawurlencode($customer->mixradius_id));
            } catch (MixradiusException $e) {
                $remoteError = $e->getMessage();
            }
        }

        return view('admin.customers.show', compact('customer', 'invoices', 'remote', 'remoteError'));
    }

    public function create()
    {
        return view('admin.customers.create', $this->formData(new Customer(['type' => 'pppoe', 'billing_type' => 'prepaid', 'billing_cycle' => 'monthly'])));
    }

    public function store(Request $request)
    {
        $business = $this->business();
        $network = $business->hasMixradius();

        $data = $request->validate($this->rules(null, $network));
        $data = $this->moneyDefaults($data);
        $data['is_isolated'] = $request->boolean('is_isolated');
        $data['status'] = ($data['status'] ?? null) ?: ($data['is_isolated'] ? 'isolated' : 'active');

        if ($network) {
            $product = collect($this->catalog($business))->firstWhere('id', (int) $data['product_id']);
            if (!$product) {
                return back()->withInput()->withErrors(['product_id' => 'Paket tidak ditemukan di server jaringan.']);
            }

            try {
                $created = MixradiusClient::forBusiness($business)->post('/v1/customers', array_filter([
                    'fullname' => $data['name'],
                    'product_id' => (int) $data['product_id'],
                    'billing_type' => $data['billing_type'] ?? null,
                    'billing_cycle' => $data['billing_cycle'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ], fn ($v) => $v !== null && $v !== ''));
            } catch (MixradiusException $e) {
                return back()->withInput()->with('error', 'Pelanggan gagal dibuat di server jaringan: ' . $e->getMessage());
            }

            $data += [
                'mixradius_id' => isset($created['customer_id']) ? (string) $created['customer_id'] : null,
                'code' => $created['customer_code'] ?? null,
                'mixradius_service_id' => isset($created['service_id']) ? (string) $created['service_id'] : null,
                'mixradius_username' => $created['service_username'] ?? null,
                'mixradius_synced_at' => now(),
            ];
            $data['customer_id'] = ($data['customer_id'] ?? null) ?: ($created['customer_code'] ?? null);
            $data['type'] = $this->localType($product['type'] ?? null);
            $data['package_fee'] = (float) ($product['price'] ?? 0);
            $data['product_name'] = $product['name'] ?? null;
            $data['bandwidth'] = $product['bandwidth'] ?? null;
            $data['status'] = 'active';
        }

        unset($data['product_id']);
        $customer = Customer::create($data);

        foreach (User::inCurrentBusiness(['admin', 'owner'])->get() as $admin) {
            $admin->notify(new NewCustomerNotification($customer));
        }

        $msg = 'Pelanggan berhasil ditambahkan.';
        if ($request->boolean('bill_now')) {
            $invoice = (new \App\Services\Billing\InvoiceGenerator($business))->createFor($customer, now());
            $msg .= $invoice
                ? ' Tagihan pertama ' . $invoice->invoice_number . ' dibuat: Rp ' . number_format($invoice->amount, 0, ',', '.') . '.'
                : ' Tagihan pertama belum dibuat (belum mulai berlangganan bulan ini atau totalnya nol).';
        }
        if ($network) {
            $msg .= ' Layanan internet aktif setelah tagihan pertama dibayar.';
        }

        return redirect()->route('customers.show', $customer)->with('success', $msg);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', $this->formData($customer));
    }

    public function update(Request $request, Customer $customer)
    {
        $business = $this->business();
        $linked = $customer->isLinked() && $business->hasMixradius();

        $data = $request->validate($this->rules($customer, false, $linked));
        $data = $this->moneyDefaults($data);
        if ($customer->installation_billed) {
            unset($data['installation_fee']); // sudah ditagihkan, tidak bisa diubah
        }

        if ($linked) {
            // Profil dikirim ke server dulu; kalau ditolak, data lokal tidak diubah
            // (sinkron berikutnya akan menimpa data lokal dengan data server).
            try {
                MixradiusClient::forBusiness($business)->put('/v1/customers/' . rawurlencode($customer->mixradius_id), array_filter([
                    'fullname' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ], fn ($v) => $v !== null && $v !== ''));
            } catch (MixradiusException $e) {
                return back()->withInput()->with('error', 'Perubahan gagal dikirim ke server jaringan: ' . $e->getMessage());
            }
        } else {
            $data['is_isolated'] = $request->boolean('is_isolated');
            if ($data['is_isolated'] && ($data['status'] ?? $customer->status) === 'active') {
                $data['status'] = 'isolated';
            } elseif (!$data['is_isolated'] && ($data['status'] ?? $customer->status) === 'isolated') {
                $data['status'] = 'active';
            }
        }

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan dihapus dari aplikasi.');
    }

    // Export & Import
    public function export() { return Excel::download(new CustomersExport, 'data-pelanggan.xlsx'); }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new CustomersImport, $request->file('file'));

        return redirect()->back()->with('success', 'Import berhasil.');
    }

    // ------------------------------------------------------------------

    private function business(): Business
    {
        return Business::findOrFail(auth()->user()->current_business_id);
    }

    private function rules(?Customer $customer, bool $creatingOnNetwork = false, bool $linked = false): array
    {
        $businessId = auth()->user()->current_business_id;

        $rules = [
            'name' => 'required|string|max:150',
            'customer_id' => ['nullable', 'string', 'max:50',
                Rule::unique('customers', 'customer_id')->where('business_id', $businessId)->ignore($customer?->id)],
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:500',
            'activated_by_id' => ['nullable', 'integer', Rule::exists('business_user', 'user_id')->where('business_id', $businessId)],
            // Penagihan lokal (tidak dikirim ke server jaringan)
            'collector_id' => ['nullable', 'integer', Rule::exists('business_user', 'user_id')->where('business_id', $businessId)],
            'discount' => 'nullable|numeric|min:0',
            'installation_fee' => 'nullable|numeric|min:0',
        ];

        if ($creatingOnNetwork) {
            return $rules + [
                'product_id' => 'required|integer',
                'billing_type' => 'required|in:' . implode(',', array_keys(Customer::BILLING_TYPES)),
                'billing_cycle' => 'required|in:' . implode(',', array_keys(Customer::BILLING_CYCLES)),
            ];
        }

        if ($linked) {
            // Paket, masa aktif & status mengikuti server.
            return $rules;
        }

        return $rules + [
            'type' => 'required|in:' . implode(',', array_keys(Customer::TYPES)),
            'package_fee' => 'required|numeric|min:0',
            'product_name' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'status' => 'nullable|in:' . implode(',', array_keys(Customer::STATUSES)),
        ];
    }

    /** Kolom nominal yang dikosongkan di form disimpan sebagai 0. */
    private function moneyDefaults(array $data): array
    {
        foreach (['discount', 'installation_fee'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = (float) ($data[$key] ?? 0);
            }
        }

        return $data;
    }

    private function formData(Customer $customer): array
    {
        $business = $this->business();
        $network = $business->hasMixradius();
        $products = [];
        $catalogError = null;

        if ($network && !$customer->exists) {
            try {
                $products = $this->catalog($business);
            } catch (MixradiusException $e) {
                $catalogError = $e->getMessage();
            }
        }

        return [
            'customer' => $customer,
            'staff' => User::inCurrentBusiness(['teknisi', 'kolektor'])->get(),
            'collectors' => User::membersOfCurrentBusiness()->orderByRaw("CASE WHEN bu.role = 'kolektor' THEN 0 ELSE 1 END")->orderBy('name')->get(),
            'network' => $network,
            'linked' => $customer->isLinked() && $network,
            'products' => $products,
            'catalogError' => $catalogError,
        ];
    }

    /** Semua paket di server (PPPoE, hotspot, DHCP, static). */
    private function catalog(Business $business): array
    {
        return MixradiusClient::cachedCatalog($business);
    }

    private function localType(?string $remoteType): string
    {
        return match ($remoteType) {
            'ppp' => 'pppoe',
            'hotspot' => 'hotspot',
            default => 'general',
        };
    }
}
