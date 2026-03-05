<?php

namespace App\Application;

use App\Application\Input\PlayerInput;
use App\Domain\Card;
use App\Poker\Hand\HandComparer;
use App\Poker\Hand\HandEvaluator;

final class PokerGame
{
    private HandEvaluator $evaluator;
    private HandComparer $comparer;

    public function __construct(?HandEvaluator $evaluator = null, ?HandComparer $comparer = null)
    {
        $this->evaluator = $evaluator ?? new HandEvaluator();
        $this->comparer = $comparer ?? new HandComparer();
    }

    /**
     * @param list<PlayerInput> $players
     * @param list<Card> $board
     */
    public function play(array $players, array $board): PokerGameResult
    {
        $evaluations = [];
        foreach ($players as $player) {
            $seven = array_merge($board, $player->cards);
            $hand = $this->evaluator->evaluateBestHand($seven);
            $evaluations[] = new PlayerHandResult($player->id, $hand);
        }

        if ($evaluations === []) {
            return new PokerGameResult([]);
        }

        $best = $evaluations[0];
        foreach ($evaluations as $candidate) {
            if ($this->comparer->compare($candidate->hand, $best->hand) > 0) {
                $best = $candidate;
            }
        }

        $winners = [];
        foreach ($evaluations as $candidate) {
            if ($this->comparer->compare($candidate->hand, $best->hand) === 0) {
                $winners[] = $candidate;
            }
        }

        return new PokerGameResult($winners);
    }
}
