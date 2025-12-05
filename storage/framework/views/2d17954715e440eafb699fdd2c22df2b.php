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
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/partials/unscheduled-modals.blade.php ENDPATH**/ ?>