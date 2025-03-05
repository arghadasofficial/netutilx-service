<?php
header("Content-Type: application/json");

require_once "dns_functions.php"; // Optimized DNS functions

$type = $_GET['type'] ?? null;
$domain = $_GET['domain'] ?? null;
$server = $_GET['server'] ?? null;
$ip = $_GET['ip'] ?? null;

// 🔍 Validate the request type
if (!$type) {
    echo json_encode(["error" => "Query type is required"]);
    http_response_code(400);
    exit;
}

// 🔍 Validate required parameters based on query type
$requiredParams = match ($type) {
    "A", "NS", "MX", "SOA", "TXT" => !$domain ? "Domain is required" : null,
    "PTR" => !$ip ? "IP address is required" : null,
    default => "Invalid query type"
};

// 🚨 Return error if required params are missing
if ($requiredParams) {
    echo json_encode(["error" => $requiredParams]);
    http_response_code(400);
    exit;
}

// ✅ Execute DNS Query
$response = match ($type) {
    "A"    => aQuery($domain, $server),
    "NS"   => nsQuery($domain, $server),
    "MX"   => mxQuery($domain, $server),
    "SOA"  => soaQuery($domain, $server),
    "TXT"  => txtQuery($domain, $server),
    "PTR"  => ptrQuery($ip),
};

// 🟢 Success response
echo json_encode(["success" => true, "data" => $response]);

// 🚨 Immediately stop execution to prevent further processing
exit;
?>
