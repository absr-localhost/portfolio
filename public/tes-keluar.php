<?php
// Tes sementara: apakah server wuaze bisa memanggil Worker Cloudflare?
// HAPUS file ini setelah selesai dites.
header('Content-Type: text/plain; charset=utf-8');
$url = 'https://statistik-situs.extreamcollectiveofficial.workers.dev/api/statistik';
echo "PHP " . PHP_VERSION . "\n";
echo "curl tersedia: " . (function_exists('curl_init') ? 'ya' : 'tidak') . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ya' : 'tidak') . "\n\n";

if (function_exists('curl_init')) {
    $t = microtime(true);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $out = curl_exec($ch);
    $kode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    echo "[cURL] kode HTTP: " . $kode . "\n";
    echo "[cURL] waktu: " . round((microtime(true) - $t) * 1000) . " ms\n";
    echo "[cURL] error: " . ($err !== '' ? $err : '-') . "\n";
    echo "[cURL] isi (200 huruf pertama): " . substr((string)$out, 0, 200) . "\n\n";
}

if (ini_get('allow_url_fopen')) {
    $t = microtime(true);
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $out = @file_get_contents($url, false, $ctx);
    echo "[fopen] berhasil: " . ($out === false ? 'tidak' : 'ya') . "\n";
    echo "[fopen] waktu: " . round((microtime(true) - $t) * 1000) . " ms\n";
    echo "[fopen] isi (200 huruf pertama): " . ($out === false ? '-' : substr($out, 0, 200)) . "\n";
}
