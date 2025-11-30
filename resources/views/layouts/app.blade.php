<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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

    <!-- Active School Management Bar -->
    @auth
        @php
            $currentSchool = $activeSchool ?? null;
        @endphp
        @if($currentSchool && (Auth::user()->isSupervisor() || Auth::user()->isSchoolAdmin($currentSchool->id)))
        <div class="admin-container">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <!-- Supervisor Points -->
                @if(Auth::user()->isSupervisor())
                    <div class="sidebar-section-title">
                        <i class="bi bi-shield-lock"></i> Administratorius
                    </div>
                    <nav class="nav flex-column">
                        <a href="{{ route('schools.index') }}" class="nav-link">
                            <i class="bi bi-building"></i> Mokyklos
                        </a>
                        <a href="{{ route('users.index') }}" class="nav-link">
                            <i class="bi bi-person-gear"></i> Vartotojai
                        </a>
                    </nav>
                @endif

                <!-- Active School Management -->
                <div class="sidebar-section-title mt-3">
                    <i class="bi bi-collection"></i> {{ $currentSchool->name }}
                </div>
                <nav class="nav flex-column">
                    <a href="{{ route('schools.dashboard', $currentSchool) }}" class="nav-link">
                        <i class="bi bi-speedometer2"></i> Valdymas
                    </a>
                    <a href="{{ route('schools.classes.index', $currentSchool) }}" class="nav-link">
                        <i class="bi bi-collection"></i> Klasės
                    </a>
                    <a href="{{ route('schools.login-keys.import', $currentSchool) }}" class="nav-link">
                        <i class="bi bi-upload"></i> Importavimas
                    </a>
                    <a href="{{ route('schools.login-keys.index', $currentSchool) }}" class="nav-link">
                        <i class="bi bi-key"></i> Raktai
                    </a>
                </nav>
            </div>

            <!-- Content -->
            <div class="admin-content">
                @yield('content')
            </div>
        </div>
        @else
            <div class="container-fluid mt-4">
                @yield('content')
            </div>
        @endif
    @else
        <div class="container-fluid mt-4">
            @yield('content')
        </div>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
