<div class="modern-card mb-4">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-calendar-week"></i> Mokytojų darbo dienos</div>
        <button type="button" class="btn btn-sm btn-light" style="background-color: white; border: 1px solid #dee2e6;" data-bs-toggle="collapse" data-bs-target="#teacherWorkingDaysCollapse">
            Peržiūrėti
        </button>
    </div>
    <div id="teacherWorkingDaysCollapse" class="collapse">
        <div class="card-body">
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle"></i> Čia galite nustatyti, kuriomis dienomis kiekvienas mokytojas dirba šiame tvarkaraštyje. 
                Jei nepažymėta nė viena diena, laikoma, kad mokytojas dirba visas dienas.
            </p>
            <p class="text-muted small mb-3">
                <i class="bi bi-slash-circle"></i> Taip pat galite nurodyti laikus (pamokų intervalus), kai mokytojas NEGALI dirbti.
            </p>
            <div id="teachersWorkingDaysList">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Kraunama...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
