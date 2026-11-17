<?php
declare(strict_types=1);
// Daftar artikel terbaru (JSON) untuk beranda.
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../admin/lib-artikel.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
$hasil = [];
foreach (array_slice(array_values(array_filter(art_semua(), 'art_publik')), 0, 6) as $a) {
    $hasil[] = [
        'judul' => (string)$a['judul'],
        'slug' => (string)$a['slug'],
        'kategori' => (string)($a['kategori'] ?? ''),
        'tanggal' => art_tgl((string)$a['waktu']),
        'foto' => art_thumb((string)($a['foto'] ?? '')),
    ];
}
echo json_encode(['artikel' => $hasil], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
