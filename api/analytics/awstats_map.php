<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function safeGet($array, $index, $default = null) {
    return isset($array[$index]) ? $array[$index] : $default;
}

function normalizeNumber($value) {
    // Entfernt alles außer Ziffern und Punkt, dann Tausenderpunkte raus
    $clean = preg_replace('/[^0-9.]/', '', (string)$value);
    $clean = str_replace('.', '', $clean);
    return $clean === '' ? 0 : (int)$clean;
}

function mapAwstats($raw) {

    $result = [
        "summary" => [
            "uniqueVisitors" => 0,
            "visits"         => 0,
            "pages"          => 0,
            "hits"           => 0,
            "bandwidth"      => null
        ],
        "monthly"   => [],
        "browsers"  => [],
        "os"        => [],
        "robots"    => [],
        "referrers" => [],
        "errors404" => [],
        "lastHosts" => []
    ];

    // -----------------------------
    // 1. SUMMARY (main[4])
    // -----------------------------
    if (isset($raw["main"][4])) {
        foreach ($raw["main"][4] as $row) {
            // Wir suchen die Traffic-Zeile:
            // ["gesehener Traffic *", "863", "903", "1.761", "5.455", "2.85 GB (3306.52 KB/Besuch)"]
            if (isset($row[0]) && str_starts_with($row[0], "gesehener Traffic")) {

                $result["summary"]["uniqueVisitors"] = (int)$row[1];
                $result["summary"]["visits"]         = (int)$row[2];
                $result["summary"]["pages"]          = normalizeNumber($row[3]);
                $result["summary"]["hits"]           = normalizeNumber($row[4]);

                // Nur den ersten Wert extrahieren, z. B. "2.85 GB"
                $bw = preg_replace('/\(.*/', '', (string)$row[5]);
                $bw = trim($bw);
                $result["summary"]["bandwidth"] = $bw !== '' ? $bw : null;

                break;
            }
        }
    }

    // -----------------------------
    // 2. MONTHLY HISTORY
    // -----------------------------
    $months = [];

    if (isset($raw["main"])) {
        foreach ($raw["main"] as $table) {
            foreach ($table as $row) {
                $col0 = safeGet($row, 0, "");
                if (!is_string($col0)) {
                    continue;
                }

                // Whitespace normalisieren (z. B. "Jan  2026" -> "Jan 2026")
                $monthName = preg_replace('/\s+/', ' ', trim($col0));

                // "Total" etc. rausfiltern
                if ($monthName === '' || stripos($monthName, 'Total') === 0) {
                    continue;
                }

                // Format: "März 2026"
                if (preg_match('/^[A-Za-zÄÖÜäöüß]+ \d{4}$/u', $monthName)) {
                    $unique    = (int)safeGet($row, 1, 0);
                    $visits    = (int)safeGet($row, 2, 0);
                    $pages     = normalizeNumber(safeGet($row, 3, "0"));
                    $hits      = normalizeNumber(safeGet($row, 4, "0"));
                    $bandwidth = trim((string)safeGet($row, 5, "0"));

                    $months[$monthName] = [
                        "month"     => $monthName,
                        "unique"    => $unique,
                        "visits"    => $visits,
                        "pages"     => $pages,
                        "hits"      => $hits,
                        "bandwidth" => $bandwidth
                    ];
                }
            }
        }
    }

    // Duplikate entfernen, Werte neu indexieren
    $result["monthly"] = array_values($months);

    // -----------------------------
    // 3. BROWSERS
    // -----------------------------
    if (isset($raw["browserdetail"][4])) {
        foreach ($raw["browserdetail"][4] as $row) {
            if (count($row) >= 2) {
                $name = trim((string)$row[0]);
                $hits = normalizeNumber($row[1]);
                if ($name !== '' && $hits > 0) {
                    $result["browsers"][] = [
                        "name" => $name,
                        "hits" => $hits
                    ];
                }
            }
        }
    }

    // -----------------------------
    // 4. OS
    // -----------------------------
    if (isset($raw["osdetail"][4])) {
        foreach ($raw["osdetail"][4] as $row) {
            if (count($row) >= 2) {
                $name = trim((string)$row[0]);
                $hits = normalizeNumber($row[1]);
                if ($name !== '' && $hits > 0) {
                    $result["os"][] = [
                        "name" => $name,
                        "hits" => $hits
                    ];
                }
            }
        }
    }

    // -----------------------------
    // 5. ROBOTS
    // -----------------------------
    if (isset($raw["robots"][4])) {
        foreach ($raw["robots"][4] as $row) {
            if (count($row) >= 2) {
                $name = trim((string)$row[0]);
                $hitsStr = (string)$row[1];

                // Werte wie "82+24" -> 106
                $parts = preg_split('/\s*\+\s*/', $hitsStr);
                $hits  = 0;
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '' && is_numeric($p)) {
                        $hits += (int)$p;
                    }
                }

                if ($name !== '' && $hits > 0) {
                    $result["robots"][] = [
                        "name" => $name,
                        "hits" => $hits
                    ];
                }
            }
        }
    }

    // -----------------------------
    // 6. REFERRERS
    // -----------------------------
    if (isset($raw["refererpages"][4])) {
        foreach ($raw["refererpages"][4] as $row) {
            if (count($row) >= 2) {
                $host = trim((string)$row[0]);
                // führendes "-" entfernen (z. B. "-Google.com")
                $host = ltrim($host, "-");
                $host = trim($host);

                $hits = normalizeNumber($row[1]);

                if ($host !== '' && $hits > 0) {
                    $result["referrers"][] = [
                        "host" => $host,
                        "hits" => $hits
                    ];
                }
            }
        }
    }

    // -----------------------------
    // 7. 404 ERRORS (nur echte URLs)
    // -----------------------------
    if (isset($raw["errors404"][4])) {
        foreach ($raw["errors404"][4] as $row) {
            if (count($row) >= 2) {
                $url = trim((string)$row[0]);
                $hits = normalizeNumber($row[1]);

                // Nur Zeilen, die wie URLs aussehen (beginnen mit "/" oder "//")
                if ($hits > 0 && preg_match('~^/|^//~', $url)) {
                    $result["errors404"][] = [
                        "url"  => $url,
                        "hits" => $hits
                    ];
                }
            }
        }
    }

    // -----------------------------
    // 8. LAST HOSTS
    // -----------------------------
    if (isset($raw["lasthosts"][4])) {
        foreach ($raw["lasthosts"][4] as $row) {
            if (count($row) >= 2) {
                $ip   = trim((string)$row[0]);
                $hits = normalizeNumber($row[1]);

                if ($ip !== '' && $hits > 0) {
                    $result["lastHosts"][] = [
                        "ip"   => $ip,
                        "hits" => $hits
                    ];
                }
            }
        }
    }

    return $result;
}
