<?php

namespace App\Services;

use App\Models\TastingRound;

class TastingScoringService
{
    private const PROFILE_META = [
        'profile:color_viscosity:' => ['name' => 'Viscositeit', 'emoji' => '💧'],
        'profile:nose_intensity:' => ['name' => 'Neus intensiteit', 'emoji' => '👃'],
        'profile:nose_complexity:' => ['name' => 'Neus complexiteit', 'emoji' => '🧠'],
        'profile:taste_mouthfeel:' => ['name' => 'Mondgevoel', 'emoji' => '👄'],
        'profile:taste_finish:' => ['name' => 'Afdronk', 'emoji' => '⏳'],
        'profile:taste_development:' => ['name' => 'Ontwikkeling', 'emoji' => '🔄'],
    ];

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
            $tags = $this->normalizedSubmissionTags($sub);

            $participantTags[$pid] = $tags;

            foreach ($tags as $tag) {
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

    /**
     * Compute a detailed breakdown for a round:
     * - per tag: how many players picked it, team points, and which participants contributed
     * - per participant: total points and points per tag they picked
     *
     * This does not alter any stored scores; it is purely for display in the UI.
     */
    public function computeRoundDetails(TastingRound $round): array
    {
        $submissions = $round->submissions()->with('sessionParticipant')->get();

        $tagCounts = [];
        $participantTags = []; // participant_id => [tags]
        $participants = []; // participant_id => SessionParticipant

        foreach ($submissions as $sub) {
            $participant = $sub->sessionParticipant;
            if (! $participant) {
                continue;
            }

            $pid = $participant->id;
            $participants[$pid] = $participant;

            $tags = $this->normalizedSubmissionTags($sub);
            $participantTags[$pid] = $tags;

            foreach ($tags as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }

        // Load tag metadata (name + emoji via category) in one query where possible.
        $tagMeta = [];
        if (! empty($tagCounts)) {
            $slugs = array_keys($tagCounts);
            $tasteSlugs = [];
            foreach ($slugs as $slug) {
                if (str_starts_with($slug, 'color:')) {
                    continue;
                }
                if (str_starts_with($slug, 'nose:')) {
                    $tasteSlugs[] = substr($slug, 5);
                } else {
                    $tasteSlugs[] = $slug;
                }
            }

            $tasteSlugs = array_unique($tasteSlugs);

            $tagModels = empty($tasteSlugs)
                ? collect()
                : \App\Models\TasteTag::with('category')
                    ->whereIn('slug', $tasteSlugs)
                    ->get()
                    ->keyBy('slug');

            foreach ($tagCounts as $slug => $_count) {
                if (str_starts_with($slug, 'color:')) {
                    $raw = substr($slug, 6);
                    $label = ucfirst($raw);
                    $name = $label;
                    $emoji = '🎨';
                } elseif (str_starts_with($slug, 'profile:')) {
                    $meta = $this->profileMeta($slug);
                    $name = $meta['name'];
                    $emoji = $meta['emoji'];
                } elseif (str_starts_with($slug, 'nose:')) {
                    $base = substr($slug, 5);
                    $model = $tagModels->get($base);
                    $baseName = $model ? $model->name : $base;
                    $name = 'Neus: '.$baseName;
                    $emoji = '👃';
                } else {
                    $model = $tagModels->get($slug);
                    $name = $model ? $model->name : $slug;
                    $emoji = $model && $model->category ? $model->category->emoji : null;
                }

                $tagMeta[$slug] = [
                    'slug' => $slug,
                    'name' => $name,
                    'emoji' => $emoji,
                ];
            }
        }

        $participantsDetail = [];
        $tagsDetail = [];

        // First pass: per participant, compute points per tag and totals.
        foreach ($participantTags as $pid => $tags) {
            $participant = $participants[$pid] ?? null;
            $displayName = $participant ? $participant->display_name : ('#'.$pid);

            $byTag = [];
            $total = 0;

            foreach ($tags as $slug) {
                $n = $tagCounts[$slug] ?? 0;
                if ($n < 2) {
                    // Unique tags do not generate points.
                    $points = 0;
                } else {
                    $points = ($n - 1) * 10;
                }

                $byTag[$slug] = ($byTag[$slug] ?? 0) + $points;
                $total += $points;

                // Prepare per-tag participants list; we will fill metadata below.
                if (! isset($tagsDetail[$slug])) {
                    $meta = $tagMeta[$slug] ?? ['slug' => $slug, 'name' => $slug, 'emoji' => null];
                    $tagsDetail[$slug] = [
                        'slug' => $meta['slug'],
                        'name' => $meta['name'],
                        'emoji' => $meta['emoji'],
                        'count' => $tagCounts[$slug] ?? 0,
                        'team_points' => 0,
                        'participants' => [],
                    ];
                }

                $tagsDetail[$slug]['participants'][] = [
                    'id' => $pid,
                    'name' => $displayName,
                    'points' => $points,
                ];
            }

            $participantsDetail[$pid] = [
                'id' => $pid,
                'name' => $displayName,
                'total' => $total,
                'by_tag' => $byTag,
            ];
        }

        // Compute team points per tag and sort structures for nicer display.
        foreach ($tagsDetail as $slug => &$tag) {
            $n = $tag['count'];
            $tag['team_points'] = $n >= 2 ? (int) ($n * ($n - 1) / 2 * 10) : 0;

            usort($tag['participants'], function (array $a, array $b) {
                if ($a['points'] === $b['points']) {
                    return strcmp($a['name'], $b['name']);
                }

                return $b['points'] <=> $a['points'];
            });
        }
        unset($tag);

        // Sort tags by team_points desc, then by name.
        uasort($tagsDetail, function (array $a, array $b) {
            if ($a['team_points'] === $b['team_points']) {
                return strcmp($a['name'], $b['name']);
            }

            return $b['team_points'] <=> $a['team_points'];
        });

        // Sort participants by total desc, then name.
        uasort($participantsDetail, function (array $a, array $b) {
            if ($a['total'] === $b['total']) {
                return strcmp($a['name'], $b['name']);
            }

            return $b['total'] <=> $a['total'];
        });

        return [
            'tags' => $tagsDetail,
            'participants' => $participantsDetail,
        ];
    }

    private function normalizedSubmissionTags($sub): array
    {
        $tags = $sub->taste_tags ?? [];

        foreach ($sub->nose_tags ?? [] as $noseSlug) {
            $tags[] = 'nose:'.$noseSlug;
        }

        if (! empty($sub->color)) {
            $tags[] = 'color:'.mb_strtolower($sub->color);
        }

        foreach ([
            'color_viscosity',
            'nose_intensity',
            'nose_complexity',
            'taste_mouthfeel',
            'taste_finish',
            'taste_development',
        ] as $field) {
            $value = $sub->{$field} ?? null;
            if (! is_null($value) && $value >= 1 && $value <= 5) {
                $tags[] = 'profile:'.$field.':'.$value;
            }
        }

        return $tags;
    }

    private function profileMeta(string $slug): array
    {
        foreach (self::PROFILE_META as $prefix => $meta) {
            if (str_starts_with($slug, $prefix)) {
                $value = substr($slug, strlen($prefix));
                return [
                    'name' => $meta['name'].': '.$value.'/5',
                    'emoji' => $meta['emoji'],
                ];
            }
        }

        return ['name' => $slug, 'emoji' => '🎚️'];
    }
}
