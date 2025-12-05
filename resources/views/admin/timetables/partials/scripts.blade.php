<script>
// Load groups via AJAX
async function loadGroups() {
    const groupsList = document.getElementById('groupsList');
    const loader = document.getElementById('groupsLoader');
    
    try {
        const response = await fetch('{{ route("schools.timetables.groups.list", [$school, $timetable]) }}', {
            headers: { 'Accept': 'application/json' }
        });
        const ct = response.headers.get('content-type') || '';
        if (!response.ok) {
            const text = await response.text();
            throw new Error('HTTP ' + response.status + ' ' + text.slice(0, 300));
        }
        if (!ct.includes('application/json')) {
            const text = await response.text();
            throw new Error('Non-JSON response: ' + text.slice(0, 300));
        }
        const data = await response.json();
        
        if (data.success && data.groups) {
            if (data.groups.length === 0) {
                groupsList.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Nėra grupių</p>
                    </div>
                `;
                return;
            }
            
            // Render groups
            let html = '';
            data.groups.forEach(group => {
                html += renderGroupHTML(group);
            });
            
            groupsList.innerHTML = html;
            
            // Initialize listeners for all groups
            data.groups.forEach(group => {
                initializeGroupListeners(group.id);
            });
            
            // Initialize collapse event listeners
            data.groups.forEach(group => {
                const collapseEl = document.getElementById('groupCollapse' + group.id);
                if (collapseEl) {
                    collapseEl.addEventListener('shown.bs.collapse', function() {
                        initializeGroupListeners(group.id);
                    });
                }
            });
            
            // Initialize group search functionality
            initializeGroupSearch();
        }
    } catch (error) {
        console.error('Error loading groups:', error);
        const msg = (error && (error.message || error.toString())) || 'Nežinoma klaida';
        groupsList.innerHTML = `
            <div class="alert alert-danger">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <div>
                        <div>Klaida kraunant grupes.</div>
                        <div class="small text-muted">${msg}</div>
                    </div>
                </div>
            </div>
        `;
    }
}

// Render single group HTML
function renderGroupHTML(group) {
    const weekTypeLabel = group.week_type === 'all' ? 'Kiekv. savaitė' : (group.week_type === 'even' ? 'Lyginės' : 'Nelyginės');
    const roomBadge = group.room_number ? `<span class="badge bg-dark">${group.room_number} ${group.room_name || ''}</span>` : '';
    const priorityBadge = group.is_priority ? `<span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Prioritetinė</span>` : '';
    
    return `
        <div class="modern-card mb-2" id="group${group.id}">
            <div class="d-flex justify-content-between align-items-center py-2 px-3" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#groupCollapse${group.id}" aria-expanded="false">
                <div class="d-flex align-items-center gap-2">
                    <strong>${group.name}</strong>
                    <span class="badge bg-secondary">${group.subject_name || ''}</span>
                    <span class="badge bg-info text-dark">${group.teacher_name || ''}</span>
                    ${roomBadge}
                    <span class="badge bg-light text-dark">${weekTypeLabel}</span>
                    <span class="badge bg-primary">${group.lessons_per_week} pam./sav.</span>
                    ${priorityBadge}
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editGroup${group.id}" onclick="event.stopPropagation()"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteGroup${group.id}" onclick="event.stopPropagation()"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <div class="collapse" id="groupCollapse${group.id}">
                <div class="card-body border-top">
                    <form method="POST" class="assign-form" action="${'{{ route('schools.timetables.groups.assign-students', [$school, $timetable, ':groupId']) }}'.replace(':groupId', group.id)}">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label">Ieškoti mokinių</label>
                                <input type="text" class="form-control mb-2" id="globalSearch${group.id}" placeholder="Įveskite vardą ar pavardę...">
                                <div class="mt-2">
                                    <label class="form-label text-muted small">arba pasirinkite klasę</label>
                                    <select id="classSelect${group.id}" class="form-select">
                                        <option value="">-- Pasirinkite klasę --</option>
                                        @foreach($school->classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll${group.id}">
                                        <label class="form-check-label" for="selectAll${group.id}">Pažymėti visus</label>
                                    </div>
                                    <input type="text" class="form-control form-control-sm" id="filterInput${group.id}" placeholder="Filtruoti rezultatus">
                                </div>
                                <div class="mt-3" id="studentsList${group.id}">
                                    <p class="text-muted small">Ieškokite mokinio arba pasirinkite klasę.</p>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="d-flex justify-content-end align-items-center mb-1">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="removeAll${group.id}"><i class="bi bi-x-circle"></i> Pašalinti visus</button>
                                </div>
                                <div class="modern-table-wrapper">
                                    <table class="modern-table table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:40px"></th>
                                                <th>Vardas</th>
                                                <th>Klasė</th>
                                            </tr>
                                        </thead>
                                        <tbody id="assignedStudents${group.id}">
                                            ${group.students.map(student => `
                                                <tr>
                                                    <td><input type="checkbox" name="login_key_ids[]" value="${student.id}" checked></td>
                                                    <td>${student.full_name}</td>
                                                    <td>${student.class_name}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-2">
                                    <button class="btn btn-primary btn-sm assign-submit" type="submit" data-loading-text="<span class='spinner-border spinner-border-sm me-1'></span>Saugoma..."><i class="bi bi-save"></i> Išsaugoti priskyrimus</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Group Modal -->
        <div class="modal fade" id="editGroup${group.id}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Redaguoti grupę</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('schools.timetables.groups.update', [$school, $timetable, ':groupId']) }}".replace(':groupId', '${group.id}')>
                        <div class="modal-body">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Pavadinimas</label>
                                <input type="text" name="name" class="form-control" value="${group.name}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dalykas</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">-- Pasirinkite --</option>
                                    @foreach($school->subjects as $subject)
                                        <option value="{{ $subject->id }}">${group.subject_id === {{ $subject->id }} ? 'selected' : ''}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mokytojas</label>
                                <select name="teacher_login_key_id" class="form-select">
                                    <option value="">-- Pasirinkite --</option>
                                    @foreach($school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->orderBy('first_name')->get() as $teacher)
                                        <option value="{{ $teacher->id }}">${group.teacher_login_key_id === {{ $teacher->id }} ? 'selected' : ''}>{{ $teacher->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kabinetas</label>
                                <select name="room_id" class="form-select">
                                    <option value="">-- Pasirinkite --</option>
                                    @foreach($school->rooms as $room)
                                        <option value="{{ $room->id }}">${group.room_id === {{ $room->id }} ? 'selected' : ''}>{{ $room->number }} {{ $room->name }}</option>
                                    @endforeach
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
                                <input class="form-check-input" type="checkbox" name="is_priority" id="is_priority${group.id}" ${group.is_priority ? 'checked' : ''}>
                                <label class="form-check-label" for="is_priority${group.id}">Prioritetinė grupė</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="submit" class="btn btn-warning">Išsaugoti</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Delete Group Modal -->
        <div class="modal fade" id="deleteGroup${group.id}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-trash"></i> Pašalinti grupę</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('schools.timetables.groups.destroy', [$school, $timetable, ':groupId']) }}".replace(':groupId', '${group.id}')>
                        <div class="modal-body">
                            @csrf
                            @method('DELETE')
                            <p>Ar tikrai norite pašalinti grupę <strong>${group.name}</strong>?</p>
                            <p class="text-danger small">Veiksmas negrįžtamas!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="submit" class="btn btn-danger">Pašalinti</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
}

// Load groups on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGroups();
    
    // Only load unscheduled lessons if container exists
    const container = document.getElementById('unscheduledLessonsContainer');
    if (container) {
        loadUnscheduledLessons();
    }
});

// Load unscheduled lessons via AJAX
async function loadUnscheduledLessons() {
    const container = document.getElementById('unscheduledLessonsContainer');
    
    if (!container) {
        console.warn('Unscheduled lessons container not found');
        return;
    }
    
    try {
        const response = await fetch('{{ route("schools.timetables.unscheduled-html", [$school, $timetable]) }}');
        
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        
        const html = await response.text();
        container.innerHTML = html;
        
        // Re-initialize event listeners and Bootstrap components
        initializeUnscheduledElements();
        
    } catch (err) {
        console.error('Error loading unscheduled lessons:', err);
        container.innerHTML = `
            <div class="modern-card mb-4">
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Nepavyko užkrauti nepaskirstytų pamokų. 
                    <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="loadUnscheduledLessons()">
                        <i class="bi bi-arrow-clockwise"></i> Pabandyti dar kartą
                    </button>
                </div>
            </div>
        `;
    }
}

// Initialize dynamically loaded unscheduled content
function initializeUnscheduledElements() {
    const container = document.getElementById('unscheduledLessonsContainer');
    
    // Initialize Bootstrap tooltips and modals
    if (window.bootstrap) {
        container.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
        
        container.querySelectorAll('.modal').forEach(el => {
            new bootstrap.Modal(el);
        });
    }
    
    // Re-initialize merge group checkboxes
    container.querySelectorAll('.merge-group-checkbox').forEach(checkbox => {
        checkbox.removeEventListener('change', window.updateSelectedLessonsCount);
        checkbox.addEventListener('change', window.updateSelectedLessonsCount);
    });
}

// Scroll to group function
function scrollToGroup(groupId) {
    const groupElement = document.getElementById('group' + groupId);
    if (groupElement) {
        // Scroll to the element
        groupElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Highlight the group briefly
        groupElement.style.transition = 'background-color 0.3s';
        groupElement.style.backgroundColor = '#fff3cd';
        setTimeout(() => {
            groupElement.style.backgroundColor = '';
        }, 2000);
        
        // Open the collapse if it's not open
        const collapseElement = document.getElementById('groupCollapse' + groupId);
        if (collapseElement && !collapseElement.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: true });
        }
    }
}

// Copy unscheduled group function
function copyUnscheduledGroup(groupId, unscheduledCount) {
    if (!confirm('Ar tikrai norite sukurti grupės kopiją su ' + unscheduledCount + ' nepaskirstytomis pamokomis?\n\nNaujoje grupėje:\n- Bus tie patys mokiniai\n- Bus tas pats mokytojas\n- NEBUS priskirtas kabinetas (turėsite patys pasirinkti)\n- Bus ' + unscheduledCount + ' pamokos per savaitę')) {
        return;
    }

    const url = '{{ route("schools.timetables.groups.copy-unscheduled", [$school, $timetable, ":groupId"]) }}'.replace(':groupId', groupId);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            unscheduled_count: unscheduledCount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            window.location.reload();
        } else {
            alert('Klaida: ' + (data.message || 'Nepavyko sukurti kopijos'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Įvyko klaida kopijuojant grupę');
    });
}

// Functions for unscheduled group modals
function getCurrentAssignedIds(groupId) {
    const assignedList = document.getElementById('assignedStudentsListUnsch' + groupId);
    if (!assignedList) return [];
    // Only treat CHECKED checkboxes as currently assigned
    const checks = assignedList.querySelectorAll('input.assigned-checkbox:checked');
    return Array.from(checks)
        .map(el => parseInt(el.value))
        .filter(v => !isNaN(v));
}

function loadAllStudentsUnsch(groupId) {
    const studentsList = document.getElementById('studentsListUnsch' + groupId);
    
    // Get current assigned IDs from DOM
    const currentAssignedIds = getCurrentAssignedIds(groupId);
    
    fetch(`{{ url('/admin/api/schools') }}/{{ $school->id }}/students`)
        .then(res => res.json())
        .then(json => {
            const items = json.data || [];
            renderStudentsUnschWithAssigned(groupId, items, currentAssignedIds);
        })
        .catch(e => {
            studentsList.innerHTML = '<div class="alert alert-danger small">Klaida kraunant mokinius</div>';
        });
}

function renderStudentsUnsch(groupId, students) {
    const currentAssignedIds = getCurrentAssignedIds(groupId);
    renderStudentsUnschWithAssigned(groupId, students, currentAssignedIds);
}

function renderStudentsUnschWithAssigned(groupId, students, assignedIds) {
    const studentsList = document.getElementById('studentsListUnsch' + groupId);
    const assignedList = document.getElementById('assignedStudentsListUnsch' + groupId);
    const assignedCount = document.getElementById('assignedCountUnsch' + groupId);
    
    if (students.length === 0) {
        studentsList.innerHTML = '<p class="text-muted small p-2">Mokinių nerasta</p>';
        return;
    }
    
    // Separate assigned and unassigned students
    let assignedStudents = students.filter(s => assignedIds.includes(s.id));
    let unassignedStudents = students.filter(s => !assignedIds.includes(s.id));

    // Sort both lists by full_name (vardas pavardė)
    const byName = (a, b) => (a.full_name || '').localeCompare(b.full_name || '', 'lt', { sensitivity: 'base' });
    assignedStudents = assignedStudents.sort(byName);
    unassignedStudents = unassignedStudents.sort(byName);
    
    // Update counter
    if (assignedCount) {
        assignedCount.textContent = assignedStudents.length;
    }
    
    // Render UNASSIGNED students (left panel - search results)
    let searchHtml = '';
    if (unassignedStudents.length === 0) {
        searchHtml = '<p class="text-muted small p-2">Visi mokiniai jau priskirti</p>';
    } else {
        unassignedStudents.forEach(student => {
            searchHtml += `
                <div class="d-flex align-items-center justify-content-between p-2 border-bottom hover-bg-light" style="cursor: pointer;">
                    <div class="flex-grow-1">
                        <div class="small">${student.full_name}</div>
                        <small class="text-muted">${student.class_name || ''}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="assignStudentUnsch(${groupId}, ${student.id}, '${student.full_name}', '${student.class_name || ''}')">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            `;
        });
    }
    studentsList.innerHTML = searchHtml;
    
    // Render ASSIGNED students (right panel)
    let assignedHtml = '';
    if (assignedStudents.length === 0) {
        assignedHtml = '<div class="text-center py-3 text-muted"><small>Mokinių nėra</small></div>';
    } else {
        assignedStudents.forEach(student => {
            const inputId = `assignedCheck${groupId}_${student.id}`;
            assignedHtml += `
                <div class="d-flex align-items-center justify-content-between p-2 border-bottom" data-student-id="${student.id}">
                    <div class="form-check mb-0">
                        <input class="form-check-input assigned-checkbox" type="checkbox" value="${student.id}" id="${inputId}" checked>
                        <label class="form-check-label" for="${inputId}">
                            <span class="small fw-bold">${student.full_name}</span>
                            <small class="text-muted ms-1">${student.class_name || ''}</small>
                        </label>
                    </div>
                </div>
            `;
        });
    }
    assignedList.innerHTML = assignedHtml;
}

// Assign student to group
window.assignStudentUnsch = function(groupId, studentId, studentName, className) {
    const assignedList = document.getElementById('assignedStudentsListUnsch' + groupId);
    const assignedCount = document.getElementById('assignedCountUnsch' + groupId);
    
    // Check if already exists
    if (assignedList.querySelector(`[data-student-id="${studentId}"]`)) {
        // If exists but checkbox is unchecked, check it
        const existingCheckbox = assignedList.querySelector(`[data-student-id="${studentId}"] input.assigned-checkbox`);
        if (existingCheckbox && !existingCheckbox.checked) {
            existingCheckbox.checked = true;
        }
        return;
    }
    
    // Remove "no students" message if exists
    const emptyMsg = assignedList.querySelector('.text-center');
    if (emptyMsg) emptyMsg.remove();
    
    // Add to assigned list
    const newItem = document.createElement('div');
    newItem.className = 'd-flex align-items-center justify-content-between p-2 border-bottom';
    newItem.setAttribute('data-student-id', studentId);
    const inputId = `assignedCheck${groupId}_${studentId}`;
    newItem.innerHTML = `
        <div class="form-check mb-0">
            <input class="form-check-input assigned-checkbox" type="checkbox" value="${studentId}" id="${inputId}" checked>
            <label class="form-check-label" for="${inputId}">
                <span class="small fw-bold">${studentName}</span>
                <small class="text-muted ms-1">${className || ''}</small>
            </label>
        </div>
    `;
    assignedList.appendChild(newItem);
    
    // Update counter
    const currentCount = parseInt(assignedCount.textContent || '0');
    assignedCount.textContent = currentCount + 1;
    
    // Remove from search list
    const searchInput = document.getElementById('studentSearchUnsch' + groupId);
    if (searchInput && searchInput.value.trim()) {
        // Re-trigger search to update list
        searchInput.dispatchEvent(new Event('input'));
    } else {
        // Reload all students
        loadAllStudentsUnsch(groupId);
    }
}

// Unassign student from group
window.unassignStudentUnsch = function(groupId, studentId) {
    const assignedList = document.getElementById('assignedStudentsListUnsch' + groupId);
    const assignedCount = document.getElementById('assignedCountUnsch' + groupId);
    
    // Toggle to unchecked instead of removing (checkbox principle)
    const checkbox = assignedList.querySelector(`[data-student-id="${studentId}"] input.assigned-checkbox`);
    if (checkbox) checkbox.checked = false;
    
    // Update counter based on checked boxes
    const newCount = assignedList.querySelectorAll('input.assigned-checkbox:checked').length;
    assignedCount.textContent = newCount;
    
    // Do not remove item; saving will apply changes
    
    // Reload search list to show this student again
    const searchInput = document.getElementById('studentSearchUnsch' + groupId);
    if (searchInput && searchInput.value.trim()) {
        searchInput.dispatchEvent(new Event('input'));
    } else {
        loadAllStudentsUnsch(groupId);
    }
}

// Load students when edit modal opens
document.addEventListener('DOMContentLoaded', function() {
    @foreach(($timetable->generation_report['unscheduled'] ?? []) as $item)
        const editModal{{ $item['group_id'] }} = document.getElementById('editUnscheduledGroup{{ $item['group_id'] }}');
        const searchInput{{ $item['group_id'] }} = document.getElementById('studentSearchUnsch{{ $item['group_id'] }}');
        let searchTimeout{{ $item['group_id'] }} = null;
        let currentAssignedIds{{ $item['group_id'] }} = [];
        
        // Search functionality
        if (searchInput{{ $item['group_id'] }}) {
            searchInput{{ $item['group_id'] }}.addEventListener('input', function() {
                clearTimeout(searchTimeout{{ $item['group_id'] }});
                const query = this.value.trim();
                
                if (query.length < 2) {
                    document.getElementById('studentsListUnsch{{ $item['group_id'] }}').innerHTML = 
                        '<p class="text-muted small">Įveskite bent 2 simbolius...</p>';
                    return;
                }
                
                searchTimeout{{ $item['group_id'] }} = setTimeout(() => {
                    fetch(`{{ url('/admin/api/schools') }}/{{ $school->id }}/students/search?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(json => {
                            const items = json.data || [];
                            // Use getCurrentAssignedIds to read actual DOM state
                            const currentIds = getCurrentAssignedIds({{ $item['group_id'] }});
                            renderStudentsUnschWithAssigned({{ $item['group_id'] }}, items, currentIds);
                        })
                        .catch(e => {
                            document.getElementById('studentsListUnsch{{ $item['group_id'] }}').innerHTML = 
                                '<div class="alert alert-danger small">Klaida ieškant</div>';
                        });
                }, 300);
            });
        }
        
        if (editModal{{ $item['group_id'] }}) {
            editModal{{ $item['group_id'] }}.addEventListener('shown.bs.modal', function() {
                // Load group's current students
                fetch('{{ route("schools.timetables.groups.students", [$school, $timetable, $item["group_id"]]) }}')
                    .then(res => res.json())
                    .then(data => {
                        currentAssignedIds{{ $item['group_id'] }} = (data.students || []).map(s => s.id);
                        
                        // Load all students
                        return fetch(`{{ url('/admin/api/schools') }}/{{ $school->id }}/students`)
                            .then(res => res.json())
                            .then(json => {
                                const allStudents = json.data || [];
                                renderStudentsUnschWithAssigned({{ $item['group_id'] }}, allStudents, currentAssignedIds{{ $item['group_id'] }});

                                // Bind checkbox change handler once to keep counter in sync
                                const listEl = document.getElementById('assignedStudentsListUnsch' + {{ $item['group_id'] }});
                                if (listEl && !listEl.dataset.changeBound) {
                                    listEl.addEventListener('change', (ev) => {
                                        if (ev.target && ev.target.classList.contains('assigned-checkbox')) {
                                            const cnt = listEl.querySelectorAll('input.assigned-checkbox:checked').length;
                                            const cntEl = document.getElementById('assignedCountUnsch' + {{ $item['group_id'] }});
                                            if (cntEl) cntEl.textContent = cnt;
                                        }
                                    });
                                    listEl.dataset.changeBound = '1';
                                }

                                // Bind assigned search input to filter right panel independently
                                const assignedSearchEl = document.getElementById('assignedSearchUnsch' + {{ $item['group_id'] }});
                                if (assignedSearchEl && !assignedSearchEl.dataset.bound) {
                                    assignedSearchEl.addEventListener('input', () => {
                                        const q = (assignedSearchEl.value || '').toLowerCase();
                                        const items = listEl.querySelectorAll('[data-student-id]');
                                        items.forEach(it => {
                                            const text = (it.textContent || '').toLowerCase();
                                            it.classList.toggle('d-none', q && !text.includes(q));
                                        });
                                    });
                                    assignedSearchEl.dataset.bound = '1';
                                }
                            });
                    })
                    .catch(e => {
                        console.error('Error loading students:', e);
                        document.getElementById('studentsListUnsch{{ $item['group_id'] }}').innerHTML = 
                            '<div class="alert alert-danger small">Klaida kraunant mokinius</div>';
                    });
            });
        }
    @endforeach
});

