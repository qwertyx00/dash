<?php

header("Content-Type: application/json");

// Pfad zu deinem Logfile
$logFile = __DIR__ . "/test-logs/access.log"; // später: $_SERVER['DOCUMENT_ROOT'] . "/logs/access.log"

if (!file_exists($logFile)) {
    echo json_encode(["error" => "Logfile not found"]);
    exit;
}

$handle = fopen($logFile, "r");

$visitors = [];
$pageViews = 0;
$statusCodes = [];
$bots = [];
$referrers = [];
$responseTimes = [];
$traffic = [];
$lastRequests = [];

$botPattern = '/bot|crawl|spider|bing|google|yandex|duckduckgo|baidu/i';

while (($line = fgets($handle)) !== false) {

    // Beispiel Apache Combined Log Format:
    // 123.123.123.123 - - [23/Mar/2026:18:00:00 +0100] "GET /index.html HTTP/1.1" 200 532 "-" "Mozilla/5.0" 0.123

    preg_match(
        '/^(\S+) .*?\[(.*?)\] "\S+ (.*?) HTTP.*?" (\d{3}) .*?"(.*?)" "(.*?)"(?: (\d+\.\d+))?$/',
        $line,
        $m
    );

    if (!$m || count($m) < 6) continue;

    $ip = $m[1];
    $timestamp = $m[2];
    $url = $m[3];
    $status = intval($m[4]);
    $ref = $m[5];
    $agent = $m[6];
    $rt = isset($m[7]) ? floatval($m[7]) : null;

    // Besucher
    $visitors[$ip] = true;

    // Pageviews
    $pageViews++;

    // Statuscodes
    if (!isset($statusCodes[$status])) $statusCodes[$status] = 0;
    $statusCodes[$status]++;

    // Bots
    if (preg_match($botPattern, $agent)) {
        if (!isset($bots[$agent])) $bots[$agent] = 0;
        $bots[$agent]++;
    }

    // Referrer
    if ($ref !== "-" && !str_contains($ref, $_SERVER['HTTP_HOST'] ?? "")) {
        $host = parse_url($ref, PHP_URL_HOST);
        if ($host) {
            if (!isset($referrers[$host])) $referrers[$host] = 0;
            $referrers[$host]++;
        }
    }

    // Response Times
    if ($rt !== null) {
        $responseTimes[] = $rt;
    }

    // Traffic (pro Stunde)
    $time = date("H:00", strtotime($timestamp));
    if (!isset($traffic[$time])) $traffic[$time] = 0;
    $traffic[$time]++;

    // Letzte Requests
    $lastRequests[] = [
        "ip" => $ip,
        "url" => $url,
        "status" => $status,
        "agent" => $agent,
        "time" => $timestamp
    ];
}

fclose($handle);

// Performance
$avg = count($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
sort($responseTimes);
$p95 = $responseTimes ? $responseTimes[(int)(count($responseTimes) * 0.95)] : 0;

// Systemstatus
$system = [
    "php" => phpversion(),
    "memory" => round(memory_get_usage() / 1024 / 1024, 2) . " MB",
    "load" => function_exists("sys_getloadavg") ? implode(", ", sys_getloadavg()) : "n/a"
];

// Ausgabe
echo json_encode([
    "updatedAt" => date(DATE_ATOM),

    "visitors" => [
        "total" => count($visitors),
        "today" => count($visitors) // später ersetzen
    ],

    "pageViews" => [
        "total" => $pageViews,
        "perVisitor" => $pageViews / max(count($visitors), 1)
    ],

    "errors" => [
        "count" => ($statusCodes[400] ?? 0) + ($statusCodes[404] ?? 0) + ($statusCodes[500] ?? 0),
        "rate" => round((($statusCodes[400] ?? 0) + ($statusCodes[404] ?? 0) + ($statusCodes[500] ?? 0)) / max($pageViews, 1) * 100, 2)
    ],

    "performance" => [
        "avgResponseTime" => round($avg * 1000),
        "p95" => round($p95 * 1000)
    ],

    "statusCodes" => array_map(
        fn($code, $count) => ["code" => $code, "count" => $count],
        array_keys($statusCodes),
        $statusCodes
    ),

    "bots" => array_map(
        fn($name, $hits) => ["name" => $name, "hits" => $hits],
        array_keys($bots),
        $bots
    ),

    "referrers" => array_map(
        fn($host, $hits) => ["host" => $host, "hits" => $hits],
        array_keys($referrers),
        $referrers
    ),

    "traffic" => array_map(
        fn($time, $views) => ["time" => $time, "views" => $views],
        array_keys($traffic),
        $traffic
    ),

    "lastRequests" => array_slice(array_reverse($lastRequests), 0, 20),

    "system" => $system
]);
