<?php

function executeQuery($command)
{
    return [
        "query"  => $command,
        "output" => shell_exec($command)
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

?>
