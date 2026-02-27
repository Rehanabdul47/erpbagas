<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $platform = $request->input('platform', 'all');
        $status = $request->input('status', 'all');

        $query = EcommerceOrder::with('items.product')->latest();

        if ($platform !== 'all') {
            $query->where('platform', $platform);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total' => EcommerceOrder::count(),
            'pending' => EcommerceOrder::where('status', 'pending')->count(),
            'ready' => EcommerceOrder::where('status', 'ready_to_ship')->count(),
            'shipped' => EcommerceOrder::where('status', 'shipped')->count(),
        ];

        $activePage = 'orders';

        return view('pages.orders', compact('orders', 'stats', 'platform', 'status', 'activePage'));
    }

    public function show(EcommerceOrder $order)
    {
        $order->load('items.product');
        return view('pages.orders-show', compact('order'))->with('activePage', 'orders');
    }

    public function updateStatus(Request $request, EcommerceOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready_to_ship,shipped,delivered,cancelled',
            'receipt_number' => 'nullable|string|max:255',
        ]);

        $order->status = $validated['status'];
        
        if (isset($validated['receipt_number'])) {
            $order->receipt_number = $validated['receipt_number'];
        }

        $order->save();

        // Optional: If status becomes shipped/delivered, reduce stock
        if ($order->status == 'shipped' && $order->wasChanged('status')) {
            $this->deductStock($order);
        }

        return redirect()->back()->with('success', "Status pesanan {$order->order_number} berhasil diperbarui.");
    }

    private function deductStock(EcommerceOrder $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock -= $item->quantity;
                    $product->save();
                }
            }
        });
    }

    // Mock API Sync Endpoint
    public function syncApi()
    {
        // Define random platforms
        $platforms = ['Shopee', 'Tokopedia', 'TikTok Shop'];
        $couriers = ['J&T Express', 'JNE Reguler', 'Sicepat HALU', 'GoSend', 'GrabExpress'];
        $statuses = ['pending', 'ready_to_ship'];

        $products = Product::where('stock', '>', 5)->inRandomOrder()->take(5)->get();

        if ($products->isEmpty()) {
            return redirect()->back()->with('error', 'Produk tidak mencukupi untuk simulasi pesanan.');
        }

        DB::transaction(function () use ($platforms, $couriers, $statuses, $products) {
            // Generate 2-4 random orders
            $orderCount = rand(2, 4);

            for ($i = 0; $i < $orderCount; $i++) {
                $platform = $platforms[array_rand($platforms)];
                $shippingCost = rand(10, 50) * 1000;
                
                $order = EcommerceOrder::create([
                    'order_number' => strtoupper(Str::random(3)) . '-' . time() . '-' . rand(100, 999),
                    'platform' => $platform,
                    'customer_name' => 'Online Customer ' . rand(1, 100),
                    'customer_phone' => '081' . rand(100000000, 999999999),
                    'shipping_address' => 'Jl. E-Commerce Mockup No.' . rand(1, 99) . ', Jakarta',
                    'shipping_courier' => $couriers[array_rand($couriers)],
                    'shipping_cost' => $shippingCost,
                    'total_amount' => 0, // Calculated below
                    'status' => $statuses[array_rand($statuses)],
                    'order_date' => Carbon::now()->subMinutes(rand(1, 120))
                ]);

                $totalAmount = $shippingCost;
                
                // Add 1-3 items
                $itemCount = rand(1, 3);
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $price = $product->price;
                    $subtotal = $qty * $price;
                    
                    EcommerceOrderItem::create([
                        'ecommerce_order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $totalAmount += $subtotal;
                }

                $order->update(['total_amount' => $totalAmount]);
            }
        });

        return redirect()->route('orders')->with('success', 'Berhasil menyinkronkan data pesanan baru dari platform E-Commerce.');
    }
}
