<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'GUPALA ERP') — GUPALA ERP</title>
<meta name="description" content="GUPALA ERP - Sistem manajemen perusahaan terintegrasi.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
    <!-- Tailwind CSS & Alpine JS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#EEF2FF',
                            100: '#E0E7FF',
                            200: '#C7D2FE',
                            300: '#A5B4FC',
                            400: '#818CF8',
                            500: '#6366F1',
                            600: '#4F46E5',
                            700: '#4338CA',
                            800: '#3730A3',
                            900: '#312E81',
                        },
                        surface: '#111827',
                        card: '#1E293B',
                        input: '#1A2332',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar and base adjustments */
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748B; }
        body { -webkit-font-smoothing: antialiased; }

        /* ===== LIGHT THEME OVERRIDES ===== */
        body.theme-light {
            --bg-body: #F1F5F9;
            --bg-sidebar: #FFFFFF;
            --bg-navbar: rgba(255,255,255,0.92);
            --bg-card: #FFFFFF;
            --bg-card-inner: #F8FAFC;
            --bg-input: #F1F5F9;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border-color: #E2E8F0;
            --border-color-subtle: #F1F5F9;
        }

        /* Body bg + text */
        body.theme-light { background-color: var(--bg-body) !important; color: var(--text-primary) !important; }

        /* Scrollbar for light */
        body.theme-light ::-webkit-scrollbar-thumb { background: #CBD5E1; }
        body.theme-light ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        /* === SIDEBAR === */
        body.theme-light #sidebar { background-color: var(--bg-sidebar) !important; border-color: var(--border-color) !important; }
        body.theme-light #sidebar .text-gray-400,
        body.theme-light #sidebar .text-gray-500 { color: var(--text-secondary) !important; }
        body.theme-light #sidebar a.text-gray-400 { color: var(--text-secondary) !important; }
        body.theme-light #sidebar a:hover { background-color: #EEF2FF !important; color: #4F46E5 !important; }
        body.theme-light #sidebar a .text-gray-400 { color: var(--text-secondary) !important; }
        body.theme-light #sidebar a:hover .text-gray-400 { color: #4F46E5 !important; }
        body.theme-light #sidebar .border-gray-700\/50,
        body.theme-light #sidebar .border-gray-700 { border-color: var(--border-color) !important; }
        body.theme-light #sidebar-user { background-color: var(--bg-card-inner) !important; border-color: var(--border-color) !important; }
        body.theme-light #sidebar-user .text-white { color: var(--text-primary) !important; }
        body.theme-light #sidebar-user .text-gray-400 { color: var(--text-secondary) !important; }

        /* === NAVBAR === */
        body.theme-light header { background-color: var(--bg-navbar) !important; border-color: var(--border-color) !important; backdrop-filter: blur(12px); }
        body.theme-light header .text-white { color: var(--text-primary) !important; }
        body.theme-light header .text-gray-400 { color: var(--text-secondary) !important; }
        body.theme-light header input#global-search { background-color: var(--bg-input) !important; color: var(--text-primary) !important; border: 1px solid var(--border-color) !important; }
        body.theme-light header input#global-search::placeholder { color: var(--text-muted) !important; }
        body.theme-light #notification-dropdown { background-color: var(--bg-card) !important; border-color: var(--border-color) !important; }
        body.theme-light #notification-dropdown .text-white { color: var(--text-primary) !important; }
        body.theme-light #notification-dropdown .border-gray-700 { border-color: var(--border-color) !important; }

        /* === CARDS & PANELS === */
        body.theme-light .bg-gray-900 { background-color: var(--bg-body) !important; }
        body.theme-light .bg-gray-800 { background-color: var(--bg-card) !important; }
        body.theme-light .bg-gray-800\/95 { background-color: var(--bg-card) !important; }
        body.theme-light .bg-gray-800\/50 { background-color: var(--bg-card-inner) !important; }
        body.theme-light .bg-gray-900\/50 { background-color: var(--bg-card-inner) !important; }
        body.theme-light .bg-gray-900\/30 { background-color: var(--bg-card-inner) !important; }
        body.theme-light .bg-gray-900\/80 { background-color: rgba(15,23,42,0.5) !important; }

        /* === TEXT === */
        body.theme-light .text-white { color: var(--text-primary) !important; }
        body.theme-light .text-slate-100 { color: var(--text-primary) !important; }
        body.theme-light .text-gray-400 { color: var(--text-secondary) !important; }
        body.theme-light .text-gray-500 { color: var(--text-muted) !important; }
        body.theme-light .text-gray-300 { color: var(--text-secondary) !important; }

        /* === BORDERS === */
        body.theme-light .border-gray-700 { border-color: var(--border-color) !important; }
        body.theme-light .border-gray-700\/50 { border-color: var(--border-color) !important; }
        body.theme-light .border-gray-600 { border-color: var(--border-color) !important; }

        /* === INPUTS === */
        body.theme-light input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="hidden"]),
        body.theme-light select,
        body.theme-light textarea {
            background-color: var(--bg-input) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        body.theme-light input::placeholder,
        body.theme-light textarea::placeholder { color: var(--text-muted) !important; }

        /* === TABLES === */
        body.theme-light table thead { background-color: var(--bg-card-inner) !important; }
        body.theme-light table thead th { color: var(--text-secondary) !important; }
        body.theme-light table tbody tr { border-color: var(--border-color) !important; }
        body.theme-light table tbody td { color: var(--text-primary) !important; }

        /* === MOBILE BACKDROP (keep dark) === */
        body.theme-light .bg-gray-900\/80[x-show] { background-color: rgba(0,0,0,0.4) !important; }

        /* Preserve accent colors — indigo buttons, badges etc. should NOT change */
        body.theme-light .bg-indigo-600 .text-white,
        body.theme-light .bg-indigo-500 .text-white,
        body.theme-light .bg-emerald-500 .text-white,
        body.theme-light .bg-red-500 .text-white { color: #FFFFFF !important; }
        body.theme-light .bg-indigo-600 { color: #FFFFFF !important; }

        /* === SHADOW ADJUSTMENTS === */
        body.theme-light .shadow-2xl { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05) !important; }
        body.theme-light .shadow-xl { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05) !important; }
        body.theme-light .shadow-lg { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; }

        /* === GLOBAL MODAL === */
        body.theme-light [x-data] .bg-gray-800\/90 { background-color: rgba(255,255,255,0.95) !important; }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* === CUSTOM FLATPICKR THEME === */
        .flatpickr-calendar {
            background: #111827 !important; 
            border: 1px solid #374151 !important; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            border-radius: 12px !important;
            backdrop-filter: blur(10px);
            z-index: 9999 !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
            background: #6366F1 !important; /* primary-500 */
            border-color: #6366F1 !important;
        }
        .flatpickr-day:hover { background: #374151 !important; }
        .flatpickr-months .flatpickr-month, .flatpickr-current-month .flatpickr-month {
            background: #111827 !important;
            color: white !important;
            fill: white !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months { background: #111827 !important; }
        .flatpickr-weekdays { background: #1f2937 !important; }
        span.flatpickr-weekday { background: #1f2937 !important; color: #9ca3af !important; }
        .flatpickr-calendar.arrowTop:after, .flatpickr-calendar.arrowTop:before { border-bottom-color: #374151 !important; }

        /* Light mode overrides for flatpickr */
        body.theme-light .flatpickr-calendar {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            color: #0F172A !important;
        }
        body.theme-light .flatpickr-months .flatpickr-month { background: #FFFFFF !important; color: #0F172A !important; }
    </style>
    @stack('styles')
