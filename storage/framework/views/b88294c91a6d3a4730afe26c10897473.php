<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-person-badge"></i> <?php echo e($teacher->full_name); ?> — Tvarkaraštis</h2>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="<?php echo e(route('schools.timetables.teachers-view', [$school, $timetable])); ?>">
                <i class="bi bi-arrow-left"></i> Atgal į mokytojų sąrašą
            </a>
            <a class="btn btn-outline-primary" href="<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>">
                <i class="bi bi-calendar3"></i> Tvarkaraščio nustatymai
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-9">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="teacherGrid" data-teacher-id="<?php echo e($teacher->id); ?>">
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
                                                    $roomDisplay = $roomNumber ?: '—';
                                                    $dayLabel = $label;
                                                    $lessonNr = $row;
                                                    $teacherName = $teacher->full_name ?? '—';
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
                                            <td class="text-center lesson-col drop-target timetable-cell" style="min-width:220px" data-day="<?php echo e($code); ?>" data-slot="<?php echo e($row); ?>" data-teacher-id="<?php echo e($teacher->id); ?>">
                                                <?php if($cell): ?>
                                                    <span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" draggable="true"
                                                        data-tooltip-b64="<?php echo e($tooltipB64); ?>"
                                                        data-kind="scheduled"
                                                        data-slot-id="<?php echo e($cell['slot_id']); ?>"
                                                        data-group-id="<?php echo e($cell['group_id']); ?>"
                                                        data-teacher-id="<?php echo e($teacher->id); ?>"
                                                        data-group-name="<?php echo e($cell['group']); ?>"
                                                        data-subject-name="<?php echo e($cell['subject'] ?? ''); ?>"
                                                    ><?php echo e($cell['group']); ?></span>
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
        </div>
        <div class="col-md-3">
            <div class="card h-100" id="unscheduledPanel">
                <div class="card-header p-2"><strong>Nesuplanuotos (šiam mokytojui)</strong></div>
                <div class="card-body p-2" style="max-height:60vh; overflow:auto;">
                    <?php $__empty_1 = true; $__currentLoopData = ($unscheduled ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="unscheduled-item mb-1 d-flex align-items-center" draggable="true"
                             data-kind="unscheduled"
                             data-group-id="<?php echo e($u['group_id']); ?>"
                             data-group-name="<?php echo e($u['group_name'] ?? $u['group'] ?? ''); ?>"
                             data-subject-name="<?php echo e($u['subject_name'] ?? $u['subject'] ?? ''); ?>"
                             data-teacher-id="<?php echo e($u['teacher_login_key_id'] ?? ''); ?>"
                             data-teacher-name="<?php echo e($u['teacher_name'] ?? $u['teacher'] ?? ''); ?>"
                             data-remaining="<?php echo e($u['remaining_lessons']); ?>">
                            <div class="flex-grow-1">
                                <div class="unscheduled-title">
                                    <?php echo e($u['group_name'] ?? $u['group'] ?? 'Grupė'); ?>

                                    <span class="badge bg-primary ms-2 remaining-badge"><?php echo e($u['remaining_lessons']); ?></span>
                                </div>
                                <div class="unscheduled-meta">
                                    <span class="unscheduled-subject"><?php echo e($u['subject_name'] ?? $u['subject'] ?? ''); ?></span>
                                    <span class="unscheduled-room"><i class="bi bi-door-closed"></i> <?php echo e($u['room_number'] ?? '—'); ?></span>
                                </div>
                            </div>
                            <div class="ms-2">
                                <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="findAvailableSlots(<?php echo e($u['group_id']); ?>, '<?php echo e(addslashes($u['group_name'] ?? $u['group'] ?? '')); ?>', '<?php echo e(addslashes($u['subject_name'] ?? $u['subject'] ?? '')); ?>', <?php echo e($u['teacher_login_key_id'] ?? 'null'); ?>)"
                                        title="Rasti laisvus langelius">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="text-muted small">Nėra neužpildytų pamokų šiam mokytojui</span>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center p-1 small text-muted">
                    <span>Tempkite ant pasirinktų langelių</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAvailabilityMarks()" title="Išvalyti žymėjimus">Išvalyti</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Global variables for drag & drop
let dragged = null;
let draggedKind = null;

document.addEventListener('DOMContentLoaded', function(){
    const grid = document.getElementById('teacherGrid');
    if (!grid) return;

    function initBadgeDrag(el){
        if (!el) return;
        el.addEventListener('dragstart', e => {
            dragged = el;
            draggedKind = 'scheduled';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', el.dataset.slotId || '');
            el.classList.add('dragging');
        });
        el.addEventListener('dragend', () => {
            dragged?.classList.remove('dragging');
            dragged = null;
            draggedKind = null;
        });
        
        // Add right-click context menu to move back to unscheduled
        el.addEventListener('contextmenu', e => {
            e.preventDefault();
            
            // Remove previous selection
            document.querySelectorAll('.lesson-selected').forEach(sel => sel.classList.remove('lesson-selected'));
            
            // Add selection highlight
            el.classList.add('lesson-selected');
            
            const slotId = el.dataset.slotId;
            const groupId = el.dataset.groupId;
            const groupName = el.dataset.groupName || 'Pamoka';
            const subjectName = el.dataset.subjectName || '';
            
            showContextMenu(e, slotId, groupId, groupName, subjectName, el);
        });
        
        // Also add click to select
        el.addEventListener('click', e => {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                // Toggle selection
                if (el.classList.contains('lesson-selected')) {
                    el.classList.remove('lesson-selected');
                } else {
                    document.querySelectorAll('.lesson-selected').forEach(sel => sel.classList.remove('lesson-selected'));
                    el.classList.add('lesson-selected');
                }
            }
        });
    }
    grid.querySelectorAll('.tt-trigger[draggable="true"]').forEach(initBadgeDrag);

    // Make unscheduled items draggable
    document.querySelectorAll('.unscheduled-item').forEach(el => {
        el.addEventListener('dragstart', e => {
            dragged = el;
            draggedKind = 'unscheduled';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', el.dataset.groupId);
            el.classList.add('dragging');
        });
        el.addEventListener('dragend', () => {
            dragged?.classList.remove('dragging');
            dragged = null;
            draggedKind = null;
        });
    });

    grid.querySelectorAll('.drop-target').forEach(cell => {
        cell.addEventListener('dragover', e => {
            if (!dragged) return;
            const rowTeacherId = String(cell.dataset.teacherId || '');
            const itemTeacherId = String(dragged.dataset.teacherId || '');
            const canDrop = !!itemTeacherId && itemTeacherId === rowTeacherId;
            if (!canDrop) {
                e.dataTransfer.dropEffect = 'none';
                cell.classList.remove('drop-hover');
                return;
            }
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            cell.classList.add('drop-hover');
        });
        cell.addEventListener('dragleave', () => cell.classList.remove('drop-hover'));
        cell.addEventListener('drop', async e => {
            e.preventDefault();
            cell.classList.remove('drop-hover');
            if (!dragged) return;
            const teacherId = cell.dataset.teacherId;
            const day = cell.dataset.day;
            const slot = cell.dataset.slot;
            const groupName = dragged.dataset.groupName || 'Grupė';
            const subjectName = dragged.dataset.subjectName || '';

            if (draggedKind === 'scheduled') {
                const slotId = dragged.dataset.slotId;
                const groupId = dragged.dataset.groupId;
                const originalCell = dragged.closest('td');
                
                try {
                    // Try to move (this will check for swap needs)
                    const resp = await fetch(`<?php echo e(route('schools.timetables.move-slot', [$school, $timetable])); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ slot_id: slotId, teacher_id: teacherId, day: day, slot: slot, swap: false })
                    });
                    const data = await resp.json();
                    
                    // Check if swap is needed
                    if (data.needsSwap) {
                        const confirmed = await showSwapDialog(groupName, subjectName, data.targetGroup, data.targetSubject, day, slot);
                        if (!confirmed) return;
                        
                        // Perform swap
                        const swapResp = await fetch(`<?php echo e(route('schools.timetables.move-slot', [$school, $timetable])); ?>`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ slot_id: slotId, teacher_id: teacherId, day: day, slot: slot, swap: true })
                        });
                        const swapData = await swapResp.json();
                        
                        if (!swapResp.ok || !swapData.success) {
                            showErrorModal('Klaida', swapData.error || 'Nepavyko sukeisti pamokų');
                            return;
                        }
                        
                        // Handle swap UI update
                        if (swapData.swapped && swapData.swappedHtml) {
                            // Update original cell with swapped lesson
                            if (originalCell) {
                                const dayNames = {'Mon':'Pirmadienis', 'Tue':'Antradienis', 'Wed':'Trečiadienis', 'Thu':'Ketvirtadienis', 'Fri':'Penktadienis'};
                                const origDay = originalCell.dataset.day;
                                const origSlot = originalCell.dataset.slot;
                                const tooltipB64 = createTooltipData(
                                    swapData.swappedHtml.group,
                                    swapData.swappedHtml.subject ?? '—',
                                    swapData.swappedHtml.room ?? '—',
                                    '<?php echo e($teacher->full_name); ?>',
                                    dayNames[origDay] ?? origDay,
                                    origSlot
                                );
                                const swappedBadgeHtml = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" draggable=\"true\"
                                        data-tooltip-b64=\"${tooltipB64}\"
                                        data-kind=\"scheduled\"
                                        data-slot-id=\"${swapData.swappedHtml.slot_id}\"
                                        data-group-id=\"${swapData.swappedHtml.group_id ?? ''}\"
                                        data-teacher-id=\"${teacherId}\"
                                        data-group-name=\"${swapData.swappedHtml.group}\"
                                        data-subject-name=\"${swapData.swappedHtml.subject ?? ''}\">${swapData.swappedHtml.group}</span>`;
                                originalCell.innerHTML = swappedBadgeHtml;
                                const badge = originalCell.querySelector('.tt-trigger');
                                initBadgeDrag(badge);
                                initTooltip(badge);
                            }
                        }
                        
                        // Update target cell
                        const dayNames = {'Mon':'Pirmadienis', 'Tue':'Antradienis', 'Wed':'Trečiadienis', 'Thu':'Ketvirtadienis', 'Fri':'Penktadienis'};
                        const tooltipB64 = createTooltipData(
                            swapData.html.group,
                            swapData.html.subject ?? '—',
                            swapData.html.room ?? '—',
                            '<?php echo e($teacher->full_name); ?>',
                            dayNames[day] ?? day,
                            slot
                        );
                        const badgeHtml = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" draggable=\"true\"
                                data-tooltip-b64=\"${tooltipB64}\"
                                data-kind=\"scheduled\"
                                data-slot-id=\"${swapData.html.slot_id ?? ''}\"
                                data-group-id=\"${groupId}\"
                                data-teacher-id=\"${teacherId}\"
                                data-group-name=\"${swapData.html.group}\"
                                data-subject-name=\"${swapData.html.subject ?? ''}\">${swapData.html.group}</span>`;
                        cell.innerHTML = badgeHtml;
                        const targetBadge = cell.querySelector('.tt-trigger');
                        initBadgeDrag(targetBadge);
                        initTooltip(targetBadge);
                        flashMessage('Pamokos sėkmingai sukeistos', 'success');
                        return;
                    }
                    
                    if (!resp.ok || !data.success) {
                        showErrorModal('Klaida', data.error || 'Nepavyko perkelti pamokos');
                        return;
                    }
                    
                    // Simple move (no swap)
                    if (originalCell) originalCell.innerHTML = '<span class="text-muted">—</span>';
                    const dayNames = {'Mon':'Pirmadienis', 'Tue':'Antradienis', 'Wed':'Trečiadienis', 'Thu':'Ketvirtadienis', 'Fri':'Penktadienis'};
                    const tooltipB64 = createTooltipData(
                        data.html.group,
                        data.html.subject ?? '—',
                        data.html.room ?? '—',
                        '<?php echo e($teacher->full_name); ?>',
                        dayNames[day] ?? day,
                        slot
                    );
                    const badgeHtml = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" draggable=\"true\"
                            data-tooltip-b64=\"${tooltipB64}\"
                            data-kind=\"scheduled\"
                            data-slot-id=\"${data.html.slot_id ?? ''}\"
                            data-group-id=\"${groupId}\"
                            data-teacher-id=\"${teacherId}\"
                            data-group-name=\"${data.html.group}\"
                            data-subject-name=\"${data.html.subject ?? ''}\">${data.html.group}</span>`;
                    cell.innerHTML = badgeHtml;
                    const badge = cell.querySelector('.tt-trigger');
                    initBadgeDrag(badge);
                    initTooltip(badge);
                    flashMessage('Pamoka perkelta', 'success');
                } catch (err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            } else if (draggedKind === 'unscheduled') {
                const groupId = dragged.dataset.groupId;
                let scheduled = false;
                try {
                    const conflicts = await checkConflicts(groupId, teacherId, day, slot);
                    const confirmed = await showConfirmDialog(groupId, groupName, subjectName, day, slot, conflicts);
                    if (!confirmed) return;
                    if (conflicts.hasConflicts) return;
                    const resp = await fetch(`<?php echo e(route('schools.timetables.manual-slot', [$school, $timetable])); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, day: day, slot: slot })
                    });
                    const data = await resp.json();
                    console.log('Manual slot response:', resp.status, data);
                    if (!resp.ok || !data.success) {
                        // Check if it's a room conflict
                        if (data.conflict_type === 'room' && data.available_rooms) {
                            console.log('Room conflict detected, opening modal...', data);
                            await showRoomConflictModal(data, { day, slot, teacherId, groupId, groupName, subjectName });
                            return;
                        }
                        console.log('Error response (not room conflict):', data);
                        showErrorModal('Klaida', data.error || 'Nepavyko įtraukti pamokos');
                        return;
                    }
                    const dayNames = {'Mon':'Pirmadienis', 'Tue':'Antradienis', 'Wed':'Trečiadienis', 'Thu':'Ketvirtadienis', 'Fri':'Penktadienis'};
                    const tooltipB64 = createTooltipData(
                        data.html.group,
                        data.html.subject ?? '—',
                        data.html.room ?? '—',
                        '<?php echo e($teacher->full_name); ?>',
                        dayNames[day] ?? day,
                        slot
                    );
                    const badgeHtml = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" draggable=\"true\"
                                            data-tooltip-b64=\"${tooltipB64}\"
                                            data-kind=\"scheduled\"
                                            data-slot-id=\"${data.html.slot_id}\"
                                            data-group-id=\"${groupId}\"
                                            data-teacher-id=\"${teacherId}\"
                                            data-group-name=\"${data.html.group}\"
                                            data-subject-name=\"${data.html.subject ?? ''}\">${data.html.group}</span>`;
                    cell.innerHTML = badgeHtml;
                    scheduled = true;
                    const badge = cell.querySelector('.tt-trigger');
                    try { initBadgeDrag(badge); initTooltip(badge); } catch(e) { /* ignore tooltip init errors */ }
                    
                    // Update unscheduled list using backend data (this handles removal if remaining = 0)
                    if (data.group_id && data.remaining_lessons !== undefined && data.group_data) {
                        updateUnscheduledList(data.group_id, data.remaining_lessons, data.group_data);
                    }
                    flashMessage('Pamoka sėkmingai įtraukta', 'success');
                } catch (err) {
                    // Suppress error if scheduling succeeded
                    if (!scheduled) {
                        showErrorModal('Klaida', 'Nepavyko įtraukti pamokos');
                    } else {
                        console.warn('Non-critical error after scheduling:', err);
                    }
                }
            }
        });
    });

    function showConfirmDialog(groupId, groupName, subjectName, day, slot, conflictData) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            const manageUrl = `<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>?openGroupEdit=${groupId || ''}`;
            
            // Process conflicts to show as interactive buttons
            let conflictsHtml = '';
            if (conflictData.hasConflicts && conflictData.conflicts) {
                conflictsHtml = '<div class="d-flex flex-wrap gap-1">';
                conflictData.conflicts.forEach(c => {
                    if (typeof c === 'object' && c.type === 'room' && c.details) {
                        conflictsHtml += `<button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="showConflictDetails('${c.details.group || ''}', '${c.details.subject || ''}', '${c.details.teacher || ''}', null, 'room')">
                            <i class="bi bi-door-closed"></i> ${c.message}
                        </button>`;
                    } else if (typeof c === 'object' && c.type === 'students') {
                        const studentsJson = JSON.stringify(c.students).replace(/'/g, "\\'");
                        conflictsHtml += `<button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick='showConflictDetails("", "", "", ${studentsJson}, "students")'>
                            <i class="bi bi-people"></i> ${c.message}
                        </button>`;
                    } else if (typeof c === 'string' && c.startsWith('Užimti mokiniai:')) {
                        // Legacy format - parse student conflicts from string
                        const studentsPart = c.substring('Užimti mokiniai:'.length).trim();
                        const students = studentsPart.split(',').map(s => s.trim()).filter(s => s.length > 0);
                        const studentsArray = students.map(s => {
                            const match = s.match(/^(.+?)\\s*\\((.+?)\\)$/);
                            return match ? { name: match[1], group: match[2], subject: '' } : { name: s, group: '', subject: '' };
                        });
                        const studentsJson = JSON.stringify(studentsArray).replace(/'/g, "\\'");
                        conflictsHtml += `<button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick='showConflictDetails("", "", "", ${studentsJson}, "students")'>
                            <i class="bi bi-people"></i> ${c}
                        </button>`;
                    } else {
                        const msg = typeof c === 'string' ? c : c.message;
                        conflictsHtml += `<div class="alert alert-danger mb-1 py-1 px-2 small">${msg}</div>`;
                    }
                });
                conflictsHtml += '</div>';
            }
            
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header ${conflictData.hasConflicts ? 'bg-danger text-white' : 'bg-primary text-white'}">
                            <h5 class="modal-title">
                                <i class="bi ${conflictData.hasConflicts ? 'bi-exclamation-triangle' : 'bi-check-circle'}"></i>
                                ${conflictData.hasConflicts ? 'Aptikti konfliktai' : 'Patvirtinti pamokos pridėjimą'}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong>Grupė:</strong> ${groupName} (${subjectName})</p>
                            <p class="mb-3"><strong>Laikas:</strong> ${day}, ${slot} pamoka</p>
                            ${conflictData.hasConflicts ? `
                                <div class="mb-3">
                                    ${conflictsHtml}
                                </div>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-warning" onclick="openEditGroupModal(${groupId || ''}, this)">
                                        <i class="bi bi-gear"></i> Tvarkyti grupę
                                    </button>
                                </div>
                            ` : `
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle-fill"></i> Konfliktų nerasta. Ar norite pridėti šią pamoką?
                                </div>
                            `}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${conflictData.hasConflicts ? 'Uždaryti' : 'Atšaukti'}</button>
                            ${!conflictData.hasConflicts ? `
                                <button type="button" class="btn btn-primary" id="confirmAdd">
                                    <i class="bi bi-plus-circle"></i> Pridėti
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            if (!conflictData.hasConflicts) {
                modal.querySelector('#confirmAdd').addEventListener('click', () => { bsModal.hide(); resolve(true); });
            }
            modal.addEventListener('hidden.bs.modal', () => { modal.remove(); resolve(false); });
        });
    }

    function showSwapDialog(movingGroup, movingSubject, targetGroup, targetSubject, day, slot) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                                <i class="bi bi-arrow-left-right"></i> Sukeisti pamokų vietomis?
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Pasirinkta pozicija <strong>${day}, ${slot} pamoka</strong> jau užimta.</p>
                            <div class="alert alert-info mb-3">
                                <strong>Keliama pamoka:</strong><br>
                                ${movingGroup} ${movingSubject ? '(' + movingSubject + ')' : ''}
                            </div>
                            <div class="alert alert-warning mb-0">
                                <strong>Esanti pamoka:</strong><br>
                                ${targetGroup} ${targetSubject ? '(' + targetSubject + ')' : ''}
                            </div>
                            <p class="mt-3 mb-0"><i class="bi bi-info-circle"></i> Ar norite sukeisti šias dvi pamokas vietomis?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="button" class="btn btn-warning" id="confirmSwap">
                                <i class="bi bi-arrow-left-right"></i> Sukeisti
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.querySelector('#confirmSwap').addEventListener('click', () => {
                bsModal.hide();
                resolve(true);
            });
            
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
                resolve(false);
            });
        });
    }

});

function showErrorModal(title, message) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let bodyHtml;
    if (Array.isArray(message)) {
        // Parse array of conflicts (can be strings or objects)
        const parsedConflicts = message.map(conflict => {
            // Check if conflict is an object with type property
            if (typeof conflict === 'object' && conflict !== null && conflict.type) {
                if (conflict.type === 'room') {
                    const details = conflict.details || {};
                    return `<button class="btn btn-sm btn-outline-warning mb-1" onclick="showConflictDetails('${details.group || ''}', '${details.subject || ''}', '${details.teacher || ''}', null, 'room')"><i class="bi bi-door-closed"></i> ${conflict.message || ''}</button>`;
                } else if (conflict.type === 'students') {
                    const students = conflict.students || [];
                    const escapedStudents = JSON.stringify(students).replace(/'/g, "\\'");
                    return `<button class="btn btn-sm btn-outline-danger mb-1" onclick='showConflictDetails("", "", "", ${JSON.stringify(students)}, "students")'><i class="bi bi-people"></i> ${conflict.message || ''}</button>`;
                }
            }
            // Legacy string format
            return `<li>${conflict}</li>`;
        });
        
        bodyHtml = `<div class="d-flex flex-column gap-1">${parsedConflicts.join('')}</div>`;
    } else if (typeof message === 'object' && message !== null && message.type) {
        // Single conflict object
        if (message.type === 'room') {
            const details = message.details || {};
            bodyHtml = `<button class="btn btn-sm btn-outline-warning" onclick="showConflictDetails('${details.group || ''}', '${details.subject || ''}', '${details.teacher || ''}', null, 'room')"><i class="bi bi-door-closed"></i> ${message.message || ''}</button>`;
        } else if (message.type === 'students') {
            const students = message.students || [];
            bodyHtml = `<button class="btn btn-sm btn-outline-danger" onclick='showConflictDetails("", "", "", ${JSON.stringify(students)}, "students")'><i class="bi bi-people"></i> ${message.message || ''}</button>`;
        } else {
            bodyHtml = `<p class="mb-0">${message.message || message}</p>`;
        }
    } else if (typeof message === 'string' && message.startsWith('Užimti mokiniai:')) {
        // Legacy student conflicts string format
        const studentsPart = message.substring('Užimti mokiniai:'.length).trim();
        const students = studentsPart.split(',').map(s => s.trim()).filter(s => s.length > 0);
        students.sort((a, b) => a.localeCompare(b, 'lt'));
        bodyHtml = `<p class="mb-2"><strong>Užimti mokiniai:</strong></p><ul class="mb-0">${students.map(s => `<li>${s}</li>`).join('')}</ul>`;
    } else {
        bodyHtml = `<p class="mb-0">${message}</p>`;
    }
    
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> ${title}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">${bodyHtml}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // If room conflict modal rendered, wire availability check on select change
    const roomSelect = modal.querySelector('#newRoomSelect');
    if (roomSelect) {
        // Create/ensure hint container
        let hint = modal.querySelector('#roomSelectionHint');
        if (!hint) {
            hint = document.createElement('div');
            hint.id = 'roomSelectionHint';
            hint.className = 'alert alert-info mt-3 mb-0';
            hint.innerHTML = '<i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą';
            const body = modal.querySelector('.modal-body');
            if (body) body.appendChild(hint);
        }
        
        roomSelect.addEventListener('change', async () => {
            const selectedRoomId = roomSelect.value;
            if (!selectedRoomId) {
                hint.className = 'alert alert-info mt-3 mb-0';
                hint.innerHTML = '<i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą';
                return;
            }
            hint.className = 'alert alert-info mt-3 mb-0';
            hint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Tikrinama kabineto prieinamumas...';
            try {
                const resp = await fetch(`<?php echo e(route('schools.timetables.check-conflict', [$school, $timetable])); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        group_id: groupId, 
                        teacher_id: teacherId, 
                        day: day, 
                        slot: slot,
                        check_room_id: parseInt(selectedRoomId, 10)
                    })
                });
                const data = await resp.json();
                let hasRoomConflict = false;
                if (data && Array.isArray(data.conflicts)) {
                    hasRoomConflict = data.conflicts.some(c => {
                        if (typeof c === 'object' && c !== null) return c.type === 'room';
                        if (typeof c === 'string') return c.includes('Kabinetas') || c.includes('užimtas');
                        return false;
                    });
                }
                if (hasRoomConflict) {
                    hint.className = 'alert alert-danger mt-3 mb-0';
                    hint.innerHTML = '<i class="bi bi-x-circle"></i> Pasirinktas kabinetas užimtas. Pasirinkite kitą.';
                } else {
                    hint.className = 'alert alert-success mt-3 mb-0';
                    hint.innerHTML = '<i class="bi bi-check-circle"></i> Pasirinktas kabinetas laisvas. Galite pridėti pamoką.';
                }
            } catch (e) {
                hint.className = 'alert alert-secondary mt-3 mb-0';
                hint.innerHTML = '<i class="bi bi-question-circle"></i> Nepavyko patikrinti. Bandykite dar kartą.';
            }
        });
    }
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

