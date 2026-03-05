<?php

namespace App\Poker\Hand;

use App\Domain\Card;
use App\Domain\Rank;

final class HandEvaluator
{
    /**
     * @param list<Card> $cards
     */
    public function evaluateBestHand(array $cards): EvaluatedHand
    {
        $sorted = $this->sortByRankDesc($cards);

        $strategies = [
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateStraightFlush($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateFourOfAKind($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateFullHouse($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateFlush($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateStraight($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateThreeOfAKind($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateTwoPair($c),
            fn(array $c): ?EvaluatedHand => $this->tryEvaluateOnePair($c),
        ];

        foreach ($strategies as $tryEvaluate) {
            $hand = $tryEvaluate($sorted);
            if ($hand !== null) {
                return $hand;
            }
        }

        return $this->evaluateHighCard($sorted);
    }

    /**
     * @param list<Card> $cards
     * @return list<Card>
     */
    private function sortByRankDesc(array $cards): array
    {
        $sorted = $cards;
        usort($sorted, static fn(Card $a, Card $b): int => self::rankValue($b->rank) <=> self::rankValue($a->rank));
        return $sorted;
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function evaluateHighCard(array $sortedDesc): EvaluatedHand
    {
        $bestFive = array_slice($sortedDesc, 0, 5);
        return new EvaluatedHand(HandCategory::HighCard, $bestFive);
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateOnePair(array $sortedDesc): ?EvaluatedHand
    {
        $pairRank = $this->findBestPairRank($sortedDesc);
        if ($pairRank === null) {
            return null;
        }

        [$pairCards, $kickers] = $this->splitPairAndKickers($sortedDesc, $pairRank, kickerCount: 3);
        return new EvaluatedHand(HandCategory::OnePair, array_merge($pairCards, $kickers));
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateTwoPair(array $sortedDesc): ?EvaluatedHand
    {
        $pairRanks = $this->findPairRanksDesc($sortedDesc);
        if (count($pairRanks) < 2) {
            return null;
        }

        $highPair = $pairRanks[0];
        $lowPair = $pairRanks[1];

        [$highPairCards] = $this->splitPairAndKickers($sortedDesc, $highPair, kickerCount: 0);
        [$lowPairCards] = $this->splitPairAndKickers($sortedDesc, $lowPair, kickerCount: 0);

        $kicker = $this->findBestKickerExcludingRanks($sortedDesc, [$highPair, $lowPair]);
        if ($kicker === null) {
            return null;
        }

        return new EvaluatedHand(HandCategory::TwoPair, array_merge($highPairCards, $lowPairCards, [$kicker]));
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateThreeOfAKind(array $sortedDesc): ?EvaluatedHand
    {
        $tripsRank = $this->findBestTripsRank($sortedDesc);
        if ($tripsRank === null) {
            return null;
        }

        $tripsCards = [];
        $kickers = [];

        foreach ($sortedDesc as $card) {
            if ($card->rank === $tripsRank && count($tripsCards) < 3) {
                $tripsCards[] = $card;
                continue;
            }

            if ($card->rank !== $tripsRank && count($kickers) < 2) {
                $kickers[] = $card;
            }
        }

        if (count($tripsCards) !== 3) {
            return null;
        }

        return new EvaluatedHand(HandCategory::ThreeOfAKind, array_merge($tripsCards, $kickers));
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateStraight(array $sortedDesc): ?EvaluatedHand
    {
        $byRank = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            if (!isset($byRank[$key])) {
                $byRank[$key] = $card;
            }
        }

        $hasAce = isset($byRank[Rank::Ace->value]);

        $uniqueCards = array_values($byRank);
        usort($uniqueCards, static fn(Card $a, Card $b): int => self::rankValue($b->rank) <=> self::rankValue($a->rank));

        $run = [];
        $prevValue = null;

        foreach ($uniqueCards as $card) {
            $v = self::rankValue($card->rank);

            if ($prevValue === null) {
                $run = [$card];
                $prevValue = $v;
                continue;
            }

            if ($v === $prevValue - 1) {
                $run[] = $card;
            } else {
                $run = [$card];
            }

            $prevValue = $v;

            if (count($run) >= 5) {
                $bestFive = array_slice($run, 0, 5);
                return new EvaluatedHand(HandCategory::Straight, $bestFive);
            }
        }

        if ($hasAce
            && isset($byRank[Rank::Five->value], $byRank[Rank::Four->value], $byRank[Rank::Three->value], $byRank[Rank::Two->value])
        ) {
            return new EvaluatedHand(
                HandCategory::Straight,
                [
                    $byRank[Rank::Five->value],
                    $byRank[Rank::Four->value],
                    $byRank[Rank::Three->value],
                    $byRank[Rank::Two->value],
                    $byRank[Rank::Ace->value],
                ]
            );
        }

        return null;
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateFlush(array $sortedDesc): ?EvaluatedHand
    {
        $bySuit = [];
        foreach ($sortedDesc as $card) {
            $suitKey = $card->suit->value;
            $bySuit[$suitKey] ??= [];
            $bySuit[$suitKey][] = $card;
        }

        foreach ($bySuit as $cardsOfSuit) {
            if (count($cardsOfSuit) >= 5) {
                $bestFive = array_slice($cardsOfSuit, 0, 5);
                return new EvaluatedHand(HandCategory::Flush, $bestFive);
            }
        }

        return null;
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateFullHouse(array $sortedDesc): ?EvaluatedHand
    {
        $tripsRank = $this->findBestTripsRank($sortedDesc);
        if ($tripsRank === null) {
            return null;
        }

        $pairRank = $this->findBestPairRankExcluding($sortedDesc, $tripsRank);
        if ($pairRank === null) {
            return null;
        }

        $trips = [];
        $pair = [];

        foreach ($sortedDesc as $card) {
            if ($card->rank === $tripsRank && count($trips) < 3) {
                $trips[] = $card;
                continue;
            }

            if ($card->rank === $pairRank && count($pair) < 2) {
                $pair[] = $card;
            }
        }

        if (count($trips) !== 3 || count($pair) !== 2) {
            return null;
        }

        return new EvaluatedHand(HandCategory::FullHouse, array_merge($trips, $pair));
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function findBestTripsRank(array $sortedDesc): ?Rank
    {
        $counts = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            if (($counts[$key] ?? 0) >= 3) {
                return $card->rank;
            }
        }

        return null;
    }

    /**
     * @param list<Card> $sortedDesc
     * @param list<Rank> $excluded
     */
    private function findBestKickerExcludingRanks(array $sortedDesc, array $excluded): ?Card
    {
        foreach ($sortedDesc as $card) {
            foreach ($excluded as $rank) {
                if ($card->rank === $rank) {
                    continue 2;
                }
            }
            return $card;
        }

        return null;
    }

    /**
     * @param list<Card> $sortedDesc
     * @return array{0: list<Card>, 1: list<Card>}
     */
    private function splitPairAndKickers(array $sortedDesc, Rank $pairRank, int $kickerCount): array
    {
        $pairCards = [];
        $kickers = [];

        foreach ($sortedDesc as $card) {
            if ($card->rank === $pairRank && count($pairCards) < 2) {
                $pairCards[] = $card;
                continue;
            }

            if ($kickerCount > 0 && $card->rank !== $pairRank && count($kickers) < $kickerCount) {
                $kickers[] = $card;
            }
        }

        return [$pairCards, $kickers];
    }

    /**
     * @param list<Card> $sortedDesc
     * @return list<Rank> ranks with count >=2, sorted desc
     */
    private function findPairRanksDesc(array $sortedDesc): array
    {
        $counts = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $seen = [];
        $pairs = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            if (($counts[$key] ?? 0) >= 2) {
                $pairs[] = $card->rank;
            }
        }

        return $pairs;
    }

    /** @param list<Card> $sortedDesc */
    private function findBestPairRank(array $sortedDesc): ?Rank
    {
        $pairRanks = $this->findPairRanksDesc($sortedDesc);
        return $pairRanks[0] ?? null;
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function findBestPairRankExcluding(array $sortedDesc, Rank $excludedRank): ?Rank
    {
        $counts = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        foreach ($sortedDesc as $card) {
            if ($card->rank === $excludedRank) {
                continue;
            }

            $key = $card->rank->value;
            if (($counts[$key] ?? 0) >= 2) {
                return $card->rank;
            }
        }

        return null;
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateFourOfAKind(array $sortedDesc): ?EvaluatedHand
    {
        $quadsRank = $this->findBestQuadsRank($sortedDesc);
        if ($quadsRank === null) {
            return null;
        }

        $quads = [];
        $kicker = null;

        foreach ($sortedDesc as $card) {
            if ($card->rank === $quadsRank && count($quads) < 4) {
                $quads[] = $card;
                continue;
            }

            if ($kicker === null && $card->rank !== $quadsRank) {
                $kicker = $card;
            }
        }

        if (count($quads) !== 4 || $kicker === null) {
            return null;
        }

        return new EvaluatedHand(HandCategory::FourOfAKind, array_merge($quads, [$kicker]));
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function findBestQuadsRank(array $sortedDesc): ?Rank
    {
        $counts = [];
        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        foreach ($sortedDesc as $card) {
            $key = $card->rank->value;
            if (($counts[$key] ?? 0) >= 4) {
                return $card->rank;
            }
        }

        return null;
    }

    /**
     * @param list<Card> $sortedDesc
     */
    private function tryEvaluateStraightFlush(array $sortedDesc): ?EvaluatedHand
    {
        $bySuit = [];
        foreach ($sortedDesc as $card) {
            $suitKey = $card->suit->value;
            $bySuit[$suitKey] ??= [];
            $bySuit[$suitKey][] = $card;
        }

        foreach ($bySuit as $cardsOfSuit) {
            if (count($cardsOfSuit) < 5) {
                continue;
            }

            $straight = $this->tryEvaluateStraight($cardsOfSuit);
            if ($straight !== null) {
                return new EvaluatedHand(HandCategory::StraightFlush, $straight->cards);
            }
        }

        return null;
    }

    private static function rankValue(Rank $rank): int
    {
        return match ($rank) {
            Rank::Two => 2,
            Rank::Three => 3,
            Rank::Four => 4,
            Rank::Five => 5,
            Rank::Six => 6,
            Rank::Seven => 7,
            Rank::Eight => 8,
            Rank::Nine => 9,
            Rank::Ten => 10,
            Rank::Jack => 11,
            Rank::Queen => 12,
            Rank::King => 13,
            Rank::Ace => 14,
        };
    }
}
