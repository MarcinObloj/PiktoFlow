<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ClickLog;
use App\Models\Pictogram;
use Illuminate\Support\Facades\DB;

class PredictionService
{
    /**
     * Predict the next pictograms based on the last clicked pictogram using Markov Chains.
     */
    public function predictNextPictograms(Child $child, ?int $lastId, array $excludeIds = [], int $limit = 4)
    {
        if (!$lastId) {
            return $this->getMostPopularPictograms($child->id, $excludeIds, $limit);
        }

        // Get recent logs for this child to find transitions
        $logs = ClickLog::where('child_id', $child->id)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get();

        $transitions = [];

        // Build transition matrix (Markov Chain)
        for ($i = count($logs) - 1; $i > 0; $i--) {
            $current = $logs[$i];
            $next = $logs[$i - 1];

            // If the clicks are within 120 seconds, consider them a sequence
            if ($current->created_at->diffInSeconds($next->created_at) < 120) {
                if ($current->pictogram_id == $lastId) {
                    $nextId = $next->pictogram_id;
                    // Don't predict the exact same word or words already in the sentence
                    if ($nextId != $lastId && !in_array($nextId, $excludeIds)) {
                        $transitions[$nextId] = ($transitions[$nextId] ?? 0) + 1;
                    }
                }
            }
        }

        arsort($transitions);
        $predictedIds = array_slice(array_keys($transitions), 0, $limit);

        // Fallback to most popular if no predictions found
        if (empty($predictedIds)) {
            return $this->getMostPopularPictograms($child->id, $excludeIds, $limit);
        }

        $pictograms = Pictogram::whereIn('id', $predictedIds)->get();
        return $pictograms->sortBy(function ($model) use ($predictedIds) {
            return array_search($model->id, $predictedIds);
        })->values();
    }

    /**
     * Get the most popular pictograms for a child as a fallback.
     */
    private function getMostPopularPictograms(int $childId, array $excludeIds, int $limit)
    {
        $query = ClickLog::where('child_id', $childId);
        
        if (!empty($excludeIds)) {
            $query->whereNotIn('pictogram_id', $excludeIds);
        }

        $popularIds = $query->select('pictogram_id', DB::raw('count(*) as total'))
            ->groupBy('pictogram_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('pictogram_id');

        $pictograms = Pictogram::whereIn('id', $popularIds)->get();
        return $pictograms->sortBy(function ($model) use ($popularIds) {
            return array_search($model->id, $popularIds->toArray());
        })->values();
    }
}