// Room conflict resolution modal
async function showRoomConflictModal(conflictData, context) {
    const { day, slot, teacherId, groupId, groupName, subjectName } = context;
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    const roomOptionsHtml = conflictData.available_rooms.map(room => 
        `<option value="${room.id}">${room.label}</option>`
    ).join('');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-door-closed"></i> Kabineto konfliktas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <strong><i class="bi bi-exclamation-triangle"></i> ${conflictData.error}</strong>
                    </div>
                    
                    <p class="mb-2"><strong>Dabartinis kabinetas:</strong> ${conflictData.current_room.number} ${conflictData.current_room.name}</p>
                    <p class="mb-3"><strong>Grupė:</strong> ${conflictData.group.name} (${conflictData.group.subject_name})</p>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label for="newRoomSelect" class="form-label"><strong>Pasirinkite alternatyvų kabinetą:</strong></label>
                        <select class="form-select" id="newRoomSelect">
                            <option value="">-- Pasirinkite kabinetą --</option>
                            ${roomOptionsHtml}
                        </select>
                    </div>
                    
                    <div class="alert alert-info mb-0" id="roomSelectionHint">
                        <i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-primary" id="saveAlternativeRoom" disabled>
                        <i class="bi bi-check-circle"></i> Pridėti
                    </button>
                </div>
            </div>
        </div>`;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    const saveBtn = modal.querySelector('#saveAlternativeRoom');
    const roomSelect = modal.querySelector('#newRoomSelect');
    const hint = modal.querySelector('#roomSelectionHint');
    let selectedRoomId = null;
    let roomAvailability = {};
    
    // Check availability for a specific room
    async function checkRoomAvailability(roomId) {
        if (!roomId) return;
        
        console.log('Checking room availability for room:', roomId);
        
        // Show checking state
        hint.className = 'alert alert-info mb-0';
        hint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Tikrinama kabineto prieinamumas...';
        saveBtn.disabled = true;
        
        try {
            const resp = await fetch(`<?php echo e(route('schools.timetables.check-conflict', [$school, $timetable])); ?>`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    group_id: groupId, 
                    teacher_id: teacherId, 
                    day: day, 
                    slot: slot,
                    check_room_id: parseInt(roomId, 10)
                })
            });
            const data = await resp.json();
            console.log('Room availability check response:', data);
            
            // Check if there's a room conflict
            let hasRoomConflict = false;
            if (data.conflicts && Array.isArray(data.conflicts)) {
                hasRoomConflict = data.conflicts.some(c => {
                    if (typeof c === 'object' && c !== null) {
                        return c.type === 'room';
                    }
                    if (typeof c === 'string') {
                        return c.includes('Kabinetas') || c.includes('užimtas');
                    }
                    return false;
                });
            }
            
            const isAvailable = !hasRoomConflict;
            roomAvailability[roomId] = isAvailable;
            console.log('Room available:', isAvailable);
            
            if (isAvailable) {
                hint.className = 'alert alert-success mb-0';
                hint.innerHTML = '<i class="bi bi-check-circle"></i> Pasirinktas kabinetas laisvas. Galite pridėti.';
                saveBtn.disabled = false;
            } else {
                hint.className = 'alert alert-danger mb-0';
                hint.innerHTML = '<i class="bi bi-x-circle"></i> Pasirinktas kabinetas užimtas. Pasirinkite kitą.';
                saveBtn.disabled = true;
            }
        } catch (err) {
            console.error('Error checking room availability:', err);
            hint.className = 'alert alert-secondary mb-0';
            hint.innerHTML = '<i class="bi bi-question-circle"></i> Nepavyko patikrinti. Bandykite dar kartą.';
            roomAvailability[roomId] = false;
            saveBtn.disabled = true;
        }
    }
    
    // Handle room selection change
    roomSelect.addEventListener('change', async (e) => {
        selectedRoomId = e.target.value ? parseInt(e.target.value) : null;
        console.log('Room selected:', selectedRoomId);
        
        if (!selectedRoomId) {
            hint.className = 'alert alert-info mb-0';
            hint.innerHTML = '<i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą';
            saveBtn.disabled = true;
            return;
        }
        
        await checkRoomAvailability(selectedRoomId);
    });
    
    // Handle save button
    saveBtn.addEventListener('click', async () => {
        if (!selectedRoomId || roomAvailability[selectedRoomId] !== true) {
            return;
        }
        
        // Close current modal
        bsModal.hide();
        
        // Open new modal for group copy creation
        showGroupCopyModal(groupId, groupName, subjectName, teacherId, day, slot, selectedRoomId);
    });
    
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function showGroupCopyModal(groupId, groupName, subjectName, teacherId, day, slot, newRoomId) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    // Fetch group info to get remaining lessons
    fetch(`<?php echo e(url('/admin/schools/' . $school->id . '/timetables/' . $timetable->id)); ?>/group-info/${groupId}`)
        .then(r => r.json())
        .then(groupInfo => {
            const remainingLessons = groupInfo.remaining_lessons || 1;
            const totalLessons = groupInfo.lessons_per_week || remainingLessons;
            
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-copy"></i> Sukurti grupės kopiją</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle"></i> Bus sukurta nauja grupė su pasirinktu kabinetu. 
                                Originalios grupės pamokų skaičius bus sumažintas.
                            </div>
                            
                            <p><strong>Originali grupė:</strong> ${groupName}</p>
                            <p><strong>Dalykas:</strong> ${subjectName}</p>
                            <p><strong>Originalios grupės pamokos per savaitę:</strong> ${totalLessons}</p>
                            <p><strong>Nesuplanuotos pamokos:</strong> ${remainingLessons}</p>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Naujos grupės pavadinimas:</strong></label>
                                <input type="text" class="form-control" id="newGroupName" value="${groupName}">
                                <small class="text-muted">Galite palikti tą patį pavadinimą arba pakeisti</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><strong>Naujos grupės pamokų kiekis per savaitę:</strong></label>
                                <input type="number" class="form-control" id="newGroupLessons" value="1" min="1" max="${remainingLessons}">
                                <small class="text-muted">Maks: ${remainingLessons} (nesuplanuotų pamokų)</small>
                            </div>
                            
                            <div class="alert alert-warning mb-0">
                                <strong>Veiksmai:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Sukuriama nauja grupė su pamokų kiekiu: <span id="previewNewLessons">1</span></li>
                                    <li>Originalios grupės pamokos sumažinamos iki: <span id="previewOriginalLessons">${totalLessons - 1}</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="button" class="btn btn-primary" id="confirmCreateCopy">
                                <i class="bi bi-check-circle"></i> Sukurti kopiją ir pridėti pamoką
                            </button>
                        </div>
                    </div>
                </div>`;
            
            document.body.appendChild(modal);
            const copyModal = new bootstrap.Modal(modal);
            copyModal.show();
            
            const newGroupLessonsInput = modal.querySelector('#newGroupLessons');
            const previewNewLessons = modal.querySelector('#previewNewLessons');
            const previewOriginalLessons = modal.querySelector('#previewOriginalLessons');
            const confirmBtn = modal.querySelector('#confirmCreateCopy');
            
            // Update preview when lessons change
            newGroupLessonsInput.addEventListener('input', () => {
                const newLessons = parseInt(newGroupLessonsInput.value) || 1;
                previewNewLessons.textContent = newLessons;
                previewOriginalLessons.textContent = totalLessons - newLessons;
            });
            
            confirmBtn.addEventListener('click', async () => {
                const newGroupNameVal = modal.querySelector('#newGroupName').value.trim();
                const newLessonsCount = parseInt(newGroupLessonsInput.value) || 1;
                
                if (!newGroupNameVal) {
                    alert('Įveskite grupės pavadinimą');
                    return;
                }
                
                if (newLessonsCount < 1 || newLessonsCount > remainingLessons) {
                    alert(`Pamokų kiekis turi būti nuo 1 iki ${remainingLessons}`);
                    return;
                }
                
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kuriama...';
                
                try {
                    const resp = await fetch(`<?php echo e(route('schools.timetables.manual-slot-alt-room', [$school, $timetable])); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            group_id: groupId,
                            teacher_id: teacherId,
                            day: day,
                            slot: slot,
                            alternative_room_id: newRoomId,
                            new_group_name: newGroupNameVal,
                            new_group_lessons: newLessonsCount
                        })
                    });
                    
                    const data = await resp.json();
                    
                    if (!resp.ok || !data.success) {
                        alert(data.error || 'Nepavyko sukurti kopijos');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="bi bi-check-circle"></i> Sukurti kopiją ir pridėti pamoką';
                        return;
                    }
                    
                    copyModal.hide();
                    
                    // Update unscheduled list if data provided
                    if (typeof updateUnscheduledList === 'function' && data.group_id !== undefined && data.remaining_lessons !== undefined && data.group_data) {
                        updateUnscheduledList(data.group_id, data.remaining_lessons, data.group_data);
                    }
                    
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    location.reload(); // Reload to show updated timetable
                } catch (err) {
                    alert('Klaida siunčiant užklausą');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle"></i> Sukurti kopiją ir pridėti pamoką';
                }
            });
            
            modal.addEventListener('hidden.bs.modal', () => modal.remove());
        })
        .catch(err => {
            console.error('Error fetching group info:', err);
            alert('Nepavyko gauti grupės informacijos');
        });
}

// Helper function to create tooltip data
function createTooltipData(groupName, subjectName, roomDisplay, teacherName, day, slot) {
    const tooltipHtml = '<div class="tt-inner">'
        +'<div class="tt-row tt-row-head"><i class="bi bi-clock-history tt-ico"></i><span class="tt-val">'+day+' • '+slot+' pamoka</span></div>'
        +'<div class="tt-divider"></div>'
        +'<div class="tt-row"><i class="bi bi-collection-fill tt-ico"></i><span class="tt-val">'+groupName+'</span></div>'
        +'<div class="tt-row"><i class="bi bi-book-half tt-ico"></i><span class="tt-val">'+subjectName+'</span></div>'
        +'<div class="tt-row"><i class="bi bi-door-closed tt-ico"></i><span class="tt-val">'+roomDisplay+'</span></div>'
        +'<div class="tt-row"><i class="bi bi-person-badge tt-ico"></i><span class="tt-val">'+teacherName+'</span></div>'
    +'</div>';
    return btoa(encodeURIComponent(tooltipHtml).replace(/%([0-9A-F]{2})/g, (match, p1) => String.fromCharCode('0x' + p1)));
}

// Helper function to initialize tooltip on element
function initTooltip(el) {
    // Tooltips are initialized globally - this function is now for reference only
    // No need to reinitialize here
}

function showContextMenu(event, slotId, groupId, groupName, subjectName, badgeElement) {
    event.preventDefault();
    event.stopPropagation();
    
    // Remove any existing context menu
    const existingMenu = document.getElementById('lessonContextMenu');
    if (existingMenu) existingMenu.remove();
    
    // Create context menu
    const menu = document.createElement('div');
    menu.id = 'lessonContextMenu';
    menu.className = 'context-menu';
    // Inline fallback styles so menu renders even if CSS classes don’t apply
    Object.assign(menu.style, {
        position: 'fixed',
        background: '#ffffff',
        border: '2px solid #dee2e6',
        borderRadius: '8px',
        boxShadow: '0 8px 24px rgba(0,0,0,0.25)',
        minWidth: '280px',
        zIndex: '2000',
        fontSize: '14px',
        padding: '8px 0'
    });
    menu.innerHTML = `
        <div class="context-menu-header" style="padding:12px 16px; font-weight:600; color:#212529; border-bottom:2px solid #e9ecef; background:#f8f9fa; border-radius:6px 6px 0 0; font-size:14px;">
            <i class="bi bi-gear-fill me-2"></i>${groupName}
            ${subjectName ? '<small class="ms-2 text-muted">' + subjectName + '</small>' : ''}
        </div>
        <div class="context-menu-item" data-action="edit" style="padding:12px 16px; cursor:pointer; display:flex; align-items:center; transition:all .15s ease; color:#212529; white-space:nowrap; border:none; background:transparent; text-align:left;">
            <i class="bi bi-pencil-square me-2"></i>Redaguoti grupės nustatymus
        </div>
        <div class="context-menu-divider" style="height:1px; background:#d0d0d0; margin:6px 0; border:none;"></div>
        <div class="context-menu-item text-danger" data-action="unschedule" style="padding:12px 16px; cursor:pointer; display:flex; align-items:center; transition:all .15s ease; color:#dc3545; white-space:nowrap; border:none; background:transparent; text-align:left;">
            <i class="bi bi-arrow-left-circle me-2"></i>Perkelti į nesuplanuotų sąrašą
        </div>
    `;
    
    // Position menu at mouse cursor using clientX/clientY with fixed position
    menu.style.position = 'fixed';
    menu.style.left = '0px';
    menu.style.top = '0px';
    
    // Add to DOM to calculate dimensions
    document.body.appendChild(menu);
    
    // Get actual dimensions for adjustment
    const menuRect = menu.getBoundingClientRect();
    const menuWidth = menuRect.width;
    const menuHeight = menuRect.height;
    
    let adjustedLeft = event.clientX;
    let adjustedTop = event.clientY;
    
    // Keep menu within viewport with 10px margin
    if (adjustedLeft + menuWidth + 10 > window.innerWidth) {
        adjustedLeft = Math.max(10, window.innerWidth - menuWidth - 10);
    }
    
    if (adjustedTop + menuHeight + 10 > window.innerHeight) {
        adjustedTop = Math.max(10, window.innerHeight - menuHeight - 10);
    }
    
    // Apply position
    menu.style.left = adjustedLeft + 'px';
    menu.style.top = adjustedTop + 'px';
    
    // Handle menu item clicks
    menu.querySelectorAll('.context-menu-item').forEach(item => {
        item.addEventListener('click', async () => {
            const action = item.dataset.action;
            menu.remove();
            badgeElement.classList.remove('lesson-selected');
            
            if (action === 'edit') {
                await openGroupEditModal(groupId);
            } else if (action === 'unschedule') {
                await unscheduleLesson(slotId, badgeElement);
            }
        });
    });
    // Inline hover effects
    menu.querySelectorAll('.context-menu-item').forEach(item => {
        item.addEventListener('mouseenter', () => {
            if (item.classList.contains('text-danger')) {
                item.style.background = '#ffe0e0';
                item.style.color = '#dc3545';
            } else {
                item.style.background = '#f0f0f0';
                item.style.color = '#212529';
            }
            item.style.paddingLeft = '20px';
        });
        item.addEventListener('mouseleave', () => {
            item.style.background = 'transparent';
            item.style.color = item.classList.contains('text-danger') ? '#dc3545' : '#212529';
            item.style.paddingLeft = '16px';
        });
    });
    
    // Close menu on click outside
    const closeMenu = (e) => {
        if (!menu.contains(e.target)) {
            menu.remove();
            badgeElement.classList.remove('lesson-selected');
            document.removeEventListener('click', closeMenu);
        }
    };
    setTimeout(() => document.addEventListener('click', closeMenu), 10);
    
    // Close menu on escape
    const closeOnEscape = (e) => {
        if (e.key === 'Escape') {
            menu.remove();
            badgeElement.classList.remove('lesson-selected');
            document.removeEventListener('keydown', closeOnEscape);
        }
    };
    document.addEventListener('keydown', closeOnEscape);
}

async function openGroupEditModal(groupId) {
    try {
        const resp = await fetch(`<?php echo e(route('schools.timetables.groups.edit-data', [$school, $timetable, ':groupId'])); ?>`.replace(':groupId', groupId));
        const data = await resp.json();
        
        if (!resp.ok || !data.group) {
            flashMessage('Klaida kraunant grupės duomenis', 'danger');
            return;
        }
        
        const group = data.group;
        
        // Construct update URL
        const updateUrl = `<?php echo e(route('schools.timetables.groups.update', [$school, $timetable, ':groupId'])); ?>`.replace(':groupId', groupId);
        
        // Create edit modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Redaguoti grupę</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="editGroupForm">
                        <div class="modal-body">
                            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                            <input type="hidden" name="_method" value="PUT">
                            <div class="mb-3">
                                <label class="form-label">Pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="${group.name}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dalykas</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Pasirinkite --</option>
                                    ${data.subjects.map(s => `<option value="${s.id}" ${s.id == group.subject_id ? 'selected' : ''}>${s.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mokytojas</label>
                                <select name="teacher_login_key_id" class="form-select">
                                    <option value="">-- Pasirinkite --</option>
                                    ${data.teachers.map(t => `<option value="${t.id}" ${t.id == group.teacher_login_key_id ? 'selected' : ''}>${t.full_name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kabinetas</label>
                                <select name="room_id" class="form-select">
                                    <option value="">-- Pasirinkite --</option>
                                    ${data.rooms.map(r => `<option value="${r.id}" ${r.id == group.room_id ? 'selected' : ''}>${r.number} ${r.name || ''}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Savaitės tipas</label>
                                <select name="week_type" class="form-select" required>
                                    <option value="all" ${group.week_type === 'all' ? 'selected' : ''}>Kiekvieną savaitę</option>
                                    <option value="even" ${group.week_type === 'even' ? 'selected' : ''}>Tik lygines savaites</option>
                                    <option value="odd" ${group.week_type === 'odd' ? 'selected' : ''}>Tik nelygines savaites</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pamokų per savaitę</label>
                                <input type="number" name="lessons_per_week" class="form-control" value="${group.lessons_per_week}" min="1" max="20" required>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_priority" id="is_priority_${groupId}" ${group.is_priority ? 'checked' : ''}>
                                <label class="form-check-label" for="is_priority_${groupId}">Prioritetinė grupė</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="submit" class="btn btn-warning">Išsaugoti</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
        
        // Handle form submission
        modal.querySelector('#editGroupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const submitResp = await fetch(updateUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await submitResp.json();
                
                if (submitResp.ok && result.success) {
                    bsModal.hide();
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    flashMessage('Grupė sėkmingai atnaujinta', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    flashMessage('Klaida: ' + (result.message || 'Nepavyko išsaugoti'), 'danger');
                }
            } catch(err) {
                console.error(err);
                flashMessage('Klaida siunčiant duomenis', 'danger');
            }
        });
    } catch(err) {
        console.error(err);
        flashMessage('Klaida užklausiant duomenis', 'danger');
    }
}

async function unscheduleLesson(slotId, badgeElement) {
    try {
        const resp = await fetch(`<?php echo e(route('schools.timetables.unschedule-slot', [$school, $timetable])); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ slot_id: slotId })
        });
        
        const data = await resp.json();
        
        if (!resp.ok || !data.success) {
            flashMessage('Klaida: ' + (data.error || 'Nepavyko perkelti pamokos'), 'danger');
            return;
        }
        
        // Remove badge from timetable
        const cell = badgeElement.closest('td');
        if (cell) {
            cell.innerHTML = '<span class="text-muted">—</span>';
        }
        
        // Show success message
        flashMessage('Pamoka sėkmingai perkelta į nesuplanuotas pamokas', 'success');
        
        // Update unscheduled list
        if (data.group_id && data.remaining_lessons !== undefined && data.group_data) {
            updateUnscheduledList(data.group_id, data.remaining_lessons, data.group_data);
        }
    } catch(err) {
        console.error(err);
        flashMessage('Klaida siunčiant užklausą', 'danger');
    }
}

function updateUnscheduledList(groupId, remainingLessons, groupData) {
    const unscheduledPanel = document.querySelector('#unscheduledPanel .card-body');
    if (!unscheduledPanel) return;
    
    // Find existing unscheduled item for this group
    let existingItem = null;
    unscheduledPanel.querySelectorAll('.unscheduled-item').forEach(item => {
        if (item.dataset.groupId == groupId) {
            existingItem = item;
        }
    });
    
    if (remainingLessons > 0) {
        if (existingItem) {
            // Update existing item
            existingItem.dataset.remaining = remainingLessons;
            const countEl = existingItem.querySelector('.remaining-badge');
            if (countEl) { countEl.textContent = remainingLessons; }
        } else {
            // Create new item with new design (no badges)
            const newItem = document.createElement('div');
            newItem.className = 'unscheduled-item mb-1 d-flex align-items-center';
            newItem.draggable = true;
            newItem.dataset.kind = 'unscheduled';
            newItem.dataset.groupId = groupId;
            newItem.dataset.groupName = groupData.group_name;
            newItem.dataset.subjectName = groupData.subject_name;
            newItem.dataset.teacherId = groupData.teacher_login_key_id || '';
            newItem.dataset.teacherName = groupData.teacher_name || '';
            newItem.dataset.remaining = remainingLessons;
            
            newItem.innerHTML = `
                <div class="flex-grow-1">
                    <div class="unscheduled-title">${groupData.group_name}
                        <span class="badge bg-primary ms-2 remaining-badge">${remainingLessons}</span>
                    </div>
                    <div class="unscheduled-meta">
                        <span class="unscheduled-subject">${groupData.subject_name}</span>
                        <span class="unscheduled-room"><i class="bi bi-door-closed"></i> ${groupData.room_number || '—'}</span>
                    </div>
                </div>
                <div class="ms-2">
                    <button type="button" class="btn btn-outline-info btn-sm" 
                            onclick="findAvailableSlots(${groupId}, '${groupData.group_name.replace(/'/g, "\\'")}', '${groupData.subject_name.replace(/'/g, "\\'")}', ${groupData.teacher_login_key_id || 'null'})"
                            title="Rasti laisvus langelius">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            `;
            
            // Remove "Nėra neužpildytų" message if exists
            const emptyMsg = unscheduledPanel.querySelector('.text-muted.small');
            if (emptyMsg) emptyMsg.remove();
            
            // Add to list
            unscheduledPanel.insertBefore(newItem, unscheduledPanel.firstChild);
            
            // Make it draggable
            newItem.addEventListener('dragstart', e => {
                dragged = newItem;
                draggedKind = 'unscheduled';
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', groupId);
                newItem.classList.add('dragging');
            });
            newItem.addEventListener('dragend', () => {
                dragged?.classList.remove('dragging');
                dragged = null;
                draggedKind = null;
            });
        }
    } else if (existingItem) {
        // Remove item if no remaining lessons
        existingItem.remove();
        
        // If list is empty, show "Nėra" message
        if (!unscheduledPanel.querySelector('.unscheduled-item')) {
            const emptyMsg = document.createElement('span');
            emptyMsg.className = 'text-muted small';
            emptyMsg.textContent = 'Nėra neužpildytų pamokų šiam mokytojui';
            unscheduledPanel.appendChild(emptyMsg);
        }
    }
}

async function checkConflicts(groupId, teacherId, day, slot, tempRoomId = null) {
    try {
        const body = { 
            group_id: groupId, 
            teacher_id: teacherId, 
            day: day, 
            slot: slot 
        };
        
        if (tempRoomId) {
            body.temp_room_id = tempRoomId;
        }
        
        const resp = await fetch(`<?php echo e(route('schools.timetables.check-conflict', [$school, $timetable])); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });
        const data = await resp.json();
        return data;
    } catch (err) {
        return { hasConflicts: false, conflicts: [] };
    }
}

async function findAvailableSlots(groupId, groupName, subjectName, teacherId) {
    if (!teacherId) { showErrorModal('Klaida', 'Grupė neturi priskirto mokytojo'); return; }

    // Highlight selected unscheduled group
    document.querySelectorAll('.unscheduled-item').forEach(el=> el.classList.remove('active-group'));
    const chosen = document.querySelector(`.unscheduled-item[data-group-id='${groupId}']`);
    if (chosen) chosen.classList.add('active-group');

    // Overlay
    let overlay = document.getElementById('availabilityLoading');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'availabilityLoading';
        overlay.style.position = 'fixed';
        overlay.style.top = 0; overlay.style.left = 0; overlay.style.right = 0; overlay.style.bottom = 0;
        overlay.style.background = 'rgba(255,255,255,0.85)';
        overlay.style.zIndex = 2000;
        overlay.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100"><div class="spinner-border text-primary" role="status"></div><div class="mt-3 text-muted">Tikrinamas užimtumas...</div></div>';
        document.body.appendChild(overlay);
    }

    // Fetch rooms once
    const roomsResp = await fetch(`<?php echo e(route('schools.timetables.groups.edit-data', [$school, $timetable, ':groupId'])); ?>`.replace(':groupId', groupId));
    const roomsData = await roomsResp.json();
    const availableRooms = roomsData.rooms || [];
    const currentRoomId = roomsData.group?.room_id;

    // Clear previous badges/highlights
    document.querySelectorAll('.timetable-cell').forEach(cell => {
        cell.classList.remove('bg-success-subtle','bg-warning-subtle','bg-danger-subtle','bg-info-subtle','checking-slot');
        const oldBadge = cell.querySelector('.availability-badge'); if (oldBadge) oldBadge.remove();
    });

    // Collect empty cells for this teacher
    const teacherCells = Array.from(document.querySelectorAll(`#teacherGrid td[data-teacher-id='${teacherId}']`));
    const items = [];
    teacherCells.forEach(cell => {
        const hasLesson = cell.querySelector('[draggable="true"]');
        if (hasLesson) return;
        const dayCode = cell.dataset.day; const slot = parseInt(cell.dataset.slot,10);
        if (!dayCode || !slot) return;
        items.push({ day: dayCode, slot });
        cell.classList.add('bg-info-subtle');
    });

    if (items.length === 0) { overlay.remove(); flashMessage('Nėra laisvų langelių tikrinimui', 'info'); return; }

    // Bulk request
    let resp; let data;
    try {
        resp = await fetch(`<?php echo e(route('schools.timetables.bulk-conflicts', [$school, $timetable])); ?>`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content'),'Accept':'application/json' },
            body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, items })
        });
        data = await resp.json();
    } catch (e) {
        overlay.remove(); showErrorModal('Klaida','Nepavyko atlikti masinio tikrinimo'); return;
    }

    overlay.remove();
    if (!resp.ok || !data.success) { showErrorModal('Klaida', data.error || 'Nepavyko gauti rezultatų'); return; }

    const resultsMap = {};
    data.items.forEach(r => { resultsMap[r.day+'|'+r.slot] = r; });

    teacherCells.forEach(cell => {
        const hasLesson = cell.querySelector('[draggable="true"]'); if (hasLesson) return;
        const dayCode = cell.dataset.day; const slot = parseInt(cell.dataset.slot,10);
        const key = dayCode+'|'+slot; const r = resultsMap[key];
        cell.classList.remove('bg-info-subtle');
        if (!r) return;
        let badge;
        if (!r.hasConflicts) {
            badge = document.createElement('div');
            badge.className='availability-badge badge bg-success position-absolute top-0 end-0 m-1';
            badge.style.cursor='pointer';
            badge.innerHTML='<i class="bi bi-check-circle"></i> Laisvas';
            badge.onclick=()=>showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, dayCode, slot, 'free', [], availableRooms, currentRoomId);
        } else {
            const conf = r.conflicts || [];
            const student = conf.find(c=>typeof c==='object' && c.type==='students');
            const room = conf.find(c=>typeof c==='object' && c.type==='room');
            const teacherBusy = conf.find(c=>typeof c==='string' && (c.includes('turi pamoką') || c.includes('Mokytojas')));
            const studentCount = student && Array.isArray(student.students) ? student.students.length : 0;
            badge = document.createElement('div');
            let status = r.status;
            if (teacherBusy) {
                badge.className='availability-badge badge bg-secondary position-absolute top-0 end-0 m-1';
                badge.innerHTML='<i class="bi bi-person-x"></i>';
                badge.title='Mokytojas užimtas';
            } else if (student && room) {
                badge.className='availability-badge badge bg-danger position-absolute top-0 end-0 m-1';
                badge.innerHTML='<i class="bi bi-people"></i> '+studentCount+' <i class="bi bi-door-closed"></i>';
                badge.title='Mokiniai ir kabinetas konfliktuoja';
            } else if (student) {
                badge.className='availability-badge badge bg-danger position-absolute top-0 end-0 m-1';
                badge.innerHTML='<i class="bi bi-people"></i> '+studentCount;
                badge.title='Mokinių konfliktas';
            } else if (room) {
                badge.className='availability-badge badge bg-warning position-absolute top-0 end-0 m-1';
                badge.innerHTML='<i class="bi bi-door-closed"></i>';
                badge.title='Kabinetas užimtas';
            } else {
                badge.className='availability-badge badge bg-warning position-absolute top-0 end-0 m-1';
                badge.innerHTML='<i class="bi bi-exclamation-triangle"></i>';
                badge.title='Kitas konfliktas';
            }
            badge.style.cursor='pointer';
            badge.onclick=()=>showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, dayCode, slot, status, conf, availableRooms, currentRoomId);
        }
        cell.style.position='relative';
        cell.appendChild(badge);
    });
    
    // Setup drag-drop listeners for room conflict badges
    setupBadgeDragDrop();
}

