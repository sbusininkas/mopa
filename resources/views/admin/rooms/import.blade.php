@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="mb-4">
        <h2><i class="bi bi-upload"></i> Importuoti kabinetus: {{ $school->name }}</h2>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <i class="bi bi-file-earmark-excel"></i> Importuoti iš Excel/CSV
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('schools.rooms.import-excel', $school) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label">Excel / CSV failas *</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted mt-2 d-block">
                                <strong>Formatas:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>1-as stulpelis: <strong>Kabineto numeris</strong> (pvz. 358, A101, 2.15)</li>
                                    <li>2-as stulpelis: <strong>Pavadinimas</strong> (pvz. Informatika, Matematika)</li>
                                    <li>Pirma eilutė (antraštė) bus praleista</li>
                                </ul>
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Pavyzdys:</strong>
                            <table class="table table-sm table-bordered mt-2 mb-0" style="background: white;">
                                <thead>
                                    <tr>
                                        <th>Numeris</th>
                                        <th>Pavadinimas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>358</td>
                                        <td>Informatika</td>
                                    </tr>
                                    <tr>
                                        <td>201</td>
                                        <td>Matematika</td>
                                    </tr>
                                    <tr>
                                        <td>A-105</td>
                                        <td>Fizika</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Svarbu:</strong>
                            <ul class="mb-0">
                                <li>Kabinetai su esamais numeriais bus praleisti</li>
                                <li>Tuščios eilutės bus praleistos</li>
                                <li>Numeris ir pavadinimas yra privalomi</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('schools.rooms.index', $school) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Grįžti
                            </a>
                            <button type="submit" class="btn btn-success flex-grow-1">
                                <i class="bi bi-upload"></i> Importuoti kabinetus
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Example Excel template download -->
            <div class="modern-card mt-4">
                <div class="modern-card-header">
                    <i class="bi bi-download"></i> Šablonas
                </div>
                <div class="card-body">
                    <p class="mb-3">Atsisiųskite pavyzdinį Excel failą, kad lengviau paruoštumėte savo duomenis:</p>
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-file-earmark-excel"></i>
                        Sukurkite Excel failą su dviem stulpeliais:
                        <ol class="mb-0 mt-2">
                            <li>Pirmoje eilutėje - antraštės (pvz. "Numeris", "Pavadinimas")</li>
                            <li>Kitose eilutėse - kabinetų duomenys</li>
                            <li>Išsaugokite kaip .xlsx arba .csv</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
