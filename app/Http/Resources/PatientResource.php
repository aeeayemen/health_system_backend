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
        $activeSubscription = null;
        try {
            if ($this->id) {
                $sub = $this->subscriptions()->where('status', 'active')->latest()->first();
                $activeSubscription = $sub ? clone $sub : null;
            }
        } catch (\Exception $e) {
            // Ignore missing relations quietly
        }

        $isSubscribed = !is_null($activeSubscription);

        $doctorName = null;
        try {
            if ($this->doctor) {
                $doctorName = $this->doctor->name;
            } elseif ($activeSubscription && $activeSubscription->doctor) {
                $doctorName = $activeSubscription->doctor->name;
            }
        } catch (\Exception $e) {
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->fullname ?? ($this->user->name ?? null),
            'date_of_birth' => $this->birthdate,
            'age' => $this->age, // age attribute has its own try/catch in the model
            'gender' => $this->gender ?? 'male',
            'current_weight' => $this->weight,
            'target_weight' => $this->target_weight,
            'height' => $this->height,
            'medical_history' => $this->medical,
            'allergies' => $this->allergies,
            'physical_activity' => $this->physical_activity,
            'phone_number' => $this->phone_number ?? ($this->user->phone ?? null),
            'image' => $this->image,
            'current_doctor_id' => $this->current_doctor_id ?? ($activeSubscription->doctor_id ?? null),
            'doctor_name' => $doctorName,
            'is_subscribed' => $isSubscribed,
            'active_subscription' => $activeSubscription,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
