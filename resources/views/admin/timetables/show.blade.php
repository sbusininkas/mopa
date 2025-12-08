@extends('layouts.admin')

@section('content')
<style>
.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}

.group-name-link,
.unscheduled-group-link,
.unscheduled-subject-link {
    color: #0d6efd;
    text-decoration: none;
    border-bottom: 1px dashed #0d6efd;
    transition: all 0.2s ease;
}

.group-name-link:hover,
.unscheduled-group-link:hover,
.unscheduled-subject-link:hover {
    color: #0b5ed7;
    border-bottom: 1px solid #0b5ed7;
    text-decoration: none;
}
</style>
<div class="container">
    @if(session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
            <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
    
    @include('admin.timetables.partials.header')

    @include('admin.timetables.partials.current-settings')

    @include('admin.timetables.partials.unscheduled-lessons')

    @include('admin.timetables.partials.settings-modal')

    @include('admin.timetables.partials.teacher-working-days')

    @include('admin.timetables.partials.group-create-form')

    @include('admin.timetables.partials.groups-list')
</div>

@include('admin.timetables.partials.scripts')
@endsection
