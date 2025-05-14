<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    {{-- Sidebar Brand --}}
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{ get_template_url('assets/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
                class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">{{ config('app.name') }}</span>
        </a>
    </div>
    {{-- Sidebar Menu --}}
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                {{-- Order Management --}}
                <li class="nav-header">ORDER MANAGEMENT</li>
                <li class="nav-item">
                    <a href="{{ route('order.index') }}" class="nav-link {{ request()->routeIs('order.index') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cart-fill"></i>
                        <p>Order</p>
                    </a>
                </li>
                {{-- Tracking Management --}}
                <li class="nav-header">TRACKING MANAGEMENT</li>
                <li class="nav-item">
                    <a href="{{ route('tracking.index') }}" class="nav-link {{ request()->routeIs('tracking.index') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-map-fill"></i>
                        <p>Tracking</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('tracking.history') }}" class="nav-link {{ request()->routeIs('tracking.history') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-clock-history"></i>
                        <p>History</p>
                    </a>
                </li>
                {{-- User Management --}}
                <li class="nav-header">USER MANAGEMENT</li>
                <li class="nav-item">
                    <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person"></i>
                        <p>User</p>
                    </a>
                </li>
                {{-- Role Management --}}
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-rolodex"></i>
                        <p>Role</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
