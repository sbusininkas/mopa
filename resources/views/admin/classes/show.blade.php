@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-collection"></i> Klasė: {{ $class->name }}</h2>
        <a href="{{ route('schools.classes.index', $school) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Atgal į klasių sąrašą
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-info-circle"></i> Informacija
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Pavadinimas:</dt>
                        <dd class="col-sm-7">{{ $class->name }}</dd>

                        <dt class="col-sm-5">Aprašymas:</dt>
                        <dd class="col-sm-7">{{ $class->description ?: '-' }}</dd>

                        <dt class="col-sm-5">Klasės vadovas:</dt>
                        <dd class="col-sm-7">{{ $class->teacher ? $class->teacher->full_name : '-' }}</dd>

                        <dt class="col-sm-5">Mokslo metai:</dt>
                        <dd class="col-sm-7">{{ $class->school_year ?: '-' }}</dd>

                        <dt class="col-sm-5">Mokinių:</dt>
                        <dd class="col-sm-7"><span class="badge bg-primary">{{ $students->count() }}</span></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="modern-card">
                <div class="modern-card-header d-flex justify-content-between align-items-center">
                    <div><i class="bi bi-people"></i> Mokiniai</div>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-plus-lg"></i> Pridėti mokinį
                    </button>
                </div>
                <div class="card-body">
                    @if($students->isEmpty())
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Nėra mokinių šioje klasėje</p>
                        </div>
                    @else
                        <div class="modern-table-wrapper">
                            <table class="modern-table table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Nr.</th>
                                        <th>Vardas</th>
                                        <th>Pavardė</th>
                                        <th>Prisijungimo raktas</th>
                                        <th class="text-end">Veiksmai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->first_name }}</td>
                                            <td>{{ $student->last_name }}</td>
                                            <td><code>{{ $student->key }}</code></td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStudent{{ $student->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteStudent{{ $student->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Edit Student Modal -->
                                        <div class="modal fade" id="editStudent{{ $student->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('schools.login-keys.update-student', [$school, $student]) }}">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning text-dark">
                                                            <h5 class="modal-title">Redaguoti mokinį</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Vardas *</label>
                                                                <input type="text" name="first_name" class="form-control" value="{{ $student->first_name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Pavardė *</label>
                                                                <input type="text" name="last_name" class="form-control" value="{{ $student->last_name }}" required>
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

                                        <!-- Delete Student Modal -->
                                        <div class="modal fade" id="deleteStudent{{ $student->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Pašalinti mokinį</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Ar tikrai norite pašalinti mokinį <strong>{{ $student->full_name }}</strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                                        <form method="POST" action="{{ route('schools.login-keys.destroy', [$school, $student]) }}" class="d-inline">
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('schools.login-keys.store-student', $school) }}">
            @csrf
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Pridėti naują mokinį</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Vardas *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pavardė *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> Prisijungimo raktas bus sugeneruotas automatiškai.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="submit" class="btn btn-success">Pridėti</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
