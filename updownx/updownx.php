<?php
header('Content-Type: application/json');

/**
 * Checks the availability of a given URL.
 *
 * @param string $url The domain/IP to check.
 * @return array Monitoring result (status, response time, etc.).
 */
function checkUrl($url) {
    // Validate URL format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['success' => false, 'error' => 'Invalid URL format.'];
    }

    // Start measuring response time
    $startTime = microtime(true);

    // Perform a GET request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // We only care about headers
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
    curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get status code
    $responseTime = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds
    curl_close($ch);

    // If the response is 0, the server might be down
    if ($httpCode == 0) {
        return [
            'success' => false,
            'error' => 'No response. The website might be down.',
            'response_time_ms' => $responseTime
        ];
    }

    // Return monitoring results
    return [
        'success' => true,
        'url' => $url,
        'status_code' => $httpCode,
        'response_time_ms' => $responseTime
    ];
}

// --- Process GET Request ---
$url = $_GET['url'] ?? null;

if (!$url) {
    echo json_encode(['success' => false, 'error' => 'Missing URL parameter.']);
    exit;
}

// Run the monitoring check
$result = checkUrl($url);

// Output the result as JSON
echo json_encode($result, JSON_PRETTY_PRINT);
