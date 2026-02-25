@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('breadcrumb', 'Laporan Keuangan')

@section('content')
<div class="card" style="margin-bottom: 24px">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <h2 style="margin:0; font-size: 20px; font-weight: 600;">Laporan Periode: {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</h2>
        <form id="filter-form" style="display:flex;gap:12px;align-items:center">
            <a href="{{ route('finance.index', ['month' => $month]) }}" class="btn btn-ghost btn-sm">
                <i data-lucide="arrow-left" style="width:16px;height:16px;margin-right:6px"></i> Buku Kas
            </a>
            <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="document.getElementById('filter-form').submit()">
            <button class="btn btn-outline" type="button" onclick="window.print()" style="border:1px solid var(--border-color)">
                <i data-lucide="printer" style="width:16px;height:16px;margin-right:6px"></i> Cetak Laporan
            </button>
        </form>
    </div>
</div>

<div class="reports-container">
    <div class="tabs" style="display:flex;gap:4px;border-bottom:1px solid var(--border-color);margin-bottom:24px;overflow-x:auto;">
        <button class="tab-btn active" onclick="switchTab('laba-rugi', event)">Laba Rugi</button>
        <button class="tab-btn" onclick="switchTab('neraca', event)">Neraca</button>
        <button class="tab-btn" onclick="switchTab('arus-kas', event)">Arus Kas</button>
        <button class="tab-btn" onclick="switchTab('ekuitas', event)">Perubahan Ekuitas</button>
        <button class="tab-btn" onclick="switchTab('catatan', event)">Catatan</button>
    </div>

    <!-- 1. LABA RUGI -->
    <div id="tab-laba-rugi" class="tab-pane active card print-section">
        <div class="report-header" style="text-align:center;margin-bottom:24px">
            <h3 style="margin-bottom:4px;font-size:20px">Laporan Laba Rugi</h3>
            <p style="color:var(--text-secondary);font-size:14px">Untuk periode bulan {{ $startDate->translatedFormat('F Y') }}</p>
        </div>
        
        <table class="report-table">
            <tbody>
                <tr class="header-row"><td colspan="2"><strong>Pendapatan</strong></td></tr>
                @foreach($revenues as $rev)
                    @if($rev->period_balance != 0)
                    <tr>
                        <td style="padding-left:24px">{{ $rev->name }} ({{ $rev->code }})</td>
                        <td align="right">Rp {{ number_format($rev->period_balance, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                @if($revenues->sum('period_balance') == 0)
                    <tr><td style="padding-left:24px; color:var(--text-tertiary)">Tidak ada transaksi pendapatan</td><td align="right">Rp 0</td></tr>
                @endif
                <tr class="subtotal-row">
                    <td><strong>Total Pendapatan</strong></td>
                    <td align="right"><strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></td>
                </tr>

                <tr><td colspan="2"><br></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Beban (Biaya)</strong></td></tr>
                @foreach($expenses as $exp)
                    @if($exp->period_balance != 0)
                    <tr>
                        <td style="padding-left:24px">{{ $exp->name }} ({{ $exp->code }})</td>
                        <td align="right">Rp {{ number_format($exp->period_balance, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                @if($expenses->sum('period_balance') == 0)
                    <tr><td style="padding-left:24px; color:var(--text-tertiary)">Tidak ada transaksi beban</td><td align="right">Rp 0</td></tr>
                @endif
                <tr class="subtotal-row">
                    <td><strong>Total Beban</strong></td>
                    <td align="right"><strong>Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong></td>
                </tr>

                <tr><td colspan="2"><br></td></tr>

                <tr class="total-row {{ $netIncome >= 0 ? 'profit' : 'loss' }}">
                    <td><strong>Laba (Rugi) Bersih</strong></td>
                    <td align="right"><strong>Rp {{ number_format($netIncome, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 2. NERACA -->
    <div id="tab-neraca" class="tab-pane card print-section" style="display:none">
        <div class="report-header" style="text-align:center;margin-bottom:24px">
            <h3 style="margin-bottom:4px;font-size:20px">Neraca (Laporan Posisi Keuangan)</h3>
            <p style="color:var(--text-secondary);font-size:14px">Per {{ $endDate->translatedFormat('d F Y') }}</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="neraca-grid">
            <!-- Aset -->
            <div>
                <table class="report-table">
                    <tbody>
                        <tr class="header-row"><td colspan="2"><strong>Aset</strong></td></tr>
                        @foreach($assets as $asset)
                            @if($asset->period_balance != 0)
                            <tr>
                                <td style="padding-left:24px">{{ $asset->name }} ({{ $asset->code }})</td>
                                <td align="right">Rp {{ number_format($asset->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if($assets->sum('period_balance') == 0)
                            <tr><td style="padding-left:24px; color:var(--text-tertiary)">Tidak ada saldo aset</td><td align="right">Rp 0</td></tr>
                        @endif
                        
                        <!-- spacer lines to equalize height roughly if needed -->
                        <tr><td style="border:none;padding:12px"></td><td style="border:none;padding:12px"></td></tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Kewajiban & Ekuitas -->
            <div>
                <table class="report-table">
                    <tbody>
                        <tr class="header-row"><td colspan="2"><strong>Kewajiban (Liabilitas)</strong></td></tr>
                        @foreach($liabilities as $liab)
                            @if($liab->period_balance != 0)
                            <tr>
                                <td style="padding-left:24px">{{ $liab->name }} ({{ $liab->code }})</td>
                                <td align="right">Rp {{ number_format($liab->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr class="subtotal-row">
                            <td style="padding-left:24px;color:var(--text-secondary)"><em>Total Kewajiban</em></td>
                            <td align="right"><em>Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</em></td>
                        </tr>

                        <tr><td colspan="2" style="border:none;padding:4px"></td></tr>

                        <tr class="header-row"><td colspan="2"><strong>Ekuitas</strong></td></tr>
                        @foreach($equities as $eq)
                            @if($eq->period_balance != 0)
                            <tr>
                                <td style="padding-left:24px">{{ $eq->name }} ({{ $eq->code }})</td>
                                <td align="right">Rp {{ number_format($eq->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td style="padding-left:24px">Laba Ditahan</td>
                            <td align="right">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="subtotal-row">
                            <td style="padding-left:24px;color:var(--text-secondary)"><em>Total Ekuitas</em></td>
                            <td align="right"><em>Rp {{ number_format($totalEquity, 0, ',', '.') }}</em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px; margin-top:24px" class="neraca-grid">
            <table class="report-table">
                <tr class="total-row">
                    <td><strong>Total Aset</strong></td>
                    <td align="right"><strong>Rp {{ number_format($totalAssets, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
            <table class="report-table">
                <tr class="total-row">
                    <td><strong>Total Kewajiban & Ekuitas</strong></td>
                    <td align="right"><strong>Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. ARUS KAS -->
    <div id="tab-arus-kas" class="tab-pane card print-section" style="display:none">
        <div class="report-header" style="text-align:center;margin-bottom:24px">
            <h3 style="margin-bottom:4px;font-size:20px">Laporan Arus Kas</h3>
            <p style="color:var(--text-secondary);font-size:14px">Untuk periode bulan {{ $startDate->translatedFormat('F Y') }}</p>
        </div>

        <table class="report-table">
            <tbody>
                <tr>
                    <td style="font-size:15px"><strong>Saldo Kas Awal</strong> (Per 1 {{ $startDate->translatedFormat('F Y') }})</td>
                    <td align="right" style="font-size:15px"><strong>Rp {{ number_format($cashInitial, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" style="border:none;padding:8px"></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Arus Kas Masuk</strong></td></tr>
                <tr>
                    <td style="padding-left:24px">Penerimaan Kas (Pendapatan, dll)</td>
                    <td align="right">Rp {{ number_format($cashInflow, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" style="border:none;padding:8px"></td></tr>

                <tr class="header-row"><td colspan="2"><strong>Arus Kas Keluar</strong></td></tr>
                <tr>
                    <td style="padding-left:24px">Pengeluaran Kas (Beban, Prive, dll)</td>
                    <td align="right">(Rp {{ number_format($cashOutflow, 0, ',', '.') }})</td>
                </tr>

                <tr><td colspan="2"><br></td></tr>

                <tr class="subtotal-row">
                    <td>Kenaikan (Penurunan) Kas Bersih</td>
                    <td align="right">Rp {{ number_format($cashInflow - $cashOutflow, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2"><br></td></tr>
                
                <tr class="total-row">
                    <td><strong>Saldo Kas Akhir</strong> (Per {{ $endDate->translatedFormat('d F Y') }})</td>
                    <td align="right"><strong>Rp {{ number_format($cashEnding, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 4. PERUBAHAN EKUITAS -->
    <div id="tab-ekuitas" class="tab-pane card print-section" style="display:none">
        <div class="report-header" style="text-align:center;margin-bottom:24px">
            <h3 style="margin-bottom:4px;font-size:20px">Laporan Perubahan Ekuitas</h3>
            <p style="color:var(--text-secondary);font-size:14px">Untuk periode bulan {{ $startDate->translatedFormat('F Y') }}</p>
        </div>

        <table class="report-table">
            <tbody>
                <tr>
                    <td style="font-size:15px"><strong>Modal Awal</strong> (Per 1 {{ $startDate->translatedFormat('F Y') }})</td>
                    <td align="right" style="font-size:15px"><strong>Rp {{ number_format($beginningEquity, 0, ',', '.') }}</strong></td>
                </tr>
                <tr><td colspan="2" style="border:none;padding:8px"></td></tr>

                <tr>
                    <td style="padding-left:24px">Laba (Rugi) Bersih Periode Berjalan</td>
                    <td align="right">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding-left:24px">Prive (Penarikan Modal)</td>
                    <td align="right">(Rp {{ number_format($prive, 0, ',', '.') }})</td>
                </tr>

                <tr><td colspan="2"><br></td></tr>

                <tr class="subtotal-row">
                    <td>Penambahan (Pengurangan) Ekuitas</td>
                    <td align="right">Rp {{ number_format($netIncome - $prive, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2"><br></td></tr>

                <tr class="total-row">
                    <td><strong>Modal Akhir</strong> (Per {{ $endDate->translatedFormat('d F Y') }})</td>
                    <td align="right"><strong>Rp {{ number_format($endingEquity, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 5. CATATAN -->
    <div id="tab-catatan" class="tab-pane card print-section" style="display:none">
        <div class="report-header" style="text-align:center;margin-bottom:24px">
            <h3 style="margin-bottom:4px;font-size:20px">Catatan atas Laporan Keuangan</h3>
            <p style="color:var(--text-secondary);font-size:14px">Periode berakhir pada {{ $endDate->translatedFormat('d F Y') }}</p>
        </div>

        <div style="line-height:1.6; max-width: 800px; margin: 0 auto;">
            <h4 style="border-bottom:1px solid var(--border-color);padding-bottom:8px;margin-bottom:16px;margin-top:24px">1. Gambaran Umum</h4>
            <p style="margin-bottom:24px;color:var(--text-secondary)">
                Laporan keuangan ini menyajikan posisi keuangan (Neraca), kinerja keuangan (Laba Rugi), arus kas, 
                serta perubahan ekuitas perusahaan untuk periode pembukuan yang berakhir pada <strong>{{ $endDate->translatedFormat('d F Y') }}</strong>. 
                Laporan disusun menggunakan basis jurnal otomatis (Auto-Posted Journal) yang tersinkronisasi dengan seluruh transaksi kas dan operasional.
            </p>

            <h4 style="border-bottom:1px solid var(--border-color);padding-bottom:8px;margin-bottom:16px">2. Kebijakan Akuntansi</h4>
            <ul style="margin-bottom:24px;padding-left:20px;color:var(--text-secondary)">
                <li style="margin-bottom:8px"><strong>Kas dan Setara Kas:</strong> Saldo kas dilaporkan pada nilai nominalnya berdasarkan arus masuk dan arus keluar harian (kode akun 1001).</li>
                <li style="margin-bottom:8px"><strong>Pendapatan:</strong> Pendapatan diakui pada saat terjadi penambahan nilai tambah ekonomi ke dalam perusahaan.</li>
                <li style="margin-bottom:8px"><strong>Beban:</strong> Beban diakui ketika terdapat penggunaan manfaat sumber daya di bulan yang bersangkutan.</li>
            </ul>

            <h4 style="border-bottom:1px solid var(--border-color);padding-bottom:8px;margin-bottom:16px">3. Rincian Saldo Material</h4>
            <div style="background:var(--bg-tertiary);padding:16px;border-radius:8px">
                <p style="margin-bottom:12px;color:var(--text-secondary)"><strong>Kas (Aset Lancar):</strong> Saldo di akhir periode sebesar <strong>Rp {{ number_format($cashEnding, 0, ',', '.') }}</strong> yang merupakan representasi likuiditas saat ini.</p>
                <p style="margin-bottom:12px;color:var(--text-secondary)"><strong>Total Aset Berwujud:</strong> Disajikan senilai <strong>Rp {{ number_format($totalAssets, 0, ',', '.') }}</strong> yang terdiri dari akumulasi Kas dan Piutang (jika ada).</p>
                <p style="margin-bottom:12px;color:var(--text-secondary)"><strong>Kewajiban Pokok:</strong> Total kewajiban yang ditanggung tercatat sebesar <strong>Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</strong>.</p>
                <p style="margin-bottom:0;color:var(--text-secondary)"><strong>Kinerja (Laba Bersih):</strong> Pada laporan laba rugi, perusahaan mencatatkan <strong>{{ $netIncome >= 0 ? 'Laba' : 'Rugi' }}</strong> periode sebesar <strong>Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</strong>.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
.tab-btn {
    padding: 12px 24px;
    border: none;
    background: transparent;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}
.tab-btn:hover {
    color: var(--primary-600);
}
.tab-btn.active {
    color: var(--primary-600);
    border-bottom-color: var(--primary-600);
}
.report-table {
    width: 100%;
    border-collapse: collapse;
}
.report-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
}
.report-table .header-row td {
    background: rgba(0,0,0,0.02);
    font-size: 15px;
}
.report-table .subtotal-row td {
    border-top: 2px solid var(--border-color);
    border-bottom: none;
}
.report-table .total-row td {
    border-top: 2px solid var(--text-primary);
    border-bottom: 2px solid var(--text-primary);
    font-size: 16px;
}
.profit { color: var(--success-600); }
.loss { color: var(--danger-600); }

@media (max-width: 768px) {
    .neraca-grid {
        grid-template-columns: 1fr !important;
    }
}

@media print {
    body * { visibility: hidden; }
    .print-section, .print-section * { visibility: visible; }
    .print-section { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100%; 
        padding: 0; 
        box-shadow: none !important; 
        border: none !important;
    }
    .card { box-shadow: none !important; border: none !important; }
    .tabs, #filter-form, .sidebar, .header { display: none !important; }
}
</style>
<script>
function switchTab(tabId, event) {
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    
    if (event) {
        event.currentTarget.classList.add('active');
    } else {
        document.querySelector(`.tab-btn[onclick*="${tabId}"]`).classList.add('active');
    }
}
</script>
@endpush
