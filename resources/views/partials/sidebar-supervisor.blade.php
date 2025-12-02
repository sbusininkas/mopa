{{-- Supervisor (System Administrator) Sidebar --}}
<div class="sidebar-section-title">
    <i class="bi bi-shield-lock"></i> Administratorius
</div>
<nav class="nav flex-column">
    <a href="{{ route('schools.index') }}" class="nav-link {{ request()->routeIs('schools.index') ? 'active' : '' }}">
        <i class="bi bi-building"></i> Mokyklos
    </a>
    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <i class="bi bi-person-gear"></i> Vartotojai
    </a>
</nav>
