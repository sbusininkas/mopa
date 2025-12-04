
<div class="sidebar-section-title">
    <i class="bi bi-shield-check-fill"></i> Sistemos Valdymas
</div>
<nav class="nav flex-column">
    <a href="<?php echo e(route('schools.index')); ?>" class="nav-link <?php echo e(request()->routeIs('schools.index') ? 'active' : ''); ?>">
        <i class="bi bi-buildings-fill"></i>
        <span>Mokyklos</span>
    </a>
    <a href="<?php echo e(route('users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
        <i class="bi bi-people-fill"></i>
        <span>Vartotojai</span>
    </a>
</nav>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/partials/sidebar-supervisor.blade.php ENDPATH**/ ?>