<?php $__env->startSection('content'); ?>
<div style="width: 100%;">
    <div class="row mb-3">
        <div class="col-md-9">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-calendar3"></i> <?php echo e($timetable->name); ?> — Mokytojų tvarkaraštis</h2>
        <div class="btn-group">
            <a href="<?php echo e(route('schools.timetables.show', [$school, $timetable])); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Atgal
            </a>
            <button id="btnFullscreen" class="btn btn-primary" type="button">
                <i class="bi bi-arrows-fullscreen"></i> Visas ekranas
            </button>
        </div>
    </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" id="unscheduledPanel">
                <div class="card-header p-2"><strong>Nesuplanuotos pamokos</strong></div>
                <div class="card-body p-2" style="max-height:220px; overflow:auto;">
                    <?php $__empty_1 = true; $__currentLoopData = $unscheduled; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="unscheduled-item mb-1 d-flex align-items-center" draggable="true"
                             data-kind="unscheduled"
                             data-group-id="<?php echo e($u['group_id']); ?>"
                             data-group-name="<?php echo e($u['group_name'] ?? $u['group'] ?? ''); ?>"
                             data-subject-name="<?php echo e($u['subject_name'] ?? $u['subject'] ?? ''); ?>"
                             data-teacher-id="<?php echo e($u['teacher_login_key_id'] ?? ''); ?>"
                             data-remaining="<?php echo e($u['remaining_lessons']); ?>">
                            <div class="flex-grow-1">
                                <div class="unscheduled-title">
                                    <?php echo e($u['group_name'] ?? $u['group'] ?? 'Grupė'); ?>

                                    <span class="badge bg-primary ms-2 remaining-badge"><?php echo e($u['remaining_lessons']); ?></span>
                                </div>
                                <div class="unscheduled-meta">
                                    <span class="unscheduled-subject"><?php echo e($u['subject_name'] ?? $u['subject'] ?? ''); ?></span>
                                    <?php if(!empty($u['teacher_name'] ?? $u['teacher'] ?? '')): ?>
                                        <span class="unscheduled-teacher"><?php echo e($u['teacher_name'] ?? $u['teacher']); ?></span>
                                    <?php endif; ?>
                                    <span class="unscheduled-room"><i class="bi bi-door-closed"></i> <?php echo e($u['room_number'] ?? '—'); ?></span>
                                </div>
                            </div>
                            <?php if(!empty($u['teacher_login_key_id'])): ?>
                            <div class="ms-2">
                                <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="findAvailableSlots(<?php echo e($u['group_id']); ?>, '<?php echo e(addslashes($u['group_name'] ?? $u['group'] ?? '')); ?>', '<?php echo e(addslashes($u['subject_name'] ?? $u['subject'] ?? '')); ?>', <?php echo e($u['teacher_login_key_id']); ?>)"
                                        title="Rasti laisvus langelius">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="text-muted small">Visos grupės suplanuotos</span>
                    <?php endif; ?>
                </div>
                <div class="card-footer p-1 small d-flex justify-content-between align-items-center text-muted">
                    <span>Tempkite ant mokytojo pamokos langelio</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAvailabilityMarks()" title="Išvalyti žymėjimus">Išvalyti</button>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-2" role="alert">
        <i class="bi bi-info-circle me-1 align-middle fs-16"></i>
        Čia galite peržiūrėti mokytojų tvarkaraštį pagal dienas ir pamokas. Slankiokite lentelę horizontaliai ir vertikaliai.
    </div>

    

    <div class="card" id="timetableCard">
        <div class="card-body p-0">
            <div id="timetableContainer" data-simplebar data-simplebar-auto-hide="false" style="max-height: calc(100vh - 280px); position: relative; width: 100%;">
                <table class="table table-hover table-bordered align-middle mb-0" id="teachersGrid">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="text-center align-middle sticky-col" style="width:48px;">#</th>
                            <th rowspan="2" class="text-center align-middle sticky-col-name" style="width:220px;">Mokytojas</th>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th colspan="<?php echo e($dayCaps[$code]); ?>" class="text-center"><?php echo e($label); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <tr>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php for($i = 1; $i <= $dayCaps[$code]; $i++): ?>
                                    <?php $isLast = $i === $dayCaps[$code]; ?>
                                    <th class="text-center lesson-col <?php echo e($isLast ? 'day-break' : ''); ?>"><?php echo e($i); ?></th>
                                <?php endfor; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center sticky-col"><?php echo e($index + 1); ?></td>
                                <td class="sticky-col-name"><strong>
                                    <a href="<?php echo e(route('schools.timetables.teacher', [$school, $timetable, $teacher->id])); ?>" class="link-dark text-decoration-underline">
                                        <?php echo e($teacher->full_name); ?>

                                    </a>
                                </strong></td>
                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php for($l = 1; $l <= $dayCaps[$code]; $l++): ?>
                                        <?php
                                            $cell = $slots[$teacher->id][$code][$l] ?? null;
                                            $isLast = $l === $dayCaps[$code];
                                        ?>
                                        <td class="text-center lesson-col drop-target <?php echo e($isLast ? 'day-break' : ''); ?>" style="padding:0.3rem;" data-day="<?php echo e($code); ?>" data-slot="<?php echo e($l); ?>" data-teacher-id="<?php echo e($teacher->id); ?>">
                                            <?php if($cell): ?>
                                                <?php
                                                    $subject = $cell['subject'] ?? '—';
                                                    $roomNumber = $cell['room_number'] ?? null;
                                                    $roomDisplay = $roomNumber ?: '—';
                                                    $dayLabel = $days[$code] ?? $code;
                                                    $lessonNr = $l; // slot index
                                                    $teacherName = $teacher->full_name ?? '—';
                                                    // Minimalist tooltip: only icons + values
                                                    $tooltipHtml = '<div class="tt-inner">'
                                                        .'<div class="tt-row tt-row-head"><i class="bi bi-clock-history tt-ico"></i><span class="tt-val">'.e($dayLabel).' • '.e($lessonNr).' pamoka</span></div>'
                                                        .'<div class="tt-divider"></div>'
                                                        .'<div class="tt-row"><i class="bi bi-collection-fill tt-ico"></i><span class="tt-val">'.e($cell['group']).'</span></div>'
                                                        .'<div class="tt-row"><i class="bi bi-book-half tt-ico"></i><span class="tt-val">'.e($subject).'</span></div>'
                                                        .'<div class="tt-row"><i class="bi bi-door-closed tt-ico"></i><span class="tt-val">'.e($roomDisplay).'</span></div>'
                                                        .'<div class="tt-row"><i class="bi bi-person-badge tt-ico"></i><span class="tt-val">'.e($teacherName).'</span></div>'
                                                    .'</div>';
                                                    $tooltipB64 = base64_encode($tooltipHtml);
                                                ?>
                                                <span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" data-tooltip-b64="<?php echo e($tooltipB64); ?>" draggable="true"
                                                      data-kind="scheduled"
                                                      data-slot-id="<?php echo e($cell['slot_id']); ?>"
                                                      data-group-id="<?php echo e($cell['group_id']); ?>"
                                                      data-teacher-id="<?php echo e($cell['teacher_id']); ?>"
                                                      data-group-name="<?php echo e($cell['group']); ?>"
                                                      data-subject-name="<?php echo e($cell['subject'] ?? ''); ?>"
                                                ><?php echo e($cell['group']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Paprastas pilno ekrano perjungimas be perdangos
(function() {
    const card = document.getElementById('timetableCard');
    const btn = document.getElementById('btnFullscreen');
    const container = document.getElementById('timetableContainer');
    if (!card || !btn || !container) return;

    // Capture full original inline style strings for precise restoration
    if (!card.dataset.originalStyle) card.dataset.originalStyle = card.getAttribute('style') || '';
    if (!container.dataset.originalStyle) container.dataset.originalStyle = container.getAttribute('style') || '';
    const grid = document.getElementById('teachersGrid');
    if (grid && !grid.dataset.originalStyle) grid.dataset.originalStyle = grid.getAttribute('style') || '';

    function ensureHorizontalScroll() {
        // If after exit horizontal scroll disappeared, force intrinsic sizing then reflow
        const g = document.getElementById('teachersGrid');
        if (!g) return;
        // Force intrinsic width recalculation
        g.style.width = 'max-content';
        g.style.minWidth = '100%';
        // Recalculate SimpleBar if present
        requestAnimationFrame(()=>{
            if (container.SimpleBar) { try { container.SimpleBar.recalculate(); } catch(e) {} }
        });
                                                            scheduled=true;
    }

    btn.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            card.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    });

    document.addEventListener('fullscreenchange', function() {
        if (document.fullscreenElement === card) {
                                                            if (!scheduled) {
                                                                showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                                                            } else {
                                                                console.warn('Non-critical error after scheduling:', err);
                                                            }
            // Replace container inline style only for maxHeight without wiping others
            container.style.maxHeight = 'calc(100vh - 70px)';
            requestAnimationFrame(()=> window.dispatchEvent(new Event('resize')));
        } else {
            // Restore original inline styles exactly
            card.classList.remove('fullscreen-active');
            card.setAttribute('style', card.dataset.originalStyle);
            container.setAttribute('style', container.dataset.originalStyle);
            if (grid) {
                grid.setAttribute('style', grid.dataset.originalStyle);
            }
            // Dispatch resize twice (some browsers need double reflow after fullscreen exit)
            requestAnimationFrame(()=> {
                window.dispatchEvent(new Event('resize'));
                ensureHorizontalScroll();
                requestAnimationFrame(()=> window.dispatchEvent(new Event('resize')));
            });
        }
    });
})();

