@php
    $user = auth()->user();
    $isMgmt = $user && $user->hasAnyRole(['Manager', 'Executive']);
    $dept = $user ? $user->department : null;

    $menuConfig = [
        ['section' => 'OVERVIEW', 'visible' => true, 'items' => [
            ['id' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard', 'route' => 'dashboard', 'visible' => $isMgmt],
            ['id' => 'kiosk', 'icon' => 'scan-line', 'label' => 'Presensi / Kiosk', 'route' => 'hrm.kiosk', 'target' => '_blank', 'visible' => true],
        ]],
        ['section' => 'DIVISI PRODUKSI', 'visible' => $isMgmt || in_array($dept, ['Produksi', 'Warehouse/Logistik']) || ($user && $user->hasRole('Operator Produksi')), 'items' => [
            ['id' => 'production-dashboard', 'icon' => 'layout-dashboard', 'label' => 'Overview Produksi', 'route' => 'production.dashboard', 'visible' => true],
            ['id' => 'inventory', 'icon' => 'package', 'label' => 'Inventory', 'route' => 'inventory.index', 'visible' => true],
            ['id' => 'purchase-orders', 'icon' => 'shopping-bag', 'label' => 'Purchase Order', 'route' => 'purchase-orders.index', 'visible' => true],
            ['id' => 'suppliers', 'icon' => 'truck', 'label' => 'Suppliers', 'route' => 'suppliers.index', 'visible' => true],
            ['id' => 'production', 'icon' => 'factory', 'label' => 'Produksi', 'route' => 'production.index', 'visible' => true],
            ['id' => 'tailors', 'icon' => 'scissors', 'label' => 'Vendor Produksi', 'route' => 'tailors.index', 'visible' => true],
            ['id' => 'tailors-monitoring', 'icon' => 'activity', 'label' => 'Monitor Vendor Produksi', 'route' => 'tailors.monitoring', 'visible' => true],
            ['id' => 'production-transfers', 'icon' => 'arrow-right-left', 'label' => 'Transfer Barang', 'route' => 'production.transfers.index', 'visible' => true],
            ['id' => 'categories', 'icon' => 'tags', 'label' => 'Kategori', 'route' => 'categories.index', 'visible' => true],
        ]],
        ['section' => 'DIVISI MARKETING', 'visible' => $isMgmt || $dept === 'Marketing' || ($user && $user->hasRole('Sales/Marketing')), 'items' => [
            ['id' => 'marketing-dashboard', 'icon' => 'line-chart', 'label' => 'Overview Marketing', 'route' => 'marketing.dashboard', 'visible' => true],
            ['id' => 'sales', 'icon' => 'trending-up', 'label' => 'Sales & CRM', 'route' => 'sales.index', 'visible' => true],
            ['id' => 'redeem-codes', 'icon' => 'ticket', 'label' => 'Promo & Redeem Codes', 'route' => 'redeem-codes.index', 'visible' => true],
            ['id' => 'pos', 'icon' => 'monitor', 'label' => 'Point of Sale', 'route' => 'pos.index', 'visible' => true],
        ]],
        ['section' => 'DIVISI DIGITAL MARKETING', 'visible' => $isMgmt || $dept === 'Digital Marketing', 'items' => [
            ['id' => 'digital-marketing-dashboard', 'icon' => 'line-chart', 'label' => 'Overview Digital Marketing', 'route' => 'digital-marketing.dashboard', 'visible' => true],
            ['id' => 'digital-platforms', 'icon' => 'layout-list', 'label' => 'Data Platform', 'route' => 'digital-platforms.index', 'visible' => true],
            ['id' => 'orders', 'icon' => 'shopping-cart', 'label' => 'E-Commerce Orders', 'route' => 'orders.index', 'badge' => 3, 'visible' => true],
        ]],
        ['section' => 'DIVISI KEUANGAN', 'visible' => $isMgmt || $dept === 'Keuangan', 'items' => [
            ['id' => 'finance-dashboard', 'icon' => 'line-chart', 'label' => 'Overview Keuangan', 'route' => 'finance.dashboard', 'visible' => true],
            ['id' => 'finance', 'icon' => 'wallet', 'label' => 'Buku Kas', 'route' => 'finance.index', 'visible' => true],
            ['id' => 'reports', 'icon' => 'bar-chart-3', 'label' => 'Laporan Keuangan', 'route' => 'finance.reports', 'visible' => true],
        ]],
        ['section' => 'DIVISI SUMBER DAYA MANUSIA', 'visible' => $isMgmt || $dept === 'HRD & GA', 'items' => [
            ['id' => 'hrm-dashboard', 'icon' => 'layout-dashboard', 'label' => 'Overview HRM', 'route' => 'hrm.dashboard', 'visible' => true],
            ['id' => 'hrm', 'icon' => 'users', 'label' => 'HRM & Payroll', 'route' => 'hrm.index', 'visible' => true],
        ]],
        ['section' => 'SISTEM', 'visible' => ($user && $user->hasRole('Executive')) || ($user && $user->hasRole('Admin')), 'items' => [
            ['id' => 'branches', 'icon' => 'store', 'label' => 'Cabang / Toko', 'route' => 'branches.index', 'visible' => true],
            ['id' => 'users', 'icon' => 'user-cog', 'label' => 'Manage Users', 'route' => 'users.index', 'visible' => true],
            ['id' => 'settings', 'icon' => 'settings', 'label' => 'Settings', 'route' => 'settings', 'visible' => true],
        ]],
    ];
    $activePage = $activePage ?? 'dashboard';
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gray-800/95 border-r border-gray-700/50 flex flex-col z-[100] transition-transform duration-300">
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-700/50 shrink-0">
        <div class="flex items-center gap-3 text-indigo-400 font-bold tracking-widest text-lg">
            @php
                $sidebarLogo = \App\Models\Setting::where('key', 'company_logo')->value('value');
                $sidebarName = \App\Models\Setting::where('key', 'company_name')->value('value') ?? 'GUPALA';
            @endphp
            @if($sidebarLogo)
                <img src="{{ asset($sidebarLogo) }}" alt="Logo" class="w-8 h-8 object-contain">
            @else
                <i data-lucide="hexagon" class="w-6 h-6"></i>
            @endif
            <span class="truncate max-w-[130px]">{{ $sidebarName }}</span>
        </div>
        <button @click="sidebarOpen = false" id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-2 scrollbar-thin scrollbar-thumb-gray-700 hover:scrollbar-thumb-gray-600" id="sidebar-nav">
        @foreach($menuConfig as $sectionIndex => $section)
        @php
            if (isset($section['visible']) && !$section['visible']) continue;
            $hasActiveItem = collect($section['items'])->pluck('id')->contains($activePage);
        @endphp
        <div class="mb-1 px-4" x-data="{ open: {{ $hasActiveItem ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                    class="w-full flex items-center justify-between px-2 py-3 text-[10px] font-bold text-gray-500 hover:text-gray-300 uppercase tracking-wider transition-colors group">
                <span class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full" :class="open ? 'bg-indigo-500' : 'bg-gray-600'"></span>
                    {{ $section['section'] }}
                </span>
                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-1 pb-2">
                @foreach($section['items'] as $item)
                @if(isset($item['visible']) && !$item['visible']) @continue @endif
                <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $activePage === $item['id'] ? 'bg-indigo-600 border border-indigo-500 text-white shadow-lg shadow-indigo-600/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/50' }}" 
                    @if(isset($item['route_name']) && Route::has($item['route_name']))
                        href="{{ route($item['route_name'], $item['route_params'] ?? []) }}"
                    @elseif(isset($item['route']) && Route::has($item['route'])) 
                        href="{{ route($item['route']) }}" 
                    @else 
                        href="#" 
                    @endif 
                    data-id="{{ $item['id'] }}" @if(isset($item['target'])) target="{{ $item['target'] }}" @endif>
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 {{ $activePage === $item['id'] ? 'text-white' : 'text-gray-400 group-hover:text-white' }}"></i>
                    <span class="flex-1 truncate text-xs">{{ $item['label'] }}</span>
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
