<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $isManagement = $currentUser->hasAnyRole(['Executive', 'Manager']);

        $query = User::with(['roles', 'branch']);
        
        // Apply branch scoping for non-management users
        if (!$isManagement) {
            $query->where('branch_id', $currentUser->branch_id);
        }

        $users = $query->get();
        $requestedRoles = ['Executive', 'Manager', 'Supervisor', 'Staff', 'Operator Produksi', 'Sales/Marketing', 'Admin', 'Security', 'Driver'];
        $roles = Role::whereIn('name', $requestedRoles)->get();

        // Extract unique departments and branches for frontend filters based on the scoped users
        $availableDepartments = $users->pluck('department')->filter()->unique()->values();
        $availableBranches = $users->pluck('branch')->filter()->unique('id')->values();

        $stats = [
            'total' => $users->count(),
            'active' => $users->count(), // All seeded users are active
            'roles' => $roles->count(),
        ];

        return view('pages.users.index', compact('users', 'roles', 'stats', 'availableDepartments', 'availableBranches'))
            ->with('activePage', 'users');
    }

    public function create()
    {
        $requestedRoles = ['Executive', 'Manager', 'Supervisor', 'Staff', 'Operator Produksi', 'Sales/Marketing', 'Admin', 'Security', 'Driver'];
        $roles = Role::whereIn('name', $requestedRoles)->get();
        $departments = ['All', 'Produksi', 'Marketing', 'Digital Marketing', 'Keuangan', 'HRD & GA', 'Warehouse/Logistik', 'IT Support'];
        $branches = Branch::where('is_active', true)->get();

        return view('pages.users.create', compact('roles', 'departments', 'branches'))
            ->with('activePage', 'users');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'department' => 'required|string',
            'role' => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'department' => $validated['department'],
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        // Auto-create Employee Record
        \App\Models\Employee::create([
            'nip' => 'EMP-' . now()->format('Ymd') . '-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '-', // Default phone
            'position' => ucfirst(str_replace('_', ' ', $validated['role'])),
            'department' => ucfirst($validated['department']),
            'branch_id' => $validated['branch_id'] ?? null,
            'join_date' => now()->toDateString(),
            'basic_salary' => 0,
            'status' => 'active',
        ]);

        return redirect()->route('hrm.index')->with('success', "User {$user->name} berhasil ditambahkan dan data Pegawai otomatis dibuat.");
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasAnyRole(['Executive', 'Manager']) && $user->branch_id !== $currentUser->branch_id) {
            abort(403, 'Unauthorized action. You can only edit users within your own branch.');
        }

        $requestedRoles = ['Executive', 'Manager', 'Supervisor', 'Staff', 'Operator Produksi', 'Sales/Marketing', 'Admin', 'Security', 'Driver'];
        $roles = Role::whereIn('name', $requestedRoles)->get();
        $departments = ['All', 'Produksi', 'Marketing', 'Digital Marketing', 'Keuangan', 'HRD & GA', 'Warehouse/Logistik', 'IT Support'];
        $branches = Branch::where('is_active', true)->get();

        return view('pages.users.edit', compact('user', 'roles', 'departments', 'branches'))
            ->with('activePage', 'users');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        if (!$currentUser->hasAnyRole(['Executive', 'Manager']) && $user->branch_id !== $currentUser->branch_id) {
            abort(403, 'Unauthorized action. You can only update users within your own branch.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'department' => 'required|string',
            'role' => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department' => $validated['department'],
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil diupdate");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }

        $currentUser = auth()->user();
        if (!$currentUser->hasAnyRole(['Executive', 'Manager']) && $user->branch_id !== $currentUser->branch_id) {
            abort(403, 'Unauthorized action. You can only delete users within your own branch.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$name} berhasil dihapus");
    }
}
