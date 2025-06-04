<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ProjectGenerationResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => [
                'code' => $this->status,
                'name' => $this->getStatusName(),
                'color' => $this->getStatusColor(),
            ],
            'estimated_value' => [
                'amount' => $this->estimated_value,
                'formatted' => number_format($this->estimated_value, 2) . ' €',
            ],
            'timing' => [
                'started_at' => $this->started_at?->toISOString(),
                'estimated_completion_at' => $this->estimated_completion_at?->toISOString(),
                'completed_at' => $this->completed_at?->toISOString(),
                'current_progress' => round($this->calculateCurrentProgress(), 1),
                'seconds_remaining' => $this->getSecondsRemaining(),
                'time_remaining_formatted' => $this->getTimeRemainingFormatted(),
                'estimated_duration_minutes' => $this->getEstimatedDuration(),
                'is_ready_for_completion' => $this->isReadyForCompletion(),
            ],
            'sales_person' => $this->whenLoaded('salesPerson', function () {
                return new SalesPersonResource($this->salesPerson);
            }),
            'actual_project' => $this->whenLoaded('actualProject', function () {
                return $this->actualProject ? new ProjectResource($this->actualProject) : null;
            }),
            'description' => $this->getDescription(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}