<?php
header("Content-Type: application/json");

require_once "dns_functions.php"; // Optimized DNS functions

// 🚀 Ensure the request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Only GET requests are allowed"]);
    exit;
}

// 🔍 Get parameters
$action = $_GET['action'] ?? null;
$query = $_GET['query'] ?? null;
$type = $_GET['type'] ?? null;
$server = $_GET['server'] ?? null;

// 🔎 Validate 'action' (must be 'ip' or 'domain')
if (!in_array($action, ['ip', 'domain'], true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid action. Allowed: ip, domain"]);
    exit;
}

// 🔎 Validate 'query', 'type', and 'server' in one condition
if (!$query || !$type || !$server) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Query, type, and server parameters are required"]);
    exit;
}

// 🔎 Ensure 'PTR' queries require an IP
if ($type === "PTR" && $action !== "ip") {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "PTR queries require an IP address"]);
    exit;
}

// ✅ Execute DNS Query based on type
$response = match ($type) {
    "A"    => aQuery($query, $server),
    "NS"   => nsQuery($query, $server),
    "MX"   => mxQuery($query, $server),
    "SOA"  => soaQuery($query, $server),
    "TXT"  => txtQuery($query, $server),
    "PTR"  => ptrQuery($query),
    default => null
};

// 🚨 Check the success status dynamically
if (!$response || !$response['success']) {
    echo json_encode([
        "success" => false,
        "error"   => "DNS query failed or no response received",
        "query"   => $response['query'] ?? $query
    ]);
    exit;
}

// ✅ Return successful response
echo json_encode(["success" => true, "data" => [
    "query" => $response['query'],
    "output" => $response['output']
]]);
exit;