function saveUnscheduledGroup(groupId) {
    const form = document.getElementById('editUnscheduledForm' + groupId);
    const formData = new FormData(form);
    
    // Collect assigned students from the assigned list (right panel)
    const assignedList = document.getElementById('assignedStudentsListUnsch' + groupId);
    const checked = assignedList.querySelectorAll('input.assigned-checkbox:checked');
    const studentIds = Array.from(checked).map(el => el.value);
    
    const data = {
        name: formData.get('name'),
        subject_id: formData.get('subject_id'),
        teacher_login_key_id: formData.get('teacher_login_key_id'),
        room_id: formData.get('room_id'),
        week_type: formData.get('week_type'),
        lessons_per_week: formData.get('lessons_per_week'),
        is_priority: formData.get('is_priority') ? 1 : 0,
        student_ids: studentIds
    };
    
    const url = '{{ route("schools.timetables.groups.update", [$school, $timetable, ":groupId"]) }}'.replace(':groupId', groupId);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success !== false) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUnscheduledGroup' + groupId));
            if (modal) modal.hide();
            
            // Show success message
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            showFlashMessage('Grupė sėkmingai atnaujinta!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showFlashMessage('Klaida: ' + (result.message || 'Nepavyko išsaugoti grupės'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFlashMessage('Įvyko klaida išsaugant grupę', 'danger');
    });
}

