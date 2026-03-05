<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class CliTest extends TestCase
{
    public function testHelpCommand(): void
    {
        $cmd = $this->getBaseCmd() . ' --help';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertSame(0, $exitCode, 'Le code retour de --help doit être 0');
        self::assertStringContainsString('Usage:', $output, 'La sortie --help doit contenir un usage');
    }

    public function testRunWithoutArgumentsShowsErrorAndReturnsNonZero(): void
    {
        $cmd = $this->getBaseCmd() . ' run';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertNotSame(0, $exitCode, 'Le code retour de `run` sans arguments doit être non nul');
        self::assertStringContainsString('Error:', $output, 'La sortie doit contenir un message d\'erreur');
        self::assertStringContainsString('Usage:', $output, 'La sortie doit rappeler l\'usage');
    }

    public function testRunWithTwoPlayers(): void
    {
        $cmd = $this->getBaseCmd() . ' run --p1 SK:HQ --p2 DA:C3';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('OK', $output);
    }

    public function testRunRejectsInvalidSuit(): void
    {
        $cmd = $this->getBaseCmd() . ' run --p1 XK:HQ --p2 DA:C3';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertNotSame(0, $exitCode);
        self::assertStringContainsString('Error:', $output);
    }

    public function testRunRejectsInvalidRank(): void
    {
        $cmd = $this->getBaseCmd() . ' run --p1 SZ:HQ --p2 DA:C3';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertNotSame(0, $exitCode);
        self::assertStringContainsString('Error:', $output);
    }

    public function testRunRejectsDuplicateCardsBetweenPlayers(): void
    {
        $cmd = $this->getBaseCmd() . ' run --p1 SK:HQ --p2 SK:C3';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertNotSame(0, $exitCode);
        self::assertStringContainsString('Error:', $output);
    }

    private function getBaseCmd(): string
    {
        $projectRoot = dirname(__DIR__);
        return 'php ' . escapeshellarg($projectRoot . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'poker');
    }
}
