{{-- Supervisor School Management Sidebar (uses active school from session) --}}
@php
    $currentSchool = $school ?? $activeSchool ?? $currentSchool ?? null;
@endphp

@if($currentSchool)
    <div class="sidebar-section-title">
        <i class="bi bi-building"></i> {{ $currentSchool->name }}
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.dashboard') }}" class="nav-link {{ request()->routeIs('school.dashboard') || request()->routeIs('schools.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') || request()->routeIs('schools.classes.*') ? 'active' : '' }}">
            <i class="bi bi-collection"></i> Klasės
        </a>
        <a href="{{ route('import.index') }}" class="nav-link {{ request()->routeIs('import.*') || request()->routeIs('schools.login-keys.import') ? 'active' : '' }}">
            <i class="bi bi-upload"></i> Importavimas
        </a>
        <a href="{{ route('login-keys.index') }}" class="nav-link {{ request()->routeIs('login-keys.*') || request()->routeIs('schools.login-keys.*') ? 'active' : '' }}">
            <i class="bi bi-key"></i> Prisijungimo raktai
        </a>
        <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') || request()->routeIs('schools.subjects.*') ? 'active' : '' }}">
            <i class="bi bi-journal-bookmark"></i> Dalykai
        </a>
        <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*') || request()->routeIs('schools.timetables.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Tvarkaraščiai
        </a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') || request()->routeIs('schools.rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-closed"></i> Kabinetai
        </a>
    </nav>

    {{-- School Settings Section --}}
    <div class="sidebar-section-title mt-3">
        <i class="bi bi-gear"></i> Nustatymai
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.settings') }}" class="nav-link {{ request()->routeIs('school.settings') || request()->routeIs('schools.edit') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i> Mokyklos duomenys
        </a>
        <a href="{{ route('school.contacts') }}" class="nav-link {{ request()->routeIs('school.contacts') || request()->routeIs('schools.edit-contacts') ? 'active' : '' }}">
            <i class="bi bi-telephone"></i> Kontaktai
        </a>
    </nav>
@endif
