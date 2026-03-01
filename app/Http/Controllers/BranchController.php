<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Employee;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::latest()->get();
        return view('pages.branches.index', compact('branches'))->with('activePage', 'branches');
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('pages.branches.create', compact('employees'))->with('activePage', 'branches');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch',
            'manager_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Cabang/Toko berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('pages.branches.edit', compact('branch', 'employees'))->with('activePage', 'branches');
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,branch',
            'manager_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Data Cabang/Toko berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        // Add check if branch has related sales/users, but simplify for now
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang/Toko berhasil dihapus.');
    }
}
