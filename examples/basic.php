<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AfazTech\Glyphify\Glyphify;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║           GLYPHIFY - Font Generator Example              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$text = readline("Enter text (supports Persian and English): ");

$glyphify = new Glyphify($text, ['all_fonts' => false]);

$glyphify->display();

echo "\n\n=== All Font Names ===\n";
print_r($glyphify->getFontNames());

echo "\n\n=== JSON Output ===\n";
echo $glyphify->toJson();

echo "\n\n=== CSV Output ===\n";
echo $glyphify->toCsv();
