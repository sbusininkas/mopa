@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-door-closed"></i> Kabinetai - {{ $school->name }}</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> Importavimo klaidos:
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-plus-circle"></i> Pridėti naują kabinetą
                </div>
                <div class="card-body">
                    <form id="addRoomForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Numeris *</label>
                                <input type="text" id="roomNumber" class="form-control" placeholder="pvz. 358" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Pavadinimas *</label>
                                <input type="text" id="roomName" class="form-control" placeholder="pvz. Informatika" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Pridėti
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-upload"></i> Importuoti iš Excel
                </div>
                <div class="card-body">
                    <a href="{{ route('schools.rooms.import', $school) }}" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-excel"></i> Importuoti kabinetus
                    </a>
                    <small class="text-muted d-block mt-2">
                        Formatas: 1 stulpelis - numeris, 2 stulpelis - pavadinimas
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Kabinetų sąrašas</h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Example filter input, add more as needed -->
                    <div class="form-check me-2">
                        <input type="checkbox" class="form-check-input" id="showOnlyFreeRooms">
                        <label class="form-check-label" for="showOnlyFreeRooms">Tik laisvi</label>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download"></i> Eksportuoti
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                        <i class="bi bi-trash"></i> Pašalinti pažymėtus
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <form id="bulkDeleteForm">
                <div class="modern-table-wrapper">
                    <table class="modern-table table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th style="width: 150px;"><i class="bi bi-hash"></i> Numeris</th>
                                <th><i class="bi bi-tag"></i> Pavadinimas</th>
                                <th style="width: 200px;" class="text-end">Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rooms as $room)
                                <tr data-room-id="{{ $room->id }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="room-checkbox form-check-input" value="{{ $room->id }}">
                                    </td>
                                    <td>
                                        <span class="room-number fw-medium" data-id="{{ $room->id }}">{{ $room->number }}</span>
                                    </td>
                                    <td>
                                        <span class="room-name" data-id="{{ $room->id }}">{{ $room->name }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-warning edit-btn" 
                                                    data-id="{{ $room->id }}" 
                                                    data-number="{{ $room->number }}"
                                                    data-name="{{ $room->name }}">
                                                <i class="bi bi-pencil"></i> Redaguoti
                                            </button>
                                            <button type="button" class="btn btn-outline-danger delete-btn" 
                                                    data-id="{{ $room->id }}" 
                                                    data-number="{{ $room->number }}">
                                                <i class="bi bi-trash"></i> Trinti
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <p>Nėra sukurtų kabinetų</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Redaguoti kabinetą
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editRoomId">
                    <div class="mb-3">
                        <label class="form-label">Numeris *</label>
                        <input type="text" id="editNumber" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pavadinimas *</label>
                        <input type="text" id="editName" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <button type="button" class="btn btn-warning" id="saveEditBtn">Išsaugoti</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Patvirtinti šalinimą
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Ar tikrai norite pašalinti kabinetą <strong id="deleteRoomNumber"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Pašalinti</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Patvirtinti šalinimą
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Ar tikrai norite pašalinti <strong id="bulkDeleteCount"></strong> pažymėtus kabinetus?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">Pašalinti</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    let deleteId = null;

    // Add room form
    document.getElementById('addRoomForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const number = document.getElementById('roomNumber').value;
        const name = document.getElementById('roomName').value;
        
        try {
            const response = await fetch('{{ route('schools.rooms.store', $school) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ number, name })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Klaida kuriant kabinetą');
            }
        } catch (error) {
            alert('Klaida kuriant kabinetą');
        }
    });

    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const number = this.dataset.number;
            const name = this.dataset.name;
            
            document.getElementById('editRoomId').value = id;
            document.getElementById('editNumber').value = number;
            document.getElementById('editName').value = name;
            
            editModal.show();
        });
    });

    // Save edit
    document.getElementById('saveEditBtn').addEventListener('click', async function() {
        const id = document.getElementById('editRoomId').value;
        const number = document.getElementById('editNumber').value;
        const name = document.getElementById('editName').value;
        
        try {
            const response = await fetch(`/admin/schools/{{ $school->id }}/rooms/${id}/edit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ number, name })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update DOM
                const numberSpan = document.querySelector(`.room-number[data-id="${id}"]`);
                const nameSpan = document.querySelector(`.room-name[data-id="${id}"]`);
                const editBtn = document.querySelector(`.edit-btn[data-id="${id}"]`);
                const deleteBtn = document.querySelector(`.delete-btn[data-id="${id}"]`);
                
                if (numberSpan) numberSpan.textContent = number;
                if (nameSpan) nameSpan.textContent = name;
                if (editBtn) {
                    editBtn.setAttribute('data-number', number);
                    editBtn.setAttribute('data-name', name);
                }
                if (deleteBtn) deleteBtn.setAttribute('data-number', number);
                
                editModal.hide();
            } else {
                alert(data.message || 'Klaida redaguojant kabinetą');
            }
        } catch (error) {
            alert('Klaida redaguojant kabinetą');
        }
    });

    // Delete buttons
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.dataset.id;
            const number = this.dataset.number;
            document.getElementById('deleteRoomNumber').textContent = number;
            deleteModal.show();
        });
    });

    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
        if (!deleteId) return;
        
        try {
            const response = await fetch(`/admin/schools/{{ $school->id }}/rooms/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const row = document.querySelector(`tr[data-room-id="${deleteId}"]`);
                if (row) row.remove();
                updateBulkDeleteBtn();
                deleteModal.hide();
            }
        } catch (error) {
            alert('Klaida šalinant kabinetą');
        }
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.room-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
        updateBulkDeleteBtn();
    });

    // Individual checkboxes
    document.querySelectorAll('.room-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteBtn);
    });

    // Bulk delete button
    document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
        const checked = document.querySelectorAll('.room-checkbox:checked');
        document.getElementById('bulkDeleteCount').textContent = checked.length;
        bulkDeleteModal.show();
    });

    // Confirm bulk delete
    document.getElementById('confirmBulkDeleteBtn').addEventListener('click', async function() {
        const checked = Array.from(document.querySelectorAll('.room-checkbox:checked'));
        const ids = checked.map(cb => cb.value);
        
        try {
            const response = await fetch('{{ route('schools.rooms.bulk-delete', $school) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.reload();
            }
        } catch (error) {
            alert('Klaida šalinant kabinetus');
        }
    });

    function updateBulkDeleteBtn() {
        const checked = document.querySelectorAll('.room-checkbox:checked').length;
        document.getElementById('bulkDeleteBtn').disabled = checked === 0;
    }

    // Clear checkboxes on page reload
    window.addEventListener('pageshow', function() {
        document.querySelectorAll('.room-checkbox, #selectAll').forEach(cb => cb.checked = false);
        updateBulkDeleteBtn();
    });
});
</script>
@endsection
