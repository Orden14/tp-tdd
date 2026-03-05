<?php

namespace App\Application;

final readonly class PokerGameResult
{
    /**
     * @param list<PlayerHandResult> $results
     * @param list<PlayerHandResult> $winners
     */
    public function __construct(
        public array $results,
        public array $winners,
    ) {
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

    public function resultFor(string $playerId): ?PlayerHandResult
    {
        foreach ($this->results as $result) {
            if ($result->playerId === $playerId) {
                return $result;
            }
        }

        return null;
    }
}
