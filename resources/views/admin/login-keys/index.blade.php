@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-key"></i> Prisijungimo raktai: {{ $school->name }}</h2>
        <div>
            <a href="{{ route('schools.login-keys.import', $school) }}" class="btn btn-success">
                <i class="bi bi-upload"></i> Importuoti
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                <i class="bi bi-plus-circle"></i> Sukurti raktą
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Bulk Actions Card (hidden by default) -->
    <div class="modern-card border-primary" id="bulkActionsCard" style="display: none;">
        <div class="modern-card-header">
            <i class="bi bi-check-square"></i> Pasirinkta: <span id="selectedCount">0</span>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-warning me-2" id="bulkRegenerateBtn">
                <i class="bi bi-arrow-repeat"></i> Regeneruoti pasirinktus
            </button>
            <button type="button" class="btn btn-danger" id="bulkDeleteBtn">
                <i class="bi bi-trash"></i> Ištrinti pasirinktus
            </button>
        </div>
    </div>

    <!-- Export / Filter section -->
    <div class="filter-export-block mb-4">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-filter me-2"></i>
            <h5 class="mb-0">Filtrai ir eksportavimas</h5>
        </div>
        <form method="GET" action="{{ route('schools.login-keys.export-pdf', $school) }}" class="filter-export-controls" id="exportForm">
            <div>
                <label class="form-label">Tipas</label>
                <select name="type" id="typeFilter" class="form-select">
                    <option value="">-- Visi --</option>
                    <option value="student" {{ request('type') === 'student' ? 'selected' : '' }}>Mokiniai</option>
                    <option value="teacher" {{ request('type') === 'teacher' ? 'selected' : '' }}>Mokytojai</option>
                </select>
            </div>
            <div id="classFilterWrapper">
                <label class="form-label" id="secondFilterLabel">Klasė</label>
                <select name="class_id" id="classFilter" class="form-select">
                    <option value="">-- Visos klasės --</option>
                    @foreach($school->classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                <select name="teacher_id" id="teacherFilter" class="form-select" style="display: none;">
                    <option value="">-- Visi mokytojai --</option>
                    @foreach($school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->get() as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="schoolYearFilterWrapper">
                <label class="form-label">Mokslo metai</label>
                <select name="school_year" id="schoolYearFilter" class="form-select">
                    <option value="">-- Visi metai --</option>
                    @php
                        $years = $loginKeys->pluck('school_year')->unique()->filter()->sort()->reverse();
                    @endphp
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('school_year') === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">&nbsp;</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="show_email" id="showEmailCheckbox" value="1" {{ request('show_email') ? 'checked' : '' }}>
                    <label class="form-check-label" for="showEmailCheckbox">
                        Rodyti el. paštą
                    </label>
                </div>
            </div>
            <div>
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-info">
                    <i class="bi bi-file-pdf"></i> Eksportuoti PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Search section -->
    <div class="modern-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('schools.login-keys.search', $school) }}" class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="q" class="form-control" placeholder="Ieškoti pagal raktą, vardą, pavardę arba el. paštą..." 
                           value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="bi bi-search"></i> Ieškoti
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Keys table -->
    @if($loginKeys->isEmpty())
        <div class="modern-card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Nėra prisijungimo raktų</p>
                </div>
            </div>
        </div>
    @else
        <div class="modern-table-wrapper">
            <table class="modern-table table table-hover" id="keysTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th><i class="bi bi-key"></i> Raktas</th>
                        <th><i class="bi bi-tag"></i> Tipas</th>
                        <th><i class="bi bi-person"></i> Vardas</th>
                        <th><i class="bi bi-envelope"></i> El. paštas</th>
                        <th><i class="bi bi-collection"></i> Klasė</th>
                        <th><i class="bi bi-person-badge"></i> Klasės vadovas</th>
                        <th><i class="bi bi-check-circle"></i> Būsena</th>
                        <th><i class="bi bi-person-circle"></i> Vartotojas</th>
                        <th class="text-end">Veiksmai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loginKeys as $key)
                        <tr>
                            <td>
                                @if(!$key->used)
                                    <input type="checkbox" class="form-check-input key-checkbox" value="{{ $key->id }}">
                                @endif
                            </td>
                            <td>
                                <code class="bg-light p-2">{{ $key->key }}</code>
                            </td>
                            <td>
                                <span class="badge badge-modern {{ $key->type === 'student' ? 'bg-success' : 'bg-info' }}">
                                    {{ $key->type === 'student' ? 'Mokinys' : 'Mokytojas' }}
                                </span>
                            </td>
                            <td>{{ $key->first_name }} {{ $key->last_name }}</td>
                            <td>{{ $key->email ?: '-' }}</td>
                            <td>{{ $key->class ? $key->class->name : '-' }}</td>
                            <td>
                                @if($key->type === 'student')
                                    {{-- Mokiniui rodyti klasės vadovą --}}
                                    {{ $key->class && $key->class->teacher ? $key->class->teacher->full_name : '-' }}
                                @else
                                    {{-- Mokytojui rodyti kokių klasių jis vadovas --}}
                                    @if($key->leadingClasses && $key->leadingClasses->count() > 0)
                                        {{ $key->leadingClasses->pluck('name')->join(', ') }}
                                    @else
                                        -
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-modern {{ $key->used ? 'bg-success' : 'bg-warning' }}">
                                    {{ $key->used ? 'Naudotas' : 'Nenaudotas' }}
                                </span>
                            </td>
                            <td>
                                @if($key->user)
                                    <a href="{{ route('users.edit', $key->user) }}" title="Peržiūrėti vartotoją">
                                        {{ $key->user->name }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$key->used)
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-warning regenerate-single" 
                                                data-id="{{ $key->id }}" 
                                                data-url="{{ route('schools.login-keys.regenerate', [$school, $key]) }}"
                                                title="Regeneruoti raktą">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger delete-single" 
                                                data-id="{{ $key->id }}"
                                                data-url="{{ route('schools.login-keys.destroy', [$school, $key]) }}"
                                                title="Ištrinti raktą">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $loginKeys->links() }}
    @endif

    <!-- Create Key Modal -->
    <div class="modal fade" id="createKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Sukurti naują raktą
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipas *</label>
                        <select id="createType" class="form-select" required>
                            <option value="">-- Pasirinkite --</option>
                            <option value="student">Mokinys</option>
                            <option value="teacher">Mokytojas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vardas *</label>
                        <input type="text" id="createFirstName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pavardė *</label>
                        <input type="text" id="createLastName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">El. paštas</label>
                        <input type="email" id="createEmail" class="form-control">
                    </div>
                    <div class="mb-3" id="createClassDiv" style="display: none;">
                        <label class="form-label">Klasė *</label>
                        <select id="createClassId" class="form-select">
                            <option value="">-- Pasirinkite --</option>
                            @foreach($school->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mokslo metai</label>
                        <select id="createSchoolYear" class="form-select">
                            <option value="">-- Pasirinkite --</option>
                            @php
                                $currentYear = date('Y');
                                for($i = 0; $i < 10; $i++) {
                                    $yearStart = $currentYear - $i;
                                    $yearEnd = $yearStart + 1;
                                    $yearLabel = $yearStart . '-' . $yearEnd;
                                    echo '<option value="' . $yearLabel . '"' . ($i === 0 ? ' selected' : '') . '>' . $yearLabel . '</option>';
                                }
                            @endphp
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-primary" id="createKeyBtn">Sukurti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Sėkmė
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="successMessage"></p>
                    <div id="keyDisplay" style="display: none;">
                        <strong>Sugeneruotas raktas:</strong>
                        <div class="alert alert-info mt-2">
                            <code class="fs-4" id="generatedKey"></code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successReloadBtn">Gerai</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Klaida
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-question-circle"></i> Patvirtinimas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-primary" id="confirmBtn">Patvirtinti</button>
                </div>
            </div>
        </div>
    </div>

<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Helper functions for modals
function showSuccess(message, key = null, reload = true) {
    document.getElementById('successMessage').textContent = message;
    
    if (key) {
        document.getElementById('generatedKey').textContent = key;
        document.getElementById('keyDisplay').style.display = 'block';
    } else {
        document.getElementById('keyDisplay').style.display = 'none';
    }
    
    if (reload) {
        document.getElementById('successReloadBtn').onclick = () => {
            window.location.href = window.location.href.split('?')[0];
        };
    } else {
        document.getElementById('successReloadBtn').onclick = () => {
            bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
        };
    }
    
    new bootstrap.Modal(document.getElementById('successModal')).show();
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    new bootstrap.Modal(document.getElementById('errorModal')).show();
}

function showConfirm(message, callback) {
    document.getElementById('confirmMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    
    document.getElementById('confirmBtn').onclick = () => {
        modal.hide();
        callback();
    };
    
    modal.show();
}

// Type filter change - switch between class and teacher filters
document.getElementById('typeFilter')?.addEventListener('change', function() {
    const type = this.value;
    const classFilter = document.getElementById('classFilter');
    const teacherFilter = document.getElementById('teacherFilter');
    const schoolYearWrapper = document.getElementById('schoolYearFilterWrapper');
    const label = document.getElementById('secondFilterLabel');
    
    if (type === 'teacher') {
        classFilter.style.display = 'none';
        classFilter.disabled = true;
        teacherFilter.style.display = 'block';
        teacherFilter.disabled = false;
        schoolYearWrapper.style.display = 'none';
        document.getElementById('schoolYearFilter').disabled = true;
        label.textContent = 'Mokytojas';
    } else if (type === 'student') {
        classFilter.style.display = 'block';
        classFilter.disabled = false;
        teacherFilter.style.display = 'none';
        teacherFilter.disabled = true;
        schoolYearWrapper.style.display = 'block';
        document.getElementById('schoolYearFilter').disabled = false;
        label.textContent = 'Klasė';
    } else {
        classFilter.style.display = 'block';
        classFilter.disabled = false;
        teacherFilter.style.display = 'none';
        teacherFilter.disabled = true;
        schoolYearWrapper.style.display = 'block';
        document.getElementById('schoolYearFilter').disabled = false;
        label.textContent = 'Klasė';
    }
});

// Initialize filter visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.dispatchEvent(new Event('change'));
    }
});

