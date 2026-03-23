<?php

header("Content-Type: text/plain; charset=utf-8");

require_once "awstats.php"; // oder direkt fetch + parse einfügen

// NUR die main-Daten debuggen
$tables = $raw["main"]; // falls du $raw nicht hast, ersetze durch deinen Parser

foreach ($tables as $index => $table) {
    echo "=== Tabelle $index ===\n";

    if (isset($table[0])) {
        echo "Erste Zeile: " . implode(" | ", $table[0]) . "\n\n";
    } else {
        echo "(leer)\n\n";
    }
}
