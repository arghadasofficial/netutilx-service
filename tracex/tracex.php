<?php
header('Content-Type: application/json');
set_time_limit(10); // Prevent infinite execution

/**
 * Runs a production-grade traceroute command.
 */
function runTraceroute($host, $protocol = 'icmp', $maxHops = 30, $queries = 3, $timeout = 5.0) {
    if (!function_exists('shell_exec')) {
        return ['success' => false, 'error' => 'shell_exec() is disabled on this server.'];
    }

    // Validate host (prevent invalid input)
    if (!filter_var($host, FILTER_VALIDATE_DOMAIN) && !filter_var($host, FILTER_VALIDATE_IP)) {
        return ['success' => false, 'error' => 'Invalid host provided.'];
    }

    $sanitizedHost = escapeshellcmd($host);
    $protocolFlag = ($protocol === 'tcp') ? '-T' : (($protocol === 'udp') ? '-U' : '-I');
    $command = "traceroute $protocolFlag -m $maxHops -q $queries -w $timeout -n $sanitizedHost";

    error_log("Executing: " . $command); // Log executed command

    $output = shell_exec($command);
    
    // Fallback to TCP if ICMP fails
    if (!$output && $protocol === 'icmp') {
        $command = "traceroute -T -m $maxHops -q $queries -w $timeout -n $sanitizedHost";
        $output = shell_exec($command);
    }

    if (!$output) {
        error_log("Traceroute failed: " . $command);
        return ['success' => false, 'error' => 'Traceroute failed.', 'command' => $command];
    }

    return [
        'success' => true,
        'output' => $output
    ]
}

// --- Handle GET Request ---
$host = $_GET['query'] ?? null;
$protocol = $_GET['protocol'] ?? 'icmp';
$maxHops = (int) ($_GET['max_hops'] ?? 30);
$queries = (int) ($_GET['queries'] ?? 3);
$timeout = (float) ($_GET['timeout'] ?? 5.0);

if (!$host) {
    echo json_encode(['success' => false, 'error' => 'Missing host parameter (query)']);
    exit;
}

echo json_encode(runTraceroute($host, $protocol, $maxHops, $queries, $timeout), JSON_PRETTY_PRINT);
