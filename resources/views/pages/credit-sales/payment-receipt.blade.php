@php
    $gs = $global_settings ?? [];
    $branch = $creditSale->branch;

    $storeName = !empty($branch->invoice_name) ? $branch->invoice_name : ($gs['invoice_store_name'] ?? ($gs['company_name'] ?? 'GUPALA BATIK'));
    $tagline = !empty($branch->invoice_tagline) ? $branch->invoice_tagline : ($gs['invoice_tagline'] ?? '');
    $invoiceAddress = !empty($branch->address) ? $branch->address : ($gs['invoice_address'] ?? ($gs['address'] ?? 'Jl. Pembatikan, Wiradesa'));
    $invoicePhone = !empty($branch->phone) ? $branch->phone : ($gs['invoice_phone'] ?? ($gs['contact_phone'] ?? '+62 812 3456 7890'));
    $footerText = !empty($branch->invoice_footer) ? $branch->invoice_footer : ($gs['invoice_footer_text'] ?? 'Terima Kasih Atas Kunjungan Anda');
    $invoiceNotes = !empty($branch->invoice_notes) ? $branch->invoice_notes : ($gs['invoice_notes'] ?? '');

    $showLogo = ($gs['invoice_show_logo'] ?? 'true') === 'true';
    $companyLogo = $gs['company_logo'] ?? null;

    $remaining = $creditSale->total_amount - $creditSale->paid_amount;
    $paidPercent = $creditSale->total_amount > 0 ? round(($creditSale->paid_amount / $creditSale->total_amount) * 100) : 0;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Cicilan #{{ $creditSale->invoice_no }} - {{ $payment->payment_date->format('d/m/Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                margin: 0;
            }

            body {
                margin: 1cm;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: transparent;
        }

        .receipt {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
    </style>
</head>

<body class="bg-gray-100 py-10">
    <div class="receipt bg-white shadow-xl mx-auto rounded-sm border border-gray-200">
        {{-- Header --}}
        <div class="text-center mb-4 space-y-1">
            @if($showLogo && $companyLogo)
                <div class="flex justify-center mb-2">
                    <img src="{{ asset($companyLogo) }}" alt="{{ $storeName }}" class="h-20 object-contain">
                </div>
            @endif
            <h1 class="text-lg font-black tracking-tighter uppercase">{{ $storeName }}</h1>
            @if($tagline)
                <p class="text-[9px] text-gray-600 font-bold uppercase leading-none">{{ $tagline }}</p>
            @endif
            <p class="text-[10px] text-gray-800 font-bold uppercase leading-none">
                {{ $creditSale->branch ? 'Cabang ' . $creditSale->branch->name : 'Kantor Pusat' }}</p>
            <p class="text-[9px] text-gray-500 uppercase leading-none pt-1">
                {{ $creditSale->branch?->address ?? $invoiceAddress }}</p>
            <p class="text-[9px] text-gray-500 leading-none">Telp: {{ $creditSale->branch?->phone ?? $invoicePhone }}
            </p>
            <div class="border-b-[1px] border-dashed border-gray-300 pt-3"></div>
        </div>

        {{-- Title --}}
        <div class="text-center mb-3">
            <p class="text-[12px] font-black uppercase tracking-widest bg-gray-100 py-1.5 border-y border-gray-300">
                NOTA PEMBAYARAN CICILAN
            </p>
        </div>

        {{-- Info --}}
        <div class="text-[10px] mb-3 space-y-1">
            <div class="flex justify-between">
                <span>No. Invoice:</span>
                <span class="font-bold">#{{ $creditSale->invoice_no }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span class="font-bold truncate ml-2">{{ $creditSale->customer->name }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tgl Penjualan:</span>
                <span>{{ $creditSale->sale_date->format('d/m/Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Jatuh Tempo:</span>
                <span
                    class="font-bold {{ $creditSale->is_overdue && $creditSale->status !== 'paid' ? 'text-red-600' : '' }}">{{ $creditSale->due_date->format('d/m/Y') }}</span>
            </div>
            <div class="border-b-[1px] border-dashed border-gray-300 pt-2"></div>
        </div>

        {{-- Cicilan Info --}}
        <div class="text-[10px] mb-3 space-y-1">
            <div class="flex justify-between font-bold bg-gray-100 px-1 py-0.5">
                <span>CICILAN KE-{{ $installmentNumber }}</span>
                <span>{{ $payment->payment_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Metode Bayar:</span>
                <span class="font-bold">{{ $payment->payment_method === 'cash' ? 'TUNAI' : 'TRANSFER BANK' }}</span>
            </div>
            @if($payment->reference)
                <div class="flex justify-between">
                    <span>No. Referensi:</span>
                    <span>{{ $payment->reference }}</span>
                </div>
            @endif
            <div class="flex justify-between text-sm font-black border-y border-gray-900 py-1 mt-1">
                <span>JUMLAH BAYAR:</span>
                <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
            </div>
            @if($payment->notes)
                <div class="text-[9px] text-gray-500 italic pt-1">
                    Catatan: {{ $payment->notes }}
                </div>
            @endif
            <div class="border-b-[1px] border-dashed border-gray-300 pt-2"></div>
        </div>

        {{-- Detail Barang --}}
        <div class="text-[10px] mb-3 space-y-1.5">
            <p class="font-bold text-[9px] uppercase tracking-wider text-gray-500">Detail Barang:</p>
            @foreach($creditSale->items as $item)
                <div>
                    <div class="font-bold uppercase tracking-tight text-[10px]">{{ $item->product->name }}</div>
                    <div class="flex justify-between items-center text-[9px] text-gray-600">
                        <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
            <div class="border-b-[1px] border-dashed border-gray-300 pt-2"></div>
        </div>

        {{-- Rekap Pembayaran --}}
        <div class="text-[10px] space-y-1 mb-3">
            <p class="font-bold text-[9px] uppercase tracking-wider text-gray-500">Rekap Pembayaran:</p>
            <div class="flex justify-between">
                <span>Total Tagihan:</span>
                <span class="font-bold">Rp {{ number_format($creditSale->total_amount, 0, ',', '.') }}</span>
            </div>
            @foreach($allPayments as $idx => $p)
                <div
                    class="flex justify-between {{ $p->id === $payment->id ? 'font-bold bg-gray-100 px-1' : 'text-gray-600' }}">
                    <span>Cicilan #{{ $idx + 1 }} ({{ $p->payment_date->format('d/m/Y') }})</span>
                    <span>Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="border-t border-gray-400 pt-1 mt-1"></div>
            <div class="flex justify-between font-bold">
                <span>Total Terbayar:</span>
                <span>Rp {{ number_format($creditSale->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div
                class="flex justify-between font-black text-sm {{ $remaining <= 0 ? 'text-green-700' : 'text-red-700' }} border-y border-gray-900 py-1">
                <span>{{ $remaining <= 0 ? 'LUNAS' : 'SISA PEMBAYARAN:' }}</span>
                <span>Rp {{ number_format(max(0, $remaining), 0, ',', '.') }}</span>
            </div>

            {{-- Progress Bar (text-based for receipt) --}}
            <div class="mt-1">
                <div class="h-3 bg-gray-200 rounded-sm overflow-hidden border border-gray-300">
                    <div class="h-full {{ $paidPercent >= 100 ? 'bg-green-600' : 'bg-gray-700' }}"
                        style="width: {{ $paidPercent }}%"></div>
                </div>
                <div class="flex justify-between text-[8px] text-gray-500 mt-0.5">
                    <span>{{ $paidPercent }}% terbayar</span>
                    <span>{{ $allPayments->count() }}x cicilan</span>
                </div>
            </div>
        </div>

        {{-- Status --}}
        @if($remaining <= 0)
            <div class="text-center py-2 mb-3">
                <div class="border-2 border-green-700 inline-block px-6 py-1.5 rounded-sm">
                    <p class="text-[12px] font-black text-green-700 uppercase tracking-widest">✓ LUNAS</p>
                </div>
            </div>
        @else
            <div class="text-center py-1 mb-3">
                <p class="text-[9px] text-gray-500">Sisa pembayaran harap diselesaikan sebelum jatuh tempo</p>
                <p class="text-[10px] font-bold">{{ $creditSale->due_date->format('d/m/Y') }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="text-center mt-4 space-y-2">
            <div class="border-b-[1px] border-dashed border-gray-300 mb-3"></div>
            <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">{{ $footerText }}</p>
            @if($invoiceNotes)
                <p class="text-[8px] text-gray-400 italic leading-tight px-2">{{ $invoiceNotes }}</p>
            @endif
            <p class="text-[8px] text-gray-400">Kasir: {{ $payment->user->name ?? '-' }}</p>
            <p class="text-[8px] text-gray-300 uppercase tracking-tighter italic mt-2">{{ $storeName }}</p>
        </div>
    </div>

    {{-- Control Buttons --}}
    <div class="receipt mt-6 flex gap-2 no-print">
        <button onclick="window.print()"
            class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
            Cetak Nota Cicilan
        </button>
        <button onclick="window.close()"
            class="px-6 bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 rounded-xl border border-gray-700 transition-all">
            Tutup
        </button>
    </div>

    <script>
        window.addEventListener('load', () => {
            if (window.location.search.includes('print=true')) {
                window.print();
            }
        });
    </script>
</body>

</html>