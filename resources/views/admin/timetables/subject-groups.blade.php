@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-book-half"></i> {{ $subject }} — Grupės</h2>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="{{ route('schools.timetables.show', [$school, $timetable]) }}">
                <i class="bi bi-arrow-left"></i> Atgal į tvarkaraštį
            </a>
        </div>
    </div>

    @if(count($groups) === 0)
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Šiam dalykui nėra sudarytų grupių.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Grupė</th>
                        <th class="text-center" style="width: 120px;">Mokytojas</th>
                        <th class="text-center" style="width: 100px;">Kabinetas</th>
                        <th class="text-center" style="width: 80px;">Mokiniai</th>
                        <th class="text-center" style="width: 100px;">Suplanuota</th>
                        <th class="text-center" style="width: 100px;">Nesuplanuota</th>
                        <th class="text-center" style="width: 60px;">Veiksmai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td>
                                <a href="{{ route('schools.timetables.groups.details', [$school, $timetable, $group['id']]) }}" class="text-decoration-none">
                                    <strong>{{ $group['name'] }}</strong>
                                </a>
                            </td>
                            <td class="text-center">
                                @if($group['teacher_id'] && $group['teacher_name'])
                                    <a href="{{ route('schools.timetables.teacher', [$school, $timetable, $group['teacher_id']]) }}" 
                                       class="text-decoration-none link-primary"
                                       title="Atidaryti mokytojo tvarkaraštį">
                                        <small>{{ $group['teacher_name'] }}</small>
                                    </a>
                                @else
                                    <small>{{ $group['teacher_name'] ?? '—' }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($group['room_number'])
                                    <span class="badge bg-dark">{{ $group['room_number'] }}
                                        @if($group['room_name'])
                                            {{ $group['room_name'] }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $group['students_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $group['scheduled_count'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($group['unscheduled_count'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $group['unscheduled_count'] }}</span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('schools.timetables.groups.details', [$school, $timetable, $group['id']]) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="Atidaryti grupę">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5><i class="bi bi-info-circle"></i> Informacija</h5>
                <ul class="mb-0">
                    <li><strong>Iš viso grupių:</strong> {{ count($groups) }}</li>
                    <li><strong>Iš viso mokinių:</strong> {{ $groups->sum('students_count') }}</li>
                    <li><strong>Iš viso suplanuotų pamokų:</strong> {{ $groups->sum('scheduled_count') }}</li>
                    <li><strong>Iš viso nesuplanuotų pamokų:</strong> {{ $groups->sum('unscheduled_count') }}</li>
                </ul>
            </div>
        </div>
    @endif
</div>

<style>
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
@endsection
