<?php
header('Content-Type: application/json');

/**
 * Runs a traceroute command with the selected network utility parameters.
 *
 * @param string $host The destination hostname or IP address.
 * @param string $protocol The protocol to use (icmp, tcp, udp).
 * @param int $maxHops The maximum number of hops to trace.
 * @param int $queries The number of probes per hop.
 * @param float $timeout The timeout for each response.
 * @return array The parsed traceroute results.
 */
function runTraceroute($host, $protocol = 'icmp', $maxHops = 30, $queries = 3, $timeout = 5.0) {
    // Sanitize input to prevent shell injection
    $sanitizedHost = escapeshellarg($host);

    // Determine the correct protocol flag
    $protocolFlag = '';
    if ($protocol === 'icmp') {
        $protocolFlag = '-I';  // ICMP (similar to ping)
    } elseif ($protocol === 'tcp') {
        $protocolFlag = '-T';  // TCP SYN (useful for firewalls)
    } elseif ($protocol === 'udp') {
        $protocolFlag = '-U';  // UDP (default traceroute method)
    }

    // Build the traceroute command with selected parameters
    $command = "traceroute $protocolFlag -m $maxHops -q $queries -w $timeout -n $sanitizedHost";

    // Execute the command and capture output
    $output = shell_exec($command);

    // If no output, return error message
    if (!$output) {
        return [
            'success' => false,
            'error'   => 'Failed to execute traceroute.',
            'command' => $command
        ];
    }

    // Parse the traceroute output
    return $output;
}

/**
 * Parses the traceroute output into a structured format.
 *
 * @param string $output The raw output of the traceroute command.
 * @return array The parsed traceroute data.
 */
function parseTracerouteResponse($output) {
    $lines = array_values(array_filter(explode("\n", trim($output))));
    $result = [];

    // Extract the header (example: "traceroute to google.com (142.250.182.206), 30 hops max, 60 byte packets")
    $header = array_shift($lines);
    if (preg_match('/traceroute to (\S+) \((\d+\.\d+\.\d+\.\d+)\)/', $header, $matches)) {
        $result['destination'] = [
            'hostname' => $matches[1],
            'ip'       => $matches[2],
        ];
    }

    $result['hops'] = [];

    // Process each hop line
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Example hop line:
        // "1  192.168.1.1  1.234 ms  1.321 ms  1.412 ms"
        // "2  * * *"
        if (preg_match('/^(\d+)\s+(\S+)?\s+\((\d+\.\d+\.\d+\.\d+)\)?\s+([\d\.]+) ms\s+([\d\.]+) ms\s+([\d\.]+) ms/', $line, $matches)) {
            $result['hops'][] = [
                'hop'       => (int)$matches[1],
                'hostname'  => $matches[2] ?: 'Unknown',
                'ip'        => $matches[3],
                'latency'   => [
                    'first'  => (float)$matches[4],
                    'second' => (float)$matches[5],
                    'third'  => (float)$matches[6],
                ],
            ];
        }
        // Handle "request timeout" or missing hops (e.g., "* * *")
        elseif (preg_match('/^(\d+)\s+\*\s+\*\s+\*/', $line, $matches)) {
            $result['hops'][] = [
                'hop'       => (int)$matches[1],
                'hostname'  => 'Request timed out',
                'ip'        => 'N/A',
                'latency'   => null,
            ];
        }
    }

    return $result;
}

// --- Process GET Request ---
$host = $_GET['query'] ?? null;
$protocol = $_GET['protocol'] ?? 'icmp';  // Default to ICMP
$maxHops = isset($_GET['max_hops']) ? (int) $_GET['max_hops'] : 30;
$queries = isset($_GET['queries']) ? (int) $_GET['queries'] : 3;
$timeout = isset($_GET['timeout']) ? (float) $_GET['timeout'] : 5.0;

if (!$host) {
    echo json_encode(['success' => false, 'error' => 'Missing host parameter (query)']);
    exit;
}

// Run traceroute and return results
$result = runTraceroute($host, $protocol, $maxHops, $queries, $timeout);
echo json_encode($result, JSON_PRETTY_PRINT);
