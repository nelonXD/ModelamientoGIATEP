<?php

namespace App\Http\Resources\Api\V1;

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
            'rut' => $this->rut,
            'name' => $this->name,
            'email' => $this->email,
            'job_title' => $this->job_title,
            'phone' => $this->phone,
            'status' => $this->status,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('slug')->values()),
            'establishments' => $this->whenLoaded('establishments', fn () => $this->establishments->map(fn ($establishment): array => [
                'id' => $establishment->id,
                'type' => $establishment->type,
                'name' => $establishment->name,
            ])->values()),
        ];
    }
}
