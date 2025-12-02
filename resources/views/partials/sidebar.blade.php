{{-- Main Sidebar Controller - determines which sidebar to show based on user role --}}
@php
    $user = Auth::user();
    $currentSchool = $school ?? $activeSchool ?? null;
@endphp

{{-- Supervisor (System Administrator) --}}
@if($user->isSupervisor())
    @include('partials.sidebar-supervisor')
    
    {{-- If supervisor has selected a school, show school management menu --}}
    @if($currentSchool)
        <hr class="sidebar-divider my-3">
        @include('partials.sidebar-supervisor-school')
    @endif
@endif

{{-- School Administrator (non-supervisor) --}}
@if(!$user->isSupervisor() && $currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool))
    @include('partials.sidebar-school-admin')
@endif

{{-- Teacher --}}
@if($user->isTeacher() && !$user->isSupervisor() && !($currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool)))
    @include('partials.sidebar-teacher')
@endif

{{-- Student --}}
@if($user->isStudent() && !$user->isSupervisor() && !($currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool)))
    @include('partials.sidebar-student')
@endif

{{-- No role assigned --}}
@if(!$user->isSupervisor() && !$user->isTeacher() && !$user->isStudent() && !($currentSchool && $user->isSchoolAdmin(is_object($currentSchool) ? $currentSchool->id : $currentSchool)))
    <div class="alert alert-warning mx-2" role="alert">
        <i class="bi bi-exclamation-triangle"></i> 
        <strong>Prieiga nesuteikta</strong>
        <p class="mb-0 mt-2 small">Jūs neturite priskirtos rolės. Susisiekite su administratoriumi.</p>
    </div>
@endif
