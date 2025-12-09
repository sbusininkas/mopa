<div class="modal fade" id="timetableSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?php echo e(route('schools.timetables.update', [$school, $timetable])); ?>" class="modal-content">
            <?php echo csrf_field(); ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear"></i> Tvarkaraščio nustatymai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Pavadinimas</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($timetable->name); ?>" required>
                        <div class="form-check ms-2">
                            <input type="checkbox" name="is_public" id="isPublic" class="form-check-input" <?php echo e($timetable->is_public ? 'checked' : ''); ?>>
                            <label for="isPublic" class="form-check-label">Viešas</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. pamokų (Pirmadienis)</label>
                        <input type="number" name="max_lessons_monday" class="form-control" min="1" max="20" value="<?php echo e($timetable->max_lessons_monday ?? 9); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. pamokų (Antradienis)</label>
                        <input type="number" name="max_lessons_tuesday" class="form-control" min="1" max="20" value="<?php echo e($timetable->max_lessons_tuesday ?? 9); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. pamokų (Trečiadienis)</label>
                        <input type="number" name="max_lessons_wednesday" class="form-control" min="1" max="20" value="<?php echo e($timetable->max_lessons_wednesday ?? 9); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. pamokų (Ketvirtadienis)</label>
                        <input type="number" name="max_lessons_thursday" class="form-control" min="1" max="20" value="<?php echo e($timetable->max_lessons_thursday ?? 9); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. pamokų (Penktadienis)</label>
                        <input type="number" name="max_lessons_friday" class="form-control" min="1" max="20" value="<?php echo e($timetable->max_lessons_friday ?? 9); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Maks. to paties dalyko per dieną</label>
                        <input type="number" name="max_same_subject_per_day" class="form-control" min="1" max="20" value="<?php echo e($timetable->max_same_subject_per_day ?? 3); ?>">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="use_priority_logic" id="usePriorityLogic" class="form-check-input" <?php echo e(($timetable->use_priority_logic ?? true) ? 'checked' : ''); ?>>
                            <label for="usePriorityLogic" class="form-check-label">
                                Naudoti prioritetinų pamokų logiką (pamokos su prioritetu ≥ 3 dėsis į 1-5 pamokas)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Išsaugoti</button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/admin/timetables/partials/settings-modal.blade.php ENDPATH**/ ?>