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
        usort($cards, static function (Card $a, Card $b): int {
            $aV = self::rankValue($a->rank);
            $bV = self::rankValue($b->rank);

            return $bV <=> $aV;
        });

        $bestFive = array_slice($cards, 0, 5);

        return new EvaluatedHand(HandCategory::HighCard, $bestFive);
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
