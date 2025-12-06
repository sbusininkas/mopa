
<?php
    $user = Auth::user();
    $currentSchool = $school ?? $activeSchool ?? null;
?>


<?php if($user->isSupervisor()): ?>
    <?php echo $__env->make('partials.sidebar-supervisor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    
    <?php if($currentSchool): ?>
        <hr class="sidebar-divider my-3">
        <?php echo $__env->make('partials.sidebar-supervisor-school', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php endif; ?>


<?php if(!$user->isSupervisor() && $currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool)): ?>
    <?php echo $__env->make('partials.sidebar-school-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>


<?php if($user->isTeacher() && !$user->isSupervisor() && !($currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool))): ?>
    <?php echo $__env->make('partials.sidebar-teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>


<?php if($user->isStudent() && !$user->isSupervisor() && !($currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool))): ?>
    <?php echo $__env->make('partials.sidebar-student', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>


<?php if(!$user->isSupervisor() && !$user->isTeacher() && !$user->isStudent() && !($currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool))): ?>
    <div class="alert alert-warning mx-2" role="alert">
        <i class="bi bi-exclamation-triangle"></i> 
        <strong>Prieiga nesuteikta</strong>
        <p class="mb-0 mt-2 small">Jūs neturite priskirtos rolės. Susisiekite su administratoriumi.</p>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>