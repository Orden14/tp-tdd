<?php

namespace App\Application\Input;

use App\Domain\Card;

final readonly class PlayerInput
{
    /** @param list<Card> $cards */
    public function __construct(
        public string $id,
        public array $cards,
    ) {
    }
}
