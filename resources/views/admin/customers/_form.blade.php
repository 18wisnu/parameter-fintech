@php
    use App\Models\Customer;
    $input = 'w-full px-3 py-2.5 rounded-xl border-slate-200 text-sm focus:border-sky-500 focus:ring-sky-500';
    $label = 'block text-sm font-medium text-slate-700 mb-1';
    $creatingOnNetwork = $network && !$customer->exists;
@endphp

@include('partials.flash')
@if($errors->any())
<div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 text-sm">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<div class="space-y-4">
    {{-- Data pelanggan --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-4">Data Pelanggan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="{{ $label }}">Nama lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">ID Pelanggan</label>
                <input type="text" name="customer_id" value="{{ old('customer_id', $customer->customer_id) }}" class="{{ $input }} font-mono" placeholder="{{ $creatingOnNetwork ? 'Kosongkan = dibuat otomatis' : 'Contoh: 100234' }}">
            </div>
            <div>
                <label class="{{ $label }}">No. HP / WhatsApp</label>
                <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" class="{{ $input }}" placeholder="628123456789">
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $label }}">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="{{ $input }}">
            </div>
            <div class="sm:col-span-2">
                <label class="{{ $label }}">Alamat pemasangan</label>
                <textarea name="address" rows="2" class="{{ $input }}">{{ old('address', $customer->address) }}</textarea>
            </div>
            <div x-data="{ busy: false, locate() { if (!navigator.geolocation) return; this.busy = true; navigator.geolocation.getCurrentPosition(p => { $refs.lat.value = p.coords.latitude.toFixed(7); $refs.lng.value = p.coords.longitude.toFixed(7); this.busy = false; }, () => this.busy = false); } }" class="sm:col-span-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-slate-700">Titik lokasi</span>
                    <button type="button" @click="locate()" class="text-xs font-semibold text-sky-600 hover:underline" x-text="busy ? 'Mencari lokasi…' : 'Pakai lokasi saya'"></button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <input x-ref="lat" type="text" inputmode="decimal" name="latitude" value="{{ old('latitude', $customer->latitude) }}" placeholder="Latitude" class="{{ $input }}">
                    <input x-ref="lng" type="text" inputmode="decimal" name="longitude" value="{{ old('longitude', $customer->longitude) }}" placeholder="Longitude" class="{{ $input }}">
                </div>
            </div>
        </div>
    </section>

    {{-- Layanan --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-4">Layanan & Tagihan</h3>

        @if($creatingOnNetwork)
            @if($catalogError)
                <p class="text-sm text-rose-700 bg-rose-50 rounded-lg px-3 py-2">Daftar paket belum bisa dimuat: {{ $catalogError }}</p>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Paket internet <span class="text-rose-500">*</span></label>
                    @php $groups = collect($products)->groupBy(fn ($p) => ['ppp' => 'PPPoE', 'hotspot' => 'Hotspot', 'dhcp' => 'DHCP', 'static' => 'Static'][$p['type'] ?? ''] ?? 'Lainnya'); @endphp
                    <select name="product_id" required class="{{ $input }}">
                        <option value="">Pilih paket…</option>
                        @foreach($groups as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach($items as $p)
                                    <option value="{{ $p['id'] }}" @selected(old('product_id') == $p['id'])>{{ $p['name'] }}{{ !empty($p['bandwidth']) ? ' · ' . $p['bandwidth'] : '' }} — Rp {{ number_format($p['price'] ?? 0, 0, ',', '.') }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Pembayaran</label>
                    <select name="billing_type" class="{{ $input }}">
                        @foreach(Customer::BILLING_TYPES as $k => $v)<option value="{{ $k }}" @selected(old('billing_type', $customer->billing_type) === $k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Siklus tagihan</label>
                    <select name="billing_cycle" class="{{ $input }}">
                        @foreach(Customer::BILLING_CYCLES as $k => $v)<option value="{{ $k }}" @selected(old('billing_cycle', $customer->billing_cycle) === $k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-3">Akun internet (username) dibuat otomatis. Layanan aktif setelah tagihan pertama dibayar.</p>
            @endif

        @elseif($linked)
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div><dt class="text-xs text-slate-400">Jenis</dt><dd class="text-slate-700">{{ Customer::TYPES[$customer->type] ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Paket</dt><dd class="text-slate-700">{{ $customer->product_name ?: '—' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Harga</dt><dd class="text-slate-700">Rp {{ number_format($customer->package_fee, 0, ',', '.') }}</dd></div>
                <div><dt class="text-xs text-slate-400">Jatuh tempo</dt><dd class="text-slate-700">{{ $customer->due_date ?: '—' }}</dd></div>
            </dl>
            <p class="text-xs text-slate-400 mt-3">Paket, masa aktif dan status diatur oleh server jaringan dan diperbarui otomatis saat sinkron.</p>

        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Jenis layanan</label>
                    <select name="type" class="{{ $input }}">
                        @foreach(Customer::TYPES as $k => $v)<option value="{{ $k }}" @selected(old('type', $customer->type) === $k)>{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}">Nama paket</label>
                    <input type="text" name="product_name" value="{{ old('product_name', $customer->product_name) }}" class="{{ $input }}" placeholder="Contoh: Home 20 Mbps">
                </div>
                <div>
                    <label class="{{ $label }}">Harga per bulan <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-slate-400">Rp</span>
                        <input type="number" min="0" name="package_fee" value="{{ old('package_fee', (int) $customer->package_fee) }}" required class="{{ $input }} pl-10">
                    </div>
                </div>
                <div>
                    <label class="{{ $label }}">Jatuh tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $customer->due_date) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Status</label>
                    <select name="status" class="{{ $input }}">
                        @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif (berhenti langganan)'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('status', $customer->status === 'isolated' ? 'active' : ($customer->status ?? 'active')) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700 self-end pb-2.5">
                    <input type="checkbox" name="is_isolated" value="1" @checked(old('is_isolated', $customer->is_isolated)) class="rounded border-slate-300 text-amber-600">
                    Isolir (menunggak)
                </label>
            </div>
        @endif
    </section>

    {{-- Penagihan (lokal, tidak dikirim ke server jaringan) --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Penagihan</h3>
        <p class="text-xs text-slate-400 mb-4">Berlaku di tagihan aplikasi ini.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Kolektor penanggung jawab</label>
                <select name="collector_id" class="{{ $input }}">
                    <option value="">— Belum ditentukan —</option>
                    @foreach($collectors as $s)
                        <option value="{{ $s->id }}" @selected(old('collector_id', $customer->collector_id) == $s->id)>{{ $s->name }} ({{ $s->role }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Diskon per bulan</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-slate-400">Rp</span>
                    <input type="number" min="0" name="discount" value="{{ old('discount', (int) $customer->discount) }}" class="{{ $input }} pl-10">
                </div>
            </div>
            <div>
                <label class="{{ $label }}">Biaya pemasangan</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-slate-400">Rp</span>
                    <input type="number" min="0" name="installation_fee" value="{{ old('installation_fee', (int) $customer->installation_fee) }}" class="{{ $input }} pl-10" @disabled($customer->installation_billed)>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $customer->installation_billed ? 'Sudah ditagihkan.' : 'Ditagih sekali, di tagihan pertama.' }}</p>
            </div>
            @unless($linked || $creatingOnNetwork)
            <div>
                <label class="{{ $label }}">Mulai berlangganan</label>
                <input type="date" name="start_date" value="{{ old('start_date', $customer->start_date?->toDateString()) }}" class="{{ $input }}">
                <p class="text-xs text-slate-400 mt-1">Mulai di tengah bulan → tagihan bulan itu dihitung prorata.</p>
            </div>
            @endunless
            @unless($customer->exists)
            <label class="sm:col-span-2 flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="bill_now" value="1" @checked(old('bill_now')) class="rounded border-slate-300 text-sky-600">
                Buat tagihan pertama sekarang (bulan {{ now()->locale('id')->translatedFormat('F Y') }})
            </label>
            @endunless
        </div>
    </section>

    {{-- Lainnya --}}
    <section class="bg-white rounded-2xl border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-4">Lainnya</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $label }}">Petugas aktivasi (bonus)</label>
                <select name="activated_by_id" class="{{ $input }}">
                    <option value="">— Tidak ada —</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" @selected(old('activated_by_id', $customer->activated_by_id) == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes', $customer->notes) }}" class="{{ $input }}">
            </div>
        </div>
    </section>
</div>
