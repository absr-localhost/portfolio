<?php
declare(strict_types=1);
// Daftar artikel terbaru (JSON) untuk beranda.
//   /artikel/terbaru.php            -> artikel biasa untuk kolom Artikel (tanpa kategori Karya)
//   /artikel/terbaru.php?kat=Karya  -> kartu Karya (di atas Video), lengkap dengan ringkasan dan sampul
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../admin/lib-artikel.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$publik = array_values(array_filter(art_semua(), 'art_publik'));
$laKarya = fn(array $a): bool => strcasecmp((string)($a['kategori'] ?? ''), 'Karya') === 0;

if (strcasecmp(trim((string)($_GET['kat'] ?? '')), 'Karya') === 0) {
    $hasil = [];
    foreach (array_slice(array_values(array_filter($publik, $laKarya)), 0, 8) as $a) {
        $hasil[] = [
            'judul' => (string)$a['judul'],
            'slug' => (string)$a['slug'],
            'ringkasan' => art_ringkas($a, 140),
            'tanggal' => art_tgl((string)$a['waktu']),
            'foto' => (string)($a['foto'] ?? ''),
            'kecil' => art_thumb((string)($a['foto'] ?? '')),
        ];
    }
    echo json_encode(['karya' => $hasil], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$hasil = [];
foreach (array_slice(array_values(array_filter($publik, fn(array $a): bool => !$laKarya($a))), 0, 6) as $a) {
    $hasil[] = [
        'judul' => (string)$a['judul'],
        'slug' => (string)$a['slug'],
        'kategori' => (string)($a['kategori'] ?? ''),
        'tanggal' => art_tgl((string)$a['waktu']),
        'foto' => art_thumb((string)($a['foto'] ?? '')),
    ];
}
echo json_encode(['artikel' => $hasil], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
