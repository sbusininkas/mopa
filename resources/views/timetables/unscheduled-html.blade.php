@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Unscheduled Timetable Slots</h1>
        <p>School: {{ $school->name }}</p>
        <p>Timetable: {{ $timetable->name }}</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Remaining Lessons</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unscheduled as $u)
                    <tr>
                        <td>{{ $u['group_name'] ?? '-' }}</td>
                        <td>{{ $u['subject_name'] ?? '-' }}</td>
                        <td>{{ $u['teacher_name'] ?? '-' }}</td>
                        <td>{{ $u['remaining_lessons'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No unscheduled slots found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
