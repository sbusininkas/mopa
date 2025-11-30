@extends('layouts.admin')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
            <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mt-2"><i class="bi bi-calendar3"></i> {{ $timetable->name }}</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('schools.timetables.teachers-view', [$school, $timetable]) }}">
                <i class="bi bi-people"></i> Mokytojų tvarkaraštis
            </a>
            <form method="POST" action="{{ route('timetables.add-random-groups', $timetable) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Ar tikrai norite pridėti atsitiktines grupes? Tai sukurs naujas grupes su mokiniais, mokytojais ir dalykais.')">
                    <i class="bi bi-shuffle"></i> Pridėti random grupes
                </button>
            </form>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timetableSettingsModal">
                <i class="bi bi-gear"></i> Nustatymai
            </button>
            <form method="POST" action="{{ route('timetables.generate', $timetable) }}" class="d-inline" id="generateForm">
                @csrf
                <button type="submit" class="btn btn-primary" id="generateBtn" @if($timetable->generation_status==='running') disabled @endif>
                    <span id="btnText">@if($timetable->generation_status==='running') Generuojama... @else Generuoti tvarkaraštį @endif</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-1" style="display: {{ $timetable->generation_status==='running' ? 'inline-block':'none' }};"></span>
                </button>
            </form>
        </div>
    </div>

    <!-- Current settings summary -->
    <div class="modern-card mb-3">
        <div class="modern-card-header"><i class="bi bi-sliders"></i> Dabartiniai nustatymai</div>
        <div class="card-body">
            @if($timetable->generation_status === 'running')
                <div class="mb-3">
                    <label class="form-label small text-muted">Generavimo eiga</label>
                    <div class="progress" style="height: 20px;">
                        <div id="generationProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $timetable->generation_progress }}%;">
                            {{ $timetable->generation_progress }}%
                        </div>
                    </div>
                </div>
            @elseif($timetable->generation_status === 'completed')
                <div class="alert alert-success py-2 mb-3">
                    <i class="bi bi-check-circle"></i> Tvarkaraštis sugeneruotas.
                    @php($report = $timetable->generation_report ?? [])
                    @php($uns = $report['unscheduled_count'] ?? 0)
                    @php($attempts = $report['attempts'] ?? 1)
                    @php($bestAttempt = $report['best_attempt'] ?? 1)
                    @php($placedUnits = $report['placed_units'] ?? 0)
                    @php($totalUnits = $report['total_units'] ?? 0)
                    
                    <span class="ms-2">
                        <span class="badge bg-info">{{ $attempts }} bandymai</span>
                        <span class="badge bg-primary">Geriausias: #{{ $bestAttempt }}</span>
                        <span class="badge bg-success">Įtraukta: {{ $placedUnits }}/{{ $totalUnits }}</span>
                    </span>
                    
                    @if($uns > 0)
                        <span class="ms-2 badge bg-warning text-dark">Nepaskirstyta: {{ $uns }}</span>
                    @endif
                </div>
            @elseif($timetable->generation_status === 'failed')
                <div class="alert alert-danger py-2 mb-3">
                    <i class="bi bi-exclamation-triangle"></i> Generavimas nepavyko. Bandykite dar kartą.
                </div>
            @endif
            <div class="row g-3">
                <div class="col-md-2">
                    <div class="small text-muted">Pirmadienis</div>
                    <div><strong>{{ $timetable->max_lessons_monday ?? 9 }}</strong></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Antradienis</div>
                    <div><strong>{{ $timetable->max_lessons_tuesday ?? 9 }}</strong></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Trečiadienis</div>
                    <div><strong>{{ $timetable->max_lessons_wednesday ?? 9 }}</strong></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Ketvirtadienis</div>
                    <div><strong>{{ $timetable->max_lessons_thursday ?? 9 }}</strong></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Penktadienis</div>
                    <div><strong>{{ $timetable->max_lessons_friday ?? 9 }}</strong></div>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted">Vieno dalyko/diena</div>
                    <div><strong>{{ $timetable->max_same_subject_per_day ?? 3 }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    @if($timetable->generation_status === 'completed' && ($timetable->generation_report['unscheduled_count'] ?? 0) > 0)
    <div class="modern-card mb-4">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <div><i class="bi bi-exclamation-circle"></i> Nepaskirstytos pamokos</div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#unscheduledCollapse">Peržiūrėti</button>
        </div>
        <div id="unscheduledCollapse" class="collapse show">
            <div class="card-body">
                @php($attempts = $timetable->generation_report['attempts'] ?? 5)
                @php($bestAttempt = $timetable->generation_report['best_attempt'] ?? 1)
                <p class="small text-muted mb-2">
                    Nepavyko priskirti visų pamokų po {{ $attempts }} bandymų. 
                    <strong>Rodomas geriausias variantas (bandymas #{{ $bestAttempt }})</strong> su mažiausiai konfliktų. 
                    Patikrinkite žemiau nurodytas konfliktų priežastis.
                </p>
                @php($reasonSummary = $timetable->generation_report['reason_summary_translated'] ?? $timetable->generation_report['reason_summary'] ?? [])
                <div class="row g-2 mb-3">
                    @foreach($reasonSummary as $rk => $rv)
                        @php($count = is_array($rv) ? ($rv['count'] ?? 0) : $rv)
                        @php($label = is_array($rv) ? ($rv['label'] ?? $rk) : $rk)
                        @if($count > 0)
                        <div class="col-auto">
                            <span class="badge bg-light text-dark">{{ $label }}: {{ $count }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="modern-table-wrapper">
                    <table class="modern-table table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Grupė</th>
                                <th>Dalykas</th>
                                <th>Mokytojas</th>
                                <th>Likę / Prašyta</th>
                                <th>Priežastys (top)</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach(($timetable->generation_report['unscheduled'] ?? []) as $item)
                            @php($reasonsTranslated = $item['reasons_translated'] ?? [])
                            @php($reasons = collect($reasonsTranslated)->filter(fn($v) => ($v['count'] ?? 0) > 0)->sortByDesc(fn($v) => $v['count'] ?? 0)->take(3))
                            <tr>
                                <td>{{ $item['group_name'] }}</td>
                                <td>{{ $item['subject_name'] }}</td>
                                <td>{{ $item['teacher_name'] ?? '—' }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $item['remaining_lessons'] }} / {{ $item['requested_lessons'] }}</span></td>
                                <td class="small">
                                    @forelse($reasons as $rk => $rv)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $rv['label'] ?? $rk }}: {{ $rv['count'] ?? 0 }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Settings Modal -->
    <div class="modal fade" id="timetableSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('schools.timetables.update', [$school, $timetable]) }}" class="modal-content">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger m-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear"></i> Tvarkaraščio nustatymai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 d-flex align-items-center gap-2">
                            <label class="form-label mb-0">Pavadinimas</label>
                            <input type="text" name="name" class="form-control" value="{{ $timetable->name }}" required>
                            <div class="form-check ms-2">
                                <input type="checkbox" name="is_public" id="isPublic" class="form-check-input" {{ $timetable->is_public ? 'checked' : '' }}>
                                <label for="isPublic" class="form-check-label">Viešas</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks. pamokų (Pirmadienis)</label>
                            <input type="number" name="max_lessons_monday" class="form-control" min="1" max="20" value="{{ $timetable->max_lessons_monday ?? 9 }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks. pamokų (Antradienis)</label>
                            <input type="number" name="max_lessons_tuesday" class="form-control" min="1" max="20" value="{{ $timetable->max_lessons_tuesday ?? 9 }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks. pamokų (Trečiadienis)</label>
                            <input type="number" name="max_lessons_wednesday" class="form-control" min="1" max="20" value="{{ $timetable->max_lessons_wednesday ?? 9 }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks. pamokų (Ketvirtadienis)</label>
                            <input type="number" name="max_lessons_thursday" class="form-control" min="1" max="20" value="{{ $timetable->max_lessons_thursday ?? 9 }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks. pamokų (Penktadienis)</label>
                            <input type="number" name="max_lessons_friday" class="form-control" min="1" max="20" value="{{ $timetable->max_lessons_friday ?? 9 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maks. to paties dalyko per dieną</label>
                            <input type="number" name="max_same_subject_per_day" class="form-control" min="1" max="20" value="{{ $timetable->max_same_subject_per_day ?? 3 }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Išsaugoti</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Group Creation Form -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <i class="bi bi-plus-circle"></i> Sukurti naują grupę
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('schools.timetables.groups.store', [$school, $timetable]) }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Grupės pavadinimas *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dalykas</label>
                    <select name="subject_id" class="form-select">
                        <option value="">-- Nepasirinkta --</option>
                        @foreach($school->subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mokytojas</label>
                    <select name="teacher_login_key_id" class="form-select">
                        <option value="">-- Nepasirinkta --</option>
                        @foreach($school->loginKeys()->where('type','teacher')->orderBy('last_name')->get() as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kabinetas</label>
                    <select name="room_id" class="form-select">
                        <option value="">-- Nepasirinkta --</option>
                        @foreach($school->rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->number }} {{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Savaitė</label>
                    <select name="week_type" class="form-select">
                        <option value="all">Visos</option>
                        <option value="even">Lyg.</option>
                        <option value="odd">Nelyg.</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Pam./sav.</label>
                    <input type="number" name="lessons_per_week" class="form-control" min="1" max="20" value="1" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_priority" id="isPriority" class="form-check-input" value="1">
                        <label class="form-check-label" for="isPriority" title="Prioritetinė pamoka (1-5 pamokos)">
                            <i class="bi bi-star"></i>
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg"></i> Pridėti grupę</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Groups List -->
    <div class="modern-card">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-list-ul"></i> Grupės
            </div>
            <div class="flex-grow-1 mx-3">
                <input type="text" id="groupSearch" class="form-control form-control-sm" placeholder="Ieškoti pagal grupę, dalyką, mokytoją arba mokinį...">
            </div>
        </div>
        <div class="card-body">
            <div id="groupsList">
                @forelse($groups as $group)
                            <div class="modern-card mb-2">
                                <div class="d-flex justify-content-between align-items-center py-2 px-3" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#groupCollapse{{ $group->id }}" aria-expanded="false">
                                    <div class="d-flex align-items-center gap-2">
                                        <strong>{{ $group->name }}</strong>
                                        <span class="badge bg-secondary">{{ $group->subject?->name }}</span>
                                        <span class="badge bg-info text-dark">{{ $group->teacherLoginKey?->full_name }}</span>
                                        @if($group->room)
                                            <span class="badge bg-dark">{{ $group->room->number }} {{ $group->room->name }}</span>
                                        @endif
                                        <span class="badge bg-light text-dark">{{ $group->week_type == 'all' ? 'Kiekv. savaitė' : ($group->week_type == 'even' ? 'Lyginės' : 'Nelyginės') }}</span>
                                        <span class="badge bg-primary">{{ $group->lessons_per_week }} pam./sav.</span>
                                        @if($group->is_priority)
                                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Prioritetinė</span>
                                        @endif
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editGroup{{ $group->id }}"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteGroup{{ $group->id }}"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div class="collapse" id="groupCollapse{{ $group->id }}">
                                    <div class="card-body border-top">
                                        <form method="POST" class="assign-form" action="{{ route('schools.timetables.groups.assign-students', [$school, $timetable, $group]) }}">
                                            @csrf
                                            <div class="row">
                                        <div class="col-md-5">
                                            <label class="form-label">Ieškoti mokinių</label>
                                            <input type="text" class="form-control mb-2" id="globalSearch{{ $group->id }}" placeholder="Įveskite vardą ar pavardę...">
                                            <div class="mt-2">
                                                <label class="form-label text-muted small">arba pasirinkite klasę</label>
                                                <select id="classSelect{{ $group->id }}" class="form-select">
                                                    <option value="">-- Pasirinkite klasę --</option>
                                                    @foreach($school->classes as $class)
                                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAll{{ $group->id }}">
                                                    <label class="form-check-label" for="selectAll{{ $group->id }}">Pažymėti visus</label>
                                                </div>
                                                <input type="text" class="form-control form-control-sm" id="filterInput{{ $group->id }}" placeholder="Filtruoti rezultatus">
                                            </div>
                                            <div class="mt-3" id="studentsList{{ $group->id }}">
                                                <p class="text-muted small">Ieškokite mokinio arba pasirinkite klasę.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="d-flex justify-content-end align-items-center mb-1">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="removeAll{{ $group->id }}"><i class="bi bi-x-circle"></i> Pašalinti visus</button>
                                            </div>
                                            <div class="modern-table-wrapper">
                                                <table class="modern-table table table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:40px"></th>
                                                            <th>Vardas</th>
                                                            <th>Klasė</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="assignedStudents{{ $group->id }}">
                                                        @foreach($group->students as $student)
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" name="login_key_ids[]" value="{{ $student->id }}" checked>
                                                                </td>
                                                                <td>{{ $student->full_name }}</td>
                                                                <td>{{ $student->class?->name }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="text-end mt-2">
                                                <button class="btn btn-primary btn-sm assign-submit" type="submit" data-loading-text="<span class='spinner-border spinner-border-sm me-1'></span>Saugoma..."><i class="bi bi-save"></i> Išsaugoti priskyrimus</button>
                                            </div>
                                            </div>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <!-- Edit Group Modal -->
                        <div class="modal fade" id="editGroup{{ $group->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Redaguoti grupę</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('schools.timetables.groups.update', [$school, $timetable, $group]) }}">
                                        <div class="modal-body">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">Pavadinimas</label>
                                                <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Dalykas</label>
                                                <select name="subject_id" class="form-select">
                                                    <option value="">-- Nepasirinkta --</option>
                                                    @foreach($school->subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ $group->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mokytojas</label>
                                                <select name="teacher_login_key_id" class="form-select">
                                                    <option value="">-- Nepasirinkta --</option>
                                                    @foreach($school->loginKeys()->where('type','teacher')->orderBy('last_name')->get() as $teacher)
                                                        <option value="{{ $teacher->id }}" {{ $group->teacher_login_key_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Kabinetas</label>
                                                <select name="room_id" class="form-select">
                                                    <option value="">-- Nepasirinkta --</option>
                                                    @foreach($school->rooms as $room)
                                                        <option value="{{ $room->id }}" {{ $group->room_id == $room->id ? 'selected' : '' }}>{{ $room->number }} {{ $room->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <label class="form-label">Savaitės tipas</label>
                                                    <select name="week_type" class="form-select" required>
                                                        <option value="all" {{ $group->week_type == 'all' ? 'selected' : '' }}>Kiekviena</option>
                                                        <option value="even" {{ $group->week_type == 'even' ? 'selected' : '' }}>Lyginės</option>
                                                        <option value="odd" {{ $group->week_type == 'odd' ? 'selected' : '' }}>Nelyginės</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Pamokų sk./sav.</label>
                                                    <input type="number" name="lessons_per_week" class="form-control" min="1" max="20" value="{{ $group->lessons_per_week ?? 1 }}" required>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_priority" id="editIsPriority{{ $group->id }}" class="form-check-input" value="1" {{ $group->is_priority ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="editIsPriority{{ $group->id }}">
                                                        <i class="bi bi-star"></i> Prioritetinė pamoka (1-5 pamokos)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                            <button type="submit" class="btn btn-warning">Išsaugoti</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Group Modal -->
                        <div class="modal fade" id="deleteGroup{{ $group->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Pašalinti grupę</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">Ar tikrai norite pašalinti grupę <strong>{{ $group->name }}</strong>?</div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                        <form method="POST" action="{{ route('schools.timetables.groups.destroy', [$school, $timetable, $group]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger">Pašalinti</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Nėra grupių</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Success toast
document.addEventListener('DOMContentLoaded', function() {
    // Enhance assignment forms with loading state
    const assignForms = document.querySelectorAll('.assign-form');
    assignForms.forEach(f => {
        f.addEventListener('submit', function() {
            const btn = f.querySelector('.assign-submit');
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = btn.getAttribute('data-loading-text');
        });
    });
    var toastEl = document.getElementById('toastSuccess');
    if (toastEl && window.bootstrap && bootstrap.Toast) {
        var toast = new bootstrap.Toast(toastEl, { delay: 2500 });
        toast.show();
    }
});

// Group search functionality
document.addEventListener('DOMContentLoaded', function() {
    const groupSearch = document.getElementById('groupSearch');
    const groupsList = document.getElementById('groupsList');
    const allGroups = groupsList ? Array.from(groupsList.querySelectorAll('.modern-card.mb-2')) : [];
    
    // Store group data for search
    const groupData = allGroups.map(card => {
        const badges = card.querySelectorAll('.badge');
        const groupName = card.querySelector('strong')?.textContent || '';
        const subject = badges[0]?.textContent || '';
        const teacher = badges[1]?.textContent || '';
        
        // Get students from assigned tbody
        const groupId = card.querySelector('[id^="assignedStudents"]')?.id.replace('assignedStudents', '');
        const studentRows = card.querySelectorAll('#assignedStudents' + groupId + ' tr');
        const students = Array.from(studentRows).map(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            return nameCell ? nameCell.textContent.trim() : '';
        });
        
        return {
            element: card,
            groupName: groupName.toLowerCase(),
            subject: subject.toLowerCase(),
            teacher: teacher.toLowerCase(),
            students: students.map(s => s.toLowerCase())
        };
    });
    
    if (groupSearch) {
    groupSearch.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        if (!query) {
            allGroups.forEach(card => card.style.display = '');
            return;
        }
        
        groupData.forEach(data => {
            const matchesGroup = data.groupName.includes(query);
            const matchesSubject = data.subject.includes(query);
            const matchesTeacher = data.teacher.includes(query);
            const matchesStudent = data.students.some(student => student.includes(query));
            
            if (matchesGroup || matchesSubject || matchesTeacher || matchesStudent) {
                data.element.style.display = '';
            } else {
                data.element.style.display = 'none';
            }
        });
    });
    }

    // Student assignment functionality per group
    @foreach($groups as $group)
    const globalSearch{{ $group->id }} = document.getElementById('globalSearch{{ $group->id }}');
    const classSelect{{ $group->id }} = document.getElementById('classSelect{{ $group->id }}');
    const studentsList{{ $group->id }} = document.getElementById('studentsList{{ $group->id }}');
    const assignedTbody{{ $group->id }} = document.getElementById('assignedStudents{{ $group->id }}');
    const filterInput{{ $group->id }} = document.getElementById('filterInput{{ $group->id }}');
    let searchTimeout{{ $group->id }} = null;

    // Guard against missing DOM elements
    if (!globalSearch{{ $group->id }} || !classSelect{{ $group->id }} || !studentsList{{ $group->id }} || !assignedTbody{{ $group->id }} || !filterInput{{ $group->id }}) {
        console.warn('Timetable group UI elements missing for group {{ $group->id }}');
        return;
    }

    // Global search across all students
    globalSearch{{ $group->id }}.addEventListener('input', function() {
        clearTimeout(searchTimeout{{ $group->id }});
        const query = this.value.trim();
        
        if (query.length < 2) {
            studentsList{{ $group->id }}.innerHTML = '<p class="text-muted small">Įveskite bent 2 simbolius...</p>';
            studentsList{{ $group->id }}.dataset.items = '[]';
            return;
        }

        searchTimeout{{ $group->id }} = setTimeout(async () => {
            try {
                studentsList{{ $group->id }}.innerHTML = '<div class="d-flex align-items-center gap-2 text-muted small"><span class="spinner-border spinner-border-sm"></span> Ieškoma...</div>';
                const res = await fetch(`{{ url('/admin/api/schools') }}/{{ $school->id }}/students/search?q=${encodeURIComponent(query)}`);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                const items = (json.data || []);
                studentsList{{ $group->id }}.dataset.items = JSON.stringify(items);
                studentsList{{ $group->id }}.dataset.source = 'global';
                renderStudents{{ $group->id }}(items);
                classSelect{{ $group->id }}.value = '';
            } catch (e) {
                studentsList{{ $group->id }}.innerHTML = '<div class="alert alert-danger small">Klaida ieškant</div>';
            }
        }, 300);
    });

    // Class selection
    classSelect{{ $group->id }}.addEventListener('change', async function() {
        const classId = this.value;
        globalSearch{{ $group->id }}.value = '';
        
        if (!classId) {
            studentsList{{ $group->id }}.innerHTML = '<p class="text-muted small">Ieškokite mokinio arba pasirinkite klasę.</p>';
            studentsList{{ $group->id }}.dataset.items = '[]';
            return;
        }
        try {
            classSelect{{ $group->id }}.disabled = true;
            studentsList{{ $group->id }}.innerHTML = '<div class="d-flex align-items-center gap-2 text-muted small"><span class="spinner-border spinner-border-sm"></span> Kraunama...</div>';
            const res = await fetch(`{{ url('/admin/api/classes') }}/${classId}/students`);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            const items = (json.data || []);
            studentsList{{ $group->id }}.dataset.items = JSON.stringify(items);
            studentsList{{ $group->id }}.dataset.source = 'class';
            renderStudents{{ $group->id }}(items);
            classSelect{{ $group->id }}.disabled = false;
        } catch (e) {
            studentsList{{ $group->id }}.innerHTML = '<div class="alert alert-danger small">Klaida kraunant</div>';
            classSelect{{ $group->id }}.disabled = false;
        }
    });

    // Render students list
    function renderStudents{{ $group->id }}(items) {
        const currentFilter = filterInput{{ $group->id }}.value.trim().toLowerCase();
        const toRender = currentFilter ? items.filter(s => (s.full_name || '').toLowerCase().includes(currentFilter)) : items;
        const html = toRender.map(s => `
            <div class="form-check">
                <input class="form-check-input student-checkbox" type="checkbox" value="${s.id}" data-name="${s.full_name}" data-class="${s.class_name || ''}" onchange="toggleAssign{{ $group->id }}(this)">
                <label class="form-check-label small">${s.full_name} <span class="text-muted">${s.class_name ? '(' + s.class_name + ')' : ''}</span></label>
            </div>
        `).join('');
        studentsList{{ $group->id }}.innerHTML = html || '<p class="text-muted small">Nerasta.</p>';
    }

    // Remove all assigned students
    const removeAllBtn{{ $group->id }} = document.getElementById('removeAll{{ $group->id }}');
    if (removeAllBtn{{ $group->id }}) {
        removeAllBtn{{ $group->id }}.addEventListener('click', function() {
            assignedTbody{{ $group->id }}.innerHTML = '';
            const checkboxes = studentsList{{ $group->id }}.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => { cb.checked = false; });
            const selAll = document.getElementById('selectAll{{ $group->id }}');
            if (selAll) selAll.checked = false;
        });
    }

    // Select all
    const selectAll{{ $group->id }} = document.getElementById('selectAll{{ $group->id }}');
    if (selectAll{{ $group->id }}) {
        selectAll{{ $group->id }}.addEventListener('change', function() {
            const checkboxes = studentsList{{ $group->id }}.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                if (cb.checked !== selectAll{{ $group->id }}.checked) {
                    cb.checked = selectAll{{ $group->id }}.checked;
                    toggleAssign{{ $group->id }}(cb);
                }
            });
        });
    }

    // Filter results
    filterInput{{ $group->id }}.addEventListener('input', function() {
        const items = JSON.parse(studentsList{{ $group->id }}.dataset.items || '[]');
        renderStudents{{ $group->id }}(items);
    });

    // Toggle assign student
    window['toggleAssign{{ $group->id }}'] = function(cb) {
        if (cb.checked) {
            const existing = assignedTbody{{ $group->id }}.querySelector(`input[value="${cb.value}"]`);
            if (existing) return;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox" name="login_key_ids[]" value="${cb.value}" checked></td>
                <td>${cb.dataset.name}</td>
                <td>${cb.dataset.class}</td>
            `;
            assignedTbody{{ $group->id }}.appendChild(row);
        } else {
            const input = assignedTbody{{ $group->id }}.querySelector(`input[value="${cb.value}"]`);
            if (input) input.closest('tr').remove();
        }
    }
    @endforeach
});
</script>
@endsection

@push('scripts')
<script>
// Poll progress if running
function startGenerationPolling() {
    const progressBar = document.getElementById('generationProgressBar');
    if (!progressBar) return;
    const poll = setInterval(() => {
        fetch('{{ route('timetables.generation-status', $timetable) }}')
            .then(r => r.json())
            .then(data => {
                if (data.progress != null) {
                    progressBar.style.width = data.progress + '%';
                    progressBar.textContent = data.progress + '%';
                }
                if (data.finished || data.status === 'failed') {
                    clearInterval(poll);
                    // Reload page to reflect final state & slots
                    setTimeout(()=> window.location.reload(), 800);
                }
            })
            .catch(e => console.error(e));
    }, 1500);
}
@if($timetable->generation_status==='running')
startGenerationPolling();
@endif

document.getElementById('generateForm')?.addEventListener('submit', function() {
    // After submission, create dynamic progress UI if not present
    if (!document.getElementById('generationProgressBar')) {
        const container = document.querySelector('.modern-card .card-body');
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';
        wrapper.innerHTML = `
            <label class="form-label small text-muted">Generavimo eiga</label>
            <div class="progress" style="height:20px;">
                <div id="generationProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%;">0%</div>
            </div>
        `;
        container.prepend(wrapper);
        startGenerationPolling();
    }
});

// Auto-open group edit modal if URL contains ?openGroupEdit={id}
document.addEventListener('DOMContentLoaded', function() {
    try {
        const params = new URLSearchParams(window.location.search);
        const openId = params.get('openGroupEdit');
        if (openId) {
            const modalEl = document.getElementById('editGroup' + openId);
            if (modalEl && window.bootstrap) {
                const m = new bootstrap.Modal(modalEl);
                m.show();
                // Optional: scroll into view
                const card = modalEl.closest('.modern-card');
                if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    } catch (e) { /* no-op */ }
});
</script>
@endpush
