<?php if($timetable->generation_status === 'completed' && ($timetable->generation_report['unscheduled_count'] ?? 0) > 0): ?>
<div class="modern-card mb-4">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-exclamation-circle"></i> Nepaskirstytos pamokos</div>
        <button type="button" class="btn btn-sm btn-light" style="background-color: white; border: 1px solid #dee2e6;" data-bs-toggle="collapse" data-bs-target="#unscheduledCollapse">
            Peržiūrėti
        </button>
    </div>
    <div id="unscheduledCollapse" class="collapse show">
        <div class="card-body">
            <?php ($attempts = $timetable->generation_report['attempts'] ?? 5); ?>
            <?php ($bestAttempt = $timetable->generation_report['best_attempt'] ?? 1); ?>
            <p class="small text-muted mb-2">
                Nepavyko priskirti visų pamokų po <?php echo e($attempts); ?> bandymų. 
                <strong>Rodomas geriausias variantas (bandymas #<?php echo e($bestAttempt); ?>)</strong> su mažiausiai konfliktų. 
                Patikrinkite žemiau nurodytas konfliktų priežastis.
            </p>
            <?php ($reasonSummary = $timetable->generation_report['reason_summary_translated'] ?? $timetable->generation_report['reason_summary'] ?? []); ?>
            <div class="row g-2 mb-3">
                <?php $__currentLoopData = $reasonSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rk => $rv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php ($count = is_array($rv) ? ($rv['count'] ?? 0) : $rv); ?>
                    <?php ($label = is_array($rv) ? ($rv['label'] ?? $rk) : $rk); ?>
                    <?php if($count > 0): ?>
                    <div class="col-auto">
                        <span class="badge bg-light text-dark"><?php echo e($label); ?>: <?php echo e($count); ?></span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">Sąrašas nepaskirstytų pamokų:</small>
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mergeUnscheduledGroupsModal" title="Sujungti grupes">
                    <i class="bi bi-merge"></i> Sujungti grupes
                </button>
            </div>
            <div class="modern-table-wrapper">
                <table class="modern-table table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Grupė</th>
                            <th>Dalykas</th>
                            <th>Mokytojas</th>
                            <th>Likę / Prašyta</th>
                            <th>Priežastys (top)</th>
                            <th>Rekomendacija</th>
                            <th>Veiksmai</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = ($timetable->generation_report['unscheduled'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $reasonsTranslated = $item['reasons_translated'] ?? [];
                            $reasons = collect($reasonsTranslated)->filter(fn($v) => ($v['count'] ?? 0) > 0)->sortByDesc(fn($v) => $v['count'] ?? 0)->take(3);
                            
                            $sortedReasons = $reasons->sortBy(fn($v) => $v['count'] ?? 0);
                            $topReason = $sortedReasons->keys()->first();
                            $recommendation = '';
                            $recommendationClass = 'text-info';
                            $conflictCount = $sortedReasons->first()['count'] ?? 0;
                            
                            if ($topReason === 'teacher_conflict') {
                                $recommendation = '⚡ Lengviausias sprendimas: Pakeiskite mokytoją į turintį daugiau laisvo laiko (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-primary';
                            } elseif ($topReason === 'room_conflict') {
                                $recommendation = '🛋️ Lengviausias sprendimas: Pašalinkite kabineto apribojimą arba pridėkite kabinetą (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-success';
                            } elseif ($topReason === 'student_conflict') {
                                $recommendation = '👥 Lengviausias sprendimas: Sumažinkite mokiniams grupių skaičių (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-warning';
                            } elseif ($topReason === 'subject_limit') {
                                $recommendation = '📚 Lengviausias sprendimas: Padidinkite „Maks. to paties dalyko per dieną" (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-info';
                            } elseif ($topReason === 'teacher_not_working') {
                                $recommendation = '📅 Lengviausias sprendimas: Pridėkite daugiau darbo dienų mokytojui (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-danger';
                            } elseif ($topReason === 'no_slot') {
                                $recommendation = '⏰ Lengviausias sprendimas: Padidinkite pamokų skaičių per dieną (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-secondary';
                            } else {
                                $recommendation = '✅ Pabandykite generuoti dar kartą';
                                $recommendationClass = 'text-muted';
                            }
                        ?>
                        <tr class="unscheduled-item" data-group-id="<?php echo e($item['group_id']); ?>">
                            <td>
                                <a href="<?php echo e(route('schools.timetables.groups.details', [$school, $timetable, $item['group_id']])); ?>" 
                                   class="text-decoration-none unscheduled-group-link"
                                   title="Atidaryti grupę">
                                    <?php echo e($item['group_name']); ?>

                                </a>
                            </td>
                            <td>
                                <a href="<?php echo e(route('schools.timetables.subject-groups', [$school, $timetable, $item['subject_name']])); ?>" 
                                   class="text-decoration-none unscheduled-subject-link"
                                   title="Peržiūrėti visas šio dalyko grupes">
                                    <?php echo e($item['subject_name']); ?>

                                </a>
                            </td>
                            <td>
                                <?php if(!empty($item['teacher_name']) && !empty($item['teacher_login_key_id'])): ?>
                                    <a href="<?php echo e(route('schools.timetables.teacher', [$school, $timetable, $item['teacher_login_key_id']])); ?>" 
                                       class="text-decoration-none" 
                                       title="Peržiūrėti mokytojo tvarkaraštį">
                                        <i class="bi bi-person-circle me-1"></i><?php echo e($item['teacher_name']); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <?php echo e($item['remaining_lessons'] ?? 0); ?> /
                                    <?php echo e($item['requested_lessons'] ?? ($item['lessons_per_week'] ?? ($item['total_lessons'] ?? ($item['remaining_lessons'] ?? 0)))); ?>

                                </span>
                            </td>
                            <td class="small">
                                <?php $__empty_1 = true; $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rk => $rv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <span class="badge bg-secondary me-1 mb-1"><?php echo e($rv['label'] ?? $rk); ?>: <?php echo e($rv['count'] ?? 0); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small <?php echo e($recommendationClass); ?>">
                                <i class="bi bi-lightbulb"></i> <?php echo e($recommendation); ?>

                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUnscheduledGroup<?php echo e($item['group_id']); ?>" title="Redaguoti grupę">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#copyUnscheduledGroup<?php echo e($item['group_id']); ?>" title="Kopijuoti nepaskirstytas pamokas">
                                        <i class="bi bi-files"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('admin.timetables.partials.unscheduled-modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/partials/unscheduled-lessons.blade.php ENDPATH**/ ?>