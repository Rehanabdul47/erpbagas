<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\EcommerceOrder;
use App\Models\StockTransfer;
use App\Models\Production;
use App\Traits\BranchScope;
use App\Models\NotificationRead;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use BranchScope;

    public function getNotifications()
    {
        $user = auth()->user();
        $notifications = collect();

        // Get read notification IDs for this user
        $readIds = NotificationRead::where('user_id', $user->id)
            ->pluck('notification_id')
            ->toArray();

        // 1. Low Stock Notifications
        $lowStockQuery = Product::whereColumn('stock', '<=', 'min_stock_level');
        $this->scopeByBranch($lowStockQuery);
        $lowStockItems = $lowStockQuery->take(5)->get();
        
        foreach ($lowStockItems as $item) {
            $id = 'stock-' . $item->id;
            $notifications->push([
                'id' => $id,
                'is_read' => in_array($id, $readIds),
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
        $pendingOrders = EcommerceOrder::where('status', 'pending')->latest()->take(5)->get();
        foreach ($pendingOrders as $order) {
            $id = 'order-' . $order->id;
            $notifications->push([
                'id' => $id,
                'is_read' => in_array($id, $readIds),
                'type' => 'info',
                'title' => 'Pesanan Baru',
                'message' => "Order #{$order->order_number} dari {$order->platform}.",
                'time' => $order->created_at->diffForHumans(),
                'icon' => 'shopping-cart',
                'color' => 'indigo',
                'route' => route('digital-marketing.orders.index')
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
        $transfers = $transferQuery->take(3)->get();
        foreach ($transfers as $trf) {
            $id = 'trf-' . $trf->id;
            $roleContext = ($user->branch_id == $trf->destination_branch_id) ? 'Masuk' : 'Keluar';
            $notifications->push([
                'id' => $id,
                'is_read' => in_array($id, $readIds),
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
            ->take(3)
            ->get();
        foreach ($productions as $prod) {
            $id = 'prod-' . $prod->id;
            $notifications->push([
                'id' => $id,
                'is_read' => in_array($id, $readIds),
                'type' => 'info',
                'title' => 'Produksi Berjalan',
                'message' => "{$prod->product->name} - Tahap: {$prod->stage}.",
                'time' => $prod->created_at->diffForHumans(),
                'icon' => 'factory',
                'color' => 'emerald',
                'route' => route('production.index')
            ]);
        }

        // Sort: Unread first, then by time? Actually these are different types.
        // I'll just return them all.
        
        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'count' => $unreadCount,
            'total' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'id' => 'required|string'
        ]);

        NotificationRead::updateOrCreate(
            ['user_id' => auth()->id(), 'notification_id' => $request->id],
            ['read_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $response = $this->getNotifications();
        $notifications = $response->original['notifications'];

        foreach ($notifications as $notif) {
            NotificationRead::updateOrCreate(
                ['user_id' => auth()->id(), 'notification_id' => $notif['id']],
                ['read_at' => now()]
            );
        }

        return response()->json(['success' => true]);
    }
}
