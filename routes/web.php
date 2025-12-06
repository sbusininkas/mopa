<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UserLoginKeyController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Routes (Protected)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User profile - activate login keys
    Route::get('/profile/activate-key', [UserLoginKeyController::class, 'showActivateForm'])->name('profile.activate-key');
    Route::post('/profile/activate-key', [UserLoginKeyController::class, 'activate'])->name('profile.activate-key.store');
    Route::get('/profile/my-schools', [UserLoginKeyController::class, 'mySchools'])->name('profile.my-schools');
    Route::post('/profile/deactivate-key/{loginKey}', [UserLoginKeyController::class, 'deactivate'])->name('profile.deactivate-key');
});

// Admin Routes (Protected - Admin only)
Route::middleware(['auth'])->group(function () {
    // allow system admins full access; school admins will be limited by controller logic
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/admin/users/search', [UserManagementController::class, 'search'])->name('users.search');
    Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::post('/admin/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
});

    Route::post('/admin/timetables/{timetable}/generate', [\App\Http\Controllers\TimetableController::class, 'generate'])
        ->name('timetables.generate');
    Route::post('/admin/timetables/{timetable}/add-random-groups', [\App\Http\Controllers\TimetableController::class, 'addRandomGroups'])
        ->name('timetables.add-random-groups');
    Route::get('/admin/timetables/{timetable}/generation-status', [\App\Http\Controllers\TimetableController::class, 'generationStatus'])
        ->name('timetables.generation-status');
// Admin Schools management
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/schools', [\App\Http\Controllers\SchoolController::class, 'index'])->name('schools.index');
    Route::get('/admin/schools/create', [\App\Http\Controllers\SchoolController::class, 'create'])->name('schools.create');
    Route::post('/admin/schools', [\App\Http\Controllers\SchoolController::class, 'store'])->name('schools.store');
    // editing/updating a specific school is allowed to system admins or that school's admins (checked in controller)
    Route::get('/admin/schools/{school}/edit', [\App\Http\Controllers\SchoolController::class, 'edit'])->name('schools.edit');
    Route::post('/admin/schools/{school}', [\App\Http\Controllers\SchoolController::class, 'update'])->name('schools.update');
    Route::delete('/admin/schools/{school}', [\App\Http\Controllers\SchoolController::class, 'destroy'])->name('schools.destroy');
    
    // School contacts (phone, email) - for school admins
    Route::get('/admin/schools/{school}/edit-contacts', [\App\Http\Controllers\SchoolController::class, 'editContacts'])->name('schools.edit-contacts');
    Route::post('/admin/schools/{school}/update-contacts', [\App\Http\Controllers\SchoolController::class, 'updateContacts'])->name('schools.update-contacts');
    
    // Switch active school
    Route::post('/admin/schools/{school}/switch', [\App\Http\Controllers\SchoolSwitchController::class, 'switch'])->name('schools.switch');
});

