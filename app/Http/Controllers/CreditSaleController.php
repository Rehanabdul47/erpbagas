<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\CreditSaleItem;
use App\Models\CreditSalePayment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use App\Traits\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreditSaleController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $branchId = $request->input('branch_id', 'all');
        $branches = $this->getBranchesForFilter();

        if (!$this->isExecutive()) {
            $branchId = auth()->user()->branch_id ?? 1;
        }

        // Credit Sales
        $creditSalesQuery = CreditSale::with(['customer', 'branch', 'user', 'items.product', 'payments'])
            ->latest();
        $this->scopeByBranch($creditSalesQuery, $branchId);
        $creditSales = $creditSalesQuery->get();

        // Auto-update overdue status
        foreach ($creditSales as $cs) {
            if ($cs->status !== 'paid' && Carbon::now()->gt($cs->due_date) && $cs->status !== 'overdue') {
                $cs->update(['status' => 'overdue']);
            }
        }

        // Customers for dropdown
        $customersQuery = Customer::where('status', 'active');
        $this->scopeByBranch($customersQuery, $branchId);
        $customers = $customersQuery->get();

        // Products for dropdown
        $productsQuery = Product::where('stock', '>', 0)->with('category');
        $this->scopeByBranch($productsQuery, $branchId);
        $products = $productsQuery->get();

        // All payments
        $paymentsQuery = CreditSalePayment::with(['creditSale.customer', 'user'])
            ->latest();
        // scope payments through creditSale relationship
        if (!$this->isExecutive()) {
            $userBranchId = auth()->user()->branch_id;
            if ($userBranchId) {
                $paymentsQuery->whereHas('creditSale', function ($q) use ($userBranchId) {
                    $q->where('branch_id', $userBranchId);
                });
            }
        } elseif ($branchId && $branchId !== 'all') {
            $paymentsQuery->whereHas('creditSale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $payments = $paymentsQuery->limit(50)->get();

        // Stats
        $statsBaseQuery = CreditSale::query();
        $this->scopeByBranch($statsBaseQuery, $branchId);

        $totalOutstanding = (clone $statsBaseQuery)->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum(DB::raw('total_amount - paid_amount'));

        $totalOverdue = (clone $statsBaseQuery)->where('status', 'overdue')
            ->sum(DB::raw('total_amount - paid_amount'));

        $totalThisMonth = (clone $statsBaseQuery)->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $totalCreditSales = (clone $statsBaseQuery)->count();

        $stats = [
            'totalOutstanding' => $totalOutstanding,
            'totalOverdue' => $totalOverdue,
            'totalThisMonth' => $totalThisMonth,
            'totalCreditSales' => $totalCreditSales,
        ];

        // Customer summary (piutang per customer)
        $customerSummaryQuery = Customer::withCount(['creditSales as credit_sales_count'])
            ->addSelect([
                'total_outstanding' => CreditSale::selectRaw('COALESCE(SUM(total_amount - paid_amount), 0)')
                    ->whereColumn('customer_id', 'customers.id')
                    ->whereIn('status', ['unpaid', 'partial', 'overdue']),
                'total_credit' => CreditSale::selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('customer_id', 'customers.id'),
            ])
            ->has('creditSales');
        $this->scopeByBranch($customerSummaryQuery, $branchId);
        $customerSummary = $customerSummaryQuery->get();

        return view('pages.credit-sales.index', compact(
            'creditSales', 'customers', 'products', 'payments',
            'stats', 'branches', 'branchId', 'customerSummary'
        ))->with('activePage', 'credit-sales');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_term_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $invoiceNo = 'CS-' . strtoupper(uniqid());
                $saleDate = Carbon::now();
                $dueDate = $saleDate->copy()->addDays((int) $validated['payment_term_days']);
                $totalAmount = 0;
                $totalCogs = 0;

                // 1. Create Credit Sale
                $creditSale = CreditSale::create([
                    'invoice_no' => $invoiceNo,
                    'customer_id' => $validated['customer_id'],
                    'branch_id' => Auth::user()->branch_id ?? null,
                    'sale_date' => $saleDate,
                    'due_date' => $dueDate,
                    'total_amount' => 0,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                    'payment_term_days' => $validated['payment_term_days'],
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                // 2. Process Items
                foreach ($validated['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$product->name}");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $totalAmount += $subtotal;
                    $totalCogs += ($product->cogs * $item['quantity']);

                    // Create item
                    CreditSaleItem::create([
                        'credit_sale_id' => $creditSale->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    // Decrement stock
                    $product->decrement('stock', $item['quantity']);

                    // Log stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'reference' => "Penjualan Tempo: {$invoiceNo}",
                        'method' => 'Credit Sale',
                    ]);
                }

                // 3. Update total
                $creditSale->update(['total_amount' => $totalAmount]);

                // 4. Journal Entry: Piutang Dagang
                $piutangAccount = Account::firstOrCreate(
                    ['code' => '1-103'],
                    [
                        'name' => 'Piutang Dagang',
                        'type' => 'asset',
                        'description' => 'Piutang dari penjualan tempo/kredit',
                        'level' => 2,
                        'is_header' => false,
                        'is_control' => false,
                        'is_active' => true,
                        'normal_balance' => 'debit',
                    ]
                );

                $pendapatanAccount = Account::where('code', '4-101')->first();
                $hppAccount = Account::where('code', '5-101')->first();
                $persediaanAccount = Account::where('code', '1-104')->first();

                if ($piutangAccount && $pendapatanAccount && $hppAccount && $persediaanAccount) {
                    $journalEntry = JournalEntry::create([
                        'branch_id' => Auth::user()->branch_id ?? 1,
                        'date' => $saleDate,
                        'reference' => $invoiceNo,
                        'description' => "Penjualan Tempo #{$invoiceNo} - " . Customer::find($validated['customer_id'])->name,
                    ]);

                    $journalEntry->items()->createMany([
                        // Debit: Piutang Dagang
                        [
                            'account_id' => $piutangAccount->id,
                            'debit' => $totalAmount,
                            'credit' => 0,
                        ],
                        // Credit: Pendapatan Penjualan
                        [
                            'account_id' => $pendapatanAccount->id,
                            'debit' => 0,
                            'credit' => $totalAmount,
                        ],
                        // Debit: HPP
                        [
                            'account_id' => $hppAccount->id,
                            'debit' => $totalCogs,
                            'credit' => 0,
                        ],
                        // Credit: Persediaan Barang
                        [
                            'account_id' => $persediaanAccount->id,
                            'debit' => 0,
                            'credit' => $totalCogs,
                        ],
                    ]);
                }

                return redirect()->route('credit-sales.index')
                    ->with('success', "Penjualan tempo #{$invoiceNo} berhasil dibuat. Jatuh tempo: {$dueDate->format('d M Y')}");
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(CreditSale $creditSale)
    {
        $creditSale->load(['customer', 'branch', 'user', 'items.product', 'payments.user']);
        return response()->json($creditSale);
    }

    public function storePayment(Request $request, CreditSale $creditSale)
    {
        $remaining = $creditSale->total_amount - $creditSale->paid_amount;

        $validated = $request->validate([
            'amount' => "required|numeric|min:1|max:{$remaining}",
            'payment_method' => 'required|string|in:cash,transfer',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            return DB::transaction(function () use ($validated, $creditSale) {
                // 1. Create Payment Record
                CreditSalePayment::create([
                    'credit_sale_id' => $creditSale->id,
                    'payment_date' => $validated['payment_date'],
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'reference' => $validated['reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                // 2. Update paid_amount
                $newPaidAmount = $creditSale->paid_amount + $validated['amount'];
                $creditSale->update(['paid_amount' => $newPaidAmount]);

                // 3. Refresh status
                $creditSale->refreshStatus();

                // 4. Record Cash Transaction
                CashTransaction::create([
                    'date' => $validated['payment_date'],
                    'type' => 'in',
                    'category' => 'Pembayaran Piutang',
                    'amount' => $validated['amount'],
                    'description' => "Pembayaran piutang #{$creditSale->invoice_no} - {$creditSale->customer->name}",
                    'reference' => $creditSale->invoice_no,
                    'user_id' => Auth::id(),
                ]);

                // 5. Journal Entry: Kas masuk, Piutang berkurang
                $kasAccount = $validated['payment_method'] === 'transfer'
                    ? Account::where('code', '1-102')->first() // Bank
                    : Account::where('code', '1-101')->first(); // Kas

                $piutangAccount = Account::where('code', '1-103')->first();

                if ($kasAccount && $piutangAccount) {
                    $journalEntry = JournalEntry::create([
                        'branch_id' => Auth::user()->branch_id ?? 1,
                        'date' => Carbon::parse($validated['payment_date']),
                        'reference' => $creditSale->invoice_no,
                        'description' => "Pembayaran Piutang #{$creditSale->invoice_no} - {$creditSale->customer->name}",
                    ]);

                    $journalEntry->items()->createMany([
                        // Debit: Kas / Bank
                        [
                            'account_id' => $kasAccount->id,
                            'debit' => $validated['amount'],
                            'credit' => 0,
                        ],
                        // Credit: Piutang Dagang
                        [
                            'account_id' => $piutangAccount->id,
                            'debit' => 0,
                            'credit' => $validated['amount'],
                        ],
                    ]);
                }

                $statusMsg = $creditSale->status === 'paid'
                    ? 'Piutang telah LUNAS!'
                    : 'Pembayaran berhasil dicatat.';

                return redirect()->route('credit-sales.index')
                    ->with('success', "Pembayaran Rp " . number_format($validated['amount'], 0, ',', '.') . " untuk #{$creditSale->invoice_no} berhasil. {$statusMsg}");
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getProducts(Request $request)
    {
        $branchId = $request->query('branch_id', null);

        $query = Product::where('stock', '>', 0)
            ->with('category')
            ->select('id', 'name', 'sku', 'price', 'stock', 'category_id');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->get());
    }

    public function paymentReceipt(CreditSale $creditSale, CreditSalePayment $payment)
    {
        $creditSale->load(['customer', 'branch', 'user', 'items.product']);
        $payment->load('user');

        $allPayments = $creditSale->payments()->orderBy('payment_date')->orderBy('id')->get();
        $installmentNumber = $allPayments->search(fn($p) => $p->id === $payment->id) + 1;

        $global_settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('pages.credit-sales.payment-receipt', compact(
            'creditSale', 'payment', 'allPayments', 'installmentNumber', 'global_settings'
        ));
    }
}
