<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar3"></i> Tvarkaraščiai - <?php echo e($school->name); ?></h2>
        <form method="POST" action="<?php echo e(route('schools.timetables.store', $school)); ?>" class="d-flex gap-2">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" class="form-control" placeholder="Naujo tvarkaraščio pavadinimas" required>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Sukurti</button>
        </form>
    </div>

    <div class="modern-table-wrapper">
        <table class="modern-table table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-tag"></i> Pavadinimas</th>
                    <th><i class="bi bi-eye"></i> Viešas</th>
                    <th class="text-end">Veiksmai</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $timetables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('schools.timetables.show', [$school, $t])); ?>"><?php echo e($t->name); ?></a></td>
                    <td>
                        <?php if($t->is_public): ?>
                            <span class="badge badge-modern bg-success">Taip</span>
                        <?php else: ?>
                            <span class="badge badge-modern bg-secondary">Ne</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <?php if(!$t->is_public): ?>
                            <form method="POST" action="<?php echo e(route('schools.timetables.set-public', [$school, $t])); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-outline-success"><i class="bi bi-eye"></i> Rodyti viešai</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?php echo e(route('schools.timetables.copy', [$school, $t])); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-outline-info"><i class="bi bi-files"></i> Kopijuoti</button>
                            </form>
                            <form method="POST" action="<?php echo e(route('schools.timetables.destroy', [$school, $t])); ?>" onsubmit="return confirm('Ar tikrai?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Trinti</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Nėra tvarkaraščių</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/index.blade.php ENDPATH**/ ?>