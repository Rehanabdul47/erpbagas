@php
    $gs = $global_settings ?? [];
    $branch = $sale->branch;
    
    // Use branch setting if not empty, otherwise fallback to global setting
    $storeName = !empty($branch->invoice_name) ? $branch->invoice_name : ($gs['invoice_store_name'] ?? ($gs['company_name'] ?? 'GUPALA BATIK'));
    $tagline = !empty($branch->invoice_tagline) ? $branch->invoice_tagline : ($gs['invoice_tagline'] ?? '');
    $invoiceAddress = !empty($branch->address) ? $branch->address : ($gs['invoice_address'] ?? ($gs['address'] ?? 'Jl. Pembatikan, Wiradesa'));
    $invoicePhone = !empty($branch->phone) ? $branch->phone : ($gs['invoice_phone'] ?? ($gs['contact_phone'] ?? '+62 812 3456 7890'));
    $footerText = !empty($branch->invoice_footer) ? $branch->invoice_footer : ($gs['invoice_footer_text'] ?? 'Terima Kasih Atas Kunjungan Anda');
    $invoiceNotes = !empty($branch->invoice_notes) ? $branch->invoice_notes : ($gs['invoice_notes'] ?? '');
    
    $showLogo = ($gs['invoice_show_logo'] ?? 'true') === 'true';
    $companyLogo = $gs['company_logo'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 1cm; }
            .no-print { display: none; }
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
        <div class="text-center mb-6 space-y-1">
            @if($showLogo && $companyLogo)
            <div class="flex justify-center mb-2">
                <img src="{{ asset($companyLogo) }}" alt="{{ $storeName }}" class="h-20 object-contain">
            </div>
            @endif
            <h1 class="text-lg font-black tracking-tighter uppercase">{{ $storeName }}</h1>
            @if($tagline)
            <p class="text-[9px] text-gray-600 font-bold uppercase leading-none">{{ $tagline }}</p>
            @endif
            <p class="text-[10px] text-gray-800 font-bold uppercase leading-none">{{ $sale->branch ? 'Cabang ' . $sale->branch->name : 'Kantor Pusat' }}</p>
            <p class="text-[9px] text-gray-500 uppercase leading-none pt-1">{{ $sale->branch?->address ?? $invoiceAddress }}</p>
            <p class="text-[9px] text-gray-500 leading-none">Telp: {{ $sale->branch?->phone ?? $invoicePhone }}</p>
            <div class="border-b-[1px] border-dashed border-gray-300 pt-4"></div>
        </div>

        {{-- Info --}}
        <div class="text-[10px] mb-4 space-y-1">
            <div class="flex justify-between">
                <span>Invoice:</span>
                <span class="font-bold">#{{ $sale->invoice_no }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu:</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span class="truncate">{{ $sale->customer->name }}</span>
            </div>
            <div class="border-b-[1px] border-dashed border-gray-300 pt-2"></div>
        </div>

        {{-- Items --}}
        <div class="text-[11px] mb-4 space-y-2">
            @foreach($sale->items as $item)
            <div>
                <div class="font-bold uppercase tracking-tight">{{ $item->product->name }}</div>
                <div class="flex justify-between items-center text-[10px]">
                    <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
            <div class="border-b-[1px] border-dashed border-gray-300 pt-2"></div>
        </div>

        {{-- Summary --}}
        <div class="text-[11px] space-y-1">
            <div class="flex justify-between font-bold">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($sale->items->sum('subtotal'), 0, ',', '.') }}</span>
            </div>
            @if($sale->discount_amount > 0)
            <div class="flex justify-between text-emerald-600 italic">
                <span>Diskon:</span>
                <span>-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm font-black border-t border-gray-900 pt-1 mt-1">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 space-y-2">
            <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">{{ $footerText }}</p>
            @if($invoiceNotes)
            <p class="text-[8px] text-gray-400 italic leading-tight px-2">{{ $invoiceNotes }}</p>
            @endif
            <div class="flex justify-center opacity-50">
                <div class="w-16 h-16 border-2 border-gray-300 rounded overflow-hidden">
                    <div class="h-full flex flex-col justify-around py-2 px-1">
                        @for($i=0; $i<8; $i++)
                        <div class="w-full bg-gray-500" style="height: {{ rand(1, 4) }}px"></div>
                        @endfor
                    </div>
                </div>
            </div>
            <p class="text-[8px] text-gray-400 uppercase tracking-tighter italic">{{ $storeName }}</p>
        </div>
    </div>

    {{-- Control Buttons --}}
    <div class="receipt mt-6 flex gap-2 no-print">
        <button onclick="window.print()" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
            Cetak Struk
        </button>
        <button onclick="window.close()" class="px-6 bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 rounded-xl border border-gray-700 transition-all">
            Tutup
        </button>
    </div>

    <script>
        // Auto print on load if requested
        window.addEventListener('load', () => {
            if (window.location.search.includes('print=true')) {
                window.print();
            }
        });
    </script>
</body>
</html>
