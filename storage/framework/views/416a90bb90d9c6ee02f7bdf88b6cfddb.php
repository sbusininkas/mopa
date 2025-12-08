<!DOCTYPE html>
<html lang="<?php echo str_replace('_', '-', app()->getLocale()); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MOPA â€” Mokyklos TvarkaraÅ¡Äiai</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <style>
    body { background: #f7f7ff; }
    .hero-section { position: relative; min-height: 60vh; display: flex; align-items: center; }
    .hero-bg { position: absolute; inset: 0; z-index: -1; background: linear-gradient(135deg, rgba(102,126,234,0.15), rgba(118,75,162,0.15)); border-radius: 16px; filter: blur(2px); }
    .btn-gradient { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; box-shadow: 0 8px 24px rgba(118,75,162,0.25); }
    .btn-gradient:hover { opacity: 0.9; }
    .feature-card .feature-icon { width: 48px; height: 48px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .section-spacer { padding: 3rem 0; }
  </style>
</head>
<body>
  <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">MOPA</a>
      <div class="d-flex gap-2">
        <a href="<?php echo route('schools.index'); ?>" class="btn btn-outline-primary"><i class="bi bi-building"></i> Mokyklos</a>
        <?php 
          $activeSchoolId = session('active_school_id');
          if ($activeSchoolId): 
        ?>
          <a href="<?php echo route('schools.timetables.index', [$activeSchoolId]); ?>" class="btn btn-primary"><i class="bi bi-calendar3"></i> TvarkaraÅ¡Äiai</a>
        <?php 
          else: 
        ?>
          <a href="<?php echo route('schools.index'); ?>" class="btn btn-primary" title="Pirmiausia pasirinkite mokyklÄ…"><i class="bi bi-calendar3"></i> TvarkaraÅ¡Äiai</a>
        <?php 
          endif; 
        ?>
      </div>
    </div>
  </nav>

  <main>
    <section class="hero-section text-center section-spacer">
      <div class="hero-bg"></div>
      <div class="container position-relative">
        <h1 class="display-5 fw-bold mb-3" data-aos="fade-up">MOPA â€” Moderni Mokyklos TvarkaraÅ¡ÄiÅ³ Sistema</h1>
        <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
          Automatizuokite pamokÅ³ planavimÄ…, valdykite mokytojÅ³, mokiniÅ³ ir kabinetÅ³ uÅ¾imtumÄ…,
          greitai aptikite konfliktus ir kurkite aiÅ¡kius, lengvai suprantamus tvarkaraÅ¡Äius.
        </p>
        <div class="d-flex justify-content-center gap-3" data-aos="zoom-in" data-aos-delay="200">
          <a href="<?php echo route('schools.index'); ?>" class="btn btn-primary btn-lg">
            <i class="bi bi-building"></i> Mano mokyklos
          </a>
          <?php 
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId): 
          ?>
            <a href="<?php echo route('schools.timetables.index', [$activeSchoolId]); ?>" class="btn btn-outline-primary btn-lg">
              <i class="bi bi-calendar3"></i> TvarkaraÅ¡Äiai
            </a>
          <?php 
            else: 
          ?>
            <a href="<?php echo route('schools.index'); ?>" class="btn btn-outline-primary btn-lg" title="Pirmiausia pasirinkite mokyklÄ…">
              <i class="bi bi-calendar3"></i> TvarkaraÅ¡Äiai
            </a>
          <?php 
            endif; 
          ?>
        </div>
      </div>
    </section>

    <section class="features-section section-spacer">
      <div class="container">
        <div class="row g-4">
          <div class="col-md-4" data-aos="fade-up">
            <div class="card h-100 shadow-sm feature-card">
              <div class="card-body">
                <div class="feature-icon bg-primary text-white"><i class="bi bi-gear"></i></div>
                <h5 class="card-title mt-3">IÅ¡manus generavimas</h5>
                <p class="card-text">Sistema siÅ«lo optimalÅ³ pamokÅ³ iÅ¡dÄ—stymÄ… pagal mokytojÅ³ darbo dienas, kabinetÅ³ uÅ¾imtumÄ… ir mokiniÅ³ grupes.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100 shadow-sm feature-card">
              <div class="card-body">
                <div class="feature-icon bg-success text-white"><i class="bi bi-shield-check"></i></div>
                <h5 class="card-title mt-3">KonfliktÅ³ aptikimas</h5>
                <p class="card-text">AiÅ¡kiai iÅ¡ryÅ¡kinami laiko, mokiniÅ³ ir kabinetÅ³ konfliktai â€” problemos sprendÅ¾iamos vos keliais paspaudimais.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100 shadow-sm feature-card">
              <div class="card-body">
                <div class="feature-icon bg-info text-white"><i class="bi bi-people"></i></div>
                <h5 class="card-title mt-3">GrupÄ—s ir vaidmenys</h5>
                <p class="card-text">Patogus mokiniÅ³ priskyrimas grupÄ—ms, mokytojÅ³ darbo dienÅ³ valdymas ir prieigos kontrolÄ— pagal vaidmenis.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="capabilities-section section-spacer">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-6" data-aos="fade-right">
            <div class="p-4 rounded-3 bg-white shadow-sm">
              <h4 class="fw-bold"><i class="bi bi-lightning-charge"></i> GalimybÄ—s</h4>
              <ul class="mt-3 mb-0">
                <li>TvarkaraÅ¡ÄiÅ³ generavimas su konfliktais ir be jÅ³</li>
                <li>MokytojÅ³ individualÅ«s tvarkaraÅ¡Äiai ir darbo dienos</li>
                <li>MokiniÅ³ tvarkaraÅ¡Äiai, grupiÅ³ pridÄ—jimas ir konfliktÅ³ modalai</li>
                <li>KlasÄ—s, kabinetÅ³ ir dalykÅ³ valdymas</li>
                <li>PraneÅ¡imai ir sistemos Ä¯spÄ—jimai realiu laiku</li>
                <li>GraÅ¾us, modernus UI su patogiomis lentelÄ—mis</li>
              </ul>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <div class="p-4 rounded-3 bg-white shadow-sm">
              <h4 class="fw-bold"><i class="bi bi-play"></i> Greitas startas</h4>
              <ol class="mt-3 mb-0">
                <li>PridÄ—kite mokyklÄ… ir importuokite mokytojus bei mokinius</li>
                <li>Sukurkite dalykÅ³ grupes ir nurodykite valandÅ³ skaiÄiÅ³</li>
                <li>Priskirkite kabinetus ir mokytojÅ³ darbo dienas</li>
                <li>Generuokite tvarkaraÅ¡tÄ¯ ir perÅ¾iÅ«rÄ—kite konfliktus</li>
                <li>DalinkitÄ—s tvarkaraÅ¡Äiu su bendruomene</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section text-center section-spacer" data-aos="zoom-in">
      <div class="container">
        <h3 class="fw-bold mb-3">PasiruoÅ¡Ä™ pradÄ—ti?</h3>
        <p class="text-muted mb-4">MOPA â€” tvarkaraÅ¡Äiai, kurie tiesiog veikia.</p>
        <a href="<?php echo route('schools.index'); ?>" class="btn btn-gradient btn-lg"><i class="bi bi-rocket"></i> PradÄ—ti darbÄ…</a>
      </div>
    </section>
  </main>

  <footer class="py-4 bg-white border-top">
    <div class="container text-center">
      <div class="fw-bold">MOPA</div>
      <small class="text-muted">Â© 2026 MOPA. Visos teisÄ—s saugomos.</small>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      AOS.init({ duration: 600, once: true });
    });
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/welcome.blade.php ENDPATH**/ ?>