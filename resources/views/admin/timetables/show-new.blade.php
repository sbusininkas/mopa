@extends('layouts.admin')

@section('content')
<style>
.hover-bg-light:hover {
    background-color: #f8f9fa !important;
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

    <!-- Unscheduled lessons will be loaded via AJAX -->
    <div id="unscheduledLessonsContainer">
        @include('admin.timetables.partials.unscheduled-lessons-loader')
    </div>

    @include('admin.timetables.partials.settings-modal')

    @include('admin.timetables.partials.teacher-working-days')

    @include('admin.timetables.partials.group-create-form')

    @include('admin.timetables.partials.groups-list')
</div>

@include('admin.timetables.partials.scripts')
@endsection
