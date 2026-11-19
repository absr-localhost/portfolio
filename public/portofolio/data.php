<?php
declare(strict_types=1);
// Daftar portofolio (JSON) untuk halaman /proyek. Dikelola dari /admin/?m=portofolio.
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../admin/lib-portofolio.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
echo json_encode(['portofolio' => por_publik()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
