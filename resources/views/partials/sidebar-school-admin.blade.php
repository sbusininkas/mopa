{{-- School Administrator Sidebar --}}
@php
    $currentSchool = $school ?? $activeSchool ?? $currentSchool ?? null;
@endphp

@if($currentSchool)
    <div class="sidebar-section-title">
        <i class="bi bi-building-fill"></i> {{ Str::limit($currentSchool->name, 20) }}
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.dashboard') }}" class="nav-link {{ request()->routeIs('school.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
            <i class="bi bi-collection-fill"></i>
            <span>Klasės</span>
        </a>
        <a href="{{ route('import.index') }}" class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <i class="bi bi-cloud-upload-fill"></i>
            <span>Importavimas</span>
        </a>
        <a href="{{ route('login-keys.index') }}" class="nav-link {{ request()->routeIs('login-keys.*') ? 'active' : '' }}">
            <i class="bi bi-key-fill"></i>
            <span>Prisijungimo raktai</span>
        </a>
        <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
            <i class="bi bi-book-fill"></i>
            <span>Dalykai</span>
        </a>
        <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i>
            <span>Tvarkaraščiai</span>
        </a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-closed-fill"></i>
            <span>Kabinetai</span>
        </a>
    </nav>

    {{-- School Settings Section --}}
    <div class="sidebar-section-title mt-3">
        <i class="bi bi-gear-fill"></i> Nustatymai
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.settings') }}" class="nav-link {{ request()->routeIs('school.settings') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i>
            <span>Mokyklos duomenys</span>
        </a>
        <a href="{{ route('school.contacts') }}" class="nav-link {{ request()->routeIs('school.contacts') ? 'active' : '' }}">
            <i class="bi bi-telephone-fill"></i>
            <span>Kontaktai</span>
        </a>
    </nav>
@endif

