<?php

namespace App\Domain;

use InvalidArgumentException;

enum Suit: string
{
    case Spades = 'S';
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';

    public static function fromChar(string $char): self
    {
        $char = strtoupper(trim($char));

        if (strlen($char) !== 1) {
            throw new InvalidArgumentException('invalid suit');
        }

        return match ($char) {
            'S' => self::Spades,
            'H' => self::Hearts,
            'D' => self::Diamonds,
            'C' => self::Clubs,
            default => throw new InvalidArgumentException('invalid suit'),
        };
    }

    public function labelFr(): string
    {
        return match ($this) {
            self::Spades => 'pique',
            self::Hearts => 'coeur',
            self::Diamonds => 'carreau',
            self::Clubs => 'trèfle',
        };
    }
}
