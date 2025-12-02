@extends('layouts.admin')

@section('content')
<div style="width: 100%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-calendar3"></i> {{ $timetable->name }} — Mokytojų tvarkaraštis</h2>
        <div class="btn-group">
            <a href="{{ route('schools.timetables.show', [$school, $timetable]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Atgal
            </a>
            <button id="btnFullscreen" class="btn btn-primary" type="button">
                <i class="bi bi-arrows-fullscreen"></i> Visas ekranas
            </button>
        </div>
    </div>

    <div class="alert alert-info mb-2" role="alert">
        <i class="bi bi-info-circle me-1 align-middle fs-16"></i>
        Čia galite peržiūrėti mokytojų tvarkaraštį pagal dienas ir pamokas. Slankiokite lentelę horizontaliai ir vertikaliai.
    </div>

    <div class="card mb-2">
        <div class="card-body p-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted fw-bold">Legenda:</span>
                <span class="badge" style="background-color: #198754; color: white;">Dalykas</span>
                <span class="badge bg-secondary">Grupė</span>
                <span class="badge bg-dark">Kabinetas</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card" id="timetableCard">
                <div class="card-body p-0">
                    <div id="timetableContainer" data-simplebar data-simplebar-auto-hide="false" style="max-height: calc(100vh - 280px); position: relative; width: 100%;">
                        <table class="table table-hover table-bordered align-middle mb-0" id="teachersGrid">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="text-center align-middle sticky-col" style="width:48px;">#</th>
                            <th rowspan="2" class="text-center align-middle sticky-col-name" style="width:220px;">Mokytojas</th>
                            @foreach($days as $code => $label)
                                <th colspan="{{ $dayCaps[$code] }}" class="text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($days as $code => $label)
                                @for($i = 1; $i <= $dayCaps[$code]; $i++)
                                    @php $isLast = $i === $dayCaps[$code]; @endphp
                                    <th class="text-center lesson-col {{ $isLast ? 'day-break' : '' }}">{{ $i }}</th>
                                @endfor
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $index => $teacher)
                            <tr>
                                <td class="text-center sticky-col">{{ $index + 1 }}</td>
                                <td class="sticky-col-name"><strong>
                                    <a href="{{ route('schools.timetables.teacher', [$school, $timetable, $teacher->id]) }}" class="link-dark text-decoration-underline">
                                        {{ $teacher->full_name }}
                                    </a>
                                </strong></td>
                                @foreach($days as $code => $label)
                                    @for($l = 1; $l <= $dayCaps[$code]; $l++)
                                        @php
                                            $cell = $slots[$teacher->id][$code][$l] ?? null;
                                            $isLast = $l === $dayCaps[$code];
                                        @endphp
                                        <td class="text-center lesson-col drop-target {{ $isLast ? 'day-break' : '' }}" style="padding:0.3rem;" data-day="{{ $code }}" data-slot="{{ $l }}" data-teacher-id="{{ $teacher->id }}">
                                            @if($cell)
                                                @php
                                                    $subject = $cell['subject'] ?? '—';
                                                    $roomNumber = $cell['room_number'] ?? null;
                                                    $roomName = $cell['room_name'] ?? null;
                                                    $roomDisplay = $roomNumber ? ($roomNumber . ($roomName ? ' ' . $roomName : '')) : '—';
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
                                                @endphp
                                                <span class="badge bg-secondary tt-trigger lesson-badge" style="font-size:0.75rem; cursor:pointer;" data-tooltip-b64="{{ $tooltipB64 }}" draggable="true"
                                                      data-kind="scheduled"
                                                      data-slot-id="{{ $cell['slot_id'] }}"
                                                      data-group-id="{{ $cell['group_id'] }}"
                                                      data-teacher-id="{{ $cell['teacher_id'] }}"
                                                      data-group-name="{{ $cell['group'] }}"
                                                      data-subject-name="{{ $cell['subject'] ?? '' }}"
                                                      data-room-number="{{ $roomNumber ?? '' }}"
                                                      data-room-name="{{ $roomName ?? '' }}"
                                                      data-teacher-full-name="{{ $teacherName }}"
                                                      data-day-label="{{ $dayLabel }}"
                                                      data-lesson-nr="{{ $lessonNr }}"
                                                      onclick="showLessonDetails(this, event)"
                                                >{{ $cell['group'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endfor
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100" id="unscheduledPanel">
                <div class="card-header p-2"><strong>Nesuplanuotos grupės</strong></div>
                <div class="card-body p-2" style="max-height: calc(100vh - 280px); overflow:auto;">
                    @forelse($unscheduled as $u)
                        <div class="unscheduled-item mb-1" draggable="true"
                             data-kind="unscheduled"
                             data-group-id="{{ $u['group_id'] }}"
                             data-group-name="{{ $u['group_name'] }}"
                             data-subject-name="{{ $u['subject_name'] }}"
                             data-teacher-id="{{ $u['teacher_login_key_id'] ?? '' }}"
                             data-teacher-name="{{ $u['teacher_name'] ?? '' }}"
                             data-remaining="{{ $u['remaining_lessons'] }}">
                            <span class="badge bg-secondary me-1">{{ $u['group_name'] }}</span>
                            <span class="badge bg-success">{{ $u['subject_name'] }}</span>
                            @if(!empty($u['teacher_name']))
                            <span class="badge bg-dark ms-1">{{ $u['teacher_name'] }}</span>
                            @endif
                            <small class="text-muted ms-1">({{ $u['remaining_lessons'] }} liko)</small>
                        </div>
                    @empty
                        <span class="text-muted small">Visos grupės suplanuotos</span>
                    @endforelse
                </div>
                <div class="card-footer p-1 small text-muted">Tempkite ant mokytojo pamokos langelio</div>
            </div>
        </div>
    </div>
</div>

<!-- Lesson Details Modal -->
<div class="modal fade" id="lessonDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i> Pamokos informacija
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="bi bi-clock"></i> Laikas</h6>
                        <p id="modal-time" class="text-muted"></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-person-badge"></i> Mokytojas</h6>
                        <p id="modal-teacher" class="text-muted"></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="bi bi-collection-fill"></i> Grupė</h6>
                        <p id="modal-group" class="text-muted"></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-book-half"></i> Dalykas</h6>
                        <p id="modal-subject" class="text-muted"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6><i class="bi bi-door-closed"></i> Kabinetas</h6>
                        <p id="modal-room" class="text-muted"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6><i class="bi bi-people"></i> Mokiniai</h6>
                        <div id="modal-students-loading" class="text-muted">
                            <span class="spinner-border spinner-border-sm me-2"></span>Kraunama...
                        </div>
                        <div id="modal-students" class="d-none"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h6><i class="bi bi-exclamation-triangle"></i> Konfliktai</h6>
                        <div id="modal-conflicts-loading" class="text-muted">
                            <span class="spinner-border spinner-border-sm me-2"></span>Kraunama...
                        </div>
                        <div id="modal-conflicts" class="d-none"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
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
            card.classList.add('fullscreen-active');
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

// Drag & drop manual scheduling
document.addEventListener('DOMContentLoaded', function(){
    const UNSCHEDULED_SELECTOR = '.unscheduled-item';
    let dragged = null;
    let draggedKind = null; // 'unscheduled' | 'scheduled'
    
    document.querySelectorAll(UNSCHEDULED_SELECTOR).forEach(el => {
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
    // Make existing scheduled badges draggable
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
    }
    document.querySelectorAll('.tt-trigger[draggable="true"]').forEach(initBadgeDrag);
    
    document.querySelectorAll('.drop-target').forEach(cell => {
        cell.addEventListener('dragover', e => {
            if (!dragged) return;
            const rowTeacherId = String(cell.dataset.teacherId || '');
            let canDrop = true;
            if (draggedKind === 'unscheduled') {
                const itemTeacherId = String(dragged.dataset.teacherId || '');
                canDrop = !!itemTeacherId && itemTeacherId === rowTeacherId;
            } else if (draggedKind === 'scheduled') {
                const itemTeacherId = String(dragged.dataset.teacherId || '');
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
            if (!dragged) return;
            
            const rowTeacherId = cell.dataset.teacherId;
            const teacherId = cell.dataset.teacherId;
            const day = cell.dataset.day;
            const slot = cell.dataset.slot;
            if (draggedKind === 'unscheduled') {
                const groupId = dragged.dataset.groupId;
                const groupName = dragged.dataset.groupName;
                const subjectName = dragged.dataset.subjectName;
                // Check conflicts before saving
                const conflicts = await checkConflicts(groupId, teacherId, day, slot);
                // Show confirmation dialog with conflict info
                const confirmation = await showConfirmDialog(groupName, subjectName, day, slot, conflicts, groupId);
                if (!confirmation) {
                    return; // User cancelled
                }
                
                // Determine if force is needed
                const forceAdd = confirmation === 'force';
                
                try {
                    const resp = await fetch(`{{ route('schools.timetables.manual-slot', [$school, $timetable]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ group_id: groupId, teacher_id: teacherId, day: day, slot: slot, force: forceAdd })
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.success) {
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
                    if (window.bootstrap) {
                      const decoded = tooltipHtml;
                      const badge = cell.querySelector('.tt-trigger');
                      new bootstrap.Tooltip(badge, { title: decoded, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
                                            initBadgeDrag(badge);
                    }
                    // Update remaining count on dragged item
                    const rem = parseInt(dragged.dataset.remaining,10)-1;
                    if (rem <= 0) { dragged.remove(); } else { dragged.dataset.remaining = rem; dragged.querySelector('small').textContent = `(${rem} liko)`; }
                    flashMessage('Pamoka sėkmingai įtraukta', 'success');
                } catch(err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            } else if (draggedKind === 'scheduled') {
                // Move existing slot
                const slotId = dragged.dataset.slotId;
                const groupName = dragged.dataset.groupName || 'Grupė';
                const subjectName = dragged.dataset.subjectName || '';
                const groupId = dragged.dataset.groupId;
                const originalCell = dragged.closest('td');
                
                try {
                    // Try to move (this will check for swap needs)
                    const resp = await fetch(`{{ route('schools.timetables.move-slot', [$school, $timetable]) }}`, {
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
                        const swapResp = await fetch(`{{ route('schools.timetables.move-slot', [$school, $timetable]) }}`, {
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
                              + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${swapData.swappedHtml.room ?? '—'}</span></div>`
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
                                if (window.bootstrap) {
                                    const swappedBadge = originalCell.querySelector('.tt-trigger');
                                    new bootstrap.Tooltip(swappedBadge, { title: swappedTooltip, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
                                    initBadgeDrag(swappedBadge);
                                }
                            }
                        }
                        
                        // Update target cell
                        const tooltipHtml = `<div class=\"tt-inner\">`
                          + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
                          + `<div class=\"tt-divider\"></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${swapData.html.group}</span></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${swapData.html.subject ?? '—'}</span></div>`
                          + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${swapData.html.room ?? '—'}</span></div>`
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
                        if (window.bootstrap) {
                            const badge = cell.querySelector('.tt-trigger');
                            new bootstrap.Tooltip(badge, { title: tooltipHtml, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
                            initBadgeDrag(badge);
                        }
                        flashMessage('Pamokos sėkmingai sukeistos', 'success');
                        return;
                    }
                    
                    if (!resp.ok || !data.success) {
                        // Check if error is conflict-related - if so, offer force option
                        if (data.error && (
                            data.error.includes('užimtas') || 
                            data.error.includes('Užimti') ||
                            data.error.includes('konflikt')
                        )) {
                            // Show confirmation with force option
                            const conflictData = {
                                hasConflicts: true,
                                conflicts: [data.error]
                            };
                            const confirmation = await showMoveConfirmDialog(groupName, subjectName, day, slot, conflictData);
                            if (confirmation === 'force') {
                                // Retry with force flag
                                const forceResp = await fetch(`{{ route('schools.timetables.move-slot', [$school, $timetable]) }}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ slot_id: slotId, teacher_id: teacherId, day: day, slot: slot, swap: false, force: true })
                                });
                                const forceData = await forceResp.json();
                                
                                if (!forceResp.ok || !forceData.success) {
                                    showErrorModal('Klaida', forceData.error || 'Nepavyko perkelti pamokos');
                                    return;
                                }
                                
                                // Update UI after force move
                                if (originalCell) originalCell.innerHTML = '<span class="text-muted">—</span>';
                                const tooltipHtml = `<div class=\"tt-inner\">`
                                  + `<div class=\"tt-row tt-row-head\"><i class=\"bi bi-clock-history tt-ico\"></i><span class=\"tt-val\">${day} • ${slot} pamoka</span></div>`
                                  + `<div class=\"tt-divider\"></div>`
                                  + `<div class=\"tt-row\"><i class=\"bi bi-collection-fill tt-ico\"></i><span class=\"tt-val\">${forceData.html.group}</span></div>`
                                  + `<div class=\"tt-row\"><i class=\"bi bi-book-half tt-ico\"></i><span class=\"tt-val\">${forceData.html.subject ?? '—'}</span></div>`
                                  + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${forceData.html.room ?? '—'}</span></div>`
                                  + `</div>`;
                                const b64 = btoa(unescape(encodeURIComponent(tooltipHtml)));
                                cell.innerHTML = `<span class=\"badge bg-secondary tt-trigger\" style=\"font-size:0.75rem; cursor:move;\" data-tooltip-b64=\"${b64}\" draggable=\"true\"
                                        data-kind=\"scheduled\"
                                        data-slot-id=\"${forceData.html.slot_id ?? ''}\"
                                        data-group-id=\"${groupId}\"
                                        data-teacher-id=\"${teacherId}\"
                                        data-group-name=\"${forceData.html.group}\"
                                        data-subject-name=\"${forceData.html.subject ?? ''}\"
                                >${forceData.html.group}</span>`;
                                if (window.bootstrap) {
                                    const badge = cell.querySelector('.tt-trigger');
                                    new bootstrap.Tooltip(badge, { title: tooltipHtml, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
                                    initBadgeDrag(badge);
                                }
                                flashMessage('Pamoka perkelta (su konfliktais)', 'warning');
                                return;
                            } else {
                                return; // User cancelled
                            }
                        }
                        
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
                      + `<div class=\"tt-row\"><i class=\"bi bi-door-closed tt-ico\"></i><span class=\"tt-val\">${data.html.room ?? '—'}</span></div>`
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
                    if (window.bootstrap) {
                        const badge = cell.querySelector('.tt-trigger');
                        new bootstrap.Tooltip(badge, { title: tooltipHtml, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
                        initBadgeDrag(badge);
                    }
                    flashMessage('Pamoka perkelta', 'success');
                } catch(err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            }
        });
    });
    
    async function checkConflicts(groupId, teacherId, day, slot) {
        try {
            const resp = await fetch(`{{ route('schools.timetables.check-conflict', [$school, $timetable]) }}`, {
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
            
            // Process conflicts to handle "Užimti mokiniai:" specially
            let conflictsHtml = '';
            if (conflictData.hasConflicts && conflictData.conflicts) {
                conflictsHtml = conflictData.conflicts.map(c => {
                    if (typeof c === 'string' && c.startsWith('Užimti mokiniai:')) {
                        const studentsPart = c.substring('Užimti mokiniai:'.length).trim();
                        const students = studentsPart.split(',').map(s => s.trim()).filter(s => s.length > 0);
                        students.sort((a, b) => a.localeCompare(b, 'lt'));
                        return '<li><strong>Užimti mokiniai:</strong><ul class="mt-1">' + students.map(s => `<li>${s}</li>`).join('') + '</ul></li>';
                    }
                    return `<li>${c}</li>`;
                }).join('');
            }
            
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header ${conflictData.hasConflicts ? 'bg-warning text-dark' : 'bg-primary text-white'}">
                            <h5 class="modal-title">
                                <i class="bi ${conflictData.hasConflicts ? 'bi-exclamation-triangle' : 'bi-check-circle'}"></i>
                                ${conflictData.hasConflicts ? 'Aptikti konfliktai' : 'Patvirtinti pamokos pridėjimą'}
                            </h5>
                            <button type="button" class="btn-close ${conflictData.hasConflicts ? '' : 'btn-close-white'}" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong>Grupė:</strong> ${groupName} (${subjectName})</p>
                            <p class="mb-3"><strong>Laikas:</strong> ${day}, ${slot} pamoka</p>
                            ${conflictData.hasConflicts ? `
                                <div class="alert alert-warning mb-3">
                                    <strong><i class="bi bi-exclamation-triangle-fill"></i> Aptikti šie konfliktai:</strong>
                                    <ul class="mb-0 mt-2">
                                        ${conflictsHtml}
                                    </ul>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle-fill"></i> <strong>Dėmesio!</strong> Jei pridėsite šią pamoką, bus sukurtas tvarkaraščio konfliktas. Mokytojas, mokiniai arba kabinetas gali būti užimti tuo pačiu metu.
                                </div>
                            ` : `
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle-fill"></i> Konfliktų nerasta. Ar norite pridėti šią pamoką?
                                </div>
                            `}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            ${conflictData.hasConflicts ? `
                                <button type="button" class="btn btn-warning" id="confirmAddForce">
                                    <i class="bi bi-exclamation-triangle"></i> Vis tiek pridėti
                                </button>
                            ` : `
                                <button type="button" class="btn btn-primary" id="confirmAdd">
                                    <i class="bi bi-plus-circle"></i> Pridėti
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            if (conflictData.hasConflicts) {
                modal.querySelector('#confirmAddForce').addEventListener('click', () => {
                    bsModal.hide();
                    resolve('force'); // Return 'force' to indicate override
                });
            } else {
                modal.querySelector('#confirmAdd').addEventListener('click', () => {
                    bsModal.hide();
                    resolve(true);
                });
            }
            
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
                if (!modal._resolved) resolve(false);
            });
            
            // Mark as resolved when button clicked
            modal.addEventListener('hide.bs.modal', () => {
                modal._resolved = true;
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

    function showMoveConfirmDialog(groupName, subjectName, day, slot, conflictData) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            
            // Process conflicts
            let conflictsHtml = '';
            if (conflictData.hasConflicts && conflictData.conflicts) {
                conflictsHtml = conflictData.conflicts.map(c => {
                    if (typeof c === 'string' && c.startsWith('Užimti mokiniai:')) {
                        const studentsPart = c.substring('Užimti mokiniai:'.length).trim();
                        const students = studentsPart.split(',').map(s => s.trim()).filter(s => s.length > 0);
                        students.sort((a, b) => a.localeCompare(b, 'lt'));
                        return '<li><strong>Užimti mokiniai:</strong><ul class="mt-1">' + students.map(s => `<li>${s}</li>`).join('') + '</ul></li>';
                    }
                    return `<li>${c}</li>`;
                }).join('');
            }
            
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle"></i> Aptikti konfliktai
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong>Grupė:</strong> ${groupName} (${subjectName})</p>
                            <p class="mb-3"><strong>Norimas laikas:</strong> ${day}, ${slot} pamoka</p>
                            <div class="alert alert-warning mb-3">
                                <strong><i class="bi bi-exclamation-triangle-fill"></i> Aptikti šie konfliktai:</strong>
                                <ul class="mb-0 mt-2">
                                    ${conflictsHtml}
                                </ul>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle-fill"></i> <strong>Dėmesio!</strong> Jei perkelsite šią pamoką, bus sukurtas tvarkaraščio konfliktas.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="button" class="btn btn-warning" id="confirmMoveForce">
                                <i class="bi bi-exclamation-triangle"></i> Vis tiek perkelti
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.querySelector('#confirmMoveForce').addEventListener('click', () => {
                bsModal.hide();
                resolve('force');
            });
            
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
                if (!modal._resolved) resolve(false);
            });
            
            modal.addEventListener('hide.bs.modal', () => {
                modal._resolved = true;
            });
        });
    }

    function showErrorModal(title, message) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        
        let bodyHtml;
        if (Array.isArray(message)) {
            bodyHtml = `<ul class="mb-0">${message.map(m=>`<li>${m}</li>`).join('')}</ul>`;
        } else if (typeof message === 'string' && message.startsWith('Užimti mokiniai:')) {
            // Parse student conflicts
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
        modal.addEventListener('hidden.bs.modal', () => modal.remove());
    }
    
});

function flashMessage(msg,type){
    let box=document.getElementById('flashBox');
    if(!box){ box=document.createElement('div'); box.id='flashBox'; box.style.position='fixed'; box.style.top='10px'; box.style.right='10px'; box.style.zIndex='9999'; document.body.appendChild(box); }
    const el=document.createElement('div'); el.className=`alert alert-${type} py-1 px-2 mb-2`; el.textContent=msg; box.appendChild(el); setTimeout(()=>{ el.remove(); if(!box.children.length) box.remove(); },3000);
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
        const resp = await fetch(`{{ route('schools.timetables.groups.edit-data', [$school, $timetable, ':groupId']) }}`.replace(':groupId', groupId));
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
                const updateResp = await fetch(`{{ route('schools.timetables.groups.update', [$school, $timetable, ':groupId']) }}`.replace(':groupId', groupId), {
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
            // Fallback plain title
            el.setAttribute('title', html.replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim());
            new bootstrap.Tooltip(el, { title: html, html: true, sanitize: false, placement: 'top', trigger: 'hover focus', delay:{show:120, hide:60} });
        });
    }
});

// Show lesson details in modal
function showLessonDetails(badge, event) {
    event.stopPropagation(); // Prevent drag when clicking
    
    const slotId = badge.dataset.slotId;
    const groupName = badge.dataset.groupName;
    const subjectName = badge.dataset.subjectName;
    const roomNumber = badge.dataset.roomNumber;
    const roomName = badge.dataset.roomName;
    const teacherName = badge.dataset.teacherFullName;
    const dayLabel = badge.dataset.dayLabel;
    const lessonNr = badge.dataset.lessonNr;
    const groupId = badge.dataset.groupId;
    
    // Populate modal basic info
    document.getElementById('modal-time').textContent = `${dayLabel}, ${lessonNr} pamoka`;
    document.getElementById('modal-teacher').textContent = teacherName;
    document.getElementById('modal-group').textContent = groupName;
    document.getElementById('modal-subject').textContent = subjectName;
    document.getElementById('modal-room').textContent = roomNumber ? `${roomNumber} ${roomName || ''}` : 'Nenurodytas';
    
    // Show loading states
    document.getElementById('modal-students-loading').classList.remove('d-none');
    document.getElementById('modal-students').classList.add('d-none');
    document.getElementById('modal-conflicts-loading').classList.remove('d-none');
    document.getElementById('modal-conflicts').classList.add('d-none');
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('lessonDetailsModal'));
    modal.show();
    
    // Load students
    fetch(`{{ route('schools.timetables.groups.show', [$school, $timetable, '__GROUP_ID__']) }}`.replace('__GROUP_ID__', groupId))
        .then(response => response.json())
        .then(data => {
            document.getElementById('modal-students-loading').classList.add('d-none');
            const studentsDiv = document.getElementById('modal-students');
            studentsDiv.classList.remove('d-none');
            
            if (data.students && data.students.length > 0) {
                studentsDiv.innerHTML = '<div class="list-group">' + 
                    data.students.map(s => `<div class="list-group-item"><i class="bi bi-person"></i> ${s.full_name}</div>`).join('') +
                    '</div>';
            } else {
                studentsDiv.innerHTML = '<p class="text-muted">Nėra priskirtų mokinių</p>';
            }
        })
        .catch(err => {
            document.getElementById('modal-students-loading').classList.add('d-none');
            document.getElementById('modal-students').classList.remove('d-none');
            document.getElementById('modal-students').innerHTML = '<p class="text-danger">Klaida kraunant mokinius</p>';
        });
    
    // Load conflicts
    const day = badge.closest('[data-day]').dataset.day;
    const slot = badge.closest('[data-slot]').dataset.slot;
    
    fetch(`{{ route('schools.timetables.check-conflict', [$school, $timetable]) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            group_id: groupId,
            day_of_week: day,
            lesson_number: slot
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('modal-conflicts-loading').classList.add('d-none');
        const conflictsDiv = document.getElementById('modal-conflicts');
        conflictsDiv.classList.remove('d-none');
        
        if (data.conflicts && data.conflicts.length > 0) {
            conflictsDiv.innerHTML = '<div class="alert alert-warning">' +
                data.conflicts.map(c => `<div><i class="bi bi-exclamation-triangle"></i> ${c}</div>`).join('') +
                '</div>';
        } else {
            conflictsDiv.innerHTML = '<p class="text-success"><i class="bi bi-check-circle"></i> Konfliktų nėra</p>';
        }
    })
    .catch(err => {
        document.getElementById('modal-conflicts-loading').classList.add('d-none');
        document.getElementById('modal-conflicts').classList.remove('d-none');
        document.getElementById('modal-conflicts').innerHTML = '<p class="text-danger">Klaida tikrinant konfliktus</p>';
    });
}
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
.tt-inner { display: flex; flex-direction: column; gap: 0.5rem; }
.tt-row { display: flex; align-items: flex-start; gap: 0.65rem; line-height: 1.3; }
.tt-row-head { font-weight: 600; }
.tt-val { color: #f8f9fa; font-weight: 500; letter-spacing: .2px; }
.tt-ico { color: #10b981; font-size: 1.05rem; flex-shrink: 0; margin-right: 6px; margin-top: 2px; }
.tt-row .tt-val { display: inline-block; padding-top: 1px; }
.tt-divider { height:1px; background:#374151; margin:4px 0 2px 0; }
.tt-trigger { transition: transform .12s ease, box-shadow .12s; }
.tt-trigger:hover { transform: translateY(-2px); box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
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
.drop-target {
    transition: background-color 0.2s;
}
.drop-target.drop-hover {
    background-color: #d1e7dd !important;
    border: 2px dashed #198754 !important;
}

</style>
@endsection