// Create key type change
document.getElementById('createType')?.addEventListener('change', function() {
    const classDiv = document.getElementById('createClassDiv');
    if (this.value === 'student') {
        classDiv.style.display = 'block';
        document.getElementById('createClassId').required = true;
    } else {
        classDiv.style.display = 'none';
        document.getElementById('createClassId').required = false;
    }
});

// Create key button
document.getElementById('createKeyBtn')?.addEventListener('click', async function() {
    const type = document.getElementById('createType').value;
    const firstName = document.getElementById('createFirstName').value;
    const lastName = document.getElementById('createLastName').value;
    const email = document.getElementById('createEmail').value;
    const classId = document.getElementById('createClassId').value;
    const schoolYear = document.getElementById('createSchoolYear').value;
    
    if (!type || !firstName || !lastName) {
        showError('Prašome užpildyti visus privalomus laukus');
        return;
    }
    
    if (type === 'student' && !classId) {
        showError('Prašome pasirinkti klasę mokiniui');
        return;
    }
    
    const url = type === 'student' 
        ? '{{ route("schools.login-keys.store-student", $school) }}'
        : '{{ route("schools.login-keys.store-teacher", $school) }}';
    
    const data = { first_name: firstName, last_name: lastName, email: email, school_year: schoolYear };
    if (type === 'student') {
        data.class_id = classId;
    }
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            bootstrap.Modal.getInstance(document.getElementById('createKeyModal')).hide();
            showSuccess(result.message, result.key, true);
        } else {
            showError(result.message || 'Įvyko klaida kuriant raktą');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Įvyko klaida kuriant raktą');
    }
});

// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.key-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

// Update bulk actions visibility
document.querySelectorAll('.key-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const checked = document.querySelectorAll('.key-checkbox:checked');
    const bulkCard = document.getElementById('bulkActionsCard');
    const countSpan = document.getElementById('selectedCount');
    
    if (checked.length > 0) {
        bulkCard.style.display = 'block';
        countSpan.textContent = checked.length;
    } else {
        bulkCard.style.display = 'none';
    }
}

// Bulk regenerate
document.getElementById('bulkRegenerateBtn')?.addEventListener('click', function() {
    const checked = Array.from(document.querySelectorAll('.key-checkbox:checked')).map(cb => cb.value);
    
    if (checked.length === 0) {
        showError('Nepasirinkta jokių raktų');
        return;
    }
    
    showConfirm(`Ar tikrai norite regeneruoti ${checked.length} raktų?`, async () => {
        try {
            const response = await fetch('{{ route("schools.login-keys.bulk-regenerate", $school) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: checked })
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                showSuccess(result.message, null, true);
            } else {
                showError(result.message || 'Įvyko klaida regeneruojant raktus');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Įvyko klaida regeneruojant raktus');
        }
    });
});

// Bulk delete
document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
    const checked = Array.from(document.querySelectorAll('.key-checkbox:checked')).map(cb => cb.value);
    
    if (checked.length === 0) {
        showError('Nepasirinkta jokių raktų');
        return;
    }
    
    showConfirm(`Ar tikrai norite ištrinti ${checked.length} raktų? Šio veiksmo negalima atšaukti.`, async () => {
        try {
            const response = await fetch('{{ route("schools.login-keys.bulk-delete", $school) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: checked })
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                showSuccess(result.message, null, true);
            } else {
                showError(result.message || 'Įvyko klaida trinant raktus');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Įvyko klaida trinant raktus');
        }
    });
});

// Single regenerate
document.querySelectorAll('.regenerate-single').forEach(btn => {
    btn.addEventListener('click', async function() {
        const url = this.dataset.url;
        const id = this.dataset.id;
        
        showConfirm('Ar tikrai norite regeneruoti šį raktą?', async () => {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showSuccess(result.message, result.key, true);
                } else {
                    showError(result.message || 'Įvyko klaida regeneruojant raktą');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Įvyko klaida regeneruojant raktą');
            }
        });
    });
});

// Single delete
document.querySelectorAll('.delete-single').forEach(btn => {
    btn.addEventListener('click', async function() {
        const url = this.dataset.url;
        const id = this.dataset.id;
        
        showConfirm('Ar tikrai norite ištrinti šį raktą? Šio veiksmo negalima atšaukti.', async () => {
            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showSuccess(result.message, null, true);
                } else {
                    showError(result.message || 'Įvyko klaida trinant raktą');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Įvyko klaida trinant raktą');
            }
        });
    });
});
</script>
@endsection
