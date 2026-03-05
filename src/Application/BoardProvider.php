<?php

namespace App\Application;

use App\Application\Parser\CardParser;
use App\Domain\Card;
use App\Domain\Deck;

final readonly class BoardProvider
{
    public function __construct(
        private CardParser $parser,
        private Deck $deck,
    ) {
    }

    /**
     * @param string|null $boardArg
     * @param list<Card> $excluded
     * @return list<Card>
     */
    public function provide(?string $boardArg, array $excluded): array
    {
        if ($boardArg !== null) {
            return $this->parser->parseCards($boardArg, 5);
        }

        $this->deck->exclude($excluded);

        return $this->deck->draw(5);
    }
}