function findCellByDayAndSlot(day, slot) {
    // Find the cell in the timetable by day and slot
    // Day codes: Mon, Tue, Wed, Thu, Fri
    const dayCodeMap = {
        'Pirmadienis': 'Mon',
        'Antradienis': 'Tue',
        'Trečiadienis': 'Wed',
        'Ketvirtadienis': 'Thu',
        'Penktadienis': 'Fri'
    };
    
    const dayCode = dayCodeMap[day];
    if (!dayCode) {
        console.log('Unknown day:', day);
        return null;
    }
    
    // Find the cell using data attributes
    const cell = document.querySelector(`#teacherGrid td[data-day="${dayCode}"][data-slot="${slot}"]`);
    if (!cell) {
        console.log('Cell not found for:', { dayCode, slot });
    }
    return cell;
}

function showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, day, slot, status, conflicts, availableRooms, currentRoomId) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let contentHtml = '';
    let headerClass = 'bg-info';
    let title = 'Langelio informacija';
    
    if (status === 'free') {
        headerClass = 'bg-success';
        title = 'Laisvas langelis';
        contentHtml = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Šis langelis visiškai laisvas!
            </div>
            <p><strong>Diena:</strong> ${day}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
        `;
    } else if (status === 'student_conflict') {
        headerClass = 'bg-danger';
        title = 'Mokinių konfliktas';
        
        let conflictsHtml = '<div class="mb-3">';
        conflicts.forEach(c => {
            if (typeof c === 'object' && c.type === 'students' && c.students) {
                conflictsHtml += `
                    <button type="button" class="btn btn-sm btn-outline-danger mb-2 me-1 w-100 text-start" 
                        onclick='showConflictDetails("", "", "", ${JSON.stringify(c.students)}, "students")'>
                        <i class="bi bi-people"></i> ${c.message}
                    </button>`;
            } else if (typeof c === 'object' && c.type === 'room' && c.details) {
                conflictsHtml += `
                    <button type="button" class="btn btn-sm btn-outline-warning mb-2 me-1" 
                        onclick="showConflictDetails('${c.details.group || ''}', '${c.details.subject || ''}', '${c.details.teacher || ''}', null, 'room')">
                        <i class="bi bi-door-closed"></i> ${c.message}
                    </button>`;
            } else {
                const msg = typeof c === 'string' ? c : c.message;
                conflictsHtml += `<div class="text-danger small mb-1">${msg}</div>`;
            }
        });
        conflictsHtml += '</div>';
        
        contentHtml = `
            <div class="alert alert-danger">
                <i class="bi bi-people"></i> Užimti mokiniai
            </div>
            <p><strong>Diena:</strong> ${day}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
            
            <h6 class="mt-3">Konfliktai (spauskite detalesnei informacijai):</h6>
            ${conflictsHtml}
            
            <div class="alert alert-warning mt-3">
                <i class="bi bi-info-circle"></i> Negalima pridėti pamokos, nes mokiniai jau užimti kitoje pamokoje.
            </div>
        `;
    } else if (status === 'both_conflicts') {
        headerClass = 'bg-danger';
        title = 'Konfliktai: Mokiniai ir Kabinetas';
        
        let conflictsHtml = '<div class="mb-3">';
        conflicts.forEach(c => {
            if (typeof c === 'object' && c.type === 'students' && c.students) {
                conflictsHtml += `
                    <button type="button" class="btn btn-sm btn-outline-danger mb-2 me-1 w-100 text-start" 
                        onclick='showConflictDetails("", "", "", ${JSON.stringify(c.students)}, "students")'>
                        <i class="bi bi-people"></i> ${c.message}
                    </button>`;
            } else if (typeof c === 'object' && c.type === 'room' && c.details) {
                conflictsHtml += `
                    <button type="button" class="btn btn-sm btn-outline-warning mb-2 me-1 w-100 text-start" 
                        onclick="showConflictDetails('${c.details.group || ''}', '${c.details.subject || ''}', '${c.details.teacher || ''}', null, 'room')">
                        <i class="bi bi-door-closed"></i> ${c.message}
                    </button>`;
            } else {
                const msg = typeof c === 'string' ? c : c.message;
                conflictsHtml += `<div class="text-danger small mb-1">${msg}</div>`;
            }
        });
        conflictsHtml += '</div>';
        
        contentHtml = `
            <div class="alert alert-danger">
                <i class="bi bi-people"></i> <i class="bi bi-door-closed"></i> Užimti mokiniai IR kabinetas
            </div>
            <p><strong>Diena:</strong> ${day}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
            
            <h6 class="mt-3">Visi konfliktai (spauskite detalesnei informacijai):</h6>
            ${conflictsHtml}
            
            <div class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle"></i> Negalima pridėti pamokos - užimti ir mokiniai, ir kabinetas.
            </div>
        `;
    } else if (status === 'teacher_conflict') {
        headerClass = 'bg-secondary';
        title = 'Mokytojas užimtas';
        
        let conflictsHtml = '<div class="mb-3">';
        conflicts.forEach(c => {
            const msg = typeof c === 'string' ? c : c.message;
            conflictsHtml += `<div class="text-muted small mb-1"><i class="bi bi-person-x"></i> ${msg}</div>`;
        });
        conflictsHtml += '</div>';
        
        contentHtml = `
            <div class="alert alert-secondary">
                <i class="bi bi-person-x"></i> Mokytojas užimtas
            </div>
            <p><strong>Diena:</strong> ${day}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
            
            <h6 class="mt-3">Konfliktai:</h6>
            ${conflictsHtml}
            
            <div class="alert alert-warning mt-3">
                <i class="bi bi-info-circle"></i> Negalima pridėti pamokos, nes mokytojas tuo metu jau turi kitą pamoką.
            </div>
        `;
    } else if (status === 'room_conflict') {
        headerClass = 'bg-warning';
        title = 'Kabineto konfliktas';
        
        let conflictsHtml = '<div class="mb-3">';
        conflicts.forEach(c => {
            if (typeof c === 'object' && c.type === 'room' && c.details) {
                conflictsHtml += `
                    <button type="button" class="btn btn-sm btn-outline-warning mb-2 me-1 w-100 text-start" 
                        onclick="showConflictDetails('${c.details.group || ''}', '${c.details.subject || ''}', '${c.details.teacher || ''}', null, 'room')">
                        <i class="bi bi-door-closed"></i> ${c.message}
                    </button>`;
            } else {
                const msg = typeof c === 'string' ? c : c.message;
                conflictsHtml += `<div class="text-warning small mb-1">${msg}</div>`;
            }
        });
        conflictsHtml += '</div>';
        
        contentHtml = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Kabineto konfliktas
            </div>
            <p><strong>Diena:</strong> ${day}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
            
            <h6 class="mt-3">Konfliktai (spauskite detalesnei informacijai):</h6>
            ${conflictsHtml}
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> Norėdami pridėti šią pamoką su kitu kabinetu, spauskite žemiau
            </div>
        `;
    }
    
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header ${headerClass} text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle"></i> ${title}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${contentHtml}
                </div>
                <div class="modal-footer">
                    ${status === 'free' ? `
                        <button type="button" class="btn btn-success" 
                                onclick="addLessonToSlot(${groupId}, ${teacherId}, '${day}', ${slot}, null); bootstrap.Modal.getInstance(document.querySelector('.modal.show')).hide();">
                            <i class="bi bi-plus-circle"></i> Pridėti pamoką
                        </button>
                    ` : status === 'room_conflict' ? `
                        <button type="button" class="btn btn-warning"
                                onclick="showRoomConflictModal({available_rooms: ${JSON.stringify(availableRooms).replace(/"/g, '&quot;')}, current_room: {id: ${currentRoomId}}}, {day: '${day}', slot: ${slot}, teacherId: ${teacherId}, groupId: ${groupId}, groupName: '${groupName}', subjectName: '${subjectName}'}); bootstrap.Modal.getInstance(document.querySelector('.modal.show')).hide();">
                            <i class="bi bi-door-closed"></i> Kurti grupės kopiją su kitu kabinetu
                        </button>
                    ` : ''}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function showAvailableSlotsModal(groupId, groupName, subjectName, teacherId, slots, availableRooms, currentRoomId) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    // Group slots by status
    const freeSlots = slots.filter(s => s.status === 'free');
    const roomConflictSlots = slots.filter(s => s.status === 'room_conflict');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-search"></i> Laisvi langeliai: ${groupName}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <p class="mb-3"><strong>Dalykas:</strong> ${subjectName}</p>
                    
                    ${freeSlots.length > 0 ? `
                        <div class="mb-4">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> Visiškai laisvi langeliai (${freeSlots.length})</h6>
                            <div class="list-group">
                                ${freeSlots.map(s => `
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><strong>${s.day}</strong>, ${s.slot} pamoka</span>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="addLessonToSlot(${groupId}, ${teacherId}, '${s.day}', ${s.slot}, null)">
                                            <i class="bi bi-plus-circle"></i> Pridėti
                                        </button>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    ${roomConflictSlots.length > 0 ? `
                        <div class="mb-4">
                            <h6 class="text-warning"><i class="bi bi-exclamation-triangle"></i> Galima su kitu kabinetu (${roomConflictSlots.length})</h6>
                            <p class="small text-muted">Šie langeliai užimti tik dėl kabineto konflikto. Galite pasirinkti kitą kabinetą.</p>
                            <div class="list-group">
                                ${roomConflictSlots.map((s, idx) => {
                                    let conflictsHtml = '';
                                    s.conflicts.forEach(c => {
                                        if (typeof c === 'object' && c.type === 'room' && c.details) {
                                            conflictsHtml += `<button type="button" class="btn btn-sm btn-outline-danger mb-1 me-1" 
                                                onclick="showConflictDetails('${c.details.group || ''}', '${c.details.subject || ''}', '${c.details.teacher || ''}', null, 'room')">
                                                <i class="bi bi-door-closed"></i> ${c.message}
                                            </button>`;
                                        } else if (typeof c === 'object' && c.type === 'students') {
                                            const studentsJson = JSON.stringify(c.students).replace(/'/g, "\\'");
                                            conflictsHtml += `<button type="button" class="btn btn-sm btn-outline-danger mb-1 me-1" 
                                                onclick='showConflictDetails("", "", "", ${studentsJson}, "students")'>
                                                <i class="bi bi-people"></i> ${c.message}
                                            </button>`;
                                        } else {
                                            const msg = typeof c === 'string' ? c : c.message;
                                            conflictsHtml += `<div class="text-danger small mb-1">${msg}</div>`;
                                        }
                                    });
                                    return `
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><strong>${s.day}</strong>, ${s.slot} pamoka</span>
                                        </div>
                                        <div class="mb-2">
                                            ${conflictsHtml}
                                        </div>
                                        <div class="d-flex gap-2 align-items-end">
                                            <div class="flex-grow-1">
                                                <label class="form-label small mb-1">Pasirinkite kitą kabinetą:</label>
                                                <select class="form-select form-select-sm" id="roomSelect_${idx}">
                                                    <option value="">-- Pasirinkite --</option>
                                                    ${availableRooms.map(r => `
                                                        <option value="${r.id}" ${r.id == currentRoomId ? 'selected' : ''}>
                                                            ${r.number} ${r.name || ''}
                                                        </option>
                                                    `).join('')}
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="addLessonWithNewRoom(${groupId}, ${teacherId}, '${s.day}', ${s.slot}, 'roomSelect_${idx}')">
                                                <i class="bi bi-plus-circle"></i> Pridėti su nauju kabinetu
                                            </button>
                                        </div>
                                    </div>
                                `}).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    ${freeSlots.length === 0 && roomConflictSlots.length === 0 ? `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Nerasta laisvų langelių
                        </div>
                    ` : ''}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

async function addLessonToSlot(groupId, teacherId, day, slot, roomId) {
    try {
        const resp = await fetch(`<?php echo e(route('schools.timetables.manual-slot', [$school, $timetable])); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, day, slot, temp_room_id: roomId })
        });
        
        const data = await resp.json();
        if (data.success) {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            flashMessage('Pamoka sėkmingai pridėta', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorModal('Klaida', data.error || 'Nepavyko pridėti pamokos');
        }
    } catch (err) {
        showErrorModal('Klaida', 'Įvyko klaida pridedant pamoką');
    }
}

