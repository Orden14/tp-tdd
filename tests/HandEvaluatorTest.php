<?php

namespace Tests;

use App\Application\Parser\CardParser;
use App\Poker\Hand\HandCategory;
use App\Poker\Hand\HandEvaluator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class HandEvaluatorTest extends TestCase
{
    public function testHighCardSelectsBestFiveCardsOutOfSeven(): void
    {
        $parser = new CardParser();

        // 7 cartes: pas de paire/flush/straight. Meilleure main = A, K, Q, J, 9.
        $cards = $parser->parseCards('SA:DK:HQ:CJ:D9:S7:H2', 7);

        $evaluator = new HandEvaluator();
        $hand = $evaluator->evaluateBestHand($cards);

        self::assertSame(HandCategory::HighCard, $hand->category);
        self::assertSame(['SA', 'DK', 'HQ', 'CJ', 'D9'], $hand->cardsAsKeys());
    }
}

