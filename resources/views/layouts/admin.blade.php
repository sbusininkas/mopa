<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom-tables.css') }}">
    <!-- SimpleBar for reliable in-card scrolling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body {
            background-color: #f7f7ff;
        }
        .admin-container {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .admin-sidebar {
            width: 280px;
            background: white;
            border-radius: 8px;
            padding: 1.5rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .admin-sidebar .nav-link {
            padding: 0.75rem 1.5rem;
            color: #333;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .admin-sidebar .nav-link:hover {
            background-color: #f8f9fa;
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }
        .admin-sidebar .nav-link.active {
            background-color: #f0f0ff;
            border-left-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
        }
        .sidebar-section-title {
            padding: 1rem 1.5rem 0.5rem;
            font-weight: 700;
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .admin-content {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="/"><i class="bi bi-speedometer2"></i> Mokyklos Valdymas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item me-3">
                            @include('partials.active_school')
                        </li>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span><strong>Pranešimai</strong></span>
                                    <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.75rem;">
                                            Pažymėti visus
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notificationsList">
                                    <li class="text-center py-3 text-muted">
                                        <small>Kraunama...</small>
                                    </li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center small" href="{{ route('notifications.index') }}">
                                        Peržiūrėti visus pranešimus
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.my-schools') }}"><i class="bi bi-building"></i> Mano mokyklos</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.activate-key') }}"><i class="bi bi-key"></i> Suaktyvinti raktą</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="border: none; background: none; cursor: pointer;">
                                            <i class="bi bi-box-arrow-right"></i> Atsijungti
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        @auth
            @php
                $currentSchool = $school ?? $activeSchool ?? null;
            @endphp
            @if($currentSchool)
            <div class="admin-container">
                <!-- Sidebar -->
                <div class="admin-sidebar">
                    <!-- Sidebar -->
                    <div class="admin-sidebar">
                        @include('partials.sidebar')
                    </div>
                </div>

                <!-- Content -->
                <div class="admin-content">
                    @yield('content')
                </div>
            </div>
            @else
                <div class="mt-4">
                    @yield('content')
                </div>
            @endif
        @else
            <div class="mt-4">
                @yield('content')
            </div>
        @endauth
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    
    @auth
    <script>
        // Fetch unread notifications
        function fetchUnreadNotifications() {
            fetch('{{ route('notifications.unread') }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    const list = document.getElementById('notificationsList');
                    
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                        
                        let html = '';
                        data.notifications.forEach(notification => {
                            const message = notification.data.message || 'Naujas pranešimas';
                            html += `
                                <li>
                                    <a class="dropdown-item" href="{{ route('notifications.index') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small">${notification.type}</div>
                                                <div class="text-muted small">${message}</div>
                                            </div>
                                            <span class="badge bg-primary ms-2">Naujas</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.7rem;">${notification.created_at}</div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            `;
                        });
                        list.innerHTML = html;
                    } else {
                        badge.style.display = 'none';
                        list.innerHTML = `
                            <li class="text-center py-3 text-muted">
                                <small>Naujų pranešimų nėra</small>
                            </li>
                        `;
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }
        
        // Load notifications on page load
        fetchUnreadNotifications();
        
        // Poll every 30 seconds
        setInterval(fetchUnreadNotifications, 30000);
    </script>
    @endauth
    
    @stack('scripts')
</body>
</html>
