<?php $__env->startSection('content'); ?>
    <style>
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 16px;
            margin: 0;
            opacity: 0.9;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
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
            font-size: 40px;
            margin-bottom: 10px;
        }

        .stat-icon.primary {
            color: var(--primary-color);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px 10px 0 0;
            padding: 20px;
            font-weight: 600;
        }

        .table thead th {
            background: #f7f7ff;
            border-bottom: 2px solid #e0e0e0;
            color: #333;
            font-weight: 600;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f7f7ff;
        }
    </style>

    <div class="welcome-section mb-4">
        <h2><i class="bi bi-wave"></i> Sveiki, <?php echo e(Auth::user()->name); ?>!</h2>
        <p>Jūsų sistemos apžvalga ir vadybinės funkcijos</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-building"></i>
                </div>
                <h5>Mokyklos</h5>
                <p class="fs-5 fw-bold"><?php echo e(Auth::user()->isSupervisor() ? \App\Models\School::count() : Auth::user()->schools->count()); ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #48bb78;">
                    <i class="bi bi-people"></i>
                </div>
                <h5>Vartotojai</h5>
                <p class="fs-5 fw-bold"><?php echo e(\App\Models\User::count()); ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #ed8936;">
                    <i class="bi bi-collection"></i>
                </div>
                <h5>Klasės</h5>
                <p class="fs-5 fw-bold"><?php echo e(\App\Models\SchoolClass::count()); ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #f56565;">
                    <i class="bi bi-key"></i>
                </div>
                <h5>Raktai</h5>
                <p class="fs-5 fw-bold"><?php echo e(\App\Models\LoginKey::count()); ?></p>
            </div>
        </div>
    </div>

    <?php if(Auth::user()->isSupervisor()): ?>
        <!-- Schools List for Supervisor -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-building"></i> Mokyklos
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mokykla</th>
                                <th>Adresas</th>
                                <th>Telefonas</th>
                                <th>Vartotojai</th>
                                <th>Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = \App\Models\School::withCount('users')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><strong><?php echo e($school->name); ?></strong></td>
                                    <td><?php echo e($school->address ?: '-'); ?></td>
                                    <td><?php echo e($school->phone ?: '-'); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo e($school->users_count); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('schools.dashboard', $school)); ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Valdyti
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Schools for Regular Users -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-building"></i> Mano mokyklos
            </div>
            <div class="card-body p-0">
                <?php if(Auth::user()->schools->isEmpty()): ?>
                    <div class="alert alert-info m-3">
                        Jūs nėra priskirtas prie jokios mokyklos. <a href="<?php echo e(route('profile.activate-key')); ?>">Suaktyvinkite raktą</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mokykla</th>
                                    <th>Jūsų rolė</th>
                                    <th>Veiksmai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = Auth::user()->schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $role = Auth::user()->isSchoolAdmin($school->id) ? 'Administratorius' : 'Naudotojas';
                                    ?>
                                    <tr>
                                        <td><strong><?php echo e($school->name); ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo e($role); ?></span>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('schools.dashboard', $school)); ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-arrow-right"></i> Peržiūrėti
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/dashboard/index.blade.php ENDPATH**/ ?>