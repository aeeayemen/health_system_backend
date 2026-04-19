<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->user?->email ?? null,
            'gender' => $this->gender,
            'degree' => $this->degree,
            'bank_account' => $this->bank_account,
            'phone_number' => $this->phone_number,
            'CV' => $this->CV,
            'user_id' => $this->user_id,
            'admin_id' => $this->admin_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'application_status' => $this->application_status,
            'specialization' => $this->specialization,
            'license_number' => $this->license_number,
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->bio,
            'profile_image' => $this->profile_image,
            'is_verified' => $this->is_verified,
            'rating' => $this->rating,
            'consultation_fee' => $this->consultation_fee,
            'is_available' => $this->is_available,
        ];
    }
}
