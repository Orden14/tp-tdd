<?php

namespace App\Poker\Hand;

enum HandCategory: int
{
    case HighCard = 1;
    case OnePair = 2;
    case TwoPair = 3;
    case ThreeOfAKind = 4;
    case Straight = 5;
    case Flush = 6;
    case FullHouse = 7;
    case FourOfAKind = 8;
    case StraightFlush = 9;
}
