<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function index(): Response
    {
        $prices = PriceSetting::query()
            ->orderBy('mutu_beton', 'asc')
            ->get(['id', 'mutu_beton', 'harga_per_m3', 'is_active']);

        return Inertia::render('Admin/Pricing/ManagePricing', [
            'prices' => $prices,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mutu_beton' => ['required', 'string', 'max:50', 'unique:price_settings,mutu_beton'],
            'harga_per_m3' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        PriceSetting::create($validated);

        return back()->with('success', 'Mutu beton baru berhasil ditambahkan.');
    }

    public function update(Request $request, PriceSetting $priceSetting): RedirectResponse
    {
        $validated = $request->validate([
            'harga_per_m3' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $priceSetting->update($validated);

        return back()->with('success', 'Harga berhasil diperbarui.');
    }
}