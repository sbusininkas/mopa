@extends('layouts.admin')

@section('content')
    <h3>{{ $class->exists ? 'Redaguoti klasę' : 'Sukurti naują klasę' }}</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $class->exists ? route('schools.classes.update', [$school, $class]) : route('schools.classes.store', $school) }}">
        @csrf
        @if($class->exists)
            @method('POST')
        @endif

        <div class="mb-3">
            <label class="form-label">Klasės pavadinimas *</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name', $class->name) }}" required placeholder="pvz. 1A, 2B, 3C">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Aprašymas</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                      rows="4" placeholder="Iš viso mokinių, pagrindinė kryptis, etc.">{{ old('description', $class->description) }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                {{ $class->exists ? 'Atnaujinti klasę' : 'Sukurti klasę' }}
            </button>
        </div>
    </form>
</div>
@endsection
