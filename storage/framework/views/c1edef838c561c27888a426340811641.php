<?php $__env->startSection('content'); ?>
    <style>
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 36px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card p {
            font-size: 16px;
            color: #666;
            margin: 0;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 18px;
            margin: 0;
            opacity: 0.9;
        }

        .quick-actions {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h4 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .quick-actions .btn {
            margin-bottom: 10px;
        }
    </style>

    <div class="welcome-section">
        <h2><i class="bi bi-building"></i> <?php echo e($school->name); ?></h2>
        <p><?php echo e($school->address); ?> | <?php echo e($school->phone); ?></p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #667eea;">
                    <i class="bi bi-collection"></i>
                </div>
                <h3><?php echo e($stats['classes_count']); ?></h3>
                <p>Klasės</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #48bb78;">
                    <i class="bi bi-people"></i>
                </div>
                <h3><?php echo e($stats['students_count']); ?></h3>
                <p>Mokiniai</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #ed8936;">
                    <i class="bi bi-briefcase"></i>
                </div>
                <h3><?php echo e($stats['teachers_count']); ?></h3>
                <p>Mokytojai</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #f56565;">
                    <i class="bi bi-calendar3"></i>
                </div>
                <h3><?php echo e($stats['timetables_count']); ?></h3>
                <p>Tvarkaraščiai</p>
                <?php if($stats['active_timetables_count'] > 0): ?>
                    <small class="text-success"><i class="bi bi-check-circle"></i> <?php echo e($stats['active_timetables_count']); ?> aktyvūs</small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-8">
            <div class="quick-actions">
                <h4><i class="bi bi-lightning"></i> Greitos nuorodos</h4>
                <div class="row">
                    <?php if(auth()->user()->isSupervisor()): ?>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.classes.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-collection"></i> Valdyti klases
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.login-keys.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-key"></i> Prisijungimo raktai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.subjects.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-book"></i> Valdyti dalykus
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.timetables.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-calendar3"></i> Tvarkaraščiai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.rooms.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-door-open"></i> Kabinetai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.login-keys.import', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-upload"></i> Importuoti duomenis
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('classes.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-collection"></i> Valdyti klases
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('login-keys.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-key"></i> Prisijungimo raktai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('subjects.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-book"></i> Valdyti dalykus
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('timetables.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-calendar3"></i> Tvarkaraščiai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('rooms.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-door-open"></i> Kabinetai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('import.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-upload"></i> Importuoti duomenis
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-actions">
                <h4><i class="bi bi-gear"></i> Nustatymai</h4>
                <?php if(auth()->user()->isSupervisor()): ?>
                    <a href="<?php echo e(route('schools.edit', $school)); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-building"></i> Mokyklos duomenys
                    </a>
                    <a href="<?php echo e(route('schools.edit-contacts', $school)); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-telephone"></i> Kontaktai
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('school.settings')); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-building"></i> Mokyklos duomenys
                    </a>
                    <a href="<?php echo e(route('school.contacts')); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-telephone"></i> Kontaktai
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/schools/dashboard.blade.php ENDPATH**/ ?>