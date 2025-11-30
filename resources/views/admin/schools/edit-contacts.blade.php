@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $school->name }} - Kontaktinė informacija</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Redaguoti kontaktus</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('schools.update-contacts', $school) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefonas</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $school->phone) }}"
                                   placeholder="Pvz. +370 1 234 5678">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">El. paštas</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $school->email) }}"
                                   placeholder="Pvz. info@mokykla.lt">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Išsaugoti
                            </button>
                            <!-- Dashboard cancel button removed -->
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Mokyklos informacija</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Pavadinimas:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $school->name }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Adresas:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $school->address ?? 'Nenurodyta' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Telefonas:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $school->phone ?? 'Nenurodyta' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>El. paštas:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $school->email ?? 'Nenurodyta' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <strong>Sukurta:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $school->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
