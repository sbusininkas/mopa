<?php $__env->startSection('content'); ?>
    <div class="mb-4">
        <h2><i class="bi bi-upload"></i> Importuoti mokinius ir mokytojus: <?php echo e($school->name); ?></h2>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i>
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Students Import -->
        <div class="col-md-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-people-fill"></i> Importuoti mokinius
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('schools.login-keys.import-students', $school)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Pasirinkite klasę *</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Pasirinkite klasę --</option>
                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Jei klasės nėra, sukurkite ją klasių valdyme.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mokslo metai *</label>
                            <select name="school_year" class="form-select" required>
                                <option value="">-- Pasirinkite --</option>
                                <?php $__currentLoopData = $schoolYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CSV / Excel failas *</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted">
                                Formatas: Viename stulpelyje vardas ir pavardė (pvz. "Petras Petraitis")
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Importuoti mokinius
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Teachers Import -->
        <div class="col-md-6">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-briefcase-fill"></i> Importuoti mokytojus
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('schools.login-keys.import-teachers', $school)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Mokslo metai *</label>
                            <select name="school_year" class="form-select" required>
                                <option value="">-- Pasirinkite --</option>
                                <?php $__currentLoopData = $schoolYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CSV / Excel failas *</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted">
                                Formatas: Viename stulpelyje vardas ir pavardė (pvz. "Laima Laimė")
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-info">
                                <small>Mokytojams klasė nereikalinga.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-upload"></i> Importuoti mokytojus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h5>Pavyzdžiai:</h5>
        <div class="card">
            <div class="card-body">
                <p><strong>Mokiniai (CSV) - Viename stulpelyje vardas ir pavardė:</strong></p>
                <pre>Vardas Pavardė
Petras Petraitis
Gintarė Gintaraitė
Jonas Jonaitis</pre>

                <p class="mt-3"><strong>Mokytojai (CSV) - Viename stulpelyje vardas ir pavardė:</strong></p>
                <pre>Vardas Pavardė
Laima Laimė
Darius Darylis</pre>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/login-keys/import.blade.php ENDPATH**/ ?>