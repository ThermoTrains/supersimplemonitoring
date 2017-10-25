<?php
$stats = file_get_contents("php://input");
$stats = json_encode(json_decode($stats));
$timestamp = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
$content = preg_replace("/\r|\n/", "", $stats);

$line = "[" . $timestamp . "] " . $content . "\n";

$file = "./../../data/stats.txt";

// Prepend line to file
$handle = fopen($file, "r+");
$len = strlen($line);
$final_len = filesize($file) + $len;
$cache_old = fread($handle, $len);
rewind($handle);
$i = 1;

while (ftell($handle) < $final_len) {
    fwrite($handle, $line);
    $line = $cache_old;
    $cache_old = fread($handle, $len);
    fseek($handle, $i * $len);
    $i++;
}
