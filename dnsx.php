<?php

/**
 * Execute a DIG command with proper error handling.
 *
 * @param string $query The full DIG command.
 * @return array An array containing raw output and the executed query.
 */
function executeDnsQuery($query)
{
    $output = [];
    $status = 0;
    exec($query, $output, $status);

    return [
        'query' => $query,
        'output' => ($status === 0) ? implode("\n", $output) : "Query failed. Status Code: $status"
    ];
}

/**
 * A Record Lookup
 */
function aQuery($domain, $server)
{
    $query = "dig @" . escapeshellarg($server) . " A " . escapeshellarg($domain) . " +noall +answer";
    return executeDnsQuery($query);
}

/**
 * NS Record Lookup
 */
function nsQuery($domain, $server)
{
    $query = "dig @" . escapeshellarg($server) . " NS " . escapeshellarg($domain) . " +noall +answer";
    return executeDnsQuery($query);
}

/**
 * MX Record Lookup
 */
function mxQuery($domain, $server)
{
    $query = "dig @" . escapeshellarg($server) . " MX " . escapeshellarg($domain) . " +noall +answer";
    return executeDnsQuery($query);
}

/**
 * SOA Record Lookup
 */
function soaQuery($domain, $server)
{
    $query = "dig @" . escapeshellarg($server) . " SOA " . escapeshellarg($domain) . " +noall +answer";
    return executeDnsQuery($query);
}

/**
 * TXT Record Lookup
 */
function txtQuery($domain, $server)
{
    $query = "dig @" . escapeshellarg($server) . " TXT " . escapeshellarg($domain) . " +noall +answer";
    return executeDnsQuery($query);
}

/**
 * PTR Record Lookup
 */
function ptrQuery($ip)
{
    $query = "dig -x " . escapeshellarg($ip) . " +noall +answer";
    return executeDnsQuery($query);
}

// Example Usage
$domain = "crudoimage.com";
$server = "8.8.8.8";
$ip = "89.116.20.177";

// Test Queries
header("Content-Type: text/plain"); // For clean text output

echo "--- A Record Lookup ---\n";
print_r(aQuery($domain, $server));

echo "\n--- NS Record Lookup ---\n";
print_r(nsQuery($domain, $server));

echo "\n--- MX Record Lookup ---\n";
print_r(mxQuery($domain, $server));

echo "\n--- SOA Record Lookup ---\n";
print_r(soaQuery($domain, $server));

echo "\n--- TXT Record Lookup ---\n";
print_r(txtQuery($domain, $server));

echo "\n--- PTR Record Lookup ---\n";
print_r(ptrQuery($ip));

?>