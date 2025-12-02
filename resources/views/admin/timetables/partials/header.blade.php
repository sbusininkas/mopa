<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mt-2"><i class="bi bi-calendar3"></i> {{ $timetable->name }}</h2>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('schools.timetables.teachers-view', [$school, $timetable]) }}">
            <i class="bi bi-people"></i> Mokytojų tvarkaraštis
        </a>
        <form method="POST" action="{{ route('timetables.add-random-groups', $timetable) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success" onclick="return confirm('Ar tikrai norite pridėti atsitiktines grupes? Tai sukurs naujas grupes su mokiniais, mokytojais ir dalykais.')">
                <i class="bi bi-shuffle"></i> Pridėti random grupes
            </button>
        </form>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timetableSettingsModal">
            <i class="bi bi-gear"></i> Nustatymai
        </button>
        <form method="POST" action="{{ route('timetables.generate', $timetable) }}" class="d-inline" id="generateForm">
            @csrf
            <button type="submit" class="btn btn-primary" id="generateBtn" @if($timetable->generation_status==='running') disabled @endif>
                <span id="btnText">@if($timetable->generation_status==='running') Generuojama... @else Generuoti tvarkaraštį @endif</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm ms-1" style="display: {{ $timetable->generation_status==='running' ? 'inline-block':'none' }};"></span>
            </button>
        </form>
    </div>
</div>
