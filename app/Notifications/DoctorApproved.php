<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoctorApproved extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم قبول طلبك',
            'body' => 'مبروك! تم قبول طلبك كدكتور في المنصة. يمكنك الآن البدء باستقبال المرضى.',
            'type' => 'doctor_approved',
        ];
    }
}
