<?php
header('Content-Type: application/json');

echo json_encode([
    "updatedAt" => date(DATE_ATOM),
    "visitors" => ["total" => 1234, "today" => 87],
    "pageViews" => ["total" => 5432, "perVisitor" => 4.4],
    "errors" => ["count" => 123, "rate" => 2.3],
    "performance" => ["avgResponseTime" => 180, "p95" => 420],
    "countries" => [
        ["code" => "DE", "name" => "Deutschland", "hits" => 3200]
    ],
    "statusCodes" => [
        ["code" => 200, "count" => 4800]
    ],
    "bots" => [
        ["name" => "Googlebot", "hits" => 320]
    ],
    "referrers" => [
        ["host" => "google.com", "hits" => 1200]
    ]
]);
