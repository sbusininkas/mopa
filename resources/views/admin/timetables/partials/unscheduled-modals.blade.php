@foreach(($timetable->generation_report['unscheduled'] ?? []) as $item)
    <!-- Edit Unscheduled Group Modal -->
    <div class="modal fade" id="editUnscheduledGroup{{ $item['group_id'] }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Redaguoti grupę: {{ $item['group_name'] }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nepaskirstyta pamokų: <strong>{{ $item['remaining_lessons'] ?? 0 }}</strong> /
                        {{ $item['requested_lessons'] ?? ($item['lessons_per_week'] ?? ($item['total_lessons'] ?? ($item['remaining_lessons'] ?? 0))) }}
                    </div>
                    <form id="editUnscheduledForm{{ $item['group_id'] }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="{{ $item['group_name'] }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dalykas</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    @foreach($school->subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ (($item['subject_id'] ?? null) == $subject->id) ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mokytojas</label>
                                <select name="teacher_login_key_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    @foreach($school->loginKeys()->where('type','teacher')->orderBy('last_name')->orderBy('first_name')->get() as $teacher)
                                        <option value="{{ $teacher->id }}" {{ (($item['teacher_login_key_id'] ?? null) == $teacher->id) ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kabinetas</label>
                                <select name="room_id" class="form-select">
                                    <option value="">-- Nepasirinkta --</option>
                                    @foreach($school->rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->number }} {{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Savaitės tipas</label>
                                <select name="week_type" class="form-select" required>
                                    <option value="all">Kiekviena</option>
                                    <option value="even">Lyginės</option>
                                    <option value="odd">Nelyginės</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pamokų sk./sav.</label>
                                <input type="number" name="lessons_per_week" class="form-control" min="1" max="20" value="{{ $item['requested_lessons'] ?? ($item['lessons_per_week'] ?? 1) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input type="checkbox" name="is_priority" id="editUnschPriority{{ $item['group_id'] }}" class="form-check-input" value="1">
                                    <label class="form-check-label" for="editUnschPriority{{ $item['group_id'] }}">
                                        <i class="bi bi-star"></i> Prioritetinė
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3"><i class="bi bi-people"></i> Mokinių valdymas</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong><i class="bi bi-search"></i> Ieškoti mokinių</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="studentSearchUnsch{{ $item['group_id'] }}" placeholder="Ieškoti pagal vardą/pavardę...">
                                            <button class="btn btn-outline-secondary" type="button" onclick="loadAllStudentsUnsch({{ $item['group_id'] }})">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                        <div id="studentsListUnsch{{ $item['group_id'] }}" style="max-height: 350px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.5rem;">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                                <p class="text-muted small mt-2">Kraunami mokiniai...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <strong><i class="bi bi-people-fill"></i> Priskirti mokiniai (<span id="assignedCountUnsch{{ $item['group_id'] }}">0</span>)</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="assignedSearchUnsch{{ $item['group_id'] }}" placeholder="Filtruoti priskirtus mokinius...">
                                        </div>
                                        <div id="assignedStudentsListUnsch{{ $item['group_id'] }}" style="max-height: 400px; overflow-y: auto;">
                                            <div class="text-center py-3 text-muted">
                                                <small>Mokinių nėra</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-primary" onclick="saveUnscheduledGroup({{ $item['group_id'] }})">
                        <i class="bi bi-save"></i> Išsaugoti
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Copy Unscheduled Group Modal -->
    <div class="modal fade" id="copyUnscheduledGroup{{ $item['group_id'] }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-files"></i> Kopijuoti grupę</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Kopijuojama grupė:</strong> {{ $item['group_name'] }}<br>
                        <strong>Dalykas:</strong> {{ $item['subject_name'] }}<br>
                        <strong>Pamokų skaičius:</strong> {{ $item['remaining_lessons'] }} (tik nepaskirstytos)
                    </div>
                    
                    <form id="copyUnscheduledForm{{ $item['group_id'] }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Naujos grupės pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="{{ $item['group_name'] }} (kopija)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mokytojas <span class="text-danger">*</span></label>
                                <select name="teacher_login_key_id" class="form-select" required>
                                    <option value="">-- Pasirinkite --</option>
                                    @foreach($school->loginKeys()->where('type','teacher')->orderBy('last_name')->orderBy('first_name')->get() as $teacher)
                                        <option value="{{ $teacher->id }}" {{ ($item['teacher_login_key_id'] ?? null) == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kabinetas <span class="text-danger">*</span></label>
                                <select name="room_id" class="form-select" required>
                                    <option value="">-- Pasirinkite --</option>
                                    @foreach($school->rooms as $room)
                                        <option value="{{ $room->id }}" {{ ($item['room_id'] ?? null) == $room->id ? 'selected' : '' }}>{{ $room->number }} {{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-info-circle"></i> <strong>Pastaba:</strong> Bus nukopijuoti visi mokiniai iš originalios grupės.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-success" onclick="confirmCopyGroupWithData({{ $item['group_id'] }}, {{ $item['remaining_lessons'] }})">
                        <i class="bi bi-check-circle"></i> Sukurti kopiją
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal for merging unscheduled groups -->
<div class="modal fade" id="mergeUnscheduledGroupsModal" tabindex="-1" data-school-id="{{ $school->id ?? '' }}" data-timetable-id="{{ $timetable->id ?? '' }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-merge"></i> Sujungti grupes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Šiame dialoge galite sujungti kelias nepaskirstytas grupes į vieną, jei jos atitinka pagal mokinius, dalykus ir mokytoją.
                </div>
                <form id="mergeGroupsForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label"><strong>Pasirinkite grupes sujungti:</strong></label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            @php
                                $unscheduled = $timetable->generation_report['unscheduled'] ?? [];
                                // Prefetch students for the unscheduled group IDs
                                $unscheduledIds = collect($unscheduled)->pluck('group_id')->filter()->unique()->values()->all();
                                $studentsByGroup = $timetable->groups()
                                    ->whereIn('id', $unscheduledIds)
                                    ->with(['students:id'])
                                    ->get()
                                    ->mapWithKeys(function($g){
                                        $ids = $g->students->pluck('id')->sort()->values()->all();
                                        return [$g->id => implode(',', $ids)]; // stable hash
                                    })->toArray();

                                // Group by teacher + subject + identical students to show mergeable groups
                                $groupedByKey = [];
                                foreach ($unscheduled as $item) {
                                    if (($item['remaining_lessons'] ?? 0) > 0) {
                                        $studentHash = $studentsByGroup[$item['group_id']] ?? '';
                                        $key = ($item['teacher_login_key_id'] ?? '') . '|' . ($item['subject_id'] ?? '') . '|' . $studentHash;
                                        if (!isset($groupedByKey[$key])) {
                                            $groupedByKey[$key] = [];
                                        }
                                        $groupedByKey[$key][] = $item;
                                    }
                                }
                            @endphp
                            @forelse($groupedByKey as $key => $items)
                                @if(count($items) >= 2)
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">
                                        <strong>{{ $items[0]['teacher_name'] ?? '—' }} • {{ $items[0]['subject_name'] ?? '—' }}</strong>
                                    </small>
                                    @foreach($items as $item)
                                    <div class="form-check">
                                        <input class="form-check-input merge-group-checkbox" type="checkbox" 
                                               name="group_ids[]" value="{{ $item['group_id'] }}"
                                               data-teacher="{{ $item['teacher_login_key_id'] }}"
                                               data-subject="{{ $item['subject_id'] }}"
                                               data-group-name="{{ $item['group_name'] }}"
                                               data-lessons="{{ $item['remaining_lessons'] }}"
                                               id="mergeCheck{{ $item['group_id'] }}">
                                        <label class="form-check-label" for="mergeCheck{{ $item['group_id'] }}">
                                            {{ $item['group_name'] }} 
                                            <span class="badge bg-warning text-dark ms-2">{{ $item['remaining_lessons'] }} pamokos</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                <hr>
                                @endif
                            @empty
                                <span class="text-muted">Nėra grupių, kurias būtų galima sujungti</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Sujungtos grupės pavadinimas</label>
                            <input type="text" name="group_name" class="form-control" placeholder="Pvz: 6B_lietuviu_kalba_sujungta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kabinetas</label>
                            <select name="room_id" class="form-select" required>
                                <option value="">-- Pasirinkite --</option>
                                @foreach($school->rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-secondary mt-3 mb-0">
                        <small><strong>Pasirinktos pamokos:</strong> <span id="selectedLessonsCount">0</span> pamokos</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <button type="button" class="btn btn-success" onclick="submitMergeGroupsAction()">
                    <i class="bi bi-check-circle"></i> Sujungti grupes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.initializeMergeGroupsModal = function() {
    const checkboxes = document.querySelectorAll('.merge-group-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.removeEventListener('change', updateSelectedLessonsCount);
        checkbox.addEventListener('change', updateSelectedLessonsCount);
    });
};

window.updateSelectedLessonsCount = function() {
    const checkboxes = document.querySelectorAll('.merge-group-checkbox:checked');
    let totalLessons = 0;
    
    checkboxes.forEach(checkbox => {
        totalLessons += parseInt(checkbox.dataset.lessons) || 0;
    });
    
    const countElement = document.getElementById('selectedLessonsCount');
    if (countElement) {
        countElement.textContent = totalLessons;
    }
};

window.showMergeNotification = function(message, type = 'error') {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '1080';
    
    let bgClass = 'text-bg-danger';
    let icon = 'bi-exclamation-circle';
    
    if (type === 'success') {
        bgClass = 'text-bg-success';
        icon = 'bi-check-circle';
    } else if (type === 'warning') {
        bgClass = 'text-bg-warning';
        icon = 'bi-exclamation-triangle';
    } else if (type === 'info') {
        bgClass = 'text-bg-info';
        icon = 'bi-info-circle';
    }
    
    toast.innerHTML = `
        <div class="toast align-items-center ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast.querySelector('.toast'));
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
};

window.submitMergeGroupsAction = async function() {
    const checkboxes = document.querySelectorAll('.merge-group-checkbox:checked');
    
    if (checkboxes.length < 2) {
        showMergeNotification('Prašom pasirinkti bent 2 grupes sujungti', 'warning');
        return;
    }
    
    // Verify all selected groups have same teacher and subject
    const firstTeacher = checkboxes[0].dataset.teacher;
    const firstSubject = checkboxes[0].dataset.subject;
    
    for (let checkbox of checkboxes) {
        if (checkbox.dataset.teacher !== firstTeacher || checkbox.dataset.subject !== firstSubject) {
            showMergeNotification('Visos pasirinktos grupės turi turėti tą patį mokytoją ir dalyką', 'error');
            return;
        }
    }
    
    const groupIds = Array.from(checkboxes).map(cb => cb.value);
    const modalElement = document.getElementById('mergeUnscheduledGroupsModal');
    const groupNameInput = modalElement.querySelector('input[name="group_name"]');
    const roomSelect = modalElement.querySelector('select[name="room_id"]');
    
    const groupName = groupNameInput.value.trim();
    const roomId = roomSelect.value;
    
    if (!groupName) {
        showMergeNotification('Prašom įvesti sujungtos grupės pavadinimą', 'warning');
        groupNameInput.focus();
        return;
    }
    
    if (!roomId) {
        showMergeNotification('Prašom pasirinkti kabinetą', 'warning');
        roomSelect.focus();
        return;
    }
    
    try {
        const modalElement = document.getElementById('mergeUnscheduledGroupsModal');
        const schoolId = modalElement.dataset.schoolId;
        const timetableId = modalElement.dataset.timetableId;
        
        console.log('Merge request:', {
            schoolId,
            timetableId,
            group_ids: groupIds,
            group_name: groupName,
            room_id: parseInt(roomId)
        });
        
        const response = await fetch(`/admin/schools/${schoolId}/timetables/${timetableId}/merge-unscheduled-groups`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                group_ids: groupIds,
                group_name: groupName,
                room_id: parseInt(roomId)
            })
        });
        
        const data = await response.json();
        console.log('Response:', response.status, data);
        
        if (!response.ok || !data.success) {
            const errorMsg = data.error || data.message || 'Nepavyko sujungti grupių';
            showMergeNotification(errorMsg, 'error');
            return;
        }
        
        // Close modal and reload page
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        
        // Show success message and reload
        showMergeNotification('Grupės sėkmingai sujungtos! Puslapį perkraunama...', 'success');
        
        setTimeout(() => {
            location.reload();
        }, 1500);
        
    } catch (err) {
        console.error('Error merging groups:', err);
        showMergeNotification('Klaida siunčiant užklausą: ' + err.message, 'error');
    }
};

// Initialize modal when shown
document.addEventListener('shown.bs.modal', function(e) {
    if (e.target && e.target.id === 'mergeUnscheduledGroupsModal') {
        window.initializeMergeGroupsModal();
    }
});
</script>