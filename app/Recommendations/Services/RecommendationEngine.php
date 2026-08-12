<?php

namespace App\Recommendations\Services;

class RecommendationEngine
{
    public function rank(array $candidates, array $profile, int $limit = 10): array
    {
        $interests = array_fill_keys(array_map('strtolower', $profile['interests'] ?? []), true);
        $seen = array_fill_keys($profile['seen_ids'] ?? [], true);
        foreach ($candidates as &$candidate) {
            $score = (float) ($candidate['weight'] ?? 0);
            foreach (array_map('strtolower', $candidate['tags'] ?? []) as $tag) {
                if (isset($interests[$tag])) {
                    $score += 10;
                }
            }
            if (isset($seen[$candidate['id'] ?? null])) {
                $score -= 100;
            }
            $candidate['_score'] = $score;
        }
        unset($candidate);
        usort($candidates, fn ($a, $b) => [$b['_score'], $b['id']] <=> [$a['_score'], $a['id']]);

        return array_slice($candidates, 0, max(0, $limit));
    }
}
