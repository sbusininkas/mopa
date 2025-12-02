{{-- Supervisor (System Administrator) Sidebar --}}
<div class="sidebar-section-title">
    <i class="bi bi-shield-check-fill"></i> Sistemos Valdymas
</div>
<nav class="nav flex-column">
    <a href="{{ route('schools.index') }}" class="nav-link {{ request()->routeIs('schools.index') ? 'active' : '' }}">
        <i class="bi bi-buildings-fill"></i>
        <span>Mokyklos</span>
    </a>
    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <i class="bi bi-people-fill"></i>
        <span>Vartotojai</span>
    </a>
</nav>
