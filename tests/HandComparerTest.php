<?php

namespace Tests;

use App\Application\Parser\CardParser;
use App\Poker\Hand\HandCategory;
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

    public function testCompareOnePairComparesPairRankThenKickers(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // Paire d'As avec kickers K,Q,9
        $a = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DK:HQ:D9:C4:S2', 7));
        // Paire d'As avec kickers K,Q,8
        $b = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DK:HQ:D8:C4:S2', 7));

        self::assertSame(1, $comparer->compare($a, $b));
        self::assertSame(-1, $comparer->compare($b, $a));

        // Paire de Rois doit perdre contre paire d'As
        $kings = $evaluator->evaluateBestHand($parser->parseCards('SK:HK:DA:HQ:D9:C4:S2', 7));
        self::assertSame(1, $comparer->compare($a, $kings));
        self::assertSame(-1, $comparer->compare($kings, $a));
    }

    public function testCompareTwoPairComparesHighPairThenLowPairThenKicker(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // AA + KK + 9
        $a = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:SK:DK:D9:C4:S2', 7));
        // AA + KK + 8
        $b = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:SK:DK:D8:C4:S2', 7));

        self::assertSame(HandCategory::TwoPair, $a->category);
        self::assertSame(HandCategory::TwoPair, $b->category);
        self::assertSame(1, $comparer->compare($a, $b));
        self::assertSame(-1, $comparer->compare($b, $a));

        // AA+QQ doit perdre contre AA+KK
        $aaqq = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:SQ:DQ:D9:C4:S2', 7));
        self::assertSame(HandCategory::TwoPair, $aaqq->category);
        self::assertSame(1, $comparer->compare($a, $aaqq));
        self::assertSame(-1, $comparer->compare($aaqq, $a));
    }

    public function testCompareThreeOfAKindComparesTripsThenKickers(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // AAA + K,Q
        $a = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DA:DK:HQ:C4:S2', 7));
        // AAA + K,J
        $b = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DA:DK:CJ:C4:S2', 7));

        self::assertSame(HandCategory::ThreeOfAKind, $a->category);
        self::assertSame(HandCategory::ThreeOfAKind, $b->category);
        self::assertSame(1, $comparer->compare($a, $b));
        self::assertSame(-1, $comparer->compare($b, $a));

        // KKK doit perdre contre AAA
        $kkk = $evaluator->evaluateBestHand($parser->parseCards('SK:HK:DK:DA:HQ:C4:S2', 7));
        self::assertSame(HandCategory::ThreeOfAKind, $kkk->category);
        self::assertSame(1, $comparer->compare($a, $kkk));
        self::assertSame(-1, $comparer->compare($kkk, $a));
    }

    public function testCompareStraightUsesHighCardOfStraightWithAceCountsAsFive(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // 5-4-3-2-A
        $wheel = $evaluator->evaluateBestHand($parser->parseCards('SA:D5:C4:H3:S2:DK:HQ', 7));
        // 6-5-4-3-2
        $sixHigh = $evaluator->evaluateBestHand($parser->parseCards('S6:D5:C4:H3:S2:DK:HQ', 7));

        self::assertSame(HandCategory::Straight, $wheel->category);
        self::assertSame(HandCategory::Straight, $sixHigh->category);
        self::assertSame(-1, $comparer->compare($wheel, $sixHigh));
        self::assertSame(1, $comparer->compare($sixHigh, $wheel));
    }

    public function testCompareFlushUsesAllFiveCardsInOrder(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // Flush spades A K Q 9 2
        $a = $evaluator->evaluateBestHand($parser->parseCards('SA:SK:SQ:S9:S2:DJ:H7', 7));
        // Flush spades A K Q 8 2
        $b = $evaluator->evaluateBestHand($parser->parseCards('SA:SK:SQ:S8:S2:DJ:H7', 7));

        self::assertSame(HandCategory::Flush, $a->category);
        self::assertSame(HandCategory::Flush, $b->category);
        self::assertSame(1, $comparer->compare($a, $b));
        self::assertSame(-1, $comparer->compare($b, $a));
    }

    public function testCompareFullHouseComparesTripsThenPair(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // AAA + KK
        $a = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DA:DK:SK:HQ:D9', 7));
        // AAA + QQ
        $b = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DA:DQ:SQ:HK:D9', 7));

        self::assertSame(HandCategory::FullHouse, $a->category);
        self::assertSame(HandCategory::FullHouse, $b->category);
        self::assertSame(1, $comparer->compare($a, $b));
        self::assertSame(-1, $comparer->compare($b, $a));

        // KKK + AA doit perdre contre AAA + KK (départage sur trips)
        $kkkaa = $evaluator->evaluateBestHand($parser->parseCards('SK:HK:DK:SA:HA:D9:C4', 7));
        self::assertSame(HandCategory::FullHouse, $kkkaa->category);
        self::assertSame(1, $comparer->compare($a, $kkkaa));
        self::assertSame(-1, $comparer->compare($kkkaa, $a));
    }

    public function testCompareFourOfAKindComparesQuadsThenKicker(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        // Quads A + kicker K
        $a = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DA:CA:DK:HQ:D9', 7));
        // Quads A + kicker Q
        $b = $evaluator->evaluateBestHand($parser->parseCards('SA:HA:DA:CA:DQ:HJ:D9', 7));

        self::assertSame(HandCategory::FourOfAKind, $a->category);
        self::assertSame(HandCategory::FourOfAKind, $b->category);
        self::assertSame(1, $comparer->compare($a, $b));
        self::assertSame(-1, $comparer->compare($b, $a));

        // Quads K doit perdre contre Quads A
        $kkk = $evaluator->evaluateBestHand($parser->parseCards('SK:HK:DK:CK:DA:HQ:D9', 7));
        self::assertSame(HandCategory::FourOfAKind, $kkk->category);
        self::assertSame(1, $comparer->compare($a, $kkk));
        self::assertSame(-1, $comparer->compare($kkk, $a));
    }

    public function testCompareStraightFlushUsesHighCardWithAceCountsAsFive(): void
    {
        $parser = new CardParser();
        $evaluator = new HandEvaluator();
        $comparer = new HandComparer();

        $wheel = $evaluator->evaluateBestHand($parser->parseCards('SA:S5:S4:S3:S2:DK:HQ', 7));
        $sixHigh = $evaluator->evaluateBestHand($parser->parseCards('S6:S5:S4:S3:S2:DK:HQ', 7));

        self::assertSame(HandCategory::StraightFlush, $wheel->category);
        self::assertSame(HandCategory::StraightFlush, $sixHigh->category);
        self::assertSame(-1, $comparer->compare($wheel, $sixHigh));
        self::assertSame(1, $comparer->compare($sixHigh, $wheel));
    }
}
