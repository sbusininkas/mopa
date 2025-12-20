<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MOPA — Mokyklos tvarkaraščiai</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    
    .navbar {
      background: rgba(255, 255, 255, 0.95) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .navbar-brand {
      font-size: 1.5rem;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .hero-section {
      position: relative;
      min-height: 70vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      overflow: hidden;
      padding: 3rem 1rem;
    }
    
    .hero-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)" /></svg>');
      z-index: 1;
    }
    
    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
    }
    
    .hero-section h1 {
      font-size: 3.5rem;
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .hero-section .lead {
      font-size: 1.3rem;
      margin-bottom: 2rem;
      color: rgba(255, 255, 255, 0.95);
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }
    
    .btn-hero {
      background: white;
      color: #667eea;
      font-weight: 600;
      padding: 0.75rem 2rem;
      border: none;
      border-radius: 50px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .btn-hero:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
      background: #f0f0f0;
    }
    
    .btn-hero-secondary {
      background: transparent;
      color: white;
      border: 2px solid white;
      font-weight: 600;
      padding: 0.65rem 1.8rem;
      border-radius: 50px;
      transition: all 0.3s ease;
    }
    
    .btn-hero-secondary:hover {
      background: white;
      color: #667eea;
      transform: translateY(-3px);
    }
    
    .features-section {
      background: white;
      padding: 4rem 1rem;
    }
    
    .feature-card {
      background: white;
      border: none;
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border-left: 4px solid transparent;
    }
    
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
      border-left-color: #667eea;
    }
    
    .feature-icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 1rem;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.8rem;
    }
    
    .feature-card h5 {
      color: #333;
      margin-bottom: 1rem;
      font-weight: 600;
    }
    
    .feature-card p {
      color: #666;
      line-height: 1.6;
    }
    
    .benefits-section {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
      padding: 4rem 1rem;
    }
    
    .benefits-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }
    
    .benefit-item {
      display: flex;
      gap: 1rem;
      padding: 1.5rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .benefit-icon {
      color: #667eea;
      font-size: 1.8rem;
      flex-shrink: 0;
    }
    
    .benefit-content h6 {
      color: #333;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }
    
    .benefit-content p {
      color: #666;
      font-size: 0.95rem;
      line-height: 1.5;
    }
    
    .cta-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 4rem 1rem;
      text-align: center;
    }
    
    .cta-section h3 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      font-weight: 800;
    }
    
    .cta-section p {
      font-size: 1.1rem;
      margin-bottom: 2rem;
      opacity: 0.95;
    }
    
    footer {
      background: rgba(255, 255, 255, 0.95);
      border-top: 1px solid rgba(0, 0, 0, 0.1);
      padding: 2rem 1rem;
    }
    
    .stats-section {
      background: white;
      padding: 3rem 1rem;
    }
    
    .stat-item {
      text-align: center;
      padding: 1.5rem;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .stat-label {
      color: #666;
      margin-top: 0.5rem;
      font-weight: 500;
    }
    
    @media (max-width: 768px) {
      .hero-section h1 {
        font-size: 2rem;
      }
      
      .hero-section .lead {
        font-size: 1rem;
      }
      
      .cta-section h3 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-light sticky-top">
    <div class="container">
      <a class="navbar-brand" href="#"><i class="bi bi-calendar-week"></i> MOPA</a>
      <div class="d-flex gap-2">
        <a href="{{ route('schools.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-building"></i> Mokyklos</a>
        @if(session('active_school_id'))
          <a href="{{ route('schools.timetables.index', session('active_school_id')) }}" class="btn btn-primary btn-sm"><i class="bi bi-calendar3"></i> Tvarkaraščiai</a>
        @else
          <a href="{{ route('schools.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-calendar3"></i> Tvarkaraščiai</a>
        @endif
      </div>
    </div>
  </nav>

  <main>
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content" data-aos="fade-up">
        <h1>Modernios mokyklos tvarkaraščių sistema</h1>
        <p class="lead">MOPA — patikima platforma, kuri padeda automatizuoti pamokų planavimą,
          aptikti konfliktus ir kurti optimizuotus tvarkaraščius.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap" data-aos="zoom-in" data-aos-delay="200">
          <a href="{{ route('schools.index') }}" class="btn btn-hero"><i class="bi bi-rocket-fill"></i> Pradėti</a>
          @if(session('active_school_id'))
            <a href="{{ route('schools.timetables.index', session('active_school_id')) }}" class="btn btn-hero-secondary"><i class="bi bi-calendar3"></i> Mano tvarkaraščiai</a>
          @else
            <a href="{{ route('schools.index') }}" class="btn btn-hero-secondary"><i class="bi bi-building"></i> Mano mokyklos</a>
          @endif
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <h2 class="text-center fw-bold mb-5" data-aos="fade-up">
          <span style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            Pagrindinės funkcijos
          </span>
        </h2>
        <div class="row g-4">
          <div class="col-md-4" data-aos="fade-up">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-lightning-fill"></i>
              </div>
              <h5>Automatinis generavimas</h5>
              <p>Naudojant sumanią AI sistemą, automatiškai generuokite optimalius tvarkaraščius, atsižvelgiant į visus apribojimus.</p>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-shield-check"></i>
              </div>
              <h5>Konfliktų aptikimas</h5>
              <p>Realaus laiko konfliktų identifikacija — sužinokite apie problemas dar prieš joms tapus iššūkiais.</p>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-people-fill"></i>
              </div>
              <h5>Grupės ir valdymas</h5>
              <p>Patogus mokinių priskyrimas grupėms, mokytojų darbo dienų nustatymas ir prieigos kontrolė.</p>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-diagram-3"></i>
              </div>
              <h5>Integruota peržiūra</h5>
              <p>Greitai peržiūrėkite mokytojų, mokinių ir kabinetų tvarkaraščius iš vienos vietos.</p>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-file-earmark-pdf"></i>
              </div>
              <h5>Ataskaitos ir eksportas</h5>
              <p>Kurkite profesionalias ataskaitas ir eksportuokite duomenis PDF, Excel ir kitais formatais.</p>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bi bi-lock-fill"></i>
              </div>
              <h5>Saugus prieiga</h5>
              <p>Sukonfigūruokite vaidmenis ir leidimus, jog tik reikalingi žmonės turėtų prieigą prie duomenų.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
      <div class="container">
        <h2 class="text-center fw-bold mb-5" data-aos="fade-up">
          Kodėl rinktis MOPA?
        </h2>
        <div class="benefits-grid">
          <div class="benefit-item" data-aos="fade-up">
            <div class="benefit-icon">
              <i class="bi bi-clock-history"></i>
            </div>
            <div class="benefit-content">
              <h6>Taupykite laiką</h6>
              <p>Rankinis tvarkaraščio kūrimas užima savaitės. MOPA tai padaro per kelias minutes.</p>
            </div>
          </div>
          <div class="benefit-item" data-aos="fade-up" data-aos-delay="50">
            <div class="benefit-icon">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="benefit-content">
              <h6>Sumažinkite klaidas</h6>
              <p>Automatinis konfliktų aptikimas ir validacija užtikrina, kad nėra nesustigrumo.</p>
            </div>
          </div>
          <div class="benefit-item" data-aos="fade-up" data-aos-delay="100">
            <div class="benefit-icon">
              <i class="bi bi-graph-up"></i>
            </div>
            <div class="benefit-content">
              <h6>Optimizuoti ištekliai</h6>
              <p>Maksimaliai pasinaudokite kabinetais, mokytojų laiku ir mokinių užimtumu.</p>
            </div>
          </div>
          <div class="benefit-item" data-aos="fade-up" data-aos-delay="150">
            <div class="benefit-icon">
              <i class="bi bi-chat-dots"></i>
            </div>
            <div class="benefit-content">
              <h6>Greita nustatymo</h6>
              <p>Intuityvi sąsaja ir paprasta konfigūracija — pradėkite per kelias minutes.</p>
            </div>
          </div>
          <div class="benefit-item" data-aos="fade-up" data-aos-delay="200">
            <div class="benefit-icon">
              <i class="bi bi-cloud-check"></i>
            </div>
            <div class="benefit-content">
              <h6>Debesyje saugus</h6>
              <p>Jūsų duomenys visada saugūs, su automatinėmis atsarginėmis kopijomis.</p>
            </div>
          </div>
          <div class="benefit-item" data-aos="fade-up" data-aos-delay="250">
            <div class="benefit-icon">
              <i class="bi bi-headset"></i>
            </div>
            <div class="benefit-content">
              <h6>24/7 pagalba</h6>
              <p>Mūsų komanda visada paruošta padėti jums su bet kokiais klausimais.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="container">
        <div class="row">
          <div class="col-md-3" data-aos="zoom-in">
            <div class="stat-item">
              <div class="stat-number">500+</div>
              <div class="stat-label">Naudojančios mokyklos</div>
            </div>
          </div>
          <div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">
            <div class="stat-item">
              <div class="stat-number">50K+</div>
              <div class="stat-label">Mokytojų</div>
            </div>
          </div>
          <div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">
            <div class="stat-item">
              <div class="stat-number">500K+</div>
              <div class="stat-label">Mokinių</div>
            </div>
          </div>
          <div class="col-md-3" data-aos="zoom-in" data-aos-delay="300">
            <div class="stat-item">
              <div class="stat-number">99.9%</div>
              <div class="stat-label">Prieinamumas</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <h3 data-aos="fade-up">Pradėkite keisti savo tvarkaraščius šiandien</h3>
        <p data-aos="fade-up" data-aos-delay="100">Prisijunkite prie šimtų mokyklų, kurios jau naudoja MOPA ir sutaupė šimtus valandų.</p>
        <a href="{{ route('schools.index') }}" class="btn btn-hero" data-aos="zoom-in" data-aos-delay="200">
          <i class="bi bi-rocket-fill"></i> Pradėti nemokamai
        </a>
      </div>
    </section>
  </main>

  <footer>
    <div class="container text-center">
      <div class="fw-bold mb-2" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        <i class="bi bi-calendar-week"></i> MOPA
      </div>
      <small class="text-muted">© {{ date('Y') }} MOPA. Visos teisės saugomos. | 
        <a href="#" class="text-decoration-none text-muted">Privatumas</a> | 
        <a href="#" class="text-decoration-none text-muted">Sąlygos</a>
      </small>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      AOS.init({ duration: 800, once: true, offset: 100 });
    });
  </script>
</body>
</html>
