<?php

namespace App\Http\Controllers;

use App\Models\RedeemCode;
use App\Models\Customer;
use App\Traits\BranchScope;
use Illuminate\Http\Request;

class RedeemCodeController extends Controller
{
    use BranchScope;

    public function index()
    {
        $query = RedeemCode::latest();
        $this->scopeByBranch($query);
        $codes = $query->paginate(10);
        
        return view('pages.redeem-codes.index', compact('codes'))->with('activePage', 'redeem-codes');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:redeem_codes',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:0',
            'max_uses_per_customer' => 'required|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
            'only_new_customer' => 'boolean',
            'only_returning_customer' => 'boolean',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        RedeemCode::create($validated);
        return back()->with('success', 'Redeem code created successfully');
    }

    public function update(Request $request, RedeemCode $redeemCode)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:redeem_codes,code,' . $redeemCode->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:0',
            'max_uses_per_customer' => 'required|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'only_new_customer' => 'boolean',
            'only_returning_customer' => 'boolean',
        ]);

        $redeemCode->update($validated);
        return back()->with('success', 'Redeem code updated successfully');
    }

    public function destroy(RedeemCode $redeemCode)
    {
        $redeemCode->delete();
        return back()->with('success', 'Redeem code deleted successfully');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'customer_id' => 'required|exists:customers,id'
        ]);

        $customer = Customer::find($request->customer_id);
        
        $codeQuery = RedeemCode::where('code', $request->code);
        // Scoping: can only apply codes from their own branch or global codes (null branch)
        if (!$this->isExecutive()) {
            $userBranchId = auth()->user()->branch_id;
            $codeQuery->where(function($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId)
                  ->orWhereNull('branch_id');
            });
        }
        $code = $codeQuery->first();

        if (!$code || !$code->isValid($request->customer_id)) {
            $msg = 'Kode redeem tidak valid, kedaluwarsa, atau sudah habis.';
            
            // Provide more specific feedback if possible
            if ($code) {
                // Check usage limit per customer first if customer exists
                $userUsage = 0;
                if ($customer) {
                    $userUsage = \App\Models\Sale::where('customer_id', $customer->id)
                        ->where('redeem_code_id', $code->id)
                        ->count();
                }

                if (!$code->is_active) {
                    $msg = 'Kode redeem ini sudah tidak aktif.';
                } elseif ($code->branch_id && $code->branch_id !== auth()->user()->branch_id && !$this->isExecutive()) {
                    $msg = 'Kode redeem ini tidak berlaku untuk cabang Anda.';
                } elseif ($code->max_uses > 0 && $code->used_count >= $code->max_uses) {
                    $msg = 'Kuota penggunaan kode redeem ini sudah habis.';
                } elseif ($code->max_uses_per_customer > 0 && $userUsage >= $code->max_uses_per_customer) {
                    $msg = 'Anda sudah pernah menggunakan kode redeem ini.';
                } elseif ($code->only_new_customer && $customer->hasCompletedPurchase()) {
                    $msg = 'Kode ini hanya berlaku untuk pelanggan baru.';
                } elseif ($code->only_returning_customer && !$customer->hasCompletedPurchase()) {
                    $msg = 'Kode ini hanya berlaku untuk pelanggan setia (pernah belanja).';
                } elseif ($code->valid_until && now()->gt($code->valid_until->endOfDay())) {
                    $msg = 'Kode redeem ini sudah kedaluwarsa.';
                } elseif ($code->valid_from && now()->lt($code->valid_from)) {
                    $msg = 'Kode redeem ini belum dapat digunakan.';
                }
            }

            return response()->json([
                'success' => false,
                'message' => $msg
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Redeem code applied successfully!',
            'code' => [
                'id' => $code->id,
                'code' => $code->code,
                'type' => $code->type,
                'value' => $code->value,
            ]
        ]);
    }

    public function barcode(RedeemCode $redeemCode)
    {
        return view('pages.redeem-codes.barcode', ['code' => $redeemCode])->with('activePage', 'redeem-codes');
    }
}
