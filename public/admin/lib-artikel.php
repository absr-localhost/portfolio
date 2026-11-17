<?php
declare(strict_types=1);
// Pustaka bersama modul Artikel: dipakai admin dan halaman publik /artikel.
if (!defined('ART_DIR')) {
    define('ART_DIR', __DIR__ . '/data/artikel');
    define('MEDIA_DIR', __DIR__ . '/media');
    define('MEDIA_URL', '/admin/media/');
    define('SITE_URL', 'https://absrenv.wuaze.com');
    date_default_timezone_set('Asia/Jakarta');
}

if (!function_exists('h')) {
    function h($s): string
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

function ikon(string $n, int $u = 20): string
{
    static $p = [
        'home' => '<path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>',
        'video' => '<path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z"/><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/>',
        'atur' => '<path d="M4 6h8m4 0h4"/><circle cx="14" cy="6" r="2"/><path d="M4 12h2m4 0h10"/><circle cx="8" cy="12" r="2"/><path d="M4 18h10m4 0h2"/><circle cx="16" cy="18" r="2"/>',
        'keluar' => '<path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M9 12h12l-3 -3"/><path d="M18 15l3 -3"/>',
        'naik' => '<path d="M12 19v-14"/><path d="M18 11l-6 -6l-6 6"/>',
        'turun' => '<path d="M12 5v14"/><path d="M18 13l-6 6l-6 -6"/>',
        'hapus' => '<path d="M4 7h16"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>',
        'mata' => '<path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>',
        'mata-tutup' => '<path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/>',
        'ubah' => '<path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/>',
        'unduh' => '<path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/><path d="M7 11l5 5l5 -5"/><path d="M12 4v12"/>',
        'unggah' => '<path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/><path d="M7 9l5 -5l5 5"/><path d="M12 4v12"/>',
        'tambah' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'kunci' => '<path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/>',
        'simpan' => '<path d="M5 12l5 5l10 -10"/>',
        'riwayat' => '<path d="M12 8v4l2 2"/><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5"/>',
        'artikel' => '<path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/>',
        'cari' => '<path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/>',
        'lihat' => '<path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6"/><path d="M11 13l9 -9"/><path d="M15 4h5v5"/>',
        'tebal' => '<path d="M7 5h6a3.5 3.5 0 0 1 0 7h-6z"/><path d="M13 12h1a3.5 3.5 0 0 1 0 7h-7v-7"/>',
        'miring' => '<path d="M11 5l6 0"/><path d="M7 19l6 0"/><path d="M14 5l-4 14"/>',
        'h2' => '<text x="2" y="17" font-size="12" font-weight="700" fill="currentColor" stroke="none" font-family="system-ui,sans-serif">H2</text>',
        'h3' => '<text x="2" y="17" font-size="12" font-weight="700" fill="currentColor" stroke="none" font-family="system-ui,sans-serif">H3</text>',
        'daftar' => '<path d="M9 6l11 0"/><path d="M9 12l11 0"/><path d="M9 18l11 0"/><path d="M5 6l0 .01"/><path d="M5 12l0 .01"/><path d="M5 18l0 .01"/>',
        'nomor' => '<path d="M11 6h9"/><path d="M11 12h9"/><path d="M12 18h8"/><path d="M4 16a2 2 0 1 1 4 0c0 .591 -.5 1 -1 1.5l-3 2.5h4"/><path d="M6 10v-6l-2 2"/>',
        'kutip' => '<path d="M10 11h-4a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h3a1 1 0 0 1 1 1v6c0 2.667 -1.333 4.333 -4 5"/><path d="M19 11h-4a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h3a1 1 0 0 1 1 1v6c0 2.667 -1.333 4.333 -4 5"/>',
        'tautan' => '<path d="M9 15l6 -6"/><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/>',
        'gambar' => '<path d="M15 8h.01"/><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5"/><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3"/>',
        'bersih' => '<path d="M19 20h-10.5l-4.21 -4.3a1 1 0 0 1 0 -1.41l10 -10a1 1 0 0 1 1.41 0l5 5a1 1 0 0 1 0 1.41l-9.2 9.3"/><path d="M18 13.3l-6.3 -6.3"/>',
        'bagikan' => '<path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/><path d="M8.7 10.7l6.6 -3.4"/><path d="M8.7 13.3l6.6 3.4"/>',
        'wa' => '<path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"/><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"/>',
        'tg' => '<path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4"/>',
        'fb' => '<path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3"/>',
        'user' => '<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>',
        'kamera' => '<path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"/><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>',
        'chev' => '<path d="M6 9l6 6l6 -6"/>',
    ];
    return '<svg class="ik" width="' . $u . '" height="' . $u . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
}

/** Profil pemilik situs (nama, label admin, jabatan, foto). Disimpan di admin/data/profil.php. */
function art_profil(): array
{
    $d = ['nama' => 'Agus Budiono, S.Pd.', 'peran' => 'Admin', 'jabatan' => 'IT specialist dan full-stack developer', 'foto' => ''];
    $f = __DIR__ . '/data/profil.php';
    if (is_file($f)) {
        $x = include $f;
        if (is_array($x)) {
            foreach ($d as $k => $v) {
                if (isset($x[$k]) && is_string($x[$k]) && ($x[$k] !== '' || $k === 'foto' || $k === 'jabatan')) {
                    $d[$k] = $x[$k];
                }
            }
        }
    }
    if ($d['foto'] !== '' && !art_url_foto_ok($d['foto'])) {
        $d['foto'] = '';
    }
    return $d;
}
function art_inisial(string $n): string
{
    $i = '';
    foreach (array_slice(preg_split('/[\s,]+/u', trim($n)) ?: [], 0, 2) as $w) {
        $i .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    return $i !== '' ? $i : 'A';
}
function art_avatar(array $p, string $kls = ''): string
{
    $in = art_inisial((string)$p['nama']);
    $isi = $p['foto'] !== '' ? '<img src="' . h(art_thumb((string)$p['foto'])) . '" alt="">' : h($in);
    return '<span class="av ' . h($kls) . '" data-i="' . h($in) . '">' . $isi . '</span>';
}

function art_siapkan(): void
{
    $deny = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
    $noexec = "<FilesMatch \"\\.(php|phtml|phar|pl|py|cgi|sh)\$\">\n" . $deny . "</FilesMatch>\n";
    foreach ([[ART_DIR, $deny], [MEDIA_DIR, $noexec]] as [$d, $ht]) {
        if (!is_dir($d)) {
            mkdir($d, 0755, true);
        }
        if (!is_file($d . '/.htaccess')) {
            file_put_contents($d . '/.htaccess', $ht);
        }
    }
}

function art_semua(): array
{
    $hasil = [];
    foreach (glob(ART_DIR . '/*.json') ?: [] as $f) {
        $a = json_decode((string)file_get_contents($f), true);
        if (is_array($a) && !empty($a['id'])) {
            $hasil[] = $a;
        }
    }
    usort($hasil, fn($x, $y) => strcmp((string)($y['waktu'] ?? ''), (string)($x['waktu'] ?? '')));
    return $hasil;
}
function art_baca(string $id): ?array
{
    if (!preg_match('/^[a-f0-9]{16}$/', $id)) {
        return null;
    }
    $f = ART_DIR . '/' . $id . '.json';
    if (!is_file($f)) {
        return null;
    }
    $a = json_decode((string)file_get_contents($f), true);
    return is_array($a) ? $a : null;
}
function art_tulis(array $a): void
{
    file_put_contents(ART_DIR . '/' . $a['id'] . '.json', json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function art_hapus(string $id): void
{
    if (preg_match('/^[a-f0-9]{16}$/', $id)) {
        @unlink(ART_DIR . '/' . $id . '.json');
    }
}
function art_publik(array $a): bool
{
    return ($a['status'] ?? '') === 'terbit' && (string)($a['waktu'] ?? '') <= date('Y-m-d\TH:i');
}
function art_status(array $a): string
{
    if (($a['status'] ?? '') !== 'terbit') {
        return 'draf';
    }
    return art_publik($a) ? 'terbit' : 'terjadwal';
}
function art_slug(string $judul, string $kecuali = ''): string
{
    $s = $judul;
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if (is_string($t) && $t !== '') {
            $s = $t;
        }
    }
    $s = strtolower($s);
    $s = (string)preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim(substr(trim($s, '-'), 0, 70), '-');
    if ($s === '') {
        $s = 'artikel';
    }
    $pakai = [];
    foreach (art_semua() as $a) {
        if (($a['id'] ?? '') !== $kecuali) {
            $pakai[(string)($a['slug'] ?? '')] = true;
        }
    }
    $dasar = $s;
    $n = 2;
    while (isset($pakai[$s])) {
        $s = $dasar . '-' . $n++;
    }
    return $s;
}
function art_bulan(int $n): string
{
    return ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$n - 1];
}
function art_tgl(string $w): string
{
    $t = strtotime($w);
    return $t ? date('j', $t) . ' ' . art_bulan((int)date('n', $t)) . ' ' . date('Y', $t) : $w;
}
function art_menit(string $html): int
{
    $teks = trim(strip_tags($html));
    $n = $teks === '' ? 0 : count(preg_split('/\s+/u', $teks) ?: []);
    return max(1, (int)ceil($n / 200));
}
function art_ringkas(array $a, int $n = 160): string
{
    $r = trim((string)($a['ringkasan'] ?? ''));
    if ($r !== '') {
        return $r;
    }
    $t = trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($a['isi'] ?? ''))));
    return mb_strlen($t) > $n ? rtrim(mb_substr($t, 0, $n)) . '...' : $t;
}
function art_thumb(string $url): string
{
    if ($url !== '' && preg_match('#^/admin/media/m_([a-f0-9]{16})\.jpg$#', $url, $m) && is_file(MEDIA_DIR . '/s_' . $m[1] . '.jpg')) {
        return MEDIA_URL . 's_' . $m[1] . '.jpg';
    }
    return $url;
}
function art_url_foto_ok(string $u): bool
{
    return $u === '' || (bool)preg_match('#^/admin/media/[a-z]_[a-f0-9]{16}\.jpg$#', $u);
}

