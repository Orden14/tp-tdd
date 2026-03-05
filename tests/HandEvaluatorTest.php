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

    public function testOnePairBeatsHighCardAndSelectsPairThenKickers(): void
    {
        $parser = new CardParser();

        // Paire d'As + kickers K,Q,J.
        $cards = $parser->parseCards('SA:HA:DK:HQ:CJ:D9:S7', 7);

        $evaluator = new HandEvaluator();
        $hand = $evaluator->evaluateBestHand($cards);

        self::assertSame(HandCategory::OnePair, $hand->category);
        self::assertSame(['SA', 'HA', 'DK', 'HQ', 'CJ'], $hand->cardsAsKeys());
    }

    public function testTwoPairSelectsTwoBestPairsThenBestKicker(): void
    {
        $parser = new CardParser();

        // Paires A et K + kicker Q (on ignore J, 9).
        $cards = $parser->parseCards('SA:HA:DK:SK:HQ:CJ:D9', 7);

        $evaluator = new HandEvaluator();
        $hand = $evaluator->evaluateBestHand($cards);

        self::assertSame(HandCategory::TwoPair, $hand->category);
        self::assertSame(['SA', 'HA', 'DK', 'SK', 'HQ'], $hand->cardsAsKeys());
    }

    public function testThreeOfAKindSelectsTripsThenTwoBestKickers(): void
    {
        $parser = new CardParser();

        // Brelan d'As + kickers K,Q.
        $cards = $parser->parseCards('SA:HA:DA:DK:HQ:CJ:D9', 7);

        $evaluator = new HandEvaluator();
        $hand = $evaluator->evaluateBestHand($cards);

        self::assertSame(HandCategory::ThreeOfAKind, $hand->category);
        self::assertSame(['SA', 'HA', 'DA', 'DK', 'HQ'], $hand->cardsAsKeys());
    }

    public function testStraightSelectsBestStraightFiveCards(): void
    {
        $parser = new CardParser();

        // Suite 10-J-Q-K-A (broadway)
        $cards = $parser->parseCards('SA:DK:HQ:CJ:DT:D9:S7', 7);

        $evaluator = new HandEvaluator();
        $hand = $evaluator->evaluateBestHand($cards);

        self::assertSame(HandCategory::Straight, $hand->category);
        self::assertSame(['SA', 'DK', 'HQ', 'CJ', 'DT'], $hand->cardsAsKeys());
    }

    public function testFlushSelectsFiveHighestCardsOfSameSuit(): void
    {
        $parser = new CardParser();

        // 5 piques: A,K,Q,9,2 => flush.
        $cards = $parser->parseCards('SA:SK:SQ:S9:S2:DJ:H7', 7);

        $evaluator = new HandEvaluator();
        $hand = $evaluator->evaluateBestHand($cards);

        self::assertSame(HandCategory::Flush, $hand->category);
        self::assertSame(['SA', 'SK', 'SQ', 'S9', 'S2'], $hand->cardsAsKeys());
    }
}
