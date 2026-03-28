<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('allChildren')
            ->roots()
            ->orderBy('code')
            ->get();

        $flatAccounts = Account::orderBy('code')->get();

        return view('pages.finance.accounts.index', compact('accounts', 'flatAccounts'))
            ->with('activePage', 'coa');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_header' => 'boolean',
            'is_control' => 'boolean',
            'normal_balance' => 'required|in:debit,credit',
            'department_code' => 'nullable|string|max:20',
            'cost_center_code' => 'nullable|string|max:20',
        ]);

        $validated['is_header'] = $request->boolean('is_header');
        $validated['is_control'] = $request->boolean('is_control');
        $validated['is_active'] = true;

        // Calculate level
        if ($validated['parent_id']) {
            $parent = Account::find($validated['parent_id']);
            $validated['level'] = $parent ? $parent->level + 1 : 1;
        } else {
            $validated['level'] = 1;
        }

        $account = Account::create($validated);

        AuditTrail::record($account, 'created', null, $account->toArray(), "Akun baru dibuat: {$account->code} - {$account->name}");

        return redirect()->route('finance.accounts.index')->with('success', "Akun {$account->code} berhasil dibuat.");
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_header' => 'boolean',
            'is_control' => 'boolean',
            'normal_balance' => 'required|in:debit,credit',
            'department_code' => 'nullable|string|max:20',
            'cost_center_code' => 'nullable|string|max:20',
        ]);

        $validated['is_header'] = $request->boolean('is_header');
        $validated['is_control'] = $request->boolean('is_control');

        $old = $account->toArray();
        $account->update($validated);

        AuditTrail::record($account, 'updated', $old, $account->fresh()->toArray(), "Akun diperbarui: {$account->code}");

        return redirect()->route('finance.accounts.index')->with('success', "Akun {$account->code} berhasil diperbarui.");
    }

    public function toggleActive(Account $account)
    {
        $account->update(['is_active' => !$account->is_active]);
        $status = $account->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditTrail::record($account, 'updated', null, ['is_active' => $account->is_active], "Akun {$account->code} {$status}");

        return redirect()->route('finance.accounts.index')->with('success', "Akun {$account->code} berhasil {$status}.");
    }
}
