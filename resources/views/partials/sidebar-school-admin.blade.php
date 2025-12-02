{{-- School Administrator Sidebar --}}
@php
    $currentSchool = $school ?? $activeSchool ?? $currentSchool ?? null;
@endphp

@if($currentSchool)
    <div class="sidebar-section-title">
        <i class="bi bi-building"></i> {{ $currentSchool->name }}
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.dashboard') }}" class="nav-link {{ request()->routeIs('school.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
            <i class="bi bi-collection"></i> Klasės
        </a>
        <a href="{{ route('import.index') }}" class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <i class="bi bi-upload"></i> Importavimas
        </a>
        <a href="{{ route('login-keys.index') }}" class="nav-link {{ request()->routeIs('login-keys.*') ? 'active' : '' }}">
            <i class="bi bi-key"></i> Prisijungimo raktai
        </a>
        <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
            <i class="bi bi-journal-bookmark"></i> Dalykai
        </a>
        <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Tvarkaraščiai
        </a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-closed"></i> Kabinetai
        </a>
    </nav>

    {{-- School Settings Section --}}
    <div class="sidebar-section-title mt-3">
        <i class="bi bi-gear"></i> Nustatymai
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.settings') }}" class="nav-link {{ request()->routeIs('school.settings') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i> Mokyklos duomenys
        </a>
        <a href="{{ route('school.contacts') }}" class="nav-link {{ request()->routeIs('school.contacts') ? 'active' : '' }}">
            <i class="bi bi-telephone"></i> Kontaktai
        </a>
    </nav>
@endif

