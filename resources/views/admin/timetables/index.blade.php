@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar3"></i> Tvarkaraščiai - {{ $school->name }}</h2>
        <form method="POST" action="{{ route('schools.timetables.store', $school) }}" class="d-flex gap-2">
            @csrf
            <input type="text" name="name" class="form-control" placeholder="Naujo tvarkaraščio pavadinimas" required>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Sukurti</button>
        </form>
    </div>

    <div class="modern-table-wrapper">
        <table class="modern-table table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-tag"></i> Pavadinimas</th>
                    <th><i class="bi bi-eye"></i> Viešas</th>
                    <th class="text-end">Veiksmai</th>
                </tr>
            </thead>
            <tbody>
            @forelse($timetables as $t)
                <tr>
                    <td><a href="{{ route('schools.timetables.show', [$school, $t]) }}">{{ $t->name }}</a></td>
                    <td>
                        @if($t->is_public)
                            <span class="badge badge-modern bg-success">Taip</span>
                        @else
                            <span class="badge badge-modern bg-secondary">Ne</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if(!$t->is_public)
                            <form method="POST" action="{{ route('schools.timetables.set-public', [$school, $t]) }}">
                                @csrf
                                <button class="btn btn-outline-success"><i class="bi bi-eye"></i> Rodyti viešai</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('schools.timetables.copy', [$school, $t]) }}">
                                @csrf
                                <button class="btn btn-outline-info"><i class="bi bi-files"></i> Kopijuoti</button>
                            </form>
                            <form method="POST" action="{{ route('schools.timetables.destroy', [$school, $t]) }}" onsubmit="return confirm('Ar tikrai?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Trinti</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Nėra tvarkaraščių</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
