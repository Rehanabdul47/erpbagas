<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Str;

class DigitalMarketingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $selectedPlatform = $request->query('platform', 'all');

        // Get unique platforms for filter
        $platforms = EcommerceOrder::distinct()->pluck('platform')->filter()->values();

        $query = EcommerceOrder::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->where('status', '!=', 'cancelled');

        if ($selectedPlatform !== 'all') {
            $query->where('platform', $selectedPlatform);
        }

        // Metrik Bulan Ini
        $totalOrders = (clone $query)->count();
        $totalGrossRevenue = (clone $query)->sum('total_amount');
        $totalFees = (clone $query)->select(DB::raw('SUM(platform_fee + fixed_fee + service_fee) as total'))->first()->total ?? 0;
        
        // Total Pemasukan setelah dipotong fee platform
        $totalRevenue = $totalGrossRevenue - $totalFees;

        // Biaya Pengeluaran (Iklan & Marketing Online) dari Buku Kas
        $totalExpense = CashTransaction::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('type', 'out')
            ->where(function ($q) {
                $q->where('category', 'like', '%Iklan%')
                  ->orWhere('category', 'like', '%Marketing%')
                  ->orWhere('description', 'like', '%Ads%');
            })
            ->sum('amount');

        // Kalkulasi ROAS (Return on Ad Spend) - Menggunakan Gross Revenue untuk ROAS standar
        $roas = $totalExpense > 0 ? $totalGrossRevenue / $totalExpense : 0;

        // Net Revenue (Pemasukan Bersih setelah Fee & Biaya Iklan)
        $netRevenue = $totalRevenue - $totalExpense;

        // Kalkulasi AOV (Average Order Value)
        $aov = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Pelanggan Baru Bulan Ini
        $newCustomersQuery = EcommerceOrder::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->distinct('customer_email');

        if ($selectedPlatform !== 'all') {
            $newCustomersQuery->where('platform', $selectedPlatform);
        }

        $newCustomers = $newCustomersQuery->count('customer_email');

        // Pesanan Terbaru (Online Only)
        $recentOrdersQuery = EcommerceOrder::with('items.product')
            ->latest('order_date');

        if ($selectedPlatform !== 'all') {
            $recentOrdersQuery->where('platform', $selectedPlatform);
        }

        $recentOrders = $recentOrdersQuery->take(5)
            ->get()
            ->map(function ($order) {
                return (object)[
                    'id' => $order->id,
                    'reference' => '#' . $order->order_number,
                    'customer_name' => $order->customer_name,
                    'date' => $order->order_date,
                    'amount' => $order->total_amount,
                    'platform' => $order->platform ?: 'Online',
                    'status' => strtolower($order->status),
                    'route' => route('orders.show', $order->id),
                    'is_redeem' => false // Bisa di-expand jika e-commerce support redeem codes
                ];
            });

        // Produk Terlaris via Online (Bulan Ini)
        $topOnlineProducts = EcommerceOrderItem::with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($q) use ($currentMonth, $currentYear, $selectedPlatform) {
                $q->whereMonth('order_date', $currentMonth)
                  ->whereYear('order_date', $currentYear)
                  ->where('status', '!=', 'cancelled');
                
                if ($selectedPlatform !== 'all') {
                    $q->where('platform', $selectedPlatform);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // Data Grafik 30 Hari Terakhir
        $chartData = $this->getChartData($selectedPlatform, $platforms);

        return view('pages.digital-marketing-dashboard.index', compact(
            'totalOrders',
            'totalGrossRevenue',
            'totalRevenue',
            'totalFees',
            'totalExpense',
            'netRevenue',
            'roas',
            'aov',
            'newCustomers',
            'recentOrders',
            'topOnlineProducts',
            'chartData',
            'platforms',
            'selectedPlatform'
        ))->with('activePage', 'digital-marketing-dashboard');
    }

    private function getChartData($selectedPlatform, $allPlatforms)
    {
        $days = [];
        $datasets = [];

        // If 'all' is selected, we show multiple lines for comparison
        if ($selectedPlatform === 'all') {
            foreach ($allPlatforms as $platform) {
                $datasets[$platform] = [
                    'label' => $platform,
                    'data' => []
                ];
            }
            $datasets['Total'] = [
                'label' => 'Total Revenue',
                'data' => []
            ];
        } else {
            $datasets[$selectedPlatform] = [
                'label' => 'Revenue ' . $selectedPlatform,
                'data' => []
            ];
            $datasets['Orders'] = [
                'label' => 'Orders Count',
                'data' => []
            ];
        }

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d M');

            if ($selectedPlatform === 'all') {
                $totalDayRevenue = 0;
                foreach ($allPlatforms as $platform) {
                    $rev = EcommerceOrder::whereDate('order_date', $date->toDateString())
                        ->where('platform', $platform)
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount');
                    $datasets[$platform]['data'][] = $rev;
                    $totalDayRevenue += $rev;
                }
                $datasets['Total']['data'][] = $totalDayRevenue;
                
                // Add total daily expense for 'all' mode
                if (!isset($datasets['Expenses'])) {
                    $datasets['Expenses'] = [
                        'label' => 'Total Expenses',
                        'data' => []
                    ];
                }
                $datasets['Expenses']['data'][] = CashTransaction::whereDate('date', $date->toDateString())
                    ->where('type', 'out')
                    ->where(function ($q) {
                        $q->where('category', 'like', '%Iklan%')
                          ->orWhere('category', 'like', '%Marketing%')
                          ->orWhere('description', 'like', '%Ads%');
                    })
                    ->sum('amount');

            } else {
                $rev = EcommerceOrder::whereDate('order_date', $date->toDateString())
                    ->where('platform', $selectedPlatform)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount');
                $ord = EcommerceOrder::whereDate('order_date', $date->toDateString())
                    ->where('platform', $selectedPlatform)
                    ->where('status', '!=', 'cancelled')
                    ->count();
                
                $datasets[$selectedPlatform]['data'][] = $rev;
                $datasets['Orders']['data'][] = $ord;
            }
        }

        // Clean up datasets array to prevent indexed keys issue if needed, but array_values is already at end
        return [
            'labels' => $days,
            'datasets' => array_values($datasets),
            'isComparison' => ($selectedPlatform === 'all')
        ];
    }

    public function generateShopeeReport(Request $request)
    {
        $request->validate([
            'shopee_file' => 'required|file|max:10240',
        ], [
            'shopee_file.required' => 'File laporan wajib diunggah.',
            'shopee_file.max' => 'Ukuran file maksimal 10 MB.'
        ]);

        $file = $request->file('shopee_file');
        
        try {
            $spreadsheetIn = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheetIn->getActiveSheet();
            $data = $worksheet->toArray();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (count($data) < 2) {
            return redirect()->back()->with('error', 'Data kosong atau tidak memiliki header.');
        }

        $header = array_map('strtolower', array_map('trim', $data[0]));

        // Detect Columns
        $statusIdx = $this->findColumnIndex($header, ['status', 'keadaan', 'state']);
        $totalIdx = $this->findColumnIndex($header, ['total', 'harga', 'amount', 'jumlah pembayaran']);
        $productNameIdx = $this->findColumnIndex($header, ['nama produk', 'product name', 'produk']);
        $qtyIdx = $this->findColumnIndex($header, ['jumlah', 'qty', 'quantity']);
        $provinceIdx = $this->findColumnIndex($header, ['provinsi', 'province', 'state']);
        $cityIdx = $this->findColumnIndex($header, ['kota', 'kabupaten', 'city']);
        $addressIdx = $this->findColumnIndex($header, ['alamat', 'address']);
        $recipientIdx = $this->findColumnIndex($header, ['nama', 'pembeli', 'penerima', 'recipient']);
        $receiptIdx = $this->findColumnIndex($header, ['resi', 'tracking', 'awb']);
        $paymentMethodIdx = $this->findColumnIndex($header, ['metode pembayaran', 'payment method']);
        $orderDateIdx = $this->findColumnIndex($header, ['waktu pesanan dibuat', 'tanggal', 'order date', 'created_at']);
        $orderNoIdx = $this->findColumnIndex($header, ['no. pesanan', 'order', 'nomer']);
        
        // Data Aggregation Variables
        $summary = [
            'total_revenue' => 0,
            'total_orders' => 0,
            'status_selesai' => 0,
            'status_batal' => 0,
            'cod_revenue' => 0,
            'transfer_revenue' => 0,
        ];

        $productStats = []; // [product_name] => ['qty' => 0, 'revenue' => 0]
        $regionStats = []; // [province][city] => count
        $dailyOrders = []; // [date] => count
        $completedOrdersRaw = [];
        $completedShippingData = [];

        foreach ($data as $index => $row) {
            if ($index === 0 || empty(array_filter($row))) continue; // Skip header or empty rows

            $summary['total_orders']++;

            $rawStatus = $statusIdx !== false ? strtolower(trim($row[$statusIdx] ?? '')) : '';
            $isSelesai = str_contains($rawStatus, 'selesai') || str_contains($rawStatus, 'diterima') || str_contains($rawStatus, 'delivered');
            $isBatal = str_contains($rawStatus, 'batal') || str_contains($rawStatus, 'pengembalian') || str_contains($rawStatus, 'cancelled');

            if ($isSelesai) $summary['status_selesai']++;
            if ($isBatal) $summary['status_batal']++;

            $revenue = 0;
            if ($totalIdx !== false && isset($row[$totalIdx])) {
                $revenue = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $row[$totalIdx]));
            }

            if (!$isBatal) {
                $summary['total_revenue'] += $revenue;

                // Payment Method
                $paymentMethod = $paymentMethodIdx !== false ? strtolower(trim($row[$paymentMethodIdx] ?? '')) : '';
                if (str_contains($paymentMethod, 'cod') || str_contains($paymentMethod, 'bayar di tempat')) {
                    $summary['cod_revenue'] += $revenue;
                } else {
                    $summary['transfer_revenue'] += $revenue;
                }

                // Daily Orders
                if ($orderDateIdx !== false && isset($row[$orderDateIdx])) {
                    $dateStr = trim($row[$orderDateIdx]);
                    $dateOnly = substr($dateStr, 0, 10); // Extract simple YYYY-MM-DD or DD/MM/YYYY
                    if (!isset($dailyOrders[$dateOnly])) $dailyOrders[$dateOnly] = 0;
                    $dailyOrders[$dateOnly]++;
                }

                // Product Analysis
                $productName = $productNameIdx !== false ? trim($row[$productNameIdx] ?? 'Unknown Product') : 'Unknown Product';
                $qty = $qtyIdx !== false ? (int) ($row[$qtyIdx] ?? 1) : 1;
                
                if (!isset($productStats[$productName])) {
                    $productStats[$productName] = ['qty' => 0, 'revenue' => 0];
                }
                $productStats[$productName]['qty'] += $qty;
                // If a row has revenue, we attribute it. Realistically we might need unit price, but we use total row revenue for simplicity.
                $productStats[$productName]['revenue'] += $revenue;

                // Regional Analysis
                $province = $provinceIdx !== false ? trim($row[$provinceIdx] ?? 'Unknown') : 'Unknown';
                $city = $cityIdx !== false ? trim($row[$cityIdx] ?? 'Unknown') : 'Unknown';
                
                if ($province && $province !== 'Unknown') {
                    if (!isset($regionStats[$province])) $regionStats[$province] = [];
                    if (!isset($regionStats[$province][$city])) $regionStats[$province][$city] = 0;
                    $regionStats[$province][$city]++;
                }
            }

            if ($isSelesai) {
                // For Sheet: Filter Selesai Raw
                $completedOrdersRaw[] = $row;

                // For Sheet: Rekap Pengiriman
                $recipient = $recipientIdx !== false ? trim($row[$recipientIdx] ?? '') : '';
                $address = $addressIdx !== false ? trim($row[$addressIdx] ?? '') : '';
                $receipt = $receiptIdx !== false ? trim($row[$receiptIdx] ?? '') : '';
                $products = $productNameIdx !== false ? trim($row[$productNameIdx] ?? '') : '';
                
                $completedShippingData[] = [
                    $recipient,
                    $address,
                    $receipt,
                    $products
                ];
            }
        }

        // Sort Top Products
        uasort($productStats, function($a, $b) { return $b['revenue'] <=> $a['revenue']; });
        
        // --- Create Generated Spreadsheet ---
        $outSpreadsheet = new Spreadsheet();
        
        $this->styleHeader = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        // SHEET 0: Dashboard
        $sheetDash = $outSpreadsheet->getActiveSheet();
        $sheetDash->setTitle('Dashboard Ringkasan');
        
        $sheetDash->setCellValue('B2', 'DASHBOARD RINGKASAN PENJUALAN');
        $sheetDash->getStyle('B2')->getFont()->setBold(true)->setSize(16);
        
        $sheetDash->setCellValue('B4', 'Total Pendapatan');
        $sheetDash->setCellValue('C4', $summary['total_revenue']);
        $sheetDash->getStyle('C4')->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

        $sheetDash->setCellValue('B5', 'Total Order Diterima');
        $sheetDash->setCellValue('C5', $summary['total_orders']);

        $sheetDash->setCellValue('B6', 'Order Selesai');
        $sheetDash->setCellValue('C6', $summary['status_selesai']);

        $sheetDash->setCellValue('B7', 'Order Batal');
        $sheetDash->setCellValue('C7', $summary['status_batal']);

        $sheetDash->setCellValue('B8', 'Rata-rata Nilai Pesanan (AOV)');
        $aov = ($summary['status_selesai'] > 0) ? $summary['total_revenue'] / $summary['status_selesai'] : 0;
        $sheetDash->setCellValue('C8', $aov);
        $sheetDash->getStyle('C8')->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

        $sheetDash->setCellValue('B10', 'Omzet COD');
        $sheetDash->setCellValue('C10', $summary['cod_revenue']);
        $sheetDash->getStyle('C10')->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

        $sheetDash->setCellValue('B11', 'Omzet Transfer/Online');
        $sheetDash->setCellValue('C11', $summary['transfer_revenue']);
        $sheetDash->getStyle('C11')->getNumberFormat()->setFormatCode('"Rp "#,##0_-');

        // Top 5 Products on Dashboard
        $sheetDash->setCellValue('E4', 'TOP 5 PRODUK TERLARIS');
        $sheetDash->getStyle('E4')->getFont()->setBold(true);
        $sheetDash->setCellValue('E5', 'Produk')->setCellValue('F5', 'Qty')->setCellValue('G5', 'Revenue');
        $sheetDash->getStyle('E5:G5')->applyFromArray($this->styleHeader);
        
        $rowIdx = 6;
        $count = 0;
        foreach ($productStats as $name => $stat) {
            if ($count >= 5) break;
            $sheetDash->setCellValue('E'.$rowIdx, $name);
            $sheetDash->setCellValue('F'.$rowIdx, $stat['qty']);
            $sheetDash->setCellValue('G'.$rowIdx, $stat['revenue']);
            $sheetDash->getStyle('G'.$rowIdx)->getNumberFormat()->setFormatCode('"Rp "#,##0_-');
            $rowIdx++;
            $count++;
        }

        // Daily Chart Data
        $sheetDash->setCellValue('I4', 'GRAFIK HARIAN (Waktu Pesanan)');
        $sheetDash->getStyle('I4')->getFont()->setBold(true);
        $sheetDash->setCellValue('I5', 'Tanggal')->setCellValue('J5', 'Jumlah Order');
        $sheetDash->getStyle('I5:J5')->applyFromArray($this->styleHeader);
        
        $rowIdx = 6;
        ksort($dailyOrders);
        foreach ($dailyOrders as $date => $cnt) {
            $sheetDash->setCellValue('I'.$rowIdx, $date);
            $sheetDash->setCellValue('J'.$rowIdx, $cnt);
            $rowIdx++;
        }

        foreach (['B','C','E','F','G','I','J'] as $col) $sheetDash->getColumnDimension($col)->setAutoSize(true);

        // SHEET 1: Analisis Produk
        $sheetProd = $outSpreadsheet->createSheet();
        $sheetProd->setTitle('Analisis Produk');
        $sheetProd->setCellValue('A1', 'Nama Produk')->setCellValue('B1', 'Quantity Terjual')->setCellValue('C1', 'Total Revenue');
        $sheetProd->getStyle('A1:C1')->applyFromArray($this->styleHeader);
        $rowIdx = 2;
        foreach ($productStats as $name => $stat) {
            $sheetProd->setCellValue('A'.$rowIdx, $name);
            $sheetProd->setCellValue('B'.$rowIdx, $stat['qty']);
            $sheetProd->setCellValue('C'.$rowIdx, $stat['revenue']);
            $sheetProd->getStyle('C'.$rowIdx)->getNumberFormat()->setFormatCode('"Rp "#,##0_-');
            $rowIdx++;
        }
        $sheetProd->getColumnDimension('A')->setWidth(50);
        $sheetProd->getColumnDimension('C')->setAutoSize(true);

        // SHEET 2: Analisis Wilayah
        $sheetLoc = $outSpreadsheet->createSheet();
        $sheetLoc->setTitle('Analisis Wilayah');
        $sheetLoc->setCellValue('A1', 'Provinsi')->setCellValue('B1', 'Kota / Kabupaten')->setCellValue('C1', 'Jumlah Pesanan');
        $sheetLoc->getStyle('A1:C1')->applyFromArray($this->styleHeader);
        $rowIdx = 2;
        arsort($regionStats); // Simple sort
        foreach ($regionStats as $prov => $cities) {
            arsort($cities);
            foreach ($cities as $cty => $cnt) {
                $sheetLoc->setCellValue('A'.$rowIdx, $prov);
                $sheetLoc->setCellValue('B'.$rowIdx, $cty);
                $sheetLoc->setCellValue('C'.$rowIdx, $cnt);
                $rowIdx++;
            }
        }
        foreach (['A','B','C'] as $col) $sheetLoc->getColumnDimension($col)->setAutoSize(true);

        // SHEET 3: Order Selesai
        $sheetOk = $outSpreadsheet->createSheet();
        $sheetOk->setTitle('Order Selesai');
        // Copy Headers
        foreach ($data[0] as $colIdx => $colName) {
            $sheetOk->setCellValue([$colIdx + 1, 1], $colName);
        }
        $sheetOk->getStyle('A1:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($data[0])).'1')->applyFromArray($this->styleHeader);
        $rowIdx = 2;
        foreach ($completedOrdersRaw as $dataRow) {
            foreach ($dataRow as $colIdx => $val) {
                $sheetOk->setCellValueExplicit([$colIdx + 1, $rowIdx], $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $rowIdx++;
        }

        // SHEET 4: Rekap Pengiriman
        $sheetShip = $outSpreadsheet->createSheet();
        $sheetShip->setTitle('Rekap Pengiriman Label');
        $sheetShip->setCellValue('A1', 'Nama Penerima')->setCellValue('B1', 'Alamat Lengkap')->setCellValue('C1', 'No. Resi')->setCellValue('D1', 'Produk Dipesan');
        $sheetShip->getStyle('A1:D1')->applyFromArray($this->styleHeader);
        $rowIdx = 2;
        foreach ($completedShippingData as $shipData) {
            $sheetShip->setCellValue('A'.$rowIdx, $shipData[0]);
            $sheetShip->setCellValue('B'.$rowIdx, $shipData[1]);
            $sheetShip->setCellValueExplicit('C'.$rowIdx, $shipData[2], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetShip->setCellValue('D'.$rowIdx, $shipData[3]);
            $rowIdx++;
        }
        $sheetShip->getColumnDimension('A')->setAutoSize(true);
        $sheetShip->getColumnDimension('B')->setWidth(50);
        $sheetShip->getColumnDimension('C')->setAutoSize(true);
        $sheetShip->getColumnDimension('D')->setWidth(50);

        // Set focus back to first sheet
        $outSpreadsheet->setActiveSheetIndex(0);

        // Download Response
        $fileName = 'Laporan_Analisis_Shopee_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($outSpreadsheet);
        
        // Output to buffer and send it as response
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    private function findColumnIndex($header, $keywords)
    {
        if (!is_array($header)) return false;
        foreach ($header as $index => $colName) {
            foreach ($keywords as $keyword) {
                if (strpos($colName, strtolower($keyword)) !== false) {
                    return $index;
                }
            }
        }
        return false;
    }
}
