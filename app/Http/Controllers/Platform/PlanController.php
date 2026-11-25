<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

/** Paket langganan & pengaturan platform (instruksi pembayaran). */
class PlanController extends Controller
{
    public function index()
    {
        return view('platform.plans.index', [
            'plans' => Plan::withCount('businesses')->orderBy('sort')->orderBy('price')->get(),
            'instructions' => PlatformSetting::get('payment_instructions'),
        ]);
    }

    public function store(Request $request)
    {
        Plan::create($this->validated($request));

        return back()->with('success', 'Paket ditambahkan.');
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validated($request));

        return back()->with('success', "Paket {$plan->name} diperbarui.");
    }

    public function settings(Request $request)
    {
        $data = $request->validate(['payment_instructions' => 'nullable|string|max:2000']);
        PlatformSetting::put('payment_instructions', $data['payment_instructions'] ?? null);

        return back()->with('success', 'Instruksi pembayaran disimpan.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'max_customers' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:255',
            'sort' => 'nullable|integer|min:0|max:999',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort'] = $data['sort'] ?? 0;

        return $data;
    }
}
