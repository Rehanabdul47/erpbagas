<?php

namespace App\Http\Controllers;

use App\Models\DigitalPlatform;
use Illuminate\Http\Request;

class DigitalPlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $platforms = DigitalPlatform::latest()->get();
        $activePage = 'digital-platforms';
        return view('pages.digital-platforms.index', compact('platforms', 'activePage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:digital_platforms',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        DigitalPlatform::create($validated);

        return redirect()->route('digital-platforms.index')->with('success', 'Platform E-Commerce berhasil ditambahkan.');
    }

    public function update(Request $request, DigitalPlatform $digitalPlatform)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:digital_platforms,name,' . $digitalPlatform->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $digitalPlatform->update($validated);

        return redirect()->route('digital-platforms.index')->with('success', 'Data Platform berhasil diperbarui.');
    }

    public function destroy(DigitalPlatform $digitalPlatform)
    {
        try {
            $digitalPlatform->delete();
            return redirect()->route('digital-platforms.index')->with('success', 'Data Platform berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('digital-platforms.index')->with('error', 'Platform tidak dapat dihapus karena masih digunakan.');
        }
    }
}
