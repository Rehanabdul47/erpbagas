<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEcommerceOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-ecommerce-orders';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Simulate syncing orders from E-commerce platforms automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated e-commerce order sync simulation...');

        $platforms = ['Shopee', 'Tokopedia', 'TikTok Shop'];
        $couriers = ['J&T Express', 'JNE Reguler', 'Sicepat HALU', 'GoSend', 'GrabExpress'];
        $statuses = ['pending', 'ready_to_ship'];

        $products = Product::where('stock', '>', 5)->inRandomOrder()->take(5)->get();

        if ($products->isEmpty()) {
            $this->error('Not enough products with stock > 5 to simulate orders.');
            return Command::FAILURE;
        }

        DB::transaction(function () use ($platforms, $couriers, $statuses, $products) {
            $orderCount = rand(1, 3); // Slightly fewer for automatic sync to avoid flooding

            for ($i = 0; $i < $orderCount; $i++) {
                $platform = $platforms[array_rand($platforms)];
                $shippingCost = rand(10, 50) * 1000;
                
                $order = EcommerceOrder::create([
                    'order_number' => strtoupper(Str::random(3)) . '-' . time() . '-' . rand(100, 999),
                    'platform' => $platform,
                    'customer_name' => 'Auto Customer ' . rand(1, 100),
                    'customer_phone' => '081' . rand(100000000, 999999999),
                    'shipping_address' => 'Jl. E-Commerce Auto No.' . rand(1, 99) . ', Jakarta',
                    'shipping_courier' => $couriers[array_rand($couriers)],
                    'shipping_cost' => $shippingCost,
                    'total_amount' => 0,
                    'status' => $statuses[array_rand($statuses)],
                    'order_date' => Carbon::now()
                ]);

                $totalAmount = $shippingCost;
                $itemCount = rand(1, 2);
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 2);
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
                $this->line("Created order: {$order->order_number} ({$platform})");
            }
        });

        $this->info('Sync simulation completed successfully.');
        return Command::SUCCESS;
    }
}
