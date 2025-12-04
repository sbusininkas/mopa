<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MOPA - Mokyklų Pamokų Tvarkaraščių Sistema</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #1e293b;
            --light: #f1f5f9;
        }
        
        body {
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            color: white;
            line-height: 1.2;
            text-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 1.5rem;
        }
        
        .hero-subtitle {
            font-size: clamp(1.1rem, 2vw, 1.4rem);
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.8;
            margin-bottom: 2rem;
        }
        
        .btn-hero {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-decoration: none;
        }
        
        .btn-hero-primary {
            background: white;
            color: var(--primary);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            color: var(--primary-dark);
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
        }
        
        /* Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--dark) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
        }
        
        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .feature-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 20px;
            background: inherit;
            filter: blur(20px);
            opacity: 0.4;
            z-index: -1;
        }
        
        .icon-purple { background: linear-gradient(135deg, #667eea, #764ba2); }
        .icon-green { background: linear-gradient(135deg, #10b981, #059669); }
        .icon-orange { background: linear-gradient(135deg, #f59e0b, #ea580c); }
        .icon-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .icon-pink { background: linear-gradient(135deg, #ec4899, #be185d); }
        .icon-cyan { background: linear-gradient(135deg, #06b6d4, #0e7490); }
        
        /* Stats */
        .stat-number {
            font-size: 4rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 1.2rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        /* Section Titles */
        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 3rem;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }
        
        /* Animations */
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 0.8s ease forwards;
        }
        
        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Hero Illustration */
        .hero-illustration {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 30px 60px rgba(0,0,0,0.3));
        }
        
        /* Benefits */
        .benefit-item {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .benefit-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .benefit-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }
        
        /* Footer */
        footer {
            background: #0f172a;
            color: rgba(255,255,255,0.8);
        }
        
        footer h5, footer h6 {
            color: white;
        }
        
        footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        footer a:hover {
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding-top: 60px;
            }
            
            .btn-hero {
                display: block;
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <i class="bi bi-calendar-check-fill me-2"></i>
                <span>MOPA</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#funkcijos">Funkcijos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#privalumai">Privalumai</a>
                    </li>
                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill px-4 ms-lg-3" href="<?php echo e(route('dashboard')); ?>">
                                <i class="bi bi-grid-fill me-2"></i>Valdymo skydas
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('login')); ?>">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Prisijungti
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill px-4 ms-lg-3 mt-2 mt-lg-0" href="<?php echo e(route('register')); ?>">
                                <i class="bi bi-person-plus me-2"></i>Registruotis
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center">
        <div class="hero-content w-100">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 text-center text-lg-start">
                        <div class="fade-up">
                            <h1 class="hero-title">
                                Modernūs pamokų tvarkaraščiai <span class="d-inline-block">vienoje vietoje</span>
                            </h1>
                            <p class="hero-subtitle">
                                <strong>MOPA</strong> - profesionali mokyklų tvarkaraščių valdymo sistema. Kurkite, valdykite ir optimizuokite pamokų tvarkaraščius lengvai ir greitai.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                                <?php if(auth()->guard()->guest()): ?>
                                    <a href="<?php echo e(route('register')); ?>" class="btn btn-hero btn-hero-primary">
                                        <i class="bi bi-rocket-takeoff me-2"></i>Pradėti nemokamai
                                    </a>
                                    <a href="<?php echo e(route('login')); ?>" class="btn btn-hero btn-hero-outline">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Prisijungti
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-hero btn-hero-primary">
                                        <i class="bi bi-grid-fill me-2"></i>Eiti į valdymo skydą
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="floating">
                            <img src="data:image/svg+xml,%3Csvg viewBox='0 0 800 600' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3ClinearGradient id='grad1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23667eea;stop-opacity:1'/%3E%3Cstop offset='100%25' style='stop-color:%23764ba2;stop-opacity:1'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='800' height='600' fill='white' rx='20'/%3E%3Crect x='50' y='50' width='700' height='80' fill='url(%23grad1)' rx='10'/%3E%3Ctext x='400' y='105' fill='white' font-size='32' font-weight='bold' text-anchor='middle'%3EPamokų tvarkaraštis%3C/text%3E%3Cg%3E%3Crect x='50' y='160' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='220' y='160' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='390' y='160' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='560' y='160' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='70' y='180' width='110' height='30' fill='%23667eea' rx='5'/%3E%3Crect x='70' y='220' width='110' height='20' fill='%2310b981' rx='5'/%3E%3Crect x='240' y='180' width='110' height='30' fill='%23f59e0b' rx='5'/%3E%3Crect x='240' y='220' width='110' height='20' fill='%233b82f6' rx='5'/%3E%3Crect x='410' y='180' width='110' height='30' fill='%23ec4899' rx='5'/%3E%3Crect x='410' y='220' width='110' height='20' fill='%2306b6d4' rx='5'/%3E%3Crect x='580' y='180' width='110' height='30' fill='%238b5cf6' rx='5'/%3E%3Crect x='580' y='220' width='110' height='20' fill='%2314b8a6' rx='5'/%3E%3C/g%3E%3Cg%3E%3Crect x='50' y='280' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='220' y='280' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='390' y='280' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='560' y='280' width='150' height='100' fill='%23f0f0f0' rx='8'/%3E%3Crect x='70' y='300' width='110' height='30' fill='%2310b981' rx='5'/%3E%3Crect x='70' y='340' width='110' height='20' fill='%23f59e0b' rx='5'/%3E%3Crect x='240' y='300' width='110' height='30' fill='%233b82f6' rx='5'/%3E%3Crect x='410' y='300' width='110' height='30' fill='%2306b6d4' rx='5'/%3E%3Crect x='580' y='300' width='110' height='30' fill='%23667eea' rx='5'/%3E%3C/g%3E%3Ccircle cx='720' cy='480' r='80' fill='%2310b981' opacity='0.2'/%3E%3Ccircle cx='720' cy='480' r='60' fill='%2310b981' opacity='0.4'/%3E%3Ccircle cx='720' cy='480' r='40' fill='%2310b981'/%3E%3Cpath d='M 700 480 L 715 495 L 745 465' stroke='white' stroke-width='6' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E" 
                                 alt="Tvarkaraščių iliustracija" 
                                 class="hero-illustration">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Nemokama</div>
                </div>
                <div class="col-md-4">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Prieiga</div>
                </div>
                <div class="col-md-4">
                    <div class="stat-number">∞</div>
                    <div class="stat-label">Tvarkaraščių</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" id="funkcijos" style="padding-top: 5rem !important; padding-bottom: 5rem !important;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Pagrindinės funkcijos</h2>
                <p class="section-subtitle">Visos reikalingos priemonės efektyviam tvarkaraščių valdymui</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-purple">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Automatinis generavimas</h3>
                        <p class="text-muted mb-0">Sukurkite optimalų tvarkaraštį vienu mygtuko paspaudimu. Sistema automatiškai paskirsto pamokas atsižvelgdama į visus apribojimus.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-green">
                            <i class="bi bi-cursor-fill"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Drag & Drop redagavimas</h3>
                        <p class="text-muted mb-0">Keiskite tvarkaraštį paprastai tempiant pamokas. Sistema iš karto perspės apie galimus konfliktus.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-orange">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Konfliktų aptikimas</h3>
                        <p class="text-muted mb-0">Automatiškai aptinkame mokinių, mokytojų ir kabinetų konfliktus. Gaukite pasiūlymus kaip juos išspręsti.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-blue">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Grupių valdymas</h3>
                        <p class="text-muted mb-0">Valdykite klases, grupes ir pogrupius. Kurkite grupių kopijas skirtingiems dalykams ar kabinetams.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-pink">
                            <i class="bi bi-door-closed-fill"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Kabinetų optimizavimas</h3>
                        <p class="text-muted mb-0">Efektyviai paskirstykite kabinetus. Sistema siūlo laisvus kabinetus ir padeda išvengti konfliktų.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-cyan">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                        </div>
                        <h3 class="h5 fw-bold mb-3">Importas iš Excel</h3>
                        <p class="text-muted mb-0">Importuokite mokinius ir mokytojus iš Excel failų. Greitai ir patogiai perkelkite esamus duomenis.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-5 bg-light" id="privalumai" style="padding-top: 5rem !important; padding-bottom: 5rem !important;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Kodėl MOPA?</h2>
                <p class="section-subtitle">Sutaupykite laiko ir sumažinkite klaidų kiekį</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="benefit-item d-flex gap-4">
                        <div class="benefit-icon icon-purple">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <h4 class="h5 fw-bold mb-2">Greitas ir efektyvus</h4>
                            <p class="text-muted mb-0">Sukurkite visą mokyklos tvarkaraštį per kelias minutes, o ne dienas.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="benefit-item d-flex gap-4">
                        <div class="benefit-icon icon-green">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h4 class="h5 fw-bold mb-2">Patikimas ir saugus</h4>
                            <p class="text-muted mb-0">Visi duomenys saugomi šifruotai. Reguliarūs atsarginiai kopijavimai.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="benefit-item d-flex gap-4">
                        <div class="benefit-icon icon-orange">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div>
                            <h4 class="h5 fw-bold mb-2">Veikia visur</h4>
                            <p class="text-muted mb-0">Naudokitės sistema bet kuriame įrenginyje - kompiuteryje, planšetėje ar telefone.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="benefit-item d-flex gap-4">
                        <div class="benefit-icon icon-blue">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <h4 class="h5 fw-bold mb-2">Nuolat tobulinama</h4>
                            <p class="text-muted mb-0">Reguliariai pridedamos naujos funkcijos pagal vartotojų poreikius.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5 text-white text-center" style="padding-top: 5rem !important; padding-bottom: 5rem !important;">
        <div class="container position-relative" style="z-index: 2;">
            <h2 class="display-4 fw-bold mb-4">Pasiruošę pradėti?</h2>
            <p class="lead mb-5" style="max-width: 600px; margin-left: auto; margin-right: auto;">
                Prisijunkite prie mokyklų, kurios jau naudoja MOPA sistemą tvarkaraščių valdymui
            </p>
            <?php if(auth()->guard()->guest()): ?>
                <a href="<?php echo e(route('register')); ?>" class="btn btn-hero btn-hero-primary btn-lg">
                    <i class="bi bi-person-plus me-2"></i>Registruotis dabar
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-hero btn-hero-primary btn-lg">
                    <i class="bi bi-grid-fill me-2"></i>Eiti į valdymo skydą
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-calendar-check-fill me-2"></i>MOPA
                    </h5>
                    <p class="text-white-50">
                        Moderni mokyklų pamokų tvarkaraščių valdymo sistema
                    </p>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-semibold mb-3">Nuorodos</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="#funkcijos" class="text-decoration-none">
                                <i class="bi bi-chevron-right me-1"></i>Funkcijos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#privalumai" class="text-decoration-none">
                                <i class="bi bi-chevron-right me-1"></i>Privalumai
                            </a>
                        </li>
                        <?php if(auth()->guard()->guest()): ?>
                            <li class="mb-2">
                                <a href="<?php echo e(route('login')); ?>" class="text-decoration-none">
                                    <i class="bi bi-chevron-right me-1"></i>Prisijungti
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo e(route('register')); ?>" class="text-decoration-none">
                                    <i class="bi bi-chevron-right me-1"></i>Registruotis
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-semibold mb-3">Kontaktai</h6>
                    <p class="text-white-50">
                        <i class="bi bi-envelope me-2"></i>info@mopa.lt
                    </p>
                    <p class="text-white-50">
                        <i class="bi bi-telephone me-2"></i>+370 600 00000
                    </p>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="text-center text-white-50">
                <p class="mb-0">&copy; <?php echo e(date('Y')); ?> MOPA. Visos teisės saugomos.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        const navbarHeight = document.querySelector('.navbar').offsetHeight;
                        const targetPosition = target.offsetTop - navbarHeight;
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });

        // Navbar background on scroll
        const navbar = document.querySelector('.navbar-custom');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.12)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.08)';
            }
        });

        // Add fade-up animation delay to features
        document.addEventListener('DOMContentLoaded', function() {
            const features = document.querySelectorAll('.feature-card');
            features.forEach((feature, index) => {
                feature.style.animationDelay = `${index * 0.1}s`;
                feature.classList.add('fade-up');
            });
        });
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/welcome-new.blade.php ENDPATH**/ ?>