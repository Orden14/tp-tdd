<?php

namespace App\Poker\Hand;

use App\Domain\Card;
use App\Domain\Rank;

final class HandEvaluator
{
    /**
     * @param list<Card> $cards
     */
    public function evaluateBestHand(array $cards): EvaluatedHand
    {
        $sorted = $cards;
        usort($sorted, static function (Card $a, Card $b): int {
            return self::rankValue($b->rank) <=> self::rankValue($a->rank);
        });

        $pairRank = $this->findBestPairRank($sorted);
        if ($pairRank !== null) {
            $pairCards = [];
            $kickers = [];

            foreach ($sorted as $card) {
                if ($card->rank === $pairRank && count($pairCards) < 2) {
                    $pairCards[] = $card;
                    continue;
                }

                if ($card->rank !== $pairRank && count($kickers) < 3) {
                    $kickers[] = $card;
                }
            }

            return new EvaluatedHand(HandCategory::OnePair, array_merge($pairCards, $kickers));
        }

        $bestFive = array_slice($sorted, 0, 5);
        return new EvaluatedHand(HandCategory::HighCard, $bestFive);
    }

    /** @param list<Card> $sortedDesc */
    private function findBestPairRank(array $sortedDesc): ?Rank
    {
        $counts = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $best = null;
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            if (($counts[$key] ?? 0) >= 2) {
                if ($best === null || self::rankValue($card->rank) > self::rankValue($best)) {
                    $best = $card->rank;
                }
            }
        }

        return $best;
    }

    private static function rankValue(Rank $rank): int
    {
        return match ($rank) {
            Rank::Two => 2,
            Rank::Three => 3,
            Rank::Four => 4,
            Rank::Five => 5,
            Rank::Six => 6,
            Rank::Seven => 7,
            Rank::Eight => 8,
            Rank::Nine => 9,
            Rank::Ten => 10,
            Rank::Jack => 11,
            Rank::Queen => 12,
            Rank::King => 13,
            Rank::Ace => 14,
        };
    }
}
