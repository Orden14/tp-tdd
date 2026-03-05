<?php

namespace App\Application;

use App\Application\Exception\InvalidGameInput;
use App\Application\Input\PlayerInput;

final class GameInputValidator
{
    /** @param list<PlayerInput> $players */
    public function assertNoDuplicateCards(array $players): void
    {
        $seen = [];

        foreach ($players as $player) {
            foreach ($player->cards as $card) {
                $key = $card->key();

                if (isset($seen[$key])) {
                    throw new InvalidGameInput('duplicate card detected');
                }

                $seen[$key] = true;
            }
        }
    }
}
