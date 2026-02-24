<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --primary-500: #4f46e5;
            --primary-600: #4338ca;
            --success-500: #22c55e;
            --success-600: #16a34a;
            --danger-500: #ef4444;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --bg-body: #f1f5f9;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            background-image: radial-gradient(circle at 50% 0%, #ffffff 0%, #f1f5f9 100%);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-primary);
        }

        .header {
            position: absolute;
            top: 40px;
            text-align: center;
            width: 100%;
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-600);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 16px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .kiosk-container {
            background: white;
            padding: 60px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            max-width: 600px;
            position: relative;
            z-index: 10;
        }

        .time-display {
            font-size: 84px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
            letter-spacing: -2px;
        }

        .date-display {
            font-size: 20px;
            font-weight: 500;
            color: var(--primary-600);
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-group {
            position: relative;
            max-width: 400px;
            margin: 0 auto 30px;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .nip-input {
            width: 100%;
            padding: 24px 20px 24px 54px;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            outline: none;
            transition: all 0.3s;
            text-align: center;
            font-family: inherit;
            box-sizing: border-box;
            background: #f8fafc;
        }

        .nip-input:focus {
            border-color: var(--primary-500);
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .nip-input::placeholder {
            color: #cbd5e1;
            font-weight: 400;
        }

        .instructions {
            font-size: 16px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .btn-home {
            position: absolute;
            bottom: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 99px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .btn-home:hover {
            color: var(--primary-600);
            border-color: var(--primary-300);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        }

        /* Message Overlay */
        .message-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.95);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .message-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .message-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            transform: scale(0.5);
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .message-overlay.active .message-icon {
            transform: scale(1);
        }

        .message-icon.success {
            background: var(--success-500);
            color: white;
            box-shadow: 0 0 0 20px rgba(34, 197, 94, 0.1);
        }

        .message-icon.error {
            background: var(--danger-500);
            color: white;
            box-shadow: 0 0 0 20px rgba(239, 68, 68, 0.1);
        }

        .message-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .message-desc {
            font-size: 20px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .message-time {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .bg-shapes {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.5;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: rgba(79, 70, 229, 0.15);
            top: -100px;
            left: -100px;
        }

        .shape-2 {
            width: 500px;
            height: 500px;
            background: rgba(34, 197, 94, 0.1);
            bottom: -200px;
            right: -100px;
        }

        /* Camera Styles */
        .camera-container {
            width: 100%;
            max-width: 320px;
            margin: 0 auto 24px;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4/3;
            position: relative;
            border: 4px solid #f1f5f9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror effect */
        }

        .camera-status {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #reader {
            width: 100%;
            border: none !important;
        }

        #reader video {
            border-radius: 12px;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="header">
        <div class="logo">
            <i data-lucide="scan-line" style="width:36px;height:36px"></i>
            Presensi
        </div>
        <div class="subtitle">Sistem Absensi Mandiri Karyawan</div>
    </div>

    <div class="kiosk-container">
        <div class="time-display" id="clock">00:00:00</div>
        <div class="date-display" id="date">Memuat Tanggal...</div>

        <div class="camera-container" style="background: #000;">
            <div id="reader"></div>
            <div class="camera-status" style="z-index: 100;">
                <i data-lucide="scan" style="width:14px;height:14px"></i>
                <span id="cameraStatusText">QR Scanner Active</span>
            </div>
        </div>

        <form id="attendanceFrame" onsubmit="handleManualSubmit(event)">
            <div class="input-group">
                <i data-lucide="user" class="input-icon" style="width:24px;height:24px"></i>
                <input type="text" id="nipInput" class="nip-input" placeholder="Scan QR / Ketik NIP" autocomplete="off" autofocus>
            </div>
        </form>

        <div class="instructions">
            <i data-lucide="info" style="width:18px;height:18px"></i>
            Arahkan QR Code ke kamera atau ketik NIP manual
        </div>
    </div>

    <a href="{{ route('dashboard') }}" class="btn-home">
        <i data-lucide="arrow-left" style="width:18px;height:18px"></i> Kembali ke Dashboard
    </a>

    <!-- Message Overlay -->
    <div id="messageOverlay" class="message-overlay">
        <div id="msgIcon" class="message-icon success">
            <i data-lucide="check" style="width:64px;height:64px" id="msgIconInner"></i>
        </div>
        <div id="msgTitle" class="message-title">Berhasil!</div>
        <div id="msgDesc" class="message-desc">Absen Masuk: Budi Santoso</div>
        <div id="msgTime" class="message-time">08:00:12</div>
    </div>

    <script>
        // Init Lucide Icons
        lucide.createIcons();

        // Clock Script
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('id-ID', options);

            document.getElementById('clock').textContent = timeString;
            document.getElementById('date').textContent = dateString;
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Keep focus on input
        const nipInput = document.getElementById('nipInput');
        document.addEventListener('click', () => {
            // Don't steal focus if clicking on link
            if(document.activeElement.tagName !== 'A') {
                nipInput.focus();
            }
        });

        // Scanner & Camera Logic
        let html5QrCode = null;
        let isProcessing = false;

        async function initScanner() {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            try {
                await html5QrCode.start(
                    { facingMode: "user" }, 
                    config,
                    (decodedText) => {
                        if (!isProcessing) {
                            processAttendance(decodedText);
                        }
                    }
                );
            } catch (err) {
                console.error("Error starting scanner:", err);
                document.getElementById('cameraStatusText').textContent = "Scanner Error / No Camera";
            }
        }

        initScanner();

        function handleManualSubmit(e) {
            e.preventDefault();
            const nip = nipInput.value.trim();
            if (nip) processAttendance(nip);
        }

        async function processAttendance(nip) {
            if (isProcessing) return;
            isProcessing = true;

            // Visual feedback
            nipInput.value = nip;
            nipInput.style.borderColor = 'var(--primary-500)';

            // Capture Frame for proof (getting from the video element used by the library)
            let imageData = null;
            const videoElem = document.querySelector('#reader video');
            if (videoElem) {
                const canvas = document.createElement('canvas');
                canvas.width = videoElem.videoWidth;
                canvas.height = videoElem.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(videoElem, 0, 0, canvas.width, canvas.height);
                imageData = canvas.toDataURL('image/png');
            }
            
            try {
                const response = await fetch('{{ route('hrm.kiosk.scan') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        nip: nip,
                        image: imageData
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showMessage('success', data.type === 'in' ? 'Absen Masuk Berhasil' : 'Absen Keluar Berhasil', data.message, data.time);
                } else {
                    showMessage('error', 'Gagal', data.message || 'Terjadi kesalahan sistem', '');
                }

            } catch (error) {
                showMessage('error', 'Koneksi Terputus', 'Tidak dapat terhubung ke server', '');
            } finally {
                // Reset after delay to allow next scan
                setTimeout(() => {
                    isProcessing = false;
                    nipInput.value = '';
                    nipInput.style.borderColor = '#e2e8f0';
                }, 4000); 
            }
        }

        function showMessage(type, title, desc, time) {
            const overlay = document.getElementById('messageOverlay');
            const iconDiv = document.getElementById('msgIcon');
            const iconInner = document.getElementById('msgIconInner');
            const titleEl = document.getElementById('msgTitle');
            const descEl = document.getElementById('msgDesc');
            const timeEl = document.getElementById('msgTime');

            // Setup styling based on type
            if (type === 'success') {
                iconDiv.className = 'message-icon success';
                iconInner.setAttribute('data-lucide', 'check');
                titleEl.style.color = 'var(--success-600)';
            } else {
                iconDiv.className = 'message-icon error';
                iconInner.setAttribute('data-lucide', 'x');
                titleEl.style.color = 'var(--danger-500)';
            }

            // Re-render icon
            lucide.createIcons();

            titleEl.textContent = title;
            descEl.textContent = desc;
            timeEl.textContent = time;

            overlay.classList.add('active');

            // Auto hide after 3 seconds
            setTimeout(() => {
                overlay.classList.remove('active');
                // Refocus input after overlay hides
                setTimeout(() => {
                    nipInput.focus();
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>
