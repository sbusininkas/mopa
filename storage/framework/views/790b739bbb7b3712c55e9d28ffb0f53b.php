<?php $__env->startSection('content'); ?>
<!-- Toast Container -->
<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-collection"></i> Grupė: <?php echo e($group->name); ?></h2>
        <div>
            <a href="<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Atgal į tvarkaraštį
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Grupės informacija</strong>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editGroupModal">
                        <i class="bi bi-pencil-square"></i> Redaguoti grupę
                    </button>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Pavadinimas</dt>
                        <dd class="col-sm-8"><?php echo e($group->name); ?></dd>
                        <dt class="col-sm-4">Dalykas</dt>
                        <dd class="col-sm-8">
                            <?php if($group->subject): ?>
                                <a href="<?php echo e(route('schools.timetables.subject-groups', [$school, $timetable, $group->subject->name])); ?>" class="text-decoration-none">
                                    <?php echo e($group->subject->name); ?>

                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Mokytojas</dt>
                        <dd class="col-sm-8">
                            <?php if($group->teacherLoginKey): ?>
                                <a href="<?php echo e(route('schools.timetables.teacher', [$school, $timetable, $group->teacherLoginKey->id])); ?>" class="text-decoration-none">
                                    <?php echo e($group->teacherLoginKey->full_name); ?>

                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Kabinetas</dt>
                        <dd class="col-sm-8">
                            <?php if($group->room): ?>
                                <a href="<?php echo e(route('schools.timetables.teachers-view', [$school, $timetable])); ?>?room=<?php echo e($group->room->id); ?>" class="text-decoration-none">
                                    <?php echo e($group->room->number); ?>

                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Savaitė</dt>
                        <dd class="col-sm-8"><?php echo e(['all'=>'Visos','even'=>'Lyginės','odd'=>'Nelyginės'][$group->week_type] ?? '—'); ?></dd>
                        <dt class="col-sm-4">Pamokos / savaitę</dt>
                        <dd class="col-sm-8"><?php echo e($group->lessons_per_week ?? 0); ?></dd>
                        <dt class="col-sm-4">Prioritetas</dt>
                        <dd class="col-sm-8"><?php echo $group->is_priority ? '<span class="badge bg-warning text-dark">Taip</span>' : '<span class="badge bg-secondary">Ne</span>'; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Priskirti mokiniai (<?php echo e($group->students->count()); ?>)</strong>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editGroupModal">
                <i class="bi bi-plus-circle"></i> Pridėti
            </button>
        </div>
        <div class="card-body">
            <?php $__empty_1 = true; $__currentLoopData = $group->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <?php if($s->class_name): ?>
                            <span class="badge bg-info me-2"><?php echo e($s->class_name); ?></span>
                        <?php endif; ?>
                        <a href="<?php echo e(route('schools.timetables.student-view', [$school, $timetable, $s->id])); ?>" class="text-decoration-none">
                            <?php echo e($s->full_name); ?>

                        </a>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-student-btn" data-student-id="<?php echo e($s->id); ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-muted text-center py-3">Nėra priskirtų mokinių</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit modal -->
    <div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editGroupModalLabel">Redaguoti grupę</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="<?php echo e(route('schools.timetables.groups.update', [$school, $timetable, $group])); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pavadinimas</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $group->name)); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pamokos / savaitę</label>
                        <input type="number" min="1" max="20" name="lessons_per_week" class="form-control" value="<?php echo e(old('lessons_per_week', $group->lessons_per_week ?? 1)); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dalykas</label>
                        <select name="subject_id" class="form-select">
                            <option value="">—</option>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s->id); ?>" <?php if(old('subject_id', $group->subject_id)===$s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mokytojas</label>
                        <select name="teacher_login_key_id" class="form-select">
                            <option value="">—</option>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($t->id); ?>" <?php if(old('teacher_login_key_id', $group->teacher_login_key_id)===$t->id): echo 'selected'; endif; ?>><?php echo e($t->full_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kabinetas</label>
                        <select name="room_id" class="form-select">
                            <option value="">—</option>
                            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($r->id); ?>" <?php if(old('room_id', $group->room_id)===$r->id): echo 'selected'; endif; ?>><?php echo e($r->number); ?> <?php echo e($r->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Savaitės tipas</label>
                        <select name="week_type" class="form-select">
                            <?php $__currentLoopData = ['all'=>'Visos','even'=>'Lyginės','odd'=>'Nelyginės']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php if(old('week_type', $group->week_type)===$val): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_priority" name="is_priority" value="1" <?php if(old('is_priority', $group->is_priority)): echo 'checked'; endif; ?>>
                            <label class="form-check-label" for="is_priority">Prioritetas</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="studentsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned-pane" type="button" role="tab" aria-controls="assigned-pane" aria-selected="true">
                                    <i class="bi bi-check-circle"></i> Priskirti mokiniai (<span id="assignedCount"><?php echo e(count($group->students)); ?></span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="available-tab" data-bs-toggle="tab" data-bs-target="#available-pane" type="button" role="tab" aria-controls="available-pane" aria-selected="false">
                                    <i class="bi bi-plus-circle"></i> Nepriskirti mokiniai
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content mt-2" id="studentsTabContent">
                            <div class="tab-pane fade show active" id="assigned-pane" role="tabpanel" aria-labelledby="assigned-tab">
                                <div class="mb-2">
                                    <input type="text" class="form-control" id="assignedSearchInput" placeholder="Paieška priskirtuose...">
                                </div>
                                <div class="border rounded p-2" style="max-height: 300px; overflow:auto;" id="assignedList">
                                    <?php $__currentLoopData = $group->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="d-flex justify-content-between align-items-center p-2 assigned-item" data-student-id="<?php echo e($s->id); ?>" data-name="<?php echo e($s->full_name); ?>" data-class="<?php echo e($s->class_name ?? ''); ?>">
                                            <div>
                                                <?php if($s->class_name): ?>
                                                    <span class="badge bg-light text-dark me-2"><?php echo e($s->class_name); ?></span>
                                                <?php endif; ?>
                                                <span><?php echo e($s->full_name); ?></span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-student-modal-btn" data-student-id="<?php echo e($s->id); ?>">
                                                <i class="bi bi-dash-circle"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="available-pane" role="tabpanel" aria-labelledby="available-tab">
                                <div class="mb-2">
                                    <input type="text" class="form-control" id="availableSearchInput" placeholder="Paieška pagal vardą, pavardę arba klasę...">
                                </div>
                                <div class="border rounded p-2" style="max-height: 300px; overflow:auto;" id="availableList">
                                    <?php $assigned = $group->students->pluck('id')->all(); ?>
                                    <?php $__currentLoopData = $allStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(!in_array($s->id, $assigned)): ?>
                                            <div class="d-flex justify-content-between align-items-center p-2 available-item" data-class="<?php echo e($s->class_name ?? ''); ?>" data-name="<?php echo e($s->full_name); ?>" data-student-id="<?php echo e($s->id); ?>">
                                                <div>
                                                    <?php if($s->class_name): ?>
                                                        <span class="badge bg-light text-dark me-2"><?php echo e($s->class_name); ?></span>
                                                    <?php endif; ?>
                                                    <span><?php echo e($s->full_name); ?></span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-success add-student-modal-btn" data-student-id="<?php echo e($s->id); ?>">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Uždaryti</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Toast notification helper
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const toastDiv = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-warning');
    
    toastDiv.className = `toast show ${bgClass} text-white`;
    toastDiv.style.marginBottom = '10px';
    toastDiv.setAttribute('role', 'alert');
    toastDiv.innerHTML = `
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    toastContainer.appendChild(toastDiv);
    
    setTimeout(() => {
        toastDiv.remove();
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function(){
    const groupId = <?php echo e($group->id); ?>;
    const schoolId = <?php echo e($school->id); ?>;
    const timetableId = <?php echo e($timetable->id); ?>;
    const currentGroupName = '<?php echo e($group->name); ?>';
    const csrfToken = '<?php echo e(csrf_token()); ?>';
    
    if (location.hash === '#edit' || new URLSearchParams(location.search).get('edit') === '1') {
        const modalEl = document.getElementById('editGroupModal');
        if (modalEl && window.bootstrap) {
            const m = new bootstrap.Modal(modalEl);
            m.show();
        } else if (modalEl) {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
    }

    // AJAX remove student button on main page
    // Clean up any orphaned backdrops before starting
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    
    document.querySelectorAll('.remove-student-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const studentId = this.dataset.studentId;
            const studentRow = this.closest('.d-flex');
            const studentName = studentRow.querySelector('a').textContent.trim();
            
            showRemoveConfirmationModal(studentId, studentName, () => {
                fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/groups/${groupId}/assign-students`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_ids: [studentId],
                        action: 'remove'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`.assigned-item[data-student-id="${studentId}"]`);
                        if (item) item.remove();
                        showToast('Mokinys sėkmingai pašalintas iš grupės', 'success');
                    } else {
                        showToast('Klaida pašalinant mokinį', 'error');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
    
    function showRemoveConfirmationModal(studentId, studentName, onConfirm) {
        const modalDiv = document.createElement('div');
        modalDiv.className = 'modal fade';
        modalDiv.id = 'removeConfirmModal';
        modalDiv.setAttribute('tabindex', '-1');
        modalDiv.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Patvirtinti šalinimą</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Ar tikrai norite pašalinti <strong>${studentName}</strong> iš šios grupės?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Atšaukti</button>
                        <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Pašalinti</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modalDiv);
        const modal = new bootstrap.Modal(modalDiv);
        modal.show();
        
        document.getElementById('confirmRemoveBtn').addEventListener('click', () => {
            modal.hide();
            onConfirm();
        });
        
        modalDiv.addEventListener('hidden.bs.modal', () => {
            // Remove backdrop and modal together
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            modalDiv.remove();
        });
    }

    // AJAX add/remove student from modal
    document.querySelectorAll('.add-student-modal-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const studentId = this.dataset.studentId;
            const availableItem = this.closest('.available-item');
            const studentName = availableItem.querySelector('span:not(.badge)').textContent;
            const studentClass = availableItem.querySelector('.badge')?.textContent || '';
            
            fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/groups/${groupId}/assign-students`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    student_ids: [studentId]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.hasConflict) {
                    // Show conflict modal only if there are actual conflicts
                    showConflictModal(studentId, studentName, studentClass, data.conflicts, availableItem);
                } else if (data.success) {
                    // Add to assigned list
                    addStudentToAssignedList(studentId, studentName, studentClass, availableItem);
                } else {
                    console.error('Unexpected response:', data);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Function to add remove modal listener
    function addRemoveModalListener(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const studentId = this.dataset.studentId;
            const assignedItem = this.closest('.assigned-item');
            const studentName = assignedItem.querySelector('span:not(.badge)').textContent.trim();
            
            showRemoveConfirmationModal(studentId, studentName, () => {
                fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/groups/${groupId}/assign-students`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_ids: [studentId],
                        action: 'remove'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Get student info
                        const studentClass = assignedItem.querySelector('.badge')?.textContent || '';
                        
                        // Remove from assigned list
                        assignedItem.remove();
                        
                        // Update count
                        const count = document.getElementById('assignedCount');
                        count.textContent = parseInt(count.textContent) - 1;
                        
                        // Add to available list
                        const availableList = document.getElementById('availableList');
                        const newItem = document.createElement('div');
                        newItem.className = 'd-flex justify-content-between align-items-center p-2 available-item';
                        newItem.dataset.studentId = studentId;
                        newItem.dataset.name = studentName;
                        newItem.dataset.class = studentClass;
                        newItem.innerHTML = `
                            <div>
                                ${studentClass ? `<span class="badge bg-light text-dark me-2">${studentClass}</span>` : ''}
                                <span>${studentName}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success add-student-modal-btn" data-student-id="${studentId}">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        `;
                        availableList.appendChild(newItem);
                        
                        // Add event listener to new add button
                        addAddModalListener(newItem.querySelector('.add-student-modal-btn'));
                    } else {
                        console.error('Error removing student:', data);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    }

    // Function to add add modal listener
    function addAddModalListener(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.click();
        });
    }

    // Add initial listeners to remove buttons
    document.querySelectorAll('.remove-student-modal-btn').forEach(btn => {
        addRemoveModalListener(btn);
    });

    // Search in assigned students
    const assignedSearch = document.getElementById('assignedSearchInput');
    if (assignedSearch) {
        assignedSearch.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.assigned-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const className = item.dataset.class.toLowerCase();
                const matches = name.includes(query) || className.includes(query);
                item.style.display = matches ? 'flex' : 'none';
            });
        });
    }

    // Helper function to add student to assigned list
    function addStudentToAssignedList(studentId, studentName, studentClass, availableItem) {
        const assignedList = document.getElementById('assignedList');
        const newItem = document.createElement('div');
        newItem.className = 'd-flex justify-content-between align-items-center p-2 assigned-item';
        newItem.dataset.studentId = studentId;
        newItem.dataset.name = studentName;
        newItem.dataset.class = studentClass;
        newItem.innerHTML = `
            <div>
                ${studentClass ? `<span class="badge bg-light text-dark me-2">${studentClass}</span>` : ''}
                <span>${studentName}</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-student-modal-btn" data-student-id="${studentId}">
                <i class="bi bi-dash-circle"></i>
            </button>
        `;
        assignedList.appendChild(newItem);
        
        // Update count
        const count = document.getElementById('assignedCount');
        count.textContent = parseInt(count.textContent) + 1;
        
        // Remove from available list
        availableItem.remove();
        
        // Add event listener to new remove button
        addRemoveModalListener(newItem.querySelector('.remove-student-modal-btn'));
        
        // Show success toast
        showToast(`${studentName} sėkmingai pridėta prie grupės`, 'success');
    }
    
    // Helper function to show conflict modal
    function showConflictModal(studentId, studentName, studentClass, conflicts, availableItem) {
        // Fetch BOTH student schedule and target group schedule
        Promise.all([
            fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/student-schedule?student_id=${studentId}`).then(r => r.json()),
            fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/groups/${groupId}/schedule`).then(r => r.json())
        ])
            .then(([studentData, groupData]) => {
                if (studentData.success && groupData.success) {
                    const studentSchedule = Array.isArray(studentData.schedule) ? studentData.schedule : [];
                    const targetGroupSlots = Array.isArray(groupData.schedule) ? groupData.schedule : [];
                    
                    console.debug('Student schedule:', studentSchedule);
                    console.debug('Target group slots:', targetGroupSlots);
                    
                    // Get max hour from BOTH student schedule and target group slots
                    let maxHour = 0;
                    studentSchedule.forEach(slot => {
                        const h = parseInt(slot.hour);
                        if (!isNaN(h) && h > maxHour) maxHour = h;
                    });
                    targetGroupSlots.forEach(slot => {
                        const h = parseInt(slot.hour);
                        if (!isNaN(h) && h > maxHour) maxHour = h;
                    });
                    if (maxHour === 0) maxHour = 6; // Default min height
                    
                    // Define days using short codes
                    const days = {
                        'Mon': 'Pirmadienis',
                        'Tue': 'Antradienis',
                        'Wed': 'Trečiadienis',
                        'Thu': 'Ketvirtadienis',
                        'Fri': 'Penktadienis'
                    };
                    
                    // Build schedule table HTML - show both student existing lessons and target group slots
                    let tableHtml = '<div class="table-responsive" style="max-height: 500px; overflow-y: auto;"><table class="table table-sm table-bordered">';
                    
                    // Header row with days
                    tableHtml += '<thead><tr><th style="width: 60px; text-align: center; vertical-align: middle;">Pamoka</th>';
                    for (let dayCode in days) {
                        tableHtml += `<th style="text-align: center; width: 100px;">${days[dayCode]}</th>`;
                    }
                    tableHtml += '</tr></thead>';
                    
                    // Body with slots
                    tableHtml += '<tbody>';
                    for (let hour = 1; hour <= maxHour; hour++) {
                        tableHtml += `<tr><td style="text-align: center; font-weight: bold;">${hour}</td>`;
                        
                        for (let dayCode in days) {
                            // Check for student existing lesson at this time
                            const studentLesson = studentSchedule.find(s => s.day === dayCode && parseInt(s.hour) === hour);
                            
                            // Check if target group has a lesson at this time
                            const isTargetGroupSlot = targetGroupSlots.some(s => s.day === dayCode && parseInt(s.hour) === hour);
                            
                            let cellHtml = '';
                            let bgColor = '#f9f9f9';
                            let cellContent = '—';
                            
                            if (studentLesson && isTargetGroupSlot) {
                                // CONFLICT: Student has lesson and target group has lesson at same time
                                bgColor = '#ff9999'; // Dark red for conflict
                                cellContent = `<div style="font-size: 0.75rem; font-weight: bold; color: darkred;">⚠ KONFLIKTAS</div>
                                    <div style="font-size: 0.70rem; color: #0066cc;"><strong>Esama:</strong><br>${studentLesson.group_name}</div>
                                    <div style="font-size: 0.70rem; color: darkgreen;"><strong>Pridedama:</strong><br>${currentGroupName}</div>`;
                            } else if (studentLesson) {
                                // Only student has lesson (blue background)
                                bgColor = '#cce5ff';
                                cellContent = `<div style="font-size: 0.75rem; color: #0066cc;"><strong>Esama:</strong></div>
                                    <div style="font-size: 0.70rem; color: #0066cc;">${studentLesson.group_name}</div>`;
                            } else if (isTargetGroupSlot) {
                                // Only target group has lesson (green background - can be added)
                                bgColor = '#ccffcc';
                                cellContent = `<div style="font-size: 0.75rem; color: green;"><strong>Pridedama:</strong></div>
                                    <div style="font-size: 0.70rem; color: green;">✓ LAISVA</div>`;
                            }
                            // else: no lessons at this time (gray stays)
                            
                            tableHtml += `<td style="background-color: ${bgColor}; text-align: center; padding: 4px; font-size: 0.80rem;">
                                ${cellContent}
                            </td>`;
                        }
                        
                        tableHtml += '</tr>';
                    }
                    tableHtml += '</tbody></table></div>';
                    
                    if (targetGroupSlots.length === 0) {
                        tableHtml = '<div class="alert alert-info">Pasirinkta grupė neturi suplanuotų pamokų.</div>' + tableHtml;
                    }
                    
                    // Create modal
                    const tempModal = document.createElement('div');
                    tempModal.className = 'modal fade';
                    tempModal.id = 'conflictModal';
                    tempModal.setAttribute('tabindex', '-1');
                    tempModal.innerHTML = `
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tvarkaraščio konfliktas - <a href="/admin/schools/${schoolId}/timetables/${timetableId}/student/${studentId}" class="text-decoration-none" target="_blank" style="color: #0d6efd; cursor: pointer;">${studentName}</a></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <h6 class="mb-3"><a href="/admin/schools/${schoolId}/timetables/${timetableId}/student/${studentId}" class="text-decoration-none" target="_blank" style="color: #0d6efd; cursor: pointer;"><strong>${studentName}</strong></a> — Suplanuotos pamokos</h6>
                                    ${tableHtml}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                    <button type="button" class="btn btn-warning" id="ignoreConflictBtn">Nepaisyti konflikto</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(tempModal);
                    const conflictModal = new bootstrap.Modal(tempModal);
                    conflictModal.show();
                    
                    // Handle ignore conflict button
                    document.getElementById('ignoreConflictBtn').addEventListener('click', function() {
                        conflictModal.hide();
                        
                        // Call assign again with ignore_conflict flag
                        fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/groups/${groupId}/assign-students`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                student_ids: [studentId],
                                ignore_conflict: true
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                addStudentToAssignedList(studentId, studentName, studentClass, availableItem);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    });
                    
                    // Clean up modal and backdrop when hidden
                    tempModal.addEventListener('hidden.bs.modal', () => {
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        tempModal.remove();
                    });

                    // Debug UI removed per request
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Search in available students
    const availableSearch = document.getElementById('availableSearchInput');
    if (availableSearch) {
        availableSearch.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.available-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const className = item.dataset.class.toLowerCase();
                const matches = name.includes(query) || className.includes(query);
                item.style.display = matches ? 'flex' : 'none';
            });
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/admin/timetables/group-show.blade.php ENDPATH**/ ?>