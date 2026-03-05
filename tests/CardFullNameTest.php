<?php

namespace Tests;

use App\Domain\Card;
use App\Domain\Rank;
use App\Domain\Suit;
use PHPUnit\Framework\TestCase;

final class CardFullNameTest extends TestCase
{
    public function testFullNameAceOfSpadesInFrench(): void
    {
        $card = new Card(Suit::Spades, Rank::Ace);
        self::assertSame('As de pique', $card->fullName());
    }
}

