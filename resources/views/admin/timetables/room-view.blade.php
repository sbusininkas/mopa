@extends('layouts.admin')

@section('content')
<div style="width: 100%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-door-closed"></i> {{ $room->number }} {{ $room->name }} — Kabineto tvarkaraštis</h2>
        <div class="btn-group">
            <a href="{{ route('schools.timetables.teachers-view', [$school, $timetable]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Atgal
            </a>
            <button id="btnFullscreen" class="btn btn-primary" type="button">
                <i class="bi bi-arrows-fullscreen"></i> Visas ekranas
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-2" style="overflow-x: auto;">
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px; text-align: center; vertical-align: middle;"><strong>Valanda</strong></th>
                        @foreach($days as $code => $label)
                            <th style="text-align: center; width: 150px;"><strong>{{ $label }}</strong></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for($hour = 1; $hour <= $maxHour; $hour++)
                        <tr>
                            <td style="text-align: center; font-weight: bold; vertical-align: middle;">{{ $hour }}</td>
                            @foreach($days as $code => $label)
                                @php
                                    $cell = $slots[$code][$hour] ?? null;
                                    $isLast = $hour === $maxHour;
                                @endphp
                                <td class="text-center lesson-col" style="padding:0.3rem; min-height: 60px; {{ $isLast ? 'border-bottom: 2px solid #999;' : '' }}">
                                    @if($cell)
                                        <div class="p-1" style="background-color: #e8f4f8; border-radius: 4px;">
                                            <div><strong style="font-size: 0.9rem;">{{ $cell['group'] }}</strong></div>
                                            <div><small style="color: #666;">{{ $cell['subject'] ?? '—' }}</small></div>
                                            <div><small style="color: #999;">{{ $cell['teacher_name'] ?? '—' }}</small></div>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('btnFullscreen')?.addEventListener('click', function() {
    const elem = document.querySelector('.card');
    if (elem.requestFullscreen) {
        elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
        elem.webkitRequestFullscreen();
    }
});
</script>
@endsection
