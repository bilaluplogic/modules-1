<?php
function netquery_userapi_whoisip($args)
{
    extract($args);
    if (!isset($ip_addr))
    {
        $msg = xarML('Invalid Parameter Count');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }
    $msg = '';
    $target = $ip_addr;
    $readbuf = '';
    $nextServer = '';
    $whois_server = "whois.arin.net";
    if (!$target = gethostbyname($target))
    {
        $msg .= "IP Whois requires an IP address.";
    }
    else
    {
        if (! $sock = @fsockopen($whois_server, 43, $errnum, $error, 10))
        {
            unset($sock);
            $msg .= "Cannot connect to ".$whois_server." (".$error.")";
        }
        else
        {
            fputs($sock, $target."\r\n");
            while (!feof($sock))
            {
                $readbuf .= fgets($sock, 10240);
            }
            @fclose($sock);
        }
        if (eregi("whois.apnic.net", $readbuf)) $nextServer = "whois.apnic.net";
        else if (eregi("whois.ripe.net", $readbuf)) $nextServer = "whois.ripe.net";
        else if (eregi("whois.lacnic.net", $readbuf)) $nextServer = "whois.lacnic.net";
        else if (eregi("whois.registro.br", $readbuf)) $nextServer = "whois.registro.br";
        else if (eregi("whois.afrinic.net", $readbuf)) $nextServer = "whois.afrinic.net";
        if ($nextServer)
        {
            $readbuf = "";
            if (! $sock = @fsockopen($nextServer, 43, $errnum, $error, 10))
            {
                unset($sock);
                $msg .= "Cannot connect to ".$nextServer." (".$error.")";
            }
            else
            {
                fputs($sock, $target."\r\n");
                while (!feof($sock))
                {
                    $readbuf .= fgets($sock, 10240);
                }
                @fclose($sock);
            }
        }
        $readbuf = str_replace(" ", "&nbsp;", $readbuf);
        $msg .= nl2br($readbuf);
    }
    return $msg;
}
?>