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

        \Log::info('ProfitSharing Update Request:', $request->all());

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
            $features[$key] = $request->input('feature_' . $key) == '1';
        }
        
        $business->update(['enabled_features' => $features]);

        return redirect()->back()->with('success', 'Pengaturan bisnis berhasil diperbarui.');
    }

    public function storeStakeholder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

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
