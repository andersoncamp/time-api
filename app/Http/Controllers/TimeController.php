<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function handle(Request $request)
    {
        $occurrences = $this->morphRequestInputIntoCarbonInstances($request->get('occurrences'));
        $groupedOccurrences = $this->getTimeStampsGroupedByHour($occurrences);
        $timeWithMostOccurrences = $this->getTimeWithMostOccurrences($groupedOccurrences);
        $occurrencesPerHour = $this->getAverageOfOccurrencesPerHour($groupedOccurrences);
        list($biggestInterval, $smallestInterval) = $this->getBiggestAndSmallestIntervals($occurrences);

        return [
            'grouped_occurrences' => $groupedOccurrences,
            'most_occurrences_at' => $timeWithMostOccurrences,
            'occurrences_per_hour' => $occurrencesPerHour,
            'biggest_interval_in_minutes' => $biggestInterval,
            'smallest_interval_in_minutes' => $smallestInterval,
        ];
    }

    /**
     * @param array $occurrences
     * @return Carbon[]
     */
    private function morphRequestInputIntoCarbonInstances(array $occurrences): array
    {
        return array_map(function (string $occurrence) {
            list($hour, $minutes) = explode(':', $occurrence);
            return Carbon::createFromTime($hour, $minutes, 0, 'America/Sao_Paulo');
        }, $occurrences);
    }

    /**
     * @param Carbon[] $carbonOccurrences
     * @return array
     */
    private function getTimeStampsGroupedByHour(array $carbonOccurrences): array
    {
        $hours = array_map(function (Carbon $timestamp) {
            return $timestamp->hour;
        }, $carbonOccurrences);

        $groupedOccurrences = array_count_values($hours);
        return $groupedOccurrences;
    }

    /**
     * @param array $groupedOccurrences
     * @return int
     */
    private function getTimeWithMostOccurrences(array $groupedOccurrences): int
    {
        return array_keys($groupedOccurrences, max($groupedOccurrences))[0];
    }

    /**
     * @param Carbon[] $occurrences
     * @return Carbon[]
     */
    private function sortTimeStamps(array $occurrences): array
    {
        $sortedOccurrences = collect($occurrences)->sortBy(function ($obj) {
            return $obj;
        })->toArray();
        return $sortedOccurrences;
    }

    /**
     * @param Carbon[] $occurrences
     * @return array
     */
    private function getBiggestAndSmallestIntervals(array $occurrences): array
    {
        $sortedOccurrences = $this->sortTimeStamps($occurrences);

        $intervals = array_reduce($sortedOccurrences, function (array $carry, Carbon $item) {
            $sub = $item->diffInMinutes($carry[0]);
            $biggest = $sub > $carry[1] ? $sub : $carry[1];
            $smallest = $sub > $carry[2] ? $carry[2] : $sub;

            return [$item, $biggest, $smallest];
        }, [$sortedOccurrences[0], 0, 86400]);
        return [$intervals[1], $intervals[2]];
    }

    /**
     * @param $groupedOccurrences
     * @return float
     */
    private function getAverageOfOccurrencesPerHour($groupedOccurrences): float
    {
        return round((array_sum($groupedOccurrences) / count($groupedOccurrences)), 2);
    }
}
