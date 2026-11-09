<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'address' => $this->address,
            'status' => $this->status,
            'employees_count' => $this->employees_count,
            'progress' => $this->progress,
            'period_end' => $this->period_end?->format('d.m'),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'assigned_shifts' => AssignedShiftResource::collection($this->whenLoaded('assignedShifts')),
        ];
    }
}
