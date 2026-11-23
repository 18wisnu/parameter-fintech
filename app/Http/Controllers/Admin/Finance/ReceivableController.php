<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Services\Finance\Receivables;
use Illuminate\Http\Request;

/** Umur piutang & daftar tagih per kolektor / area. */
class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.finance.receivables', $this->data($request));
    }

    /** Daftar tagih siap cetak untuk dibawa kolektor. */
    public function print(Request $request)
    {
        return view('admin.finance.receivables_print', $this->data($request));
    }

    private function data(Request $request): array
    {
        $filters = array_filter($request->only(['collector_id', 'area', 'bucket', 'search']), fn ($v) => $v !== null && $v !== '');

        return Receivables::aging($filters) + [
            'filters' => $filters,
            'collectorOptions' => User::membersOfCurrentBusiness()->orderBy('name')->get(),
            'areas' => Customer::whereNotNull('entity_name')->where('entity_name', '!=', '')->distinct()->orderBy('entity_name')->pluck('entity_name'),
            'bucketLabels' => Receivables::BUCKETS,
        ];
    }
}
