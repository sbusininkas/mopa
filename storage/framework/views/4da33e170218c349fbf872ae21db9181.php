

<?php $__env->startSection('content'); ?>
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
        <div class="card-header">
            <strong>Grupės tvarkaraštis</strong>
        </div>
        <div class="card-body">
            <?php if($slots->isEmpty()): ?>
                <div class="alert alert-info">Šiai grupei dar nėra suplanuotų pamokų.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Diena</th>
                                <th>Pamoka (valanda)</th>
                                <th>Mokytojas</th>
                                <th>Kabinetas</th>
                                <th>Dalykas</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($slot->day); ?></td>
                                <td><?php echo e($slot->slot); ?></td>
                                <td><?php echo e($group->teacherLoginKey?->full_name ?? '—'); ?></td>
                                <td><?php echo e($group->room?->number ?? '—'); ?></td>
                                <td><?php echo e($group->subject?->name ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/admin/timetables/group-details.blade.php ENDPATH**/ ?>