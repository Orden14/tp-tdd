<?php

namespace Tests;

use App\Application\Parser\CardParser;
use App\Poker\Hand\HandComparer;
use App\Poker\Hand\HandEvaluator;
use PHPUnit\Framework\TestCase;

final class HandComparerTest extends TestCase
{
    public function testCompareHighCardUsesKickersInOrder(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        $h1 = $evaluator->evaluateBestHand($parser->parseCards('SA:DK:HQ:CJ:D9:S7:H2', 7)); // A K Q J 9
        $h2 = $evaluator->evaluateBestHand($parser->parseCards('SA:DK:HQ:CJ:D8:S7:H2', 7)); // A K Q J 8

        self::assertSame(1, $comparer->compare($h1, $h2));
        self::assertSame(-1, $comparer->compare($h2, $h1));
        self::assertSame(0, $comparer->compare($h1, $h1));
    }

    public function testDifferentCategoriesAreOrderedByStrength(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        $highCard = $evaluator->evaluateBestHand($parser->parseCards('SA:DK:HQ:CJ:D9:S7:H2', 7));
        $onePair = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DK:HQ:CJ:D9:S7', 7));

        self::assertSame(-1, $comparer->compare($highCard, $onePair));
        self::assertSame(1, $comparer->compare($onePair, $highCard));
    }
}

