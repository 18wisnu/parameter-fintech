<?php

namespace App\Http\Controllers\Network;

use App\Models\Customer;
use Illuminate\Http\Request;

/** Tiket gangguan/keluhan pelanggan. Izin: tickets.read, tickets.write. */
class TicketController extends NetworkController
{
    public const STATUSES = ['open' => 'Baru', 'pending' => 'Menunggu', 'in_progress' => 'Diproses', 'resolved' => 'Selesai', 'closed' => 'Ditutup'];
    public const PRIORITIES = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'];
    public const CATEGORIES = ['technical' => 'Teknis', 'network' => 'Jaringan', 'billing' => 'Tagihan', 'account' => 'Akun', 'general' => 'Umum'];

    public function index(Request $request)
    {
        $page = $this->remotePage('/v1/tickets', $request, ['status', 'priority', 'category']);

        return view('network.tickets.index', compact('page') + $this->labels());
    }

    public function show(int $id)
    {
        $ticket = $this->client()->get("/v1/tickets/{$id}");

        return view('network.tickets.show', compact('ticket') + $this->labels());
    }

    public function create()
    {
        // Hanya pelanggan yang sudah tersinkron dengan server jaringan
        $customers = Customer::whereNotNull('mixradius_id')->orderBy('name')->get(['id', 'name', 'code', 'mixradius_id']);

        return view('network.tickets.create', compact('customers') + $this->labels());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'subject' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|in:' . implode(',', array_keys(self::CATEGORIES)),
            'priority' => 'nullable|in:' . implode(',', array_keys(self::PRIORITIES)),
        ]);

        // customer_id lokal → id pelanggan di server (dibatasi tenant aktif oleh scope)
        $customer = Customer::whereNotNull('mixradius_id')->findOrFail($data['customer_id']);
        $data['customer_id'] = (int) $customer->mixradius_id;

        $result = $this->client()->post('/v1/tickets', array_filter($data));

        return redirect()->route('network.tickets.show', $result['ticket_id'])
            ->with('success', 'Tiket ' . ($result['ticket_code'] ?? '') . ' dibuat.');
    }

    public function reply(Request $request, int $id)
    {
        $data = $request->validate(['message' => 'required|string|max:5000']);

        $this->client()->post("/v1/tickets/{$id}/reply", $data);

        return back()->with('success', 'Balasan terkirim.');
    }

    private function labels(): array
    {
        return ['statuses' => self::STATUSES, 'priorities' => self::PRIORITIES, 'categories' => self::CATEGORIES];
    }
}
