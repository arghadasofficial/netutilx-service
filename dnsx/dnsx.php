<?php
header("Content-Type: application/json");

require_once "dns_functions.php"; // Optimized DNS functions

$type = $_GET['type'] ? null;
$domain = $_GET['domain'] ?? null;
$server = $_GET['server'] ?? null;
$ip = $_GET['ip'] ?? null;

// Execute DNS Query based on type
$response = match ($type) {
    "A"    => aQuery($domain, $server),
    "NS"   => nsQuery($domain, $server),
    "MX"   => mxQuery($domain, $server),
    "SOA"  => soaQuery($domain, $server),
    "TXT"  => txtQuery($domain, $server),
    "PTR"  => ptrQuery($ip),
    default => ["error" => "Invalid query type"]
};

// Return JSON response
echo json_encode($response);

// 🚨 Immediately stop execution to prevent further processing
exit;
?>
