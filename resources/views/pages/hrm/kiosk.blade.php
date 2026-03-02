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
        #reader video { border-radius: 1.5rem; object-fit: cover; transform: scaleX(-1); }
        .hide-on-print { @media print { display: none !important; } }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-[#0b0f19] relative min-h-screen flex flex-col items-center justify-center text-white overflow-hidden" x-data="kioskData()">

    <!-- Decorative Background: Premium Mesh Gradient -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[60%] h-[60%] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[radial-gradient(circle_at_50%_50%,_rgba(15,23,42,0)_0%,_rgba(11,15,25,1)_100%)]"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>

    <!-- Header: Refined and High Contrast -->
    <div class="absolute top-8 w-full text-center z-10 px-4">
        <div class="flex items-center justify-center gap-3 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-white to-fuchsia-400 text-3xl md:text-5xl font-black mb-1 drop-shadow-sm tracking-tight">
            <i data-lucide="scan" class="w-10 h-10 md:w-12 md:h-12 text-indigo-400"></i> Presensi Kiosk
        </div>
        <div class="text-slate-400 font-semibold text-xs md:text-sm uppercase tracking-[0.2em] opacity-80">Smart Attendance System v2.0</div>
    </div>

    <!-- Main Container: Glassmorphism Ultra -->
    <div class="bg-slate-900/40 backdrop-blur-2xl px-8 py-10 md:py-14 md:px-14 rounded-[3rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] border border-white/10 max-w-xl w-full mx-4 relative z-10 text-center flex flex-col items-center">
        
        <!-- Clock & Date: High Visibility -->
        <div class="text-7xl md:text-[6rem] font-black leading-none mb-2 text-white tracking-tighter drop-shadow-2xl" 
             style="font-variant-numeric: tabular-nums; text-shadow: 0 0 30px rgba(99,102,241,0.2);" 
             x-text="timeString"></div>
        <div class="text-sm md:text-lg font-bold text-indigo-400 mb-8 md:mb-12 uppercase tracking-[0.3em]" x-text="dateString"></div>

        <!-- Camera Container: Fixed Visibility -->
        <div class="group relative mb-8 w-full max-w-[340px] mx-auto">
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-fuchsia-500 rounded-[2.2rem] blur opacity-25 group-hover:opacity-40 transition duration-700"></div>
            <div class="relative bg-black rounded-[2rem] border-2 border-white/10 shadow-2xl overflow-hidden">
                <div id="reader" class="w-full aspect-[4/3] bg-black"></div>
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/60 text-white px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-2 backdrop-blur-md border border-white/10 z-50">
                    <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-ping"></span>
                    <span x-text="cameraStatus">Initializing...</span>
                </div>
            </div>
        </div>

        <!-- Input Form: Premium Dark Field -->
        <form @submit.prevent="handleManualSubmit" class="w-full max-w-[420px] mb-8 space-y-4">
            <div class="relative w-full group">
                <div class="absolute inset-0 bg-indigo-500/20 rounded-2xl blur-xl opacity-0 group-focus-within:opacity-100 transition-opacity duration-500"></div>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-6 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-500 group-focus-within:text-indigo-400 transition-colors"></i>
                    <input type="text" x-model="nip" x-ref="nipInput" 
                        class="w-full bg-slate-950/50 border-2 border-white/5 focus:border-indigo-500/50 focus:bg-slate-950 focus:ring-8 focus:ring-indigo-500/5 rounded-2xl py-5 pl-[4.5rem] pr-6 text-3xl font-black text-center text-white placeholder-slate-700 outline-none transition-all block" 
                        placeholder="TAP QR / KETIK NIP" autocomplete="off" autofocus>
                </div>
            </div>

            <!-- Attendance Status Dropdown -->
            <div class="relative w-full group">
                <div class="absolute inset-0 bg-fuchsia-500/20 rounded-2xl blur-xl opacity-0 group-focus-within:opacity-100 transition-opacity duration-500"></div>
                <div class="relative">
                    <i data-lucide="calendar-check" class="absolute left-6 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-500 group-focus-within:text-fuchsia-400 transition-colors"></i>
                    <select x-model="status" 
                        class="w-full bg-slate-950/50 border-2 border-white/5 focus:border-fuchsia-500/50 focus:bg-slate-950 focus:ring-8 focus:ring-fuchsia-500/5 rounded-2xl py-4 pl-[4.5rem] pr-6 text-xl font-bold text-center text-white appearance-none outline-none transition-all block cursor-pointer">
                        <option value="present" class="bg-slate-900 text-white">🟢 HADIR (PRESENSI)</option>
                        <option value="leave" class="bg-slate-900 text-white">📅 CUTI</option>
                        <option value="permit" class="bg-slate-900 text-white">📝 IZIN</option>
                        <option value="sick" class="bg-slate-900 text-white">🏥 SAKIT</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-6 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 pointer-events-none"></i>
                </div>
            </div>
        </form>

        <!-- Live Activity Sync: Minimalist Dark -->
        <div class="w-full border-t border-white/5 pt-8">
            <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] mb-5 flex items-center justify-center gap-3">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span> LIVE ACTIVITY
            </div>
            <div class="flex flex-wrap justify-center gap-3 overflow-hidden px-2 max-h-16">
                <template x-for="(log, index) in recentLogs" :key="index">
                    <div class="flex items-center gap-3 bg-white/5 border border-white/10 py-2 px-4 rounded-2xl animate-[fadeIn_0.5s_ease-out]">
                        <div class="w-6 h-6 bg-gradient-to-tr from-indigo-500 to-indigo-700 text-white rounded-full flex items-center justify-center text-[10px] font-black shadow-lg" x-text="log.initials"></div>
                        <div class="flex flex-col">
                            <div class="text-xs font-bold text-slate-300 pointer-events-none" x-text="log.name"></div>
                            <div class="text-[9px] text-indigo-400/80 font-bold uppercase tracking-tighter" x-text="log.branch"></div>
                        </div>
                        <div class="text-[10px] text-slate-500 font-mono bg-black/20 px-1.5 py-0.5 rounded" x-text="log.time"></div>
                    </div>
                </template>
                <div x-show="recentLogs.length === 0" class="text-xs text-slate-600 font-medium italic">Menunggu aktivitas masuk...</div>
            </div>
        </div>
    </div>

    <!-- Home Button: Subtle Bottom -->
    <a href="{{ route('dashboard') }}" class="absolute bottom-8 flex items-center gap-2 px-6 py-3 bg-white/5 border border-white/10 rounded-full text-slate-400 font-bold text-xs uppercase tracking-widest hover:bg-white/10 hover:text-white hover:border-white/20 transition-all z-10 backdrop-blur-md">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Dashboard
    </a>

    <!-- Message Overlay: Immersive Status -->
    <div x-show="message.show" x-transition.opacity.duration.400ms 
        class="fixed inset-0 z-[200] flex flex-col items-center justify-center pointer-events-auto" 
        :class="message.type === 'success' ? 'bg-emerald-950/95 backdrop-blur-xl' : 'bg-red-950/95 backdrop-blur-xl'"
        style="display: none;">
        
        <div class="relative mb-10">
            <div class="absolute inset-0 rounded-full blur-3xl opacity-50" 
                 :class="message.type === 'success' ? 'bg-emerald-500' : 'bg-red-500'"></div>
            <div class="relative w-40 h-40 rounded-full flex items-center justify-center shadow-2xl scale-110" 
                 :class="message.type === 'success' ? 'bg-emerald-500' : 'bg-red-500'">
                <i data-lucide="check" x-show="message.type === 'success'" class="w-20 h-20 text-white"></i>
                <i data-lucide="x" x-show="message.type !== 'success'" class="w-20 h-20 text-white"></i>
            </div>
        </div>

        <h2 class="text-5xl md:text-7xl font-black mb-4 text-center px-6 tracking-tighter" 
            :class="message.type === 'success' ? 'text-emerald-400' : 'text-red-400'" 
            x-text="message.title"></h2>
        
        <p class="text-xl md:text-3xl text-white/70 font-medium mb-1 text-center px-10" x-text="message.desc"></p>
        <p class="text-indigo-400 font-bold text-sm uppercase mb-8" x-text="message.branch ? '📍 ' + message.branch : ''"></p>
        
        <div class="px-8 py-4 bg-white/10 rounded-3xl border border-white/10 backdrop-blur-md" x-show="message.time">
            <div class="text-[10px] font-black text-white/40 uppercase tracking-[0.4em] mb-1 text-center">Waktu Tercatat</div>
            <div class="text-4xl md:text-6xl font-black text-white font-mono" x-text="message.time"></div>
        </div>
    </div>

    <!-- Pose Countdown Overlay: Vibrant & Dynamic -->
    <div x-show="isCountdown" x-transition.opacity.duration.500ms 
        class="fixed inset-0 bg-gradient-to-br from-indigo-950 via-indigo-900 to-fuchsia-950 z-[250] flex flex-col items-center justify-center text-white p-6" 
        style="display: none;">
        
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
        
        <div class="relative mb-12">
            <div class="absolute inset-0 bg-white/20 rounded-full blur-[100px] animate-pulse"></div>
            <div class="text-[12rem] md:text-[20rem] font-black leading-none animate-[bounce_1s_infinite] drop-shadow-[0_20px_50px_rgba(255,255,255,0.3)]" 
                 x-text="countdown">5</div>
        </div>

        <div class="text-4xl md:text-6xl font-black mb-6 text-center tracking-tighter bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-white">
            Siap-siap Pose! ✨
        </div>
        
        <div class="text-xl md:text-3xl text-indigo-300/80 font-bold italic text-center max-w-2xl px-10 leading-relaxed">
            "Fotonya mundurin dikit biar cakepnya kelihatan"
        </div>

        <div class="mt-16 flex items-center gap-4 bg-white/10 px-8 py-4 rounded-[2rem] backdrop-blur-xl border border-white/20 shadow-2xl">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-indigo-950">
                <i data-lucide="camera" class="w-6 h-6"></i>
            </div>
            <span class="text-lg md:text-xl font-black uppercase tracking-widest text-white">Shutter Otomatis</span>
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
                status: 'present',
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

                    // Auto focus input on click anywhere except links and the status dropdown
                    document.addEventListener('click', (e) => {
                        const isLink = e.target.tagName === 'A' || e.target.closest('a');
                        const isStatusSelect = e.target.closest('[x-model="status"]') || e.target.tagName === 'SELECT';
                        
                        if(!isLink && !isStatusSelect && !this.message.show) {
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
                            body: JSON.stringify({ nip: this.nip, image: imageData, status: this.status })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.showMessage('success', data.type === 'in' ? 'Absen Masuk Berhasil' : 'Absen Keluar Berhasil', data.message, data.time || this.timeString, data.branch);
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

                showMessage(type, title, desc, time, branch = '') {
                    this.message = { show: true, type, title, desc: desc, time, branch };
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
                                branch: att.employee && att.employee.branch ? att.employee.branch.name : 'Pusat',
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
