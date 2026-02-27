@php
    $menuConfig = [
        ['section' => 'OVERVIEW', 'items' => [
            ['id' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
        ]],
        ['section' => 'DIVISI PRODUKSI', 'items' => [
            ['id' => 'production-dashboard', 'icon' => 'layout-dashboard', 'label' => 'Overview Produksi', 'route' => 'production.dashboard'],
            ['id' => 'inventory', 'icon' => 'package', 'label' => 'Inventory', 'route' => 'inventory.index'],
            ['id' => 'purchase-orders', 'icon' => 'shopping-bag', 'label' => 'Purchase Order', 'route' => 'purchase-orders.index'],
            ['id' => 'suppliers', 'icon' => 'truck', 'label' => 'Suppliers', 'route' => 'suppliers.index'],
            ['id' => 'production', 'icon' => 'factory', 'label' => 'Produksi', 'route' => 'production.index'],
            ['id' => 'categories', 'icon' => 'tags', 'label' => 'Kategori', 'route' => 'categories.index'],
        ]],
        ['section' => 'DIVISI MARKETING', 'items' => [
            ['id' => 'marketing-dashboard', 'icon' => 'line-chart', 'label' => 'Overview Marketing', 'route' => 'marketing.dashboard'],
            ['id' => 'sales', 'icon' => 'trending-up', 'label' => 'Sales & CRM', 'route' => 'sales.index'],
            ['id' => 'redeem-codes', 'icon' => 'ticket', 'label' => 'Redeem Codes', 'route' => 'redeem-codes.index'],
            ['id' => 'pos', 'icon' => 'monitor', 'label' => 'Point of Sale', 'route' => 'pos.index'],
        ]],
        ['section' => 'OPERASIONAL', 'items' => [
            ['id' => 'orders', 'icon' => 'shopping-cart', 'label' => 'Orders', 'route' => 'orders', 'badge' => 3],
        ]],
        ['section' => 'DIVISI KEUANGAN', 'items' => [
            ['id' => 'finance-dashboard', 'icon' => 'line-chart', 'label' => 'Overview Keuangan', 'route' => 'finance.dashboard'],
            ['id' => 'finance', 'icon' => 'wallet', 'label' => 'Buku Kas', 'route' => 'finance.index'],
            ['id' => 'reports', 'icon' => 'bar-chart-3', 'label' => 'Laporan Keuangan', 'route' => 'finance.reports'],
        ]],
        ['section' => 'DIVISI SUMBER DAYA MANUSIA', 'items' => [
            ['id' => 'hrm', 'icon' => 'users', 'label' => 'HRM & Payroll', 'route' => 'hrm.index'],
            ['id' => 'kiosk', 'icon' => 'scan-line', 'label' => 'Presensi', 'route' => 'hrm.kiosk', 'target' => '_blank'],
        ]],
        ['section' => 'SISTEM', 'items' => [
            ['id' => 'users', 'icon' => 'user-cog', 'label' => 'Manage Users', 'route' => 'users.index'],
            ['id' => 'settings', 'icon' => 'settings', 'label' => 'Settings', 'route' => 'settings'],
        ]],
    ];
    $activePage = $activePage ?? 'dashboard';
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gray-800/95 border-r border-gray-700/50 flex flex-col z-[100] transition-transform duration-300">
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-700/50 shrink-0">
        <div class="flex items-center gap-3 text-indigo-400 font-bold tracking-widest text-lg">
            <i data-lucide="hexagon" class="w-6 h-6"></i>
            <span>GUPALA</span>
        </div>
        <button @click="sidebarOpen = false" id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin scrollbar-thumb-gray-700 hover:scrollbar-thumb-gray-600" id="sidebar-nav">
        @foreach($menuConfig as $section)
        <div class="mb-6 px-4">
            <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 px-2">{{ $section['section'] }}</div>
            <div class="space-y-1">
                @foreach($section['items'] as $item)
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $activePage === $item['id'] ? 'bg-indigo-600 border border-indigo-500 text-white shadow-lg shadow-indigo-600/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}" 
                    @if(Route::has($item['route'])) href="{{ route($item['route']) }}" @else href="#" @endif 
                    data-id="{{ $item['id'] }}" @if(isset($item['target'])) target="{{ $item['target'] }}" @endif>
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 {{ $activePage === $item['id'] ? 'text-white' : 'text-gray-400 group-hover:text-white' }}"></i>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if(!empty($item['badge']))
                    <span class="bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $item['badge'] }}</span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </nav>

    <div class="p-4 border-t border-gray-700/50 shrink-0">
        <div class="flex items-center gap-3 bg-gray-900/50 p-3 rounded-xl border border-gray-700/50" id="sidebar-user">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20 shrink-0">
                <i data-lucide="user" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-white truncate" id="user-name">{{ Auth::user()->name ?? 'Guest' }}</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider truncate" id="user-role-label">{{ Auth::user()?->roles->first()?->name ?? '' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0 shrink-0">
                @csrf
                <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-400/10 transition-colors" id="btn-logout" title="Logout">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
