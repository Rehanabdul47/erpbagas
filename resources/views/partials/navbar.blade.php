<header :class="sidebarOpen ? 'lg:left-64' : 'left-0'" class="fixed top-0 right-0 h-16 bg-gray-900/85 backdrop-blur-md border-b border-gray-700/50 flex items-center justify-between px-4 sm:px-6 z-[80] transition-all duration-300">
    <div class="flex items-center gap-3 sm:gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
        <div class="text-base font-semibold text-white hidden sm:block">
            <span>@yield('breadcrumb', 'Dashboard')</span>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <div class="relative flex items-center hidden sm:flex">
            <i data-lucide="search" class="absolute left-3 w-4 h-4 text-gray-400"></i>
            <input type="text" placeholder="Cari..." id="global-search" class="bg-gray-800 border-none text-white pl-9 pr-3 py-2 rounded-lg w-56 focus:ring-2 focus:ring-indigo-500 transition-all outline-none text-sm placeholder-gray-500">
        </div>
        <div class="relative" x-data="{ notificationsOpen: false }">
            <button @click="notificationsOpen = !notificationsOpen" @click.away="notificationsOpen = false" class="relative p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors" id="notification-btn">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">0</span>
            </button>
            <div x-show="notificationsOpen" x-transition style="display: none;" class="absolute top-full right-0 mt-2 w-80 bg-gray-800 border border-gray-700 rounded-xl shadow-lg z-50 overflow-hidden" id="notification-dropdown">
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-700 bg-gray-800/50">
                    <strong class="text-sm text-white">Notifikasi</strong>
                    <button class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Tandai dibaca</button>
                </div>
                <div class="max-h-64 overflow-y-auto p-4 text-center text-sm text-gray-400" id="notif-dropdown-list">
                    Tidak ada notifikasi baru
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-400 hidden lg:flex">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span id="header-date">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
        </div>
    </div>
</header>
