<style>
    .timetable-review-section {
        margin-bottom: 30px;
    }

    .review-card-header {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .review-card-header:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .review-card-header h5 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .review-card-header .bi-chevron-down {
        transition: transform 0.3s ease;
        margin-left: auto;
    }

    .review-card-header[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(-180deg);
    }

    .review-card-body {
        background: white;
        border-radius: 0 0 10px 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #e0e0e0;
    }

    .class-select, .student-search {
        max-width: 400px;
        margin-bottom: 12px;
    }

    .student-list {
        list-style: none;
        padding: 0;
        margin: 20px 0 0 0;
    }

    .student-item {
        padding: 12px 15px;
        border: 1px solid #ddd;
        margin-bottom: 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .student-item:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }

    .student-item.selected {
        background-color: #e7f1ff;
        border-color: #0d6efd;
        font-weight: 500;
    }

    .timetables-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(45%, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    @media (max-width: 1200px) {
        .timetables-container {
            grid-template-columns: 1fr;
        }
    }

    .student-timetable {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .student-timetable-header {
        background-color: #f8f9fa;
        padding: 15px;
        margin: -20px -20px 15px -20px;
        border-radius: 10px 10px 0 0;
        border-left: 4px solid #0d6efd;
    }

    .student-timetable-header h6 {
        margin: 0;
        color: #0d6efd;
        font-weight: 600;
    }

    .timetable-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .timetable-table thead th {
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
        font-weight: 600;
        color: #333;
    }

    .timetable-table tbody td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
        min-height: 50px;
    }

    .timetable-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .lesson-cell {
        background-color: #e7f1ff;
        border-radius: 4px;
        padding: 8px;
        margin: 2px 0;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .lesson-cell strong {
        display: block;
        color: #0d6efd;
        font-weight: 600;
    }

    .timetable-cell.class-timetable-cell {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .timetable-cell.class-timetable-cell:hover {
        background-color: #f0f0f0;
    }

    .timetable-cell.class-timetable-cell-highlighted {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
        box-shadow: inset 0 0 0 1px #ffc107;
    }
    .lesson-cell small {
        display: block;
        color: #666;
    }

    .lesson-slot {
        font-weight: 500;
        color: #666;
        min-width: 30px;
    }

    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }

    .empty-message {
        text-align: center;
        padding: 20px;
        color: #999;
        font-style: italic;
    }

    .filters-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 250px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
    }

    /* Class Timetable Modal Styles */
    .class-timetable-modal .modal-dialog {
        max-width: 95%;
        margin: 20px auto;
    }

    .class-timetable-modal .modal-body {
        padding: 30px;
    }

    .class-timetable-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.7rem;
    }

    .class-timetable-table thead th {
        background-color: #0d6efd;
        color: white;
        border: 1px solid #0b5ed7;
        padding: 6px 4px;
        text-align: center;
        font-weight: 600;
    }

    .class-timetable-table tbody td {
        border: 1px solid #ddd;
        padding: 4px 2px;
        text-align: center;
        min-height: 30px;
        vertical-align: top;
    }

    .class-timetable-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .timetable-cell.modal-timetable-cell {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .timetable-cell.modal-timetable-cell:hover {
        background-color: #f0f0f0 !important;
    }

    .timetable-cell.modal-timetable-cell-highlighted {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
        box-shadow: inset 0 0 0 1px #ffc107;
    }

    .class-lesson-group {
        background-color: #e7f1ff;
        border-radius: 3px;
        padding: 4px 6px;
        margin: 1px 0;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        font-size: 0.75rem;
    }

    .class-lesson-group:hover {
        background-color: #cfe2ff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Student checkbox styles */
    .student-checkbox-item {
        display: flex;
        align-items: center;
        padding: 4px 6px;
        margin-bottom: 4px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        gap: 6px;
    }

    .student-checkbox-item:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }

    .student-checkbox-item input[type="checkbox"] {
        margin-right: 0;
        cursor: pointer;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .student-checkbox-item input[type="checkbox"]:checked {
        border-color: #0d6efd;
        background-color: #0d6efd;
    }

    .student-checkbox-item label {
        margin: 0;
        flex: 1;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .student-checkbox-item .btn {
        flex-shrink: 0;
        margin-left: 4px;
    }

    #studentList {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 8px;
        background-color: #fafafa;
    }

    #studentListContainer .form-label {
        margin-bottom: 8px;
        font-size: 0.95rem;
        font-weight: 500;
    }

    #studentListContainer .mb-3 {
        margin-bottom: 10px !important;
    }

    .student-checkbox-item label {
        margin: 0;
        flex: 1;
        cursor: pointer;
    }

    /* Student timetables grid */
    .students-timetables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    /* Responsive grid for modal */
    .students-timetables-grid-responsive {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 15px;
    }

    .student-timetable-card-responsive {
        flex: 0 1 calc(50% - 6px);
        min-width: 280px;
        background: white;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    @media (max-width: 1200px) {
        .student-timetable-card-responsive {
            flex: 0 1 calc(100% - 12px);
        }
    }

    .student-timetable-card {
        background: white;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .student-timetable-card-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        padding: 6px 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .student-timetable-card-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 0.7rem;
        flex: 1;
    }

    .student-timetable-card-header input[type="checkbox"] {
        cursor: pointer;
        margin: 0;
    }

    .student-timetable-card-body {
        padding: 0;
    }

    /* Selected students badge list */
    .selected-student-badge {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background-color: #e7f1ff;
        border: 1px solid #0d6efd;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .selected-student-badge-name {
        flex: 1;
    }

    .selected-student-badge button {
        background: none;
        border: none;
        color: #0d6efd;
        cursor: pointer;
        padding: 0;
        font-size: 1.3rem;
        line-height: 1;
        flex-shrink: 0;
        transition: color 0.2s ease;
    }

    .selected-student-badge button:hover {
        color: #ff6b6b;
    }

    /* Preview list styling */
    #selectedStudentsPreviewList {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 8px;
    }

    .room-number-link {
        color: #0d6efd;
        cursor: pointer;
        text-decoration: none;
        font-weight: 500;
        border-bottom: 1px dotted #0d6efd;
        transition: all 0.2s ease;
    }

    .room-number-link:hover {
        color: #0b5ed7;
        background-color: #e7f1ff;
        padding: 2px 4px;
        border-radius: 3px;
        border-bottom: 1px solid #0b5ed7;
    }

    .student-timetable-small {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.65rem;
    }

    .student-timetable-small thead th {
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        padding: 3px 2px;
        text-align: center;
        font-weight: 600;
        color: #333;
        font-size: 0.6rem;
        white-space: nowrap;
    }

    .student-timetable-small tbody td {
        border: 1px solid #ddd;
        padding: 2px;
        text-align: center;
        font-size: 0.6rem;
        min-height: 20px;
    }

    .student-timetable-small tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .lesson-cell-small {
        background-color: #e7f1ff;
        border-radius: 2px;
        padding: 1px;
        margin: 0.5px 0;
        font-size: 0.55rem;
        line-height: 1.1;
    }

    .lesson-cell-small strong {
        display: block;
        color: #0d6efd;
        font-weight: 600;
        font-size: 0.55rem;
    }

    .lesson-cell-small small {
        display: block;
        color: #666;
        font-size: 0.5rem;
        font-weight: 500;
    }

    .timetable-cell.selected-timetable-cell {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .timetable-cell.selected-timetable-cell:hover {
        background-color: #f0f0f0 !important;
    }

    .timetable-cell.selected-timetable-cell-highlighted {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
        box-shadow: inset 0 0 0 1px #ffc107;
    }

    .timetable-cell.group-toggled-highlighted {
        background-color: #e8f5e9 !important;
        border: 2px solid #4caf50 !important;
        box-shadow: inset 0 0 0 1px #4caf50;
    }

    .group-highlight-btn {
        transition: all 0.2s ease;
    }

    .group-highlight-btn:hover {
        transform: scale(1.2);
    }
    }

    .class-lesson-group strong {
        display: block;
        color: #0d6efd;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .class-lesson-group small {
        display: block;
        color: #666;
        font-size: 0.8rem;
    }

    .students-badge {
        display: inline-block;
        background-color: #0d6efd;
        color: white;
        border-radius: 10px;
        padding: 2px 8px;
        font-size: 0.75rem;
        margin-top: 4px;
    }

    .class-timetable-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        padding: 12px 15px;
        margin: -1rem -1rem 15px -1rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .class-timetable-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }

    .view-class-timetable-btn {
        margin-left: 8px;
        font-size: 0.85rem;
    }

    /* Comparison table styles */
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .comparison-table thead th {
        background-color: #0d6efd;
        color: white;
        padding: 10px 8px;
        text-align: center;
        font-weight: 600;
        border: 1px solid #0b5ed7;
        font-size: 0.85rem;
    }

    .comparison-table tbody td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        font-size: 0.75rem;
    }

    .comparison-table .lesson-num {
        background-color: #f8f9fa;
        font-weight: 600;
        width: 50px;
        text-align: center;
    }

    .comparison-cell-same {
        background-color: #d4edda;
        font-weight: 500;
        border: 1px solid #c3e6cb;
    }

    .comparison-cell-different {
        background-color: #f8d7da;
        font-weight: 500;
        border: 1px solid #f5c6cb;
    }

    .comparison-cell-empty {
        background-color: #e2e3e5;
        color: #666;
    }

    .comparison-legend {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .comparison-legend-item {
        display: inline-block;
        margin-right: 20px;
        margin-bottom: 10px;
    }

    .comparison-legend-color {
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 8px;
        border-radius: 3px;
        vertical-align: middle;
        border: 1px solid #ddd;
    }

    /* Comparison cell colors */
    .comparison-cell.comparison-same {
        background-color: #d4edda !important;
        border: 1px solid #c3e6cb !important;
    }

    .comparison-cell.comparison-different {
        background-color: #f8d7da !important;
        border: 1px solid #f5c6cb !important;
    }

</style>

<div class="timetable-review-section">
    <div class="review-card-header" data-bs-toggle="collapse" data-bs-target="#classReviewContent" aria-expanded="false">
        <h5>
            <i class="bi bi-calendar2-check"></i>
            Klasių tvarkaraščio peržiūra
            <i class="bi bi-chevron-down"></i>
        </h5>
    </div>

    <div id="classReviewContent" class="collapse">
        <div class="review-card-body">
            <div class="filters-row">
                <div class="filter-group">
                    <label for="classSelect">Pasirinkite klasę:</label>
                    <select id="classSelect" class="form-select">
                        <option value="">-- Pasirinkite klasę --</option>
                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class->id); ?>" data-class-name="<?php echo e($class->name); ?>"><?php echo e($class->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="studentSearch">Paieška mokinių (visos klasės):</label>
                    <input type="text" id="studentSearch" class="form-control" placeholder="Ieškoti mokinių iš visų klasių..." disabled>
                </div>
                <div class="filter-group" style="flex: 0;">
                    <label>&nbsp;</label>
                    <button id="viewClassTimetableBtn" class="btn btn-primary" style="white-space: nowrap;" disabled>
                        <i class="bi bi-calendar3"></i> Klasės tvarkaraštis
                    </button>
                </div>
            </div>

            <div id="studentListContainer" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <label class="form-label" style="margin-bottom: 0;">Mokiniai (pasirinkite norėdami peržiūrėti jų tvarkaraščius):</label>
                    <div>
                        <button id="selectAllBtn" class="btn btn-sm btn-outline-secondary" title="Pažymėti visus mokinius">
                            <i class="bi bi-check2-all"></i> Pažymėti visus
                        </button>
                        <button id="deselectAllBtn" class="btn btn-sm btn-outline-secondary" title="Atžymėti visus mokinius" style="margin-left: 5px;">
                            <i class="bi bi-x-lg"></i> Atžymėti visus
                        </button>
                    </div>
                </div>
                <div id="studentList" class="mb-3"></div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button id="viewSelectedTimetablesBtn" class="btn btn-info" style="display: none;" disabled>
                        <i class="bi bi-eye"></i> Peržiūrėti pasirinktų mokinių tvarkaraščius
                    </button>
                    <span id="selectedCount" class="text-muted" style="display: none;"></span>
                </div>
                <div id="selectedStudentsPreviewList" style="display: none; margin-top: 15px;"></div>
            </div>

            <div id="timetableContainer"></div>
        </div>
    </div>
