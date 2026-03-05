<?php

namespace App\Domain;

use InvalidArgumentException;

enum Rank: string
{
    case Two = '2';
    case Three = '3';
    case Four = '4';
    case Five = '5';
    case Six = '6';
    case Seven = '7';
    case Eight = '8';
    case Nine = '9';
    case Ten = 'T';
    case Jack = 'J';
    case Queen = 'Q';
    case King = 'K';
    case Ace = 'A';

    public static function fromChar(string $char): self
    {
        $char = strtoupper(trim($char));

        if (strlen($char) !== 1) {
            throw new InvalidArgumentException('invalid rank');
        }

        return match ($char) {
            '2' => self::Two,
            '3' => self::Three,
            '4' => self::Four,
            '5' => self::Five,
            '6' => self::Six,
            '7' => self::Seven,
            '8' => self::Eight,
            '9' => self::Nine,
            'T' => self::Ten,
            'J' => self::Jack,
            'Q' => self::Queen,
            'K' => self::King,
            'A' => self::Ace,
            default => throw new InvalidArgumentException('invalid rank'),
        };
    }

    public function labelFr(): string
    {
        return match ($this) {
            self::Two => 'Deux',
            self::Three => 'Trois',
            self::Four => 'Quatre',
            self::Five => 'Cinq',
            self::Six => 'Six',
            self::Seven => 'Sept',
            self::Eight => 'Huit',
            self::Nine => 'Neuf',
            self::Ten => 'Dix',
            self::Jack => 'Valet',
            self::Queen => 'Dame',
            self::King => 'Roi',
            self::Ace => 'As',
        };
    }
}
