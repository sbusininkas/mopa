@php
    $active = $activeSchool ?? null;
    $user = Auth::user();
    if ($user->isSupervisor()) {
        $available = \App\Models\School::orderBy('name')->get();
    } else {
        $available = $user->schools()->orderBy('name')->get();
    }
@endphp

<div class="d-flex align-items-center me-3">
    <form method="POST" action="{{ route('schools.switch', ['school' => 0]) }}" id="switchSchoolForm" style="display:inline-block; margin-right:10px;">
        @csrf
    </form>

    <div class="me-3">
        <strong>Aktyvi mokykla:</strong>
        @if($active)
            <span class="badge bg-light text-dark ms-2">{{ $active->name }}</span>
        @else
            <span class="text-muted ms-2">(nepasirinkta)</span>
        @endif
    </div>

    <div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="schoolSwitchDropdown" data-bs-toggle="dropdown">
                Perjungti
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="schoolSwitchDropdown">
                @foreach($available as $school)
                    <li>
                        <form method="POST" action="{{ route('schools.switch', $school) }}">
                            @csrf
                            <button class="dropdown-item" type="submit">{{ $school->name }} @if(optional($active)->id === $school->id) (aktyvi) @endif</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
