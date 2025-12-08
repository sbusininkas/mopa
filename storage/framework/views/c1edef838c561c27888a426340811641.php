<?php $__env->startSection('content'); ?>
    <style>
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 36px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card p {
            font-size: 16px;
            color: #666;
            margin: 0;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 18px;
            margin: 0;
            opacity: 0.9;
        }

        .quick-actions {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h4 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        .quick-actions .btn {
            margin-bottom: 10px;
        }

        /* Timetable Review Styles */
        .card-header {
            transition: background-color 0.3s ease;
        }

        .card-header:hover {
            background-color: #f0f0f0 !important;
        }

        .student-item {
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .student-item:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd;
        }

        .student-item.active {
            background-color: #e7f1ff;
            border-color: #0d6efd;
        }

        .timetable-grid {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .timetable-grid thead th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
            font-weight: 600;
        }

        .timetable-grid tbody td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
            min-width: 100px;
        }

        .timetable-grid tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .lesson-cell {
            background-color: #e7f1ff;
            border-radius: 4px;
            padding: 8px;
            margin: 2px 0;
        }

        .timetable-student-header {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #0d6efd;
        }

        .timetables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(45%, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        @media (max-width: 1200px) {
            .timetables-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="welcome-section">
        <h2><i class="bi bi-building"></i> <?php echo e($school->name); ?></h2>
        <p><?php echo e($school->address); ?> | <?php echo e($school->phone); ?></p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #667eea;">
                    <i class="bi bi-collection"></i>
                </div>
                <h3><?php echo e($stats['classes_count']); ?></h3>
                <p>Klasės</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #48bb78;">
                    <i class="bi bi-people"></i>
                </div>
                <h3><?php echo e($stats['students_count']); ?></h3>
                <p>Mokiniai</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #ed8936;">
                    <i class="bi bi-briefcase"></i>
                </div>
                <h3><?php echo e($stats['teachers_count']); ?></h3>
                <p>Mokytojai</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="color: #f56565;">
                    <i class="bi bi-calendar3"></i>
                </div>
                <h3><?php echo e($stats['timetables_count']); ?></h3>
                <p>Tvarkaraščiai</p>
                <?php if($stats['active_timetables_count'] > 0): ?>
                    <small class="text-success"><i class="bi bi-check-circle"></i> <?php echo e($stats['active_timetables_count']); ?> aktyvūs</small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Timetable Review Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light cursor-pointer" data-bs-toggle="collapse" data-bs-target="#timetableReviewSection" style="cursor: pointer;">
                    <h5 class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                        <i class="bi bi-calendar2-check me-2"></i> Tvarkaraščių peržiūra
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </h5>
                </div>
                <div class="collapse" id="timetableReviewSection">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pasirinkite klasę:</label>
                                <select id="classSelect" class="form-select">
                                    <option value="">-- Pasirinkite klasę --</option>
                                    <?php $__currentLoopData = $school->classes()->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Paieška mokinių:</label>
                                <input type="text" id="studentSearch" class="form-control" placeholder="Ieškoti mokinių..." disabled>
                            </div>
                        </div>
                        <div id="studentListContainer" style="display: none;" class="mt-3">
                            <label class="form-label">Mokiniai:</label>
                            <div id="studentList" class="list-group" style="max-height: 300px; overflow-y: auto;"></div>
                        </div>
                        <div id="timetableViewContainer" class="mt-4" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-8">
            <div class="quick-actions">
                <h4><i class="bi bi-lightning"></i> Greitos nuorodos</h4>
                <div class="row">
                    <?php if(auth()->user()->isSupervisor()): ?>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.classes.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-collection"></i> Valdyti klases
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.login-keys.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-key"></i> Prisijungimo raktai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.subjects.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-book"></i> Valdyti dalykus
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.timetables.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-calendar3"></i> Tvarkaraščiai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.rooms.index', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-door-open"></i> Kabinetai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('schools.login-keys.import', $school)); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-upload"></i> Importuoti duomenis
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('classes.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-collection"></i> Valdyti klases
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('login-keys.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-key"></i> Prisijungimo raktai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('subjects.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-book"></i> Valdyti dalykus
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('timetables.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-calendar3"></i> Tvarkaraščiai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('rooms.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-door-open"></i> Kabinetai
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('import.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-upload"></i> Importuoti duomenis
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-actions">
                <h4><i class="bi bi-gear"></i> Nustatymai</h4>
                <?php if(auth()->user()->isSupervisor()): ?>
                    <a href="<?php echo e(route('schools.edit', $school)); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-building"></i> Mokyklos duomenys
                    </a>
                    <a href="<?php echo e(route('schools.edit-contacts', $school)); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-telephone"></i> Kontaktai
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('school.settings')); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-building"></i> Mokyklos duomenys
                    </a>
                    <a href="<?php echo e(route('school.contacts')); ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-telephone"></i> Kontaktai
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Timetable Review functionality
        let currentSelectedStudents = [];
        let currentTimetable = null;
        let activeSchoolId = <?php echo e($school->id); ?>;

        document.getElementById('classSelect').addEventListener('change', async function() {
            const classId = this.value;
            const studentListContainer = document.getElementById('studentListContainer');
            const studentList = document.getElementById('studentList');
            const studentSearch = document.getElementById('studentSearch');

            if (!classId) {
                studentListContainer.style.display = 'none';
                studentSearch.disabled = true;
                document.getElementById('timetableViewContainer').innerHTML = '';
                document.getElementById('timetableViewContainer').style.display = 'none';
                currentSelectedStudents = [];
                return;
            }

            try {
                const response = await fetch(`/admin/api/classes/${classId}/students`);
                const data = await response.json();
                const students = data.data || [];

                studentList.innerHTML = '';
                students.forEach(student => {
                    const studentItem = document.createElement('div');
                    studentItem.className = 'student-item';
                    studentItem.dataset.studentId = student.id;
                    studentItem.innerHTML = `
                        <strong>${student.full_name}</strong>
                        ${student.class_name ? '<br><small class="text-muted">Klasė: ' + student.class_name + '</small>' : ''}
                    `;
                    studentItem.addEventListener('click', () => toggleStudent(student, studentItem));
                    studentList.appendChild(studentItem);
                });

                studentListContainer.style.display = 'block';
                studentSearch.disabled = false;

                // Setup search functionality
                studentSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    document.querySelectorAll('#studentList .student-item').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(searchTerm) ? 'block' : 'none';
                    });
                });
            } catch (error) {
                console.error('Error fetching students:', error);
                studentListContainer.style.display = 'none';
                studentSearch.disabled = true;
            }
        });

        function toggleStudent(student, element) {
            element.classList.toggle('active');
            const index = currentSelectedStudents.findIndex(s => s.id === student.id);
            
            if (index > -1) {
                currentSelectedStudents.splice(index, 1);
            } else {
                currentSelectedStudents.push(student);
            }

            if (currentSelectedStudents.length > 0) {
                loadTimetables();
            } else {
                document.getElementById('timetableViewContainer').innerHTML = '';
                document.getElementById('timetableViewContainer').style.display = 'none';
            }
        }

        async function loadTimetables() {
            const classId = document.getElementById('classSelect').value;
            const container = document.getElementById('timetableViewContainer');
            container.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Įkeliama...</span></div>';
            container.style.display = 'block';

            try {
                // Get the active timetable for this school
                const timetableResponse = await fetch(`/admin/api/schools/${activeSchoolId}/active-timetable`);
                const timetableData = await timetableResponse.json();
                
                if (!timetableData || !timetableData.timetable_id) {
                    container.innerHTML = '<div class="alert alert-warning">Nėra aktyvaus tvarkaraščio šiai mokyklai</div>';
                    return;
                }

                currentTimetable = timetableData.timetable_id;

                // Load timetables for selected students
                const timetables = await Promise.all(currentSelectedStudents.map(student =>
                    fetch(`/admin/api/timetables/${currentTimetable}/student/${student.id}`).then(r => r.json())
                ));

                // Render timetables
                renderTimetables(timetables);
            } catch (error) {
                console.error('Error loading timetables:', error);
                container.innerHTML = '<div class="alert alert-danger">Greita klaida įkeliant tvarkaraščius</div>';
            }
        }

        function renderTimetables(timetables) {
            const container = document.getElementById('timetableViewContainer');
            const days = ['Pirmadienis', 'Antradienis', 'Trečiadienis', 'Ketvirtadienis', 'Penktadienis'];
            const dayAbbrs = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

            let html = '';

            // Create grid container for timetables
            html += '<div class="timetables-grid">';

            currentSelectedStudents.forEach((student, index) => {
                const timetable = timetables[index];

                html += `
                    <div class="timetable-student-header">
                        <h6 class="mb-0">
                            <i class="bi bi-person"></i> ${student.full_name}
                        </h6>
                    </div>
                    <table class="timetable-grid">
                        <thead>
                            <tr>
                                <th>Pamoka</th>
                                ${dayAbbrs.map(day => `<th>${days[dayAbbrs.indexOf(day)]}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
`;
                // Get max lesson number
                const maxLessons = Math.max(...dayAbbrs.map((day, i) => 
                    (timetable.grid && timetable.grid[Object.keys(timetable.grid)[0]]) ? Math.max(...Object.keys(timetable.grid).filter(k => timetable.grid[k][day]).length) : 0
                )) || 6;

                for (let lesson = 1; lesson <= 10; lesson++) {
                    html += '<tr>';
                    html += `<td><strong>${lesson}</strong></td>`;
                    
                    dayAbbrs.forEach((day, dayIndex) => {
                        let cellContent = '-';
                        
                        if (timetable.grid && timetable.grid[lesson] && timetable.grid[lesson][day]) {
                            const slot = timetable.grid[lesson][day];
                            cellContent = `
                                <div class="lesson-cell">
                                    <div><strong>${slot.subject}</strong></div>
                                    <div><small>${slot.teacher}</small></div>
                                    ${slot.room ? `<div><small>${slot.room}</small></div>` : ''}
                                </div>
                            `;
                        }
                        
                        html += `<td>${cellContent}</td>`;
                    });
                    
                    html += '</tr>';
                }

                html += `
                        </tbody>
                    </table>
`;
            });

            html += '</div>';
            container.innerHTML = html;
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/schools/dashboard.blade.php ENDPATH**/ ?>