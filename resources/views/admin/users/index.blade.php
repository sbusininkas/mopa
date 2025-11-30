@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="mb-4">
        <h2><i class="bi bi-people"></i> Vartotojų sąrašas</h2>
    </div>

    <div class="modern-table-wrapper">
        <table class="modern-table table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-person"></i> Vardas</th>
                    <th><i class="bi bi-envelope"></i> El. paštas</th>
                    <th><i class="bi bi-shield"></i> Rolė</th>
                    <th class="text-end">Veiksmai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-modern bg-info">{{ $user->role }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Redaguoti
                                </a>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $user->id }}">
                                    <i class="bi bi-trash"></i> Trinti
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Delete Modal for each user -->
                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-exclamation-triangle"></i> Patvirtinti šalinimą
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    Ar tikrai norite ištrinti vartotoją <strong>{{ $user->name }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Ištrinti</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
