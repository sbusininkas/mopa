@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>{{ $school->exists ? 'Redaguoti mokyklą' : 'Sukurti mokyklą' }}</h3>
        <a href="{{ route('schools.index') }}" class="btn btn-secondary">Atgal</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $school->exists ? route('schools.update', $school) : route('schools.store') }}">
        @csrf
        @if($school->exists)
            @method('POST')
        @endif

        <div class="mb-3">
            <label class="form-label">Pavadinimas</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $school->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Adresas</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $school->address) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Telefonas</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
        </div>

        @if(auth()->user()->isSupervisor())
        <hr>
        <h5>Priskirti vartotojai</h5>
        <p class="text-muted">Pasirinkite vartotojus, kuriuos priskirti šiai mokyklai. Pažymėkite administratorių varčiu.</p>

        <div class="mb-3">
            @foreach($users as $user)
                @php
                    $attached = $school->users->firstWhere('id', $user->id);
                    $isChecked = (bool) $attached;
                    $isAdmin = $attached ? (bool) $attached->pivot->is_admin : false;
                @endphp
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}" {{ $isChecked ? 'checked' : '' }}>
                    <label class="form-check-label" for="user_{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</label>
                    <div class="form-check form-check-inline ms-3">
                        <input class="form-check-input" type="checkbox" name="admins[]" value="{{ $user->id }}" id="admin_{{ $user->id }}" {{ $isAdmin ? 'checked' : '' }}>
                        <label class="form-check-label" for="admin_{{ $user->id }}">Admin</label>
                    </div>
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary">Išsaugoti</button>
        @else
        <div class="alert alert-info">Tik prižiūrėtojas gali priskirti vartotojus prie mokyklų.</div>
        <button class="btn btn-primary">Išsaugoti</button>
        @endif
    </form>

    @if($school->exists)
    <hr class="my-4">
    
    <h4 class="mb-3">Pamokų laikai</h4>
    <p class="text-muted">Nustatykite pamokų pradžios ir pabaigos laikus. Šie laikai bus naudojami tvarkaraščio generavimui ir rodomi viešame tvarkaraštyje.</p>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <form method="POST" action="{{ route('school.settings.lesson-times') }}" id="lesson-times-form">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 80px;">Pamoka</th>
                        <th>Pradžia</th>
                        <th>Pabaiga</th>
                        <th style="width: 100px;">Veiksmai</th>
                    </tr>
                </thead>
                <tbody id="lesson-times-tbody">
                    @php
                        $lessonTimes = $school->lesson_times;
                    @endphp
                    @foreach($lessonTimes as $index => $time)
                    <tr data-index="{{ $index }}">
                        <td>
                            <input type="number" name="lesson_times[{{ $index }}][slot]" class="form-control" value="{{ $time['slot'] }}" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary time-btn w-100" data-index="{{ $index }}" data-field="start" data-value="{{ $time['start'] }}">
                                {{ $time['start'] }}
                            </button>
                            <input type="hidden" name="lesson_times[{{ $index }}][start]" class="time-hidden-start" value="{{ $time['start'] }}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary time-btn w-100" data-index="{{ $index }}" data-field="end" data-value="{{ $time['end'] }}">
                                {{ $time['end'] }}
                            </button>
                            <input type="hidden" name="lesson_times[{{ $index }}][end]" class="time-hidden-end" value="{{ $time['end'] }}">
                        </td>
                        <td>
                            @if($index >= 9)
                            <button type="button" class="btn btn-sm btn-danger remove-lesson-time">Šalinti</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" id="add-lesson-time">Pridėti pamokų laiką</button>
            <button type="submit" class="btn btn-primary">Išsaugoti pamokų laikus</button>
            <button type="button" class="btn btn-outline-secondary" id="reset-defaults">Atkurti numatytuosius</button>
        </div>
    </form>
    
    <!-- Time Picker Modal -->
    <div class="modal fade" id="timePickerModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pasirinkite laiką</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="time-picker">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Valandos</label>
                                <select id="timepicker-hours" class="form-select">
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Minutės</label>
                                <select id="timepicker-minutes" class="form-select">
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-info text-center mb-0">
                            <strong id="timepicker-preview">00:00</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-primary" id="confirm-time">Pasirinkti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Defaults Modal -->
    <div class="modal fade" id="resetDefaultsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atkurti numatytuosius laikus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Ar tikrai norite atkurti numatytuosius pamokų laikus?</p>
                    <p class="text-muted"><strong>Dėmesio!</strong> Dabartiniai pakeitimai bus prarasti.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-danger" id="confirm-reset">Atkurti</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function initTimeOptions() {
        const hoursSelect = document.getElementById('timepicker-hours');
        const minutesSelect = document.getElementById('timepicker-minutes');
        
        hoursSelect.innerHTML = '';
        minutesSelect.innerHTML = '';
        
        for (let i = 0; i < 24; i++) {
            const option = document.createElement('option');
            option.value = String(i).padStart(2, '0');
            option.textContent = String(i).padStart(2, '0');
            hoursSelect.appendChild(option);
        }
        
        for (let i = 0; i < 60; i += 5) {
            const option = document.createElement('option');
            option.value = String(i).padStart(2, '0');
            option.textContent = String(i).padStart(2, '0');
            minutesSelect.appendChild(option);
        }
    }
    
    function updateTimePreview() {
        const hours = document.getElementById('timepicker-hours').value;
        const minutes = document.getElementById('timepicker-minutes').value;
        document.getElementById('timepicker-preview').textContent = `${hours}:${minutes}`;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        let nextSlot = {{ count($lessonTimes) + 1 }};
        let currentTimeBtn = null;
        const timePickerModal = new bootstrap.Modal(document.getElementById('timePickerModal'));
        const resetModal = new bootstrap.Modal(document.getElementById('resetDefaultsModal'));
        
        initTimeOptions();
        
        // Time picker change listeners
        document.getElementById('timepicker-hours').addEventListener('change', updateTimePreview);
        document.getElementById('timepicker-minutes').addEventListener('change', updateTimePreview);
        
        // Confirm time picker
        document.getElementById('confirm-time').addEventListener('click', function() {
            if (!currentTimeBtn) return;
            
            const hours = document.getElementById('timepicker-hours').value;
            const minutes = document.getElementById('timepicker-minutes').value;
            const newTime = `${hours}:${minutes}`;
            
            currentTimeBtn.textContent = newTime;
            currentTimeBtn.setAttribute('data-value', newTime);
            
            const index = currentTimeBtn.getAttribute('data-index');
            const field = currentTimeBtn.getAttribute('data-field');
            const hiddenInput = currentTimeBtn.closest('td').querySelector(`input.time-hidden-${field}`);
            if (hiddenInput) {
                hiddenInput.value = newTime;
            }
            
            timePickerModal.hide();
            currentTimeBtn = null;
        });
        
        // Open time picker when clicking time button
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('time-btn')) {
                currentTimeBtn = e.target;
                const currentValue = e.target.getAttribute('data-value');
                const [hours, minutes] = currentValue.split(':');
                
                document.getElementById('timepicker-hours').value = hours;
                document.getElementById('timepicker-minutes').value = minutes;
                updateTimePreview();
                
                timePickerModal.show();
            }
        });
        
        document.getElementById('add-lesson-time').addEventListener('click', function() {
            const tbody = document.getElementById('lesson-times-tbody');
            const lastIndex = tbody.children.length;
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-index', lastIndex);
            newRow.innerHTML = `
                <td>
                    <input type="number" name="lesson_times[${lastIndex}][slot]" class="form-control" value="${nextSlot}" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-primary time-btn w-100" data-index="${lastIndex}" data-field="start" data-value="17:00">
                        17:00
                    </button>
                    <input type="hidden" name="lesson_times[${lastIndex}][start]" class="time-hidden-start" value="17:00">
                </td>
                <td>
                    <button type="button" class="btn btn-outline-primary time-btn w-100" data-index="${lastIndex}" data-field="end" data-value="17:45">
                        17:45
                    </button>
                    <input type="hidden" name="lesson_times[${lastIndex}][end]" class="time-hidden-end" value="17:45">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-lesson-time">Šalinti</button>
                </td>
            `;
            tbody.appendChild(newRow);
            nextSlot++;
            attachRemoveHandlers();
        });
        
        function attachRemoveHandlers() {
            document.querySelectorAll('.remove-lesson-time').forEach(btn => {
                btn.onclick = function() {
                    this.closest('tr').remove();
                    reindexRows();
                };
            });
        }
        
        function reindexRows() {
            const rows = document.querySelectorAll('#lesson-times-tbody tr');
            rows.forEach((row, index) => {
                row.setAttribute('data-index', index);
                const slotInput = row.querySelector('input[name*="[slot]"]');
                if (slotInput) slotInput.value = index + 1;
            });
            nextSlot = rows.length + 1;
        }
        
        // Show reset confirmation modal
        document.getElementById('reset-defaults').addEventListener('click', function() {
            resetModal.show();
        });
        
        // Handle reset confirmation
        document.getElementById('confirm-reset').addEventListener('click', function() {
            resetModal.hide();
            
            const defaults = @json(\App\Models\School::getDefaultLessonTimes());
            const tbody = document.getElementById('lesson-times-tbody');
            tbody.innerHTML = '';
            
            defaults.forEach((time, index) => {
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-index', index);
                newRow.innerHTML = `
                    <td>
                        <input type="number" name="lesson_times[${index}][slot]" class="form-control" value="${time.slot}" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-primary time-btn w-100" data-index="${index}" data-field="start" data-value="${time.start}">
                            ${time.start}
                        </button>
                        <input type="hidden" name="lesson_times[${index}][start]" class="time-hidden-start" value="${time.start}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-primary time-btn w-100" data-index="${index}" data-field="end" data-value="${time.end}">
                            ${time.end}
                        </button>
                        <input type="hidden" name="lesson_times[${index}][end]" class="time-hidden-end" value="${time.end}">
                    </td>
                    <td></td>
                `;
                tbody.appendChild(newRow);
            });
            
            nextSlot = defaults.length + 1;
            attachRemoveHandlers();
        });
        
        attachRemoveHandlers();
    });
    </script>
    
    <style>
    .time-btn {
        font-weight: 600;
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        padding: 0.5rem !important;
    }
    
    .time-btn:hover {
        background-color: #e7f1ff !important;
        color: #0c63e4 !important;
    }
    </style>
    @endif
</div>
@endsection
