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
                                                    <span class="badge bg-secondary tt-trigger" style="font-size:0.75rem; cursor:move;" draggable="true"
                                                        data-kind="scheduled"
                                                        data-slot-id="{{ $cell['slot_id'] }}"
                                                        data-group-id="{{ $cell['group_id'] }}"
                                                        data-teacher-id="{{ $teacher->id }}"
                                                        data-group-name="{{ $cell['group'] }}"
                                                        data-subject-name="{{ $cell['subject'] ?? '' }}"
                                                    >{{ $cell['group'] }}</span>
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
                            <small class="text-muted ms-1">({{ $u['remaining_lessons'] }} liko)</small>
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
                try {
                    const conflicts = await checkConflicts(groupId, teacherId, day, slot);
                    if (conflicts.hasConflicts) {
                        await showConfirmDialog(groupId, groupName, subjectName, day, slot, conflicts);
                        return;
                    }
                    const resp = await fetch(`{{ route('schools.timetables.move-slot', [$school, $timetable]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ slot_id: slotId, teacher_id: teacherId, day: day, slot: slot })
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.success) {
                        showErrorModal('Klaida', data.error || 'Nepavyko perkelti pamokos');
                        return;
                    }
                    const originalCell = dragged.closest('td');
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
                                        ${conflictData.conflicts.map(c => `<li>${c}</li>`).join('')}
                                    </ul>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a href="${manageUrl}" class="btn btn-warning">
                                        <i class="bi bi-gear"></i> Tvarkyti grupę
                                    </a>
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

    function showErrorModal(title, message) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        const bodyHtml = Array.isArray(message) ? `<ul class="mb-0">${message.map(m=>`<li>${m}</li>`).join('')}</ul>` : `<p class="mb-0">${message}</p>`;
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

    function flashMessage(msg,type){
        let box=document.getElementById('flashBox');
        if(!box){ box=document.createElement('div'); box.id='flashBox'; box.style.position='fixed'; box.style.top='10px'; box.style.right='10px'; box.style.zIndex='9999'; document.body.appendChild(box); }
        const el=document.createElement('div'); el.className=`alert alert-${type} py-1 px-2 mb-2`; el.textContent=msg; box.appendChild(el); setTimeout(()=>{ el.remove(); if(!box.children.length) box.remove(); },3000);
    }
});
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
</style>
@endpush
