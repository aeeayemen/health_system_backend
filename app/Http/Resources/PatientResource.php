<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
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
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'current_weight' => $this->current_weight,
            'target_weight' => $this->target_weight,
            'height' => $this->height,
            'medical_history' => $this->medical_history,
            'allergies' => $this->allergies,
            'current_doctor_id' => $this->current_doctor_id,
            'doctor_name' => $this->doctor ? $this->doctor->name : null,
            'subscription_status' => $this->subscription_status,
            'subscription_end_date' => $this->subscription_end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
