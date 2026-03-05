<?php

namespace App\Application;

use App\Application\Exception\InvalidGameInput;
use App\Application\Input\PlayerInput;
use App\Domain\Card;

final class GameInputValidator
{
    /**
     * @param list<PlayerInput> $players
     * @param list<Card> $board
     */
    public function assertNoDuplicateCards(array $players, array $board = []): void
    {
        $seen = [];

        foreach ($players as $player) {
            foreach ($player->cards as $card) {
                $this->assertUnique($seen, $card);
            }
        }

        foreach ($board as $card) {
            $this->assertUnique($seen, $card);
        }
    }

    /** @param array<string,true> $seen */
    private function assertUnique(array &$seen, Card $card): void
    {
        $key = $card->key();

        if (isset($seen[$key])) {
            throw new InvalidGameInput('duplicate card detected: ' . $key);
        }

        $seen[$key] = true;
    }
}
