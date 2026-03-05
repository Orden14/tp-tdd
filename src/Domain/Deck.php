<?php

namespace App\Domain;

use App\Application\Exception\InvalidGameInput;

final class Deck
{
    /** @var list<Card> */
    private array $cards;

    /** @var callable(int $maxExclusive): int */
    private $randomIndex;

    /**
     * @param callable(int $maxExclusive): int|null $randomIndex
     */
    public function __construct(?callable $randomIndex = null)
    {
        $this->randomIndex = $randomIndex ?? static fn(int $maxExclusive): int => random_int(0, $maxExclusive - 1);
        $this->cards = self::createFullDeck();
    }

    /** @return list<Card> */
    public static function createFullDeck(): array
    {
        $cards = [];

        foreach (Suit::cases() as $suit) {
            foreach (Rank::cases() as $rank) {
                $cards[] = new Card($suit, $rank);
            }
        }

        return $cards;
    }

    /** @param list<Card> $excluded */
    public function exclude(array $excluded): void
    {
        if ($excluded === []) {
            return;
        }

        $excludedKeys = [];
        foreach ($excluded as $card) {
            $excludedKeys[$card->key()] = true;
        }

        $this->cards = array_values(array_filter(
            $this->cards,
            static fn(Card $c): bool => !isset($excludedKeys[$c->key()])
        ));
    }

    /** @return list<Card> */
    public function draw(int $count): array
    {
        if ($count < 0) {
            throw new InvalidGameInput('invalid draw count');
        }

        if ($count > count($this->cards)) {
            throw new InvalidGameInput('not enough cards in deck');
        }

        $drawn = [];

        for ($i = 0; $i < $count; $i++) {
            $idx = ($this->randomIndex)(count($this->cards));
            $drawn[] = $this->cards[$idx];
            array_splice($this->cards, $idx, 1);
        }

        return $drawn;
    }
}
