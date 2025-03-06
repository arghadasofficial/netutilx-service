<?php
header("Content-Type: application/json");

require_once "dns_functions.php"; // Optimized DNS functions

class DnsApiHandler
{
    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Only GET requests are allowed");
        }

        // Get and sanitize parameters
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
        $query  = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);
        $type   = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
        $server = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_STRING);

        // Validate parameters
        if (!in_array($action, ['ip', 'domain'], true)) {
            $this->sendErrorResponse(400, "Invalid action. Allowed: ip, domain");
        }

        if (!$query || !$type || !$server) {
            $this->sendErrorResponse(400, "Query, type, and server parameters are required");
        }

        if ($type === "PTR" && $action !== "ip") {
            $this->sendErrorResponse(400, "PTR queries require an IP address");
        }

        // Execute DNS Query
        $response = match ($type) {
            "A"    => $this->safeDnsQuery('aQuery', $query, $server),
            "NS"   => $this->safeDnsQuery('nsQuery', $query, $server),
            "MX"   => $this->safeDnsQuery('mxQuery', $query, $server),
            "SOA"  => $this->safeDnsQuery('soaQuery', $query, $server),
            "TXT"  => $this->safeDnsQuery('txtQuery', $query, $server),
            "PTR"  => $this->safeDnsQuery('ptrQuery', $query),
            default => $this->sendErrorResponse(400, "Invalid DNS type"),
        };

        $this->sendSuccessResponse($response);
    }

    private function safeDnsQuery(string $function, string $query, ?string $server = null)
    {
        if (!function_exists($function)) {
            return ["error" => "Function $function not found"];
        }

        return $server ? $function($query, $server) : $function($query);
    }

    private function sendErrorResponse(int $code, string $message)
    {
        http_response_code($code);
        echo json_encode(["error" => $message]);
        exit;
    }

    private function sendSuccessResponse($data)
    {
        echo json_encode(["success" => true, "data" => $data]);
        exit;
    }
}

// Initialize the handler
$dnsApi = new DnsApiHandler();
$dnsApi->handleRequest();