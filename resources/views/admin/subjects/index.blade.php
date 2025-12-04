@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-journal-bookmark"></i> Dalykai - {{ $school->name }}</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-plus-circle"></i> Pridėti naują dalyką
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('schools.subjects.store', $school) }}">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="Įveskite dalyko pavadinimą" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Pridėti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-download"></i> Numatytieji dalykai
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('schools.subjects.add-defaults', $school) }}">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-arrow-down-circle"></i> Įtraukti numatytus dalykus
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">Prideda pagrindinius Lietuvos mokyklų dalykus</small>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Dalykų sąrašas</h5>
            <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                <i class="bi bi-trash"></i> Pašalinti pažymėtus
            </button>
        </div>
        <div class="card-body p-0">
            <form id="bulkDeleteForm">
                <div class="modern-table-wrapper">
                    <table class="modern-table table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th><i class="bi bi-book"></i> Pavadinimas</th>
                                <th style="width: 200px;" class="text-end">Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subject)
                                <tr data-subject-id="{{ $subject->id }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="subject-checkbox form-check-input" value="{{ $subject->id }}">
                                    </td>
                                    <td>
                                        <span class="subject-name fw-medium" data-id="{{ $subject->id }}">{{ $subject->name }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-warning edit-btn" data-id="{{ $subject->id }}" data-name="{{ $subject->name }}">
                                                <i class="bi bi-pencil"></i> Redaguoti
                                            </button>
                                            <button type="button" class="btn btn-outline-danger delete-btn" data-id="{{ $subject->id }}" data-name="{{ $subject->name }}">
                                                <i class="bi bi-trash"></i> Trinti
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <p>Nėra sukurtų dalykų</p>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title" id="editModalLabel">Redaguoti dalyką</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="editSubjectForm">
              <input type="hidden" id="editSubjectId">
              <div class="mb-3">
                <label for="editSubjectName" class="form-label">Pavadinimas</label>
                <input type="text" class="form-control" id="editSubjectName" required>
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
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteModalLabel">Patvirtinti šalinimą</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Ar tikrai norite pašalinti dalyką "<strong id="deleteSubjectName"></strong>"?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Pašalinti</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="bulkDeleteModalLabel">Patvirtinti masinio šalinimo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Ar tikrai norite pašalinti <strong id="bulkDeleteCount"></strong> pažymėtus dalykus?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
            <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">Pašalinti visus</button>
          </div>
        </div>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bulk select
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.subject-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkDeleteBtn();
        });
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteBtn);
        });
        
        function updateBulkDeleteBtn() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            bulkDeleteBtn.disabled = !anyChecked;
        }
        
        // Clear checkboxes on reload
        window.addEventListener('pageshow', function() {
            checkboxes.forEach(cb => cb.checked = false);
            selectAll.checked = false;
            updateBulkDeleteBtn();
        });
        
        // Bulk delete with modal
        let bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        bulkDeleteBtn.addEventListener('click', function() {
            const ids = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (!ids.length) return;
            document.getElementById('bulkDeleteCount').textContent = ids.length;
            bulkDeleteModal.show();
        });
        
        document.getElementById('confirmBulkDeleteBtn').addEventListener('click', function() {
            const ids = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            fetch(`{{ route('schools.subjects.bulk-delete', $school) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bulkDeleteModal.hide();
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    window.location.reload();
                }
            });
        });
        
        // Edit modal
        let editModal = new bootstrap.Modal(document.getElementById('editModal'));
        let editId = null;
        
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                editId = btn.getAttribute('data-id');
                document.getElementById('editSubjectId').value = editId;
                document.getElementById('editSubjectName').value = btn.getAttribute('data-name');
                editModal.show();
            });
        });
        
        document.getElementById('saveEditBtn').addEventListener('click', function() {
            const id = document.getElementById('editSubjectId').value;
            const name = document.getElementById('editSubjectName').value;
            
            fetch(`/admin/schools/{{ $school->id }}/subjects/${id}/edit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    editModal.hide();
                    // Update row text
                    const nameSpan = document.querySelector(`span.subject-name[data-id="${id}"]`);
                    if (nameSpan) {
                        nameSpan.textContent = name;
                    }
                    // Update button data
                    const editBtn = document.querySelector(`button.edit-btn[data-id="${id}"]`);
                    const deleteBtn = document.querySelector(`button.delete-btn[data-id="${id}"]`);
                    if (editBtn) editBtn.setAttribute('data-name', name);
                    if (deleteBtn) deleteBtn.setAttribute('data-name', name);
                }
            });
        });
        
        // Delete modal
        let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        let deleteId = null;
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteId = btn.getAttribute('data-id');
                const deleteName = btn.getAttribute('data-name');
                document.getElementById('deleteSubjectName').textContent = deleteName;
                deleteModal.show();
            });
        });
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (!deleteId) return;
            
            fetch(`/admin/schools/{{ $school->id }}/subjects/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    deleteModal.hide();
                    // Remove row from table
                    const row = document.querySelector(`tr[data-subject-id="${deleteId}"]`);
                    if (row) {
                        row.remove();
                    }
                    // Update checkboxes
                    updateBulkDeleteBtn();
                } else {
                    console.error('Delete failed:', data);
                    alert('Klaida šalinant dalyką');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Klaida šalinant dalyką');
            });
        });
    });
    </script>
</div>
@endsection
