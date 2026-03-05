<?php

namespace App\Poker\Rank;

use App\Domain\Rank;

final class RankValue
{
    public static function toInt(Rank $rank): int
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

