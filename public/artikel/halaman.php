<?php
declare(strict_types=1);
// Halaman publik Artikel: daftar (/artikel/) dan halaman baca (/artikel/<slug>).
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../admin/lib-artikel.php';

const KERANGKA = __DIR__ . '/../kerangka-artikel/index.html';
const PER_HAL = 10;

function tampil(string $judul, string $desk, string $isi, string $kanon, string $gambar = '', string $tipe = 'website', int $kode = 200, bool $noindex = false, ?array $ld = null): void
{
    http_response_code($kode);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    $penuh = $judul . ' — Agus Budiono, S.Pd.';
    $head = '<title>' . h($penuh) . '</title>' . "\n"
        . '<meta name="description" content="' . h($desk) . '">' . "\n"
        . '<link rel="canonical" href="' . h($kanon) . '">' . "\n"
        . '<meta name="robots" content="' . ($noindex ? 'noindex, follow' : 'index, follow') . '">' . "\n"
        . '<meta property="og:title" content="' . h($judul) . '">' . "\n"
        . '<meta property="og:description" content="' . h($desk) . '">' . "\n"
        . '<meta property="og:type" content="' . h($tipe) . '">' . "\n"
        . '<meta property="og:url" content="' . h($kanon) . '">' . "\n"
        . '<meta property="og:site_name" content="Agus Budiono, S.Pd.">' . "\n";
    if ($gambar !== '') {
        $head .= '<meta property="og:image" content="' . h($gambar) . '">' . "\n"
            . '<meta name="twitter:card" content="summary_large_image">' . "\n"
            . '<meta name="twitter:image" content="' . h($gambar) . '">' . "\n";
    } else {
        $head .= '<meta name="twitter:card" content="summary">' . "\n";
    }
    if ($ld) {
        $head .= '<script type="application/ld+json">' . str_replace('</', '<\/', (string)json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</script>' . "\n";
    }
    $konten = '<style>' . art_css() . '</style>' . $isi;

    $k = is_file(KERANGKA) ? (string)file_get_contents(KERANGKA) : '';
    if ($k !== '' && preg_match('#<div id="isi-artikel"[^>]*></div>#', $k, $m)) {
        $k = (string)preg_replace([
            '#<title>.*?</title>#is',
            '#<meta\s+(?:name|property)="(?:description|robots|og:[^"]*|twitter:[^"]*)"[^>]*>#i',
            '#<link\s+rel="canonical"[^>]*>#i',
        ], '', $k);
        $k = str_replace($m[0], $konten, $k);
        $k = str_replace('</head>', $head . '</head>', $k);
        echo $k;
        return;
    }
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">' . $head . '</head><body style="margin:0;font-family:system-ui,sans-serif">' . $konten . '</body></html>';
}

function url_daftar(array $p): string
{
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 1);
    return '/artikel/' . ($p ? '?' . http_build_query($p) : '');
}

$publik = array_values(array_filter(art_semua(), 'art_publik'));
$s = (string)($_GET['s'] ?? '');

if ($s !== '') {
    $a = null;
    if (preg_match('/^[a-z0-9-]{1,80}$/', $s)) {
        foreach ($publik as $b) {
            if (($b['slug'] ?? '') === $s) {
                $a = $b;
                break;
            }
        }
    }
    if (!$a) {
        tampil('Artikel tidak ditemukan', 'Artikel tidak ditemukan.', '<div class="ar"><section class="ar-hero"><div class="ar-w"><h1>Artikel tidak ditemukan</h1></div></section><section class="ar-ls"><div class="ar-w"><p class="ar-kosong">Artikel ini tidak ada atau belum terbit. <a href="/artikel/">Lihat semua artikel</a></p></div></section></div>', SITE_URL . '/artikel/', '', 'website', 404, true);
        exit;
    }
    $kanon = SITE_URL . '/artikel/' . $a['slug'];
    $gambar = !empty($a['foto']) ? SITE_URL . $a['foto'] : '';
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $a['judul'],
        'datePublished' => date('c', (int)strtotime((string)$a['waktu'])),
        'dateModified' => date('c', (int)strtotime((string)($a['diperbarui'] ?? $a['waktu']))),
        'author' => array_filter(['@type' => 'Person', 'name' => $a['penulis'] ?? 'Agus Budiono, S.Pd.', 'image' => (art_profil()['foto'] !== '' ? SITE_URL . art_profil()['foto'] : '')]),
        'description' => art_ringkas($a),
        'mainEntityOfPage' => $kanon,
    ];
    if ($gambar !== '') {
        $ld['image'] = $gambar;
    }
    tampil((string)$a['judul'], art_ringkas($a, 160), art_halaman_baca($a, art_terkait($a, $publik), $kanon), $kanon, $gambar, 'article', 200, false, $ld);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
