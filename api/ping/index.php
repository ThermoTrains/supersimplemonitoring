<?php
$stats = file_get_contents("php://input");
$stats = json_decode($stats);
$timestamp = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
$stats->timestamp = $timestamp;

if (property_exists($stats, 'disk') && is_numeric($stats->disk)) {
    $gigabytesLeft = $stats->disk / 1024 / 1024 / 1024;
    if ($gigabytesLeft < 5) {
        mail('haeni.sebastian@gmail.com', "Thermobox only has $gigabytesLeft GiB left of disk space", 'Act quickly!');
    }
}

$stats = json_encode($stats);
$content = preg_replace("/\r|\n/", "", $stats) . "\n";

$file = "./../../data/stats.txt";

// Prepend line to file
$handle = fopen($file, "r+");
$len = strlen($content);
$final_len = filesize($file) + $len;
$cache_old = fread($handle, $len);
rewind($handle);
$i = 1;

while (ftell($handle) < $final_len) {
    fwrite($handle, $content);
    $content = $cache_old;
    $cache_old = fread($handle, $len);
    fseek($handle, $i * $len);
    $i++;
}
