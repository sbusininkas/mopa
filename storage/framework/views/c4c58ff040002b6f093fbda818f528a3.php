<?php $__env->startSection('content'); ?>
<div style="width: 100%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-door-closed"></i> <?php echo e($room->number); ?> <?php echo e($room->name); ?> — Kabineto tvarkaraštis</h2>
        <div class="btn-group">
            <a href="<?php echo e(route('schools.timetables.teachers-view', [$school, $timetable])); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Atgal
            </a>
            <button id="btnFullscreen" class="btn btn-primary" type="button">
                <i class="bi bi-arrows-fullscreen"></i> Visas ekranas
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-2" style="overflow-x: auto;">
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px; text-align: center; vertical-align: middle;"><strong>Valanda</strong></th>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th style="text-align: center; width: 150px;"><strong><?php echo e($label); ?></strong></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for($hour = 1; $hour <= $maxHour; $hour++): ?>
                        <tr>
                            <td style="text-align: center; font-weight: bold; vertical-align: middle;"><?php echo e($hour); ?></td>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $cell = $slots[$hour][$code] ?? null;
                                    $isLast = $hour === $maxHour;
                                ?>
                                <td class="text-center lesson-col timetable-cell" style="padding:0.3rem; min-height: 60px; cursor: pointer; transition: background-color 0.2s ease; <?php echo e($isLast ? 'border-bottom: 2px solid #999;' : ''); ?>" data-day="<?php echo e($code); ?>" data-slot="<?php echo e($hour); ?>">
                                    <?php if($cell): ?>
                                        <div class="p-1" style="background-color: #e8f4f8; border-radius: 4px;">
                                            <div><strong style="font-size: 0.9rem;"><?php echo e($cell['group']); ?></strong></div>
                                            <div><small style="color: #666;"><?php echo e($cell['subject'] ?? '—'); ?></small></div>
                                            <div><small style="color: #999;"><?php echo e($cell['teacher_name'] ?? '—'); ?></small></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
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

<script>
document.getElementById('btnFullscreen')?.addEventListener('click', function() {
    const elem = document.querySelector('.card');
    if (elem.requestFullscreen) {
        elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
        elem.webkitRequestFullscreen();
    }
});

// Click handler to highlight cells with same day/slot
document.addEventListener('DOMContentLoaded', function() {
    const cells = document.querySelectorAll('.timetable-cell');
    
    cells.forEach(cell => {
        cell.addEventListener('mouseenter', function() {
            const day = this.dataset.day;
            const slot = this.dataset.slot;
            
            // Highlight all cells with same day and slot
            if (day && slot) {
                document.querySelectorAll(`.timetable-cell[data-day="${day}"][data-slot="${slot}"]`).forEach(c => {
                    c.classList.add('room-highlighted');
                });
            }
        });

        cell.addEventListener('mouseleave', function() {
            // Remove all highlights
            document.querySelectorAll('.timetable-cell.room-highlighted').forEach(c => {
                c.classList.remove('room-highlighted');
            });
        });
    });
});
</script>

<style>
    .timetable-cell {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .timetable-cell:hover {
        background-color: #f8f9fa;
    }
    
    .timetable-cell.room-highlighted {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
        box-shadow: inset 0 0 0 1px #ffc107;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/room-view.blade.php ENDPATH**/ ?>