<?php

namespace App\Http\Controllers\Network;

use Illuminate\Http\Request;

/** Ringkasan jaringan: pendapatan bulanan & statistik pelanggan. Izin: reports.read. */
class ReportController extends NetworkController
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'year' => 'nullable|integer|min:2020|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);
        $year = (int) ($data['year'] ?? now()->year);
        $month = (int) ($data['month'] ?? now()->month);

        $client = $this->client();
        $revenue = $client->get('/v1/reports/revenue', ['year' => $year, 'month' => $month]);
        $customers = $client->get('/v1/reports/customers');

        return view('network.reports.index', compact('revenue', 'customers', 'year', 'month'));
    }
}
