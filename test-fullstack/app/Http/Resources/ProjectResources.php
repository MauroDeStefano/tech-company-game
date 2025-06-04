<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ProjectResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'complexity' => [
                'level' => $this->complexity,
                'name' => $this->getComplexityName(),
                'stars' => $this->getComplexityStars(),
                'color' => $this->getComplexityColor(),
            ],
            'value' => [
                'amount' => $this->value,
                'formatted' => number_format($this->value, 2) . ' €',
                'hourly_rate' => round($this->getHourlyValue(), 2),
            ],
            'status' => [
                'code' => $this->status,
                'name' => $this->getStatusName(),
                'color' => $this->getStatusColor(),
            ],
            'assignment' => [
                'is_assigned' => $this->isAssigned(),
                'developer' => $this->whenLoaded('developer', function () {
                    return $this->developer ? new DeveloperResource($this->developer) : null;
                }),
            ],
            'timing' => [
                'started_at' => $this->started_at?->toISOString(),
                'estimated_completion_at' => $this->estimated_completion_at?->toISOString(),
                'completed_at' => $this->completed_at?->toISOString(),
                'current_progress' => round($this->calculateCurrentProgress(), 1),
                'seconds_remaining' => $this->getSecondsRemaining(),
                'time_remaining_formatted' => $this->getTimeRemainingFormatted(),
                'is_ready_for_completion' => $this->isReadyForCompletion(),
                'actual_duration_minutes' => $this->getActualDuration(),
            ],
            'created_by_sales_person' => $this->whenLoaded('createdBySalesPerson', function () {
                return $this->createdBySalesPerson ? new SalesPersonResource($this->createdBySalesPerson) : null;
            }),
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
