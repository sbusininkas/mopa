@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>{{ $school->exists ? 'Redaguoti mokyklą' : 'Sukurti mokyklą' }}</h3>
        <a href="{{ route('schools.index') }}" class="btn btn-secondary">Atgal</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $school->exists ? route('schools.update', $school) : route('schools.store') }}">
        @csrf
        @if($school->exists)
            @method('POST')
        @endif

        <div class="mb-3">
            <label class="form-label">Pavadinimas</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $school->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Adresas</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $school->address) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Telefonas</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
        </div>

        @if(auth()->user()->isSupervisor())
        <hr>
        <h5>Priskirti vartotojai</h5>
        <p class="text-muted">Pasirinkite vartotojus, kuriuos priskirti šiai mokyklai. Pažymėkite administratorių varčiu.</p>

        <div class="mb-3">
            @foreach($users as $user)
                @php
                    $attached = $school->users->firstWhere('id', $user->id);
                    $isChecked = (bool) $attached;
                    $isAdmin = $attached ? (bool) $attached->pivot->is_admin : false;
                @endphp
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}" {{ $isChecked ? 'checked' : '' }}>
                    <label class="form-check-label" for="user_{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</label>
                    <div class="form-check form-check-inline ms-3">
                        <input class="form-check-input" type="checkbox" name="admins[]" value="{{ $user->id }}" id="admin_{{ $user->id }}" {{ $isAdmin ? 'checked' : '' }}>
                        <label class="form-check-label" for="admin_{{ $user->id }}">Admin</label>
                    </div>
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary">Išsaugoti</button>
        @else
        <div class="alert alert-info">Tik prižiūrėtojas gali priskirti vartotojus prie mokyklų.</div>
        <button class="btn btn-primary">Išsaugoti</button>
        @endif
    </form>
</div>
@endsection
