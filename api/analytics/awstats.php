<?php

header("Content-Type: application/json; charset=utf-8");

require_once "awstats_map.php";

// Zugangsdaten
$aw_user = "knaus-filtertechnik.com";
$aw_pass = "PW";

// Basis-URL
$base = "https://www137.your-server.de/_statistics_/knaus-filtertechnik.com/awstats/awstats.pl";

function fetchAwstats($output, $base, $user, $pass) {
    $url = $base . "?config=knaus-filtertechnik.com&output=$output";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function parseAwstatsTables($html) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $tables = $dom->getElementsByTagName("table");
    $result = [];

    foreach ($tables as $table) {
        $rows = $table->getElementsByTagName("tr");
        $tableData = [];

        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName("td");
            if ($cols->length === 0) continue;

            $rowData = [];
            foreach ($cols as $col) {
                $text = trim($col->textContent);
                if ($text !== "") {
                    $rowData[] = $text;
                }
            }

            if (!empty($rowData)) {
                $tableData[] = $rowData;
            }
        }

        if (!empty($tableData)) {
            $result[] = $tableData;
        }
    }

    return $result;
}

// Welche AWStats-Bereiche abrufen?
$outputs = [
    "main",
    "alldays",
    "urldetail",
    "browserdetail",
    "osdetail",
    "refererpages",
    "robots",
    "errors404",
    "lasthosts"
];

// RAW-Daten sammeln
$raw = [];

foreach ($outputs as $out) {
    $html = fetchAwstats($out, $base, $aw_user, $aw_pass);
    $raw[$out] = parseAwstatsTables($html);
}

// Mapping anwenden
$mapped = mapAwstats($raw);

// Ausgabe
echo json_encode($mapped, JSON_PRETTY_PRINT);