/** Simpan gambar JPEG (sudah diperkecil di browser) dan buat gambar kecil. Mengembalikan URL atau pesan galat. */
function art_simpan_gambar(array $f): array
{
    if (($f['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$f['tmp_name'])) {
        return ['err' => 'Gambar gagal terunggah (kode ' . ($f['error'] ?? '-') . ').'];
    }
    if ($f['size'] > 3000000) {
        return ['err' => 'Gambar lebih dari 3 MB.'];
    }
    $gi = @getimagesize($f['tmp_name']);
    if (!$gi || $gi[2] !== IMAGETYPE_JPEG) {
        return ['err' => 'Gambar harus JPEG.'];
    }
    art_siapkan();
    $id = bin2hex(random_bytes(8));
    $tujuan = MEDIA_DIR . '/m_' . $id . '.jpg';
    if (!move_uploaded_file($f['tmp_name'], $tujuan)) {
        return ['err' => 'Gagal menyimpan gambar di server.'];
    }
    if (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
        $im = @imagecreatefromjpeg($tujuan);
        if ($im) {
            $w = imagesx($im);
            $hh = imagesy($im);
            $tw = min(360, $w);
            $th = max(1, (int)round($hh * $tw / max(1, $w)));
            $kecil = imagecreatetruecolor($tw, $th);
            imagecopyresampled($kecil, $im, 0, 0, 0, 0, $tw, $th, $w, $hh);
            imagejpeg($kecil, MEDIA_DIR . '/s_' . $id . '.jpg', 80);
            imagedestroy($kecil);
            imagedestroy($im);
        }
    }
    return ['ok' => true, 'url' => MEDIA_URL . 'm_' . $id . '.jpg'];
}

/* ---------- Pembersih HTML isi artikel ---------- */
function art_ganti_tag(DOMElement $e, string $baru): DOMElement
{
    $n = $e->ownerDocument->createElement($baru);
    while ($e->firstChild) {
        $n->appendChild($e->firstChild);
    }
    $e->parentNode->replaceChild($n, $e);
    return $n;
}
function art_blok_ada(DOMElement $e): bool
{
    foreach (['p', 'ul', 'ol', 'blockquote', 'h2', 'h3', 'h4', 'figure', 'hr'] as $t) {
        if ($e->getElementsByTagName($t)->length > 0) {
            return true;
        }
    }
    return false;
}
function art_buka(DOMNode $induk, DOMElement $e): void
{
    while ($e->firstChild) {
        $induk->insertBefore($e->firstChild, $e);
    }
    $induk->removeChild($e);
}
function art_bersih_node(DOMNode $n): void
{
    static $ok = ['p', 'h2', 'h3', 'h4', 'strong', 'em', 'u', 'ul', 'ol', 'li', 'blockquote', 'br', 'hr', 'a', 'img', 'figure', 'figcaption'];
    static $buang = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'link', 'meta', 'noscript', 'template', 'video', 'audio', 'canvas', 'applet', 'base'];
    static $peta = ['b' => 'strong', 'i' => 'em', 'h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4'];
    $anak = [];
    foreach ($n->childNodes as $c) {
        $anak[] = $c;
    }
    foreach ($anak as $c) {
        if (!($c instanceof DOMElement)) {
            if ($c->nodeType !== XML_TEXT_NODE) {
                $n->removeChild($c);
            }
            continue;
        }
        $t = strtolower($c->nodeName);
        if (in_array($t, $buang, true)) {
            $n->removeChild($c);
            continue;
        }
        art_bersih_node($c);
        if ($t === 'div') {
            if (art_blok_ada($c)) {
                art_buka($n, $c);
                continue;
            }
            $c = art_ganti_tag($c, 'p');
            $t = 'p';
        } elseif (isset($peta[$t])) {
            $c = art_ganti_tag($c, $peta[$t]);
            $t = $peta[$t];
        }
        if (!in_array($t, $ok, true)) {
            art_buka($n, $c);
            continue;
        }
        $href = $t === 'a' ? trim($c->getAttribute('href')) : '';
        $src = $t === 'img' ? trim($c->getAttribute('src')) : '';
        $alt = $t === 'img' ? mb_substr(trim($c->getAttribute('alt')), 0, 160) : '';
        $nama = [];
        foreach ($c->attributes as $at) {
            $nama[] = $at->nodeName;
        }
        foreach ($nama as $at) {
            $c->removeAttribute($at);
        }
        if ($t === 'a') {
            if (!preg_match('#^(https?://|mailto:|/(?!/))#i', $href)) {
                art_buka($n, $c);
                continue;
            }
            $c->setAttribute('href', $href);
            if (preg_match('#^https?://#i', $href)) {
                $c->setAttribute('target', '_blank');
                $c->setAttribute('rel', 'noopener noreferrer');
            }
        } elseif ($t === 'img') {
            if (!preg_match('#^(/admin/media/[a-z]_[a-f0-9]{16}\.jpg|https://[^\s"\'<>]+)$#i', $src)) {
                $n->removeChild($c);
                continue;
            }
            $c->setAttribute('src', $src);
            $c->setAttribute('alt', $alt);
            $c->setAttribute('loading', 'lazy');
        }
    }
}
function art_bersih_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $d = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $d->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $d->getElementsByTagName('div')->item(0);
    if (!$root) {
        return '';
    }
    art_bersih_node($root);

    $blok = ['p', 'h2', 'h3', 'h4', 'ul', 'ol', 'blockquote', 'figure', 'hr'];
    $anak = [];
    foreach ($root->childNodes as $c) {
        $anak[] = $c;
    }
    $lari = [];
    $tutup = function () use (&$lari, $root, $d) {
        if (!$lari) {
            return;
        }
        $ada = false;
        foreach ($lari as $x) {
            if (!($x instanceof DOMText) || trim($x->nodeValue ?? '') !== '') {
                $ada = true;
            }
        }
        if ($ada) {
            $p = $d->createElement('p');
            $root->insertBefore($p, $lari[0]);
            foreach ($lari as $x) {
                $p->appendChild($x);
            }
        } else {
            foreach ($lari as $x) {
                $root->removeChild($x);
            }
        }
        $lari = [];
    };
    foreach ($anak as $c) {
        if ($c instanceof DOMElement && in_array(strtolower($c->nodeName), $blok, true)) {
            $tutup();
        } else {
            $lari[] = $c;
        }
    }
    $tutup();

    $keluar = [];
    $semua = [];
    foreach ($root->childNodes as $c) {
        $semua[] = $c;
    }
    foreach ($semua as $c) {
        if (!($c instanceof DOMElement)) {
            continue;
        }
        $t = strtolower($c->nodeName);
        if ($t === 'p') {
            $imgs = $c->getElementsByTagName('img');
            $teks = trim($c->textContent);
            if ($imgs->length === 1 && $teks === '' && $c->getElementsByTagName('br')->length === 0) {
                $img = $imgs->item(0);
                $fig = $d->createElement('figure');
                $fig->appendChild($img->cloneNode(true));
                $alt = $img->getAttribute('alt');
                if ($alt !== '') {
                    $cap = $d->createElement('figcaption');
                    $cap->appendChild($d->createTextNode($alt));
                    $fig->appendChild($cap);
                }
                $root->replaceChild($fig, $c);
                $c = $fig;
            } elseif ($teks === '' && $imgs->length === 0) {
                $root->removeChild($c);
                continue;
            }
        }
        $keluar[] = $d->saveHTML($c);
    }
    return implode("\n", $keluar);
}

