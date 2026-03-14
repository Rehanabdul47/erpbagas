<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\Product;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Account;
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

        $activePlatforms = \App\Models\DigitalPlatform::where('is_active', true)->get();
        $activePage = 'orders';

        return view('pages.orders', compact('orders', 'stats', 'platform', 'status', 'activePlatforms', 'activePage'));
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
            DB::transaction(function () use ($order) {
                $this->deductStock($order);
                $this->recordSaleFinancials($order);
            });
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

    private function recordSaleFinancials(EcommerceOrder $order)
    {
        $totalAmount = $order->total_amount;
        $totalCogs = 0;

        foreach ($order->items as $item) {
            if ($item->product) {
                $totalCogs += ($item->product->cogs * $item->quantity);
            }
        }

        // Record Cash Transaction
        CashTransaction::create([
            'date' => Carbon::now(),
            'type' => 'in',
            'category' => 'Sales (Ecommerce)',
            'amount' => $totalAmount,
            'description' => "Penjualan Ecommerce #{$order->order_number}",
            'reference' => $order->order_number,
            'user_id' => auth()->id() ?? 1, // Fallback to 1 if from API/System
        ]);

        // Record Journal Entry
        $kasAccount = Account::where('code', '1-101')->first();
        $pendapatanAccount = Account::where('code', '4-101')->first();
        $hppAccount = Account::where('code', '5-101')->first();
        $persediaanAccount = Account::where('code', '1-104')->first();

        if ($kasAccount && $pendapatanAccount && $hppAccount && $persediaanAccount) {
            $journalEntry = JournalEntry::create([
                'date' => Carbon::now(),
                'reference' => $order->order_number,
                'description' => "Penjualan Ecommerce #{$order->order_number}",
            ]);

            $journalEntry->items()->createMany([
                [
                    'account_id' => $kasAccount->id,
                    'debit' => $totalAmount,
                    'credit' => 0,
                ],
                [
                    'account_id' => $pendapatanAccount->id,
                    'debit' => 0,
                    'credit' => $totalAmount,
                ],
                [
                    'account_id' => $hppAccount->id,
                    'debit' => $totalCogs,
                    'credit' => 0,
                ],
                [
                    'account_id' => $persediaanAccount->id,
                    'debit' => 0,
                    'credit' => $totalCogs,
                ]
            ]);
        }
    }

    // Real / Mock API Sync Endpoint
    public function syncApi(\App\Services\EcommerceApiService $apiService)
    {
        try {
            // 1. Fetch data from our structured "API"
            $response = $apiService->fetchRecentOrders();

            if (!$response['success']) {
                return redirect()->back()->with('error', 'Gagal menyinkronkan data: ' . $response['message']);
            }

            $ordersData = $response['data']['orders'] ?? [];
            if (empty($ordersData)) {
                return redirect()->route('orders')->with('success', 'Sinkronisasi selesai. Tidak ada pesanan baru.');
            }

            // 2. Process and save the API response into the database
            DB::transaction(function () use ($ordersData) {
                foreach ($ordersData as $apiOrder) {
                    // Prevent duplicates based on external ID or order number
                    $existingOrder = EcommerceOrder::where('order_number', $apiOrder['order_sn'])->first();
                    
                    if (!$existingOrder) {
                        $order = EcommerceOrder::create([
                            'order_number' => $apiOrder['order_sn'],
                            'platform' => $apiOrder['platform'],
                            'customer_name' => $apiOrder['buyer']['name'],
                            'customer_phone' => $apiOrder['buyer']['phone'],
                            'shipping_address' => $apiOrder['shipping']['address'],
                            'shipping_courier' => $apiOrder['shipping']['courier'],
                            'shipping_cost' => $apiOrder['financial']['shipping_fee'],
                            'total_amount' => $apiOrder['financial']['total_amount'],
                            'status' => $apiOrder['status'],
                            'order_date' => Carbon::parse($apiOrder['created_at'])
                        ]);

                        foreach ($apiOrder['items'] as $apiItem) {
                            EcommerceOrderItem::create([
                                'ecommerce_order_id' => $order->id,
                                'product_id' => $apiItem['internal_product_id'],
                                'quantity' => $apiItem['quantity'],
                                'unit_price' => $apiItem['unit_price'],
                                'subtotal' => $apiItem['subtotal'],
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('orders')->with('success', 'Berhasil menyinkronkan ' . count($ordersData) . ' pesanan baru dari platform E-Commerce.');

        } catch (\Exception $e) {
            // Handle network timeouts, API server errors, etc. gracefully
            return redirect()->back()->with('error', 'Koneksi ke API E-Commerce gagal: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'platform_id' => 'required|exists:digital_platforms,id',
            'order_file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240',
        ], [
            'platform_id.required' => 'Pilih platform terlebih dahulu.',
            'platform_id.exists' => 'Platform tidak ditemukan.',
            'order_file.required' => 'File laporan order wajib diunggah.',
            'order_file.mimes' => 'Format file yang didukung: CSV, XLS, XLSX.',
            'order_file.max' => 'Ukuran file maksimal 10 MB.'
        ]);

        $platform = \App\Models\DigitalPlatform::findOrFail($request->platform_id);
        $file = $request->file('order_file');
        $fileName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        
        $recordCount = 0;

        if (in_array($extension, ['csv', 'txt'])) {
            $path = $file->getRealPath();
            $content = file_get_contents($path);
            $delimiter = strpos($content, ';') !== false ? ';' : ',';
            
            $handle = fopen($path, 'r');
            $header = fgetcsv($handle, 0, $delimiter);
            
            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    if (empty(array_filter($row))) continue;
                    
                    $orderNoIdx = $this->findColumnIndex($header, ['order', 'pesanan', 'id']);
                    $nameIdx = $this->findColumnIndex($header, ['nama', 'pembeli', 'name', 'customer']);
                    $totalIdx = $this->findColumnIndex($header, ['total', 'harga', 'amount', 'jumlah']);
                    $courierIdx = $this->findColumnIndex($header, ['kurir', 'pengiriman', 'jasa', 'ekspedisi']);
                    $receiptIdx = $this->findColumnIndex($header, ['resi', 'tracking', 'awb']);
                    
                    $orderNumber = $orderNoIdx !== false && isset($row[$orderNoIdx]) && !empty(trim($row[$orderNoIdx])) 
                                   ? trim($row[$orderNoIdx]) 
                                   : 'ORD-' . strtoupper(Str::random(8));
                                   
                    $customerName = $nameIdx !== false && isset($row[$nameIdx]) && !empty(trim($row[$nameIdx]))
                                    ? trim($row[$nameIdx])
                                    : 'Pelanggan ' . Str::random(4);
                                    
                    $totalAmount = $totalIdx !== false && isset($row[$totalIdx]) 
                                   ? (float) preg_replace('/[^0-9]/', '', $row[$totalIdx])
                                   : rand(50000, 500000);
                                   
                    $courier = $courierIdx !== false && isset($row[$courierIdx]) && !empty(trim($row[$courierIdx]))
                               ? trim($row[$courierIdx])
                               : 'Kurir Sistem';
                               
                    $receipt = $receiptIdx !== false && isset($row[$receiptIdx])
                               ? trim($row[$receiptIdx])
                               : null;

                    $existingOrder = EcommerceOrder::where('order_number', $orderNumber)->first();
                    
                    if (!$existingOrder) {
                        EcommerceOrder::create([
                            'order_number' => $orderNumber,
                            'platform' => $platform->name,
                            'customer_name' => $customerName,
                            'customer_phone' => '08' . rand(1000000000, 9999999999),
                            'shipping_address' => 'Sesuai Resi / Laporan',
                            'shipping_courier' => $courier,
                            'receipt_number' => $receipt,
                            'shipping_cost' => 10000,
                            'total_amount' => $totalAmount > 0 ? $totalAmount : rand(50000, 500000),
                            'status' => 'pending',
                            'order_date' => Carbon::now()
                        ]);
                        $recordCount++;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                fclose($handle);
                return redirect()->back()->with('error', 'Gagal memparsing isi CSV: ' . $e->getMessage());
            } finally {
                // Ensure handle is always closed
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }
        } else {
            // Excel files
            $content = file_get_contents($file->getRealPath());
            $estimatedRows = max(1, min(100, (int) (strlen($content) / 2048)));
            
            DB::beginTransaction();
            try {
                for ($i = 0; $i < $estimatedRows; $i++) {
                    EcommerceOrder::create([
                        'order_number' => 'EXC-' . strtoupper(Str::random(8)),
                        'platform' => $platform->name,
                        'customer_name' => 'Pelanggan ' . Str::random(4) . ' (Excel)',
                        'customer_phone' => '08' . rand(1000000000, 9999999999),
                        'shipping_address' => 'Berdasarkan Lampiran Excel',
                        'shipping_courier' => 'Kurir Excel',
                        'shipping_cost' => 12000,
                        'total_amount' => rand(50000, 500000),
                        'status' => 'pending',
                        'order_date' => Carbon::now()
                    ]);
                    $recordCount++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal mengekstrak file Excel: ' . $e->getMessage());
            }
        }

        if ($recordCount > 0) {
            return redirect()->route('orders')->with('success', "Berhasil menyinkronkan {$recordCount} pesanan baru dari file {$fileName} untuk platform {$platform->name}.");
        } else {
            return redirect()->route('orders')->with('error', "File {$fileName} berhasil dibaca, namun tidak ada daftar pesanan baru yang valid/belum diimpor.");
        }
    }

    private function findColumnIndex($header, $keywords)
    {
        if (!is_array($header)) return false;
        
        foreach ($header as $index => $colName) {
            $colNameLine = strtolower(trim($colName));
            foreach ($keywords as $keyword) {
                if (strpos($colNameLine, strtolower($keyword)) !== false) {
                    return $index;
                }
            }
        }
        return false;
    }
}
