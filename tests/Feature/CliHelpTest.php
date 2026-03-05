<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class CliHelpTest extends TestCase
{
    public function testHelpDisplaysUsageAndReturnsZero(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $cmd = 'php ' . escapeshellarg($projectRoot . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'poker') . ' --help';

        $outputLines = [];
        $exitCode = 0;
        exec($cmd, $outputLines, $exitCode);

        $output = implode("\n", $outputLines);

        self::assertSame(0, $exitCode, 'Le code retour de --help doit être 0');
        self::assertStringContainsString('Usage:', $output, 'La sortie --help doit contenir un usage');
    }
}
