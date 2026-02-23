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
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-overlay" style="display:flex">
        <div class="login-bg-animation">
            <div class="login-orb orb-1"></div>
            <div class="login-orb orb-2"></div>
            <div class="login-orb orb-3"></div>
        </div>
        <div class="login-card">
            <div class="login-logo">
                <div class="login-logo-icon"><i data-lucide="hexagon" class="logo-hex"></i></div>
                <h1>GUPALA</h1>
                <p class="login-subtitle">Enterprise Resource Planning</p>
            </div>
            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;color:#f87171;font-size:13px;">
                {{ $errors->first() }}
            </div>
            @endif
            <form id="login-form" class="login-form" method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <div class="input-icon"><i data-lucide="mail"></i>
                        <input type="email" name="email" id="login-email" placeholder="admin@gupala.com" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <div class="input-icon"><i data-lucide="lock"></i>
                        <input type="password" name="password" id="login-password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login">
                    <i data-lucide="log-in"></i><span>Masuk</span>
                </button>
            </form>
            <p class="login-hint">Demo: email apapun @gupala.com / password: <strong>password</strong></p>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
    </script>
</body>
</html>
