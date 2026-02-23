<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Kemeja', 'Celana', 'Jaket', 'Kaos', 'Hoodie', 'Polo', 'Blazer'];
        $catMap = [];
        foreach ($categories as $name) {
            $catMap[$name] = Category::create(['name' => $name])->id;
        }

        $products = [
            ['sku' => 'GUP-001', 'name' => 'Kemeja Formal Putih L', 'category' => 'Kemeja', 'stock' => 150, 'safety_stock' => 20, 'price' => 250000, 'cogs' => 125000, 'batch' => 'B-2024-001', 'location' => 'Rak A1', 'image' => 'https://images.unsplash.com/photo-1596755094514-f87034a26ef4?w=400&q=80'],
            ['sku' => 'GUP-002', 'name' => 'Celana Chino Navy M', 'category' => 'Celana', 'stock' => 45, 'safety_stock' => 15, 'price' => 320000, 'cogs' => 160000, 'batch' => 'B-2024-002', 'location' => 'Rak A2', 'image' => 'https://images.unsplash.com/photo-1624371414361-e6e8ea04c112?w=400&q=80'],
            ['sku' => 'GUP-003', 'name' => 'Jaket Bomber Black XL', 'category' => 'Jaket', 'stock' => 5, 'safety_stock' => 10, 'price' => 450000, 'cogs' => 225000, 'batch' => 'B-2024-003', 'location' => 'Rak B1', 'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=400&q=80'],
            ['sku' => 'GUP-004', 'name' => 'Kaos Polos Hitam S', 'category' => 'Kaos', 'stock' => 0, 'safety_stock' => 5, 'price' => 95000, 'cogs' => 45000, 'batch' => 'B-2024-004', 'location' => 'Rak B2', 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80'],
            ['sku' => 'GUP-005', 'name' => 'Kemeja Batik Modern M', 'category' => 'Kemeja', 'stock' => 88, 'safety_stock' => 15, 'price' => 380000, 'cogs' => 190000, 'batch' => 'B-2024-005', 'location' => 'Rak A3', 'image' => 'https://images.unsplash.com/photo-1589310243389-96a5483213a8?w=400&q=80'],
            ['sku' => 'GUP-006', 'name' => 'Celana Jeans Slim Fit 32', 'category' => 'Celana', 'stock' => 62, 'safety_stock' => 20, 'price' => 285000, 'cogs' => 142000, 'batch' => 'B-2024-006', 'location' => 'Rak A4', 'image' => 'https://images.unsplash.com/photo-1542272604-787c38ad5551?w=400&q=80'],
            ['sku' => 'GUP-007', 'name' => 'Hoodie Basic Grey L', 'category' => 'Hoodie', 'stock' => 33, 'safety_stock' => 10, 'price' => 275000, 'cogs' => 135000, 'batch' => 'B-2024-007', 'location' => 'Rak C1', 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=400&q=80'],
            ['sku' => 'GUP-008', 'name' => 'Polo Shirt Navy XL', 'category' => 'Polo', 'stock' => 8, 'safety_stock' => 10, 'price' => 195000, 'cogs' => 95000, 'batch' => 'B-2024-008', 'location' => 'Rak C2', 'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=400&q=80'],
            ['sku' => 'GUP-009', 'name' => 'Blazer Formal Navy L', 'category' => 'Blazer', 'stock' => 22, 'safety_stock' => 5, 'price' => 650000, 'cogs' => 325000, 'batch' => 'B-2024-009', 'location' => 'Rak D1', 'image' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=400&q=80'],
            ['sku' => 'GUP-010', 'name' => 'Kemeja Flanel Merah L', 'category' => 'Kemeja', 'stock' => 41, 'safety_stock' => 10, 'price' => 220000, 'cogs' => 110000, 'batch' => 'B-2024-010', 'location' => 'Rak A5', 'image' => 'https://images.unsplash.com/photo-1617114919297-3c8ddb01f599?w=400&q=80'],
        ];

        $productMap = [];
        foreach ($products as $p) {
            $productMap[$p['sku']] = Product::create([
                'sku' => $p['sku'],
                'name' => $p['name'],
                'image' => $p['image'],
                'category_id' => $catMap[$p['category']],
                'stock' => $p['stock'],
                'safety_stock' => $p['safety_stock'],
                'price' => $p['price'],
                'cogs' => $p['cogs'],
                'batch' => $p['batch'],
                'location' => $p['location'],
            ])->id;
        }

        $movements = [
            ['product' => 'GUP-001', 'type' => 'out', 'quantity' => 1, 'reference' => 'Order #TK-12345', 'method' => 'Auto'],
            ['product' => 'GUP-002', 'type' => 'in', 'quantity' => 50, 'reference' => 'PO-001', 'method' => 'Manual'],
            ['product' => 'GUP-003', 'type' => 'out', 'quantity' => 2, 'reference' => 'Order #SP-67890', 'method' => 'Auto'],
            ['product' => 'GUP-005', 'type' => 'in', 'quantity' => 30, 'reference' => 'PO-002', 'method' => 'Manual'],
            ['product' => 'GUP-007', 'type' => 'out', 'quantity' => 3, 'reference' => 'Order #TK-12350', 'method' => 'Auto'],
        ];

        foreach ($movements as $m) {
            StockMovement::create([
                'product_id' => $productMap[$m['product']],
                'type' => $m['type'],
                'quantity' => $m['quantity'],
                'reference' => $m['reference'],
                'method' => $m['method'],
            ]);
        }
    }
}
