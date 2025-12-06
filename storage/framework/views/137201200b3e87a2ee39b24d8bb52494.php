<div class="modern-card mb-4">
    <div class="modern-card-header">
        <i class="bi bi-plus-circle"></i> Sukurti naują grupę
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('schools.timetables.groups.store', [$school, $timetable])); ?>" class="row g-3">
            <?php echo csrf_field(); ?>
            <div class="col-md-3">
                <label class="form-label">Grupės pavadinimas *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dalykas</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- Nepasirinkta --</option>
                    <?php $__currentLoopData = $school->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Mokytojas</label>
                <select name="teacher_login_key_id" class="form-select">
                    <option value="">-- Nepasirinkta --</option>
                    <?php $__currentLoopData = $school->loginKeys()->where('type','teacher')->orderBy('last_name')->orderBy('first_name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->full_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kabinetas</label>
                <select name="room_id" class="form-select">
                    <option value="">-- Nepasirinkta --</option>
                    <?php $__currentLoopData = $school->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($room->id); ?>"><?php echo e($room->number); ?> <?php echo e($room->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Savaitė</label>
                <select name="week_type" class="form-select">
                    <option value="all">Visos</option>
                    <option value="even">Lyg.</option>
                    <option value="odd">Nelyg.</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Pam./sav.</label>
                <input type="number" name="lessons_per_week" class="form-control" min="1" max="20" value="1" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_priority" id="isPriority" class="form-check-input" value="1">
                    <label class="form-check-label" for="isPriority" title="Prioritetinė pamoka (1-5 pamokos)">
                        <i class="bi bi-star"></i>
                    </label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg"></i> Pridėti grupę</button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/admin/timetables/partials/group-create-form.blade.php ENDPATH**/ ?>