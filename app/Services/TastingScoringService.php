<?php

namespace App\Services;

use App\Models\TastingRound;

class TastingScoringService
{
    /**
     * Compute round scores: for each tag, n participants chose it -> n*(n-1)/2 * 10 team points.
     * Per participant: sum over tags they chose of (count(tag)-1)*10.
     */
    public function computeRoundScores(TastingRound $round): array
    {
        $submissions = $round->submissions()->with('sessionParticipant')->get();
        $tagCounts = [];
        $participantTags = []; // participant_id => [tags]

        foreach ($submissions as $sub) {
            $pid = $sub->session_participant_id;
            $participantTags[$pid] = $sub->taste_tags ?? [];
            foreach ($sub->taste_tags ?? [] as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }

        $teamTotal = 0;
        foreach ($tagCounts as $n) {
            if ($n >= 2) {
                $teamTotal += $n * ($n - 1) / 2 * 10;
            }
        }

        $roundScores = [];
        foreach ($participantTags as $pid => $tags) {
            $points = 0;
            foreach ($tags as $tag) {
                $n = $tagCounts[$tag] ?? 0;
                $points += ($n - 1) * 10;
            }
            $roundScores[$pid] = $points;
        }

        return [
            'team_total' => $teamTotal,
            'round_score' => $roundScores,
        ];
    }
}
