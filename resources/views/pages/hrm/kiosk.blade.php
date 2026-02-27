<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Kiosk</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS & Alpine JS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        /* Camera Styles Overrides */
        #reader { border: none !important; width: 100%; }
        #reader video { border-radius: 1rem; object-fit: cover; transform: scaleX(-1); }
        .hide-on-print { @media print { display: none !important; } }
    </style>
</head>
<body class="bg-slate-50 relative min-h-screen flex flex-col items-center justify-center text-slate-800 overflow-hidden" x-data="kioskData()">

    <!-- Decorative Background -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[80px]"></div>
        <div class="absolute -bottom-48 -right-24 w-[32rem] h-[32rem] bg-emerald-500/10 rounded-full blur-[80px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,_#ffffff_0%,_transparent_100%)]"></div>
    </div>

    <!-- Header -->
    <div class="absolute top-10 w-full text-center z-10 px-4">
        <div class="flex items-center justify-center gap-3 text-indigo-600 text-3xl md:text-4xl font-bold mb-2">
            <i data-lucide="scan-line" class="w-8 h-8 md:w-10 md:h-10"></i> Presensi
        </div>
        <div class="text-slate-500 font-medium text-sm md:text-base">Sistem Absensi Mandiri Karyawan</div>
    </div>

    <!-- Main Container -->
    <div class="bg-white/80 backdrop-blur-xl px-8 py-10 md:py-12 md:px-12 rounded-[2rem] shadow-[0_20px_40px_-10px_rgba(0,0,0,0.1)] border border-white max-w-xl w-full mx-4 relative z-10 text-center flex flex-col items-center">
        
        <!-- Clock & Date -->
        <div class="text-6xl md:text-[5rem] font-bold leading-none mb-2 text-slate-800 tracking-tighter" style="font-variant-numeric: tabular-nums;" x-text="timeString"></div>
        <div class="text-lg md:text-xl font-medium text-indigo-600 mb-8 md:mb-10 uppercase tracking-wider" x-text="dateString"></div>

        <!-- Camera Container -->
        <div class="w-full max-w-[320px] aspect-[4/3] bg-black rounded-2xl border-4 border-slate-100 shadow-lg relative overflow-hidden mb-6 flex flex-col">
            <div id="reader" class="w-full h-full flex-1"></div>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/50 text-white px-4 py-1.5 rounded-full text-xs flex items-center gap-2 backdrop-blur-sm whitespace-nowrap z-50">
                <i data-lucide="scan" class="w-3.5 h-3.5"></i> <span x-text="cameraStatus">Memulai Kamera...</span>
            </div>
        </div>

        <!-- Input Form -->
        <form @submit.prevent="handleManualSubmit" class="w-full max-w-[400px] mb-6 object-center text-center mx-auto" >
            <div class="relative w-full shadow-sm rounded-2xl group mx-auto text-center" >
                <i data-lucide="user" class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" x-model="nip" x-ref="nipInput" class="w-full bg-slate-50/50 border-2 border-slate-200 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 rounded-2xl py-5 pl-[4.5rem] pr-6 text-2xl font-bold text-center text-slate-800 placeholder-slate-300 outline-none transition-all block text-center" placeholder="Scan QR / Ketik NIP" autocomplete="off" autofocus>
            </div>
        </form>

        <!-- Instructions -->
        <div class="text-slate-500 flex items-center justify-center gap-2 text-sm md:text-base font-medium mb-8">
            <i data-lucide="info" class="w-5 h-5 text-slate-400"></i> Arahkan QR Code ke kamera atau ketik NIP
        </div>

        <!-- Live Activity Sync -->
        <div class="w-full border-t border-slate-100 pt-6">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center justify-center gap-2">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Sinkronisasi Aktif
            </div>
            <div class="flex flex-wrap justify-center gap-2 overflow-hidden px-2 max-h-12">
                <template x-for="(log, index) in recentLogs" :key="index">
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 py-1.5 px-3 rounded-full animate-[fadeIn_0.5s_ease-out]">
                        <div class="w-5 h-5 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-[10px] font-bold" x-text="log.initials"></div>
                        <div class="text-[11px] font-medium text-slate-600" x-text="log.name"></div>
                        <div class="text-[10px] text-slate-400 font-mono" x-text="log.time"></div>
                    </div>
                </template>
                <div x-show="recentLogs.length === 0" class="text-xs text-slate-300 italic">Belum ada aktivitas hari ini</div>
            </div>
        </div>
    </div>

    <!-- Home Button -->
    <a href="{{ route('dashboard') }}" class="absolute bottom-10 flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-full text-slate-500 font-medium shadow-sm hover:shadow-md hover:text-indigo-600 hover:border-indigo-300 hover:-translate-y-0.5 transition-all z-10">
        <i data-lucide="arrow-left" class="w-5 h-5"></i> Kembali ke Dashboard
    </a>

    <!-- Message Overlay -->
    <div x-show="message.show" x-transition.opacity.duration.300ms class="fixed inset-0 bg-white/95 backdrop-blur-sm z-[100] flex flex-col items-center justify-center pointer-events-auto" style="display: none;">
        <div class="w-32 h-32 rounded-full flex items-center justify-center mb-8 shadow-2xl transition-transform duration-500" :class="message.type === 'success' ? 'bg-emerald-500 shadow-emerald-500/30' : 'bg-red-500 shadow-red-500/30'">
            <i data-lucide="check" x-show="message.type === 'success'" class="w-16 h-16 text-white"></i>
            <i data-lucide="x" x-show="message.type !== 'success'" class="w-16 h-16 text-white"></i>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold mb-4 text-slate-800" :class="message.type === 'success' ? 'text-emerald-600' : 'text-red-500'" x-text="message.title"></h2>
        <p class="text-xl md:text-2xl text-slate-500 mb-3" x-text="message.desc"></p>
        <div class="text-2xl md:text-3xl font-bold text-slate-800 font-mono" x-show="message.time" x-text="message.time"></div>
    </div>

    <!-- Pose Countdown Overlay -->
    <div x-show="isCountdown" x-transition.opacity class="fixed inset-0 bg-indigo-600/90 backdrop-blur-md z-[120] flex flex-col items-center justify-center text-white" style="display: none;">
        <div class="text-8xl md:text-[12rem] font-black mb-10 animate-pulse text-white drop-shadow-2xl" x-text="countdown">5</div>
        <div class="text-2xl md:text-4xl font-bold mb-4 text-center px-6">Siap-siap Pose! ✨</div>
        <div class="text-xl md:text-2xl text-indigo-100 font-medium italic text-center px-10">"Fotonya mundurin dikit biar cakepnya kelihatan"</div>
        <div class="mt-12 flex items-center gap-3 bg-white/20 px-6 py-3 rounded-full backdrop-blur-sm border border-white/30">
            <i data-lucide="camera" class="w-6 h-6 animate-bounce"></i>
            <span class="text-lg font-bold">Kamera akan memotret otomatis</span>
        </div>
    </div>

    <!-- Script Block -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kioskData', () => ({
                timeString: '00:00:00',
                dateString: 'Memuat...',
                cameraStatus: 'Menginisialisasi...',
                nip: '',
                isProcessing: false,
                isCountdown: false,
                countdown: 5,
                message: { show: false, type: 'success', title: '', desc: '', time: '' },
                recentLogs: [],

                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                    this.initSync();
                    
                    // Init scanner
                    this.$nextTick(() => {
                        this.initScanner();
                        lucide.createIcons();
                    });

                    // Auto focus input on click anywhere except links
                    document.addEventListener('click', (e) => {
                        if(e.target.tagName !== 'A' && !this.message.show) {
                            this.$refs.nipInput.focus();
                        }
                    });
                },

                updateClock() {
                    const now = new Date();
                    this.timeString = now.toLocaleTimeString('id-ID', { hour12: false });
                    this.dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                },

                async initScanner() {
                    try {
                        const html5QrCode = new Html5Qrcode("reader");
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                        await html5QrCode.start(
                            { facingMode: "user" }, 
                            config,
                            (decodedText) => {
                                if (!this.isProcessing) {
                                    this.nip = decodedText;
                                    this.processAttendance(decodedText);
                                }
                            }
                        );
                        this.cameraStatus = "QR Scanner Aktif";
                    } catch (err) {
                        console.error("Error starting scanner:", err);
                        this.cameraStatus = "Kamera Tidak Terdeteksi";
                    }
                },

                handleManualSubmit() {
                    const cleanNip = String(this.nip).trim();
                    if (cleanNip) this.processAttendance(cleanNip);
                },

                async processAttendance(scannedNip) {
                    if (this.isProcessing) return;
                    this.isProcessing = true;
                    this.nip = scannedNip;

                    // Start Countdown Before Processing
                    this.isCountdown = true;
                    this.countdown = 5;
                    this.$nextTick(() => lucide.createIcons());

                    const timer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(timer);
                            this.performFinalAttendance();
                        }
                    }, 1000);
                },

                async performFinalAttendance() {
                    this.isCountdown = false;

                    // Capture Frame for proof exactly AFTER countdown
                    let imageData = null;
                    const videoElem = document.querySelector('#reader video');
                    if (videoElem) {
                        try {
                            const canvas = document.createElement('canvas');
                            canvas.width = videoElem.videoWidth;
                            canvas.height = videoElem.videoHeight;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(videoElem, 0, 0, canvas.width, canvas.height);
                            imageData = canvas.toDataURL('image/jpeg', 0.8);
                        } catch (e) { console.error('Gagal capture video', e); }
                    }
                    
                    try {
                        const response = await fetch('{{ route('hrm.kiosk.scan') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ nip: this.nip, image: imageData })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.showMessage('success', data.type === 'in' ? 'Absen Masuk Berhasil' : 'Absen Keluar Berhasil', data.message, data.time || this.timeString);
                        } else {
                            this.showMessage('error', 'Gagal', data.message || 'Terjadi kesalahan sistem', '');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Koneksi Terputus', 'Tidak dapat terhubung ke server', '');
                    } finally {
                        setTimeout(() => {
                            this.isProcessing = false;
                            this.nip = '';
                            this.$refs.nipInput.focus();
                        }, 5000); 
                    }
                },

                showMessage(type, title, desc, time) {
                    this.message = { show: true, type, title, desc: desc, time };
                    this.$nextTick(() => lucide.createIcons());
                    
                    setTimeout(() => {
                        this.message.show = false;
                        this.$nextTick(() => { this.$refs.nipInput.focus(); });
                    }, 5000);
                },

                initSync() {
                    @if(config('broadcasting.connections.pusher.key'))
                    if (typeof Pusher !== 'undefined') {
                        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                            forceTLS: true
                        });
                        
                        const channel = pusher.subscribe('attendance');
                        channel.bind('App\\Events\\AttendanceRecorded', (data) => {
                            const att = data.attendance;
                            const empName = att.employee ? att.employee.name : 'User';
                            
                            // Add to recent logs list
                            this.recentLogs.unshift({
                                name: empName.split(' ')[0],
                                initials: empName.substring(0, 2).toUpperCase(),
                                time: att.clock_in ? att.clock_in.substring(0, 5) : att.clock_out.substring(0, 5)
                            });
                            
                            // Keep only last 4
                            if (this.recentLogs.length > 4) this.recentLogs.pop();
                        });
                    }
                    @endif
                }
            }));
        });
    </script>
</body>
</html>
