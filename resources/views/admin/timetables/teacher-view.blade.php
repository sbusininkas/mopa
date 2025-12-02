@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-person-badge"></i> {{ $teacher->full_name }} — Tvarkaraštis</h2>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="{{ route('schools.timetables.teachers-view', [$school, $timetable]) }}">
                <i class="bi bi-arrow-left"></i> Atgal į mokytojų sąrašą
            </a>
            <a class="btn btn-outline-primary" href="{{ route('schools.timetables.show', [$school, $timetable]) }}">
                <i class="bi bi-calendar3"></i> Tvarkaraščio nustatymai
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-9">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="teacherGrid" data-teacher-id="{{ $teacher->id }}">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:60px" class="text-center">#</th>
                                    @foreach($days as $code => $label)
                                        <th class="text-center">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($row=1; $row <= $maxRows; $row++)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $row }}</td>
                                        @foreach($days as $code => $label)
                                            @php $cell = $grid[$row][$code] ?? null; @endphp
                                            <td class="text-center lesson-col drop-target" style="min-width:220px" data-day="{{ $code }}" data-slot="{{ $row }}" data-teacher-id="{{ $teacher->id }}">
                                                @if($cell)
                                                    <span class="badge bg-secondary tt-trigger lesson-badge" style="font-size:0.75rem; cursor:pointer;" draggable="true"
                                                        data-kind="scheduled"
                                                        data-slot-id="{{ $cell['slot_id'] }}"
                                                        data-group-id="{{ $cell['group_id'] }}"
                                                        data-teacher-id="{{ $teacher->id }}"
                                                        data-group-name="{{ $cell['group'] }}"
                                                        data-subject-name="{{ $cell['subject'] ?? '' }}"
                                                        data-room-display="{{ $cell['room'] ?? '' }}"
                                                        data-teacher-full-name="{{ $teacher->full_name }}"
                                                        data-day-label="{{ $label }}"
                                                        data-lesson-nr="{{ $row }}"
                                                        onclick="showLessonDetails(this, event)"
                                                    >{{ $cell['group'] }}</span>
                                                    <button type="button" class="btn btn-outline-danger btn-sm ms-1 py-0 px-1" title="Iškelti į nesuplanuotas" onclick="unscheduleSlotTeacherView({{ $cell['slot_id'] }}, {{ $cell['group_id'] }}, {{ $teacher->id }}, this)"><i class="bi bi-box-arrow-down"></i></button>
                                                    <div class="mt-1 small">
                                                        <span class="badge bg-success">{{ $cell['subject'] ?? '—' }}</span>
                                                        @if($cell['room'])
                                                            <span class="badge bg-dark">{{ $cell['room'] }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
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
                    @forelse(($unscheduled ?? []) as $u)
                        <div class="unscheduled-item mb-1 d-flex align-items-center justify-content-between" draggable="true"
                             data-kind="unscheduled"
                             data-group-id="{{ $u['group_id'] }}"
                             data-group-name="{{ $u['group_name'] }}"
                             data-subject-name="{{ $u['subject_name'] }}"
                             data-teacher-id="{{ $u['teacher_login_key_id'] ?? '' }}"
                             data-teacher-name="{{ $u['teacher_name'] ?? '' }}"
                             data-remaining="{{ $u['remaining_lessons'] }}">
                            <div class="flex-grow-1">
                                <span class="badge bg-secondary me-1">{{ $u['group_name'] }}</span>
                                <span class="badge bg-success">{{ $u['subject_name'] }}</span>
                                <small class="text-muted ms-1">({{ $u['remaining_lessons'] }} liko)</small>
                            </div>
                            <div class="ms-2 btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info" title="Tikrinti galimus konfliktus"
                                    onclick="previewConflictsForGroup({{ $teacher->id }}, {{ $u['group_id'] }})">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" title="Išvalyti peržiūrą"
                                    onclick="clearConflictPreviews()">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <span class="text-muted small">Nėra neužpildytų pamokų šiam mokytojui</span>
                    @endforelse
                </div>
                <div class="card-footer p-1 small text-muted">Tempkite ant pasirinktų langelių</div>
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const grid = document.getElementById('teacherGrid');
    if (!grid) return;
    let dragged = null;
    let draggedKind = null;

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
                            if (originalCell) {
                                const swappedBadgeHtml = `<span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" draggable="true"
                                        data-kind="scheduled"
                                        data-slot-id="${swapData.swappedHtml.slot_id}"
                                        data-group-id="${swapData.swappedHtml.group_id ?? ''}"
                                        data-teacher-id="${teacherId}"
                                        data-group-name="${swapData.swappedHtml.group}"
                                        data-subject-name="${swapData.swappedHtml.subject ?? ''}">
                                    ${swapData.swappedHtml.group}
                                </span>
                                <div class="mt-1 small">
                                    <span class="badge bg-success">${swapData.swappedHtml.subject ?? '—'}</span>
                                    ${swapData.swappedHtml.room ? `<span class="badge bg-dark">${swapData.swappedHtml.room}</span>` : ''}
                                </div>`;
                                originalCell.innerHTML = swappedBadgeHtml;
                                initBadgeDrag(originalCell.querySelector('.tt-trigger'));
                            }
                        }
                        
                        // Update target cell
                        const badgeHtml = `<span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" draggable="true"
                                data-kind="scheduled"
                                data-slot-id="${swapData.html.slot_id ?? ''}"
                                data-group-id="${groupId}"
                                data-teacher-id="${teacherId}"
                                data-group-name="${swapData.html.group}"
                                data-subject-name="${swapData.html.subject ?? ''}">
                            ${swapData.html.group}
                        </span>
                        <div class="mt-1 small">
                            <span class="badge bg-success">${swapData.html.subject ?? '—'}</span>
                            ${swapData.html.room ? `<span class="badge bg-dark">${swapData.html.room}</span>` : ''}
                        </div>`;
                        cell.innerHTML = badgeHtml;
                        initBadgeDrag(cell.querySelector('.tt-trigger'));
                        flashMessage('Pamokos sėkmingai sukeistos', 'success');
                        return;
                    }
                    
                    if (!resp.ok || !data.success) {
                        showErrorModal('Klaida', data.error || 'Nepavyko perkelti pamokos');
                        return;
                    }
                    
                    // Simple move (no swap)
                    if (originalCell) originalCell.innerHTML = '<span class="text-muted">—</span>';
                    const badgeHtml = `<span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" draggable="true"
                            data-kind="scheduled"
                            data-slot-id="${data.html.slot_id ?? ''}"
                            data-group-id="${groupId}"
                            data-teacher-id="${teacherId}"
                            data-group-name="${data.html.group}"
                            data-subject-name="${data.html.subject ?? ''}">
                        ${data.html.group}
                    </span>
                    <div class="mt-1 small">
                        <span class="badge bg-success">${data.html.subject ?? '—'}</span>
                        ${data.html.room ? `<span class="badge bg-dark">${data.html.room}</span>` : ''}
                    </div>`;
                    cell.innerHTML = badgeHtml;
                    initBadgeDrag(cell.querySelector('.tt-trigger'));
                    flashMessage('Pamoka perkelta', 'success');
                } catch (err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            } else if (draggedKind === 'unscheduled') {
                const groupId = dragged.dataset.groupId;
                try {
                    const conflicts = await checkConflicts(groupId, teacherId, day, slot);
                    const confirmed = await showConfirmDialog(groupId, groupName, subjectName, day, slot, conflicts);
                    if (!confirmed) return;
                    if (conflicts.hasConflicts) return;
                    const resp = await fetch(`{{ route('schools.timetables.manual-slot', [$school, $timetable]) }}`, {
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
                        showErrorModal('Klaida', data.error || 'Nepavyko įtraukti pamokos');
                        return;
                    }
                    const badgeHtml = `<span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" draggable="true"
                                            data-kind="scheduled"
                                            data-slot-id="${data.html.slot_id}"
                                            data-group-id="${groupId}"
                                            data-teacher-id="${teacherId}"
                                            data-group-name="${data.html.group}"
                                            data-subject-name="${data.html.subject ?? ''}">
                                        ${data.html.group}
                                    </span>
                                    <div class="mt-1 small">
                                        <span class="badge bg-success">${data.html.subject ?? '—'}</span>
                                        ${data.html.room ? `<span class="badge bg-dark">${data.html.room}</span>` : ''}
                                    </div>`;
                    cell.innerHTML = badgeHtml;
                    initBadgeDrag(cell.querySelector('.tt-trigger'));
                    const rem = parseInt(dragged.dataset.remaining,10)-1;
                    if (rem <= 0) { dragged.remove(); } else { dragged.dataset.remaining = rem; const sm = dragged.querySelector('small'); if (sm) sm.textContent = `(${rem} liko)`; }
                    flashMessage('Pamoka sėkmingai įtraukta', 'success');
                } catch (err) {
                    showErrorModal('Klaida', 'Klaida siunčiant užklausą');
                }
            }
        });
    });

    // Conflict preview helpers
    window.clearConflictPreviews = function(){
        grid.querySelectorAll('.conflict-preview').forEach(el => el.remove());
        grid.querySelectorAll('.drop-target').forEach(c => c.classList.remove('preview-ok','preview-bad'));
    }

    window.previewConflictsForGroup = async function(teacherId, groupId){
        clearConflictPreviews();
        const cells = Array.from(grid.querySelectorAll('.drop-target'))
            .filter(c => String(c.dataset.teacherId || '') === String(teacherId));

        for(const cell of cells){
            // Skip cells already occupied
            if (cell.querySelector('.tt-trigger')) continue;
            const day = cell.dataset.day; const slot = cell.dataset.slot;
            // Show loading badge while checking
            const holder = document.createElement('div');
            holder.className = 'conflict-preview mt-1';
            holder.innerHTML = `<span class="badge bg-light text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Tikrinama</span>`;
            cell.appendChild(holder);
            try{
                const data = await checkConflicts(groupId, teacherId, day, slot);
                // Build badges
                let html = '';
                let title = '';
                let studentCount = 0; let roomFlag = false; let otherCount = 0;
                const list = Array.isArray(data.conflicts) ? data.conflicts : [];
                list.forEach(c => {
                    title += (title ? '\n' : '') + c;
                    const lc = String(c).toLowerCase();
                    if (lc.startsWith('užimti mokiniai:')){
                        const tail = c.substring('Užimti mokiniai:'.length).trim();
                        const arr = tail.split(',').map(s=>s.trim()).filter(Boolean);
                        studentCount = arr.length;
                    } else if (/kabin/i.test(lc)) {
                        roomFlag = true;
                    } else {
                        otherCount++;
                    }
                });

                if (data.hasConflicts){
                    const parts = [];
                    if (studentCount>0) parts.push(`<span class="badge bg-warning text-dark me-1" title="Užimti mokiniai: ${studentCount}"><i class="bi bi-people-fill"></i> ${studentCount}</span>`);
                    if (roomFlag) parts.push(`<span class="badge bg-dark me-1" title="Kabinetas užimtas"><i class="bi bi-door-closed"></i></span>`);
                    if (otherCount>0) parts.push(`<span class="badge bg-danger" title="Kiti konfliktai: ${otherCount}"><i class="bi bi-exclamation-triangle"></i> ${otherCount}</span>`);
                    html = parts.join('');
                    cell.classList.add('preview-bad');
                } else {
                    html = `<span class="badge bg-success" title="Konfliktų nėra"><i class="bi bi-check-lg"></i></span>`;
                    cell.classList.add('preview-ok');
                }
                holder.innerHTML = html;
                holder.title = title;
            }catch(err){
                holder.innerHTML = `<span class="badge bg-secondary"><i class="bi bi-question-circle"></i></span>`;
            }
        }
    }

    // Unschedule from teacher view
    window.unscheduleSlotTeacherView = async function(slotId, groupId, teacherId, btn){
        const cell = btn.closest('td');
        btn.disabled = true;
        try{
            const resp = await fetch(`{{ route('schools.timetables.manual-slot', [$school, $timetable]) }}`.replace('manual-slot','unschedule-slot'), {
                method: 'POST',
                headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept':'application/json' },
                body: JSON.stringify({ slot_id: slotId })
            });
            const data = await resp.json();
            if(!resp.ok || !data.success){ showErrorModal('Klaida', data.error || 'Nepavyko iškelti į nesuplanuotas'); btn.disabled=false; return; }
            // Free the cell
            cell.innerHTML = '<span class="text-muted">—</span>';
            // Update unscheduled panel (increase remaining or create)
            const panel = document.getElementById('unscheduledPanel');
            if(panel){
                const item = panel.querySelector(`.unscheduled-item[data-group-id="${groupId}"]`);
                if(item){
                    let rem = parseInt(item.dataset.remaining,10)||0; rem=rem+1; item.dataset.remaining=String(rem);
                    const sm = item.querySelector('small'); if (sm) sm.textContent = `(${rem} liko)`;
                } else {
                    const body = panel.querySelector('.card-body');
                    const div = document.createElement('div');
                    div.className='unscheduled-item mb-1 d-flex align-items-center justify-content-between';
                    div.setAttribute('draggable','true');
                    div.dataset.kind='unscheduled'; div.dataset.groupId=groupId; div.dataset.teacherId=teacherId; div.dataset.remaining='1';
                    div.innerHTML = `<div class="flex-grow-1"><span class="badge bg-secondary me-1">${data.group?.name || 'Grupė'}</span><span class="badge bg-success">${data.group?.subject_name || '—'}</span><small class="text-muted ms-1">(1 liko)</small></div><div class="ms-2 btn-group btn-group-sm"><button type="button" class="btn btn-outline-info" title="Tikrinti" onclick="previewConflictsForGroup(${teacherId}, ${groupId})"><i class="bi bi-search"></i></button><button type="button" class="btn btn-outline-secondary" title="Išvalyti" onclick="clearConflictPreviews()"><i class="bi bi-x-lg"></i></button></div>`;
                    body.prepend(div);
                    // Make newly created item draggable
                    div.addEventListener('dragstart', e => {
                        dragged = div;
                        draggedKind = 'unscheduled';
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', div.dataset.groupId);
                        div.classList.add('dragging');
                    });
                    div.addEventListener('dragend', () => {
                        dragged?.classList.remove('dragging');
                        dragged = null;
                        draggedKind = null;
                    });
                }
            }
            flashMessage('Pamoka iškelta į nesuplanuotas', 'warning');
        }catch(err){ showErrorModal('Klaida', 'Klaida siunčiant užklausą'); btn.disabled=false; }
    }
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

    function showConfirmDialog(groupId, groupName, subjectName, day, slot, conflictData) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            const manageUrl = `{{ route('schools.timetables.show', [$school, $timetable]) }}?openGroupEdit=${groupId || ''}`;
            
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
                                    <ul class="mb-0 mt-0">
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

