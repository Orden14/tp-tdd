<?php

namespace App\Poker\Hand;

use App\Poker\Rank\RankValue;

final class HandComparer
{
    /**
     * Retourne 1 si $a gagne, -1 si $b gagne, 0 si égalité.
     */
    public function compare(EvaluatedHand $a, EvaluatedHand $b): int
    {
        if ($a->category !== $b->category) {
            return $a->category->value <=> $b->category->value;
        }

        $aValues = $this->tieBreakerValues($a);
        $bValues = $this->tieBreakerValues($b);

        $len = min(count($aValues), count($bValues));
        for ($i = 0; $i < $len; $i++) {
            if ($aValues[$i] === $bValues[$i]) {
                continue;
            }
            return $aValues[$i] <=> $bValues[$i];
        }

        return 0;
    }

    /**
     * @return list<int>
     */
    private function tieBreakerValues(EvaluatedHand $hand): array
    {
        $values = [];
        foreach ($hand->cards as $card) {
            $values[] = RankValue::toInt($card->rank);
        }
        return $values;
    }
}
