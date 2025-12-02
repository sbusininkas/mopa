@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-collection"></i> Klasės: {{ $school->name }}</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg"></i> Sukurti naują klasę
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($classes->isEmpty())
        <div class="modern-card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Nėra sukurtų klasių</p>
                </div>
            </div>
        </div>
    @else
        <div class="modern-table-wrapper">
            <table class="modern-table table table-hover">
                <thead>
                    <tr>
                        <th><i class="bi bi-tag"></i> Pavadinimas</th>
                        <th><i class="bi bi-text-paragraph"></i> Aprašymas</th>
                        <th><i class="bi bi-person"></i> Klasės vadovas</th>
                        <th><i class="bi bi-calendar-range"></i> Mokslo metai</th>
                        <th><i class="bi bi-people"></i> Mokinių</th>
                        <th><i class="bi bi-clock"></i> Sukurta</th>
                        <th class="text-end">Veiksmai</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($classes as $class)
                    <tr>
                        <td><strong>{{ $class->name }}</strong></td>
                        <td>{{ $class->description ? substr($class->description, 0, 50) . '...' : '-' }}</td>
                        <td>{{ $class->teacher ? $class->teacher->full_name : '-' }}</td>
                        <td>{{ $class->school_year ?: '-' }}</td>
                        <td>
                            <span class="badge badge-modern bg-primary">{{ $class->loginKeys()->where('type', 'student')->count() }}</span>
                        </td>
                        <td>{{ $class->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('schools.classes.show', [$school, $class]) }}" class="btn btn-outline-info" title="Peržiūrėti">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $class->id }}" title="Redaguoti">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $class->id }}" title="Ištrinti">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $class->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('schools.classes.update', [$school, $class]) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title">Redaguoti klasę</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Pavadinimas *</label>
                                            <input type="text" name="name" class="form-control" value="{{ $class->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Aprašymas</label>
                                            <textarea name="description" class="form-control" rows="3">{{ $class->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Klasės vadovas</label>
                                            <select name="teacher_id" class="form-select">
                                                <option value="">-- Nepasirinkta --</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ $class->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mokslo metai *</label>
                                            <select name="school_year" class="form-select" required>
                                                <option value="">-- Pasirinkite --</option>
                                                @foreach(\App\Helpers\SchoolYearHelper::getAvailableYears() as $year)
                                                    <option value="{{ $year }}" {{ $class->school_year === $year ? 'selected' : '' }}>{{ $year }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                        <button type="submit" class="btn btn-warning">Išsaugoti</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $class->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Pašalinti klasę</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    Ar tikrai norite pašalinti klasę <strong>{{ $class->name }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                    <form method="POST" action="{{ route('schools.classes.destroy', [$school, $class]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Pašalinti</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $classes->links() }}
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sukurti naują klasę</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('schools.classes.store', $school) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Klasės pavadinimas *</label>
                        <input type="text" name="name" class="form-control" required placeholder="pvz. 1A, 2B, 3C">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Aprašymas</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Iš viso mokinių, pagrindinė kryptis, etc."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Klasės vadovas</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">-- Nepasirinkta --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mokslo metai *</label>
                        <select name="school_year" class="form-select" required>
                            <option value="">-- Pasirinkite --</option>
                            @foreach(\App\Helpers\SchoolYearHelper::getAvailableYears() as $year)
                                <option value="{{ $year }}" {{ \App\Helpers\SchoolYearHelper::getCurrentYear() === $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="submit" class="btn btn-primary">Sukurti klasę</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Redaguoti klasę</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Klasės pavadinimas *</label>
                        <input type="text" id="editName" name="name" class="form-control" required placeholder="pvz. 1A, 2B, 3C">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Aprašymas</label>
                        <textarea id="editDescription" name="description" class="form-control" rows="3" placeholder="Iš viso mokinių, pagrindinė kryptis, etc."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Klasės vadovas</label>
                        <select name="teacher_id" id="editTeacherId" class="form-select">
                            <option value="">-- Nepasirinkta --</option>
                            @foreach($school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->orderBy('first_name')->get() as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mokslo metai *</label>
                        <select name="school_year" id="editSchoolYear" class="form-select" required>
                            <option value="">-- Pasirinkite --</option>
                            @php
                                $currentYear = date('Y');
                                for($i = 0; $i < 10; $i++) {
                                    $yearStart = $currentYear - $i;
                                    $yearEnd = $yearStart + 1;
                                    $yearLabel = $yearStart . '-' . $yearEnd;
                                    echo '<option value="' . $yearLabel . '">' . $yearLabel . '</option>';
                                }
                            @endphp
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="submit" class="btn btn-primary">Atnaujinti klasę</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patvirtinti šalinimą</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Ar tikrai norite ištrinti šią klasę? Šio veiksmo negalima atšaukti.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ištrinti</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Edit Modal Handler (generic modal; used only if present)
(function() {
    const modal = document.getElementById('editModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (e) {
        const button = e.relatedTarget;
        const action = button.getAttribute('data-action');
        const name = button.getAttribute('data-name');
        const description = button.getAttribute('data-description');
        const teacherId = button.getAttribute('data-teacher_id');
        const schoolYear = button.getAttribute('data-school_year');
        
        document.getElementById('editForm').action = action;
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description || '';
        document.getElementById('editTeacherId').value = teacherId || '';
        document.getElementById('editSchoolYear').value = schoolYear || '';
    });
})();

// Delete Modal Handler (generic modal; used only if present)
(function() {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (e) {
        const button = e.relatedTarget;
        const action = button.getAttribute('data-action');
        document.getElementById('deleteForm').action = action;
    });
})();
</script>
@endsection
