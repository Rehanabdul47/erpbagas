<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — GUPALA ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-slate-100 min-h-screen text-sm font-sans flex items-center justify-center relative overflow-hidden">
    <!-- Animated Orbs Background -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-purple-500/20 blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-[30%] right-[20%] w-[30%] h-[30%] rounded-full bg-blue-500/10 blur-[80px] animate-pulse" style="animation-delay: 4s;"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 p-8 rounded-2xl shadow-2xl">
        <div class="flex flex-col items-center mb-8">
            <div class="w-16 h-16 bg-indigo-500/10 text-indigo-400 rounded-2xl border border-indigo-500/20 flex items-center justify-center mb-4 shadow-inner">
                <i data-lucide="hexagon" class="w-8 h-8"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-wider mb-1">GUPALA</h1>
            <p class="text-gray-400 text-sm tracking-wide">Enterprise Resource Planning</p>
        </div>

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 mb-6 text-red-400 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form id="login-form" method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            <div class="space-y-1.5">
                <label for="login-email" class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Email</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                    <input type="email" name="email" id="login-email" placeholder="admin@gupala.com" value="{{ old('email') }}" required
                           class="w-full bg-gray-900/50 border border-gray-700 text-white pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all placeholder-gray-600">
                </div>
            </div>
            
            <div class="space-y-1.5">
                <label for="login-password" class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                    <input type="password" name="password" id="login-password" placeholder="••••••••" required
                           class="w-full bg-gray-900/50 border border-gray-700 text-white pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all placeholder-gray-600">
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 rounded-xl flex items-center justify-center gap-2 transition-colors mt-2 shadow-lg shadow-indigo-600/30">
                <i data-lucide="log-in" class="w-5 h-5"></i>
                <span>Masuk</span>
            </button>
        </form>

        <p class="mt-8 text-center text-xs text-gray-500">
            Demo: email apapun @gupala.com / password: <strong class="text-gray-400">password</strong>
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
    </script>
</body>
</html>
