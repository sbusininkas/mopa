{{-- Supervisor School Management Sidebar (uses active school from session) --}}
@php
    $currentSchool = $school ?? $activeSchool ?? $currentSchool ?? null;
@endphp

@if($currentSchool)
    <div class="sidebar-section-title" title="{{ $currentSchool->name }}">
        <i class="bi bi-building-fill"></i>
        <span>{{ Str::limit($currentSchool->name, 20) }}</span>
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.dashboard') }}" class="nav-link {{ request()->routeIs('school.dashboard') || request()->routeIs('schools.dashboard') ? 'active' : '' }}" title="Dashboard">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') || request()->routeIs('schools.classes.*') ? 'active' : '' }}" title="Klasės">
            <i class="bi bi-collection-fill"></i>
            <span>Klasės</span>
        </a>
        <a href="{{ route('import.index') }}" class="nav-link {{ request()->routeIs('import.*') || request()->routeIs('schools.login-keys.import') ? 'active' : '' }}" title="Importavimas">
            <i class="bi bi-cloud-upload-fill"></i>
            <span>Importavimas</span>
        </a>
        <a href="{{ route('login-keys.index') }}" class="nav-link {{ request()->routeIs('login-keys.*') || request()->routeIs('schools.login-keys.*') ? 'active' : '' }}" title="Prisijungimo raktai">
            <i class="bi bi-key-fill"></i>
            <span>Prisijungimo raktai</span>
        </a>
        <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') || request()->routeIs('schools.subjects.*') ? 'active' : '' }}" title="Dalykai">
            <i class="bi bi-book-fill"></i>
            <span>Dalykai</span>
        </a>
        <a href="{{ route('timetables.index') }}" class="nav-link {{ request()->routeIs('timetables.*') || request()->routeIs('schools.timetables.*') ? 'active' : '' }}" title="Tvarkaraščiai">
            <i class="bi bi-calendar3"></i>
            <span>Tvarkaraščiai</span>
        </a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') || request()->routeIs('schools.rooms.*') ? 'active' : '' }}" title="Kabinetai">
            <i class="bi bi-door-closed-fill"></i>
            <span>Kabinetai</span>
        </a>
    </nav>

    {{-- School Settings Section --}}
    <div class="sidebar-section-title mt-3" title="Nustatymai">
        <i class="bi bi-gear-fill"></i>
        <span>Nustatymai</span>
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('school.settings') }}" class="nav-link {{ request()->routeIs('school.settings') || request()->routeIs('schools.edit') ? 'active' : '' }}" title="Mokyklos duomenys">
            <i class="bi bi-pencil-square"></i>
            <span>Mokyklos duomenys</span>
        </a>
        <a href="{{ route('school.contacts') }}" class="nav-link {{ request()->routeIs('school.contacts') || request()->routeIs('schools.edit-contacts') ? 'active' : '' }}" title="Kontaktai">
            <i class="bi bi-telephone-fill"></i>
            <span>Kontaktai</span>
        </a>
    </nav>
@endif
