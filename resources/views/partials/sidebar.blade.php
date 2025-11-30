@php
    $currentSchool = $school ?? $activeSchool ?? null;
@endphp
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
@if($currentSchool && (Auth::user()->isSupervisor() || Auth::user()->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool)))
    <div class="sidebar-section-title mt-3">
        <i class="bi bi-collection"></i> {{ $currentSchool->name }}
    </div>
    <nav class="nav flex-column">
        <a href="{{ route('schools.classes.index', $currentSchool) }}" class="nav-link">
            <i class="bi bi-collection"></i> Klasės
        </a>
        <a href="{{ route('schools.login-keys.import', $currentSchool) }}" class="nav-link">
            <i class="bi bi-upload"></i> Importavimas
        </a>
        <a href="{{ route('schools.login-keys.index', $currentSchool) }}" class="nav-link">
            <i class="bi bi-key"></i> Raktai
        </a>
        <a href="{{ route('schools.subjects.index', $currentSchool) }}" class="nav-link">
            <i class="bi bi-journal-bookmark"></i> Dalykai
        </a>
        <a href="{{ route('schools.timetables.index', $currentSchool) }}" class="nav-link">
            <i class="bi bi-calendar3"></i> Tvarkaraščiai
        </a>
        <a href="{{ route('schools.rooms.index', $currentSchool) }}" class="nav-link">
            <i class="bi bi-door-closed"></i> Kabinetai
        </a>
    </nav>
@endif
