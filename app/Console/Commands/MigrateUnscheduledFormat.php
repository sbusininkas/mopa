<?php

namespace App\Console\Commands;

use App\Models\Timetable;
use App\Models\TimetableGroup;
use Illuminate\Console\Command;

class MigrateUnscheduledFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timetable:migrate-unscheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old unscheduled format to new format with teacher_login_key_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of unscheduled entries...');
        
        $timetables = Timetable::whereNotNull('generation_report')->get();
        $totalMigrated = 0;
        $totalTimetables = 0;

        foreach ($timetables as $timetable) {
            $report = $timetable->generation_report ?? [];
            
            if (!isset($report['unscheduled']) || empty($report['unscheduled'])) {
                continue;
            }

            $migrated = false;
            
            foreach ($report['unscheduled'] as &$u) {
                // Check if migration is needed
                $needsMigration = false;
                
                // Migrate old format to new if needed
                if (!isset($u['group_name']) && isset($u['group'])) {
                    $u['group_name'] = $u['group'];
                    $needsMigration = true;
                }
                if (!isset($u['subject_name']) && isset($u['subject'])) {
                    $u['subject_name'] = $u['subject'];
                    $needsMigration = true;
                }
                if (!isset($u['teacher_name']) && isset($u['teacher'])) {
                    $u['teacher_name'] = $u['teacher'];
                    $needsMigration = true;
                }
                
                // Find teacher_login_key_id if missing
                if (!isset($u['teacher_login_key_id'])) {
                    $group = TimetableGroup::find($u['group_id']);
                    if ($group && $group->teacher_login_key_id) {
                        $u['teacher_login_key_id'] = $group->teacher_login_key_id;
                        $u['teacher_name'] = $group->teacherLoginKey?->full_name ?? $u['teacher_name'] ?? $u['teacher'] ?? '';
                        $needsMigration = true;
                    }
                }
                
                if ($needsMigration) {
                    $migrated = true;
                }
            }
            unset($u);
            
            if ($migrated) {
                $timetable->update(['generation_report' => $report]);
                $totalMigrated += count($report['unscheduled']);
                $totalTimetables++;
                $this->info("Migrated timetable ID {$timetable->id} with " . count($report['unscheduled']) . " unscheduled entries");
            }
        }

        $this->info("Migration complete!");
        $this->info("Timetables migrated: {$totalTimetables}");
        $this->info("Total unscheduled entries migrated: {$totalMigrated}");
        
        return Command::SUCCESS;
    }
}
