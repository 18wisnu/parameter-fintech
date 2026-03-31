<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::all();
        return view('sites.index', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pon_power' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        Site::create($request->all());

        return redirect()->route('sites.index')->with('success', 'Site berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pon_power' => 'nullable|numeric',
            // tambahkan field lain jika ada (lokasi, dll)
        ]);

        $site = \App\Models\Site::findOrFail($id);
        $site->update($request->all());

        return back()->with('success', 'Data Site berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $site = \App\Models\Site::findOrFail($id);
        
        // Opsional: Cek apakah ada device yang masih nyangkut di site ini
        if ($site->devices()->count() > 0) {
            return back()->with('error', 'Site tidak bisa dihapus karena masih ada perangkat yang terhubung!');
        }

        $site->delete();

        return back()->with('success', 'Site berhasil dihapus!');
    }
}
