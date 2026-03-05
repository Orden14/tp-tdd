<?php

namespace App\Application;

final readonly class PokerGameResult
{
    /** @param list<PlayerHandResult> $winners */
    public function __construct(public array $winners)
    {
    }

    /** @return list<string> */
    public function winnerIds(): array
    {
        $ids = [];
        foreach ($this->winners as $winner) {
            $ids[] = $winner->playerId;
        }

        return $ids;
    }
}
