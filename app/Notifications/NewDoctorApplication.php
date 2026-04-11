<?php

namespace App\Notifications;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewDoctorApplication extends Notification
{
    use Queueable;

    protected $doctor;

    public function __construct(Doctor $doctor)
    {
        $this->doctor = $doctor;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('طلب انضمام طبيب جديد')
                    ->line('يوجد طلب جديد من الطبيب: ' . $this->doctor->name)
                    ->line('التخصص: ' . $this->doctor->specialization)
                    ->action('مراجعة الطلب', url('/admin/doctors/pending'))
                    ->line('شكراً لاستخدامكم تطبيقنا!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'doctor_id' => $this->doctor->id,
            'name' => $this->doctor->name,
            'title' => 'طلب انضمام جديد',
            'body' => 'قدم الطبيب ' . $this->doctor->name . ' طلباً للانضمام إلى المنصة.',
            'type' => 'new_doctor_application',
        ];
    }
}