// Tooltip helper to initialize a single element safely
function initTooltipEl(el){
    if (!window.bootstrap || !el) return;
    const b64 = el.getAttribute('data-tooltip-b64');
    if (!b64) return;
    const html = (function(b){
        try { return decodeURIComponent(Array.prototype.map.call(atob(b), c => '%' + ('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')); } catch(e){ return ''; }
    })(b64);
    if(!html) return;
    el.setAttribute('aria-label', html.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim());
    const existing = bootstrap.Tooltip.getInstance(el);
    if (existing) existing.dispose();
    new bootstrap.Tooltip(el, { title: html, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
}

// Setup badge drag-over listeners for room conflict badges (must be OUTSIDE DOMContentLoaded)
function setupBadgeDragDrop() {
    document.querySelectorAll('.availability-badge.bg-warning').forEach(badge => {
        // Remove existing listeners to avoid duplicates
        const newBadge = badge.cloneNode(true);
        badge.parentNode.replaceChild(newBadge, badge);
        const updatedBadge = newBadge;
        
        updatedBadge.addEventListener('dragover', e => {
            if (!window.draggedState?.dragged || window.draggedState?.draggedKind !== 'unscheduled') return;
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
            const dragged = window.draggedState?.dragged;
            const draggedKind = window.draggedState?.draggedKind;
            if (!dragged || draggedKind !== 'unscheduled') return;
            
            // Get cell info (parent .drop-target)
            const cell = updatedBadge.closest('.drop-target');
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
                
                // If it's only a room conflict (no students/teacher conflicts), show room conflict modal
                if (hasRoomConflict && data.available_rooms) {
                    console.log('Room conflict detected, showing modal');
                    showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, day, slot, 'room_conflict', data.conflicts, data.available_rooms, data.current_room?.id);
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
                    console.log('Add response:', addData);
                    if (addResp.ok && addData.success) {
                        flashMessage('Pamoka sėkmingai pridėta', 'success');
                        if (addData.group_id && addData.group_data) {
                            updateUnscheduledList(addData.group_id, addData.remaining_lessons, addData.group_data);
                        }
                    } else if (addData.conflict_type === 'room' && addData.available_rooms) {
                        console.log('Room conflict from add, showing modal');
                        showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, day, slot, 'room_conflict', [], addData.available_rooms, addData.current_room?.id);
                    } else {
                        console.log('Add failed:', addData.error);
                        showErrorModal('Klaida', addData.error || 'Nepavyko pridėti pamokos');
                    }
                } catch(err) {
                    console.error('Add request error:', err);
                    showErrorModal('Klaida', 'Nepavyko siunčiant užklausą');
                }
            } catch(err) {
                console.error('Check-conflict error:', err);
                showErrorModal('Klaida', 'Nepavyko atlikti tikrinimo');
            }
        });
    });
}

