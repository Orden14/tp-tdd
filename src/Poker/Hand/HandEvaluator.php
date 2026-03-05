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

        $twoPair = $this->tryEvaluateTwoPair($sorted);
        if ($twoPair !== null) {
            return $twoPair;
        }

        $onePair = $this->tryEvaluateOnePair($sorted);
        if ($onePair !== null) {
            return $onePair;
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
     * @return list<Rank> ranks with count>=2, sorted desc
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