// Show lesson details in modal
function showLessonDetails(badge, event) {
    event.stopPropagation(); // Prevent drag when clicking
    
    const slotId = badge.dataset.slotId;
    const groupName = badge.dataset.groupName;
    const subjectName = badge.dataset.subjectName;
    const roomDisplay = badge.dataset.roomDisplay;
    const teacherName = badge.dataset.teacherFullName;
    const dayLabel = badge.dataset.dayLabel;
    const lessonNr = badge.dataset.lessonNr;
    const groupId = badge.dataset.groupId;
    
    // Populate modal basic info
    document.getElementById('modal-time').textContent = `${dayLabel}, ${lessonNr} pamoka`;
    document.getElementById('modal-teacher').textContent = teacherName;
    document.getElementById('modal-group').textContent = groupName;
    document.getElementById('modal-subject').textContent = subjectName;
    document.getElementById('modal-room').textContent = roomDisplay || 'Nenurodytas';
    
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
.lesson-col { min-width: 140px; }
.drop-target { transition: background-color 0.2s; }
.drop-target.drop-hover { background-color: #d1e7dd !important; border: 2px dashed #198754 !important; }
.tt-trigger { transition: transform .12s ease, box-shadow .12s; }
.tt-trigger:hover { transform: translateY(-2px); box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
.unscheduled-item { cursor: move; padding: 0.25rem 0.5rem; border-radius: 4px; transition: background-color 0.2s; }
.unscheduled-item:hover { background-color: #f8f9fa; }
.unscheduled-item.dragging { opacity: 0.5; }
.conflict-preview .badge { font-size: .7rem; }
.drop-target.preview-ok { box-shadow: inset 0 0 0 2px #19875466; }
.drop-target.preview-bad { box-shadow: inset 0 0 0 2px #dc354566; }
</style>
@endpush
