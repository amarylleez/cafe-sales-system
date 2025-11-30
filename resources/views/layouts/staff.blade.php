<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Staff Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
            z-index: 1000;
            overflow-x: hidden;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 600;
            white-space: nowrap;
            opacity: 1;
            transition: opacity 0.3s;
        }
        
        .sidebar.collapsed .sidebar-brand {
            opacity: 0;
            display: none;
        }
        
        .sidebar-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            min-width: 35px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }
        
        .sidebar.collapsed .sidebar-header {
            justify-content: center;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }
        
        .sidebar-menu-item {
            margin: 0.25rem 0;
        }
        
        .sidebar-menu-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }
        
        .sidebar-menu-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left: 4px solid white;
        }
        
        .sidebar-menu-icon {
            font-size: 1.25rem;
            width: 30px;
            min-width: 30px;
            text-align: center;
        }
        
        .sidebar-menu-text {
            margin-left: 1rem;
            white-space: nowrap;
            opacity: 1;
            transition: opacity 0.3s;
        }
        
        .sidebar.collapsed .sidebar-menu-text {
            opacity: 0;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            background: #f8f9fa;
            width: calc(100% - var(--sidebar-width));
            max-width: 100vw;
            overflow-x: hidden;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .notification-badge {
            position: relative;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Content Area */
        .content-area {
            padding: 2rem;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .content-area .container-fluid {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Card Animations */
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .main-content.expanded {
                margin-left: 0;
                width: 100%;
            }
        }
        
        /* Prevent horizontal scroll */
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="bi bi-speedometer2"></i> Staff Portal
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-chevron-left" id="toggleIcon"></i>
            </button>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('staff.dashboard') }}" class="sidebar-menu-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('staff.sales.create') }}" class="sidebar-menu-link {{ request()->routeIs('staff.sales.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Submit Sales</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('staff.kpi') }}" class="sidebar-menu-link {{ request()->routeIs('staff.kpi') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">My KPI</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('staff.alerts') }}" class="sidebar-menu-link {{ request()->routeIs('staff.alerts') ? 'active' : '' }}">
                    <i class="bi bi-bell sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Alerts</span>
                    @if(isset($unreadNotifications) && $unreadNotifications > 0)
                    <span class="badge bg-danger ms-auto">{{ $unreadNotifications }}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('staff.inventory') }}" class="sidebar-menu-link {{ request()->routeIs('staff.inventory') ? 'active' : '' }}">
                    <i class="bi bi-box-seam sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Inventory</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                <div class="notification-badge">
                    <a href="{{ route('staff.alerts') }}" class="btn btn-light position-relative">
                        <i class="bi bi-bell"></i>
                        @if(isset($unreadNotifications) && $unreadNotifications > 0)
                        <span class="badge bg-danger">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                </div>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <a class="user-dropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const toggleIcon = document.getElementById('toggleIcon');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Change icon direction
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.className = 'bi bi-chevron-right';
            } else {
                toggleIcon.className = 'bi bi-chevron-left';
            }
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
        
        // Restore sidebar state from localStorage
        window.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleIcon.className = 'bi bi-chevron-right';
            }
        });
        
        // Mobile menu toggle
        if (window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-show');
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>