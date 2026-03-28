<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Keuangan</title>
    <style>
        body { background: white; color: black; font-family: 'Inter', sans-serif, Arial; margin: 0; padding: 20px;}
        .report-section { page-break-inside: avoid; margin-bottom: 40px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .report-header { background: #f9fafb; padding: 12px 20px; border-bottom: 2px solid #e5e7eb; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { border-bottom: 2px solid #e5e7eb; padding: 10px 20px; text-align: left; background: #f9fafb; }
        td { border-bottom: 1px solid #e5e7eb; padding: 8px 20px; }
        .row-total { background: #f3f4f6; font-weight: bold; }
        .row-sub { background: #f9fafb; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .signature-box { display: inline-block; width: 30%; text-align: center; margin-top: 50px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    @php
        $labels = [
            'neraca' => 'Neraca',
            'lr' => 'Laporan Laba Rugi',
            'ak' => 'Laporan Arus Kas',
            'pe' => 'Laporan Perubahan Ekuitas',
            'aset' => 'Aset',
            'kewajiban' => 'Kewajiban',
            'ekuitas' => 'Ekuitas',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban'
        ];
        
        $stdName = "Standar Tidak Diketahui";
        if (in_array($standard, ['psak', 'sak_umum'])) {
            $labels['neraca'] = 'Laporan Posisi Keuangan';
            $labels['lr'] = 'Laporan Laba Rugi Komprehensif';
            $stdName = $standard == 'psak' ? 'PSAK-IFRS (Standar Internasional)' : 'SAK Indonesia (Umum)';
        } elseif (in_array($standard, ['sak_privat', 'emkm'])) {
            $labels['neraca'] = 'Laporan Posisi Keuangan';
            $stdName = $standard == 'emkm' ? 'SAK EMKM (Entitas Mikro Kecil Menengah)' : 'SAK Entitas Privat';
        } elseif ($standard == 'sap') {
            $labels['neraca'] = 'Neraca Pemerintah';
            $labels['lr'] = 'Laporan Realisasi Anggaran / Operasional';
            $labels['ekuitas'] = 'Ekuitas Dana';
            $stdName = 'Standar Akuntansi Pemerintahan (SAP)';
        } elseif ($standard == 'syariah') {
            $labels['neraca'] = 'Laporan Posisi Keuangan Syariah';
            $stdName = 'SAK Syariah';
        }
        
        $gs = \App\Models\Setting::pluck('value', 'key')->toArray();
        $storeName = $gs['company_name'] ?? 'GUPALA BATIK';
    @endphp

    <!-- Print Header -->
    <div style="text-align: center; border-bottom: 2px solid #111827; padding-bottom: 15px; margin-bottom: 30px;">
        <h1 style="font-size: 24px; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: 1px;">LAPORAN KEUANGAN</h1>
        <h3 style="font-size: 16px; font-weight: bold; margin-top: 5px;">{{ $storeName }}</h3>
        <p style="font-size: 14px; margin-top: 5px; color: #4b5563;">
            Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('1 F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}<br>
            Disusun Berdasarkan: <strong>{{ $stdName }}</strong>
        </p>
    </div>

    <!-- 1. LABA RUGI -->
    @if(in_array('lr', $selectedReports) || in_array('all', $selectedReports))
    <div class="report-section">
        <div class="report-header">{{ $labels['lr'] }}</div>
        <table>
            <tbody>
                <tr class="row-sub">
                    <td colspan="2">{{ $labels['pendapatan'] }}</td>
                </tr>
                @forelse($revenues->where('period_balance', '!=', 0) as $rev)
                <tr>
                    <td>{{ $rev->name }} <span style="color:#6b7280; font-size:11px;">({{ $rev->code }})</span></td>
                    <td class="text-right">Rp {{ number_format($rev->period_balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="color: #6b7280; font-style: italic;">Tidak ada transaksi</td></tr>
                @endforelse
                <tr class="row-total">
                    <td class="text-right">Total {{ $labels['pendapatan'] }}</td>
                    <td class="text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>

                <tr class="row-sub">
                    <td colspan="2">{{ $labels['beban'] }}</td>
                </tr>
                @forelse($expenses->where('period_balance', '!=', 0) as $exp)
                <tr>
                    <td>{{ $exp->name }} <span style="color:#6b7280; font-size:11px;">({{ $exp->code }})</span></td>
                    <td class="text-right">Rp {{ number_format($exp->period_balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="color: #6b7280; font-style: italic;">Tidak ada transaksi</td></tr>
                @endforelse
                <tr class="row-total">
                    <td class="text-right">Total {{ $labels['beban'] }}</td>
                    <td class="text-right">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                </tr>

                <tr style="border-top: 2px solid #111827; font-size: 15px; font-weight: 900;">
                    <td class="text-right uppercase">Laba (Rugi) Bersih</td>
                    <td class="text-right">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- 2. NERACA -->
    @if(in_array('neraca', $selectedReports) || in_array('all', $selectedReports))
    <div class="report-section">
        <div class="report-header">{{ $labels['neraca'] }}</div>
        <table>
            <tbody>
                <!-- Aset -->
                <tr class="row-sub"><td colspan="2">{{ $labels['aset'] }}</td></tr>
                @forelse($assets->where('period_balance', '!=', 0) as $asset)
                <tr>
                    <td>{{ $asset->name }} <span style="color:#6b7280; font-size:11px;">({{ $asset->code }})</span></td>
                    <td class="text-right">Rp {{ number_format($asset->period_balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="color: #6b7280; font-style: italic;">Tidak ada transaksi</td></tr>
                @endforelse
                <tr class="row-total">
                    <td class="text-right">Total {{ $labels['aset'] }}</td>
                    <td class="text-right">Rp {{ number_format($totalAssets, 0, ',', '.') }}</td>
                </tr>

                <!-- Kewajiban -->
                <tr class="row-sub"><td colspan="2">{{ $labels['kewajiban'] }}</td></tr>
                @forelse($liabilities->where('period_balance', '!=', 0) as $liab)
                <tr>
                    <td>{{ $liab->name }} <span style="color:#6b7280; font-size:11px;">({{ $liab->code }})</span></td>
                    <td class="text-right">Rp {{ number_format($liab->period_balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="color: #6b7280; font-style: italic;">Tidak ada transaksi</td></tr>
                @endforelse
                <tr class="row-total">
                    <td class="text-right">Total {{ $labels['kewajiban'] }}</td>
                    <td class="text-right">Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</td>
                </tr>

                <!-- Ekuitas -->
                <tr class="row-sub"><td colspan="2">{{ $labels['ekuitas'] }}</td></tr>
                @foreach($equities->where('period_balance', '!=', 0) as $eq)
                <tr>
                    <td>{{ $eq->name }} <span style="color:#6b7280; font-size:11px;">({{ $eq->code }})</span></td>
                    <td class="text-right">Rp {{ number_format($eq->period_balance, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @if($standard !== 'emkm')
                <tr>
                    <td>Laba Ditahan ({{ $rePercentage * 100 }}%) <span style="color:#6b7280; font-size:11px;">(3-103)</span></td>
                    <td class="text-right">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Laba Tahun Berjalan ({{ (1 - $rePercentage) * 100 }}%) <span style="color:#6b7280; font-size:11px;">(-)</span></td>
                    <td class="text-right">Rp {{ number_format($currentEarnings, 0, ',', '.') }}</td>
                </tr>
                @else
                <!-- EMKM simplifies Retained Earnings into just Modal/Ekuitas -->
                <tr>
                    <td>Laba (Rugi) Berjalan</td>
                    <td class="text-right">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="row-total">
                    <td class="text-right">Total {{ $labels['ekuitas'] }}</td>
                    <td class="text-right">Rp {{ number_format($standard !== 'emkm' ? $totalEquity : ($totalEquityBase + $netIncome), 0, ',', '.') }}</td>
                </tr>

                <!-- Grand Total -->
                <tr style="border-top: 2px solid #111827; font-size: 15px; font-weight: 900;">
                    <td class="text-right uppercase">Total {{ $labels['kewajiban'] }} & {{ $labels['ekuitas'] }}</td>
                    <td class="text-right">Rp {{ number_format($totalLiabilities + ($standard !== 'emkm' ? $totalEquity : ($totalEquityBase + $netIncome)), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- 3. ARUS KAS -->
    @if((in_array('ak', $selectedReports) || in_array('all', $selectedReports)) && $standard !== 'emkm')
    <!-- EMKM does not strictly mandate Cash Flow, but we show if checked unless specifically disabled. 
         Wait, let's just show it if checked, standard doesn't forbid it -->
    <div class="report-section">
        <div class="report-header">{{ $labels['ak'] }}</div>
        <table>
            <tbody>
                <tr>
                    <td style="font-weight: bold;">Saldo Kas Awal <em style="font-weight: normal; color: #4b5563; font-size: 11px;">(Per 1 {{ \Carbon\Carbon::parse($startDate)->translatedFormat('F Y') }})</em></td>
                    <td class="text-right font-bold">Rp {{ number_format($cashInitial, 0, ',', '.') }}</td>
                </tr>
                
                <tr class="row-sub"><td colspan="2">Arus Kas Masuk</td></tr>
                <tr>
                    <td>Penerimaan Kas (Pendapatan, Piutang, Modal dll)</td>
                    <td class="text-right">+ Rp {{ number_format($cashInflow, 0, ',', '.') }}</td>
                </tr>

                <tr class="row-sub"><td colspan="2">Arus Kas Keluar</td></tr>
                <tr>
                    <td>Pengeluaran Kas (Beban, Prive, Aset dll)</td>
                    <td class="text-right">- Rp {{ number_format($cashOutflow, 0, ',', '.') }}</td>
                </tr>

                <tr class="row-total">
                    <td class="text-right">Kenaikan (Penurunan) Kas Bersih</td>
                    <td class="text-right">Rp {{ number_format($cashInflow - $cashOutflow, 0, ',', '.') }}</td>
                </tr>
                
                <tr style="border-top: 2px solid #111827; font-size: 15px; font-weight: 900;">
                    <td class="text-right uppercase">Saldo Kas Akhir <em style="font-weight: normal; color: #4b5563; font-size: 11px;">(Per {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }})</em></td>
                    <td class="text-right">Rp {{ number_format($cashEnding, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- 4. PERUBAHAN EKUITAS -->
    @if((in_array('pe', $selectedReports) || in_array('all', $selectedReports)) && $standard !== 'emkm')
    <div class="report-section">
        <div class="report-header">{{ $labels['pe'] }}</div>
        <table>
            <tbody>
                <tr>
                    <td style="font-weight: bold;">Modal/Ekuitas Awal <em style="font-weight: normal; color: #4b5563; font-size: 11px;">(Per 1 {{ \Carbon\Carbon::parse($startDate)->translatedFormat('F Y') }})</em></td>
                    <td class="text-right font-bold">Rp {{ number_format($beginningEquity, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Laba (Rugi) Bersih Periode Berjalan</td>
                    <td class="text-right">{{ $netIncome >= 0 ? '+' : '-' }} Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Prive (Penarikan Modal Pemilik)</td>
                    <td class="text-right">- Rp {{ number_format($prive, 0, ',', '.') }}</td>
                </tr>
                <tr class="row-total">
                    <td class="text-right">Penambahan (Pengurangan) Ekuitas</td>
                    <td class="text-right">Rp {{ number_format($netIncome - $prive, 0, ',', '.') }}</td>
                </tr>
                <tr style="border-top: 2px solid #111827; font-size: 15px; font-weight: 900;">
                    <td class="text-right uppercase">Modal/Ekuitas Akhir <em style="font-weight: normal; color: #4b5563; font-size: 11px;">(Per {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }})</em></td>
                    <td class="text-right">Rp {{ number_format($endingEquity, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- SAK Syariah Specifics Addons -->
    @if($standard === 'syariah')
    <div class="report-section">
        <div class="report-header">Laporan Sumber dan Penyaluran Dana Zakat</div>
        <table>
            <tbody>
                <tr>
                    <td>Penerimaan Dana Zakat</td>
                    <td class="text-right">Rp {{ number_format($zakatIn ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Penyaluran Dana Zakat</td>
                    <td class="text-right">- Rp {{ number_format($zakatOut ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="row-total">
                    <td class="text-right">Saldo Dana Zakat</td>
                    <td class="text-right">Rp {{ number_format(($zakatIn ?? 0) - ($zakatOut ?? 0), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

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
