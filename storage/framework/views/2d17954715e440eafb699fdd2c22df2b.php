<?php $__currentLoopData = ($timetable->generation_report['unscheduled'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- Edit Unscheduled Group Modal -->
    <div class="modal fade" id="editUnscheduledGroup<?php echo e($item['group_id']); ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Redaguoti grupę: <?php echo e($item['group_name']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nepaskirstyta pamokų: <strong><?php echo e($item['remaining_lessons'] ?? 0); ?></strong> /
                        <?php echo e($item['requested_lessons'] ?? ($item['lessons_per_week'] ?? ($item['total_lessons'] ?? ($item['remaining_lessons'] ?? 0)))); ?>

                    </div>
                    <form id="editUnscheduledForm<?php echo e($item['group_id']); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="<?php echo e($item['group_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dalykas</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    <?php $__currentLoopData = $school->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($subject->id); ?>" <?php echo e((($item['subject_id'] ?? null) == $subject->id) ? 'selected' : ''); ?>><?php echo e($subject->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mokytojas</label>
                                <select name="teacher_login_key_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    <?php $__currentLoopData = $school->loginKeys()->where('type','teacher')->orderBy('last_name')->orderBy('first_name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>" <?php echo e((($item['teacher_login_key_id'] ?? null) == $teacher->id) ? 'selected' : ''); ?>><?php echo e($teacher->full_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kabinetas</label>
                                <select name="room_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    <?php $__currentLoopData = $school->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($room->id); ?>"><?php echo e($room->number); ?> <?php echo e($room->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Savaitės tipas</label>
                                <select name="week_type" class="form-select" required>
                                    <option value="all">Kiekviena</option>
                                    <option value="even">Lyginės</option>
                                    <option value="odd">Nelyginės</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pamokų sk./sav.</label>
                                <input type="number" name="lessons_per_week" class="form-control" min="1" max="20" value="<?php echo e($item['requested_lessons'] ?? ($item['lessons_per_week'] ?? 1)); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input type="checkbox" name="is_priority" id="editUnschPriority<?php echo e($item['group_id']); ?>" class="form-check-input" value="1">
                                    <label class="form-check-label" for="editUnschPriority<?php echo e($item['group_id']); ?>">
                                        <i class="bi bi-star"></i> Prioritetinė
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3"><i class="bi bi-people"></i> Mokinių valdymas</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong><i class="bi bi-search"></i> Ieškoti mokinių</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="studentSearchUnsch<?php echo e($item['group_id']); ?>" placeholder="Ieškoti pagal vardą/pavardę...">
                                            <button class="btn btn-outline-secondary" type="button" onclick="loadAllStudentsUnsch(<?php echo e($item['group_id']); ?>)">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                        <div id="studentsListUnsch<?php echo e($item['group_id']); ?>" style="max-height: 350px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.5rem;">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                                <p class="text-muted small mt-2">Kraunami mokiniai...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <strong><i class="bi bi-people-fill"></i> Priskirti mokiniai (<span id="assignedCountUnsch<?php echo e($item['group_id']); ?>">0</span>)</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="assignedSearchUnsch<?php echo e($item['group_id']); ?>" placeholder="Filtruoti priskirtus mokinius...">
                                        </div>
                                        <div id="assignedStudentsListUnsch<?php echo e($item['group_id']); ?>" style="max-height: 400px; overflow-y: auto;">
                                            <div class="text-center py-3 text-muted">
                                                <small>Mokinių nėra</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-primary" onclick="saveUnscheduledGroup(<?php echo e($item['group_id']); ?>)">
                        <i class="bi bi-save"></i> Išsaugoti
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Copy Unscheduled Group Modal -->
    <div class="modal fade" id="copyUnscheduledGroup<?php echo e($item['group_id']); ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-files"></i> Kopijuoti grupę</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Kopijuojama grupė:</strong> <?php echo e($item['group_name']); ?><br>
                        <strong>Dalykas:</strong> <?php echo e($item['subject_name']); ?><br>
                        <strong>Pamokų skaičius:</strong> <?php echo e($item['remaining_lessons']); ?> (tik nepaskirstytos)
                    </div>
                    
                    <form id="copyUnscheduledForm<?php echo e($item['group_id']); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Naujos grupės pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="<?php echo e($item['group_name']); ?> (kopija)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mokytojas <span class="text-danger">*</span></label>
                                <select name="teacher_login_key_id" class="form-select" required>
                                    <option value="">-- Pasirinkite --</option>
                                    <?php $__currentLoopData = $school->loginKeys()->where('type','teacher')->orderBy('last_name')->orderBy('first_name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>" <?php echo e(($item['teacher_login_key_id'] ?? null) == $teacher->id ? 'selected' : ''); ?>><?php echo e($teacher->full_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kabinetas <span class="text-danger">*</span></label>
                                <select name="room_id" class="form-select" required>
                                    <option value="">-- Pasirinkite --</option>
                                    <?php $__currentLoopData = $school->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($room->id); ?>" <?php echo e(($item['room_id'] ?? null) == $room->id ? 'selected' : ''); ?>><?php echo e($room->number); ?> <?php echo e($room->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-info-circle"></i> <strong>Pastaba:</strong> Bus nukopijuoti visi mokiniai iš originalios grupės.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-success" onclick="confirmCopyGroupWithData(<?php echo e($item['group_id']); ?>, <?php echo e($item['remaining_lessons']); ?>)">
                        <i class="bi bi-check-circle"></i> Sukurti kopiją
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<!-- Modal for merging unscheduled groups -->
<div class="modal fade" id="mergeUnscheduledGroupsModal" tabindex="-1" data-school-id="<?php echo e($school->id ?? ''); ?>" data-timetable-id="<?php echo e($timetable->id ?? ''); ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-merge"></i> Sujungti grupes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Šiame dialoge galite sujungti kelias nepaskirstytas grupes į vieną, jei jos atitinka pagal mokinius, dalykus ir mokytoją.
                </div>
                <form id="mergeGroupsForm">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label"><strong>Pasirinkite grupes sujungti:</strong></label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <?php
                                $unscheduled = $timetable->generation_report['unscheduled'] ?? [];
                                // Group by teacher + subject + students to show mergeable groups
                                $groupedByKey = [];
                                foreach ($unscheduled as $item) {
                                    if (($item['remaining_lessons'] ?? 0) > 0) {
                                        $key = ($item['teacher_login_key_id'] ?? '') . '|' . ($item['subject_id'] ?? '');
                                        if (!isset($groupedByKey[$key])) {
                                            $groupedByKey[$key] = [];
                                        }
                                        $groupedByKey[$key][] = $item;
                                    }
                                }
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $groupedByKey; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php if(count($items) >= 2): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">
                                        <strong><?php echo e($items[0]['teacher_name'] ?? '—'); ?> • <?php echo e($items[0]['subject_name'] ?? '—'); ?></strong>
                                    </small>
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check">
                                        <input class="form-check-input merge-group-checkbox" type="checkbox" 
                                               name="group_ids[]" value="<?php echo e($item['group_id']); ?>"
                                               data-teacher="<?php echo e($item['teacher_login_key_id']); ?>"
                                               data-subject="<?php echo e($item['subject_id']); ?>"
                                               data-group-name="<?php echo e($item['group_name']); ?>"
                                               data-lessons="<?php echo e($item['remaining_lessons']); ?>"
                                               id="mergeCheck<?php echo e($item['group_id']); ?>">
                                        <label class="form-check-label" for="mergeCheck<?php echo e($item['group_id']); ?>">
                                            <?php echo e($item['group_name']); ?> 
                                            <span class="badge bg-warning text-dark ms-2"><?php echo e($item['remaining_lessons']); ?> pamokos</span>
                                        </label>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <hr>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">Nėra grupių, kurias būtų galima sujungti</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Sujungtos grupės pavadinimas</label>
                            <input type="text" name="group_name" class="form-control" placeholder="Pvz: 6B_lietuviu_kalba_sujungta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kabinetas</label>
                            <select name="room_id" class="form-select" required>
                                <option value="">-- Pasirinkite --</option>
                                <?php $__currentLoopData = $school->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($room->id); ?>"><?php echo e($room->number); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-secondary mt-3 mb-0">
                        <small><strong>Pasirinktos pamokos:</strong> <span id="selectedLessonsCount">0</span> pamokos</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <button type="button" class="btn btn-success" onclick="submitMergeGroupsAction()">
                    <i class="bi bi-check-circle"></i> Sujungti grupes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.initializeMergeGroupsModal = function() {
    const checkboxes = document.querySelectorAll('.merge-group-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.removeEventListener('change', updateSelectedLessonsCount);
        checkbox.addEventListener('change', updateSelectedLessonsCount);
    });
};

window.updateSelectedLessonsCount = function() {
    const checkboxes = document.querySelectorAll('.merge-group-checkbox:checked');
    let totalLessons = 0;
    
    checkboxes.forEach(checkbox => {
        totalLessons += parseInt(checkbox.dataset.lessons) || 0;
    });
    
    const countElement = document.getElementById('selectedLessonsCount');
    if (countElement) {
        countElement.textContent = totalLessons;
    }
};

window.showMergeNotification = function(message, type = 'error') {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '1080';
    
    let bgClass = 'text-bg-danger';
    let icon = 'bi-exclamation-circle';
    
    if (type === 'success') {
        bgClass = 'text-bg-success';
        icon = 'bi-check-circle';
    } else if (type === 'warning') {
        bgClass = 'text-bg-warning';
        icon = 'bi-exclamation-triangle';
    } else if (type === 'info') {
        bgClass = 'text-bg-info';
        icon = 'bi-info-circle';
    }
    
    toast.innerHTML = `
        <div class="toast align-items-center ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast.querySelector('.toast'));
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
};

window.submitMergeGroupsAction = async function() {
    const checkboxes = document.querySelectorAll('.merge-group-checkbox:checked');
    
    if (checkboxes.length < 2) {
        showMergeNotification('Prašom pasirinkti bent 2 grupes sujungti', 'warning');
        return;
    }
    
    // Verify all selected groups have same teacher and subject
    const firstTeacher = checkboxes[0].dataset.teacher;
    const firstSubject = checkboxes[0].dataset.subject;
    
    for (let checkbox of checkboxes) {
        if (checkbox.dataset.teacher !== firstTeacher || checkbox.dataset.subject !== firstSubject) {
            showMergeNotification('Visos pasirinktos grupės turi turėti tą patį mokytoją ir dalyką', 'error');
            return;
        }
    }
    
    const groupIds = Array.from(checkboxes).map(cb => cb.value);
    const modalElement = document.getElementById('mergeUnscheduledGroupsModal');
    const groupNameInput = modalElement.querySelector('input[name="group_name"]');
    const roomSelect = modalElement.querySelector('select[name="room_id"]');
    
    const groupName = groupNameInput.value.trim();
    const roomId = roomSelect.value;
    
    if (!groupName) {
        showMergeNotification('Prašom įvesti sujungtos grupės pavadinimą', 'warning');
        groupNameInput.focus();
        return;
    }
    
    if (!roomId) {
        showMergeNotification('Prašom pasirinkti kabinetą', 'warning');
        roomSelect.focus();
        return;
    }
    
    try {
        const modalElement = document.getElementById('mergeUnscheduledGroupsModal');
        const schoolId = modalElement.dataset.schoolId;
        const timetableId = modalElement.dataset.timetableId;
        
        console.log('Merge request:', {
            schoolId,
            timetableId,
            group_ids: groupIds,
            group_name: groupName,
            room_id: parseInt(roomId)
        });
        
        const response = await fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/merge-unscheduled-groups`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                group_ids: groupIds,
                group_name: groupName,
                room_id: parseInt(roomId)
            })
        });
        
        const data = await response.json();
        console.log('Response:', response.status, data);
        
        if (!response.ok || !data.success) {
            const errorMsg = data.error || data.message || 'Nepavyko sujungti grupių';
            showMergeNotification(errorMsg, 'error');
            return;
        }
        
        // Close modal and reload page
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        
        // Show success message and reload
        showMergeNotification('Grupės sėkmingai sujungtos! Puslapį perkraunama...', 'success');
        
        setTimeout(() => {
            location.reload();
        }, 1500);
        
    } catch (err) {
        console.error('Error merging groups:', err);
        showMergeNotification('Klaida siunčiant užklausą: ' + err.message, 'error');
    }
};

// Initialize modal when shown
document.addEventListener('shown.bs.modal', function(e) {
    if (e.target && e.target.id === 'mergeUnscheduledGroupsModal') {
        window.initializeMergeGroupsModal();
    }
});
</script><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/partials/unscheduled-modals.blade.php ENDPATH**/ ?>