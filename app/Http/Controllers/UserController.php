<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();

        $stats = [
            'total' => $users->count(),
            'active' => $users->count(), // All seeded users are active
            'roles' => $roles->count(),
        ];

        return view('pages.users.index', compact('users', 'roles', 'stats'))
            ->with('activePage', 'users');
    }

    public function create()
    {
        $roles = Role::all();
        $departments = ['all', 'warehouse', 'finance', 'sales', 'hr', 'store'];

        return view('pages.users.create', compact('roles', 'departments'))
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
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'department' => $validated['department'],
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
            'join_date' => now()->toDateString(),
            'basic_salary' => 0,
            'status' => 'active',
        ]);

        return redirect()->route('hrm.index')->with('success', "User {$user->name} berhasil ditambahkan dan data Pegawai otomatis dibuat.");
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = ['all', 'warehouse', 'finance', 'sales', 'hr', 'store'];

        return view('pages.users.edit', compact('user', 'roles', 'departments'))
            ->with('activePage', 'users');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'department' => 'required|string',
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department' => $validated['department'],
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

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$name} berhasil dihapus");
    }
}