/* ---------- Tampilan ---------- */
function art_css(): string
{
    return <<<'CSS'
.ar{color:#1f2937;font-size:14px;line-height:1.55}
.ar *{box-sizing:border-box}
.ar a{color:inherit}
.ar .ik{flex:none;display:block}
.ar-w{max-width:72rem;margin:0 auto;padding:0 16px}
.ar-n{max-width:46rem}
@media(min-width:640px){.ar-w{padding:0 24px}}
.ar-hero{background:#0B2545;color:#fff;padding:48px 0}
@media(min-width:640px){.ar-hero{padding:64px 0}}
.ar-hero h1{margin:0;font-size:24px;line-height:1.3;font-weight:700;letter-spacing:-.01em}
@media(min-width:640px){.ar-hero h1.ar-j{font-size:30px}}
.ar-by{display:flex;align-items:center;gap:10px;margin:18px 0 0}
.ar-av{width:44px;height:44px;border-radius:50%;object-fit:cover;background:#1d4478;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;border:2px solid rgba(255,255,255,.35);flex:none}
.ar-bt{display:flex;flex-direction:column;line-height:1.3;min-width:0}
.ar-bt b{font-size:14px;color:#fff;font-weight:600}
.ar-bt small{font-size:12px;color:#cbd5e1}
.ar-hero .ar-meta{margin:12px 0 0;color:#cbd5e1;font-size:13px}
.ar-kat{display:inline-block;border:1px solid rgba(255,255,255,.4);border-radius:6px;padding:2px 10px;font-size:12px;color:#fff;text-decoration:none;margin-bottom:12px}
.ar-bd{padding:28px 0 8px;background:#fff}
.ar-fu{margin:0 0 20px}
.ar-fu img{display:block;width:100%;height:auto;border-radius:8px;background:#e2e8f0}
.ar-fu figcaption,.ar-isi figcaption{margin-top:6px;font-size:12px;color:#4A5568}
.ar-lead{font-size:16px;line-height:1.6;color:#4A5568;margin:0 0 20px;padding-bottom:20px;border-bottom:1px solid #e2e8f0}
.ar-isi{font-size:15px;line-height:1.8;color:#1f2937;overflow-wrap:anywhere}
.ar-isi p{margin:0 0 1.1em}
.ar-isi h2{font-size:20px;line-height:1.35;margin:1.6em 0 .6em;color:#0B2545}
.ar-isi h3{font-size:17px;line-height:1.4;margin:1.4em 0 .5em;color:#0B2545}
.ar-isi h4{font-size:15px;margin:1.2em 0 .4em;color:#0B2545}
.ar-isi ul,.ar-isi ol{margin:0 0 1.1em;padding-left:1.4em}
.ar-isi li{margin:.25em 0}
.ar-isi blockquote{margin:1.2em 0;padding:.4em 0 .4em 14px;border-left:3px solid #0B2545;color:#4A5568}
.ar-isi blockquote p{margin:0}
.ar-isi a{color:#0B2545;text-decoration:underline}
.ar-isi img{display:block;max-width:100%;height:auto;border-radius:8px;margin:0 auto}
.ar-isi figure{margin:1.4em 0}
.ar-isi hr{border:0;border-top:1px solid #e2e8f0;margin:1.6em 0}
.ar-tags{display:flex;flex-wrap:wrap;gap:6px;margin:22px 0 0}
.ar-tag{display:inline-block;border:1px solid #cbd5e1;border-radius:6px;padding:2px 9px;font-size:12px;color:#4A5568;text-decoration:none;background:#fff}
.ar-tag:hover{border-color:#0B2545;color:#0B2545}
.ar-sh{display:flex;align-items:center;gap:8px;margin:22px 0 0;padding:14px 0;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0}
.ar-sh b{font-size:13px;color:#0B2545;margin-right:4px}
.ar-sh a,.ar-sh button{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;color:#0B2545;cursor:pointer;padding:0}
.ar-sh a:hover,.ar-sh button:hover{background:#f1f5f9}
.ar-rel{background:#f8fafc;padding:32px 0;margin-top:28px}
.ar-rel h2,.ar-ls h2{margin:0 0 6px;font-size:17px;color:#0B2545}
.ar-ls{padding:24px 0 48px}
.ar-bar{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 14px}
.ar-bar .ar-in{position:relative;flex:1 1 220px}
.ar-bar input,.ar-bar select{width:100%;height:40px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font:inherit;background:#fff;color:#1f2937}
.ar-bar input{padding-left:34px}
.ar-bar .ar-in .ik{position:absolute;left:10px;top:11px;color:#4A5568}
.ar-bar select{flex:0 1 180px;width:auto}
.ar-bar button{height:40px;width:40px;border-radius:6px;border:0;background:#0B2545;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
.ar-fl{margin:0 0 8px;font-size:13px;color:#4A5568}
.ar-fl a{display:inline-block;border:1px solid #cbd5e1;border-radius:6px;padding:1px 8px;margin-left:4px;text-decoration:none;color:#0B2545;background:#fff}
.ar-row{display:flex;gap:12px;padding:12px 0;border-top:1px solid #e2e8f0;text-decoration:none;color:inherit}
.ar-row:first-of-type{border-top:0}
.ar-th{width:88px;height:62px;object-fit:cover;border-radius:6px;background:#e2e8f0;flex:none;display:flex;align-items:center;justify-content:center;color:#94a3b8}
@media(min-width:640px){.ar-th{width:120px;height:80px}}
.ar-tx{min-width:0;display:flex;flex-direction:column;gap:2px}
.ar-ms{font-size:12px;color:#4A5568}
.ar-ms .ar-k{color:#0B2545;font-weight:600;margin-right:6px}
.ar-tx b{font-size:14px;line-height:1.4;color:#0B2545;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.ar-row:hover b{text-decoration:underline}
.ar-rs{display:none;font-size:13px;color:#4A5568;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
@media(min-width:640px){.ar-rs{display:-webkit-box}}
.ar-pg{display:flex;flex-wrap:wrap;gap:6px;margin-top:18px}
.ar-pg a,.ar-pg span{min-width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #e2e8f0;background:#fff;color:#0B2545;text-decoration:none;font-size:13px}
.ar-pg span{background:#0B2545;color:#fff;border-color:#0B2545}
.ar-kosong{padding:28px 0;color:#4A5568}
CSS;
}

function art_baris(array $a): string
{
    $foto = art_thumb((string)($a['foto'] ?? ''));
    $th = $foto !== ''
        ? '<img class="ar-th" src="' . h($foto) . '" alt="" loading="lazy" width="120" height="80">'
        : '<span class="ar-th">' . ikon('artikel', 24) . '</span>';
    $kat = (string)($a['kategori'] ?? '');
    return '<a class="ar-row" href="/artikel/' . h($a['slug']) . '">' . $th
        . '<span class="ar-tx"><span class="ar-ms">' . ($kat !== '' ? '<span class="ar-k">' . h($kat) . '</span>' : '')
        . '<time datetime="' . h($a['waktu']) . '">' . h(art_tgl((string)$a['waktu'])) . '</time></span>'
        . '<b>' . h($a['judul']) . '</b>'
        . '<span class="ar-rs">' . h(art_ringkas($a)) . '</span></span></a>';
}

function art_terkait(array $a, array $publik, int $maks = 3): array
{
    $skor = [];
    foreach ($publik as $b) {
        if ($b['id'] === $a['id']) {
            continue;
        }
        $s = 0;
        if (($a['kategori'] ?? '') !== '' && ($b['kategori'] ?? '') === $a['kategori']) {
            $s += 2;
        }
        $s += count(array_intersect(array_map('mb_strtolower', (array)($a['tag'] ?? [])), array_map('mb_strtolower', (array)($b['tag'] ?? []))));
        $skor[] = [$s, $b];
    }
    usort($skor, fn($x, $y) => $y[0] <=> $x[0] ?: strcmp((string)$y[1]['waktu'], (string)$x[1]['waktu']));
    $ada = array_filter($skor, fn($x) => $x[0] > 0);
    $pakai = $ada ?: $skor;
    return array_map(fn($x) => $x[1], array_slice(array_values($pakai), 0, $maks));
}

/** Isi halaman baca (tanpa bingkai situs). */
function art_halaman_baca(array $a, array $terkait, string $urlPenuh): string
{
    $kat = (string)($a['kategori'] ?? '');
    $foto = (string)($a['foto'] ?? '');
    $o = '<div class="ar"><section class="ar-hero"><div class="ar-w ar-n">';
    if ($kat !== '') {
        $o .= '<a class="ar-kat" href="/artikel/?k=' . h(rawurlencode($kat)) . '">' . h($kat) . '</a>';
    }
    $p = art_profil();
    $nama = trim((string)($a['penulis'] ?? '')) !== '' ? (string)$a['penulis'] : $p['nama'];
    $pakaiFoto = $p['foto'] !== '' && mb_strtolower($nama) === mb_strtolower($p['nama']);
    $o .= '<h1 class="ar-j">' . h($a['judul']) . '</h1>';
    $o .= '<div class="ar-by">' . ($pakaiFoto
        ? '<img class="ar-av" src="' . h(art_thumb($p['foto'])) . '" alt="' . h($nama) . '" width="44" height="44">'
        : '<span class="ar-av" aria-hidden="true">' . h(art_inisial($nama)) . '</span>')
        . '<span class="ar-bt"><b>' . h($nama) . '</b>' . ($pakaiFoto && $p['jabatan'] !== '' ? '<small>' . h($p['jabatan']) . '</small>' : '') . '</span></div>';
    $o .= '<p class="ar-meta"><time datetime="' . h($a['waktu']) . '">' . h(art_tgl((string)$a['waktu'])) . '</time> &middot; ' . art_menit((string)$a['isi']) . ' menit baca</p>';
    $o .= '</div></section><section class="ar-bd"><div class="ar-w ar-n">';
    if ($foto !== '') {
        $o .= '<figure class="ar-fu"><img src="' . h($foto) . '" alt="' . h($a['foto_ket'] ?? '') . '">';
        if (($a['foto_ket'] ?? '') !== '') {
            $o .= '<figcaption>' . h($a['foto_ket']) . '</figcaption>';
        }
        $o .= '</figure>';
    }
    if (trim((string)($a['ringkasan'] ?? '')) !== '') {
        $o .= '<p class="ar-lead">' . h($a['ringkasan']) . '</p>';
    }
    $o .= '<div class="ar-isi">' . $a['isi'] . '</div>';
    $tags = (array)($a['tag'] ?? []);
    if ($tags) {
        $o .= '<div class="ar-tags">';
        foreach ($tags as $t) {
            $o .= '<a class="ar-tag" href="/artikel/?t=' . h(rawurlencode((string)$t)) . '">' . h($t) . '</a>';
        }
        $o .= '</div>';
    }
    $u = rawurlencode($urlPenuh);
    $jd = rawurlencode((string)$a['judul']);
    $o .= '<div class="ar-sh"><b>Bagikan</b>'
        . '<a href="https://wa.me/?text=' . $jd . '%20' . $u . '" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="WhatsApp">' . ikon('wa', 18) . '</a>'
        . '<a href="https://t.me/share/url?url=' . $u . '&amp;text=' . $jd . '" target="_blank" rel="noopener noreferrer" aria-label="Telegram" title="Telegram">' . ikon('tg', 18) . '</a>'
        . '<a href="https://www.facebook.com/sharer/sharer.php?u=' . $u . '" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook">' . ikon('fb', 18) . '</a>'
        . '<button type="button" aria-label="Salin tautan" title="Salin tautan" data-u="' . h($urlPenuh) . '" onclick="var b=this,u=b.getAttribute(\'data-u\');(navigator.clipboard?navigator.clipboard.writeText(u):Promise.reject()).catch(function(){var t=document.createElement(\'textarea\');t.value=u;document.body.appendChild(t);t.select();try{document.execCommand(\'copy\')}catch(e){}document.body.removeChild(t)});b.style.background=\'#ecfdf5\';setTimeout(function(){b.style.background=\'\'},1200)">' . ikon('tautan', 18) . '</button>'
        . '</div></div></section>';
    if ($terkait) {
        $o .= '<section class="ar-rel"><div class="ar-w ar-n"><h2>Artikel terkait</h2>';
        foreach ($terkait as $b) {
            $o .= art_baris($b);
        }
        $o .= '</div></section>';
    }
    return $o . '</div>';
}
