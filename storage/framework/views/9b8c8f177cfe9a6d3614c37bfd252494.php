
<?php
    $currentSchool = $school ?? $activeSchool ?? $currentSchool ?? null;
?>

<?php if($currentSchool): ?>
    <div class="sidebar-section-title">
        <i class="bi bi-building-fill"></i> <?php echo e(Str::limit($currentSchool->name, 20)); ?>

    </div>
    <nav class="nav flex-column">
        <a href="<?php echo e(route('school.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('school.dashboard') || request()->routeIs('schools.dashboard') ? 'active' : ''); ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo e(route('classes.index')); ?>" class="nav-link <?php echo e(request()->routeIs('classes.*') || request()->routeIs('schools.classes.*') ? 'active' : ''); ?>">
            <i class="bi bi-collection-fill"></i>
            <span>Klasės</span>
        </a>
        <a href="<?php echo e(route('import.index')); ?>" class="nav-link <?php echo e(request()->routeIs('import.*') || request()->routeIs('schools.login-keys.import') ? 'active' : ''); ?>">
            <i class="bi bi-cloud-upload-fill"></i>
            <span>Importavimas</span>
        </a>
        <a href="<?php echo e(route('login-keys.index')); ?>" class="nav-link <?php echo e(request()->routeIs('login-keys.*') || request()->routeIs('schools.login-keys.*') ? 'active' : ''); ?>">
            <i class="bi bi-key-fill"></i>
            <span>Prisijungimo raktai</span>
        </a>
        <a href="<?php echo e(route('subjects.index')); ?>" class="nav-link <?php echo e(request()->routeIs('subjects.*') || request()->routeIs('schools.subjects.*') ? 'active' : ''); ?>">
            <i class="bi bi-book-fill"></i>
            <span>Dalykai</span>
        </a>
        <a href="<?php echo e(route('timetables.index')); ?>" class="nav-link <?php echo e(request()->routeIs('timetables.*') || request()->routeIs('schools.timetables.*') ? 'active' : ''); ?>">
            <i class="bi bi-calendar3"></i>
            <span>Tvarkaraščiai</span>
        </a>
        <a href="<?php echo e(route('rooms.index')); ?>" class="nav-link <?php echo e(request()->routeIs('rooms.*') || request()->routeIs('schools.rooms.*') ? 'active' : ''); ?>">
            <i class="bi bi-door-closed-fill"></i>
            <span>Kabinetai</span>
        </a>
    </nav>

    
    <div class="sidebar-section-title mt-3">
        <i class="bi bi-gear-fill"></i> Nustatymai
    </div>
    <nav class="nav flex-column">
        <a href="<?php echo e(route('school.settings')); ?>" class="nav-link <?php echo e(request()->routeIs('school.settings') || request()->routeIs('schools.edit') ? 'active' : ''); ?>">
            <i class="bi bi-pencil-square"></i>
            <span>Mokyklos duomenys</span>
        </a>
        <a href="<?php echo e(route('school.contacts')); ?>" class="nav-link <?php echo e(request()->routeIs('school.contacts') || request()->routeIs('schools.edit-contacts') ? 'active' : ''); ?>">
            <i class="bi bi-telephone-fill"></i>
            <span>Kontaktai</span>
        </a>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/partials/sidebar-supervisor-school.blade.php ENDPATH**/ ?>