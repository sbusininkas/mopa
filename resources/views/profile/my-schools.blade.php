@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-4">
        <h2><i class="bi bi-building"></i> Mano mokyklos ir raktai</h2>
        <a href="{{ route('profile.activate-key') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Suaktyvinti naują raktą
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- User Info -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-person"></i> Jūsų informacija
        </div>
        <div class="card-body">
            <p>
                <strong>Vardas:</strong> {{ auth()->user()->name }}<br>
                <strong>El. paštas:</strong> {{ auth()->user()->email }}<br>
                <strong>Pagrindinė rolė:</strong> 
                @if(auth()->user()->isSupervisor())
                    <span class="badge bg-warning">Sistemos Priežiūrėtojas</span>
                @elseif(auth()->user()->isAdmin())
                    <span class="badge bg-danger">Administratorius</span>
                @elseif(auth()->user()->isTeacher())
                    <span class="badge bg-info">Mokytojas</span>
                @else
                    <span class="badge bg-success">Mokinys</span>
                @endif
            </p>
        </div>
    </div>

    <!-- User's Schools -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-buildings"></i> Mokyklos, kuriose jūs registruoti
        </div>
        <div class="card-body">
            @if($schools->isEmpty())
                <div class="alert alert-info">
                    Jūs dar neprisijungėte prie jokios mokyklos. 
                    <a href="{{ route('profile.activate-key') }}">Suaktyvinkite login raktą</a>.
                </div>
            @else
                <div class="row">
                    @foreach($schools as $school)
                        <div class="col-md-6 mb-3">
                            <div class="card border-left border-success">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $school->name }}</h5>
                                    <p class="small text-muted">
                                        <i class="bi bi-geo-alt"></i> {{ $school->address }}<br>
                                        <i class="bi bi-telephone"></i> {{ $school->phone }}
                                    </p>
                                    <p class="mb-2">
                                        <strong>Jūsų rolė:</strong>
                                        @php
                                            $userKeys = auth()->user()->loginKeys()->where('school_id', $school->id)->get();
                                            $roles = $userKeys->pluck('type')->unique()->map(fn($t) => $t === 'teacher' ? 'Mokytojas' : 'Mokinys')->join(', ');
                                        @endphp
                                        <span class="badge bg-info">{{ $roles ?: 'Nežinoma' }}</span>
                                    </p>
                                    @if($school->classes->count() > 0)
                                        <p class="mb-0">
                                            <strong>Klasės:</strong> {{ $school->classes->pluck('name')->join(', ') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Active Login Keys -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <i class="bi bi-key"></i> Jūsų aktyvūs raktai
        </div>
        <div class="card-body">
            @if($loginKeys->isEmpty())
                <p class="text-muted">Jūs neturite aktivuotų raktų.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Raktas</th>
                                <th>Tipas</th>
                                <th>Mokykla</th>
                                <th>Klasė</th>
                                <th>Suaktyvinta</th>
                                <th>Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loginKeys as $key)
                                <tr>
                                    <td><code class="bg-light p-1">{{ $key->key }}</code></td>
                                    <td>
                                        <span class="badge {{ $key->type === 'teacher' ? 'bg-info' : 'bg-success' }}">
                                            {{ $key->type === 'teacher' ? 'Mokytojas' : 'Mokinys' }}
                                        </span>
                                    </td>
                                    <td>{{ $key->school->name }}</td>
                                    <td>{{ $key->class ? $key->class->name : '-' }}</td>
                                    <td>{{ $key->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form action="{{ route('profile.deactivate-key', $key) }}" method="POST" class="d-inline" onsubmit="return confirm('Ar tikrai norite pasitraukti iš šios mokyklos?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i> Palieti
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Atgal į dashboard</a>
    </div>
</div>
@endsection
