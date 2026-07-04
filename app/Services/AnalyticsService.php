<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ClickLog;
use App\Models\SentenceLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Generate full statistics for a given list of children.
     */
    public function generateStatistics($children)
    {
        $statistics = [];

        foreach ($children as $child) {
            $mostUsed = $this->getMostUsedPictograms($child->id);
            $mluData = $this->getMluData($child->id);

            $trend = $this->calculateTrend($mluData);

            $predictionData = [];
            $lastIndex = count($mluData);
            
            if ($lastIndex >= 2 && $trend['a'] !== null) {
                $lastDay = end($mluData)['day_index'];
                for ($i = 1; $i <= 30; $i++) {
                    $val = $trend['a'] * ($lastDay + $i) + $trend['b'];
                    $predictionData[] = round(max(1, $val), 2);
                }
            }

            $statistics[] = [
                'child' => $child,
                'chartLabels' => $mostUsed->pluck('name'),
                'chartData' => $mostUsed->pluck('total'),
                'mluLabels' => array_column($mluData, 'date'),
                'mluData' => array_map(fn($log) => round($log['mlu'], 2), $mluData),
                'predictionData' => $predictionData,
                'growthRate' => $trend['a'] !== null ? round($trend['a'] * 100, 1) : 0,
                'rSquared' => $trend['r2'] !== null ? round($trend['r2'], 2) : 0,
                'isSignificant' => $trend['r2'] !== null && $trend['r2'] > 0.5 && $lastIndex >= 3
            ];
        }

        return $statistics;
    }

    private function getMostUsedPictograms(int $childId)
    {
        return ClickLog::where('child_id', $childId)
            ->join('pictograms', 'click_logs.pictogram_id', '=', 'pictograms.id')
            ->select('pictograms.name', DB::raw('count(*) as total'))
            ->groupBy('pictograms.name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    private function getMluData(int $childId)
    {
        $data = SentenceLog::where('child_id', $childId)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(length) as mlu'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return [];
        }

        $firstDate = Carbon::parse($data->first()->date);
        
        return $data->map(function ($log) use ($firstDate) {
            $currentDate = Carbon::parse($log->date);
            return [
                'date' => $log->date,
                'mlu' => (float) $log->mlu,
                'day_index' => $firstDate->diffInDays($currentDate)
            ];
        })->toArray();
    }

    /**
     * Calculate Linear Regression (y = ax + b) and R-squared.
     * X is the day_index (number of days since first log), Y is MLU.
     */
    private function calculateTrend(array $data)
    {
        $n = count($data);
        if ($n < 2) return ['a' => null, 'b' => null, 'r2' => null];

        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0; $sumY2 = 0;

        foreach ($data as $point) {
            $x = $point['day_index'];
            $y = $point['mlu'];
            
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
            $sumY2 += ($y * $y);
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        
        if ($denominator == 0) {
            return ['a' => 0, 'b' => $sumY / $n, 'r2' => 0];
        }

        $a = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $b = ($sumY - $a * $sumX) / $n;

        $sst = $sumY2 - ($sumY * $sumY) / $n;
        
        if ($sst == 0) {
            $r2 = 1;
        } else {
            $sse = 0;
            foreach ($data as $point) {
                $predictedY = $a * $point['day_index'] + $b;
                $sse += pow($point['mlu'] - $predictedY, 2);
            }
            $r2 = 1 - ($sse / $sst);
        }

        return ['a' => $a, 'b' => $b, 'r2' => max(0, $r2)];
    }
}
