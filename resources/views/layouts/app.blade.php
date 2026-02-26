<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="bg-gray-900 text-slate-100 min-h-screen text-sm font-sans antialiased" x-data="{ sidebarOpen: false }" :class="sidebarOpen ? 'overflow-hidden md:overflow-auto' : 'overflow-x-hidden'">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition.opacity x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[90] md:hidden" @click="sidebarOpen = false"></div>

    @include('partials.sidebar')

    <!-- Main Content Wrapper -->
    <main class="md:ml-64 flex flex-col min-h-screen transition-all duration-300" id="main-content">
        @include('partials.navbar')

        <!-- Page Content -->
        <div class="flex-1 p-4 sm:p-6 mt-16 animate-[fadeIn_0.3s_ease] overflow-x-hidden w-full max-w-full" id="page-content">
            @yield('content')
        </div>
    </main>

    @include('partials.footer')
</body>
</html>
