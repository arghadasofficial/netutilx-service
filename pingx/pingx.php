<?php
// pingx.php

header('Content-Type: application/json');

// Get required "query" parameter (the destination host)
$host = $_GET['query'] ?? null;
if (!$host) {
    echo json_encode(['success' => false, 'error' => 'Missing host parameter (query)']);
    exit;
}

// Sanitize the host parameter
$host = escapeshellarg($host);

// Optional parameters with defaults or null if not provided
$count      = isset($_GET['count'])      ? (int) $_GET['count']      : 4;
$interval   = isset($_GET['interval'])   ? (float) $_GET['interval'] : null; // seconds between pings
$packetsize = isset($_GET['packetsize']) ? (int) $_GET['packetsize'] : null; // -s option (bytes)
$ttl        = isset($_GET['ttl'])        ? (int) $_GET['ttl']        : null; // -t option
$timeout    = isset($_GET['timeout'])    ? (int) $_GET['timeout']    : null; // -W (timeout in seconds)

// Build the ping command based on provided parameters
// The basic format for Linux is: ping -c count [options] destination
$command = "ping";

// Count of echo requests (-c)
if ($count) {
    $command .= " -c $count";
}

// Interval between requests (-i)
if ($interval) {
    $command .= " -i $interval";
}

// Packet size in bytes (-s)
if ($packetsize) {
    $command .= " -s $packetsize";
}

// Time-to-live (-t)
if ($ttl) {
    $command .= " -t $ttl";
}

// Timeout for each packet (-W). Note: some systems use -w for deadline.
if ($timeout) {
    $command .= " -W $timeout";
}

// Append the destination host
$command .= " " . $host;

// Execute the command and capture the output
$output = shell_exec($command);

// If shell_exec returns empty output, it might indicate an error.
if ($output === null) {
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to execute ping command.',
        'command' => $command
    ]);
    exit;
}

echo json_encode(["success" => true, "data" => [
    "output" => $output
]]);
exit;
