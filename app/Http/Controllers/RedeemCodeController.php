<?php

namespace App\Http\Controllers;

use App\Models\RedeemCode;
use App\Models\Customer;
use Illuminate\Http\Request;

class RedeemCodeController extends Controller
{
    public function index()
    {
        $codes = RedeemCode::latest()->paginate(10);
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
        ]);

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
            'is_active' => 'boolean'
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
        
        if (!$customer->hasCompletedPurchase()) {
            return response()->json([
                'success' => false,
                'message' => 'This redeem code is only available for returning customers with previous purchases.'
            ], 403);
        }

        $code = RedeemCode::where('code', $request->code)->first();

        if (!$code || !$code->isValid($request->customer_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid, expired, or fully used redeem code.'
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
