<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\EcommerceOrder;
use App\Models\StockTransfer;
use App\Models\Production;
use App\Traits\BranchScope;
use Carbon\Carbon;

class NotificationController extends Controller
{
    use BranchScope;

    public function getNotifications()
    {
        $user = auth()->user();
        $notifications = collect();

        // 1. Low Stock Notifications
        $lowStockQuery = Product::whereColumn('stock', '<=', 'min_stock_level');
        $this->scopeByBranch($lowStockQuery);
        $lowStockItems = $lowStockQuery->take(3)->get();
        
        foreach ($lowStockItems as $item) {
            $notifications->push([
                'id' => 'stock-' . $item->id,
                'type' => 'warning',
                'title' => 'Stok Menipis',
                'message' => "Stok {$item->name} sisa {$item->stock}.",
                'time' => 'Sekarang',
                'icon' => 'alert-triangle',
                'color' => 'amber',
                'route' => route('inventory.index')
            ]);
        }

        // 2. Pending E-commerce Orders
        $pendingOrders = EcommerceOrder::where('status', 'pending')->latest()->take(3)->get();
        foreach ($pendingOrders as $order) {
            $notifications->push([
                'id' => 'order-' . $order->id,
                'type' => 'info',
                'title' => 'Pesanan Baru',
                'message' => "Order #{$order->order_number} dari {$order->platform}.",
                'time' => $order->created_at->diffForHumans(),
                'icon' => 'shopping-cart',
                'color' => 'indigo',
                'route' => route('orders')
            ]);
        }

        // 3. Stock Transfers (if user is related)
        $transferQuery = StockTransfer::whereIn('status', ['pending', 'sending'])->latest();
        if (!$user->isExecutive()) {
            $branchId = $user->branch_id ?? 1;
            $transferQuery->where(function($q) use ($branchId) {
                $q->where('source_branch_id', $branchId)
                  ->orWhere('destination_branch_id', $branchId);
            });
        }
        $transfers = $transferQuery->take(2)->get();
        foreach ($transfers as $trf) {
            $roleContext = ($user->branch_id == $trf->destination_branch_id) ? 'Masuk' : 'Keluar';
            $notifications->push([
                'id' => 'trf-' . $trf->id,
                'type' => 'info',
                'title' => "Transfer Stok {$roleContext}",
                'message' => "Ref: {$trf->reference} ({$trf->status}).",
                'time' => $trf->created_at->diffForHumans(),
                'icon' => 'arrow-right-left',
                'color' => 'blue',
                'route' => route('production.transfers.index')
            ]);
        }

        // 4. Active Productions
        $productions = Production::with('product')
            ->where('status', 'processing')
            ->latest()
            ->take(2)
            ->get();
        foreach ($productions as $prod) {
            $notifications->push([
                'id' => 'prod-' . $prod->id,
                'type' => 'info',
                'title' => 'Produksi Berjalan',
                'message' => "{$prod->product->name} - Tahap: {$prod->stage}.",
                'time' => $prod->created_at->diffForHumans(),
                'icon' => 'factory',
                'color' => 'emerald',
                'route' => route('production.index')
            ]);
        }

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }
}
