<?php

namespace App\Application;

use App\Poker\Hand\EvaluatedHand;

final readonly class PlayerHandResult
{
    public function __construct(
        public string $playerId,
        public EvaluatedHand $hand,
    ) {
    }
}