</div>

<!-- Class Timetable Modal -->
<div class="modal fade class-timetable-modal" id="classTimetableModal" tabindex="-1" aria-labelledby="classTimetableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="class-timetable-header">
                <h4 id="classTimetableModalLabel">
                    <i class="bi bi-calendar3"></i> Klasės tvarkaraštis: <span id="modalClassName"></span>
                </h4>
            </div>
            <div class="modal-body">
                <div id="classTimetableContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
            </div>
        </div>
    </div>
</div>

<!-- Selected Students Timetables Modal -->
<div class="modal fade" id="selectedStudentsTimetablesModal" tabindex="-1" aria-labelledby="selectedStudentsTimetablesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xxl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white;">
                <h5 id="selectedStudentsTimetablesModalLabel" class="modal-title" style="color: white;">
                    <i class="bi bi-calendar3"></i> Pasirinktų mokinių tvarkaraščiai
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <label style="font-weight: 600; margin: 0;">Pasirinkti mokiniai:</label>
                    </div>
                    <div id="selectedStudentsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 8px; margin-bottom: 15px;"></div>
                    <div style="padding: 12px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 15px;">
                        <label style="font-weight: 600; margin-bottom: 10px; display: block;">Pasirinkite tvarkaraščius lyginimui:</label>
                        <div id="comparisonCheckboxes" style="display: flex; flex-wrap: wrap; gap: 15px;"></div>
                        <button id="compareBtn" class="btn btn-warning mt-3" title="Palyginti pasirinktus tvarkaraščius" style="display: none;">
                            <i class="bi bi-diagram-3"></i> Palyginti pasirinktus
                        </button>
                        <button id="clearComparisonBtn" class="btn btn-secondary mt-3" title="Nutraukti palyginimą" style="display: none; margin-left: 10px;">
                            <i class="bi bi-x-lg"></i> Nutraukti palyginimą
                        </button>
                    </div>
                </div>
                <div id="selectedStudentsTimetablesGrid" class="students-timetables-grid-responsive"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedStudents = [];
    let currentClassStudents = [];
    let currentClassName = '';
    const timetableId = <?php echo e($timetable->id); ?>;

    document.getElementById('classSelect').addEventListener('change', async function() {
        const classId = this.value;
        const className = this.options[this.selectedIndex]?.dataset.className || '';
        const studentListContainer = document.getElementById('studentListContainer');
        const studentList = document.getElementById('studentList');
        const studentSearch = document.getElementById('studentSearch');
        const timetableContainer = document.getElementById('timetableContainer');
        const viewClassBtn = document.getElementById('viewClassTimetableBtn');

        selectedStudents = [];
        currentClassStudents = [];
        currentClassName = className;
        timetableContainer.innerHTML = '';

        if (!classId) {
            studentListContainer.style.display = 'none';
            studentSearch.disabled = true;
            viewClassBtn.disabled = true;
            return;
        }

        try {
            const response = await fetch(`/admin/api/classes/${classId}/students`);
            const data = await response.json();
            const students = data.data || [];
            
            currentClassStudents = students;

            studentList.innerHTML = '';
            students.forEach(student => {
                const div = document.createElement('div');
                div.className = 'student-checkbox-item';
                div.innerHTML = `
                    <input type="checkbox" class="student-checkbox" id="student-${student.id}" 
                           data-student-id="${student.id}" data-student-name="${student.full_name}">
                    <label for="student-${student.id}">${student.full_name}</label>
                    <button class="btn btn-sm btn-outline-primary" title="Atidaryti mokininio tvarkaraštį" 
                            onclick="event.stopPropagation(); openStudentTimetable(${student.id}, '${student.full_name}')" 
                            style="padding: 2px 8px; font-size: 0.8rem;">
                        <i class="bi bi-calendar"></i>
                    </button>
                `;
                studentList.appendChild(div);
            });

            studentListContainer.style.display = 'block';
            studentSearch.disabled = false;
            studentSearch.value = '';
            viewClassBtn.disabled = false;

            // Setup search filter - search across all classes
            studentSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('#studentList .student-checkbox-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(searchTerm) ? 'block' : 'none';
                });
            });
            
            // Select All button
            document.getElementById('selectAllBtn').addEventListener('click', function() {
                document.querySelectorAll('#studentList .student-checkbox-item').forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (item.style.display !== 'none' && !checkbox.checked) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
            
            // Deselect All button
            document.getElementById('deselectAllBtn').addEventListener('click', function() {
                document.querySelectorAll('#studentList .student-checkbox-item').forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox.checked) {
                        checkbox.checked = false;
                        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        } catch (error) {
            console.error('Error fetching students:', error);
            studentListContainer.style.display = 'none';
            viewClassBtn.disabled = true;
        }
    });

    // View class timetable button
    document.getElementById('viewClassTimetableBtn').addEventListener('click', async function() {
        if (currentClassStudents.length === 0) return;
        
        await loadClassTimetable();
    });

    function toggleStudent(student, element) {
        element.classList.toggle('selected');
        const index = selectedStudents.findIndex(s => s.id === student.id);

        if (index > -1) {
            selectedStudents.splice(index, 1);
        } else {
            selectedStudents.push(student);
        }

        if (selectedStudents.length > 0) {
            displayTimetables();
        } else {
            document.getElementById('timetableContainer').innerHTML = '';
        }
    }

    async function displayTimetables() {
        const container = document.getElementById('timetableContainer');
        container.innerHTML = '<div class="loading-spinner"><div class="spinner-border" role="status"><span class="visually-hidden">Įkeliama...</span></div></div>';

        try {
            const timetables = await Promise.all(selectedStudents.map(student =>
                fetch(`/admin/api/timetables/${timetableId}/student/${student.id}`).then(r => r.json())
            ));

            renderTimetables(timetables);
        } catch (error) {
            console.error('Error loading timetables:', error);
            container.innerHTML = '<div class="alert alert-danger">Klaida įkeliant tvarkaraščius</div>';
        }
    }

    function renderTimetables(timetablesData) {
        const container = document.getElementById('timetableContainer');
        const days = ['Pirmadienis', 'Antradienis', 'Trečiadienis', 'Ketvirtadienis', 'Penktadienis'];
        const dayAbbrMap = { 'Mon': 0, 'Tue': 1, 'Wed': 2, 'Thu': 3, 'Fri': 4 };

        let html = '<div class="timetables-container">';

        timetablesData.forEach((timetableData, index) => {
            const student = selectedStudents[index];
            const grid = timetableData.grid || {};

            html += `
                <div class="student-timetable">
                    <div class="student-timetable-header">
                        <h6><i class="bi bi-person"></i> ${student.full_name}</h6>
                    </div>
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th>Pamoka</th>
                                <th>Pirmadienis</th>
                                <th>Antradienis</th>
                                <th>Trečiadienis</th>
                                <th>Ketvirtadienis</th>
                                <th>Penktadienis</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            for (let lesson = 1; lesson <= 10; lesson++) {
                html += '<tr>';
                html += `<td class="lesson-slot">${lesson}</td>`;

                ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].forEach(day => {
                    let cellContent = '-';

                    if (grid[lesson] && grid[lesson][day]) {
                        const slot = grid[lesson][day];
                        cellContent = `
                            <div class="lesson-cell">
                                <strong>${slot.subject}</strong>
                                <small>${slot.teacher}</small>
                                ${slot.room ? `<small>${slot.room}</small>` : ''}
                            </div>
                        `;
                    }

                    html += `<td class="timetable-cell class-timetable-cell" data-day="${day}" data-slot="${lesson}">${cellContent}</td>`;
                });

                html += '</tr>';
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;

        // Initialize hover handlers for cells with same day/slot highlighting
        const cells = container.querySelectorAll('.timetable-cell.class-timetable-cell');
        cells.forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                const day = this.dataset.day;
                const slot = this.dataset.slot;
                
                // Highlight all cells with same day and slot
                if (day && slot) {
                    cells.forEach(c => {
                        if (c.dataset.day === day && c.dataset.slot === slot) {
                            c.classList.add('class-timetable-cell-highlighted');
                        }
                    });
                }
            });

            cell.addEventListener('mouseleave', function() {
                // Remove all highlights
                cells.forEach(c => c.classList.remove('class-timetable-cell-highlighted'));
            });
        });
    }

    async function loadClassTimetable() {
        const modalContent = document.getElementById('classTimetableContent');
        const modalClassName = document.getElementById('modalClassName');
        
        modalClassName.textContent = currentClassName;
        modalContent.innerHTML = '<div class="loading-spinner"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Įkeliama...</span></div></div>';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('classTimetableModal'));
        modal.show();

        try {
            // Load timetables for all students in class
            const timetables = await Promise.all(currentClassStudents.map(student =>
                fetch(`/admin/api/timetables/${timetableId}/student/${student.id}`).then(r => r.json())
            ));

            renderClassTimetable(timetables);
        } catch (error) {
            console.error('Error loading class timetable:', error);
            modalContent.innerHTML = '<div class="alert alert-danger">Klaida įkeliant klasės tvarkaraštį</div>';
        }
    }

    function renderClassTimetable(timetablesData) {
        const modalContent = document.getElementById('classTimetableContent');
        const days = ['Pirmadienis', 'Antradienis', 'Trečiadienis', 'Ketvirtadienis', 'Penktadienis'];
        const dayAbbrs = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

        // Merge all student timetables into one class timetable
        const classTimetable = {};
        
        timetablesData.forEach((timetableData, index) => {
            const student = currentClassStudents[index];
            const grid = timetableData.grid || {};

            Object.keys(grid).forEach(lesson => {
                if (!classTimetable[lesson]) {
                    classTimetable[lesson] = {};
                }

                Object.keys(grid[lesson]).forEach(day => {
                    if (!classTimetable[lesson][day]) {
                        classTimetable[lesson][day] = [];
                    }

                    const slot = grid[lesson][day];
                    
                    // Check if this lesson already exists
                    const existingLesson = classTimetable[lesson][day].find(l => 
                        l.subject === slot.subject && 
                        l.teacher === slot.teacher && 
                        l.room === slot.room &&
                        l.group_id === slot.group_id
                    );

                    if (existingLesson) {
                        // Add student to existing lesson
                        existingLesson.students.push(student.full_name);
                    } else {
                        // Create new lesson entry
                        classTimetable[lesson][day].push({
                            subject: slot.subject,
                            teacher: slot.teacher,
                            room: slot.room,
                            room_id: slot.room_id,
                            group_id: slot.group_id,
                            students: [student.full_name]
                        });
                    }
                });
            });
        });

        // Render the timetable
        let html = `
            <table class="class-timetable-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Pamoka</th>
                        ${days.map(day => `<th>${day}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
        `;

        for (let lesson = 1; lesson <= 10; lesson++) {
            html += '<tr>';
            html += `<td class="lesson-slot"><strong>${lesson}</strong></td>`;

            dayAbbrs.forEach(day => {
                const lessons = classTimetable[lesson]?.[day] || [];
                
                if (lessons.length === 0) {
                    html += `<td class="timetable-cell modal-timetable-cell" data-day="${day}" data-slot="${lesson}">-</td>`;
                } else {
                    html += `<td class="timetable-cell modal-timetable-cell" data-day="${day}" data-slot="${lesson}">`;
                    lessons.forEach(lessonData => {
                        const studentsList = lessonData.students.join(', ');
                        const studentsCount = lessonData.students.length;
                        const groupId = lessonData.group_id || '';
                        const groupName = lessonData.group_name || `${lessonData.subject}`;
                        
                        html += `
                            <div class="class-lesson-group" 
                                 data-bs-toggle="tooltip" 
                                 data-bs-placement="top" 
                                 data-bs-html="true"
                                 data-group-id="${groupId}"
                                 onclick="openGroupDetails(${groupId})"
                                 style="cursor: pointer;"
                                 title="<strong>${groupName}</strong><br><em>Mokytojas:</em> ${lessonData.teacher}<br><br><strong>Mokiniai (${studentsCount}):</strong><br>${studentsList}<br><br><em style='color: #0d6efd;'><i class='bi bi-pencil'></i> Spauskite redaguoti</em>">
                                <strong>${groupName}</strong>
                                ${lessonData.room_id ? `<small><a class="room-number-link" onclick="event.stopPropagation(); openRoomTimetable(${lessonData.room_id})" title="Peržiūrėti kabineto tvarkaraštį">${lessonData.room}</a></small>` : (lessonData.room ? `<small>${lessonData.room}</small>` : '')}
                                <span class="students-badge">${studentsCount} mok.</span>
                            </div>
                        `;
                    });
                    html += '</td>';
                }
            });

            html += '</tr>';
        }

        html += `
                </tbody>
            </table>
        `;

        modalContent.innerHTML = html;

        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Initialize hover handlers for modal timetable cells
        const modalCells = modalContent.querySelectorAll('.timetable-cell.modal-timetable-cell');
        modalCells.forEach(cell => {
            cell.addEventListener('mouseenter', function(e) {
                // Don't trigger on tooltip or other elements
                if (e.target.closest('[data-bs-toggle="tooltip"]')) return;
                
                const day = this.dataset.day;
                const slot = this.dataset.slot;
                
                // Highlight all cells with same day and slot
                if (day && slot) {
                    modalCells.forEach(c => {
                        if (c.dataset.day === day && c.dataset.slot === slot) {
                            c.classList.add('modal-timetable-cell-highlighted');
                        }
                    });
                }
            });

            cell.addEventListener('mouseleave', function() {
                // Remove all highlights
                modalCells.forEach(c => c.classList.remove('modal-timetable-cell-highlighted'));
            });
        });
    }

    // Open group details page
    function openGroupDetails(groupId) {
        if (!groupId) return;
        
        const schoolId = <?php echo e($school->id); ?>;
        const currentTimetableId = <?php echo e($timetable->id); ?>;
        
        // Open group details page in new tab
        window.open(`/admin/schools/${schoolId}/timetables/${currentTimetableId}/groups/${groupId}/details`, '_blank');
    }

    // Open student timetable
    function openStudentTimetable(studentId, studentName) {
        if (!studentId) return;
        
        const schoolId = <?php echo e($school->id); ?>;
        const currentTimetableId = <?php echo e($timetable->id); ?>;
        
        // Open student timetable page in new tab
        window.open(`/admin/schools/${schoolId}/timetables/${currentTimetableId}/student/${studentId}`, '_blank');
    }

    // Open room timetable
    function openRoomTimetable(roomId) {
        if (!roomId) return;
        
        const schoolId = <?php echo e($school->id); ?>;
        const currentTimetableId = <?php echo e($timetable->id); ?>;
        
        // Open room timetable page in new tab
        window.open(`/admin/schools/${schoolId}/timetables/${currentTimetableId}/room/${roomId}`, '_blank');
    }

    // Checkbox change event
    document.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox' && e.target.classList.contains('student-checkbox')) {
            const studentId = parseInt(e.target.dataset.studentId);
            const studentName = e.target.dataset.studentName;
            const parentItem = e.target.closest('.student-checkbox-item');
            
            if (e.target.checked) {
                // Add to selected
                if (!selectedStudents.find(s => s.id === studentId)) {
                    selectedStudents.push({ id: studentId, full_name: studentName });
                }
                if (parentItem) parentItem.classList.add('selected');
            } else {
                // Remove from selected
                selectedStudents = selectedStudents.filter(s => s.id !== studentId);
                if (parentItem) parentItem.classList.remove('selected');
            }
            
            // Update selected count
            updateSelectedCount();
            
            // Update button state
            const viewBtn = document.getElementById('viewSelectedTimetablesBtn');
            if (selectedStudents.length > 0) {
                viewBtn.disabled = false;
                viewBtn.style.display = 'block';
            } else {
                viewBtn.disabled = true;
                viewBtn.style.display = 'none';
            }
        }
    });
    
    // Update selected count display
    function updateSelectedCount() {
        const countSpan = document.getElementById('selectedCount');
        const previewList = document.getElementById('selectedStudentsPreviewList');
        
        if (selectedStudents.length > 0) {
            countSpan.textContent = `Pasirinkta ${selectedStudents.length} mokinių`;
            countSpan.style.display = 'inline';
            
            // Build preview list
            const previewHtml = selectedStudents.map(student => `
                <div class="selected-student-badge">
                    <span class="selected-student-badge-name">${student.full_name}</span>
                    <button type="button" class="btn-remove-student-preview" data-student-id="${student.id}" title="Atžymėti mokinį">×</button>
                </div>
            `).join('');
            
            previewList.innerHTML = previewHtml;
            previewList.style.display = 'grid';
            
            // Add click handlers to remove buttons
            document.querySelectorAll('.btn-remove-student-preview').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const studentId = parseInt(this.dataset.studentId);
                    removeStudentFromList(studentId);
                });
            });
        } else {
            countSpan.style.display = 'none';
            previewList.style.display = 'none';
            previewList.innerHTML = '';
        }
    }
    
    // Remove student from list (for preview)
    function removeStudentFromList(studentId) {
        const checkbox = document.querySelector(`.student-checkbox[data-student-id="${studentId}"]`);
        if (checkbox) {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    // View selected students timetables
    document.getElementById('viewSelectedTimetablesBtn').addEventListener('click', function() {
        loadSelectedStudentsTimetables();
    });

    // Load and display selected students' timetables
    async function loadSelectedStudentsTimetables() {
        if (selectedStudents.length === 0) return;
        
        const schoolId = <?php echo e($school->id); ?>;
        const gridContainer = document.getElementById('selectedStudentsTimetablesGrid');
        gridContainer.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm"></span> Kraunasi...</div>';
        
        try {
            // Load timetables for all selected students
            const timetablePromises = selectedStudents.map(student =>
                fetch(`/admin/api/timetables/${timetableId}/student/${student.id}`)
                    .then(r => r.json())
                    .then(data => ({ ...student, timetable: data }))
                    .catch(err => {
                        console.error(`Klaida kraunant mokinį ${student.full_name}:`, err);
                        return { ...student, timetable: null };
                    })
            );
            
            const results = await Promise.all(timetablePromises);
            
            // Store timetable data for comparison
            window.timetablesForComparison = results.filter(r => r.timetable && r.timetable.grid);
            window.comparisonState = {
                selectedStudents: new Set(),
                comparisonActive: false
            };
            
            // Build selected students list
            const selectedStudentsHtml = selectedStudents.map(student => `
                <div class="selected-student-badge">
                    <span class="selected-student-badge-name">${student.full_name}</span>
                    <button type="button" class="btn-remove-student" data-student-id="${student.id}" title="Atžymėti mokinį">×</button>
                </div>
            `).join('');
            document.getElementById('selectedStudentsList').innerHTML = selectedStudentsHtml;
            
            // Add click handlers to remove buttons
            document.querySelectorAll('.btn-remove-student').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const studentId = parseInt(this.dataset.studentId);
                    removeSelectedStudent(studentId);
                });
            });
            
            // Build comparison checkboxes
            const checkboxesHtml = window.timetablesForComparison.map((student, idx) => `
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" class="comparison-checkbox" data-student-id="${student.id}" data-student-index="${idx}">
                    <span>${student.full_name}</span>
                </label>
            `).join('');
            document.getElementById('comparisonCheckboxes').innerHTML = checkboxesHtml;
            
            // Handle comparison checkbox changes
            document.querySelectorAll('.comparison-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        window.comparisonState.selectedStudents.add(parseInt(this.dataset.studentId));
                    } else {
                        window.comparisonState.selectedStudents.delete(parseInt(this.dataset.studentId));
                    }
                    
                    if (window.comparisonState.selectedStudents.size >= 2) {
                        document.getElementById('compareBtn').style.display = 'block';
                    } else {
                        document.getElementById('compareBtn').style.display = 'none';
                    }
                });
            });
            
            // Build HTML for timetables - responsive layout
            let html = '';
            window.timetablesForComparison.forEach((result, idx) => {
                if (!result.timetable || !result.timetable.grid) {
                    return;
                }
                
                html += `<div class="student-timetable-card-responsive" data-student-id="${result.id}">
                    <div class="student-timetable-card-header">
                        <h6>${result.full_name}</h6>
                    </div>
                    <table class="table student-timetable-small">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Pamoka</th>
                                <th>Pirm</th>
                                <th>Ant</th>
                                <th>Tre</th>
                                <th>Ket</th>
                                <th>Pen</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                // Render lessons
                const lessonNumbers = Object.keys(result.timetable.grid).map(Number).sort((a, b) => a - b);
                const dayAbbreviations = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
                
                lessonNumbers.forEach(lessonNum => {
                    const lessons = result.timetable.grid[lessonNum];
                    html += `<tr><td style="font-weight: bold;">${lessonNum}</td>`;
                    
                    dayAbbreviations.forEach(day => {
                        const lessonData = lessons[day];
                        if (lessonData) {
                            const groupName = lessonData.group_name || lessonData.subject;
                            const roomLink = lessonData.room_id ? 
                                `<small><a class="room-number-link" onclick="openRoomTimetable(${lessonData.room_id})" title="Peržiūrėti kabineto tvarkaraštį">${lessonData.room}</a></small>` : 
                                (lessonData.room ? `<small>${lessonData.room}</small>` : '');
                            html += `<td class="timetable-cell lesson-cell-small comparison-cell" data-student-id="${result.id}" data-day="${day}" data-slot="${lessonNum}" data-group-id="${lessonData.group_id}" data-subject="${lessonData.subject}" data-teacher="${lessonData.teacher}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="Mokytojas: ${lessonData.teacher}" style="cursor: pointer; position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;">
                                    <div onclick="event.stopPropagation(); openGroupDetails(${lessonData.group_id})" style="flex: 1;">
                                        <strong>${groupName}</strong>
                                        ${roomLink}
                                    </div>
                                </div>
                            </td>`;
                        } else {
                            html += `<td class="timetable-cell lesson-cell-small comparison-cell" data-student-id="${result.id}" data-day="${day}" data-slot="${lessonNum}"></td>`;
                        }
                    });
                    
                    html += '</tr>';
                });
                
                html += `</tbody>
                    </table>
                </div>`;
            });
            
            gridContainer.innerHTML = html || '<p class="text-center text-muted">Nėra duomenų rodyti</p>';
            
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(tooltipTriggerEl => {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Setup compare button
            document.getElementById('compareBtn').addEventListener('click', function() {
                if (window.comparisonState.selectedStudents.size < 2) {
                    alert('Pasirinkite bent 2 tvarkaraščius lyginimui');
                    return;
                }
                performComparison();
            });
            
            // Setup clear comparison button
            document.getElementById('clearComparisonBtn').addEventListener('click', function() {
                clearComparison();
            });
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('selectedStudentsTimetablesModal'));
            modal.show();
        } catch (error) {
            console.error('Klaida:', error);
            gridContainer.innerHTML = '<p class="text-danger">Įvyko klaida kraunant duomenis</p>';
        }
    }

    // Remove selected student
    function removeSelectedStudent(studentId) {
        selectedStudents = selectedStudents.filter(s => s.id !== studentId);
        
        // Uncheck the checkbox
        const checkbox = document.querySelector(`.student-checkbox[data-student-id="${studentId}"]`);
        if (checkbox) {
            checkbox.checked = false;
        }
        
        // Update count
        updateSelectedCount();
        
        // Reload if modal is open
        const modal = document.getElementById('selectedStudentsTimetablesModal');
        if (modal.classList.contains('show')) {
            loadSelectedStudentsTimetables();
        }
    }

    // Perform comparison
    function performComparison() {
        const selectedIds = Array.from(window.comparisonState.selectedStudents);
        const selectedData = window.timetablesForComparison.filter(s => selectedIds.includes(s.id));
        
        // Clear previous comparison highlighting
        clearComparison();
        
        // Mark comparison as active
        window.comparisonState.comparisonActive = true;
        document.getElementById('compareBtn').style.display = 'none';
        document.getElementById('clearComparisonBtn').style.display = 'block';
        
        // Get all cells and compare
        const allCells = document.querySelectorAll('.comparison-cell');
        
        allCells.forEach(cell => {
            const day = cell.dataset.day;
            const slot = cell.dataset.slot;
            const studentId = parseInt(cell.dataset.studentId);
            
            // Skip if not in selected students
            if (!selectedIds.includes(studentId)) return;
            
            // Get all cells with same day and slot from selected students
            const sameCells = Array.from(allCells).filter(c => 
                c.dataset.day === day && 
                c.dataset.slot === slot &&
                selectedIds.includes(parseInt(c.dataset.studentId))
            );
            
            if (sameCells.length < 2) return;
            
            // Get lesson content for comparison
            const lessonContents = sameCells.map(c => ({
                subject: c.dataset.subject,
                teacher: c.dataset.teacher,
                content: c.textContent.trim()
            }));
            
            // Check if all are the same
            const firstContent = lessonContents[0];
            const allSame = lessonContents.every(l => 
                l.subject === firstContent.subject && 
                l.teacher === firstContent.teacher
            );
            
            // Apply colors
            sameCells.forEach(cell => {
                cell.classList.remove('comparison-same', 'comparison-different');
                if (firstContent.content === '' && firstContent.content === firstContent.content) {
                    // Both empty
                } else if (firstContent.content !== '') {
                    if (allSame) {
                        cell.classList.add('comparison-same');
                    } else {
                        cell.classList.add('comparison-different');
                    }
                }
            });
        });
    }

    // Clear comparison highlighting
    function clearComparison() {
        document.querySelectorAll('.comparison-cell').forEach(cell => {
            cell.classList.remove('comparison-same', 'comparison-different');
        });
        
        window.comparisonState.comparisonActive = false;
        document.getElementById('compareBtn').style.display = 'block';
        document.getElementById('clearComparisonBtn').style.display = 'none';
    }

    // Show timetable comparison view

</script>

<?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/timetables/partials/class-timetable-review.blade.php ENDPATH**/ ?>