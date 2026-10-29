<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function edit(Request $request): View
    {
        $store = $request->user()->store;
        if (!$store) {
            abort(404, 'Store not found');
        }

        return view('vendor.store.edit', [
            'store' => $store,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            abort(404, 'Store not found');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('stores', 'name')->ignore($store->id)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $store->update($validated);

        return back()->with('success', 'Store details updated successfully.');
    }
}
