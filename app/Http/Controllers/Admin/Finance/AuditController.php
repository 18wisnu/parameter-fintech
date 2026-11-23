<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Audit\AuditTrail;
use Illuminate\Http\Request;

/** Jejak audit: siapa menambah/mengubah/menghapus data keuangan & pelanggan, kapan, dari apa ke apa. */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->whereNotNull('event')
            ->with('user')
            ->when($request->filled('model_type'), fn ($q) => $q->where('model_type', $request->model_type))
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->event))
            ->when($request->filled('user_id'), fn ($q) => $request->user_id === 'system'
                ? $q->whereNull('user_id') : $q->where('user_id', $request->user_id))
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->when(trim((string) $request->search) !== '', fn ($q) => $q->where('activity', 'like', '%' . trim((string) $request->search) . '%'))
            ->latest('id')
            ->paginate(30)->withQueryString();

        return view('admin.finance.audit', [
            'logs' => $logs,
            'types' => AuditTrail::LABELS,
            'staff' => User::membersOfCurrentBusiness()->orderBy('name')->get(),
        ]);
    }
}
