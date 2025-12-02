<?php
    $active = $activeSchool ?? null;
    $user = Auth::user();
    if ($user->isSupervisor()) {
        // Supervisor sees all schools
        $available = \App\Models\School::orderBy('name')->get();
    } else {
        // School admin sees only schools where they are admin
        $available = $user->schools()->wherePivot('is_admin', 1)->orderBy('name')->get();
    }
?>

<div class="d-flex align-items-center me-3">
    <form method="POST" action="<?php echo e(route('schools.switch', ['school' => 0])); ?>" id="switchSchoolForm" style="display:inline-block; margin-right:10px;">
        <?php echo csrf_field(); ?>
    </form>

    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded px-3 py-2 me-2" style="backdrop-filter: blur(10px);">
        <i class="bi bi-building text-white me-2 fs-5"></i>
        <div>
            <div class="text-white-50 small" style="font-size: 0.75rem; line-height: 1;">Aktyvi mokykla</div>
            <?php if($active): ?>
                <div class="text-white fw-bold" style="font-size: 0.95rem; line-height: 1.2;"><?php echo e($active->name); ?></div>
            <?php else: ?>
                <div class="text-white-50 fst-italic" style="font-size: 0.9rem;">(nepasirinkta)</div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle shadow-sm" type="button" id="schoolSwitchDropdown" data-bs-toggle="dropdown" style="font-weight: 500;">
                <i class="bi bi-arrow-repeat me-1"></i> Perjungti
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="schoolSwitchDropdown">
                <?php $__currentLoopData = $available; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <form method="POST" action="<?php echo e(route('schools.switch', $school)); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="dropdown-item d-flex align-items-center" type="submit">
                                <i class="bi bi-building me-2 <?php echo e(optional($active)->id === $school->id ? 'text-primary' : 'text-muted'); ?>"></i>
                                <span><?php echo e($school->name); ?></span>
                                <?php if(optional($active)->id === $school->id): ?>
                                    <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                <?php endif; ?>
                            </button>
                        </form>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/partials/active_school.blade.php ENDPATH**/ ?>