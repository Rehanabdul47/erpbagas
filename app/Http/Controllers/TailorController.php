<?php

namespace App\Http\Controllers;

use App\Models\Tailor;
use Illuminate\Http\Request;

class TailorController extends Controller
{
    public function index()
    {
        $tailors = Tailor::withCount(['productions' => function ($query) {
            $query->where('status', 'processing');
        }])->with(['productions' => function ($query) {
            $query->where('status', 'processing')->select('id', 'tailor_id', 'cost', 'quantity');
        }])->get();

        // Calculate total active cost for each tailor
        $tailors->each(function ($tailor) {
            $tailor->active_cost = $tailor->productions->sum(function ($prod) {
                return $prod->cost * $prod->quantity;
            });
        });

        return view('pages.tailors.index', compact('tailors'))
            ->with('activePage', 'tailors');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Tailor::create($validated);

        return redirect()->route('tailors.index')->with('success', "Vendor {$validated['name']} berhasil ditambahkan");
    }

    public function update(Request $request, Tailor $tailor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $tailor->update($validated);

        return redirect()->route('tailors.index')->with('success', "Vendor berhasil diupdate menjadi {$validated['name']}");
    }

    public function destroy(Tailor $tailor)
    {
        if ($tailor->productions()->count() > 0) {
            return back()->with('error', "Penjahit {$tailor->name} tidak bisa dihapus karena pernah/sedang mengerjakan produksi");
        }

        $name = $tailor->name;
        $tailor->delete();

        return redirect()->route('tailors.index')->with('success', "Vendor {$name} berhasil dihapus");
    }

    public function monitoring()
    {
        $tailors = Tailor::with(['productions' => function ($query) {
            $query->where('status', 'processing')->with('product');
        }])->get();

        return view('pages.tailors.monitoring', compact('tailors'))
            ->with('activePage', 'tailors_monitoring');
    }
}