async function addLessonWithNewRoom(groupId, teacherId, day, slot, selectId) {
    const roomSelect = document.getElementById(selectId);
    const newRoomId = roomSelect.value;
    
    if (!newRoomId) {
        showErrorModal('Klaida', 'Prašome pasirinkti kabinetą');
        return;
    }
    
    // First, check if the new room is available at this time
    const checkResp = await fetch(`<?php echo e(route('schools.timetables.check-conflict', [$school, $timetable])); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            group_id: groupId, 
            teacher_id: teacherId, 
            day, 
            slot,
            temp_room_id: newRoomId
        })
    });
    
    const checkData = await checkResp.json();
    
    // Check if still has room conflict with the new room
    const hasRoomConflict = checkData.conflicts && checkData.conflicts.some(c => 
        typeof c === 'string' && c.includes('užimtas')
    );
    
    if (hasRoomConflict) {
        showErrorModal('Kabinetas užimtas', 'Pasirinktas kabinetas taip pat užimtas šiuo laiku. Pasirinkite kitą kabinetą.');
        return;
    }
    
    // Create a copy of the group with new room
    try {
        const updateResp = await fetch(`<?php echo e(route('schools.timetables.groups.update', [$school, $timetable, ':groupId'])); ?>`.replace(':groupId', groupId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ room_id: newRoomId })
        });
        
        const updateData = await updateResp.json();
        if (updateData.success) {
            // Now add the lesson
            await addLessonToSlot(groupId, teacherId, day, slot, newRoomId);
        } else {
            showErrorModal('Klaida', 'Nepavyko atnaujinti kabineto');
        }
    } catch (err) {
        showErrorModal('Klaida', 'Įvyko klaida atnaujinant kabinetą');
    }
}

function showConflictDetails(groupName, subjectName, teacherName, students, conflictType) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let contentHtml = '';
    
    if (conflictType === 'room') {
        contentHtml = `
            <div class="mb-3">
                <h6 class="text-danger"><i class="bi bi-door-closed"></i> Kabineto konfliktas</h6>
                <p>Šis kabinetas tuo metu užimtas kitos pamokos:</p>
            </div>
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><span class="badge bg-secondary">${groupName}</span></h6>
                            <p class="mb-1"><strong>Dalykas:</strong> <span class="badge bg-success">${subjectName}</span></p>
                            <p class="mb-0"><strong>Mokytojas:</strong> <i class="bi bi-person-circle"></i> ${teacherName}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (conflictType === 'students') {
        contentHtml = `
            <div class="mb-3">
                <h6 class="text-danger"><i class="bi bi-people"></i> Mokinių konfliktas</h6>
                <p>Šie mokiniai tuo metu užimti kitose pamokose:</p>
            </div>
            <div class="list-group">
                ${students.map(s => `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-person"></i> ${s.name}</strong>
                                <div class="small text-muted">
                                    Grupė: <span class="badge bg-secondary">${s.group}</span>
                                    Dalykas: <span class="badge bg-success">${s.subject}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle"></i> Konflikto informacija
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${contentHtml}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function flashMessage(msg,type){
    let box=document.getElementById('flashBox');
    if(!box){ box=document.createElement('div'); box.id='flashBox'; box.style.position='fixed'; box.style.top='10px'; box.style.right='10px'; box.style.zIndex='9999'; document.body.appendChild(box); }
    const el=document.createElement('div'); el.className=`alert alert-${type} py-1 px-2 mb-2`; el.textContent=msg; box.appendChild(el); setTimeout(()=>{ el.remove(); if(!box.children.length) box.remove(); },3000);
}

// Setup badge drag-over listeners for room conflict badges (must be OUTSIDE DOMContentLoaded)
function setupBadgeDragDrop() {
    document.querySelectorAll('.availability-badge.bg-warning').forEach(badge => {
        // Remove existing listeners to avoid duplicates
        const newBadge = badge.cloneNode(true);
        badge.parentNode.replaceChild(newBadge, badge);
        const updatedBadge = newBadge;
        
        updatedBadge.addEventListener('dragover', e => {
            if (!dragged || draggedKind !== 'unscheduled') return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            updatedBadge.style.opacity = '0.7';
            updatedBadge.style.transform = 'scale(1.2)';
        });
        updatedBadge.addEventListener('dragleave', () => {
            updatedBadge.style.opacity = '1';
            updatedBadge.style.transform = 'scale(1)';
        });
        updatedBadge.addEventListener('drop', async e => {
            e.preventDefault();
            e.stopPropagation(); // PREVENT parent drop handler from firing!
            updatedBadge.style.opacity = '1';
            updatedBadge.style.transform = 'scale(1)';
            
            if (!dragged || draggedKind !== 'unscheduled') return;
            
            // Get cell info (parent .timetable-cell)
            const cell = updatedBadge.closest('.timetable-cell');
            if (!cell) return;
            
            const groupId = dragged.dataset.groupId;
            const groupName = dragged.dataset.groupName;
            const subjectName = dragged.dataset.subjectName;
            const teacherId = cell.dataset.teacherId;
            const day = cell.dataset.day;
            const slot = cell.dataset.slot;
            
            // Fetch conflict data
            try {
                const resp = await fetch(`<?php echo e(route('schools.timetables.check-conflict', [$school, $timetable])); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, day: day, slot: slot })
                });
                const data = await resp.json();
                console.log('Drag-drop conflict check:', data);
                
                // Check if there's a room conflict in the conflicts array
                const hasRoomConflict = data.conflicts && Array.isArray(data.conflicts) && data.conflicts.some(c => 
                    typeof c === 'object' && c.type === 'room'
                );
                
                // If it's only a room conflict (no students/teacher conflicts), show group copy modal
                if (hasRoomConflict && data.available_rooms) {
                    console.log('Room conflict detected, showing group copy flow');
                    showRoomConflictModal(data, { day, slot, teacherId, groupId, groupName, subjectName });
                    return;
                }
                
                // If there are blocking conflicts (not just room), show error
                if (data.hasConflicts) {
                    console.log('Other conflicts detected:', data.message);
                    showErrorModal('Aptiktai konfliktai', data.message || 'Šiame langelyje negalima pridėti pamokos');
                    return;
                }
                
                // If no conflicts, add lesson directly
                console.log('No conflicts, adding lesson');
                try {
                    const addResp = await fetch(`<?php echo e(route('schools.timetables.manual-slot', [$school, $timetable])); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, day: day, slot: slot })
                    });
                    const addData = await addResp.json();
                    console.log('Add lesson response:', addResp.status, addData);
                    
                    if (!addResp.ok || !addData.success) {
                        // Check if it's a room conflict
                        if (addData.conflict_type === 'room' && addData.available_rooms) {
                            console.log('Room conflict detected during add, showing modal...', addData);
                            showRoomConflictModal(addData, { day, slot, teacherId, groupId, groupName, subjectName });
                            return;
                        }
                        console.log('Error response:', addData);
                        showErrorModal('Klaida', addData.error || 'Nepavyko pridėti pamokos');
                        return;
                    }
                    
                    // Update cell with new badge
                    const tooltipHtml = `<div class=\"tt-inner\">`
                      + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
                      + `<div class=\"tt-divider\"></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${addData.html.group}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${addData.html.subject ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${addData.html.room ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-person-badge tt-ico\"></i><span class=\"tt-val\">${addData.html.teacher_name ?? '—'}</span></div>`
                      + `</div>`;
                    const b64 = btoa(unescape(encodeURIComponent(tooltipHtml)));
                    cell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${b64}\" draggable=\"true\"
                            data-kind=\"scheduled\"
                            data-slot-id=\"${addData.html.slot_id}\"
                            data-group-id=\"${groupId}\"
                            data-teacher-id=\"${teacherId}\"
                            data-group-name=\"${addData.html.group}\"
                            data-subject-name=\"${addData.html.subject ?? ''}\"
                    >${addData.html.group}</span>`;
                    if (window.bootstrap) {
                        const badge = cell.querySelector('.tt-trigger');
                        initBadgeDrag(badge);
                        // Initialize tooltip for the new element only (avoid double init)
                        const b64 = badge.getAttribute('data-tooltip-b64');
                        const html = b64 ? decodeURIComponent(Array.prototype.map.call(atob(b64), c => '%' + ('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')) : '';
                        if(html){
                            badge.setAttribute('aria-label', html.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim());
                            const existing = bootstrap.Tooltip.getInstance(badge);
                            if (existing) { existing.dispose(); }
                            const tt = new bootstrap.Tooltip(badge, { title: html, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
                            badge.dataset.ttInit = '1';
                        }
                    }
                    
                    // Update unscheduled list using backend data
                    if (addData.group_id && addData.remaining_lessons !== undefined && addData.group_data) {
                        updateUnscheduledList(addData.group_id, addData.remaining_lessons, addData.group_data);
                    }
                    flashMessage('Pamoka sėkmingai įtraukta', 'success');
                } catch(err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            } catch (err) {
                console.error('Error checking conflicts:', err);
                showErrorModal('Klaida', 'Klaida tikrinant konfliktus');
            }
        });
    });
}

function clearAvailabilityMarks(){
    // Remove availability badges and cell highlight classes
    document.querySelectorAll('.availability-badge').forEach(b=>b.remove());
    document.querySelectorAll('.timetable-cell').forEach(cell=>{
        cell.classList.remove('bg-success-subtle','bg-warning-subtle','bg-danger-subtle','bg-info-subtle','checking-slot');
    });
    document.querySelectorAll('.unscheduled-item.active-group').forEach(el=> el.classList.remove('active-group'));
}

async function openEditGroupModal(groupId, buttonElement) {
    if (!groupId) return;
    
    const conflictModal = buttonElement.closest('.modal');
    if (conflictModal) {
        const bsModal = bootstrap.Modal.getInstance(conflictModal);
        if (bsModal) bsModal.hide();
    }
    
    try {
        const resp = await fetch(`<?php echo e(route('schools.timetables.groups.edit-data', [$school, $timetable, ':groupId'])); ?>`.replace(':groupId', groupId));
        const data = await resp.json();
        
        const editModal = document.createElement('div');
        editModal.className = 'modal fade';
        editModal.tabIndex = -1;
        editModal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Redaguoti grupę</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editGroupForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="${data.group.name}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dalykas</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    ${data.subjects.map(s => `<option value="${s.id}" ${data.group.subject_id == s.id ? 'selected' : ''}>${s.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mokytojas</label>
                                <select name="teacher_login_key_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    ${data.teachers.map(t => `<option value="${t.id}" ${data.group.teacher_login_key_id == t.id ? 'selected' : ''}>${t.full_name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kabinetas</label>
                                <select name="room_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    ${data.rooms.map(r => `<option value="${r.id}" ${data.group.room_id == r.id ? 'selected' : ''}>${r.number} ${r.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Savaitės tipas</label>
                                    <select name="week_type" class="form-select" required>
                                        <option value="all" ${data.group.week_type == 'all' ? 'selected' : ''}>Kiekviena</option>
                                        <option value="even" ${data.group.week_type == 'even' ? 'selected' : ''}>Lyginės</option>
                                        <option value="odd" ${data.group.week_type == 'odd' ? 'selected' : ''}>Nelyginės</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Pamokų sk./sav.</label>
                                    <input type="number" name="lessons_per_week" class="form-control" min="1" max="20" value="${data.group.lessons_per_week}" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_priority" class="form-check-input" value="1" ${data.group.is_priority ? 'checked' : ''}>
                                    <label class="form-check-label">
                                        <i class="bi bi-star"></i> Prioritetinė pamoka (1-5 pamokos)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="submit" class="btn btn-warning">Išsaugoti</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(editModal);
        const bsEditModal = new bootstrap.Modal(editModal);
        bsEditModal.show();
        
        const form = editModal.querySelector('#editGroupForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const updateData = {};
            formData.forEach((value, key) => updateData[key] = value);
            
            try {
                const updateResp = await fetch(`<?php echo e(route('schools.timetables.groups.update', [$school, $timetable, ':groupId'])); ?>`.replace(':groupId', groupId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                const result = await updateResp.json();
                
                if (result.success) {
                    bsEditModal.hide();
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    flashMessage('Grupė atnaujinta', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showErrorModal('Klaida', result.message || 'Nepavyko išsaugoti');
                }
            } catch (err) {
                showErrorModal('Klaida', 'Nepavyko išsaugoti grupės');
            }
        });
        
        editModal.addEventListener('hidden.bs.modal', () => editModal.remove());
    } catch (err) {
        showErrorModal('Klaida', 'Nepavyko užkrauti grupės duomenų');
    }
}

// Initialize Bootstrap tooltips for data-tooltip-b64
if (window.bootstrap) {
    if (!window.__tt_b64_init_done) {
        window.__tt_b64_init_done = true;
    function b64ToUtf8(b64){
        try { return decodeURIComponent(Array.prototype.map.call(atob(b64), c => '%' + ('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')); } catch(e){ return ''; }
    }
    document.querySelectorAll('[data-tooltip-b64]').forEach(function(el){
        if (el.dataset.ttInit === '1') { return; }
        const b64 = el.getAttribute('data-tooltip-b64');
        const html = b64ToUtf8(b64);
        if(!html) return;
        // Accessibility fallback only (avoid native browser tooltip)
        el.setAttribute('aria-label', html.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim());
        const existing = bootstrap.Tooltip.getInstance(el);
        if (existing) { existing.dispose(); }
        new bootstrap.Tooltip(el, { title: html, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
        el.dataset.ttInit = '1';
    });
    }
}
</script>
<style>
.lesson-col { min-width: 140px; }
.drop-target { transition: background-color 0.2s; }
.drop-target.drop-hover { background-color: #d1e7dd !important; border: 2px dashed #198754 !important; }
.tt-trigger { transition: transform .12s ease, box-shadow .12s; cursor: pointer; }
.tt-trigger:hover { transform: translateY(-2px); box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
.tt-trigger.lesson-selected { 
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.5) !important; 
    transform: scale(1.05);
    z-index: 10;
}

.context-menu {
    position: fixed;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    min-width: 280px;
    z-index: 2000;
    font-size: 14px;
    padding: 8px 0;
    display: block !important;
    visibility: visible !important;
    pointer-events: auto !important;
    overflow: visible;
}

.context-menu-header {
    padding: 12px 16px;
    font-weight: 600;
    color: #212529;
    border-bottom: 2px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 6px 6px 0 0;
    font-size: 14px;
}}

.context-menu-item {
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.15s ease;
    color: #212529 !important;
    white-space: nowrap !important;
    border: none;
    background: transparent;
    text-align: left;
}

.context-menu-item:hover {
    background: #f0f0f0;
    color: #212529 !important;
    padding-left: 20px;
}

.context-menu-item.text-danger {
    color: #dc3545 !important;
}

.context-menu-item.text-danger:hover {
    background: #ffe0e0;
    color: #dc3545 !important;
    padding-left: 20px;
}

.context-menu-item i {
    font-size: 16px;
    width: 20px;
}

.context-menu-divider {
    height: 1px;
    background: #d0d0d0;
    margin: 6px 0;
    border: none;
}
.unscheduled-item { cursor: move; padding: 0.25rem 0.5rem; border-radius: 4px; transition: background-color 0.2s; }
.unscheduled-item:hover { background-color: #f8f9fa; }
.unscheduled-item.dragging { opacity: 0.5; }
.unscheduled-item.active-group { background-color:#e7f1ff; outline:2px solid #0d6efd; }
.unscheduled-title { font-weight: 600; color: #212529; }
.unscheduled-meta { font-size: 0.85rem; color: #6c757d; display: flex; gap: 8px; align-items: baseline; }
.unscheduled-subject { color: #198754; font-weight: 500; }
.unscheduled-teacher { color: #212529; background: #212529; border-radius: 4px; padding: 2px 6px; color: #fff; font-size: 0.75rem; }
.unscheduled-room { color:#0d6efd; }
.unscheduled-room::before { content:'•'; margin:0 4px; }

/* Slot checking animation */
.checking-slot {
    animation: pulse-blue 1s ease-in-out infinite;
}

@keyframes pulse-blue {
    0%, 100% { background-color: #cfe2ff; }
    50% { background-color: #9ec5fe; }
}

.availability-badge {
    font-size: 0.7rem;
    z-index: 5;
}

/* Tooltip custom styles */
.tt-inner {
    text-align: left;
    padding: 0.5rem;
    min-width: 200px;
    background-color: #2d3748;
    border-radius: 4px;
    color: #ffffff;
}
.tt-row {
    display: flex;
    align-items: center;
    padding: 0.35rem 0;
}
.tt-row-head {
    font-weight: 600;
    padding-bottom: 0.5rem;
    color: #ffffff;
}
.tt-ico {
    width: 20px;
    margin-right: 8px;
    color: #667eea;
}
.tt-val {
    flex: 1;
    color: #ffffff;
}
.tt-divider {
    border-top: 1px solid rgba(255,255,255,0.2);
    margin: 0.25rem 0;
}
</style>
<style>
/* Sticky first column (lesson number) in individual teacher view */
.sticky-col-row {
    position: sticky;
    left: 0;
    z-index: 5;
    background: #fff;
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/teacher-view.blade.php ENDPATH**/ ?>