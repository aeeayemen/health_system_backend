<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Doctor;

class NewDoctorApplication extends Notification
{
    use Queueable;

    protected Doctor $doctor;

    public function __construct(Doctor $doctor)
    {
        $this->doctor = $doctor;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'طلب دكتور جديد',
            'body' => 'تم تقديم طلب تسجيل دكتور جديد: ' . $this->doctor->name,
            'type' => 'doctor_application',
            'doctor_id' => $this->doctor->id,
        ];
    }
}
