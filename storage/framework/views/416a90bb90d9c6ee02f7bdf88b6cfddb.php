<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MOPA — Mokyklos tvarkaraščiai</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <style>
    body { background: #f7f7ff; }
    .hero-section { position: relative; min-height: 60vh; display: flex; align-items: center; }
    .hero-bg { position: absolute; inset: 0; z-index: -1; background: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.12)); border-radius: 16px; filter: blur(2px); }
    .btn-gradient { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; }
    .section-spacer { padding: 3rem 0; }
  </style>
</head>
<body>
  <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">MOPA</a>
      <div class="d-flex gap-2">
        <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-outline-primary"><i class="bi bi-building"></i> Mokyklos</a>
        <?php if(session('active_school_id')): ?>
          <a href="<?php echo e(route('schools.timetables.index', session('active_school_id'))); ?>" class="btn btn-primary"><i class="bi bi-calendar3"></i> Tvarkaraščiai</a>
        <?php else: ?>
          <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-primary" title="Pirmiausia pasirinkite mokyklą"><i class="bi bi-calendar3"></i> Tvarkaraščiai</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main>
    <section class="hero-section text-center section-spacer">
      <div class="hero-bg"></div>
      <div class="container position-relative">
        <h1 class="display-5 fw-bold mb-3" data-aos="fade-up">MOPA — moderni mokyklos tvarkaraščių sistema</h1>
        <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
          Automatizuokite pamokų planavimą, valdykite mokytojų, mokinių ir kabinetų užimtumą,
          greitai aptikite konfliktus ir kurkite aiškius bei patogius tvarkaraščius.
        </p>
        <div class="d-flex justify-content-center gap-3" data-aos="zoom-in" data-aos-delay="200">
          <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-primary btn-lg"><i class="bi bi-building"></i> Mano mokyklos</a>
          <?php if(session('active_school_id')): ?>
            <a href="<?php echo e(route('schools.timetables.index', session('active_school_id'))); ?>" class="btn btn-outline-primary btn-lg"><i class="bi bi-calendar3"></i> Tvarkaraščiai</a>
          <?php else: ?>
            <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-outline-primary btn-lg" title="Pirmiausia pasirinkite mokyklą"><i class="bi bi-calendar3"></i> Tvarkaraščiai</a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="features-section section-spacer">
      <div class="container">
        <div class="row g-4">
          <div class="col-md-4" data-aos="fade-up">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <div class="mb-3"><i class="bi bi-gear text-primary fs-3"></i></div>
                <h5 class="card-title">Išmanus generavimas</h5>
                <p class="card-text">Optimizuokite pamokų tvarkaraščius pagal mokytojų darbo dienas, kabinetų užimtumą ir mokinių grupes.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <div class="mb-3"><i class="bi bi-shield-check text-success fs-3"></i></div>
                <h5 class="card-title">Konfliktų aptikimas</h5>
                <p class="card-text">Greita ir aiški konfliktų peržiūra bei sprendimo pasiūlymai.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <div class="mb-3"><i class="bi bi-people text-info fs-3"></i></div>
                <h5 class="card-title">Grupės ir vaidmenys</h5>
                <p class="card-text">Patogus mokinių priskyrimas grupėms, mokytojų valdymas ir prieigos kontrolė.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section text-center section-spacer">
      <div class="container">
        <h3 class="fw-bold mb-3">Pradėkite šiandien</h3>
        <p class="text-muted mb-4">Prisijunkite arba pridėkite mokyklą, kad pradėtumėte tvarkaraščių kūrimą.</p>
        <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-gradient btn-lg"><i class="bi bi-rocket"></i> Pradėti</a>
      </div>
    </section>
  </main>

  <footer class="py-4 bg-white border-top">
    <div class="container text-center">
      <div class="fw-bold">MOPA</div>
      <small class="text-muted">© <?php echo e(date('Y')); ?> MOPA. Visos teisės saugomos.</small>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){ AOS.init({ duration: 600, once: true }); });
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\mopa\resources\views/welcome.blade.php ENDPATH**/ ?>