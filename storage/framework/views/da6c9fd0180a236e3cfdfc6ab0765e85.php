
<div class="sidebar-section-title" title="Sistemos Valdymas">
    <i class="bi bi-shield-check-fill"></i>
    <span>Sistemos Valdymas</span>
</div>
<nav class="nav flex-column">
    <a href="<?php echo e(route('schools.index')); ?>" class="nav-link <?php echo e(request()->routeIs('schools.index') ? 'active' : ''); ?>" title="Mokyklos">
        <i class="bi bi-buildings-fill"></i>
        <span>Mokyklos</span>
    </a>
    <a href="<?php echo e(route('users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>" title="Vartotojai">
        <i class="bi bi-people-fill"></i>
        <span>Vartotojai</span>
    </a>
</nav>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/partials/sidebar-supervisor.blade.php ENDPATH**/ ?>