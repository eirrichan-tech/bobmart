<?php
$tests = [
    ['sandbox.smtp.mailtrap.io', 2525],
    ['sandbox.smtp.mailtrap.io', 587],
    ['sandbox.smtp.mailtrap.io', 465],
    ['sandbox.smtp.mailtrap.io', 25],
    ['sandbox.api.mailtrap.io', 443],   // the HTTPS way
];
echo "<h3>Connection check</h3>";
echo "cURL: " . (function_exists('curl_init') ? 'ON' : 'OFF') . " | OpenSSL: " . (extension_loaded('openssl') ? 'ON' : 'OFF') . "<br><br>";
foreach ($tests as [$host, $port]) {
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($fp) { echo "$host:$port &rarr; <b style='color:green'>OPEN</b><br>"; fclose($fp); }
    else     { echo "$host:$port &rarr; <b style='color:red'>BLOCKED</b> ($errstr)<br>"; }
}