// School admin + supervisor routes
Route::middleware(['auth', 'school.admin.or.supervisor'])->group(function () {
    // Dashboard route removed
    // School dashboard (per-school admin landing)
    Route::get('/admin/schools/{school}/dashboard', [\App\Http\Controllers\SchoolController::class, 'dashboard'])->name('schools.dashboard');
    Route::get('/admin/schools/{school}/classes', [\App\Http\Controllers\ClassController::class, 'index'])->name('schools.classes.index');
    Route::get('/admin/schools/{school}/classes/create', [\App\Http\Controllers\ClassController::class, 'create'])->name('schools.classes.create');
    Route::post('/admin/schools/{school}/classes', [\App\Http\Controllers\ClassController::class, 'store'])->name('schools.classes.store');
    Route::get('/admin/schools/{school}/classes/{class}', [\App\Http\Controllers\ClassController::class, 'show'])->name('schools.classes.show');
    Route::get('/admin/schools/{school}/classes/{class}/edit', [\App\Http\Controllers\ClassController::class, 'edit'])->name('schools.classes.edit');
    Route::post('/admin/schools/{school}/classes/{class}', [\App\Http\Controllers\ClassController::class, 'update'])->name('schools.classes.update');
    Route::delete('/admin/schools/{school}/classes/{class}', [\App\Http\Controllers\ClassController::class, 'destroy'])->name('schools.classes.destroy');
    Route::get('/admin/schools/{school}/login-keys', [\App\Http\Controllers\LoginKeyController::class, 'index'])->name('schools.login-keys.index');
    Route::get('/admin/schools/{school}/login-keys/import', [\App\Http\Controllers\LoginKeyController::class, 'import'])->name('schools.login-keys.import');
    Route::get('/admin/schools/{school}/login-keys/search', [\App\Http\Controllers\LoginKeyController::class, 'search'])->name('schools.login-keys.search');
    Route::get('/admin/schools/{school}/login-keys/export-pdf', [\App\Http\Controllers\LoginKeyController::class, 'exportPdf'])->name('schools.login-keys.export-pdf');
    Route::post('/admin/schools/{school}/login-keys/import-students', [\App\Http\Controllers\LoginKeyController::class, 'storeStudentImport'])->name('schools.login-keys.import-students');
    Route::post('/admin/schools/{school}/login-keys/import-teachers', [\App\Http\Controllers\LoginKeyController::class, 'storeTeacherImport'])->name('schools.login-keys.import-teachers');
    Route::post('/admin/schools/{school}/login-keys/store-student', [\App\Http\Controllers\LoginKeyController::class, 'storeStudent'])->name('schools.login-keys.store-student');
    Route::post('/admin/schools/{school}/login-keys/{loginKey}/update-student', [\App\Http\Controllers\LoginKeyController::class, 'updateStudent'])->name('schools.login-keys.update-student');
    Route::post('/admin/schools/{school}/login-keys/store-teacher', [\App\Http\Controllers\LoginKeyController::class, 'storeTeacher'])->name('schools.login-keys.store-teacher');
    Route::post('/admin/schools/{school}/login-keys/bulk-regenerate', [\App\Http\Controllers\LoginKeyController::class, 'bulkRegenerate'])->name('schools.login-keys.bulk-regenerate');
    Route::post('/admin/schools/{school}/login-keys/bulk-delete', [\App\Http\Controllers\LoginKeyController::class, 'bulkDelete'])->name('schools.login-keys.bulk-delete');
    Route::post('/admin/schools/{school}/login-keys/generate', [\App\Http\Controllers\LoginKeyController::class, 'generate'])->name('schools.login-keys.generate');
    Route::post('/admin/schools/{school}/login-keys/{loginKey}/regenerate', [\App\Http\Controllers\LoginKeyController::class, 'regenerate'])->name('schools.login-keys.regenerate');
    Route::delete('/admin/schools/{school}/login-keys/{loginKey}', [\App\Http\Controllers\LoginKeyController::class, 'destroy'])->name('schools.login-keys.destroy');
    Route::get('/admin/schools/{school}/subjects', [\App\Http\Controllers\SubjectController::class, 'index'])->name('schools.subjects.index');
    Route::post('/admin/schools/{school}/subjects', [\App\Http\Controllers\SubjectController::class, 'store'])->name('schools.subjects.store');
    Route::post('/admin/schools/{school}/subjects/{subject}/edit', [\App\Http\Controllers\SubjectController::class, 'update'])->name('schools.subjects.update');
    Route::delete('/admin/schools/{school}/subjects/{subject}', [\App\Http\Controllers\SubjectController::class, 'destroy'])->name('schools.subjects.destroy');
    Route::post('/admin/schools/{school}/subjects/add-defaults', [\App\Http\Controllers\SubjectController::class, 'addDefaults'])->name('schools.subjects.add-defaults');
    Route::post('/admin/schools/{school}/subjects/bulk-delete', [\App\Http\Controllers\SubjectController::class, 'bulkDelete'])->name('schools.subjects.bulk-delete');
    
    // Rooms management
    Route::get('/admin/schools/{school}/rooms', [\App\Http\Controllers\RoomController::class, 'index'])->name('schools.rooms.index');
    Route::post('/admin/schools/{school}/rooms', [\App\Http\Controllers\RoomController::class, 'store'])->name('schools.rooms.store');
    Route::post('/admin/schools/{school}/rooms/{room}/edit', [\App\Http\Controllers\RoomController::class, 'update'])->name('schools.rooms.update');
    Route::delete('/admin/schools/{school}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('schools.rooms.destroy');
    Route::post('/admin/schools/{school}/rooms/bulk-delete', [\App\Http\Controllers\RoomController::class, 'bulkDelete'])->name('schools.rooms.bulk-delete');
    Route::get('/admin/schools/{school}/rooms/import', [\App\Http\Controllers\RoomController::class, 'import'])->name('schools.rooms.import');
    Route::post('/admin/schools/{school}/rooms/import-excel', [\App\Http\Controllers\RoomController::class, 'importExcel'])->name('schools.rooms.import-excel');

    // Timetables
    Route::get('/admin/schools/{school}/timetables', [\App\Http\Controllers\TimetableController::class, 'index'])->name('schools.timetables.index');
    Route::post('/admin/schools/{school}/timetables', [\App\Http\Controllers\TimetableController::class, 'store'])->name('schools.timetables.store');
    Route::get('/admin/schools/{school}/timetables/{timetable}', [\App\Http\Controllers\TimetableController::class, 'show'])->name('schools.timetables.show');
    Route::get('/admin/schools/{school}/timetables/{timetable}/teachers-view', [\App\Http\Controllers\TimetableController::class, 'teachersView'])->name('schools.timetables.teachers-view');
    Route::get('/admin/schools/{school}/timetables/{timetable}/teacher/{teacher}', [\App\Http\Controllers\TimetableController::class, 'teacherView'])
        ->whereNumber('teacher')
        ->name('schools.timetables.teacher');
    Route::get('/admin/schools/{school}/timetables/{timetable}/unscheduled', [\App\Http\Controllers\TimetableController::class, 'unscheduled'])->name('schools.timetables.unscheduled');
    Route::get('/admin/schools/{school}/timetables/{timetable}/unscheduled-html', [\App\Http\Controllers\TimetableController::class, 'unscheduledHtml'])->name('schools.timetables.unscheduled-html');
    Route::post('/admin/schools/{school}/timetables/{timetable}/check-conflict', [\App\Http\Controllers\TimetableController::class, 'checkConflict'])->name('schools.timetables.check-conflict');
    Route::post('/admin/schools/{school}/timetables/{timetable}/manual-slot', [\App\Http\Controllers\TimetableController::class, 'storeManualSlot'])->name('schools.timetables.manual-slot');
    Route::post('/admin/schools/{school}/timetables/{timetable}/manual-slot-alt-room', [\App\Http\Controllers\TimetableController::class, 'storeManualSlotWithAlternativeRoom'])->name('schools.timetables.manual-slot-alt-room');
    Route::post('/admin/schools/{school}/timetables/{timetable}/bulk-conflicts', [\App\Http\Controllers\TimetableController::class, 'bulkCheckConflicts'])->name('schools.timetables.bulk-conflicts');
    Route::post('/admin/schools/{school}/timetables/{timetable}/unschedule-slot', [\App\Http\Controllers\TimetableController::class, 'unscheduleSlot'])->name('schools.timetables.unschedule-slot');
    Route::post('/admin/schools/{school}/timetables/{timetable}/move-slot', [\App\Http\Controllers\TimetableController::class, 'moveSlot'])->name('schools.timetables.move-slot');
    Route::post('/admin/schools/{school}/timetables/{timetable}/update', [\App\Http\Controllers\TimetableController::class, 'update'])->name('schools.timetables.update');
    Route::post('/admin/schools/{school}/timetables/{timetable}/set-public', [\App\Http\Controllers\TimetableController::class, 'setPublic'])->name('schools.timetables.set-public');
    Route::post('/admin/schools/{school}/timetables/{timetable}/copy', [\App\Http\Controllers\TimetableController::class, 'copy'])->name('schools.timetables.copy');
    Route::delete('/admin/schools/{school}/timetables/{timetable}', [\App\Http\Controllers\TimetableController::class, 'destroy'])->name('schools.timetables.destroy');
    
    // Teacher working days
    Route::get('/admin/schools/{school}/timetables/{timetable}/teacher-working-days', [\App\Http\Controllers\TimetableController::class, 'getTeacherWorkingDays'])->name('schools.timetables.teacher-working-days');
    Route::post('/admin/schools/{school}/timetables/{timetable}/teacher-working-days', [\App\Http\Controllers\TimetableController::class, 'updateTeacherWorkingDays'])->name('schools.timetables.update-teacher-working-days');
    Route::get('/admin/schools/{school}/timetables/{timetable}/all-teachers-working-days', [\App\Http\Controllers\TimetableController::class, 'allTeachersWorkingDays'])->name('schools.timetables.all-teachers-working-days');

    // Timetable groups
    Route::get('/admin/schools/{school}/timetables/{timetable}/groups-list', [\App\Http\Controllers\TimetableGroupController::class, 'list'])->name('schools.timetables.groups.list');
    Route::post('/admin/schools/{school}/timetables/{timetable}/groups', [\App\Http\Controllers\TimetableGroupController::class, 'store'])->name('schools.timetables.groups.store');
    Route::get('/admin/schools/{school}/timetables/{timetable}/groups/{group}', [\App\Http\Controllers\TimetableGroupController::class, 'show'])->name('schools.timetables.groups.show');
    Route::get('/admin/schools/{school}/timetables/{timetable}/groups/{group}/edit-data', [\App\Http\Controllers\TimetableGroupController::class, 'editData'])->name('schools.timetables.groups.edit-data');
    Route::put('/admin/schools/{school}/timetables/{timetable}/groups/{group}/update', [\App\Http\Controllers\TimetableGroupController::class, 'update'])->name('schools.timetables.groups.update');
    Route::delete('/admin/schools/{school}/timetables/{timetable}/groups/{group}', [\App\Http\Controllers\TimetableGroupController::class, 'destroy'])->name('schools.timetables.groups.destroy');
    Route::post('/admin/schools/{school}/timetables/{timetable}/groups/{group}/assign-students', [\App\Http\Controllers\TimetableGroupController::class, 'assignStudents'])->name('schools.timetables.groups.assign-students');
    Route::get('/admin/schools/{school}/timetables/{timetable}/groups/{group}/students', [\App\Http\Controllers\TimetableGroupController::class, 'getStudents'])->name('schools.timetables.groups.students');
    Route::post('/admin/schools/{school}/timetables/{timetable}/groups/{group}/copy-unscheduled', [\App\Http\Controllers\TimetableGroupController::class, 'copyUnscheduled'])->name('schools.timetables.groups.copy-unscheduled');

    // Admin API: students by class (used in timetable UI)
    Route::get('/admin/api/classes/{class}/students', [\App\Http\Controllers\AdminApiController::class, 'studentsByClass'])->name('admin.api.classes.students');
    Route::get('/admin/api/schools/{school}/students', [\App\Http\Controllers\AdminApiController::class, 'allStudents'])->name('admin.api.schools.students');
    Route::get('/admin/api/schools/{school}/students/search', [\App\Http\Controllers\AdminApiController::class, 'searchStudents'])->name('admin.api.schools.students.search');
});

// New routes without {school} parameter - using active school from session
Route::middleware(['auth', 'active.school'])->group(function () {
    // School dashboard
    Route::get('/school/dashboard', [\App\Http\Controllers\ActiveSchoolController::class, 'dashboard'])->name('school.dashboard');
    
    // Classes
    Route::get('/school/classes', [\App\Http\Controllers\ActiveSchoolController::class, 'classes'])->name('classes.index');
    
    // Timetables
    Route::get('/school/timetables', [\App\Http\Controllers\ActiveSchoolController::class, 'timetables'])->name('timetables.index');
    
    // Login Keys
    Route::get('/school/login-keys', [\App\Http\Controllers\ActiveSchoolController::class, 'loginKeys'])->name('login-keys.index');
    
    // Subjects  
    Route::get('/school/subjects', [\App\Http\Controllers\ActiveSchoolController::class, 'subjects'])->name('subjects.index');
    
    // Rooms
    Route::get('/school/rooms', [\App\Http\Controllers\ActiveSchoolController::class, 'rooms'])->name('rooms.index');
    
    // Import
    Route::get('/school/import', [\App\Http\Controllers\ActiveSchoolController::class, 'import'])->name('import.index');
    
    // Settings
    Route::get('/school/settings', [\App\Http\Controllers\ActiveSchoolController::class, 'settings'])->name('school.settings');
    Route::get('/school/contacts', [\App\Http\Controllers\ActiveSchoolController::class, 'contacts'])->name('school.contacts');
});
