@if($timetable->generation_status === 'completed' && ($timetable->generation_report['unscheduled_count'] ?? 0) > 0)
<div class="modern-card mb-4">
    <div class="modern-card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-exclamation-circle"></i> Nepaskirstytos pamokos</div>
        <button type="button" class="btn btn-sm btn-light" style="background-color: white; border: 1px solid #dee2e6;" data-bs-toggle="collapse" data-bs-target="#unscheduledCollapse">
            Peržiūrėti
        </button>
    </div>
    <div id="unscheduledCollapse" class="collapse show">
        <div class="card-body">
            @php($attempts = $timetable->generation_report['attempts'] ?? 5)
            @php($bestAttempt = $timetable->generation_report['best_attempt'] ?? 1)
            <p class="small text-muted mb-2">
                Nepavyko priskirti visų pamokų po {{ $attempts }} bandymų. 
                <strong>Rodomas geriausias variantas (bandymas #{{ $bestAttempt }})</strong> su mažiausiai konfliktų. 
                Patikrinkite žemiau nurodytas konfliktų priežastis.
            </p>
            @php($reasonSummary = $timetable->generation_report['reason_summary_translated'] ?? $timetable->generation_report['reason_summary'] ?? [])
            <div class="row g-2 mb-3">
                @foreach($reasonSummary as $rk => $rv)
                    @php($count = is_array($rv) ? ($rv['count'] ?? 0) : $rv)
                    @php($label = is_array($rv) ? ($rv['label'] ?? $rk) : $rk)
                    @if($count > 0)
                    <div class="col-auto">
                        <span class="badge bg-light text-dark">{{ $label }}: {{ $count }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
            <div class="modern-table-wrapper">
                <table class="modern-table table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Grupė</th>
                            <th>Dalykas</th>
                            <th>Mokytojas</th>
                            <th>Likę / Prašyta</th>
                            <th>Priežastys (top)</th>
                            <th>Rekomendacija</th>
                            <th>Veiksmai</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach(($timetable->generation_report['unscheduled'] ?? []) as $item)
                        <?php
                            $reasonsTranslated = $item['reasons_translated'] ?? [];
                            $reasons = collect($reasonsTranslated)->filter(fn($v) => ($v['count'] ?? 0) > 0)->sortByDesc(fn($v) => $v['count'] ?? 0)->take(3);
                            
                            $sortedReasons = $reasons->sortBy(fn($v) => $v['count'] ?? 0);
                            $topReason = $sortedReasons->keys()->first();
                            $recommendation = '';
                            $recommendationClass = 'text-info';
                            $conflictCount = $sortedReasons->first()['count'] ?? 0;
                            
                            if ($topReason === 'teacher_conflict') {
                                $recommendation = '⚡ Lengviausias sprendimas: Pakeiskite mokytoją į turintį daugiau laisvo laiko (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-primary';
                            } elseif ($topReason === 'room_conflict') {
                                $recommendation = '🛋️ Lengviausias sprendimas: Pašalinkite kabineto apribojimą arba pridėkite kabinetą (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-success';
                            } elseif ($topReason === 'student_conflict') {
                                $recommendation = '👥 Lengviausias sprendimas: Sumažinkite mokiniams grupių skaičių (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-warning';
                            } elseif ($topReason === 'subject_limit') {
                                $recommendation = '📚 Lengviausias sprendimas: Padidinkite „Maks. to paties dalyko per dieną" (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-info';
                            } elseif ($topReason === 'teacher_not_working') {
                                $recommendation = '📅 Lengviausias sprendimas: Pridėkite daugiau darbo dienų mokytojui (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-danger';
                            } elseif ($topReason === 'no_slot') {
                                $recommendation = '⏰ Lengviausias sprendimas: Padidinkite pamokų skaičių per dieną (' . $conflictCount . ' konfliktai)';
                                $recommendationClass = 'text-secondary';
                            } else {
                                $recommendation = '✅ Pabandykite generuoti dar kartą';
                                $recommendationClass = 'text-muted';
                            }
                        ?>
                        <tr>
                            <td>{{ $item['group_name'] }}</td>
                            <td>{{ $item['subject_name'] }}</td>
                            <td>
                                @if(!empty($item['teacher_name']) && !empty($item['teacher_login_key_id']))
                                    <a href="{{ route('schools.timetables.teacher', [$school, $timetable, $item['teacher_login_key_id']]) }}" 
                                       class="text-decoration-none" 
                                       title="Peržiūrėti mokytojo tvarkaraštį">
                                        <i class="bi bi-person-circle me-1"></i>{{ $item['teacher_name'] }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ $item['remaining_lessons'] ?? 0 }} /
                                    {{ $item['requested_lessons'] ?? ($item['lessons_per_week'] ?? ($item['total_lessons'] ?? ($item['remaining_lessons'] ?? 0))) }}
                                </span>
                            </td>
                            <td class="small">
                                @forelse($reasons as $rk => $rv)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $rv['label'] ?? $rk }}: {{ $rv['count'] ?? 0 }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td class="small {{ $recommendationClass }}">
                                <i class="bi bi-lightbulb"></i> {{ $recommendation }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUnscheduledGroup{{ $item['group_id'] }}" title="Redaguoti grupę">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#copyUnscheduledGroup{{ $item['group_id'] }}" title="Kopijuoti nepaskirstytas pamokas">
                                        <i class="bi bi-files"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.timetables.partials.unscheduled-modals')
@endif
