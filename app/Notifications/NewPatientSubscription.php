<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Patient;

class NewPatientSubscription extends Notification
{
    use Queueable;

    protected $patient;

    public function __construct($patient)
    {
        $this->patient = $patient;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $patientName = $this->patient->user->name ?? $this->patient->fullname ?? 'مريض جديد';

        return [
            'title' => 'مريض جديد',
            'body' => 'انضم مريض جديد إلى قائمتك: ' . $patientName,
            'type' => 'new_patient',
            'patient_id' => $this->patient->id,
        ];
    }
}
