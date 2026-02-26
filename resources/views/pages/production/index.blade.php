@extends('layouts.app')

@section('title', 'Produksi')
@section('breadcrumb', 'Produksi')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--success-400);display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--danger-400);display:flex;align-items:center;gap:8px;">
    <i data-lucide="x-circle" style="width:18px;height:18px"></i> {{ session('error') }}
</div>
@endif

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <h3 style="margin:0">Daftar Produksi</h3>
        <a href="{{ route('production.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Mulai Produksi</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Produk Final</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Pembuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productions as $prod)
                <tr>
                    <td>#{{ $prod->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($prod->date)->format('d M Y') }}</td>
                    <td style="font-weight:600">{{ $prod->product->name }}</td>
                    <td>{{ $prod->quantity }}</td>
                    <td>
                        @if($prod->status === 'draft')
                            <span class="badge badge-neutral">Draft</span>
                        @elseif($prod->status === 'processing')
                            <span class="badge badge-warning">⏳ Processing</span>
                        @elseif($prod->status === 'completed')
                            <span class="badge badge-success">✅ Selesai</span>
                        @elseif($prod->status === 'cancelled')
                            <span class="badge badge-danger">❌ Batal</span>
                        @endif
                    </td>
                    <td><span class="badge badge-neutral" style="font-size:10px">{{ $prod->user->name ?? 'Sistem' }}</span></td>
                    <td>
                        @if($prod->status === 'processing')
                            <div style="display:flex;gap:8px">
                                <form action="{{ route('production.update-status', $prod) }}" method="POST" onsubmit="return confirm('Tandai produksi selesai?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success btn-xs" title="Selesai"><i data-lucide="check" style="width:14px"></i></button>
                                </form>
                                <form action="{{ route('production.update-status', $prod) }}" method="POST" onsubmit="return confirm('Batalkan produksi dan kembalikan bahan baku?')">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="btn btn-danger btn-xs" title="Batal"><i data-lucide="x" style="width:14px"></i></button>
                                </form>
                            </div>
                        @else
                            <span style="color:var(--text-tertiary);font-size:12px">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada data produksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
