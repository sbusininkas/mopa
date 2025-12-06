<div class="modern-card mb-3">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-sliders"></i> Dabartiniai nustatymai</div>
        <button type="button" class="btn btn-sm btn-light" style="background-color: white; border: 1px solid #dee2e6;" data-bs-toggle="collapse" data-bs-target="#currentSettingsCollapse">
            Peržiūrėti
        </button>
    </div>
    <div id="currentSettingsCollapse" class="collapse <?php echo e($timetable->generation_status === 'running' ? 'show' : ''); ?>">
    <div class="card-body">
        <?php if($timetable->generation_status === 'running'): ?>
            <div class="mb-3">
                <label class="form-label small text-muted">Generavimo eiga</label>
                <div class="progress" style="height: 20px;">
                    <div id="generationProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo e($timetable->generation_progress); ?>%;">
                        <?php echo e($timetable->generation_progress); ?>%
                    </div>
                </div>
            </div>
        <?php elseif($timetable->generation_status === 'completed'): ?>
            <div class="alert alert-success py-2 mb-3">
                <i class="bi bi-check-circle"></i> Tvarkaraštis sugeneruotas.
                <?php ($report = $timetable->generation_report ?? []); ?>
                <?php ($uns = $report['unscheduled_count'] ?? 0); ?>
                <?php ($attempts = $report['attempts'] ?? 1); ?>
                <?php ($bestAttempt = $report['best_attempt'] ?? 1); ?>
                <?php ($placedUnits = $report['placed_units'] ?? 0); ?>
                <?php ($totalUnits = $report['total_units'] ?? 0); ?>
                
                <span class="ms-2">
                    <span class="badge bg-info"><?php echo e($attempts); ?> bandymai</span>
                    <span class="badge bg-primary">Geriausias: #<?php echo e($bestAttempt); ?></span>
                    <span class="badge bg-success">Įtraukta: <?php echo e($placedUnits); ?>/<?php echo e($totalUnits); ?></span>
                </span>
                
                <?php if($uns > 0): ?>
                    <span class="ms-2 badge bg-warning text-dark">Nepaskirstyta: <?php echo e($uns); ?></span>
                <?php endif; ?>
            </div>
        <?php elseif($timetable->generation_status === 'failed'): ?>
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-triangle"></i> Generavimas nepavyko. Bandykite dar kartą.
            </div>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-2">
                <div class="small text-muted">Pirmadienis</div>
                <div><strong><?php echo e($timetable->max_lessons_monday ?? 9); ?></strong></div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted">Antradienis</div>
                <div><strong><?php echo e($timetable->max_lessons_tuesday ?? 9); ?></strong></div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted">Trečiadienis</div>
                <div><strong><?php echo e($timetable->max_lessons_wednesday ?? 9); ?></strong></div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted">Ketvirtadienis</div>
                <div><strong><?php echo e($timetable->max_lessons_thursday ?? 9); ?></strong></div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted">Penktadienis</div>
                <div><strong><?php echo e($timetable->max_lessons_friday ?? 9); ?></strong></div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted">Vieno dalyko/diena</div>
                <div><strong><?php echo e($timetable->max_same_subject_per_day ?? 3); ?></strong></div>
            </div>
        </div>
    </div>
    </div>
</div>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/admin/timetables/partials/current-settings.blade.php ENDPATH**/ ?>