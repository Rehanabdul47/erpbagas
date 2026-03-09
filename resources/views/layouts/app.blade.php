<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="bg-gray-900 text-slate-100 min-h-screen text-sm font-sans antialiased {{ ($global_settings['dark_mode'] ?? 'true') === 'true' ? 'theme-dark' : 'theme-light' }}" 
      data-notifications="{{ $global_settings['notifications'] ?? 'true' }}" 
      x-data="{ sidebarOpen: window.innerWidth > 1024 }" 
      :class="sidebarOpen ? 'overflow-hidden lg:overflow-auto' : 'overflow-x-hidden'">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition.opacity x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[90] lg:hidden" @click="sidebarOpen = false"></div>

    @include('partials.sidebar')

    <!-- Main Content Wrapper -->
    <main class="flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'" id="main-content">
        @include('partials.navbar')

        <!-- Page Content -->
        <div class="flex-1 p-4 sm:p-6 mt-16 animate-[fadeIn_0.3s_ease] overflow-x-hidden w-full max-w-full" id="page-content">
            @yield('content')
        </div>
    </main>

    @include('partials.footer')
    @include('partials.global-modal')
    <script>
        {{-- Initialize Lucide Icons globally --}}
        document.addEventListener('alpine:init', () => {
             {{-- Observe global modal to re-init lucide --}}
        });
    </script>
</body>
</html>
