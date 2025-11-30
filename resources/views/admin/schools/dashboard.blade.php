@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-building"></i> {{ $school->name }}</h2>
            <p class="text-muted">{{ $school->address }} | {{ $school->phone }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('schools.edit-contacts', $school) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Redaguoti kontaktus
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#classes">
                <i class="bi bi-collection"></i> Klasės
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#teachers">
                <i class="bi bi-briefcase"></i> Mokytojai
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#students">
                <i class="bi bi-people"></i> Mokiniai
            </a>
        </li>
        @if(auth()->user()->isSupervisor())
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#users">
                <i class="bi bi-person-gear"></i> Vartotojai
            </a>
        </li>
        @endif
    </ul>

    <div class="tab-content mt-3">
        <!-- Classes Tab -->
        <div class="tab-pane fade show active" id="classes">
            <div class="d-flex justify-content-between mb-3">
                <h4>Klasės</h4>
                <a href="{{ route('schools.classes.create', $school) }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Sukurti naują klasę
                </a>
            </div>

            @if($school->classes->isEmpty())
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
                                <th><i class="bi bi-people"></i> Mokinių</th>
                                <th class="text-end">Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($school->classes as $class)
                                <tr>
                                    <td><strong>{{ $class->name }}</strong></td>
                                    <td>{{ $class->description ? substr($class->description, 0, 50) . '...' : '-' }}</td>
                                    <td>
                                        <span class="badge badge-modern bg-primary">{{ $class->loginKeys()->where('type', 'student')->where('used', true)->count() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('schools.classes.edit', [$school, $class]) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal" 
                                                    data-action="{{ route('schools.classes.destroy', [$school, $class]) }}">
                                                <i class="bi bi-trash"></i>

                    <label class="form-label">Mokslo metai</label>
                    <select name="school_year" class="form-select">
                        <option value="">-- Pasirinkite --</option>
                        @foreach($schoolYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Pridėti</button>
            </form>
        </div>
    </div>
</div>

<!-- Display Key Modal -->
<div class="modal fade" id="displayKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patvirtinimo raktas sukurtas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="keyDisplayName" class="fw-bold mb-3"></p>
                <div class="alert alert-info">
                    <strong>Patvirtinimo raktas:</strong>
                    <div class="mt-2 p-3 bg-light rounded font-monospace" id="keyDisplayValue" style="word-break: break-all;"></div>
                </div>
                <p class="text-muted small">Šis raktas reikalingas registracijai. Išsaugokite jį saugiai.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
            </div>
        </div>
    </div>
</div>

<script>
// Add Teacher Form Handler
document.getElementById('addTeacherForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("schools.login-keys.store-teacher", $school) }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Display the key
            document.getElementById('keyDisplayName').textContent = data.teacher.first_name + ' ' + data.teacher.last_name;
            document.getElementById('keyDisplayValue').textContent = data.teacher.key;
            
            // Close add modal and show key modal
            const addTeacherModal = bootstrap.Modal.getInstance(document.getElementById('addTeacherModal'));
            if (addTeacherModal) addTeacherModal.hide();
            
            const displayModal = new bootstrap.Modal(document.getElementById('displayKeyModal'));
            displayModal.show();
            
            // Reset form
            this.reset();
            
            // Refresh page after closing
            document.getElementById('displayKeyModal').addEventListener('hidden.bs.modal', function() {
                location.reload();
            }, { once: true });
        } else {
            alert('Klaida: ' + (data.message || 'Nepavyko pridėti mokytojo'));
        }
    } catch (error) {
        alert('Klaida: ' + error.message);
    }
});

// Add Student Form Handler
document.getElementById('addStudentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("schools.login-keys.store-student", $school) }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Display the key
            document.getElementById('keyDisplayName').textContent = data.student.first_name + ' ' + data.student.last_name;
            document.getElementById('keyDisplayValue').textContent = data.student.key;
            
            // Close add modal and show key modal
            const addStudentModal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
            if (addStudentModal) addStudentModal.hide();
            
            const displayModal = new bootstrap.Modal(document.getElementById('displayKeyModal'));
            displayModal.show();
            
            // Reset form
            this.reset();
            
            // Refresh page after closing
            document.getElementById('displayKeyModal').addEventListener('hidden.bs.modal', function() {
                location.reload();
            }, { once: true });
        } else {
            alert('Klaida: ' + (data.message || 'Nepavyko pridėti mokinio'));
        }
    } catch (error) {
        alert('Klaida: ' + error.message);
    }
});

// Delete Modal Handler
document.getElementById('deleteModal').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    document.getElementById('deleteForm').action = action;
});
</script>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patvirtinti šalinimą</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Ar tikrai norite ištrinti šį elementą? Šio veiksmo negalima atšaukti.
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
@endsection
