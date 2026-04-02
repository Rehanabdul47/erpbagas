<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceOrder;
use App\Services\TikTokShop\TikTokLogisticService;

class EcommerceOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = EcommerceOrder::with('items')->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);
        return view('pages.ecommerce-orders.index', compact('orders'))->with('activePage', 'ecommerce-orders');
    }

    public function show(EcommerceOrder $order)
    {
        return view('pages.ecommerce-orders.show', compact('order'))->with('activePage', 'ecommerce-orders');
    }

    /**
     * Get or Generate Shipping Document (Resi)
     */
    public function printResi($id)
    {
        $order = EcommerceOrder::findOrFail($id);
        
        // If already have URL, redirect to it
        if ($order->shipping_document_url) {
            return redirect($order->shipping_document_url);
        }

        // Generate via API
        $logisticService = app(TikTokLogisticService::class);
        $result = $logisticService->getShippingDocument($order);

        if ($result['status'] === 'success') {
            return redirect($result['url']);
        }

        return redirect()->back()->with('error', 'Gagal mencetak resi: ' . $result['message']);
    }

    /**
     * Confirm Shipment (Ready to Ship)
     */
    public function confirmShipment($id)
    {
        $order = EcommerceOrder::findOrFail($id);
        
        $logisticService = app(TikTokLogisticService::class);
        $success = $logisticService->confirmShipment($order);

        if ($success) {
            return redirect()->back()->with('success', 'Pengiriman berhasil dikonfirmasi ke TikTok.');
        }

        return redirect()->back()->with('error', 'Gagal konfirmasi pengiriman.');
    }
}
