<?php

$argv = $argv ?? [];

$usage = "Usage: php bin/poker run [--board <cards>] --p1 <cards> [--p2 <cards> ...]\n";

$showHelp = in_array('--help', $argv, true) || in_array('-h', $argv, true);

if ($showHelp) {
    fwrite(STDOUT, $usage);
    fwrite(STDOUT, "\n");
    fwrite(STDOUT, "Exemples:\n");
    fwrite(STDOUT, "  php bin/poker --help\n");
    fwrite(STDOUT, "  php bin/poker run --p1 KQ --p2 A3\n");
    fwrite(STDOUT, "Format des cartes : voir README.md\n");

    exit(0);
}

$subCommand = $argv[1] ?? null;
if ($subCommand === 'run') {
    if (count($argv) <= 2) {
        fwrite(STDOUT, "Error: missing arguments for run\n");
        fwrite(STDOUT, $usage);

        exit(2);
    }

    fwrite(STDOUT, "Error: not implemented yet\n");
    fwrite(STDOUT, $usage);

    exit(2);
}

fwrite(STDOUT, $usage);
exit(1);
