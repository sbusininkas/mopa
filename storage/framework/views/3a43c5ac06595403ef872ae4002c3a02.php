<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h3><?php echo e($school->exists ? 'Redaguoti mokyklą' : 'Sukurti mokyklą'); ?></h3>
        <a href="<?php echo e(route('schools.index')); ?>" class="btn btn-secondary">Atgal</a>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e($school->exists ? route('schools.update', $school) : route('schools.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($school->exists): ?>
            <?php echo method_field('POST'); ?>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Pavadinimas</label>
            <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $school->name)); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Adresas</label>
            <input type="text" name="address" class="form-control" value="<?php echo e(old('address', $school->address)); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Telefonas</label>
            <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone', $school->phone)); ?>">
        </div>

        <?php if(auth()->user()->isSupervisor()): ?>
        <hr>
        <h5>Priskirti vartotojai</h5>
        <p class="text-muted">Pasirinkite vartotojus, kuriuos priskirti šiai mokyklai. Pažymėkite administratorių varčiu.</p>

        <div class="mb-3">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $attached = $school->users->firstWhere('id', $user->id);
                    $isChecked = (bool) $attached;
                    $isAdmin = $attached ? (bool) $attached->pivot->is_admin : false;
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="users[]" value="<?php echo e($user->id); ?>" id="user_<?php echo e($user->id); ?>" <?php echo e($isChecked ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="user_<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</label>
                    <div class="form-check form-check-inline ms-3">
                        <input class="form-check-input" type="checkbox" name="admins[]" value="<?php echo e($user->id); ?>" id="admin_<?php echo e($user->id); ?>" <?php echo e($isAdmin ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="admin_<?php echo e($user->id); ?>">Admin</label>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <button class="btn btn-primary">Išsaugoti</button>
        <?php else: ?>
        <div class="alert alert-info">Tik prižiūrėtojas gali priskirti vartotojus prie mokyklų.</div>
        <button class="btn btn-primary">Išsaugoti</button>
        <?php endif; ?>
    </form>

    <?php if($school->exists): ?>
    <hr class="my-4">
    
    <h4 class="mb-3">Pamokų laikai</h4>
    <p class="text-muted">Nustatykite pamokų pradžios ir pabaigos laikus. Šie laikai bus naudojami tvarkaraščio generavimui ir rodomi viešame tvarkaraštyje.</p>
    
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo e(route('school.settings.lesson-times')); ?>" id="lesson-times-form">
        <?php echo csrf_field(); ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 80px;">Pamoka</th>
                        <th>Pradžia</th>
                        <th>Pabaiga</th>
                        <th style="width: 100px;">Veiksmai</th>
                    </tr>
                </thead>
                <tbody id="lesson-times-tbody">
                    <?php
                        $lessonTimes = $school->lesson_times;
                    ?>
                    <?php $__currentLoopData = $lessonTimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr data-index="<?php echo e($index); ?>">
                        <td>
                            <input type="number" name="lesson_times[<?php echo e($index); ?>][slot]" class="form-control" value="<?php echo e($time['slot']); ?>" readonly>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="time" name="lesson_times[<?php echo e($index); ?>][start]" class="form-control time-input" value="<?php echo e($time['start']); ?>" required data-display="<?php echo e($time['start']); ?>">
                                <span class="input-group-text time-display"><?php echo e(\Carbon\Carbon::createFromFormat('H:i', $time['start'])->format('H:i')); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="time" name="lesson_times[<?php echo e($index); ?>][end]" class="form-control time-input" value="<?php echo e($time['end']); ?>" required data-display="<?php echo e($time['end']); ?>">
                                <span class="input-group-text time-display"><?php echo e(\Carbon\Carbon::createFromFormat('H:i', $time['end'])->format('H:i')); ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if($index >= 9): ?>
                            <button type="button" class="btn btn-sm btn-danger remove-lesson-time">Šalinti</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" id="add-lesson-time">Pridėti pamokų laiką</button>
            <button type="submit" class="btn btn-primary">Išsaugoti pamokų laikus</button>
            <button type="button" class="btn btn-outline-secondary" id="reset-defaults">Atkurti numatytuosius</button>
        </div>
    </form>
    
    <!-- Reset Defaults Modal -->
    <div class="modal fade" id="resetDefaultsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atkurti numatytuosius laikus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Ar tikrai norite atkurti numatytuosius pamokų laikus?</p>
                    <p class="text-muted"><strong>Dėmesio!</strong> Dabartiniai pakeitimai bus prarasti.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                    <button type="button" class="btn btn-danger" id="confirm-reset">Atkurti</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function formatTimeDisplay(timeStr) {
        // Format time as HH:MM (24-hour format)
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        return `${hours}:${minutes}`;
    }
    
    function updateTimeDisplay(input) {
        const display = input.nextElementSibling;
        if (display && display.classList.contains('time-display')) {
            display.textContent = formatTimeDisplay(input.value);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        let nextSlot = <?php echo e(count($lessonTimes) + 1); ?>;
        const resetModal = new bootstrap.Modal(document.getElementById('resetDefaultsModal'));
        
        // Initialize time displays
        document.querySelectorAll('.time-input').forEach(input => {
            updateTimeDisplay(input);
            input.addEventListener('change', function() {
                updateTimeDisplay(this);
            });
        });
        
        document.getElementById('add-lesson-time').addEventListener('click', function() {
            const tbody = document.getElementById('lesson-times-tbody');
            const lastIndex = tbody.children.length;
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-index', lastIndex);
            newRow.innerHTML = `
                <td>
                    <input type="number" name="lesson_times[${lastIndex}][slot]" class="form-control" value="${nextSlot}" readonly>
                </td>
                <td>
                    <div class="input-group">
                        <input type="time" name="lesson_times[${lastIndex}][start]" class="form-control time-input" value="17:00" required>
                        <span class="input-group-text time-display">17:00</span>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input type="time" name="lesson_times[${lastIndex}][end]" class="form-control time-input" value="17:45" required>
                        <span class="input-group-text time-display">17:45</span>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-lesson-time">Šalinti</button>
                </td>
            `;
            tbody.appendChild(newRow);
            
            // Attach event listeners to new time inputs
            newRow.querySelectorAll('.time-input').forEach(input => {
                input.addEventListener('change', function() {
                    updateTimeDisplay(this);
                });
            });
            
            nextSlot++;
            attachRemoveHandlers();
        });
        
        function attachRemoveHandlers() {
            document.querySelectorAll('.remove-lesson-time').forEach(btn => {
                btn.onclick = function() {
                    this.closest('tr').remove();
                    reindexRows();
                };
            });
        }
        
        function reindexRows() {
            const rows = document.querySelectorAll('#lesson-times-tbody tr');
            rows.forEach((row, index) => {
                row.setAttribute('data-index', index);
                row.querySelector('input[name*="[slot]"]').name = `lesson_times[${index}][slot]`;
                row.querySelector('input[name*="[slot]"]').value = index + 1;
                row.querySelector('input[name*="[start]"]').name = `lesson_times[${index}][start]`;
                row.querySelector('input[name*="[end]"]').name = `lesson_times[${index}][end]`;
            });
            nextSlot = rows.length + 1;
        }
        
        // Show reset confirmation modal
        document.getElementById('reset-defaults').addEventListener('click', function() {
            resetModal.show();
        });
        
        // Handle reset confirmation
        document.getElementById('confirm-reset').addEventListener('click', function() {
            resetModal.hide();
            
            const defaults = <?php echo json_encode(\App\Models\School::getDefaultLessonTimes(), 15, 512) ?>;
            const tbody = document.getElementById('lesson-times-tbody');
            tbody.innerHTML = '';
            
            defaults.forEach((time, index) => {
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-index', index);
                newRow.innerHTML = `
                    <td>
                        <input type="number" name="lesson_times[${index}][slot]" class="form-control" value="${time.slot}" readonly>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="time" name="lesson_times[${index}][start]" class="form-control time-input" value="${time.start}" required>
                            <span class="input-group-text time-display">${formatTimeDisplay(time.start)}</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="time" name="lesson_times[${index}][end]" class="form-control time-input" value="${time.end}" required>
                            <span class="input-group-text time-display">${formatTimeDisplay(time.end)}</span>
                        </div>
                    </td>
                    <td></td>
                `;
                tbody.appendChild(newRow);
                
                // Attach event listeners to time inputs
                newRow.querySelectorAll('.time-input').forEach(input => {
                    input.addEventListener('change', function() {
                        updateTimeDisplay(this);
                    });
                });
            });
            
            nextSlot = defaults.length + 1;
            attachRemoveHandlers();
        });
        
        attachRemoveHandlers();
    });
    </script>
    
    <style>
    .time-display {
        background-color: #f8f9fa;
        min-width: 60px;
        text-align: center;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        padding: 0.5rem 0.75rem;
    }
    
    input[type="time"] {
        font-family: 'Courier New', monospace;
    }
    </style>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mopa\resources\views/admin/schools/edit.blade.php ENDPATH**/ ?>