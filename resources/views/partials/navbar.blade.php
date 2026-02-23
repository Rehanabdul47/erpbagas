<header class="main-header">
    <div class="header-left">
        <button id="mobile-menu-btn" class="btn-icon mobile-menu-btn">
            <i data-lucide="menu"></i>
        </button>
        <div class="breadcrumb" id="breadcrumb">
            <span>@yield('breadcrumb', 'Dashboard')</span>
        </div>
    </div>
    <div class="header-right">
        <div class="header-search">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Cari..." id="global-search">
        </div>
        <div class="notification-wrapper" id="notification-wrapper">
            <button class="btn-icon notification-btn" id="notification-btn">
                <i data-lucide="bell"></i>
                <span class="notification-badge" id="notification-badge">5</span>
            </button>
            <div class="notification-dropdown hidden" id="notification-dropdown">
                <div class="notif-dropdown-header">
                    <strong>Notifikasi</strong>
                    <button class="btn btn-ghost btn-xs" onclick="markAllRead()">Tandai dibaca</button>
                </div>
                <div class="notif-dropdown-list" id="notif-dropdown-list"></div>
            </div>
        </div>
        <div class="header-date">
            <i data-lucide="calendar"></i>
            <span id="header-date"></span>
        </div>
    </div>
</header>
