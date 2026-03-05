<?php

namespace App\Application\Parser;

use App\Application\Exception\InvalidGameInput;
use App\Domain\Card;
use App\Domain\Rank;
use App\Domain\Suit;
use InvalidArgumentException;

final class CardParser
{
    public function parseCard(string $token): Card
    {
        $token = strtoupper(trim($token));
        if (strlen($token) !== 2) {
            throw new InvalidGameInput('card must be exactly 2 chars like SK');
        }

        try {
            $suit = Suit::fromChar($token[0]);
            $rank = Rank::fromChar($token[1]);
        } catch (InvalidArgumentException $e) {
            throw new InvalidGameInput($e->getMessage(), previous: $e);
        }

        return new Card($suit, $rank);
    }

    /** @return list<Card> */
    public function parseTwoCards(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidGameInput('missing cards');
        }

        $parts = array_map('trim', explode(':', $value));
        if (count($parts) !== 2) {
            throw new InvalidGameInput('expected exactly 2 cards separated by ":"');
        }

        return [
            $this->parseCard($parts[0]),
            $this->parseCard($parts[1]),
        ];
    }
}
