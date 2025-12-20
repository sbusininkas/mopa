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
    const lockBadge = group.is_locked ? `<span class="badge bg-success"><i class="bi bi-lock-fill"></i> Užrakinta</span>` : '';
    const groupDetailsUrl = `{{ route('schools.timetables.groups.details', [$school, $timetable, ':groupId']) }}`.replace(':groupId', group.id);
    const lockIcon = group.is_locked ? 'bi-unlock' : 'bi-lock';
    const lockTitle = group.is_locked ? 'Atrakinti grupę' : 'Užrakinti grupę';
    const lockClass = group.is_locked ? 'btn-success' : 'btn-outline-secondary';
    
    return `
        <div class="modern-card mb-2" id="group${group.id}">
            <div class="d-flex justify-content-between align-items-center py-2 px-3">
                <div class="d-flex align-items-center gap-2" style="cursor:pointer; flex-grow: 1;" data-bs-toggle="collapse" data-bs-target="#groupCollapse${group.id}" aria-expanded="false">
                    <a href="${groupDetailsUrl}" class="group-name-link" onclick="event.stopPropagation()"><strong>${group.name}</strong></a>
                    <span class="badge bg-secondary">${group.subject_name || ''}</span>
                    <span class="badge bg-info text-dark">${group.teacher_name || ''}</span>
                    ${roomBadge}
                    <span class="badge bg-light text-dark">${weekTypeLabel}</span>
                    <span class="badge bg-primary">${group.lessons_per_week} pam./sav.</span>
                    ${priorityBadge}
                    ${lockBadge}
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn ${lockClass}" onclick="toggleGroupLock(${group.id}, event)" title="${lockTitle}">
                        <i class="bi ${lockIcon}"></i>
                    </button>
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editGroup${group.id}"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteGroup${group.id}"><i class="bi bi-trash"></i></button>
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
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_priority" id="is_priority${group.id}" ${group.is_priority ? 'checked' : ''}>
                                <label class="form-check-label" for="is_priority${group.id}">Prioritetinė grupė</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_merge_with_same_subject" id="can_merge_with_same_subject${group.id}" ${group.can_merge_with_same_subject ? 'checked' : ''}>
                                <label class="form-check-label" for="can_merge_with_same_subject${group.id}">
                                    <i class="bi bi-link-45deg"></i> Leisti tvarkaraštyje sulieti grupę su to paties dalyko grupe
                                </label>
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

// Toggle group lock status
async function toggleGroupLock(groupId, event) {
    event.stopPropagation();
    event.preventDefault();
    
    const url = `{{ route('schools.timetables.groups.toggle-lock', [$school, $timetable, ':groupId']) }}`.replace(':groupId', groupId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    const groupCard = document.getElementById(`group${groupId}`);
    
    // Get current accordion state
    const collapseElement = document.getElementById(`groupCollapse${groupId}`);
    const wasExpanded = collapseElement && collapseElement.classList.contains('show');
    
    try {
        // Show loading state
        const originalHtml = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        button.disabled = true;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Update button and badge without reloading entire list
            const isLocked = data.is_locked;
            
            // Update button appearance
            button.className = isLocked ? 'btn btn-success' : 'btn btn-outline-secondary';
            button.title = isLocked ? 'Atrakinti grupę' : 'Užrakinti grupę';
            icon.className = isLocked ? 'bi bi-unlock' : 'bi bi-lock';
            
            // Update lock badge in header
            const headerDiv = groupCard.querySelector('.d-flex.align-items-center.gap-2');
            const existingLockBadge = headerDiv.querySelector('.badge.bg-success');
            
            if (isLocked) {
                if (!existingLockBadge) {
                    const lockBadge = document.createElement('span');
                    lockBadge.className = 'badge bg-success';
                    lockBadge.innerHTML = '<i class="bi bi-lock-fill"></i> Užrakinta';
                    headerDiv.appendChild(lockBadge);
                }
            } else {
                if (existingLockBadge) {
                    existingLockBadge.remove();
                }
            }
            
            // Show toast notification
            showToast(data.message, 'success');
            
            // Restore accordion state if it was expanded
            if (wasExpanded && collapseElement) {
                // Don't collapse it
            }
        } else {
            showToast('Klaida keičiant užrakinimo būseną', 'error');
        }
        
        button.disabled = false;
        button.innerHTML = originalHtml;
        
    } catch (error) {
        console.error('Error toggling lock:', error);
        showToast('Klaida: ' + error.message, 'error');
        button.disabled = false;
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastHtml = `
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
            <div class="toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toastElement = toastContainer.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastContainer.remove();
    });
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
    const HIGHLIGHT_CLASS = 'group-highlight';
    const HIGHLIGHT_DURATION_MS = 2500;

    // Clear previous highlights
    document.querySelectorAll('.' + HIGHLIGHT_CLASS).forEach(el => {
        el.classList.remove(HIGHLIGHT_CLASS);
        el.style.outline = '';
        el.style.boxShadow = '';
        el.style.backgroundColor = '';
    });

    // Timetable cells for this group
    const slotCells = document.querySelectorAll(`[data-group-id='${groupId}']`);
    slotCells.forEach(cell => {
        cell.classList.add(HIGHLIGHT_CLASS);
        cell.style.transition = 'background-color 0.2s, box-shadow 0.2s';
        cell.style.backgroundColor = 'rgba(255, 229, 100, 0.6)';
        cell.style.boxShadow = '0 0 0 2px rgba(255, 200, 0, 0.9) inset';
    });

    // Unscheduled items list entries
    const unscheduledItems = document.querySelectorAll(`.unscheduled-item[data-group-id='${groupId}']`);
    unscheduledItems.forEach(item => {
        item.classList.add(HIGHLIGHT_CLASS);
        item.style.transition = 'background-color 0.2s, outline-color 0.2s';
        item.style.backgroundColor = 'rgba(255, 229, 100, 0.4)';
        item.style.outline = '2px solid rgba(255, 200, 0, 0.9)';
    });

    // If there are timetable cells, scroll to the first one
    if (slotCells.length > 0) {
        slotCells[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        // Fallback: scroll to group container if present
        const groupElement = document.getElementById('group' + groupId);
        if (groupElement) {
            groupElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Ensure any collapsible group section opens
    const collapseElement = document.getElementById('groupCollapse' + groupId);
    if (collapseElement && !collapseElement.classList.contains('show')) {
        new bootstrap.Collapse(collapseElement, { toggle: true });
    }

    // Remove highlight after a delay
    setTimeout(() => {
        document.querySelectorAll('.' + HIGHLIGHT_CLASS).forEach(el => {
            el.classList.remove(HIGHLIGHT_CLASS);
            el.style.outline = '';
            el.style.boxShadow = '';
            el.style.backgroundColor = '';
        });
    }, HIGHLIGHT_DURATION_MS);
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
        Promise.all([
            fetch('{{ route('schools.timetables.all-teachers-working-days', [$school, $timetable]) }}').then(r => r.json()),
            fetch('{{ route('schools.timetables.all-teachers-unavailability', [$school, $timetable]) }}').then(r => r.json())
        ])
        .then(([daysData, unavailData]) => {
            // Merge datasets by teacher_id
            const unavailMap = new Map();
            unavailData.forEach(u => unavailMap.set(u.teacher_id, u.unavailability || []));
            teachersData = daysData.map(d => ({
                teacher_id: d.teacher_id,
                teacher_name: d.teacher_name,
                working_days: d.working_days || [],
                unavailability: unavailMap.get(d.teacher_id) || []
            }));
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
            // Unavailability summary
            const summaryByDay = {};
            (teacher.unavailability || []).forEach(r => {
                const dn = dayNames[r.day] || r.day;
                summaryByDay[dn] = summaryByDay[dn] || [];
                summaryByDay[dn].push(`${r.start}–${r.end}`);
            });
            const unavailHtml = Object.keys(summaryByDay).length
                ? Object.entries(summaryByDay).map(([dn, ranges]) => `<span class="badge bg-danger-subtle text-danger border me-1">${dn}: ${ranges.join(', ')}</span>`).join('')
                : '<span class="badge bg-light text-muted">Nėra apribojimų</span>';

            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${teacher.teacher_name}</strong>
                            <div class="mt-2 small" id="unavail-summary-${teacher.teacher_id}">${unavailHtml}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="editTeacherUnavailability(${teacher.teacher_id})">
                            <i class="bi bi-slash-circle"></i> Nedarbo laikai
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        listContainer.innerHTML = html;
    }

    // Unavailability editor
    window.editTeacherUnavailability = function(teacherId) {
        const teacher = teachersData.find(t => t.teacher_id === teacherId);
        if (!teacher) return;
        const dayNames = { 1:'Pirmadienis',2:'Antradienis',3:'Trečiadienis',4:'Ketvirtadienis',5:'Penktadienis' };
        const current = teacher.unavailability || [];

        function renderRows(day) {
            const rows = current.filter(r => r.day === day).map((r, idx) => `
                <div class="d-flex align-items-center gap-2 mb-2 unavail-row" data-day="${day}" data-idx="${idx}">
                    <label class="form-label mb-0 me-2">${dayNames[day]}</label>
                    <input type="text" class="form-control form-control-sm time-picker" style="width:100px" value="${r.start}" data-day="${day}" data-idx="${idx}" data-field="start" placeholder="HH:MM">
                    <span>iki</span>
                    <input type="text" class="form-control form-control-sm time-picker" style="width:100px" value="${r.end}" data-day="${day}" data-idx="${idx}" data-field="end" placeholder="HH:MM">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="remove" data-day="${day}" data-idx="${idx}"><i class="bi bi-x"></i></button>
                </div>
            `).join('');
            return rows || '<div class="text-muted small">Nėra apribojimų</div>';
        }

        let sections = '';
        for (let day=1; day<=5; day++) {
            sections += `
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>${dayNames[day]}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="add" data-day="${day}"><i class="bi bi-plus"></i> Pridėti intervalą</button>
                    </div>
                    <div class="mt-2" id="unavailDay${day}">${renderRows(day)}</div>
                </div>`;
        }

        const modalHtml = `
            <div class="modal fade" id="teacherUnavailabilityModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-slash-circle"></i> ${teacher.teacher_name} - Nedarbo laikai</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${sections}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                            <button type="button" class="btn btn-danger" id="saveUnavailabilityBtn"><i class="bi bi-save"></i> Išsaugoti</button>
                        </div>
                    </div>
                </div>
            </div>`;

        const existing = document.getElementById('teacherUnavailabilityModal');
        if (existing) existing.remove();
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('teacherUnavailabilityModal'));
        modal.show();
        const root = document.getElementById('teacherUnavailabilityModal');

        // Initialize Flatpickr on all existing time picker inputs
        root.querySelectorAll('.time-picker').forEach(input => {
            flatpickr(input, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
        });

        root.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            const action = btn.dataset.action;
            const day = parseInt(btn.dataset.day || '0');
            if (action === 'add') {
                current.push({ day, start: '08:00', end: '09:00' });
                document.getElementById('unavailDay' + day).innerHTML = renderRows(day);
                // Initialize Flatpickr on newly added inputs
                document.querySelectorAll(`#unavailDay${day} .time-picker`).forEach(input => {
                    flatpickr(input, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true
                    });
                });
                updateUnavailabilitySummary(teacherId);
            } else if (action === 'remove') {
                const idx = parseInt(btn.dataset.idx || '0');
                // remove matching by day and idx among filtered
                let count = -1;
                current = current.filter(r => {
                    if (r.day !== day) return true;
                    count++;
                    return count !== idx;
                });
                document.getElementById('unavailDay' + day).innerHTML = renderRows(day);
                // Reinitialize Flatpickr on remaining inputs
                document.querySelectorAll(`#unavailDay${day} .time-picker`).forEach(input => {
                    flatpickr(input, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true
                    });
                });
                updateUnavailabilitySummary(teacherId);
            }
        });

        root.addEventListener('input', (e) => {
            const input = e.target.closest('input[data-field]');
            if (!input) return;
            const day = parseInt(input.dataset.day);
            const idx = parseInt(input.dataset.idx);
            const field = input.dataset.field;
            const val = input.value; // Now stores HH:MM format
            // update nth row for that day
            let count = -1;
            current.forEach(r => {
                if (r.day !== day) return;
                count++;
                if (count === idx) { r[field] = val; }
            });
            updateUnavailabilitySummary(teacherId);
        });

        root.querySelector('#saveUnavailabilityBtn').addEventListener('click', () => {
            fetch('{{ route('schools.timetables.update-teacher-unavailability', [$school, $timetable]) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ teacher_id: teacherId, ranges: current })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // update local and update summary immediately
                    teacher.unavailability = current;
                    updateUnavailabilitySummary(teacherId);
                    const m = bootstrap.Modal.getInstance(root);
                    if (m) m.hide();
                } else {
                        showErrorModal(data.error || 'Nepavyko išsaugoti');
                }
            })
                .catch(() => showErrorModal('Nepavyko išsaugoti'));
        });

        root.addEventListener('hidden.bs.modal', function(){ this.remove(); });
    }

    function updateUnavailabilitySummary(teacherId) {
        const teacher = teachersData.find(t => t.teacher_id === teacherId);
        if (!teacher) return;
        
        const dayNames = {
            1: 'Pirmadienis',
            2: 'Antradienis',
            3: 'Trečiadienis',
            4: 'Ketvirtadienis',
            5: 'Penktadienis'
        };
        
        const summaryByDay = {};
        (teacher.unavailability || []).forEach(r => {
            const dn = dayNames[r.day] || r.day;
            summaryByDay[dn] = summaryByDay[dn] || [];
            summaryByDay[dn].push(`${r.start}–${r.end}`);
        });
        
        const unavailHtml = Object.keys(summaryByDay).length
            ? Object.entries(summaryByDay).map(([dn, ranges]) => `<span class="badge bg-danger-subtle text-danger border me-1">${dn}: ${ranges.join(', ')}</span>`).join('')
            : '<span class="badge bg-light text-muted">Nėra apribojimų</span>';
        
        const summaryEl = document.getElementById(`unavail-summary-${teacherId}`);
        if (summaryEl) {
            summaryEl.innerHTML = unavailHtml;
        }
    }

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

        function showErrorModal(message) {
            const modalHtml = `
                <div class="modal fade" id="errorAlertModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Klaida</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${message}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Gerai</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        
            const existing = document.getElementById('errorAlertModal');
            if (existing) existing.remove();
        
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('errorAlertModal'));
            modal.show();
        
            document.getElementById('errorAlertModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
});
</script>
@endpush
