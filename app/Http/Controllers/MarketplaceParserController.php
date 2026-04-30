<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Product;

class MarketplaceParserController extends Controller
{
    public function index()
    {
        return view('pages.tools.marketplace_parser', [
            'activePage' => 'marketplace-parser'
        ]);
    }

    public function parse(Request $request)
    {
        $request->validate([
            'tiktok_file' => 'nullable|file|mimes:xlsx,xls,csv,ods',
            'shopee_file' => 'nullable|file|mimes:xlsx,xls,csv,ods',
        ]);

        if (!$request->hasFile('tiktok_file') && !$request->hasFile('shopee_file')) {
            return back()->with('error', 'Silakan unggah minimal satu file (TikTok atau Shopee).');
        }

        // Cache product cogs to prevent N+1 queries
        $products = Product::select('sku', 'cogs')->get()->keyBy('sku');
        $productCogsCache = $products->map(fn($p) => $p->cogs)->toArray();

        $tiktokData = null;
        $shopeeData = null;

        try {
            if ($request->hasFile('tiktok_file')) {
                $tiktokData = $this->parseTikTok($request->file('tiktok_file')->getRealPath(), $productCogsCache);
            }

            if ($request->hasFile('shopee_file')) {
                $shopeeData = $this->parseShopee($request->file('shopee_file')->getRealPath(), $productCogsCache);
            }

            return view('pages.tools.marketplace_parser', [
                'tiktokData' => $tiktokData,
                'shopeeData' => $shopeeData,
                'activePage' => 'marketplace-parser'
            ])->with('success', 'File berhasil dianalisis!');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function parseTikTok($filePath, $productCogsCache)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $result = [
            'omzet' => 0,
            'batal_count' => 0,
            'batal_omzet' => 0,
            'hpp_total' => 0,
            'profit' => 0,
            'unique_orders' => [],
            'order_counts' => [],
            'unique_skus' => [],
            'rankings' => [],
            'provinces' => [],
            'cities' => [],
            'payment_methods' => [],
            'cancel_by' => [],
            'status_breakdown' => [],
        ];

        // Start from row 3 (Row 1=Header, Row 2=Description)
        foreach ($rows as $index => $row) {
            if ($index < 3) continue;
            
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            $orderId = $row['A'] ?? null;
            $orderStatus = trim($row['B'] ?? '');
            $skuId = $row['F'] ?? null;
            $sellerSku = trim($row['G'] ?? 'UNKNOWN');
            $productName = ($row['H'] ?? '') . ' ' . ($row['I'] ?? '');
            
            // Handle numeric values safely
            $qty = floatval($row['J'] ?? 0);
            $returnedQty = floatval($row['K'] ?? 0);
            $netQty = $qty - $returnedQty;
            
            // In TikTok format, column L is "SKU Unit Original Price" 
            // Wait, usually the user wants to calculate omzet from unit price * qty.
            $unitPrice = $this->parseCurrency($row['L'] ?? '0');
            $potentialOmzet = $unitPrice * $netQty;
            
            $cogs = $productCogsCache[$sellerSku] ?? 0;
            $totalCogs = $cogs * $netQty;

            // Tracking uniques
            if ($orderId) {
                $result['unique_orders'][$orderId] = true;
                $result['order_counts'][$orderId] = ($result['order_counts'][$orderId] ?? 0) + 1;
            }
            if ($skuId) $result['unique_skus'][$skuId] = true;

            // Aggregations
            $province = trim($row['AW'] ?? '');
            if ($province) $result['provinces'][$province] = ($result['provinces'][$province] ?? 0) + 1;
            
            $city = trim($row['AX'] ?? '');
            if ($city) $result['cities'][$city] = ($result['cities'][$city] ?? 0) + 1;
            
            $paymentMethod = trim($row['BC'] ?? '');
            if ($paymentMethod) $result['payment_methods'][$paymentMethod] = ($result['payment_methods'][$paymentMethod] ?? 0) + 1;

            // Status Breakdown
            if (!isset($result['status_breakdown'][$orderStatus])) {
                $result['status_breakdown'][$orderStatus] = ['count' => 0, 'omzet' => 0, 'qty' => 0];
            }
            $result['status_breakdown'][$orderStatus]['count']++;
            $result['status_breakdown'][$orderStatus]['omzet'] += $potentialOmzet;
            $result['status_breakdown'][$orderStatus]['qty'] += $netQty;

            if ($orderStatus === 'Dibatalkan') {
                $result['batal_count']++;
                $result['batal_omzet'] += $potentialOmzet;
                
                $cancelBy = trim($row['AJ'] ?? '');
                if ($cancelBy) $result['cancel_by'][$cancelBy] = ($result['cancel_by'][$cancelBy] ?? 0) + 1;
                
                continue; // Skip adding to valid omzet
            }

            // Valid Orders
            if ($netQty > 0) {
                $result['omzet'] += $potentialOmzet;
                $result['hpp_total'] += $totalCogs;
                
                // Rankings
                if (!isset($result['rankings'][$sellerSku])) {
                    $result['rankings'][$sellerSku] = [
                        'name' => trim($productName),
                        'qty' => 0,
                        'omzet' => 0
                    ];
                }
                $result['rankings'][$sellerSku]['qty'] += $netQty;
                $result['rankings'][$sellerSku]['omzet'] += $potentialOmzet;
            }
        }

        $result['profit'] = $result['omzet'] - $result['hpp_total'];
        
        // Sort rankings by Qty desc
        usort($result['rankings'], fn($a, $b) => $b['qty'] <=> $a['qty']);
        
        // Count uniques
        $result['total_orders'] = count($result['unique_orders']);
        $result['total_skus'] = count($result['unique_skus']);

        // Sort occurrences
        arsort($result['provinces']);
        arsort($result['cities']);
        arsort($result['payment_methods']);
        arsort($result['cancel_by']);

        // Duplication stats
        $result['duplicate_orders'] = collect($result['order_counts'])->filter(fn($count) => $count > 1);
        $result['duplicate_count'] = $result['duplicate_orders']->count();

        return $result;
    }

    private function parseShopee($filePath, $productCogsCache)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $result = [
            'omzet' => 0,
            'batal_count' => 0,
            'batal_omzet' => 0,
            'unique_orders' => [],
            'order_counts' => [],
            'unique_resis' => [],
            'rankings' => [],
            'addresses' => [],
            'cancel_reasons' => [],
            'status_breakdown' => [],
        ];

        // Start from row 3
        foreach ($rows as $index => $row) {
            if ($index < 3) continue;
            
            // Skip empty rows
            if (empty(array_filter($row))) continue;

            $orderId = $row['A'] ?? null;
            $orderStatus = trim($row['B'] ?? '');
            $resi = $row['E'] ?? null;
            
            $sellerSku = trim($row['O'] ?? 'UNKNOWN'); // Nomor Referensi SKU
            $productName = ($row['N'] ?? '') . ' ' . ($row['P'] ?? '');
            
            $qty = floatval($row['S'] ?? 0);
            $returnedQty = floatval($row['T'] ?? 0);
            $netQty = $qty - $returnedQty;
            
            $unitPrice = $this->parseCurrency($row['Q'] ?? '0'); // Harga Awal
            $potentialOmzet = $unitPrice * $netQty;
            
            // Tracking uniques
            if ($orderId) {
                $result['unique_orders'][$orderId] = true;
                $result['order_counts'][$orderId] = ($result['order_counts'][$orderId] ?? 0) + 1;
            }
            if ($resi) $result['unique_resis'][$resi] = true;

            // Status Breakdown
            if (!isset($result['status_breakdown'][$orderStatus])) {
                $result['status_breakdown'][$orderStatus] = ['count' => 0, 'omzet' => 0, 'qty' => 0];
            }
            $result['status_breakdown'][$orderStatus]['count']++;
            $result['status_breakdown'][$orderStatus]['omzet'] += $potentialOmzet;
            $result['status_breakdown'][$orderStatus]['qty'] += $netQty;

            // Addresses
            $address = trim($row['AT'] ?? '');
            if ($address && $orderStatus !== 'Batal') {
                $result['addresses'][] = $address; 
            }

            if ($orderStatus === 'Batal') {
                $result['batal_count']++;
                $result['batal_omzet'] += $potentialOmzet;
                
                $cancelReason = trim($row['C'] ?? '');
                if ($cancelReason) $result['cancel_reasons'][$cancelReason] = ($result['cancel_reasons'][$cancelReason] ?? 0) + 1;
                
                continue; 
            }

            // Valid Orders
            if ($netQty > 0) {
                $result['omzet'] += $potentialOmzet;
                
                // Rankings
                if (!isset($result['rankings'][$sellerSku])) {
                    $result['rankings'][$sellerSku] = [
                        'name' => trim($productName),
                        'qty' => 0,
                        'omzet' => 0
                    ];
                }
                $result['rankings'][$sellerSku]['qty'] += $netQty;
                $result['rankings'][$sellerSku]['omzet'] += $potentialOmzet;
            }
        }
        
        // Sort rankings by Qty desc
        usort($result['rankings'], fn($a, $b) => $b['qty'] <=> $a['qty']);
        
        // Count uniques
        $result['total_orders'] = count($result['unique_orders']);
        $result['total_resis'] = count($result['unique_resis']);

        arsort($result['cancel_reasons']);

        // Duplication stats
        $result['duplicate_orders'] = collect($result['order_counts'])->filter(fn($count) => $count > 1);
        $result['duplicate_count'] = $result['duplicate_orders']->count();

        return $result;
    }

    private function parseCurrency($value)
    {
        // Shopee formats currency like "200.000" or "200000"
        $value = str_replace(['Rp', '.', ' '], '', $value);
        // If there's comma for decimal, replace with dot
        $value = str_replace(',', '.', $value);
        return floatval($value);
    }
}
