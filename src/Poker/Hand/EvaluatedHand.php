<?php

namespace App\Poker\Hand;

use App\Domain\Card;

final readonly class EvaluatedHand
{
    /** @param list<Card> $cards Best 5 cards (ordered) */
    public function __construct(
        public HandCategory $category,
        public array $cards,
    ) {
    }

    /** @return list<string> */
    public function cardsAsKeys(): array
    {
        $keys = [];
        foreach ($this->cards as $card) {
            $keys[] = $card->key();
        }
        return $keys;
    }
}
