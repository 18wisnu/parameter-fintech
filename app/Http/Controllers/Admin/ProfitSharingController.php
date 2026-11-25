<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfitSharingSetting;
use App\Models\ProfitSharingStakeholder;
use Illuminate\Http\Request;

class ProfitSharingController extends Controller
{
    public function index()
    {
        $business = auth()->user()->currentBusiness;
        
        if (!$business) {
            return redirect()->route('admin.business.index');
        }

        $businessName = $business->name;
        $themeColor = $business->theme_color;
        $reservePercentage = $business->reserve_percentage;
        $enabledFeatures = $business->enabled_features ?: [
            'profit_sharing' => true,
            'reserve_fund' => true,
            'salary_management' => true,
            'voucher_analysis' => true,
            'customer_data' => true,
            'invoices' => true,
            'transactions' => true,
            'staff_data' => true,
        ];

        $stakeholders = ProfitSharingStakeholder::where('business_id', $business->id)->get();
        
        return view('admin.profit_sharing.index', compact(
            'reservePercentage', 'stakeholders', 'businessName', 'themeColor', 'enabledFeatures'
        ));
    }

    public function updateSettings(Request $request)
    {
        $business = auth()->user()->currentBusiness;
        if (!$business) {
            return redirect()->route('admin.business.index');
        }

        // Update main business attributes
        $business->update([
            'reserve_percentage' => $request->input('reserve_percentage', $business->reserve_percentage),
            'name' => $request->input('business_name', $business->name),
            'theme_color' => $request->input('theme_color', $business->theme_color),
        ]);

        // Feature toggles
        $featureKeys = [
            'profit_sharing', 'reserve_fund', 'salary_management', 'voucher_analysis',
            'customer_data', 'invoices', 'transactions', 'staff_data'
        ];

        $features = $business->enabled_features ?: [];
        foreach ($featureKeys as $key) {
            if ($request->has('feature_' . $key)) {
                $features[$key] = filter_var($request->input('feature_' . $key), FILTER_VALIDATE_BOOLEAN);
            }
        }
        
        $business->update(['enabled_features' => $features]);

        return redirect()->back()->with('success', 'Pengaturan bisnis berhasil diperbarui.');
    }

    public function toggleFeature(Request $request)
    {
        $business = auth()->user()->currentBusiness;
        if (!$business) {
            return response()->json(['success' => false, 'message' => 'Bisnis tidak aktif'], 403);
        }

        $key = $request->input('key');
        $value = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        $features = $business->enabled_features ?: [];
        $features[$key] = $value;
        $business->update(['enabled_features' => $features]);

        return response()->json([
            'success' => true,
            'key' => $key,
            'value' => $value,
            'message' => "Fitur $key berhasil " . ($value ? 'diaktifkan' : 'dinonaktifkan')
        ]);
    }

    /** Total persentase semua penerima (selain $exceptId) + $new tidak boleh lebih dari 100%. */
    private function exceedsTotal(float $new, ?int $exceptId = null): ?string
    {
        $others = (float) ProfitSharingStakeholder::when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->sum('percentage');

        return $others + $new > 100.0001
            ? 'Total persentase menjadi ' . rtrim(rtrim(number_format($others + $new, 2, ',', '.'), '0'), ',') . '%. Maksimal 100%; sisa yang tersedia ' . rtrim(rtrim(number_format(max(0, 100 - $others), 2, ',', '.'), '0'), ',') . '%.'
            : null;
    }

    public function storeStakeholder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($error = $this->exceedsTotal((float) $request->percentage)) {
            return back()->withInput()->with('error', $error);
        }

        ProfitSharingStakeholder::create([
            'name' => $request->name,
            'percentage' => $request->percentage,
            'business_id' => auth()->user()->current_business_id,
        ]);

        return redirect()->back()->with('success', 'Stakeholder berhasil ditambahkan.');
    }

    public function updateStakeholder(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $stakeholder = ProfitSharingStakeholder::findOrFail($id);
        if ($error = $this->exceedsTotal((float) $request->percentage, $stakeholder->id)) {
            return back()->withInput()->with('error', $error);
        }
        $stakeholder->update([
            'name' => $request->name,
            'percentage' => $request->percentage,
        ]);

        return redirect()->back()->with('success', 'Stakeholder berhasil diperbarui.');
    }

    public function destroyStakeholder($id)
    {
        $stakeholder = ProfitSharingStakeholder::findOrFail($id);
        $stakeholder->delete();

        return redirect()->back()->with('success', 'Stakeholder berhasil dihapus.');
    }
}