function confirmCopyGroup(groupId, unscheduledCount) {
    const url = '{{ route("schools.timetables.groups.copy-unscheduled", [$school, $timetable, ":groupId"]) }}'.replace(':groupId', groupId);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            unscheduled_count: unscheduledCount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('copyUnscheduledGroup' + groupId));
            if (modal) modal.hide();
            
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            showFlashMessage(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert('Klaida: ' + (data.message || 'Nepavyko sukurti kopijos'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Įvyko klaida kopijuojant grupę');
    });
}

// New function for copy with form data
function confirmCopyGroupWithData(groupId, unscheduledCount) {
    const form = document.getElementById('copyUnscheduledForm' + groupId);
    const formData = new FormData(form);
    
    // Validate required fields
    if (!formData.get('teacher_login_key_id')) {
        showFlashMessage('Prašome pasirinkti mokytoją', 'warning');
        return;
    }
    if (!formData.get('room_id')) {
        showFlashMessage('Prašome pasirinkti kabinetą', 'warning');
        return;
    }
    
    const data = {
        name: formData.get('name'),
        teacher_login_key_id: formData.get('teacher_login_key_id'),
        room_id: formData.get('room_id'),
        unscheduled_count: unscheduledCount
    };
    
    const url = '{{ route("schools.timetables.groups.copy-unscheduled", [$school, $timetable, ":groupId"]) }}'.replace(':groupId', groupId);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('copyUnscheduledGroup' + groupId));
            if (modal) modal.hide();
            
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            showFlashMessage(result.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showFlashMessage('Klaida: ' + (result.message || 'Nepavyko sukurti kopijos'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFlashMessage('Įvyko klaida kopijuojant grupę', 'danger');
    });
}

