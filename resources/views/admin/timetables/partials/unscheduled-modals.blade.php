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
