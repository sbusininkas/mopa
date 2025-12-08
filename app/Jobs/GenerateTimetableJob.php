<?php

namespace App\Jobs;

use App\Models\Timetable;
use App\Notifications\TimetableGenerated;
use App\Services\TimetableGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTimetableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Timetable $timetable,
        public ?int $initiatorUserId = null
    ) {
        $this->onQueue('timetables');
    }

    public function handle(TimetableGenerator $generator): void
    {
        // Mark start
        $this->timetable->update([
            'generation_status' => 'running',
            'generation_progress' => 0,
            'generation_started_at' => now(),
            'generation_finished_at' => null,
        ]);

        try {
            // Clear all group copy relationships before generating new timetable
            // Note: timetable_group_copies table currently not used, commented out to prevent errors
            // $groupIds = $this->timetable->groups()->pluck('id');
            // \App\Models\TimetableGroupCopy::whereIn('original_group_id', $groupIds)
            //     ->orWhereIn('copy_group_id', $groupIds)
            //     ->delete();
            
            $result = $generator->generate($this->timetable, function(int $percent) {
                if ($percent - ($this->timetable->generation_progress ?? 0) >= 3) {
                    $this->timetable->update(['generation_progress' => $percent]);
                }
            });
            $unscheduledCount = count($result['unscheduled'] ?? []);
            $report = [
                'passes' => $result['passes'] ?? null,
                'attempts' => $result['attempts'] ?? null,
                'best_attempt' => $result['best_attempt'] ?? null,
                'attempt_summaries' => $result['attempt_summaries'] ?? [],
                'total_units' => $result['total_units'] ?? null,
                'placed_units' => $result['placed_units'] ?? null,
                'unscheduled_units' => $result['unscheduled_units'] ?? null,
                'unscheduled_count' => $unscheduledCount,
                'reason_summary' => $result['reason_summary'] ?? [],
                'reason_summary_translated' => $result['reason_summary_translated'] ?? [],
                'unscheduled' => $result['unscheduled'] ?? [],
            ];
            $this->timetable->refresh();
            $this->timetable->update([
                'generation_status' => 'completed',
                'generation_progress' => 100,
                'generation_finished_at' => now(),
                'generation_report' => $report,
            ]);
        } catch(\Throwable $e) {
            $this->timetable->update([
                'generation_status' => 'failed',
                'generation_finished_at' => now(),
            ]);
            throw $e;
        }

        // Notify all school admins & initiator
        if (method_exists($this->timetable, 'school')) {
            $school = $this->timetable->school;
            if ($school) {
                $userClass = \App\Models\User::class;
                $recipients = $userClass::query()->get()->filter(fn($u) => $u->isSupervisor() || $u->isSchoolAdmin($school->id));
                foreach ($recipients as $recipient) {
                    $recipient->notify(new TimetableGenerated($this->timetable));
                }
            }
        }
    }
}