// Success toast
document.addEventListener('DOMContentLoaded', function() {
    // Enhance assignment forms with loading state
    const assignForms = document.querySelectorAll('.assign-form');
    assignForms.forEach(f => {
        f.addEventListener('submit', function() {
            const btn = f.querySelector('.assign-submit');
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = btn.getAttribute('data-loading-text');
        });
    });
    var toastEl = document.getElementById('toastSuccess');
    if (toastEl && window.bootstrap && bootstrap.Toast) {
        var toast = new bootstrap.Toast(toastEl, { delay: 2500 });
        toast.show();
    }
});

// Group search functionality
function initializeGroupSearch() {
    const groupSearch = document.getElementById('groupSearch');
    const groupsList = document.getElementById('groupsList');
    const allGroups = groupsList ? Array.from(groupsList.querySelectorAll('.modern-card.mb-2')) : [];
    
    // Store group data for search
    const groupData = allGroups.map(card => {
        const badges = card.querySelectorAll('.badge');
        const groupName = card.querySelector('strong')?.textContent || '';
        const subject = badges[0]?.textContent || '';
        const teacher = badges[1]?.textContent || '';
        
        // Get students from assigned tbody
        const groupId = card.querySelector('[id^="assignedStudents"]')?.id.replace('assignedStudents', '');
        const studentRows = card.querySelectorAll('#assignedStudents' + groupId + ' tr');
        const students = Array.from(studentRows).map(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            return nameCell ? nameCell.textContent.trim() : '';
        });
        
        return {
            element: card,
            groupName: groupName.toLowerCase(),
            subject: subject.toLowerCase(),
            teacher: teacher.toLowerCase(),
            students: students.map(s => s.toLowerCase())
        };
    });
    
    if (groupSearch) {
    groupSearch.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        if (!query) {
            allGroups.forEach(card => card.style.display = '');
            return;
        }
        
        groupData.forEach(data => {
            const matchesGroup = data.groupName.includes(query);
            const matchesSubject = data.subject.includes(query);
            const matchesTeacher = data.teacher.includes(query);
            const matchesStudent = data.students.some(student => student.includes(query));
            
            if (matchesGroup || matchesSubject || matchesTeacher || matchesStudent) {
                data.element.style.display = '';
            } else {
                data.element.style.display = 'none';
            }
        });
    });
    }
}

