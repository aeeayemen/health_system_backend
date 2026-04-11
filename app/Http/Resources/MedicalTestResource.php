<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalTestResource extends JsonResource
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
            'user_id' => $this->user_id,
            'patient_id' => $this->user_id, // Alias for patient_id
            'image' => $this->image ? url($this->image) : null,
            'status' => $this->status ?? 'pending',
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
