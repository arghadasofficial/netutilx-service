<?php

function executeQuery($command)
{
    $escapedCommand = escapeshellcmd($command);
    $output = shell_exec("timeout 2 $escapedCommand 2>&1");

    if ($output === null) {
        return [
            "success" => false,
            "query"   => $command,
            "output"  => "Command execution failed or timed out."
        ];
    }

    $trimmedOutput = trim((string)$output);

    return [
        "success" => !empty($trimmedOutput) && !str_contains($trimmedOutput, "SERVFAIL"),
        "query"   => $command,
        "output"  => $trimmedOutput ?: "No response received."
    ];
}

function aQuery($domain, $server)
{
    return executeQuery("dig @$server A $domain +noall +answer");
}

function nsQuery($domain, $server)
{
    return executeQuery("dig @$server NS $domain +noall +answer");
}

function mxQuery($domain, $server)
{
    return executeQuery("dig @$server MX $domain +noall +answer");
}

function soaQuery($domain, $server)
{
    return executeQuery("dig @$server SOA $domain +noall +answer");
}

function txtQuery($domain, $server)
{
    return executeQuery("dig @$server TXT $domain +noall +answer");
}

function ptrQuery($ip)
{
    return executeQuery("dig -x $ip +noall +answer");
}
