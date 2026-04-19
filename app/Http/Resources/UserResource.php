<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'doctor_profile' => $this->whenLoaded('doctor', function () {
                return $this->doctor ? new DoctorResource($this->doctor) : null;
            }),
            'patient_profile' => $this->whenLoaded('patient', function () {
                return $this->patient ? new PatientResource($this->patient) : null;
            }),
            'created_at' => $this->created_at,
        ];
    }
}
