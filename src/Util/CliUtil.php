<?php

namespace App\Util;

use App\Domain\Rank;
use App\Domain\Suit;
use InvalidArgumentException;

final readonly class CliUtil
{
    public static function parseCardsArg(?string $value): array
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException('missing cards');
        }

        $parts = explode(':', $value);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('expected exactly 2 cards separated by ":"');
        }

        return array_map('trim', $parts);
    }

    public static function parseCard(string $token): array
    {
        $token = strtoupper(trim($token));
        if (strlen($token) !== 2) {
            throw new InvalidArgumentException('card must be exactly 2 chars like SK');
        }

        $suitChar = $token[0];
        $rankChar = $token[1];

        // On conserve les messages 'invalid suit' / 'invalid rank' grâce aux enums.
        $suit = Suit::fromChar($suitChar);
        $rank = Rank::fromChar($rankChar);

        return [$suit, $rank];
    }
}
