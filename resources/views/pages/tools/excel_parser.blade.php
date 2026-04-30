@extends('layouts.app')

@section('title', 'Excel Parser Debugger')
@section('breadcrumb', 'Tools / Excel Parser')

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- Header Section --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Excel Parser Debugger</h1>
            <p class="text-gray-400">Upload file Excel (.xlsx, .xls, .csv) untuk melihat output array data mentah.</p>
        </div>

        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3">
                <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($success))
            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <span class="font-medium">{{ $success }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Form Section --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl p-6 shadow-xl sticky top-24">
                    <form action="{{ route('tools.excel-parser.parse') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pilih File Excel</label>
                                <div class="relative group">
                                    <input type="file" name="excel_file" id="excel_file" required
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="bg-gray-900 border-2 border-dashed border-gray-700 group-hover:border-indigo-500/50 rounded-xl p-8 text-center transition-all">
                                        <div class="w-12 h-12 bg-indigo-500/10 text-indigo-400 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                            <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                        </div>
                                        <p id="file-name" class="text-sm text-gray-400 group-hover:text-gray-300">Tarik file ke sini atau klik untuk memilih</p>
                                        <p class="text-[10px] text-gray-600 mt-2 uppercase font-bold">XLSX, XLS, CSV (MAX 5MB)</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" 
                                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 group">
                                <span>Parsing File</span>
                                <i data-lucide="play" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </form>

                    <script>
                        document.getElementById('excel_file').addEventListener('change', function(e) {
                            const fileName = e.target.files[0]?.name || 'Tarik file ke sini atau klik untuk memilih';
                            document.getElementById('file-name').textContent = fileName;
                            document.getElementById('file-name').classList.add('text-indigo-400', 'font-medium');
                        });
                    </script>
                </div>
            </div>

            {{-- Results Section --}}
            <div class="lg:col-span-2">
                @if(isset($data))
                    <div class="space-y-6 animate-[fadeIn_0.5s_ease]">
                        {{-- Metadata Cards --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Rows</div>
                                <div class="text-xl font-bold text-white">{{ $metadata['row_count'] }}</div>
                            </div>
                            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Cols</div>
                                <div class="text-xl font-bold text-white">{{ $metadata['column_count'] }}</div>
                            </div>
                            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Sheets</div>
                                <div class="text-xl font-bold text-white">{{ $metadata['sheet_count'] }}</div>
                            </div>
                            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Size</div>
                                <div class="text-xl font-bold text-white">{{ round($metadata['size'] / 1024, 2) }} KB</div>
                            </div>
                        </div>

                        {{-- Debug Output --}}
                        <div class="bg-gray-900 border border-gray-700 rounded-2xl overflow-hidden shadow-2xl">
                            <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between bg-gray-800/50">
                                <div class="flex items-center gap-2">
                                    <div class="flex gap-1.5 mr-3">
                                        <div class="w-3 h-3 rounded-full bg-red-500/50"></div>
                                        <div class="w-3 h-3 rounded-full bg-amber-500/50"></div>
                                        <div class="w-3 h-3 rounded-full bg-emerald-500/50"></div>
                                    </div>
                                    <h3 class="font-bold text-gray-300 text-sm font-mono">parsed_data.json</h3>
                                </div>
                                <button onclick="copyData()" class="p-2 hover:bg-gray-700 rounded-lg text-gray-400 hover:text-white transition-colors">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <div class="p-6 overflow-auto max-h-[600px] scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">
                                <pre id="debug-output" class="text-xs font-mono text-indigo-300">{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>

                        {{-- Table Preview --}}
                        <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden shadow-xl">
                            <div class="px-6 py-4 border-b border-gray-700/50">
                                <h3 class="font-bold text-white">Pratinjau Tabel (10 Baris Pertama)</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-900/50 text-gray-500 text-[10px] uppercase font-bold tracking-widest border-b border-gray-700/50">
                                        <tr>
                                            @foreach(array_slice($data[1] ?? [], 0, 10) as $key => $val)
                                                <th class="px-4 py-3">{{ $key }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/50">
                                        @foreach(array_slice($data, 0, 10) as $row)
                                            <tr class="hover:bg-gray-700/20 transition-colors">
                                                @foreach(array_slice($row, 0, 10) as $cell)
                                                    <td class="px-4 py-3 text-gray-400">{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center p-12 border-2 border-dashed border-gray-800 rounded-3xl">
                        <div class="w-20 h-20 bg-gray-800/50 rounded-full flex items-center justify-center mb-6">
                            <i data-lucide="file-json" class="w-10 h-10 text-gray-700"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-600 mb-2">Belum ada data</h3>
                        <p class="text-gray-700 max-w-xs">Silakan unggah file Excel di sebelah kiri untuk melihat output debug.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyData() {
            const data = document.getElementById('debug-output').textContent;
            navigator.clipboard.writeText(data);
            alert('Data disalin ke clipboard!');
        }
    </script>
@endsection
