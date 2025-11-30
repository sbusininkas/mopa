<?php

namespace App\Notifications;

use App\Models\Timetable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimetableGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Timetable $timetable)
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        $r = $this->timetable->generation_report ?? [];
        $unscheduledCount = $r['unscheduled_count'] ?? 0;
        $unscheduledUnits = $r['unscheduled_units'] ?? 0;
        $totalUnits = $r['total_units'] ?? null;
        $placedUnits = $r['placed_units'] ?? null;
        $message = 'Tvarkaraštis "' . $this->timetable->name . '" sugeneruotas. ';
        if ($totalUnits !== null && $placedUnits !== null) {
            $message .= 'Paskirstyta ' . $placedUnits . '/' . $totalUnits . ' pamokų. ';
        }
        if ($unscheduledUnits > 0) {
            $message .= 'Nepaskirstyta pamokų: ' . $unscheduledUnits . ' (grupių: ' . $unscheduledCount . ').';
        } else {
            $message .= 'Visos pamokos paskirstytos.';
        }
        return [
            'message' => $message,
            'timetable_id' => $this->timetable->id,
            'timetable_name' => $this->timetable->name,
            'unscheduled_count' => $unscheduledCount,
            'unscheduled_units' => $unscheduledUnits,
            'total_units' => $totalUnits,
            'placed_units' => $placedUnits,
            'url' => route('schools.timetables.teachers-view', [$this->timetable->school_id, $this->timetable->id]),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $r = $this->timetable->generation_report ?? [];
        $unscheduledCount = $r['unscheduled_count'] ?? 0;
        $unscheduledUnits = $r['unscheduled_units'] ?? 0;
        $totalUnits = $r['total_units'] ?? null;
        $placedUnits = $r['placed_units'] ?? null;
        $mail = (new MailMessage)
            ->subject('Timetable generation completed')
            ->line('Timetable "' . $this->timetable->name . '" generation finished.')
            ->action('View Teachers\' Timetable', route('schools.timetables.teachers-view', [$this->timetable->school_id, $this->timetable->id]));
        if ($totalUnits !== null && $placedUnits !== null) {
            $mail->line('Placed lessons: ' . $placedUnits . '/' . $totalUnits . '.');
        }
        if ($unscheduledUnits > 0) {
            $mail->line('Unscheduled lessons: ' . $unscheduledUnits . ' across ' . $unscheduledCount . ' groups.');
        } else {
            $mail->line('All requested lessons were scheduled.');
        }
        return $mail;
    }
}
