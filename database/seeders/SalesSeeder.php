<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'status' => 'active',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@example.com',
                'phone' => '089876543210',
                'address' => 'Jl. Mawar No. 5, Bandung',
                'status' => 'active',
            ],
            [
                'name' => 'Agus Pratama',
                'email' => 'agus@example.com',
                'phone' => '081122334455',
                'address' => 'Jl. Melati No. 2, Surabaya',
                'status' => 'leads',
            ],
        ];

        foreach ($customers as $cData) {
            $customer = Customer::create($cData);

            if ($customer->status === 'active') {
                // Create a sale for active customers
                $sale = Sale::create([
                    'customer_id' => $customer->id,
                    'invoice_no' => 'INV-' . strtoupper(uniqid()),
                    'sale_date' => Carbon::now()->subDays(rand(1, 10)),
                    'total_amount' => 0,
                    'status' => 'paid',
                ]);

                $product = Product::first();
                if ($product) {
                    $qty = rand(1, 5);
                    $subtotal = $qty * $product->price;
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    $sale->update(['total_amount' => $subtotal]);
                }
            }
        }
    }
}
