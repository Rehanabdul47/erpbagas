<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Buku Kas</title>
    <style>
        body { background: white; color: black; font-family: 'Inter', sans-serif, Arial; margin: 0; padding: 20px;}
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 30px; }
        th { border-bottom: 2px solid #111827; border-top: 2px solid #111827; padding: 10px 5px; text-align: left; background: #f9fafb; font-weight: bold; text-transform: uppercase; }
        td { border-bottom: 1px dashed #d1d5db; padding: 8px 5px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-red { color: #dc2626; }
        .row-total { background: #f3f4f6; font-weight: bold; font-size: 13px; border-top: 2px solid #111827; border-bottom: 2px solid #111827; }
        .summary-box { border: 1px solid #111827; padding: 15px; margin-bottom: 20px; font-size: 13px; width: 400px; display: inline-block; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    @php
        $gs = \App\Models\Setting::pluck('value', 'key')->toArray();
        $storeName = $gs['company_name'] ?? 'GUPALA BATIK';
    @endphp

    <!-- Print Header -->
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #111827; padding-bottom: 15px;">
        <h1 style="font-size: 24px; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: 1px;">BUKU KAS UMUM</h1>
        <h3 style="font-size: 16px; font-weight: bold; margin-top: 5px;">{{ $storeName }}</h3>
        <p style="font-size: 14px; margin-top: 5px; color: #4b5563;">
            Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('1 F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
        </p>
    </div>

    <!-- Summary Box -->
    <div style="text-align: right; margin-bottom: 20px;">
        <div class="summary-box">
            <div class="summary-row">
                <span>Total Pemasukan:</span>
                <span class="text-emerald">+ Rp {{ number_format($stats['monthlyIn'], 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Total Pengeluaran:</span>
                <span class="text-red">- Rp {{ number_format($stats['monthlyOut'], 0, ',', '.') }}</span>
            </div>
            <div class="summary-row" style="border-top: 1px solid #e5e7eb; padding-top: 5px; margin-top: 5px; font-weight: bold;">
                <span>Total Kas Fisik:</span>
                <span>Rp {{ number_format($stats['totalBalance'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">No. Bukti</th>
                <th style="width: 20%;">Kategori / Akun</th>
                <th style="width: 25%;">Uraian</th>
                <th class="text-right" style="width: 15%;">Masuk (Rp)</th>
                <th class="text-right" style="width: 15%;">Keluar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $trx)
            <tr>
                <td>{{ $trx->date->format('d/m/Y') }}</td>
                <td>{{ $trx->reference ?: '-' }}</td>
                <td>{{ $trx->category }}</td>
                <td>{{ $trx->description }}</td>
                <td class="text-right text-emerald">
                    {{ $trx->type === 'in' ? number_format($trx->amount, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right text-red">
                    {{ $trx->type === 'out' ? number_format($trx->amount, 0, ',', '.') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; font-style: italic; color: #6b7280;">Tidak ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
            
            <tr class="row-total">
                <td colspan="4" class="text-right" style="padding: 10px 5px;">TOTAL PERIODE INI</td>
                <td class="text-right text-emerald" style="padding: 10px 5px;">{{ number_format($stats['monthlyIn'], 0, ',', '.') }}</td>
                <td class="text-right text-red" style="padding: 10px 5px;">{{ number_format($stats['monthlyOut'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures section -->
    <div style="margin-top: 60px; text-align: right; font-size: 14px; page-break-inside: avoid;">
        <p>Tanggal Cetak: {{ date('d F Y') }}</p>
        
        <div style="display: flex; justify-content: space-between; margin-top: 40px; text-align: center;">
            <div style="width: 30%">
                <p style="margin-bottom: 70px;"><strong>Dibuat Oleh,</strong></p>
                <p>_______________________</p>
                <p style="font-size: 12px; margin-top: 5px;">Staff Keuangan</p>
            </div>
            <div style="width: 30%">
                <p style="margin-bottom: 70px;"><strong>Disetujui Oleh,</strong></p>
                <p>_______________________</p>
                <p style="font-size: 12px; margin-top: 5px;">Direktur</p>
            </div>
        </div>
    </div>
</body>
</html>
