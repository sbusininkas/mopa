@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2><i class="bi bi-key"></i> Suaktyvinti login raktą</h2>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Jei turite prisijungimo raktą iš mokyklos, suaktyvinkite jį čia. Tada turėsite prieigą prie tos mokyklos ir galėsite matyti tvarkaraščius bei kitą informaciją.</p>

                    <form method="POST" action="{{ route('profile.activate-key.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Prisijungimo raktas *</label>
                            <input type="text" name="key" class="form-control form-control-lg @error('key') is-invalid @enderror" 
                                   placeholder="Įveskite 12 ženklų raktą" maxlength="12" required 
                                   style="font-family: monospace; font-size: 1.2rem; letter-spacing: 2px;">
                            <small class="text-muted d-block mt-2">Raktą turėtumėte gauti iš savo mokyklos administratoriaus.</small>
                            @error('key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Suaktyvinti raktą
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('profile.my-schools') }}" class="btn btn-info">
                    <i class="bi bi-building"></i> Mano mokyklos
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    ← Atgal
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-info-circle"></i> Pagalba
                </div>
                <div class="card-body">
                    <p><strong>Kaip gauti raktą?</strong></p>
                    <p class="small">
                        Skaitykitės su savo mokyklos administratoriumi. Jis gali sugeneruoti arba importuoti raktus mokiniams ir mokytojams.
                    </p>

                    <p class="small mt-3"><strong>Raktų formatas:</strong></p>
                    <p class="small text-muted">Raktai yra 12 ženklų ilgio unikalūs kodai.</p>

                    <p class="small mt-3"><strong>Dėl pagalbos:</strong></p>
                    <p class="small">Susisiekite su mokyklos IT administratoriumi.</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-building"></i> Jūsų mokyklos
                </div>
                <div class="card-body">
                    @if(auth()->user()->schools->isEmpty())
                        <p class="text-muted small">Jūs dar neprisijungėte prie jokios mokyklos.</p>
                    @else
                        <ul class="list-unstyled small">
                            @foreach(auth()->user()->schools as $school)
                                <li class="mb-2">
                                    <i class="bi bi-building"></i>
                                    <strong>{{ $school->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $school->address }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