// Drag & drop manual scheduling
document.addEventListener('DOMContentLoaded', function(){
    const UNSCHEDULED_SELECTOR = '.unscheduled-item';
    
    // Store drag state globally
    window.draggedState = {
        dragged: null,
        draggedKind: null
    };
    
    document.querySelectorAll(UNSCHEDULED_SELECTOR).forEach(el => {
        el.addEventListener('dragstart', e => {
            window.draggedState.dragged = el;
            window.draggedState.draggedKind = 'unscheduled';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', el.dataset.groupId);
            el.classList.add('dragging');
        });
        el.addEventListener('dragend', () => {
            window.draggedState.dragged?.classList.remove('dragging');
            window.draggedState.dragged = null;
            window.draggedState.draggedKind = null;
        });
    });
    // Make existing scheduled badges draggable
    function initBadgeDrag(el){
        if (!el) return;
        el.addEventListener('dragstart', e => {
            window.draggedState.dragged = el;
            window.draggedState.draggedKind = 'scheduled';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', el.dataset.slotId || '');
            el.classList.add('dragging');
        });
        el.addEventListener('dragend', () => {
            window.draggedState.dragged?.classList.remove('dragging');
            window.draggedState.dragged = null;
            window.draggedState.draggedKind = null;
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
    document.querySelectorAll('.tt-trigger[draggable="true"]').forEach(initBadgeDrag);
    
    // Initialize badge drag-drop
    setupBadgeDragDrop();

    document.querySelectorAll('.drop-target').forEach(cell => {
        cell.addEventListener('dragover', e => {
            if (!window.draggedState?.dragged) return;
            const rowTeacherId = String(cell.dataset.teacherId || '');
            let canDrop = true;
            if (window.draggedState.draggedKind === 'unscheduled') {
                const itemTeacherId = String(window.draggedState.dragged.dataset.teacherId || '');
                canDrop = !!itemTeacherId && itemTeacherId === rowTeacherId;
            } else if (window.draggedState.draggedKind === 'scheduled') {
                const itemTeacherId = String(window.draggedState.dragged.dataset.teacherId || '');
                canDrop = !!itemTeacherId && itemTeacherId === rowTeacherId;
            }
            if (!canDrop) {
                e.dataTransfer.dropEffect = 'none';
                cell.classList.remove('drop-hover');
                return; // don't preventDefault: keeps 'no drop' cursor
            }
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            cell.classList.add('drop-hover');
        });
        cell.addEventListener('dragleave', () => cell.classList.remove('drop-hover'));
        cell.addEventListener('drop', async e => {
            e.preventDefault();
            cell.classList.remove('drop-hover');
            if (!window.draggedState?.dragged) return;
            
            const rowTeacherId = cell.dataset.teacherId;
            const teacherId = cell.dataset.teacherId;
            const day = cell.dataset.day;
            const slot = cell.dataset.slot;
            if (window.draggedState.draggedKind === 'unscheduled') {
                const dragged = window.draggedState.dragged;
                const groupId = dragged.dataset.groupId;
                const groupName = dragged.dataset.groupName;
                const subjectName = dragged.dataset.subjectName;
                
                try {
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
                    if (!resp.ok || !data.success) {
                        // Check if it's a room conflict
                        if (data.conflict_type === 'room' && data.available_rooms) {
                            await showRoomConflictModal(data, cell, groupId, teacherId, day, slot, dragged);
                            return;
                        }
                        showErrorModal('Klaida', data.error || 'Nepavyko įtraukti pamokos');
                        return;
                    }
                    // Build tooltip for new cell
                    const tooltipHtml = `<div class=\"tt-inner\">`
                      + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
                      + `<div class=\"tt-divider\"></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${data.html.group}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${data.html.subject ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${data.html.room ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-person-badge tt-ico\"></i><span class=\"tt-val\">${data.html.teacher_name ?? '—'}</span></div>`
                      + `</div>`;
                                        const b64 = btoa(unescape(encodeURIComponent(tooltipHtml)));
                                        cell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${b64}\" draggable=\"true\"
                                                data-kind=\"scheduled\"
                                                data-slot-id=\"${data.html.slot_id}\"
                                                data-group-id=\"${groupId}\"
                                                data-teacher-id=\"${data.html.teacher_id}\"
                                                data-group-name=\"${data.html.group}\"
                                                data-subject-name=\"${data.html.subject ?? ''}\"
                                        >${data.html.group}</span>`;
                    // re-init tooltip
                                        const badge = cell.querySelector('.tt-trigger');
                                        initBadgeDrag(badge);
                                        initTooltipEl(badge);
                    // Update unscheduled list using backend data
                    if (data.group_id && data.remaining_lessons !== undefined && data.group_data) {
                        updateUnscheduledList(data.group_id, data.remaining_lessons, data.group_data);
                    }
                    flashMessage('Pamoka sėkmingai įtraukta', 'success');
                } catch(err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            } else if (window.draggedState.draggedKind === 'scheduled') {
                const dragged = window.draggedState.dragged;
                // Move existing slot
                const slotId = dragged.dataset.slotId;
                const groupName = dragged.dataset.groupName || 'Grupė';
                const subjectName = dragged.dataset.subjectName || '';
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
                            const swappedTooltip = `<div class=\"tt-inner\">`
                              + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${swapData.swappedHtml.day} • ${swapData.swappedHtml.slot} pamoka</span></div>`
                              + `<div class=\"tt-divider\"></div>`
                              + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${swapData.swappedHtml.group}</span></div>`
                              + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${swapData.swappedHtml.subject ?? '—'}</span></div>`
                              + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${swapData.swappedHtml.room_number ?? '—'}</span></div>`
                              + `<div class=\"tt-row\"><i class=\"bi bi-person-badge tt-ico\"></i><span class=\"tt-val\">${swapData.swappedHtml.teacher_name ?? '—'}</span></div>`
                              + `</div>`;
                            const swappedB64 = btoa(unescape(encodeURIComponent(swappedTooltip)));
                            if (originalCell) {
                                originalCell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${swappedB64}\" draggable=\"true\"
                                        data-kind=\"scheduled\"
                                        data-slot-id=\"${swapData.swappedHtml.slot_id}\"
                                        data-group-id=\"${swapData.swappedHtml.group_id ?? ''}\"
                                        data-teacher-id=\"${teacherId}\"
                                        data-group-name=\"${swapData.swappedHtml.group}\"
                                        data-subject-name=\"${swapData.swappedHtml.subject ?? ''}\"
                                >${swapData.swappedHtml.group}</span>`;
                                const swappedBadge = originalCell.querySelector('.tt-trigger');
                                initBadgeDrag(swappedBadge);
                                initTooltipEl(swappedBadge);
                            }
                        }
                        
                        // Update target cell
                        const tooltipHtml = `<div class=\"tt-inner\">`
                          + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
                          + `<div class=\"tt-divider\"></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${swapData.html.group}</span></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${swapData.html.subject ?? '—'}</span></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${swapData.html.room_number ?? '—'}</span></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-person-badge tt-ico\"></i><span class=\"tt-val\">${swapData.html.teacher_name ?? '—'}</span></div>`
                          + `</div>`;
                        const b64 = btoa(unescape(encodeURIComponent(tooltipHtml)));
                        cell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${b64}\" draggable=\"true\"
                                data-kind=\"scheduled\"
                                data-slot-id=\"${swapData.html.slot_id ?? ''}\"
                                data-group-id=\"${groupId}\"
                                data-teacher-id=\"${teacherId}\"
                                data-group-name=\"${swapData.html.group}\"
                                data-subject-name=\"${swapData.html.subject ?? ''}\"
                        >${swapData.html.group}</span>`;
                        const badge = cell.querySelector('.tt-trigger');
                        initBadgeDrag(badge);
                        initTooltipEl(badge);
                        flashMessage('Pamokos sėkmingai sukeistos', 'success');
                        return;
                    }
                    
                    if (!resp.ok || !data.success) {
                        showErrorModal('Klaida', data.error || 'Nepavyko perkelti pamokos');
                        return;
                    }
                    
                    // Simple move (no swap)
                    if (originalCell) originalCell.innerHTML = '<span class="text-muted">—</span>';
                    const tooltipHtml = `<div class=\"tt-inner\">`
                      + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
                      + `<div class=\"tt-divider\"></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${data.html.group}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${data.html.subject ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${data.html.room_number ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-person-badge tt-ico\"></i><span class=\"tt-val\">${data.html.teacher_name ?? '—'}</span></div>`
                      + `</div>`;
                    const b64 = btoa(unescape(encodeURIComponent(tooltipHtml)));
                    cell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${b64}\" draggable=\"true\"
                            data-kind=\"scheduled\"
                            data-slot-id=\"${data.html.slot_id ?? ''}\"
                            data-group-id=\"${groupId}\"
                            data-teacher-id=\"${teacherId}\"
                            data-group-name=\"${data.html.group}\"
                            data-subject-name=\"${data.html.subject ?? ''}\"
                    >${data.html.group}</span>`;
                    const badge = cell.querySelector('.tt-trigger');
                    initBadgeDrag(badge);
                    initTooltipEl(badge);
                    flashMessage('Pamoka perkelta', 'success');
                } catch(err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            }
        });
    });
});

// Global functions - outside DOMContentLoaded for onclick access
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
    // Inline fallback styles in case CSS fails to load
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
    // Hover effects inline
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

// NOTE: Context menu is already initialized in initBadgeDrag() function
// No need for duplicate DOMContentLoaded listener

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
        
        // Update unscheduled list if backend provides data
        if (data.group_id && data.remaining_lessons !== undefined && data.group_data) {
            updateUnscheduledList(data.group_id, data.remaining_lessons, data.group_data);
        }
    } catch(err) {
        console.error(err);
        flashMessage('Klaida siunčiant užklausą', 'danger');
    }
}

async function checkConflicts(groupId, teacherId, day, slot) {
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
        return data;
    } catch (err) {
        return { hasConflicts: false, conflicts: [] };
    }
}

function showConfirmDialog(groupName, subjectName, day, slot, conflictData, groupId) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        
        // Process conflicts to render objects nicely
        let conflictsHtml = '';
        if (conflictData.hasConflicts && conflictData.conflicts) {
            conflictsHtml = conflictData.conflicts.map(c => {
                if (typeof c === 'string') {
                    if (c.startsWith('Užimti mokiniai:')) {
                        const studentsPart = c.substring('Užimti mokiniai:'.length).trim();
                        const students = studentsPart.split(',').map(s => s.trim()).filter(s => s.length > 0);
                        students.sort((a, b) => a.localeCompare(b, 'lt'));
                        return '<li><strong>Užimti mokiniai:</strong><ul class="mt-1">' + students.map(s => '<li>'+s+'</li>').join('') + '</ul></li>';
                    }
                    return '<li>'+c+'</li>';
                }
                if (typeof c === 'object' && c) {
                    const msg = c.message || (c.type === 'room' ? 'Kabinetas užimtas' : (c.type === 'students' ? 'Mokinių konfliktas' : 'Konfliktas'));
                    return '<li>'+msg+'</li>';
                }
                return '';
            }).join('');
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
                            <div class="alert alert-danger mb-0">
                                <strong>Negalima pridėti pamokos:</strong>
                                <ul class="mb-0 mt-2">
                                    ${conflictsHtml}
                                </ul>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            ${conflictData.hasConflicts ? 'Uždaryti' : 'Atšaukti'}
                        </button>
                        ${!conflictData.hasConflicts ? `
                            <button type="button" class="btn btn-primary" id="confirmAdd">
                                <i class="bi bi-plus-circle"></i> Pridėti
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        if (!conflictData.hasConflicts) {
            modal.querySelector('#confirmAdd').addEventListener('click', () => {
                bsModal.hide();
                resolve(true);
            });
        }
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
            resolve(false);
        });
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


function showErrorModal(title, message) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let bodyHtml;
    if (Array.isArray(message)) {
        bodyHtml = '<ul class="mb-0">' + message.map(m=>'<li>'+m+'</li>').join('') + '</ul>';
    } else if (typeof message === 'string' && message.startsWith('Užimti mokiniai:')) {
        // Parse student conflicts
        const studentsPart = message.substring('Užimti mokiniai:'.length).trim();
        const students = studentsPart.split(',').map(s => s.trim()).filter(s => s.length > 0);
        students.sort((a, b) => a.localeCompare(b, 'lt'));
        bodyHtml = '<p class="mb-2"><strong>Užimti mokiniai:</strong></p><ul class="mb-0">' + students.map(s => '<li>'+s+'</li>').join('') + '</ul>';
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
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

async function showRoomConflictModal(conflictData, cell, groupId, teacherId, day, slot, dragged) {
    const groupName = conflictData.group.name;
    const subjectName = conflictData.group.subject_name;
    
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
        
        console.log('Checking room availability for:', roomId);
        
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
            console.log('Availability check response:', data);
            
            let hasRoomConflict = false;
            if (data.conflicts && Array.isArray(data.conflicts)) {
                hasRoomConflict = data.conflicts.some(c => {
                    if (typeof c === 'object' && c !== null) return c.type === 'room';
                    if (typeof c === 'string') return c.includes('Kabinetas') || c.includes('užimtas');
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
            console.error('Error checking availability:', err);
            hint.className = 'alert alert-secondary mb-0';
            hint.innerHTML = '<i class="bi bi-question-circle"></i> Nepavyko patikrinti. Bandykite dar kartą.';
            roomAvailability[roomId] = false;
            saveBtn.disabled = true;
        }
    }
    
    roomSelect.addEventListener('change', async (e) => {
        console.log('Room select changed:', e.target.value);
        selectedRoomId = e.target.value ? parseInt(e.target.value) : null;
        
        if (!selectedRoomId) {
            hint.className = 'alert alert-info mb-0';
            hint.innerHTML = '<i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą';
            saveBtn.disabled = true;
            return;
        }
        
        await checkRoomAvailability(selectedRoomId);
    });
    
    saveBtn.addEventListener('click', async () => {
        console.log('Save button clicked, selectedRoomId:', selectedRoomId, 'available:', roomAvailability[selectedRoomId]);
        if (!selectedRoomId || roomAvailability[selectedRoomId] !== true) return;
        
        // Close current modal
        bsModal.hide();
        
        // Open new modal for group copy creation
        showGroupCopyModal(groupId, groupName, subjectName, teacherId, day, slot, selectedRoomId);
    });
    
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function flashMessage(msg,type){
    let box=document.getElementById('flashBox');
    if(!box){ box=document.createElement('div'); box.id='flashBox'; box.style.position='fixed'; box.style.top='10px'; box.style.right='10px'; box.style.zIndex='9999'; document.body.appendChild(box); }
    const el=document.createElement('div'); el.className=`alert alert-${type} py-1 px-2 mb-2`; el.textContent=msg; box.appendChild(el); setTimeout(()=>{ el.remove(); if(!box.children.length) box.remove(); },3000);
}
// --- Added helpers for unscheduled list + clearing availability ---
function updateUnscheduledList(groupId, remainingLessons, groupData){
    const panel=document.querySelector('#unscheduledPanel .card-body');
    if(!panel) return;
    let existing=null;
    panel.querySelectorAll('.unscheduled-item').forEach(it=>{ if(it.dataset.groupId==groupId) existing=it; });
    if(remainingLessons>0){
        if(existing){
            existing.dataset.remaining=remainingLessons;
            const badge=existing.querySelector('.remaining-badge'); if(badge) badge.textContent=remainingLessons;
        } else if(groupData){
            const div=document.createElement('div');
            div.className='unscheduled-item mb-1 d-flex align-items-center';
            div.draggable=true; div.dataset.kind='unscheduled';
            div.dataset.groupId=groupId; div.dataset.groupName=groupData.group_name; div.dataset.subjectName=groupData.subject_name; div.dataset.teacherId=groupData.teacher_login_key_id||''; div.dataset.teacherName=groupData.teacher_name||''; div.dataset.remaining=remainingLessons;
            const roomInfo = `<span class=\"unscheduled-room\"><i class=\"bi bi-door-closed\"></i> ${groupData.room_number || '—'}</span>`;
            div.innerHTML=`<div class=\"flex-grow-1\"><div class=\"unscheduled-title\">${groupData.group_name} <span class=\"badge bg-primary ms-2 remaining-badge\">${remainingLessons}</span></div><div class=\"unscheduled-meta\"><span class=\"unscheduled-subject\">${groupData.subject_name}</span>${groupData.teacher_name?`<span class=\"unscheduled-teacher\">${groupData.teacher_name}</span>`:''}${roomInfo}</div></div><div class=\"ms-2\">${groupData.teacher_login_key_id?`<button type='button' class='btn btn-outline-info btn-sm' onclick=\"findAvailableSlots(${groupId}, '${groupData.group_name.replace(/'/g,"\\'")}', '${groupData.subject_name.replace(/'/g,"\\'")}', ${groupData.teacher_login_key_id})\" title='Rasti laisvus langelius'><i class='bi bi-search'></i></button>`:''}</div>`;
            panel.insertBefore(div,panel.firstChild);
        }
    } else if(existing){
        existing.remove();
        if(!panel.querySelector('.unscheduled-item')){
            const empty=document.createElement('span'); empty.className='text-muted small'; empty.textContent='Visos grupės suplanuotos'; panel.appendChild(empty);
        }
    }
}

function clearAvailabilityMarks(){
    document.querySelectorAll('.availability-badge').forEach(b=>b.remove());
    document.querySelectorAll('.lesson-col').forEach(c=>c.classList.remove('bg-success-subtle','bg-warning-subtle','bg-danger-subtle','bg-info-subtle','checking-slot'));
    document.querySelectorAll('.unscheduled-item.active-group').forEach(el=>el.classList.remove('active-group'));
}

async function openEditGroupModal(groupId, buttonElement) {
    if (!groupId) return;
    
    // Close the conflict modal first
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
                                    ${data.subjects.map(s => '<option value="'+s.id+'" '+(data.group.subject_id == s.id ? 'selected' : '')+'>'+s.name+'</option>').join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mokytojas</label>
                                <select name="teacher_login_key_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    ${data.teachers.map(t => '<option value="'+t.id+'" '+(data.group.teacher_login_key_id == t.id ? 'selected' : '')+'>'+t.full_name+'</option>').join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kabinetas</label>
                                <select name="room_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    ${data.rooms.map(r => '<option value="'+r.id+'" '+(data.group.room_id == r.id ? 'selected' : '')+'>'+r.number+' '+(r.name||'')+'</option>').join('')}
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

async function findAvailableSlots(groupId, groupName, subjectName, teacherId) {
    if (!teacherId) { showErrorModal('Klaida', 'Grupė neturi priskirto mokytojo'); return; }

    // Highlight selected group in unscheduled list
    document.querySelectorAll('.unscheduled-item').forEach(el=>el.classList.remove('active-group'));
    const chosen=document.querySelector(`.unscheduled-item[data-group-id='${groupId}']`);
    if(chosen) chosen.classList.add('active-group');

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
    document.querySelectorAll('.lesson-col').forEach(cell => {
        cell.classList.remove('bg-success-subtle','bg-warning-subtle','bg-danger-subtle','bg-info-subtle','checking-slot');
        const b = cell.querySelector('.availability-badge'); if (b) b.remove();
    });

    const teacherCells = Array.from(document.querySelectorAll(`.lesson-col[data-teacher-id='${teacherId}']`));
    const items = [];
    teacherCells.forEach(cell => {
        const hasLesson = cell.querySelector('[draggable="true"]');
        if (hasLesson) return;
        const day = cell.getAttribute('data-day');
        const slot = parseInt(cell.getAttribute('data-slot'),10);
        if (!day || !slot) return;
        items.push({ day, slot });
        cell.classList.add('bg-info-subtle');
    });

    if (!items.length) { overlay.remove(); flashMessage('Nėra laisvų langelių tikrinimui','info'); return; }

    let resp; let data;
    try {
        resp = await fetch(`<?php echo e(route('schools.timetables.bulk-conflicts', [$school, $timetable])); ?>`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content'),'Accept':'application/json' },
            body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, items })
        });
        data = await resp.json();
    } catch(e) {
        overlay.remove(); showErrorModal('Klaida','Nepavyko atlikti masinio tikrinimo'); return;
    }
    overlay.remove();
    if (!resp.ok || !data.success) { showErrorModal('Klaida', data.error || 'Nepavyko gauti rezultatų'); return; }

    const map = {}; data.items.forEach(r => { map[r.day+'|'+r.slot] = r; });
    teacherCells.forEach(cell => {
        const hasLesson = cell.querySelector('[draggable="true"]');
        if (hasLesson) return;
        const day = cell.getAttribute('data-day');
        const slot = parseInt(cell.getAttribute('data-slot'),10);
        const r = map[day+'|'+slot];
        cell.classList.remove('bg-info-subtle');
        if (!r) return;
        let badge; const conf = r.conflicts || [];
        if (!r.hasConflicts) {
            badge = document.createElement('div');
            badge.className='availability-badge badge bg-success position-absolute top-0 end-0 m-1';
            badge.style.fontSize='0.7rem';
            badge.innerHTML='<i class="bi bi-check-circle"></i>'; // Laisvas indikacija
            badge.title='Laisvas';
            badge.style.cursor='pointer';
            // Add click handler
            badge.addEventListener('click', function(e) {
                e.stopPropagation();
                showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, day, slot, 'free', [], availableRooms, currentRoomId);
            });
        } else {
            const student = conf.find(c=>typeof c==='object' && c.type==='students');
            const room = conf.find(c=>typeof c==='object' && c.type==='room');
            const teacherBusy = conf.find(c=>typeof c==='string' && (c.includes('turi pamoką') || c.includes('Mokytojas')));
            const studentCount = student && Array.isArray(student.students) ? student.students.length : 0;
            if (teacherBusy) {
                badge=document.createElement('div');
                badge.className='availability-badge badge bg-secondary position-absolute top-0 end-0 m-1';
                badge.style.fontSize='0.7rem';
                badge.innerHTML='<i class="bi bi-person-x"></i>';
                badge.title='Mokytojas užimtas';
            } else if (student && room) {
                badge=document.createElement('div');
                badge.className='availability-badge badge bg-danger position-absolute top-0 end-0 m-1';
                badge.style.fontSize='0.7rem';
                badge.innerHTML='<i class="bi bi-people"></i> '+studentCount+' <i class="bi bi-door-closed"></i>';
                badge.title='Mokiniai ir kabinetas konfliktuoja';
            } else if (student) {
                badge=document.createElement('div');
                badge.className='availability-badge badge bg-danger position-absolute top-0 end-0 m-1';
                badge.style.fontSize='0.7rem';
                badge.innerHTML='<i class="bi bi-people"></i> '+studentCount;
                badge.title='Mokinių konfliktas';
            } else if (room) {
                badge=document.createElement('div');
                badge.className='availability-badge badge bg-warning position-absolute top-0 end-0 m-1';
                badge.style.fontSize='0.7rem';
                badge.innerHTML='<i class="bi bi-door-closed"></i>';
                badge.title='Kabinetas užimtas';
            } else {
                badge=document.createElement('div');
                badge.className='availability-badge badge bg-warning position-absolute top-0 end-0 m-1';
                badge.style.fontSize='0.7rem';
                badge.innerHTML='<i class="bi bi-exclamation-triangle"></i>';
                badge.title='Kitas konfliktas';
            }
            badge.style.cursor='pointer';
            // Add click handler BEFORE appending
            const statusCopy = r.status;
            const conflictsCopy = conf.slice();
            badge.addEventListener('click', function(e) {
                e.stopPropagation();
                showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, day, slot, statusCopy, conflictsCopy, availableRooms, currentRoomId);
            });
        }
        cell.style.position='relative';
        cell.appendChild(badge);
    });
    
    // Setup drag-drop for newly created badges
    setupBadgeDragDrop();
}

function showSlotAvailabilityModal(groupId, groupName, subjectName, teacherId, day, slot, status, conflicts, availableRooms, currentRoomId) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let contentHtml = '';
    let headerClass = 'bg-info';
    let title = 'Langelio informacija';
    
    // Map day codes to Lithuanian names
    const dayNames = {
        'Mon': 'Pirmadienis',
        'Tue': 'Antradienis', 
        'Wed': 'Trečiadienis',
        'Thu': 'Ketvirtadienis',
        'Fri': 'Penktadienis'
    };
    const dayName = dayNames[day] || day;
    
    if (status === 'free') {
        headerClass = 'bg-success';
        title = 'Laisvas langelis';
        contentHtml = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Šis langelis visiškai laisvas!
            </div>
            <p><strong>Diena:</strong> ${dayName}</p>
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
            <p><strong>Diena:</strong> ${dayName}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
            
            <h6 class="mt-3">Konfliktai (spauskite detalesnei informacijai):</h6>
            ${conflictsHtml}
            
            <div class="alert alert-warning mt-3">
                <i class="bi bi-info-circle"></i> Negalima pridėti pamokos, nes mokiniai jau užimti kitoje pamokoje.
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
            <p><strong>Diena:</strong> ${dayName}</p>
            <p><strong>Pamoka:</strong> ${slot}</p>
            <p><strong>Grupė:</strong> ${groupName}</p>
            <p><strong>Dalykas:</strong> ${subjectName}</p>
            
            <h6 class="mt-3">Konfliktai (spauskite detalesnei informacijai):</h6>
            ${conflictsHtml}
            
            <h6 class="mt-3">Sprendimas - pasirinkite kitą kabinetą:</h6>
            <div class="mb-3">
                <select class="form-select" id="newRoomSelect">
                    <option value="">-- Pasirinkite kabinetą --</option>
                    ${availableRooms.map(r => '<option value="'+r.id+'" '+(r.id == currentRoomId ? 'selected' : '')+'>'+r.number+' '+(r.name || '')+'</option>').join('')}
                </select>
            </div>
            <div class="alert alert-info mb-3" id="roomSelectionHint">
                <i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą
            </div>
            <button type="button" class="btn btn-warning" id="addRoomButton" disabled>
                <i class="bi bi-plus-circle"></i> Pridėti
            </button>
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
                    ` : ''}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Add real-time room availability checking for room conflicts
    console.log('Modal status:', status);
    if (status === 'room_conflict') {
        console.log('Setting up room conflict listeners...');
        const roomSelect = modal.querySelector('#newRoomSelect');
        const hint = modal.querySelector('#roomSelectionHint');
        const addBtn = modal.querySelector('#addRoomButton');
        
        console.log('Elements found:', {roomSelect, hint, addBtn});
        
        if (!roomSelect || !hint || !addBtn) {
            console.error('Missing elements in modal!');
            modal.addEventListener('hidden.bs.modal', () => modal.remove());
            return;
        }
        
        let selectedRoomId = null;
        let roomAvailability = {};
        
        async function checkRoomAvailability(roomId) {
            if (!roomId) return;
            
            console.log('Checking room availability for:', roomId);
            
            hint.className = 'alert alert-info mb-3';
            hint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Tikrinama kabineto prieinamumas...';
            addBtn.disabled = true;
            
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
                console.log('Availability check response:', data);
                
                let hasRoomConflict = false;
                if (data.conflicts && Array.isArray(data.conflicts)) {
                    hasRoomConflict = data.conflicts.some(c => {
                        if (typeof c === 'object' && c !== null) return c.type === 'room';
                        if (typeof c === 'string') return c.includes('Kabinetas') || c.includes('užimtas');
                        return false;
                    });
                }
                
                const isAvailable = !hasRoomConflict;
                roomAvailability[roomId] = isAvailable;
                console.log('Room available:', isAvailable);
                
                if (isAvailable) {
                    hint.className = 'alert alert-success mb-3';
                    hint.innerHTML = '<i class="bi bi-check-circle"></i> Pasirinktas kabinetas laisvas. Galite pridėti.';
                    addBtn.disabled = false;
                } else {
                    hint.className = 'alert alert-danger mb-3';
                    hint.innerHTML = '<i class="bi bi-x-circle"></i> Pasirinktas kabinetas užimtas. Pasirinkite kitą.';
                    addBtn.disabled = true;
                }
            } catch (err) {
                console.error('Error checking availability:', err);
                hint.className = 'alert alert-secondary mb-3';
                hint.innerHTML = '<i class="bi bi-question-circle"></i> Nepavyko patikrinti. Bandykite dar kartą.';
                roomAvailability[roomId] = false;
                addBtn.disabled = true;
            }
        }
        
        roomSelect.addEventListener('change', async (e) => {
            console.log('Room select changed:', e.target.value);
            selectedRoomId = e.target.value ? parseInt(e.target.value) : null;
            
            if (!selectedRoomId) {
                hint.className = 'alert alert-info mb-3';
                hint.innerHTML = '<i class="bi bi-info-circle"></i> Pasirinkite kabinetą, kad patikrintume jo prieinamumą';
                addBtn.disabled = true;
                return;
            }
            
            await checkRoomAvailability(selectedRoomId);
        });
        
        addBtn.addEventListener('click', async () => {
            console.log('Add button clicked, selectedRoomId:', selectedRoomId, 'available:', roomAvailability[selectedRoomId]);
            if (!selectedRoomId || roomAvailability[selectedRoomId] !== true) return;
            
            // Close current modal
            bsModal.hide();
            
            // Open new modal for group copy creation
            showGroupCopyModal(groupId, groupName, subjectName, teacherId, day, slot, selectedRoomId);
        });
    }
    
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
                                <i class="bi bi-info-circle"></i> Bus sukurta nauja grupė su pasirinktu kabinetu vienai pamokai. 
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

async function addLessonToSlot(groupId, teacherId, day, slot, tempRoomId) {
    try {
        const response = await fetch(`<?php echo e(route('schools.timetables.manual-slot', [$school, $timetable])); ?>`, {
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
                temp_room_id: tempRoomId
            })
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            showErrorModal('Klaida', data.message || data.error || 'Nepavyko pridėti pamokos');
            return;
        }
        // Update the target cell UI without reloading
        const cell = document.querySelector(`#teachersGrid td[data-day='${day}'][data-slot='${slot}'][data-teacher-id='${teacherId}']`);
        if (cell && data.html) {
            const tooltipHtml = `<div class=\"tt-inner\">`
              + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
              + `<div class=\"tt-divider\"></div>`
              + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${data.html.group}</span></div>`
              + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${data.html.subject ?? '—'}</span></div>`
                      + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${data.html.room_number ?? '—'}</span></div>`
              + `<div class=\"tt-row\"><i class=\"bi bi-person-badge tt-ico\"></i><span class=\"tt-val\">${data.html.teacher_name ?? '—'}</span></div>`
              + `</div>`;
            const b64 = btoa(unescape(encodeURIComponent(tooltipHtml)));
            cell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${b64}\" draggable=\"true\"
                    data-kind=\"scheduled\"
                    data-slot-id=\"${data.html.slot_id}\"
                    data-group-id=\"${groupId}\"
                    data-teacher-id=\"${data.html.teacher_id}\"
                    data-group-name=\"${data.html.group}\"
                    data-subject-name=\"${data.html.subject ?? ''}\"
            >${data.html.group}</span>`;
            // re-init drag
            if (window.bootstrap) {
                const badge = cell.querySelector('.tt-trigger');
                initBadgeDrag(badge);
            }
        }
        // Update unscheduled list counts if backend provided
        if (typeof updateUnscheduledList === 'function' && data.group_id !== undefined && data.remaining_lessons !== undefined && data.group_data) {
            try { updateUnscheduledList(data.group_id, data.remaining_lessons, data.group_data); } catch(e) {}
        }
        flashMessage('Pamoka pridėta', 'success');
    } catch (err) {
        console.error('Error adding lesson:', err);
        showErrorModal('Klaida', 'Klaida siunčiant užklausą');
    }
}

function addLessonWithNewRoom(groupId, teacherId, day, slot, selectElementId) {
    const selectElement = document.getElementById(selectElementId);
    const newRoomId = selectElement?.value;
    
    if (!newRoomId) {
        alert('Prašome pasirinkti kabinetą');
        return;
    }
    
    addLessonToSlot(groupId, teacherId, day, slot, newRoomId);
}

function showConflictDetails(groupName, subjectName, teacherName, students, type) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    
    let contentHtml = '';
    let headerClass = 'bg-info';
    let title = 'Konflikto detalės';
    
    if (type === 'students' && students && students.length > 0) {
        headerClass = 'bg-danger';
        title = 'Užimti mokiniai';
        const sortedStudents = [...students].sort((a, b) => a.localeCompare(b, 'lt'));
        contentHtml = `
            <div class="alert alert-danger">
                <i class="bi bi-people"></i> Šie mokiniai jau turi pamoką šiuo metu:
            </div>
            <ul class="list-group">
                ${sortedStudents.map(s => '<li class="list-group-item">'+s+'</li>').join('')}
            </ul>
        `;
    } else if (type === 'room') {
        headerClass = 'bg-warning';
        title = 'Kabineto konfliktas';
        contentHtml = `
            <div class="alert alert-warning">
                <i class="bi bi-door-closed"></i> Kabinetas jau užimtas kitos pamokos:
            </div>
            <p><strong>Grupė:</strong> ${groupName || 'Nežinoma'}</p>
            <p><strong>Dalykas:</strong> ${subjectName || 'Nežinomas'}</p>
            <p><strong>Mokytojas:</strong> ${teacherName || 'Nežinomas'}</p>
        `;
    }
    
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
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

// Initialize Bootstrap tooltips (requires Bootstrap JS loaded globally)
document.addEventListener('DOMContentLoaded', function() {
    if (window.bootstrap) {
        function b64ToUtf8(b64){
            try { return decodeURIComponent(Array.prototype.map.call(atob(b64), c => '%' + ('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')); } catch(e){ return ''; }
        }
        document.querySelectorAll('[data-tooltip-b64]').forEach(function(el){
            const b64 = el.getAttribute('data-tooltip-b64');
            const html = b64ToUtf8(b64);
            if(!html) return;
            // Accessibility fallback only (avoid native browser tooltip)
            el.setAttribute('aria-label', html.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim());
            // Avoid duplicate tooltips
            const existing = bootstrap.Tooltip.getInstance(el);
            if (!existing) {
                new bootstrap.Tooltip(el, { title: html, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
            }
        });
    }
});
</script>

<style>
/* Sticky column for # and teacher name */
.sticky-col {
    position: sticky !important;
    left: 0 !important;
    z-index: 10 !important;
    background-color: #212529 !important;
    color: white !important;
}

.sticky-col-name {
    position: sticky !important;
    left: 48px !important;
    z-index: 10 !important;
    background-color: #212529 !important;
    color: white !important;
}

/* Body cells sticky background */
tbody .sticky-col {
    background-color: #fff !important;
    color: #212529 !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.15) !important;
}

tbody .sticky-col-name {
    background-color: #fff !important;
    color: #212529 !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.15) !important;
}

/* Lesson column sizing */
.lesson-col { 
    min-width: 140px;
    white-space: normal;
}

/* Let SimpleBar handle scroll; ensure content can overflow horizontally */
#timetableContainer { display: block; width: 100%; max-width: 100%; }
#teachersGrid { width: max-content; min-width: 100%; }

/* Day separators */
.day-break { 
    border-right: 3px solid #adb5bd !important; 
}

/* Compact table */
#teachersGrid { 
    font-size: 0.9rem;
}

#teachersGrid th {
    padding: 0.5rem 0.3rem;
    font-weight: 600;

/* Tooltip custom styling */
.tooltip .tooltip-inner { 
    max-width: 260px; 
    text-align: left; 
    background: #1f2937; 
    color: #f8f9fa; 
    padding: 0.6rem 0.7rem; 
    border-radius: 8px; 
    font-size: 0.75rem; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}
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
    z-index: 2000; /* ensure above grid badges and SimpleBar layers */
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
}

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

#teachersGrid td {
    padding: 0.3rem;
}

/* Scrollbar */
#timetableContainer::-webkit-scrollbar { 
    height: 10px;
    width: 10px;
}

#timetableContainer::-webkit-scrollbar-thumb { 
    background: #adb5bd;
    border-radius: 6px;
}

#timetableContainer::-webkit-scrollbar-track {
    background: #f1f1f1;
}

/* Fullscreen adjustments */
#timetableCard.fullscreen-active {
    width: 100vw;
    height: 100vh;
    max-height: 100vh !important;
    margin: 0;
    border-radius: 0;
    box-shadow: none;
}
#timetableCard.fullscreen-active .card-body { height: 100%; }
#timetableCard.fullscreen-active #timetableContainer { width: 100%; }

/* Drag & drop styles */
.unscheduled-item {
    cursor: move;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s;
}
.unscheduled-item:hover {
    background-color: #f8f9fa;
}
.unscheduled-item.dragging {
    opacity: 0.5;
}
.unscheduled-item.active-group { background-color:#e7f1ff; outline:2px solid #0d6efd; }
.unscheduled-title { font-weight:600; color:#212529; }
.unscheduled-meta { font-size:0.85rem; color:#6c757d; display:flex; gap:8px; align-items:baseline; }
.unscheduled-subject { color:#198754; font-weight:500; }
.unscheduled-teacher::before { content:'•'; margin:0 4px; }
.unscheduled-room { color:#0d6efd; }
.unscheduled-room::before { content:'•'; margin:0 4px; }
.drop-target {
    transition: background-color 0.2s;
}
.drop-target.drop-hover {
    background-color: #d1e7dd !important;
    border: 2px dashed #198754 !important;
}

</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/teachers-view.blade.php ENDPATH**/ ?>