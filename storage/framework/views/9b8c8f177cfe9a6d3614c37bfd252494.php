
<?php
    $currentSchool = $school ?? $activeSchool ?? $currentSchool ?? null;
?>

<?php if($currentSchool): ?>
    <div class="sidebar-section-title" title="<?php echo e($currentSchool->name); ?>">
        <i class="bi bi-building-fill"></i>
        <span><?php echo e(Str::limit($currentSchool->name, 20)); ?></span>
    </div>
    <nav class="nav flex-column">
        <a href="<?php echo e(route('school.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('school.dashboard') || request()->routeIs('schools.dashboard') ? 'active' : ''); ?>" title="Dashboard">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo e(route('classes.index')); ?>" class="nav-link <?php echo e(request()->routeIs('classes.*') || request()->routeIs('schools.classes.*') ? 'active' : ''); ?>" title="Klasės">
            <i class="bi bi-collection-fill"></i>
            <span>Klasės</span>
        </a>
        <a href="<?php echo e(route('import.index')); ?>" class="nav-link <?php echo e(request()->routeIs('import.*') || request()->routeIs('schools.login-keys.import') ? 'active' : ''); ?>" title="Importavimas">
            <i class="bi bi-cloud-upload-fill"></i>
            <span>Importavimas</span>
        </a>
        <a href="<?php echo e(route('login-keys.index')); ?>" class="nav-link <?php echo e(request()->routeIs('login-keys.*') || request()->routeIs('schools.login-keys.*') ? 'active' : ''); ?>" title="Prisijungimo raktai">
            <i class="bi bi-key-fill"></i>
            <span>Prisijungimo raktai</span>
        </a>
        <a href="<?php echo e(route('subjects.index')); ?>" class="nav-link <?php echo e(request()->routeIs('subjects.*') || request()->routeIs('schools.subjects.*') ? 'active' : ''); ?>" title="Dalykai">
            <i class="bi bi-book-fill"></i>
            <span>Dalykai</span>
        </a>
        <a href="<?php echo e(route('timetables.index')); ?>" class="nav-link <?php echo e(request()->routeIs('timetables.*') || request()->routeIs('schools.timetables.*') ? 'active' : ''); ?>" title="Tvarkaraščiai">
            <i class="bi bi-calendar3"></i>
            <span>Tvarkaraščiai</span>
        </a>
        <a href="<?php echo e(route('rooms.index')); ?>" class="nav-link <?php echo e(request()->routeIs('rooms.*') || request()->routeIs('schools.rooms.*') ? 'active' : ''); ?>" title="Kabinetai">
            <i class="bi bi-door-closed-fill"></i>
            <span>Kabinetai</span>
        </a>
    </nav>

    
    <div class="sidebar-section-title mt-3" title="Nustatymai">
        <i class="bi bi-gear-fill"></i>
        <span>Nustatymai</span>
    </div>
    <nav class="nav flex-column">
        <a href="<?php echo e(route('school.settings')); ?>" class="nav-link <?php echo e(request()->routeIs('school.settings') || request()->routeIs('schools.edit') ? 'active' : ''); ?>" title="Mokyklos duomenys">
            <i class="bi bi-pencil-square"></i>
            <span>Mokyklos duomenys</span>
        </a>
        <a href="<?php echo e(route('school.contacts')); ?>" class="nav-link <?php echo e(request()->routeIs('school.contacts') || request()->routeIs('schools.edit-contacts') ? 'active' : ''); ?>" title="Kontaktai">
            <i class="bi bi-telephone-fill"></i>
            <span>Kontaktai</span>
        </a>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/partials/sidebar-supervisor-school.blade.php ENDPATH**/ ?>