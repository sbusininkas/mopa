@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Redaguoti vartotoją</h2>
    {{-- ...čia dedamas visas formos ir informacijos turinys iš seno failo... --}}
    {{-- ...existing code... --}}
</div>
@endsection
            background: linear-gradient(135deg, #5568d3 0%, #6a3d8e 100%);
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .user-info {
            background: #f7f7ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .user-info h5 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Grįžti
                </a>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-pencil-square"></i> Redaguoti Vartotoją</h1>
            <p>Atnaujinkite vartotojo informaciją ir rolę</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <strong>Klaida!</strong>
                <ul class="mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- User Information Card -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person-badge"></i> Vartotojo Informacija
                    </div>
                    <div class="card-body">
                        <!-- User Info -->
                        <div class="user-info">
                            <h5>Esama Informacija</h5>
                            <div class="info-row">
                                <span class="info-label">ID:</span>
                                <span class="info-value">#{{ $user->id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Sukurta:</span>
                                <span class="info-value">{{ $user->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Atnaujinta:</span>
                                <span class="info-value">{{ $user->updated_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Esama Rolė:</span>
                                <span class="info-value">
                                    @if($user->isAdmin())
                                        <span class="badge bg-danger">Administratorius</span>
                                    @elseif($user->isTeacher())
                                        <span class="badge bg-info">Mokytojas</span>
                                    @else
                                        <span class="badge bg-success">Mokinys</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <form method="POST" action="{{ route('users.update', $user) }}" class="mt-4">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Vardas ir Pavardė</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">El. Pašto Adresas</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label">Rolė</label>
                                @if(auth()->user()->isSupervisor())
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>
                                            <i class="bi bi-person-check"></i> Mokinys
                                        </option>
                                        <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>
                                            <i class="bi bi-book"></i> Mokytojas
                                        </option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                            <i class="bi bi-shield-lock"></i> Administratorius
                                        </option>
                                        <option value="supervisor" {{ $user->role === 'supervisor' ? 'selected' : '' }}>
                                            <i class="bi bi-gear"></i> Prižiūrėtojas
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <select class="form-select" id="role" name="role" disabled>
                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>
                                            Mokinys
                                        </option>
                                        <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>
                                            Mokytojas
                                        </option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                            Administratorius
                                        </option>
                                        <option value="supervisor" {{ $user->role === 'supervisor' ? 'selected' : '' }}>
                                            Prižiūrėtojas
                                        </option>
                                    </select>
                                    <small class="text-muted">Tik prižiūrėtojas gali keisti rolę</small>
                                @endif
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Išsaugoti Pakeitimus
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Atšaukti
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            
                <!-- Schools Assignment Card -->
                @if(auth()->user()->isSupervisor())
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-building"></i> Priskirtos Mokyklos
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('users.update', $user) }}">
                                @csrf
                                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                                <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
                                <input type="hidden" name="role" value="{{ old('role', $user->role) }}">

                                <p class="small text-muted">Pažymėkite mokyklas, prie kurių priskirti vartotoją. Pažymėkite kaip administratorių, jei šis vartotojas turi valdyti konkrečią mokyklą.</p>

                                @foreach($schools as $school)
                                    @php
                                        $attached = $user->schools->firstWhere('id', $school->id);
                                        $isChecked = (bool) $attached;
                                        $isAdmin = $attached ? (bool) $attached->pivot->is_admin : false;
                                    @endphp
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="schools[]" value="{{ $school->id }}" id="school_{{ $school->id }}" {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="school_{{ $school->id }}">{{ $school->name }} <small class="text-muted">{{ $school->address }}</small></label>

                                        <div class="form-check form-check-inline ms-3">
                                            <input class="form-check-input" type="checkbox" name="school_admins[]" value="{{ $school->id }}" id="school_admin_{{ $school->id }}" {{ $isAdmin ? 'checked' : '' }} {{ $isChecked ? '' : 'disabled' }}>
                                            <label class="form-check-label" for="school_admin_{{ $school->id }}">Mokyklos administratorius</label>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="mt-3">
                                    <button class="btn btn-primary" type="submit">Atnaujinti mokyklų priskyrimą</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Side Info -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Pagalba
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Rolės Paaiškinimas:</h6>
                        <div class="mb-3">
                            <strong><span class="badge bg-success">Mokinys</span></strong>
                            <p class="small mt-2">Standartinis vartotojas, gali peržiūrėti turinį ir pateikti užduotis.</p>
                        </div>
                        <div class="mb-3">
                            <strong><span class="badge bg-info">Mokytojas</span></strong>
                            <p class="small mt-2">Gali kurti turinį, priimti užduotis ir vertinti studentų darbą.</p>
                        </div>
                        <div class="mb-3">
                            <strong><span class="badge bg-danger">Administratorius</span></strong>
                            <p class="small mt-2">Pilna prieiga prie sistemos. Gali valdyti visus vartotojus ir nustatymus.</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-shield-lock"></i> Saugumo Pastaba
                    </div>
                    <div class="card-body small">
                        <p>Administratorių rolę galėtų suteikti tik patikimiems vartotojams. Administratoriai turi pilną prieigą tik prie savo mokyklos. Pilną prieigą prie visos sistemos turi tik prižiūrėtojas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
