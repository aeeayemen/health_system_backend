<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DietPlanAssigned extends Notification
{
    use Queueable;

    protected $dietPlan;

    public function __construct($dietPlan)
    {
        $this->dietPlan = $dietPlan;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'خطة غذائية جديدة',
            'body' => 'تم إضافة خطة غذائية جديدة لك. راجعها الآن!',
            'type' => 'diet_plan',
            'diet_id' => $this->dietPlan->id,
        ];
    }
}
