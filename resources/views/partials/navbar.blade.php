<header :class="sidebarOpen ? 'lg:left-64' : 'left-0'" class="fixed top-0 right-0 h-16 bg-gray-900/85 backdrop-blur-md border-b border-gray-700/50 flex items-center justify-between px-4 sm:px-6 z-[80] transition-all duration-300">
    <div class="flex items-center gap-3 sm:gap-4">
        <button @click="toggleSidebar()" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
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
        <div class="relative" x-data="{ 
            notificationsOpen: false, 
            notifCount: 0, 
            notifications: [], 
            loading: false,
            fetchNotifs() {
                this.loading = true;
                fetch('{{ route('api.notifications') }}')
                    .then(res => res.json())
                    .then(data => {
                        this.notifCount = data.count;
                        this.notifications = data.notifications;
                        this.loading = false;
                        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                    })
                    .catch(err => { console.error('Error fetching notifications:', err); this.loading = false; });
            },
            markRead(id) {
                fetch('{{ route('api.notifications.read') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ id: id })
                }).then(() => this.fetchNotifs());
            },
            markAllRead() {
                fetch('{{ route('api.notifications.readAll') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => this.fetchNotifs());
            }
        }" x-init="fetchNotifs(); setInterval(() => fetchNotifs(), 60000)">
            <button @click="notificationsOpen = !notificationsOpen; if(notificationsOpen) fetchNotifs()" @click.away="notificationsOpen = false" class="relative p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors" id="notification-btn">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span x-show="notifCount > 0" x-text="notifCount" class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center"></span>
            </button>
            <div x-show="notificationsOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="display: none;" 
                 class="absolute top-full right-0 mt-2 w-80 bg-gray-800 border border-gray-700 rounded-xl shadow-lg z-[110] overflow-hidden" id="notification-dropdown">
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-700 bg-gray-900/50">
                    <div class="flex items-center gap-2">
                        <strong class="text-sm text-white">Notifikasi</strong>
                        <span x-show="notifCount > 0" class="px-1.5 py-0.5 bg-indigo-500 text-[9px] text-white rounded font-bold" x-text="notifCount + ' Baru'"></span>
                    </div>
                    <button @click="markAllRead()" class="text-[10px] uppercase font-bold text-indigo-400 hover:text-indigo-300 transition-colors">Baca Semua</button>
                </div>
                <div class="max-h-80 overflow-y-auto" id="notif-dropdown-list">
                    <template x-if="loading && notifications.length === 0">
                        <div class="p-8 text-center"><div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto"></div></div>
                    </template>
                    <template x-for="notif in notifications" :key="notif.id">
                        <a :href="notif.route" @click="markRead(notif.id)" class="flex items-start gap-3 p-4 hover:bg-gray-700/50 border-b border-gray-700/50 last:border-0 transition-colors group relative" :class="notif.is_read ? 'opacity-60 bg-transparent' : 'bg-indigo-500/5'">
                            <span x-show="!notif.is_read" class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="`bg-${notif.color}-500/10 text-${notif.color}-400 shadow-sm`">
                                <i :data-lucide="notif.icon" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-white mb-0.5 truncate" x-text="notif.title"></p>
                                <p class="text-[10px] text-gray-400 leading-relaxed line-clamp-2" x-text="notif.message"></p>
                                <p class="text-[9px] text-gray-500 mt-1 font-medium" x-text="notif.time"></p>
                            </div>
                        </a>
                    </template>
                    <template x-if="!loading && notifications.length === 0">
                        <div class="p-8 text-center space-y-2">
                            <i data-lucide="bell-off" class="w-8 h-8 text-gray-600 mx-auto"></i>
                            <p class="text-xs text-gray-500 font-medium">Tidak ada notifikasi baru</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-400 hidden lg:flex">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span id="header-date">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
        </div>
    </div>
</header>