// Student assignment functionality per group - lazy initialization
const initializedGroups = new Set();

function initializeGroupListeners(groupId) {
    if (initializedGroups.has(groupId)) return; // Already initialized
    initializedGroups.add(groupId);
    
    const globalSearch = document.getElementById('globalSearch' + groupId);
    const classSelect = document.getElementById('classSelect' + groupId);
    const studentsList = document.getElementById('studentsList' + groupId);
    const assignedTbody = document.getElementById('assignedStudents' + groupId);
    const filterInput = document.getElementById('filterInput' + groupId);
    let searchTimeout = null;

    // Guard against missing DOM elements
    if (!globalSearch || !classSelect || !studentsList || !assignedTbody || !filterInput) {
        console.warn('Timetable group UI elements missing for group ' + groupId);
        return;
        }

        // Global search across all students
        globalSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                studentsList.innerHTML = '<p class="text-muted small">Įveskite bent 2 simbolius...</p>';
                studentsList.dataset.items = '[]';
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    studentsList.innerHTML = '<div class="d-flex align-items-center gap-2 text-muted small"><span class="spinner-border spinner-border-sm"></span> Ieškoma...</div>';
                    const res = await fetch(`{{ url('/admin/api/schools') }}/{{ $school->id }}/students/search?q=${encodeURIComponent(query)}`);
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    const items = (json.data || []);
                    studentsList.dataset.items = JSON.stringify(items);
                    studentsList.dataset.source = 'global';
                    renderStudents(groupId, items);
                    classSelect.value = '';
                } catch (e) {
                    studentsList.innerHTML = '<div class="alert alert-danger small">Klaida ieškant</div>';
                }
            }, 300);
        });

        // Class selection
        classSelect.addEventListener('change', async function() {
            const classId = this.value;
            globalSearch.value = '';
            
            if (!classId) {
                studentsList.innerHTML = '<p class="text-muted small">Ieškokite mokinio arba pasirinkite klasę.</p>';
                studentsList.dataset.items = '[]';
                return;
            }
            try {
                classSelect.disabled = true;
                studentsList.innerHTML = '<div class="d-flex align-items-center gap-2 text-muted small"><span class="spinner-border spinner-border-sm"></span> Kraunama...</div>';
                const res = await fetch(`{{ url('/admin/api/classes') }}/${classId}/students`);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                const items = (json.data || []);
                studentsList.dataset.items = JSON.stringify(items);
                studentsList.dataset.source = 'class';
                renderStudents(groupId, items);
                classSelect.disabled = false;
            } catch (e) {
                studentsList.innerHTML = '<div class="alert alert-danger small">Klaida kraunant</div>';
                classSelect.disabled = false;
            }
        });

        // Remove all assigned students
        const removeAllBtn = document.getElementById('removeAll' + groupId);
        if (removeAllBtn) {
            removeAllBtn.addEventListener('click', function() {
                assignedTbody.innerHTML = '';
                const checkboxes = studentsList.querySelectorAll('.student-checkbox');
                checkboxes.forEach(cb => { cb.checked = false; });
                const selAll = document.getElementById('selectAll' + groupId);
                if (selAll) selAll.checked = false;
            });
        }

        // Select all
        const selectAll = document.getElementById('selectAll' + groupId);
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = studentsList.querySelectorAll('.student-checkbox');
                checkboxes.forEach(cb => {
                    if (cb.checked !== selectAll.checked) {
                        cb.checked = selectAll.checked;
                        toggleAssign(groupId, cb);
                    }
                });
            });
        }

        // Filter results
        filterInput.addEventListener('input', function() {
            const items = JSON.parse(studentsList.dataset.items || '[]');
            renderStudents(groupId, items);
        });
    }
    
    // Render students list (shared function)
    function renderStudents(groupId, items) {
        const studentsList = document.getElementById('studentsList' + groupId);
        const filterInput = document.getElementById('filterInput' + groupId);
        const currentFilter = filterInput.value.trim().toLowerCase();
        const toRender = currentFilter ? items.filter(s => (s.full_name || '').toLowerCase().includes(currentFilter)) : items;
        const html = toRender.map(s => `
            <div class="form-check">
                <input class="form-check-input student-checkbox" type="checkbox" value="${s.id}" data-name="${s.full_name}" data-class="${s.class_name || ''}" onchange="toggleAssign(${groupId}, this)">
                <label class="form-check-label small">${s.full_name} <span class="text-muted">${s.class_name ? '(' + s.class_name + ')' : ''}</span></label>
            </div>
        `).join('');
        studentsList.innerHTML = html || '<p class="text-muted small">Nerasta.</p>';
    }

    // Toggle assign student (global function)
    window.toggleAssign = function(groupId, cb) {
        const assignedTbody = document.getElementById('assignedStudents' + groupId);
        if (cb.checked) {
            const existing = assignedTbody.querySelector(`input[value="${cb.value}"]`);
            if (existing) return;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox" name="login_key_ids[]" value="${cb.value}" checked></td>
                <td>${cb.dataset.name}</td>
                <td>${cb.dataset.class}</td>
            `;
            assignedTbody.appendChild(row);
        } else {
            const input = assignedTbody.querySelector(`input[value="${cb.value}"]`);
            if (input) input.closest('tr').remove();
        }
    }
</script>
@push('scripts')
<script>
// Flash message function
function showFlashMessage(msg, type = 'success') {
    let box = document.getElementById('flashBox');
    if (!box) {
        box = document.createElement('div');
        box.id = 'flashBox';
        box.style.position = 'fixed';
        box.style.top = '10px';
        box.style.right = '10px';
        box.style.zIndex = '9999';
        document.body.appendChild(box);
    }
    const el = document.createElement('div');
    el.className = `alert alert-${type} py-2 px-3 mb-2`;
    el.textContent = msg;
    box.appendChild(el);
    setTimeout(() => {
        el.remove();
        if (!box.children.length) box.remove();
    }, 3000);
}

// Poll progress if running
function startGenerationPolling() {
    const progressBar = document.getElementById('generationProgressBar');
    if (!progressBar) return;
    const poll = setInterval(() => {
        fetch('{{ route('timetables.generation-status', $timetable) }}')
            .then(r => r.json())
            .then(data => {
                if (data.progress != null) {
                    progressBar.style.width = data.progress + '%';
                    progressBar.textContent = data.progress + '%';
                }
                if (data.finished || data.status === 'failed') {
                    clearInterval(poll);
                    // Reload page to reflect final state & slots
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    setTimeout(()=> window.location.reload(), 800);
                }
            })
            .catch(e => console.error(e));
    }, 1500);
}
@if($timetable->generation_status==='running')
startGenerationPolling();
@endif

document.getElementById('generateForm')?.addEventListener('submit', function() {
    // After submission, create dynamic progress UI if not present
    if (!document.getElementById('generationProgressBar')) {
        const container = document.querySelector('.modern-card .card-body');
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';
        wrapper.innerHTML = `
            <label class="form-label small text-muted">Generavimo eiga</label>
            <div class="progress" style="height:20px;">
                <div id="generationProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%;">0%</div>
            </div>
        `;
        container.prepend(wrapper);
        startGenerationPolling();
    }
});

// Auto-open group edit modal if URL contains ?openGroupEdit={id}
document.addEventListener('DOMContentLoaded', function() {
    try {
        const params = new URLSearchParams(window.location.search);
        const openId = params.get('openGroupEdit');
        if (openId) {
            const modalEl = document.getElementById('editGroup' + openId);
            if (modalEl && window.bootstrap) {
                const m = new bootstrap.Modal(modalEl);
                m.show();
                // Optional: scroll into view
                const card = modalEl.closest('.modern-card');
                if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    } catch (e) { /* no-op */ }
});

// Teacher Working Days Management
document.addEventListener('DOMContentLoaded', function() {
    const teacherWorkingDaysCollapse = document.getElementById('teacherWorkingDaysCollapse');
    if (!teacherWorkingDaysCollapse) return;

    let teachersData = [];
    let loaded = false;

    teacherWorkingDaysCollapse.addEventListener('show.bs.collapse', function() {
        if (loaded) return;
        loadTeachersWorkingDays();
    });

    function loadTeachersWorkingDays() {
        const listContainer = document.getElementById('teachersWorkingDaysList');
        
        fetch('{{ route('schools.timetables.all-teachers-working-days', [$school, $timetable]) }}')
            .then(r => r.json())
            .then(data => {
                teachersData = data;
                loaded = true;
                renderTeachersList();
            })
            .catch(e => {
                console.error(e);
                listContainer.innerHTML = '<div class="alert alert-danger">Klaida kraunant mokytojų duomenis</div>';
            });
    }

    function renderTeachersList() {
        const listContainer = document.getElementById('teachersWorkingDaysList');
        if (teachersData.length === 0) {
            listContainer.innerHTML = '<p class="text-muted">Šiame tvarkaraštyje nėra priskirtų mokytojų.</p>';
            return;
        }

        const dayNames = {
            1: 'Pirmadienis',
            2: 'Antradienis',
            3: 'Trečiadienis',
            4: 'Ketvirtadienis',
            5: 'Penktadienis'
        };

        let html = '<div class="list-group">';
        
        teachersData.forEach(teacher => {
            const allDays = teacher.working_days.length === 0 || teacher.working_days.length === 5;
            const dayBadges = allDays 
                ? '<span class="badge bg-success">Visos dienos</span>'
                : teacher.working_days.sort().map(d => `<span class="badge bg-primary me-1">${dayNames[d]}</span>`).join('');

            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${teacher.teacher_name}</strong>
                            <div class="mt-1">${dayBadges}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editTeacherWorkingDays(${teacher.teacher_id})">
                            <i class="bi bi-pencil"></i> Redaguoti
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        listContainer.innerHTML = html;
    }

    window.editTeacherWorkingDays = function(teacherId) {
        const teacher = teachersData.find(t => t.teacher_id === teacherId);
        if (!teacher) return;

        const currentDays = teacher.working_days.length === 0 ? [1, 2, 3, 4, 5] : teacher.working_days;

        const dayNames = {
            1: 'Pirmadienis',
            2: 'Antradienis',
            3: 'Trečiadienis',
            4: 'Ketvirtadienis',
            5: 'Penktadienis'
        };

        let checkboxes = '';
        for (let day = 1; day <= 5; day++) {
            const checked = currentDays.includes(day) ? 'checked' : '';
            checkboxes += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${day}" id="day${day}" ${checked}>
                    <label class="form-check-label" for="day${day}">${dayNames[day]}</label>
                </div>
            `;
        }

        const modalHtml = `
            <div class="modal fade" id="teacherWorkingDaysModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-calendar-week"></i> ${teacher.teacher_name} - Darbo dienos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Pasirinkite dienas, kuriomis mokytojas dirba šiame tvarkaraštyje:</p>
                            ${checkboxes}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="button" class="btn btn-primary" onclick="saveTeacherWorkingDays(${teacherId})">
                                <i class="bi bi-save"></i> Išsaugoti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('teacherWorkingDaysModal');
        if (existingModal) existingModal.remove();

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('teacherWorkingDaysModal'));
        modal.show();

        // Remove modal from DOM after hidden
        document.getElementById('teacherWorkingDaysModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    };

    window.saveTeacherWorkingDays = function(teacherId) {
        const selectedDays = [];
        for (let day = 1; day <= 5; day++) {
            const checkbox = document.getElementById(`day${day}`);
            if (checkbox && checkbox.checked) {
                selectedDays.push(day);
            }
        }

        fetch('{{ route('schools.timetables.update-teacher-working-days', [$school, $timetable]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                teacher_id: teacherId,
                working_days: selectedDays
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Update local data
                const teacher = teachersData.find(t => t.teacher_id === teacherId);
                if (teacher) {
                    teacher.working_days = selectedDays;
                }
                
                // Re-render list
                renderTeachersList();

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('teacherWorkingDaysModal'));
                if (modal) modal.hide();

                // Show success message
                showToast('Mokytojo darbo dienos atnaujintos', 'success');
            } else {
                showFlashMessage('Klaida išsaugant duomenis', 'danger');
            }
        })
        .catch(e => {
            console.error(e);
            showFlashMessage('Klaida išsaugant duomenis', 'danger');
        });
    };

    function showToast(message, type = 'success') {
        const toastHtml = `
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
                <div class="toast align-items-center text-bg-${type} border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.body.lastElementChild.querySelector('.toast');
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', function() {
            this.closest('.position-fixed').remove();
        });
    }
});
</script>
@endpush
