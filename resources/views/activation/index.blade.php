@extends('layouts.admin')

@section('content')
<style>
    :root {
        --primary-color: #667eea;
        --secondary-color: #764ba2;
    }

    body {
        background-color: #f7f7ff;
    }
        max-width: 800px;
        margin: 60px auto;
    }

    .activation-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .activation-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 40px 30px;
        text-align: center;
    }

    .activation-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .activation-header p {
        font-size: 16px;
        opacity: 0.95;
        margin: 0;
    }

    .activation-body {
        padding: 40px 30px;
    }

    .activation-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid #e0e0e0;
    }

    .activation-tab {
        flex: 1;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #666;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
    }

    .activation-tab.active {
        color: var(--primary-color);
    }

    .activation-tab.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary-color);
    }

    .activation-tab:hover:not(.active) {
        color: #333;
        background: #f7f7ff;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        padding: 12px 16px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.1);
    }

    .btn-activate {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
        color: white;
        padding: 14px 30px;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
        transition: transform 0.2s ease;
    }

    .btn-activate:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .info-box {
        background: #f7f7ff;
        border-left: 4px solid var(--primary-color);
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box h6 {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 8px;
    }

    .info-box p {
        color: #666;
        margin: 0;
        font-size: 14px;
    }

    .info-box ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
        color: #666;
        font-size: 14px;
    }

    .info-box ul li {
        margin-bottom: 5px;
    }
</style>

<div class="activation-container">
    <div class="activation-card">
        <div class="activation-header">
            <i class="bi bi-building" style="font-size: 48px; margin-bottom: 15px;"></i>
            <h1>Įstaigos aktyvacija</h1>
            <p>Įveskite aktyvacijos raktą, kad galėtumėte prisijungti prie įstaigos</p>
        </div>

        <div class="activation-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <strong>Klaida!</strong>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tabs -->
            <div class="activation-tabs">
                <button class="activation-tab active" onclick="switchTab('admin')">
                    <i class="bi bi-shield-check"></i> Administratoriaus raktas
                </button>
                <button class="activation-tab" onclick="switchTab('user')">
                    <i class="bi bi-key"></i> Vartotojo raktas
                </button>
            </div>

            <!-- Admin Key Form -->
            <div id="admin-tab" class="tab-content active">
                <div class="info-box">
                    <h6><i class="bi bi-info-circle"></i> Administratoriaus raktas</h6>
                    <p>Jei esate įstaigos administratorius, įveskite įstaigos administratoriaus raktą. Tai suteiks jums:</p>
                    <ul>
                        <li>Pilną prieigą prie įstaigos valdymo funkcijų</li>
                        <li>Galimybę valdyti mokinius, mokytojus ir tvarkaraščius</li>
                        <li>Prieigą prie visų įstaigos nustatymų</li>
                    </ul>
                </div>

                <form action="{{ route('activation.admin-key') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="admin_key" class="form-label">
                            <i class="bi bi-shield-check"></i> Administratoriaus raktas
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('admin_key') is-invalid @enderror" 
                            id="admin_key" 
                            name="admin_key" 
                            placeholder="Įveskite įstaigos administratoriaus raktą"
                            value="{{ old('admin_key') }}"
                            required
                        >
                        @error('admin_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-activate">
                        <i class="bi bi-check-circle"></i> Aktyvuoti kaip administratorius
                    </button>
                </form>
            </div>

            <!-- User Token Form -->
            <div id="user-tab" class="tab-content">
                <div class="info-box">
                    <h6><i class="bi bi-info-circle"></i> Vartotojo raktas</h6>
                    <p>Jei esate mokytojas ar mokinys, įveskite jums paskirtą prisijungimo raktą. Tai suteiks jums:</p>
                    <ul>
                        <li>Prieigą prie savo asmeninio tvarkaraščio</li>
                        <li>Galimybę matyti savo pamokas ir užsiėmimus</li>
                        <li>Prieigą prie įstaigos informacijos</li>
                    </ul>
                </div>

                <form action="{{ route('activation.user-token') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="user_token" class="form-label">
                            <i class="bi bi-key"></i> Prisijungimo raktas
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('user_token') is-invalid @enderror" 
                            id="user_token" 
                            name="user_token" 
                            placeholder="Įveskite jums paskirtą prisijungimo raktą"
                            value="{{ old('user_token') }}"
                            required
                        >
                        @error('user_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-activate">
                        <i class="bi bi-check-circle"></i> Aktyvuoti raktą
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    // Update tab buttons
    const allTabs = document.querySelectorAll('.activation-tab');
    allTabs.forEach(btn => btn.classList.remove('active'));
    event.target.closest('.activation-tab')?.classList.add('active');

    // Update tab content
    const allContent = document.querySelectorAll('.tab-content');
    allContent.forEach(content => content.classList.remove('active'));
    
    const tabContent = document.getElementById(tab + '-tab');
    if (tabContent) {
        tabContent.classList.add('active');
    }
}
</script>
@endsection
