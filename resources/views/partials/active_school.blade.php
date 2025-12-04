@php
    $active = $activeSchool ?? null;
    $user = Auth::user();
    if ($user->isSupervisor()) {
        // Supervisor sees all schools
        $available = \App\Models\School::orderBy('name')->get();
    } else {
        // School admin sees only schools where they are admin
        $available = $user->schools()->wherePivot('is_admin', 1)->orderBy('name')->get();
    }
@endphp

<div class="d-flex align-items-center me-3">
    <form method="POST" action="{{ route('schools.switch', ['school' => 0]) }}" id="switchSchoolForm" style="display:inline-block; margin-right:10px;">
        @csrf
    </form>

    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded px-3 py-2 me-2" style="backdrop-filter: blur(10px);">
        <i class="bi bi-building text-white me-2 fs-5"></i>
        <div>
            <div class="text-white-50 small" style="font-size: 0.75rem; line-height: 1;">Aktyvi mokykla</div>
            @if($active)
                <div class="text-white fw-bold" style="font-size: 0.95rem; line-height: 1.2;">{{ $active->name }}</div>
            @else
                <div class="text-white-50 fst-italic" style="font-size: 0.9rem;">(nepasirinkta)</div>
            @endif
        </div>
    </div>

    <div>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle shadow-sm" type="button" id="schoolSwitchDropdown" data-bs-toggle="dropdown" style="font-weight: 500;">
                <i class="bi bi-arrow-repeat me-1"></i> Perjungti
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow school-switch-dropdown" aria-labelledby="schoolSwitchDropdown">
                @foreach($available as $school)
                    <li>
                        <form method="POST" action="{{ route('schools.switch', $school) }}">
                            @csrf
                            <button class="dropdown-item d-flex align-items-center" type="submit">
                                <i class="bi bi-building me-2 {{ optional($active)->id === $school->id ? 'text-primary' : 'text-muted' }}"></i>
                                <span>{{ $school->name }}</span>
                                @if(optional($active)->id === $school->id)
                                    <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                @endif
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
