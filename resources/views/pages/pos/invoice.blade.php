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
            <h1 class="text-lg font-black tracking-tighter uppercase">GUPALA INDONESIA</h1>
            <p class="text-[10px] text-gray-500 uppercase leading-none">Jl. Antigravity No. 10, Jakarta Selatan</p>
            <p class="text-[10px] text-gray-500 leading-none">Telp: +62 812 3456 7890</p>
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
            <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Terima Kasih Atas Kunjungan Anda</p>
            <div class="flex justify-center opacity-50">
                <div class="w-16 h-16 border-2 border-gray-300 rounded overflow-hidden">
                    {{-- Minimal Barcode Placeholder --}}
                    <div class="h-full flex flex-col justify-around py-2 px-1">
                        @for($i=0; $i<8; $i++)
                        <div class="w-full bg-gray-500" style="height: {{ rand(1, 4) }}px"></div>
                        @endfor
                    </div>
                </div>
            </div>
            <p class="text-[8px] text-gray-400 uppercase tracking-tighter italic">Sistem POS Gupala ERP v1.0.0</p>
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
