<?php

$argv = $argv ?? [];

$showHelp = in_array('--help', $argv, true) || in_array('-h', $argv, true);

if ($showHelp) {
    fwrite(STDOUT, "Usage: php bin/poker run [--board <cards>] --p1 <cards> [--p2 <cards> ...]\n");
    fwrite(STDOUT, "\n");
    fwrite(STDOUT, "Exemples:\n");
    fwrite(STDOUT, "  php bin/poker --help\n");
    fwrite(STDOUT, "  php bin/poker run --p1 KQ --p2 A3\n");
    fwrite(STDOUT, "Format des cartes : voir README.md\n");

    exit(0);
}

fwrite(STDOUT, "Usage: php bin/poker --help\n");
exit(1);