$k = trim((string)($_GET['k'] ?? ''));
$t = trim((string)($_GET['t'] ?? ''));
$hal = max(1, (int)($_GET['hal'] ?? 1));

$kategori = [];
foreach ($publik as $b) {
    if (($b['kategori'] ?? '') !== '') {
        $kategori[$b['kategori']] = true;
    }
}
$kategori = array_keys($kategori);
sort($kategori, SORT_NATURAL | SORT_FLAG_CASE);

$hasil = array_values(array_filter($publik, function ($b) use ($q, $k, $t) {
    if ($k !== '' && mb_strtolower((string)($b['kategori'] ?? '')) !== mb_strtolower($k)) {
        return false;
    }
    if ($t !== '' && !in_array(mb_strtolower($t), array_map('mb_strtolower', (array)($b['tag'] ?? [])), true)) {
        return false;
    }
    if ($q !== '') {
        $gudang = ($b['judul'] ?? '') . ' ' . ($b['ringkasan'] ?? '') . ' ' . ($b['kategori'] ?? '') . ' ' . implode(' ', (array)($b['tag'] ?? [])) . ' ' . strip_tags((string)($b['isi'] ?? ''));
        foreach (preg_split('/\s+/u', $q) ?: [] as $kata) {
            if ($kata !== '' && mb_stripos($gudang, $kata) === false) {
                return false;
            }
        }
    }
    return true;
}));

$total = count($hasil);
$maksHal = max(1, (int)ceil($total / PER_HAL));
$hal = min($hal, $maksHal);
$tampil = array_slice($hasil, ($hal - 1) * PER_HAL, PER_HAL);

$o = '<div class="ar"><section class="ar-hero"><div class="ar-w"><h1>Artikel</h1></div></section><section class="ar-ls"><div class="ar-w">';
$o .= '<form class="ar-bar" method="get" action="/artikel/"><div class="ar-in">' . ikon('cari', 18) . '<input type="search" name="q" value="' . h($q) . '" placeholder="Cari artikel" aria-label="Cari artikel"></div>';
if ($kategori) {
    $o .= '<select name="k" aria-label="Kategori"><option value="">Semua kategori</option>';
    foreach ($kategori as $c) {
        $o .= '<option value="' . h($c) . '"' . (mb_strtolower($c) === mb_strtolower($k) ? ' selected' : '') . '>' . h($c) . '</option>';
    }
    $o .= '</select>';
}
if ($t !== '') {
    $o .= '<input type="hidden" name="t" value="' . h($t) . '">';
}
$o .= '<button type="submit" aria-label="Cari" title="Cari">' . ikon('cari', 18) . '</button></form>';
if ($t !== '') {
    $o .= '<p class="ar-fl">Tag: <b>' . h($t) . '</b><a href="' . h(url_daftar(['q' => $q, 'k' => $k])) . '">Hapus</a></p>';
}
if (!$tampil) {
    $o .= '<p class="ar-kosong">' . ($publik ? 'Tidak ada artikel yang cocok.' : 'Belum ada artikel.') . '</p>';
} else {
    foreach ($tampil as $b) {
        $o .= art_baris($b);
    }
    if ($maksHal > 1) {
        $o .= '<nav class="ar-pg" aria-label="Halaman">';
        for ($i = 1; $i <= $maksHal; $i++) {
            $o .= $i === $hal ? '<span aria-current="page">' . $i . '</span>' : '<a href="' . h(url_daftar(['q' => $q, 'k' => $k, 't' => $t, 'hal' => $i])) . '">' . $i . '</a>';
        }
        $o .= '</nav>';
    }
}
$o .= '</div></section></div>';
$saring = ($q !== '' || $k !== '' || $t !== '' || $hal > 1);
tampil('Artikel', 'Kumpulan artikel Agus Budiono, S.Pd. tentang teknologi informasi.', $o, SITE_URL . '/artikel/', '', 'website', 200, $saring);
