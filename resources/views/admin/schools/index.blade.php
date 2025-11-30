@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-building"></i> Mokyklos</h2>
        <a href="{{ route('schools.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Sukurti naują
        </a>
    </div>

    <div class="d-flex justify-content-end mb-3">
        @include('partials.active_school')
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="modern-table-wrapper">
        <table class="modern-table table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-building"></i> Pavadinimas</th>
                    <th><i class="bi bi-geo-alt"></i> Adresas</th>
                    <th><i class="bi bi-telephone"></i> Telefonas</th>
                    <th><i class="bi bi-people"></i> Vartotojai</th>
                    <th class="text-end">Veiksmai</th>
                </tr>
            </thead>
        <tbody>
        @foreach($schools as $school)
            <tr>
                <td>{{ $school->name }}</td>
                <td>{{ $school->address }}</td>
                <td>{{ $school->phone }}</td>
                <td>{{ $school->users_count }}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('schools.edit', $school) }}" class="btn btn-outline-secondary" title="Redaguoti mokyklą">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="{{ route('schools.classes.index', $school) }}" class="btn btn-outline-info" title="Valdyti klases">
                            <i class="bi bi-diagram-3"></i>
                        </a>
                        <a href="{{ route('schools.login-keys.index', $school) }}" class="btn btn-outline-success" title="Valdyti prisijungimo raktus">
                            <i class="bi bi-key"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-action="{{ route('schools.destroy', $school) }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    {{ $schools->links() }}
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patvirtinti šalinimą</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Ar tikrai norite ištrinti šią mokyklą? Šio veiksmo negalima atšaukti.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ištrinti</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteModal').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    document.getElementById('deleteForm').action = action;
});
</script>
@endsection
