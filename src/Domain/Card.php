<?php

namespace App\Domain;

final readonly class Card
{
    public function __construct(
        public Suit $suit,
        public Rank $rank,
    ) {
    }

    public function key(): string
    {
        return $this->suit->value . $this->rank->value;
    }
}
