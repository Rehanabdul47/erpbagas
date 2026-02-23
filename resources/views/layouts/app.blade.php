<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <main class="main-content">
            @include('partials.navbar')

            <div class="page-content" id="page-content">
                @yield('content')
            </div>
        </main>
    </div>

    @include('partials.footer')
</body>
</html>
