<?php
header("Content-Type: application/json");

require_once "dns_functions.php"; // Optimized DNS functions

// 🚀 Ensure the request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Only GET requests are allowed"]);
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
    echo json_encode(["error" => "Invalid action. Allowed: ip, domain"]);
    exit;
}

// 🔎 Validate 'query', 'type', and 'server' in one condition
if (!$query || !$type || !$server) {
    http_response_code(400);
    echo json_encode(["error" => "Query, type, and server parameters are required"]);
    exit;
}

// 🔎 Ensure 'PTR' queries require an IP
if ($type === "PTR" && $action !== "ip") {
    http_response_code(400);
    echo json_encode(["error" => "PTR queries require an IP address"]);
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
    default => ["error" => "Invalid DNS type"]
};

if(!empty($response)) {
    echo json_encode(["success" => true, "data" => $response]);
    exit;
}

echo json_encode(["success" => false, "data" => "Service failed to respond"]);
exit;

