<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-person-badge"></i> <?php echo e($student->full_name); ?> — Tvarkaraštis</h2>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>">
                <i class="bi bi-arrow-left"></i> Atgal į tvarkaraštį
            </a>
            <a class="btn btn-outline-primary" href="<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>">
                <i class="bi bi-calendar3"></i> Tvarkaraščio nustatymai
            </a>
        </div>
    </div>

    <!-- Nesuplanuotų pamokų panelė VIRŠ lentelės -->
    <div class="card mb-3" id="unscheduledPanel">
        <div class="card-header p-2"><strong>Nesuplanuotos pamokos (šiam mokiniui)</strong></div>
        <div class="card-body p-2" style="max-height: 150px; overflow:auto;">
            <?php $__empty_1 = true; $__currentLoopData = ($unscheduled ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="unscheduled-item mb-1 d-flex align-items-center"
                     data-kind="unscheduled"
                     data-group-id="<?php echo e($u['group_id']); ?>"
                     data-group-name="<?php echo e($u['group_name'] ?? $u['group'] ?? ''); ?>"
                     data-subject-name="<?php echo e($u['subject_name'] ?? $u['subject'] ?? ''); ?>"
                     data-teacher-id="<?php echo e($u['teacher_login_key_id'] ?? ''); ?>"
                     data-teacher-name="<?php echo e($u['teacher_name'] ?? $u['teacher'] ?? ''); ?>"
                     data-remaining="<?php echo e($u['remaining_lessons']); ?>">
                    <div class="flex-grow-1">
                        <div class="unscheduled-title">
                            <a href="<?php echo e(route('schools.timetables.groups.details', [$school, $timetable, $u['group_id']])); ?>" 
                               class="unscheduled-group-link" 
                               onclick="event.stopPropagation()">
                                <?php echo e($u['group_name'] ?? $u['group'] ?? 'Grupė'); ?>

                            </a>
                            <span class="badge bg-primary ms-2 remaining-badge"><?php echo e($u['remaining_lessons']); ?></span>
                        </div>
                        <div class="unscheduled-meta">
                            <a href="<?php echo e(route('schools.timetables.subject-groups', [$school, $timetable, $u['subject_name'] ?? $u['subject'] ?? ''])); ?>" 
                               class="unscheduled-subject-link"
                               onclick="event.stopPropagation()">
                                <?php echo e($u['subject_name'] ?? $u['subject'] ?? ''); ?>

                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <span class="text-muted small">Nėra neužpildytų pamokų šiam mokiniui</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tvarkaraščio lentelė - pilna plotis -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="studentGrid" data-student-id="<?php echo e($student->id); ?>">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:60px" class="text-center">#</th>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="text-center"><?php echo e($label); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($row=1; $row <= $maxRows; $row++): ?>
                            <tr>
                                <td class="text-center fw-bold sticky-col-row"><?php echo e($row); ?></td>
                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php 
                                        $cell = $grid[$row][$code] ?? null;
                                        if ($cell) {
                                            $subject = $cell['subject'] ?? '—';
                                            $roomNumber = $cell['room_number'] ?? null;
                                            $roomName = $cell['room_name'] ?? null;
                                            $roomDisplay = $roomNumber ? ($roomNumber . ($roomName ? ' ' . $roomName : '')) : '—';
                                            $dayLabel = $label;
                                            $lessonNr = $row;
                                            $teacherName = $cell['teacher_name'] ?? '—';
                                            // Tooltip su visa informacija
                                            $tooltipHtml = '<div class="tt-inner">'
                                                .'<div class="tt-row tt-row-head"><i class="bi bi-clock-history tt-ico"></i><span class="tt-val">'.e($dayLabel).' • '.e($lessonNr).' pamoka</span></div>'
                                                .'<div class="tt-divider"></div>'
                                                .'<div class="tt-row"><i class="bi bi-collection-fill tt-ico"></i><span class="tt-val">'.e($cell['group']).'</span></div>'
                                                .'<div class="tt-row"><i class="bi bi-book-half tt-ico"></i><span class="tt-val">'.e($subject).'</span></div>'
                                                .'<div class="tt-row"><i class="bi bi-door-closed tt-ico"></i><span class="tt-val">'.e($roomDisplay).'</span></div>'
                                                .'<div class="tt-row"><i class="bi bi-person-badge tt-ico"></i><span class="tt-val">'.e($teacherName).'</span></div>'
                                            .'</div>';
                                            $tooltipB64 = base64_encode($tooltipHtml);
                                        }
                                    ?>
                                    <td class="text-center lesson-col timetable-cell" style="min-width:220px" data-day="<?php echo e($code); ?>" data-slot="<?php echo e($row); ?>" data-student-id="<?php echo e($student->id); ?>">
                                        <?php if($cell): ?>
                                            <span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:pointer;" 
                                                data-tooltip-b64="<?php echo e($tooltipB64); ?>">
                                                <?php echo e($cell['group']); ?><?php echo e($roomNumber ? ' (' . $roomNumber . ')' : ''); ?><br/>
                                                <small><?php echo e($subject); ?></small>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .sticky-col-row {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    .table-responsive {
        border: 1px solid #dee2e6;
    }
    
    .timetable-cell {
        padding: 8px 4px;
        min-height: 80px;
        vertical-align: middle;
        background-color: #ffffff;
    }
    
    .timetable-cell:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        display: inline-block;
        max-width: 95%;
        word-wrap: break-word;
        white-space: normal;
        padding: 6px 10px;
    }
    
    /* Unscheduled item links */
    .unscheduled-group-link,
    .unscheduled-subject-link {
        color: #0d6efd;
        text-decoration: none;
        border-bottom: 1px dashed #0d6efd;
        transition: all 0.2s ease;
    }

    .unscheduled-group-link:hover,
    .unscheduled-subject-link:hover {
        color: #0b5ed7;
        border-bottom: 1px solid #0b5ed7;
        text-decoration: none;
    }
    
    /* Tooltip styles */
    .tt-trigger {
        position: relative;
        transition: all 0.2s ease;
    }
    
    .tt-trigger:hover {
        opacity: 0.8;
        text-decoration: underline;
    }
    
    .tt-popup {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
    }
    
    .tt-inner {
        min-width: 220px;
        background: white;
        padding: 10px;
        font-size: 0.85rem;
        color: #333;
    }
    
    .tt-row {
        display: flex;
        align-items: center;
        margin: 6px 0;
        line-height: 1.4;
    }
    
    .tt-row-head {
        font-weight: 600;
        color: #0d6efd;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 6px;
        margin-bottom: 6px;
    }
    
    .tt-ico {
        margin-right: 8px;
        color: #0d6efd;
        flex-shrink: 0;
        font-size: 1rem;
    }
    
    .tt-val {
        color: #333;
        flex: 1;
        font-weight: 500;
    }
    
    .tt-divider {
        height: 1px;
        background-color: #dee2e6;
        margin: 6px 0;
    }
    
    .unscheduled-item {
        padding: 8px;
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 4px;
    }
    
    .unscheduled-title {
        font-weight: bold;
        color: #333;
    }
    
    .unscheduled-meta {
        font-size: 0.85rem;
        color: #666;
    }
    
    .unscheduled-subject {
        font-style: italic;
    }
</style>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // UTF-8 safe base64 decoder
    function utf8Decode(str) {
        return decodeURIComponent(atob(str).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    }

    // Tooltip functionality for student view
    document.querySelectorAll('.tt-trigger').forEach(trigger => {
        trigger.addEventListener('mouseenter', function(e) {
            const b64 = this.dataset.tooltipB64;
            if (!b64) return;
            
            const html = utf8Decode(b64);
            const tooltip = document.createElement('div');
            tooltip.style.position = 'absolute';
            tooltip.style.zIndex = '9999';
            tooltip.innerHTML = html;
            tooltip.className = 'tt-popup';
            
            document.body.appendChild(tooltip);
            
            const rect = trigger.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            tooltip.style.top = (rect.top + rect.height + 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltipRect.width / 2) + 'px';
            
            const removeTooltip = () => {
                tooltip.remove();
                trigger.removeEventListener('mouseleave', removeTooltip);
                document.removeEventListener('click', removeTooltip);
            };
            
            trigger.addEventListener('mouseleave', removeTooltip);
            document.addEventListener('click', removeTooltip);
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/student-view.blade.php ENDPATH**/ ?>