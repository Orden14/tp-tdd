<?php

namespace Tests;

use App\Application\Input\PlayerInput;
use App\Application\PokerGame;
use App\Application\Parser\CardParser;
use PHPUnit\Framework\TestCase;

final class PokerGameTest extends TestCase
{
    public function testDeterminesSingleWinner(): void
    {
        $parser = new CardParser();
        $game = new PokerGame();

        $board = $parser->parseCards('S2:D3:C4:H5:D9', 5);

        $players = [
            new PlayerInput('p1', $parser->parseTwoCards('SA:HA')),
            new PlayerInput('p2', $parser->parseTwoCards('DK:HK')),
        ];

        $result = $game->play($players, $board);

        self::assertSame(['p1'], $result->winnerIds());
    }

    public function testDeterminesSplitPotOnTie(): void
    {
        $parser = new CardParser();
        $game = new PokerGame();

        $board = $parser->parseCards('S2:D3:C4:H5:DA', 5);

        $players = [
            new PlayerInput('p1', $parser->parseTwoCards('SK:HQ')),
            new PlayerInput('p2', $parser->parseTwoCards('C9:D8')),
        ];

        $result = $game->play($players, $board);

        self::assertSame(['p1', 'p2'], $result->winnerIds());
    }
}
