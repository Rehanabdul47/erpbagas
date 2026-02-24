@php
    $menuConfig = [
        ['section' => 'OVERVIEW', 'items' => [
            ['id' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
        ]],
        ['section' => 'OPERASIONAL', 'items' => [
            ['id' => 'inventory', 'icon' => 'package', 'label' => 'Inventory', 'route' => 'inventory.index'],
            ['id' => 'categories', 'icon' => 'tags', 'label' => 'Kategori', 'route' => 'categories.index'],
            ['id' => 'orders', 'icon' => 'shopping-cart', 'label' => 'Orders', 'route' => 'orders', 'badge' => 3],
            ['id' => 'sales', 'icon' => 'trending-up', 'label' => 'Sales & CRM', 'route' => 'sales.index'],
            ['id' => 'redeem-codes', 'icon' => 'ticket', 'label' => 'Redeem Codes', 'route' => 'redeem-codes.index'],
            ['id' => 'pos', 'icon' => 'monitor', 'label' => 'Point of Sale', 'route' => 'pos.index'],
        ]],
        ['section' => 'KEUANGAN', 'items' => [
            ['id' => 'finance', 'icon' => 'wallet', 'label' => 'Finance', 'route' => 'finance.index'],
        ]],
        ['section' => 'SDM', 'items' => [
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

<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i data-lucide="hexagon" class="logo-hex-sm"></i>
            <span class="sidebar-brand">GUPALA</span>
        </div>
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i data-lucide="panel-left-close"></i>
        </button>
    </div>

    <nav class="sidebar-nav" id="sidebar-nav">
        @foreach($menuConfig as $section)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ $section['section'] }}</div>
            @foreach($section['items'] as $item)
            <a class="nav-item {{ $activePage === $item['id'] ? 'active' : '' }}" href="{{ route($item['route']) }}" data-id="{{ $item['id'] }}" @if(isset($item['target'])) target="{{ $item['target'] }}" @endif>
                <i data-lucide="{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
                @if(!empty($item['badge']))
                <span class="nav-badge">{{ $item['badge'] }}</span>
                @endif
            </a>
            @endforeach
        </div>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user" id="sidebar-user">
            <div class="user-avatar"><i data-lucide="user"></i></div>
            <div class="user-info">
                <span class="user-name" id="user-name">{{ Auth::user()->name ?? 'Guest' }}</span>
                <span class="user-role" id="user-role-label">{{ Auth::user()?->roles->first()?->name ?? '' }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn-icon" id="btn-logout" title="Logout">
                    <i data-lucide="log-out"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
