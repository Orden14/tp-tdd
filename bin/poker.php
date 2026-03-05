<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Util\CliUtil;

$argv = $argv ?? [];

$usage = "Usage: php bin/poker run [--board <cards>] --p1 <cards> [--p2 <cards> ...]\n";
$cardsHelp = "Format cartes (détails dans README.md): <Couleur><Valeur> séparées par ':' (ex: SK:HQ). Couleurs: S,H,D,C. Valeurs: 2-9,T,J,Q,K,A\n";

$showHelp = in_array('--help', $argv, true) || in_array('-h', $argv, true);

if ($showHelp) {
    fwrite(STDOUT, $usage);
    fwrite(STDOUT, $cardsHelp);
    exit(0);
}

$subCommand = $argv[1] ?? null;
if ($subCommand === 'run') {
    if (count($argv) <= 2) {
        fwrite(STDOUT, "Error: missing arguments for run\n");
        fwrite(STDOUT, $usage);
        exit(2);
    }

    $p1Arg = null;
    $p2Arg = null;

    for ($i = 2, $iMax = count($argv); $i < $iMax; $i += 2) {
        $opt = $argv[$i] ?? null;
        $val = $argv[$i + 1] ?? null;

        if ($opt === '--p1') {
            $p1Arg = $val;
            continue;
        }

        if ($opt === '--p2') {
            $p2Arg = $val;
        }
    }

    if ($p1Arg === null || $p2Arg === null) {
        fwrite(STDOUT, "Error: missing required options --p1 and --p2\n");
        fwrite(STDOUT, $usage);
        exit(2);
    }

    try {
        $p1Cards = array_map(static fn(string $c) => CliUtil::parseCard($c), CliUtil::parseCardsArg($p1Arg));
        $p2Cards = array_map(static fn(string $c) => CliUtil::parseCard($c), CliUtil::parseCardsArg($p2Arg));
    } catch (InvalidArgumentException $e) {
        fwrite(STDOUT, "Error: " . $e->getMessage() . "\n");
        fwrite(STDOUT, $usage);
        fwrite(STDOUT, $cardsHelp);
        exit(2);
    }

    // Placeholder avant implémentation.
    fwrite(STDOUT, "OK\n");
    exit(0);
}

fwrite(STDOUT, $usage);
exit(1);
