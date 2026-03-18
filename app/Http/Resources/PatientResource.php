<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeSubscription = $this->id ? $this->subscriptions()->where('status', 'active')->latest()->first() : null;
        $isSubscribed = !is_null($activeSubscription);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->fullname,
            'date_of_birth' => $this->birthdate,
            'gender' => $this->gender,
            'current_weight' => $this->weight,
            'target_weight' => $this->target_weight,
            'height' => $this->height,
            'medical_history' => $this->medical,
            'allergies' => $this->allergies,
            'physical_activity' => $this->physical_activity,
            'image' => $this->image,
            'current_doctor_id' => $this->current_doctor_id ?? ($activeSubscription ? $activeSubscription->doctor_id : null),
            'doctor_name' => $this->doctor ? $this->doctor->name : ($activeSubscription && $activeSubscription->doctor ? $activeSubscription->doctor->name : null),
            'is_subscribed' => $isSubscribed,
            'active_subscription' => $activeSubscription,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
