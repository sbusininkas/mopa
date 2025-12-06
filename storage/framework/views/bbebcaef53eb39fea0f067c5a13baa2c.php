

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-book-half"></i> <?php echo e($subject); ?> — Grupės</h2>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>">
                <i class="bi bi-arrow-left"></i> Atgal į tvarkaraštį
            </a>
        </div>
    </div>

    <?php if(count($groups) === 0): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Šiam dalykui nėra sudarytų grupių.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Grupė</th>
                        <th class="text-center" style="width: 120px;">Mokytojas</th>
                        <th class="text-center" style="width: 100px;">Kabinetas</th>
                        <th class="text-center" style="width: 80px;">Mokiniai</th>
                        <th class="text-center" style="width: 100px;">Suplanuota</th>
                        <th class="text-center" style="width: 100px;">Nesuplanuota</th>
                        <th class="text-center" style="width: 60px;">Veiksmai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('schools.timetables.groups.details', [$school, $timetable, $group['id']])); ?>" class="text-decoration-none">
                                    <strong><?php echo e($group['name']); ?></strong>
                                </a>
                            </td>
                            <td class="text-center">
                                <?php if($group['teacher_id'] && $group['teacher_name']): ?>
                                    <a href="<?php echo e(route('schools.timetables.teacher', [$school, $timetable, $group['teacher_id']])); ?>" 
                                       class="text-decoration-none link-primary"
                                       title="Atidaryti mokytojo tvarkaraštį">
                                        <small><?php echo e($group['teacher_name']); ?></small>
                                    </a>
                                <?php else: ?>
                                    <small><?php echo e($group['teacher_name'] ?? '—'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($group['room_number']): ?>
                                    <span class="badge bg-dark"><?php echo e($group['room_number']); ?>

                                        <?php if($group['room_name']): ?>
                                            <?php echo e($group['room_name']); ?>

                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark"><?php echo e($group['students_count']); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success"><?php echo e($group['scheduled_count']); ?></span>
                            </td>
                            <td class="text-center">
                                <?php if($group['unscheduled_count'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?php echo e($group['unscheduled_count']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo e(route('schools.timetables.groups.details', [$school, $timetable, $group['id']])); ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="Atidaryti grupę">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5><i class="bi bi-info-circle"></i> Informacija</h5>
                <ul class="mb-0">
                    <li><strong>Iš viso grupių:</strong> <?php echo e(count($groups)); ?></li>
                    <li><strong>Iš viso mokinių:</strong> <?php echo e($groups->sum('students_count')); ?></li>
                    <li><strong>Iš viso suplanuotų pamokų:</strong> <?php echo e($groups->sum('scheduled_count')); ?></li>
                    <li><strong>Iš viso nesuplanuotų pamokų:</strong> <?php echo e($groups->sum('unscheduled_count')); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/admin/timetables/subject-groups.blade.php ENDPATH**/ ?>