<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');

const DATA = __DIR__ . '/data';
const VID = __DIR__ . '/video';
const MAXB = 19 * 1024 * 1024;
const MAXD = 30.5;
const MAXN = 12;
const MAXLOG = 100;

require __DIR__ . '/lib-artikel.php';

$deny = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
$noexec = "<FilesMatch \"\\.(php|phtml|phar|pl|py|cgi|sh)\$\">\n" . $deny . "</FilesMatch>\n";

function siapkan(string $d, string $ht): void
{
    if (!is_dir($d)) {
        mkdir($d, 0755, true);
    }
    $f = $d . '/.htaccess';
    if (!is_file($f)) {
        file_put_contents($f, $ht);
    }
}
function muat(string $f): array
{
    return is_file($f) ? (array)(include $f) : [];
}
function simpan(string $f, array $a): void
{
    file_put_contents($f, "<?php\nreturn " . var_export($a, true) . ";\n", LOCK_EX);
}
function catat(string $t): void
{
    $l = muat(DATA . '/log.php');
    array_unshift($l, ['w' => date('Y-m-d H:i'), 't' => $t]);
    simpan(DATA . '/log.php', array_slice($l, 0, MAXLOG));
}
function baca_daftar(): array
{
    $f = VID . '/video.json';
    if (!is_file($f)) {
        return [];
    }
    $j = json_decode((string)file_get_contents($f), true);
    if (!is_array($j)) {
        return [];
    }
    if (isset($j['daftar']) && is_array($j['daftar'])) {
        return array_values($j['daftar']);
    }
    if (!empty($j['video'])) {
        $j['id'] = substr((string)$j['video'], 2, 16);
        return [$j];
    }
    return [];
}
function tulis_daftar(array $d): void
{
    file_put_contents(VID . '/video.json', json_encode(['daftar' => array_values($d)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function cari(array $d, string $id): int
{
    foreach ($d as $i => $v) {
        if (($v['id'] ?? '') === $id) {
            return $i;
        }
    }
    return -1;
}
function hapus_berkas(array $v): void
{
    foreach (['video', 'sampul'] as $k) {
        if (!empty($v[$k]) && preg_match('/^[vc]_[a-f0-9]{16}\.(mp4|jpg)$/', $v[$k])) {
            @unlink(VID . '/' . $v[$k]);
        }
    }
}
function json_out(array $a, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($a);
    exit;
}
function bersih_judul(string $s): string
{
    return mb_substr(trim(strip_tags($s)), 0, 80);
}
function bersih_desk(string $s): string
{
    return mb_substr(trim((string)preg_replace('/\s+/', ' ', strip_tags($s))), 0, 200);
}
function bersih_waktu(string $s): string
{
    $d = DateTime::createFromFormat('Y-m-d\TH:i', $s);
    return ($d && $d->format('Y-m-d\TH:i') === $s) ? $s : date('Y-m-d\TH:i');
}
function btn(string $csrf, string $act, string $id, string $ik, string $label, string $cls = '', string $ask = ''): string
{
    $on = $ask !== '' ? ' onsubmit="return confirm(\'' . h($ask) . '\')"' : '';
    return '<form method="post"' . $on . '><input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="action" value="' . h($act) . '"><input type="hidden" name="id" value="' . h($id) . '"><button class="ib ' . h($cls) . '" type="submit" title="' . h($label) . '" aria-label="' . h($label) . '">' . ikon($ik, 18) . '</button></form>';
}
function art_dari_post(bool $auto): array
{
    $teks = fn(string $k, int $n) => mb_substr(trim(strip_tags((string)($_POST[$k] ?? ''))), 0, $n);
    $tag = [];
    foreach (explode(',', (string)($_POST['tag'] ?? '')) as $t) {
        $t = mb_substr(trim(strip_tags($t)), 0, 30);
        if ($t !== '' && !in_array(mb_strtolower($t), array_map('mb_strtolower', $tag), true)) {
            $tag[] = $t;
        }
    }
    $foto = trim((string)($_POST['foto'] ?? ''));
    $penulis = $teks('penulis', 80);
    return [
        'judul' => $teks('judul', 150),
        'ringkasan' => mb_substr(trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)($_POST['ringkasan'] ?? '')))), 0, 300),
        'kategori' => $teks('kategori', 40),
        'tag' => array_slice($tag, 0, 8),
        'penulis' => $penulis !== '' ? $penulis : 'Agus Budiono, S.Pd.',
        'foto' => art_url_foto_ok($foto) ? $foto : '',
        'foto_ket' => $teks('foto_ket', 160),
        'isi' => art_bersih_html((string)($_POST['isi'] ?? '')),
        'status' => (!$auto && ($_POST['status'] ?? '') === 'terbit') ? 'terbit' : 'draf',
        'waktu' => bersih_waktu((string)($_POST['waktu'] ?? '')),
    ];
}
function mb_fmt(int $b): string
{
    return number_format($b / 1048576, 1, ',', '.');
}
function tgl(string $s): string
{
    $t = strtotime($s);
    if (!$t) {
        return $s;
    }
    $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date('j', $t) . ' ' . $bln[(int)date('n', $t) - 1] . ' ' . date('Y, H:i', $t);
}

siapkan(DATA, $deny);
siapkan(DATA . '/sess', $deny);
siapkan(VID, $noexec);
art_siapkan();

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

session_save_path(DATA . '/sess');
session_name('adm');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/admin/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

$auth = muat(DATA . '/auth.php');
$belumAtur = empty($auth['hash']);
$sv = (int)($auth['sv'] ?? 0);
$csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
$err = '';
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
$prof = art_profil();

if (!empty($_SESSION['ok']) && (int)($_SESSION['sv'] ?? -1) !== $sv) {
    unset($_SESSION['ok'], $_SESSION['sv']);
}

if ($post && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    json_out(['err' => 'Ukuran kiriman melebihi batas server.'], 413);
}

$act = $post ? (string)($_POST['action'] ?? '') : '';
if ($act !== '' && !hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
    if (in_array($act, ['upload', 'media', 'profil_foto'], true) || ($act === 'artikel_simpan' && !empty($_POST['auto']))) {
        json_out(['err' => 'Sesi kedaluwarsa. Muat ulang halaman.'], 400);
    }
    $err = 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    $act = '';
}

function masukkan(int $sv): void
{
    session_regenerate_id(true);
    $_SESSION['ok'] = 1;
    $_SESSION['sv'] = $sv;
}

if ($act === 'setup' && $belumAtur) {
    $p1 = (string)($_POST['p1'] ?? '');
    $p2 = (string)($_POST['p2'] ?? '');
    if (strlen($p1) < 10) {
        $err = 'Kata sandi minimal 10 karakter.';
    } elseif ($p1 !== $p2) {
        $err = 'Kedua isian kata sandi tidak sama.';
    } else {
        simpan(DATA . '/auth.php', ['hash' => password_hash($p1, PASSWORD_DEFAULT), 'sv' => 1]);
        masukkan(1);
        catat('Kata sandi admin dibuat');
        header('Location: ./');
        exit;
    }
}

if ($act === 'login' && !$belumAtur) {
    $g = muat(DATA . '/gagal.php');
    if (($g['n'] ?? 0) >= 5 && time() - ($g['t'] ?? 0) < 300) {
        $err = 'Terlalu banyak percobaan. Tunggu 5 menit.';
    } elseif (password_verify((string)($_POST['p'] ?? ''), (string)$auth['hash'])) {
        simpan(DATA . '/gagal.php', []);
        masukkan($sv);
        catat('Masuk');
        header('Location: ./');
        exit;
    } else {
        simpan(DATA . '/gagal.php', ['n' => ($g['n'] ?? 0) + 1, 't' => time()]);
        sleep(1);
        $err = 'Kata sandi salah.';
    }
}

$masuk = !empty($_SESSION['ok']);

if ($act === 'keluar') {
    $_SESSION = [];
    session_destroy();
    header('Location: ./');
    exit;
}

if ($act === 'upload') {
    if (!$masuk) {
        json_out(['err' => 'Belum masuk.'], 401);
    }
    $dl = baca_daftar();
    if (count($dl) >= MAXN) {
        json_out(['err' => 'Sudah ' . MAXN . ' video. Hapus salah satu dulu.'], 400);
    }
    $v = $_FILES['video'] ?? null;
    $c = $_FILES['sampul'] ?? null;
    if (!$v || $v['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($v['tmp_name'])) {
        json_out(['err' => 'Video gagal terunggah (kode ' . ($v['error'] ?? '-') . ').'], 400);
    }
    if ($v['size'] > MAXB) {
        json_out(['err' => 'Video lebih dari 19 MB.'], 400);
    }
    $fh = fopen($v['tmp_name'], 'rb');
    $head = (string)fread($fh, 12);
    fclose($fh);
    if (strlen($head) < 12 || substr($head, 4, 4) !== 'ftyp') {
        json_out(['err' => 'Berkas bukan MP4.'], 400);
    }
    if (!$c || $c['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($c['tmp_name']) || $c['size'] > 1500000) {
        json_out(['err' => 'Gambar sampul tidak valid.'], 400);
    }
    $gi = @getimagesize($c['tmp_name']);
    if (!$gi || $gi[2] !== IMAGETYPE_JPEG) {
        json_out(['err' => 'Sampul harus JPEG.'], 400);
    }
    $dur = (float)($_POST['durasi'] ?? 0);
    if ($dur <= 0 || $dur > MAXD) {
        json_out(['err' => 'Durasi harus 30 detik atau kurang.'], 400);
    }
    $lebar = max(1, min(10000, (int)($_POST['lebar'] ?? 0)));
    $tinggi = max(1, min(10000, (int)($_POST['tinggi'] ?? 0)));
    $id = bin2hex(random_bytes(8));
    $nv = 'v_' . $id . '.mp4';
    $nc = 'c_' . $id . '.jpg';
    if (!move_uploaded_file($v['tmp_name'], VID . '/' . $nv) || !move_uploaded_file($c['tmp_name'], VID . '/' . $nc)) {
        json_out(['err' => 'Gagal menyimpan berkas di server.'], 500);
    }
    $judul = bersih_judul((string)($_POST['judul'] ?? ''));
    array_unshift($dl, [
        'id' => $id,
        'aktif' => true,
        'judul' => $judul,
        'deskripsi' => bersih_desk((string)($_POST['deskripsi'] ?? '')),
        'waktu' => bersih_waktu((string)($_POST['waktu'] ?? '')),
        'video' => $nv,
        'sampul' => $nc,
        'lebar' => $lebar,
        'tinggi' => $tinggi,
        'durasi' => round($dur, 1),
        'ukuran' => (int)filesize(VID . '/' . $nv),
        'diperbarui' => date('Y-m-d H:i'),
    ]);
    tulis_daftar($dl);
    catat('Video ditambah: ' . ($judul !== '' ? $judul : 'tanpa judul'));
    $_SESSION['flash'] = 'Video terunggah.';
    json_out(['ok' => true]);
}

if ($masuk && in_array($act, ['toggle', 'info', 'hapus', 'naik', 'turun'], true)) {
    $id = (string)($_POST['id'] ?? '');
    $dl = baca_daftar();
    $i = preg_match('/^[a-f0-9]{16}$/', $id) ? cari($dl, $id) : -1;
    if ($i >= 0) {
        $nm = (($dl[$i]['judul'] ?? '') !== '') ? $dl[$i]['judul'] : 'tanpa judul';
        if ($act === 'toggle') {
            $dl[$i]['aktif'] = empty($dl[$i]['aktif']);
            catat('Video ' . ($dl[$i]['aktif'] ? 'ditampilkan' : 'disembunyikan') . ': ' . $nm);
        } elseif ($act === 'info') {
            $dl[$i]['judul'] = bersih_judul((string)($_POST['judul'] ?? ''));
            $dl[$i]['deskripsi'] = bersih_desk((string)($_POST['deskripsi'] ?? ''));
            $dl[$i]['waktu'] = bersih_waktu((string)($_POST['waktu'] ?? ''));
            catat('Info video diubah: ' . $nm);
            $_SESSION['flash'] = 'Tersimpan.';
        } elseif ($act === 'hapus') {
            hapus_berkas($dl[$i]);
            array_splice($dl, $i, 1);
            catat('Video dihapus: ' . $nm);
            $_SESSION['flash'] = 'Video dihapus.';
        } elseif ($act === 'naik' && $i > 0) {
            [$dl[$i - 1], $dl[$i]] = [$dl[$i], $dl[$i - 1]];
        } elseif ($act === 'turun' && $i < count($dl) - 1) {
            [$dl[$i + 1], $dl[$i]] = [$dl[$i], $dl[$i + 1]];
        }
        tulis_daftar($dl);
    }
    header('Location: ./?m=video');
    exit;
}

if ($act === 'media') {
    if (!$masuk) {
        json_out(['err' => 'Belum masuk.'], 401);
    }
    $r = art_simpan_gambar($_FILES['gambar'] ?? []);
    json_out($r, isset($r['err']) ? 400 : 200);
}

$editPost = null;
if ($masuk && $act === 'artikel_simpan') {
    $auto = !empty($_POST['auto']);
    $id = (string)($_POST['id'] ?? '');
    $lama = $id !== '' ? art_baca($id) : null;
    $d = art_dari_post($auto);
    if ($auto) {
        if ($lama && ($lama['status'] ?? '') !== 'draf') {
            json_out(['err' => 'Artikel sudah terbit, simpan manual.'], 400);
        }
        if ($d['judul'] === '' && trim(strip_tags($d['isi'])) === '') {
            json_out(['ok' => false]);
        }
        if ($d['judul'] === '') {
            $d['judul'] = 'Tanpa judul';
        }
    }
    $galat = '';
    if (!$auto && $d['judul'] === '') {
        $galat = 'Judul wajib diisi.';
    } elseif (!$auto && $d['status'] === 'terbit' && trim(strip_tags($d['isi'])) === '' && strpos($d['isi'], '<img') === false) {
        $galat = 'Isi artikel masih kosong.';
    }
    if ($galat !== '') {
        $err = $galat;
        $editPost = $d + ['id' => $id, 'slug' => (string)($lama['slug'] ?? '')];
        $_GET['m'] = 'artikel';
        $_GET['e'] = $id !== '' ? $id : 'baru';
    } else {
        $baru = $lama === null;
        $a = $lama ?? ['id' => bin2hex(random_bytes(8)), 'dibuat' => date('Y-m-d H:i'), 'slug_kunci' => false];
        $a = array_merge($a, $d);
        if (empty($a['slug_kunci'])) {
            $a['slug'] = art_slug($a['judul'], $a['id']);
        }
        if ($a['status'] === 'terbit') {
            $a['slug_kunci'] = true;
        }
        $a['diperbarui'] = date('Y-m-d H:i');
        art_tulis($a);
        if ($auto) {
            json_out(['ok' => true, 'id' => $a['id'], 't' => date('H:i')]);
        }
        catat(($baru ? 'Artikel dibuat: ' : 'Artikel disimpan: ') . $a['judul'] . ' (' . art_status($a) . ')');
        $_SESSION['flash'] = $a['status'] === 'terbit' ? (art_publik($a) ? 'Artikel terbit.' : 'Artikel dijadwalkan terbit.') : 'Draf tersimpan.';
        header('Location: ./?m=artikel');
        exit;
    }
}

if ($masuk && $act === 'artikel_hapus') {
    $a = art_baca((string)($_POST['id'] ?? ''));
    if ($a) {
        art_hapus($a['id']);
        catat('Artikel dihapus: ' . $a['judul']);
        $_SESSION['flash'] = 'Artikel dihapus.';
    }
    header('Location: ./?m=artikel');
    exit;
}

function hapus_foto_profil(string $u): void
{
    if (preg_match('#^/admin/media/m_([a-f0-9]{16})\.jpg$#', $u, $mm)) {
        @unlink(MEDIA_DIR . '/m_' . $mm[1] . '.jpg');
        @unlink(MEDIA_DIR . '/s_' . $mm[1] . '.jpg');
    }
}

if ($masuk && $act === 'profil_simpan') {
    $tk = fn(string $k, int $n) => mb_substr(trim(strip_tags((string)($_POST[$k] ?? ''))), 0, $n);
    $nama = $tk('nama', 60);
    if ($nama === '') {
        $_SESSION['flash_err'] = 'Nama tidak boleh kosong.';
    } else {
        $p = art_profil();
        $p['nama'] = $nama;
        $peran = $tk('peran', 30);
        $p['peran'] = $peran !== '' ? $peran : 'Admin';
        $p['jabatan'] = $tk('jabatan', 80);
        simpan(DATA . '/profil.php', $p);
        catat('Profil diperbarui');
        $_SESSION['flash'] = 'Profil tersimpan.';
    }
    header('Location: ./?m=profil');
    exit;
}

if ($masuk && $act === 'profil_foto') {
    $p = art_profil();
    if (!empty($_POST['hapus'])) {
        hapus_foto_profil((string)$p['foto']);
        $p['foto'] = '';
        simpan(DATA . '/profil.php', $p);
        catat('Foto profil dihapus');
        json_out(['ok' => true, 'url' => '']);
    }
    $r = art_simpan_gambar((array)($_FILES['f'] ?? []));
    if (empty($r['ok'])) {
        json_out(['err' => (string)($r['err'] ?? 'Gagal menyimpan foto.')], 400);
    }
    hapus_foto_profil((string)$p['foto']);
    $p['foto'] = (string)$r['url'];
    simpan(DATA . '/profil.php', $p);
    catat('Foto profil diganti');
    json_out(['ok' => true, 'url' => $p['foto']]);
}

if ($masuk && $act === 'sandi') {
    $p0 = (string)($_POST['p0'] ?? '');
    $p1 = (string)($_POST['p1'] ?? '');
    $p2 = (string)($_POST['p2'] ?? '');
    if (!password_verify($p0, (string)$auth['hash'])) {
        $_SESSION['flash_err'] = 'Kata sandi saat ini salah.';
    } elseif (strlen($p1) < 10) {
        $_SESSION['flash_err'] = 'Kata sandi baru minimal 10 karakter.';
    } elseif ($p1 !== $p2) {
        $_SESSION['flash_err'] = 'Kedua isian kata sandi baru tidak sama.';
    } else {
        $sv++;
        simpan(DATA . '/auth.php', ['hash' => password_hash($p1, PASSWORD_DEFAULT), 'sv' => $sv]);
        $_SESSION['sv'] = $sv;
        catat('Kata sandi diganti, semua perangkat lain keluar');
        $_SESSION['flash'] = 'Kata sandi diganti. Perangkat lain harus masuk ulang.';
    }
    header('Location: ./?m=pengaturan');
    exit;
}

if ($masuk && $act === 'keluar_semua') {
    $sv++;
    $auth['sv'] = $sv;
    simpan(DATA . '/auth.php', $auth);
    $_SESSION['sv'] = $sv;
    catat('Keluar dari semua perangkat lain');
    $_SESSION['flash'] = 'Semua perangkat lain sudah keluar.';
    header('Location: ./?m=pengaturan');
    exit;
}

if ($masuk && $act === 'ekspor') {
    $paket = [
        'dibuat' => date('c'),
        'video' => baca_daftar(),
        'artikel' => art_semua(),
        'riwayat' => muat(DATA . '/log.php'),
    ];
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="cadangan-admin-' . date('Ymd-His') . '.json"');
    echo json_encode($paket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$m = (string)($_GET['m'] ?? 'ringkasan');
if (!in_array($m, ['ringkasan', 'artikel', 'pratinjau', 'video', 'profil', 'pengaturan'], true)) {
    $m = 'ringkasan';
}
$flash = (string)($_SESSION['flash'] ?? '');
$flashErr = (string)($_SESSION['flash_err'] ?? '');
unset($_SESSION['flash'], $_SESSION['flash_err']);
if ($flashErr !== '') {
    $err = $flashErr;
}

$daftar = baca_daftar();
$total = 0;
$aktifN = 0;
foreach ($daftar as $it) {
    $total += (int)($it['ukuran'] ?? 0);
    if (!empty($it['aktif'])) {
        $aktifN++;
    }
}
$judulM = ['ringkasan' => 'Ringkasan', 'artikel' => 'Artikel', 'pratinjau' => 'Pratinjau artikel', 'video' => 'Video beranda', 'profil' => 'Profil', 'pengaturan' => 'Pengaturan'];
$menu = [['ringkasan', 'home', 'Ringkasan'], ['artikel', 'artikel', 'Artikel'], ['video', 'video', 'Video'], ['profil', 'user', 'Profil'], ['pengaturan', 'atur', 'Pengaturan']];
$menuAktif = $m === 'pratinjau' ? 'artikel' : $m;
$semuaArt = art_semua();
$nTerbit = 0;
$nDraf = 0;
$nJadwal = 0;
foreach ($semuaArt as $x) {
    $sx = art_status($x);
    if ($sx === 'terbit') {
        $nTerbit++;
    } elseif ($sx === 'draf') {
        $nDraf++;
    } else {
        $nJadwal++;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin<?= $masuk ? ' · ' . h($judulM[$m]) : '' ?></title>
<style>
*{box-sizing:border-box}
:root{--p:#0B2545;--s:#071930;--t:#1f2937;--mu:#64748b;--ln:#e5e9f0;--bg:#f4f6fa;--sh:0 1px 2px rgba(15,23,42,.04),0 4px 14px rgba(15,23,42,.05);--r:12px}
html{-webkit-text-size-adjust:100%}
body{margin:0;font:14px/1.55 Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--t)}
.ik{flex:none;display:block}
a{color:inherit}
[hidden]{display:none!important}
label{font-size:13px;color:var(--mu);font-weight:500}
input[type=text],input[type=search],input[type=password],input[type=file],input[type=datetime-local],select,textarea{width:100%;padding:10px 12px;border:1px solid #d5dbe5;border-radius:10px;font:inherit;margin:4px 0 12px;background:#fff;color:var(--t);transition:border-color .15s,box-shadow .15s}
input:focus,textarea:focus,select:focus{outline:0;border-color:#5b7db3;box-shadow:0 0 0 3px rgba(11,37,69,.12)}
button{font:inherit}
.bt{display:inline-flex;align-items:center;gap:7px;background:var(--p);color:#fff;border:1px solid var(--p);border-radius:10px;padding:9px 16px;font-weight:600;cursor:pointer;text-decoration:none;transition:transform .12s,box-shadow .15s,background .15s}
.bt:hover{background:#12335f;box-shadow:0 4px 12px rgba(11,37,69,.22)}
.bt:active{transform:scale(.97)}
.bt:disabled{opacity:.45;cursor:default;box-shadow:none;transform:none}
.bt.o{background:#fff;color:var(--p);border-color:#d5dbe5}
.bt.o:hover{background:#f8fafc;box-shadow:none}
.bt.x{background:#fff;color:#b91c1c;border-color:#fecaca}
.bt.x:hover{background:#fef2f2;box-shadow:none}
.bt.w{background:#fff;color:var(--p);border-color:#fff}
.bt.w:hover{background:#eef3fb}
.bt.g{background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.28);color:#fff}
.bt.g:hover{background:rgba(255,255,255,.2)}
.ib{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;border:1px solid var(--ln);background:#fff;color:var(--p);cursor:pointer;padding:0;text-decoration:none;transition:background .15s,transform .12s}
.ib.x{color:#b91c1c}
.ib:hover{background:#f1f5f9}
.ib:active{transform:scale(.94)}
.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:10px 14px;margin-bottom:14px}
.f{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:10px;padding:10px 14px;margin-bottom:14px}
.c{background:#fff;border:1px solid var(--ln);border-radius:var(--r);padding:16px;margin-bottom:14px;box-shadow:var(--sh)}
.c h2{margin:0 0 10px;font-size:15px;font-weight:700}
.m{color:var(--mu);font-size:13px;margin:0 0 8px}
.row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.sp{margin-left:auto}
.av{display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:#1d4478;color:#fff;font-weight:700;overflow:hidden;flex:none;line-height:1}
.av img{width:100%;height:100%;object-fit:cover;display:block}
.av.s32{width:32px;height:32px;font-size:12px}
.av.s44{width:44px;height:44px;font-size:15px}
.av.s56{width:56px;height:56px;font-size:19px}
.av.s96{width:96px;height:96px;font-size:30px}
.tx{min-width:0}
.side{position:fixed;left:0;top:0;bottom:0;width:248px;background:linear-gradient(180deg,#0B2545 0%,#071930 100%);color:#fff;display:flex;flex-direction:column;padding:18px 14px;z-index:6}
.pf{display:flex;align-items:center;gap:12px;padding:10px;border-radius:12px;background:rgba(255,255,255,.07);margin-bottom:18px;text-decoration:none;color:#fff;transition:background .15s}
.pf:hover{background:rgba(255,255,255,.12)}
.pf b{display:block;font-size:14px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pf small{display:flex;align-items:center;gap:6px;color:#9fb3cf;font-size:12px}
.pf small:before{content:"";width:7px;height:7px;border-radius:50%;background:#34d399}
.side nav{display:flex;flex-direction:column;gap:3px;flex:1}
.side nav a,.sb2 a,.sb2 button{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:10px;color:#b6c4da;text-decoration:none;background:none;border:0;cursor:pointer;text-align:left;width:100%;position:relative;transition:background .15s,color .15s}
.side nav a:hover,.sb2 a:hover,.sb2 button:hover{background:rgba(255,255,255,.08);color:#fff}
.side nav a.on{background:rgba(255,255,255,.12);color:#fff;font-weight:600}
.side nav a.on:before{content:"";position:absolute;left:-14px;top:9px;bottom:9px;width:4px;border-radius:0 4px 4px 0;background:#fff}
.sb2{border-top:1px solid rgba(255,255,255,.1);padding-top:10px;display:flex;flex-direction:column;gap:2px}
.sb2 form{margin:0}
.top{margin-left:248px;position:sticky;top:0;z-index:5;display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.9);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);border-bottom:1px solid var(--ln);padding:10px 28px;min-height:62px}
.top h1{margin:0;font-size:18px;font-weight:700}
.um{position:relative}
.um>button{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 4px;border:1px solid var(--ln);background:#fff;border-radius:99px;cursor:pointer;color:var(--t);transition:background .15s}
.um>button:hover{background:#f8fafc}
.um .un{max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;font-size:13px}
.um .ik{transition:transform .2s}
.um.open>button .ik{transform:rotate(180deg)}
.dd{position:absolute;right:0;top:calc(100% + 8px);width:264px;background:#fff;border:1px solid var(--ln);border-radius:14px;box-shadow:0 14px 36px rgba(15,23,42,.16);padding:8px;opacity:0;transform:translateY(-6px) scale(.98);transform-origin:top right;pointer-events:none;transition:opacity .16s,transform .16s}
.um.open .dd{opacity:1;transform:none;pointer-events:auto}
.dh{display:flex;align-items:center;gap:10px;padding:8px 8px 12px;border-bottom:1px solid var(--ln);margin-bottom:6px}
.dh b{display:block;font-size:14px;line-height:1.3}
.dh small{color:var(--mu);font-size:12px}
.dd a,.dd button{display:flex;align-items:center;gap:10px;width:100%;padding:10px;border-radius:8px;border:0;background:none;color:var(--t);text-decoration:none;cursor:pointer;text-align:left}
.dd a:hover,.dd button:hover{background:#f1f5f9}
.dd form{margin:0}
main{margin-left:248px;padding:24px 28px 40px;max-width:1100px;animation:fu .28s ease}
@keyframes fu{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
@keyframes ti{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
.ts{position:fixed;right:24px;top:74px;z-index:20;display:flex;align-items:center;gap:8px;background:#0f2a4d;color:#fff;padding:11px 16px;border-radius:12px;box-shadow:0 10px 30px rgba(7,25,48,.3);animation:ti .3s ease;transition:opacity .35s,transform .35s}
.ts.h{opacity:0;transform:translateY(-8px)}
.bar{display:none}
.hero{display:flex;align-items:center;gap:16px;flex-wrap:wrap;background:linear-gradient(135deg,#0B2545 0%,#17407a 100%);color:#fff;border-radius:16px;padding:20px;margin-bottom:16px;box-shadow:0 10px 28px rgba(11,37,69,.18)}
.hero .av{border:2px solid rgba(255,255,255,.4)}
.hero h2{margin:0;font-size:20px;line-height:1.3}
.hero p{margin:2px 0 0;color:#b6c4da;font-size:13px}
.kt{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}
.k{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--ln);border-radius:var(--r);padding:14px;box-shadow:var(--sh);transition:transform .15s,box-shadow .15s}
.k:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,23,42,.08)}
.k i{width:42px;height:42px;border-radius:11px;background:#eef3fb;color:var(--p);display:flex;align-items:center;justify-content:center;flex:none}
.k b{display:block;font-size:21px;color:var(--p);line-height:1.2}
.k span{font-size:12px;color:var(--mu)}
.g3{display:grid;grid-template-columns:1.5fr 1fr;gap:14px;align-items:start}
.v{display:flex;gap:12px;align-items:center;padding:10px 0;border-top:1px solid var(--ln)}
.v:first-of-type{border-top:0}
.v img,.v .nt{width:64px;height:64px;object-fit:cover;border-radius:10px;background:#0f172a;flex:none}
.v .bd{min-width:0;flex:1}
.v .bd b{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.v .bd small{color:var(--mu);font-size:12px}
.tg{display:inline-block;font-size:11px;padding:1px 8px;border-radius:99px;border:1px solid var(--ln);color:var(--mu);margin-right:6px}
.tg.on{background:#eff6ff;border-color:#bfdbfe;color:var(--p)}
.ac{display:flex;gap:6px;flex-wrap:wrap}
details.ed>summary{list-style:none;cursor:pointer}
details.ed>summary::-webkit-details-marker{display:none}
details.ed{margin:0}
.edf{padding:10px 0 4px;border-top:1px dashed var(--ln);margin-top:8px}
.lg{list-style:none;margin:0;padding:0}
.lg li{display:flex;gap:10px;padding:8px 0;border-top:1px solid var(--ln);font-size:13px}
.lg li:first-child{border-top:0}
.lg time{color:var(--mu);white-space:nowrap;font-size:12px;flex:none;width:112px}
progress{width:100%;height:8px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media (max-width:520px){.g2{grid-template-columns:1fr}}
.tb{display:flex;gap:4px;overflow-x:auto;padding:0 0 8px;margin:0 -2px}
.tb button{flex:none;width:38px;height:38px;border:1px solid var(--ln);background:#fff;color:var(--p);border-radius:8px;display:flex;align-items:center;justify-content:center;padding:0;cursor:pointer}
.tb button:active{background:#e2e8f0}
#ed{min-height:280px;border:1px solid #d5dbe5;border-radius:10px;padding:12px;background:#fff;outline:none}
#ed:focus{border-color:#5b7db3;box-shadow:0 0 0 3px rgba(11,37,69,.12)}
.fp{display:flex;gap:10px;align-items:center;margin-bottom:8px}
.fp img{width:120px;height:80px;object-fit:cover;border-radius:10px;background:#e2e8f0}
.st2{font-size:12px;color:var(--mu)}
.fl{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.fl input[type=search]{flex:1 1 180px;margin:0}
.fl select{flex:0 1 150px;width:auto;margin:0}
.pfc{display:flex;gap:18px;align-items:center;flex-wrap:wrap}
.cam{position:relative}
.cam .cb{position:absolute;right:-2px;bottom:-2px;width:34px;height:34px;border-radius:50%;background:var(--p);color:#fff;border:3px solid #fff;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;transition:transform .15s}
.cam .cb:hover{transform:scale(1.08)}
.pv{display:flex;gap:12px;align-items:center;padding:14px;border-radius:12px;background:var(--p);color:#fff}
.pv b{display:block;font-size:14px}
.pv small{color:#cbd5e1;font-size:12px}
canvas{display:block;max-width:100%;max-height:280px;margin:0 auto 10px;border-radius:8px;background:#000}
input[type=range]{width:100%;margin:0 0 10px}
.cen{max-width:400px;margin:9vh auto 0;padding:0 16px}
.cen .lg0{display:flex;flex-direction:column;align-items:center;gap:10px;margin-bottom:18px}
.cen .br{font-weight:700;font-size:18px;color:var(--p)}
.cen .c{padding:22px;border-radius:16px}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
@media (max-width:767px){
.side{display:none}
.top{margin-left:0;padding:10px 14px}
.top h1{font-size:16px}
.um .un{display:none}
.um>button{padding:3px}
.um>button .ik{display:none}
main{margin-left:0;padding:16px 14px 104px}
.kt{grid-template-columns:repeat(2,1fr)}
.g3{grid-template-columns:1fr}
.ts{left:12px;right:12px;top:70px}
.bar{display:flex;position:fixed;left:10px;right:10px;bottom:calc(10px + env(safe-area-inset-bottom));background:#0B2545;border-radius:18px;padding:6px;box-shadow:0 10px 28px rgba(7,25,48,.4);z-index:6;gap:2px}
.bar a{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;color:#9fb3cf;text-decoration:none;font-size:11px;padding:7px 0;border-radius:13px;transition:background .15s,color .15s}
.bar a.on{color:#fff;background:rgba(255,255,255,.14)}
}
</style>
</head>
<body>
<?php if (!$masuk): ?>
<div class="cen">
<div class="lg0"><?= art_avatar($prof, 's56') ?><div class="br">Admin</div></div>
<?php if ($err !== ''): ?><div class="e"><?= h($err) ?></div><?php endif; ?>
<?php if ($belumAtur): ?>
<div class="c">
<h2>Pengaturan awal</h2>
<p class="m">Buat kata sandi admin (minimal 10 karakter). Halaman ini tertutup sendiri setelah kata sandi tersimpan.</p>
<form method="post" autocomplete="off">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="setup">
<label>Kata sandi</label>
<input type="password" name="p1" minlength="10" required>
<label>Ulangi kata sandi</label>
<input type="password" name="p2" minlength="10" required>
<button class="bt" type="submit"><?= ikon('simpan', 18) ?>Simpan</button>
</form>
</div>
<?php else: ?>
<div class="c">
<h2>Masuk</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="login">
<label>Kata sandi</label>
<input type="password" name="p" required autofocus>
<button class="bt" type="submit"><?= ikon('kunci', 18) ?>Masuk</button>
</form>
</div>
<?php endif; ?>
</div>

<?php else: ?>
<aside class="side">
<a class="pf" href="./?m=profil"><?= art_avatar($prof, 's44') ?><span class="tx"><b><?= h($prof['nama']) ?></b><small><?= h($prof['peran']) ?></small></span></a>
<nav>
<?php foreach ($menu as [$k, $ik, $tx]): ?>
<a href="./?m=<?= h($k) ?>" class="<?= $menuAktif === $k ? 'on' : '' ?>"><?= ikon($ik) ?><?= h($tx) ?></a>
<?php endforeach; ?>
</nav>
<div class="sb2">
<a href="/" target="_blank" rel="noopener"><?= ikon('lihat') ?>Lihat situs</a>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="keluar"><button type="submit"><?= ikon('keluar') ?>Keluar</button></form>
</div>
</aside>
<header class="top">
<h1><?= h($judulM[$m]) ?></h1>
<a class="ib sp" href="/" target="_blank" rel="noopener" title="Lihat situs" aria-label="Lihat situs"><?= ikon('lihat', 18) ?></a>
<div class="um" id="um">
<button type="button" id="umb" aria-haspopup="true" aria-expanded="false" aria-label="Menu akun"><?= art_avatar($prof, 's32') ?><span class="un"><?= h($prof['nama']) ?></span><?= ikon('chev', 16) ?></button>
<div class="dd" role="menu">
<div class="dh"><?= art_avatar($prof, 's44') ?><div class="tx"><b><?= h($prof['nama']) ?></b><small><?= h($prof['peran']) ?></small></div></div>
<a href="./?m=profil" role="menuitem"><?= ikon('user', 18) ?>Profil</a>
<a href="./?m=pengaturan" role="menuitem"><?= ikon('atur', 18) ?>Pengaturan</a>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="keluar"><button type="submit" role="menuitem"><?= ikon('keluar', 18) ?>Keluar</button></form>
</div>
</div>
</header>
<nav class="bar">
<?php foreach ($menu as [$k, $ik, $tx]): ?>
<a href="./?m=<?= h($k) ?>" class="<?= $menuAktif === $k ? 'on' : '' ?>"><?= ikon($ik, 22) ?><?= h($tx) ?></a>
<?php endforeach; ?>
</nav>
<main>
<?php if ($err !== ''): ?><div class="e"><?= h($err) ?></div><?php endif; ?>
<?php if ($flash !== ''): ?><div class="ts" id="ts" role="status"><?= ikon('simpan', 18) ?><?= h($flash) ?></div><?php endif; ?>

<?php if ($m === 'ringkasan'): ?>
<?php
$log = muat(DATA . '/log.php');
$hr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bl = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$jm = (int)date('G');
$sapa = $jm < 11 ? 'Selamat pagi' : ($jm < 15 ? 'Selamat siang' : ($jm < 18 ? 'Selamat sore' : 'Selamat malam'));
$hariIni = $hr[(int)date('w')] . ', ' . date('j') . ' ' . $bl[(int)date('n') - 1] . ' ' . date('Y');
$namaDepan = (preg_split('/[\s,]+/u', trim($prof['nama'])) ?: [''])[0];
$artBaru = array_slice($semuaArt, 0, 5);
?>
<div class="hero">
<?= art_avatar($prof, 's56') ?>
<div class="tx"><h2><?= h($sapa) ?>, <?= h($namaDepan) ?></h2><p><?= h($hariIni) ?></p></div>
<div class="sp ac"><a class="bt w" href="./?m=artikel&amp;e=baru"><?= ikon('tambah', 18) ?>Artikel baru</a><a class="bt g" href="./?m=video"><?= ikon('unggah', 18) ?>Video</a></div>
</div>
<div class="kt">
<div class="k"><i><?= ikon('artikel', 20) ?></i><div><b><?= $nTerbit ?></b><span>Artikel terbit</span></div></div>
<div class="k"><i><?= ikon('ubah', 20) ?></i><div><b><?= $nDraf + $nJadwal ?></b><span>Draf dan terjadwal</span></div></div>
<div class="k"><i><?= ikon('mata', 20) ?></i><div><b><?= $aktifN ?></b><span>Video tampil di beranda</span></div></div>
<div class="k"><i><?= ikon('video', 20) ?></i><div><b><?= count($daftar) ?></b><span>Video dari <?= MAXN ?></span></div></div>
<div class="k"><i><?= ikon('mata-tutup', 20) ?></i><div><b><?= count($daftar) - $aktifN ?></b><span>Video disembunyikan</span></div></div>
<div class="k"><i><?= ikon('unggah', 20) ?></i><div><b><?= h(mb_fmt($total)) ?> MB</b><span>Ruang video</span></div></div>
</div>
<div class="g3">
<div class="c">
<div class="row" style="margin-bottom:4px"><h2 style="margin:0">Artikel terbaru</h2><a class="m sp" style="margin:0" href="./?m=artikel">Semua artikel</a></div>
<?php if (!$artBaru): ?>
<p class="m" style="margin-top:8px">Belum ada artikel.</p>
<a class="bt" href="./?m=artikel&amp;e=baru"><?= ikon('tambah', 18) ?>Tulis artikel</a>
<?php endif; ?>
<?php foreach ($artBaru as $x): $sx = art_status($x); $th = art_thumb((string)($x['foto'] ?? '')); ?>
<a class="v" href="./?m=artikel&amp;e=<?= h($x['id']) ?>" style="text-decoration:none;color:inherit">
<?php if ($th !== ''): ?><img src="<?= h($th) ?>" alt="" loading="lazy" width="64" height="64"><?php else: ?><span class="nt" style="display:flex;align-items:center;justify-content:center;color:#94a3b8"><?= ikon('artikel', 24) ?></span><?php endif; ?>
<div class="bd"><b><?= h($x['judul']) ?></b><small><span class="tg<?= $sx === 'terbit' ? ' on' : '' ?>"><?= h(ucfirst($sx)) ?></span><?= h(art_tgl((string)$x['waktu'])) ?></small></div>
</a>
<?php endforeach; ?>
</div>
<div class="c">
<h2>Aktivitas terakhir</h2>
<?php if (!$log): ?>
<p class="m">Belum ada aktivitas.</p>
<?php else: ?>
<ul class="lg">
<?php foreach (array_slice($log, 0, 8) as $l): ?>
<li><time><?= h(tgl((string)$l['w'])) ?></time><span><?= h($l['t']) ?></span></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>
</div>

<?php elseif ($m === 'profil'): ?>
<div class="c">
<h2>Foto profil</h2>
<div class="pfc">
<div class="cam"><?= art_avatar($prof, 's96') ?><button type="button" class="cb" id="pfb" title="Ganti foto" aria-label="Ganti foto"><?= ikon('kamera', 18) ?></button></div>
<div class="tx">
<div class="row"><button type="button" class="bt o" id="pfg"><?= ikon('unggah', 18) ?>Pilih foto</button><button type="button" class="bt x" id="pfh"<?= $prof['foto'] === '' ? ' hidden' : '' ?>><?= ikon('hapus', 18) ?>Hapus foto</button></div>
<p class="m" id="pfs" style="margin:8px 0 0" aria-live="polite">Foto dipotong persegi otomatis.</p>
</div>
</div>
<input type="file" id="pff" accept="image/*" hidden>
</div>
<div class="c">
<h2>Data profil</h2>
<form method="post" autocomplete="off">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="profil_simpan">
<div class="g2">
<div><label for="pn1">Nama</label><input type="text" id="pn1" name="nama" maxlength="60" required value="<?= h($prof['nama']) ?>"></div>
<div><label for="pr1">Label di panel</label><input type="text" id="pr1" name="peran" maxlength="30" value="<?= h($prof['peran']) ?>"></div>
</div>
<label for="pj1">Jabatan (tampil di halaman artikel)</label>
<input type="text" id="pj1" name="jabatan" maxlength="80" value="<?= h($prof['jabatan']) ?>">
<button class="bt" type="submit"><?= ikon('simpan', 18) ?>Simpan</button>
</form>
</div>
<div class="c">
<h2>Tampilan di artikel</h2>
<div class="pv"><?= art_avatar($prof, 's44') ?><div class="tx"><b id="pvn"><?= h($prof['nama']) ?></b><small id="pvj"><?= h($prof['jabatan']) ?></small></div></div>
</div>
<script>
(function () {
  var CSRF = <?= json_encode($csrf) ?>;
  var f = document.getElementById('pff'), st = document.getElementById('pfs'), hp = document.getElementById('pfh');
  function say(t) { st.textContent = t; }
  function setFoto(url) {
    var av = document.querySelectorAll('.av');
    for (var i = 0; i < av.length; i++) {
      av[i].textContent = '';
      if (url) { var im = document.createElement('img'); im.alt = ''; im.src = url; av[i].appendChild(im); }
      else { av[i].textContent = av[i].getAttribute('data-i'); }
    }
    hp.hidden = !url;
  }
  function kirim(fd, cb) {
    var x = new XMLHttpRequest();
    x.open('POST', 'index.php');
    x.onload = function () {
      var r = {};
      try { r = JSON.parse(x.responseText); } catch (e) { r = { err: 'Balasan server tidak dikenali (kode ' + x.status + ').' }; }
      cb(r);
    };
    x.onerror = function () { cb({ err: 'Koneksi terputus.' }); };
    x.send(fd);
  }
  ['pfb', 'pfg'].forEach(function (id) { document.getElementById(id).addEventListener('click', function () { f.click(); }); });
  f.addEventListener('change', function () {
    var file = f.files && f.files[0];
    if (!file) { return; }
    var url = URL.createObjectURL(file), im = new Image();
    im.onload = function () {
      var s = Math.min(im.naturalWidth, im.naturalHeight), o = Math.min(480, s);
      var c = document.createElement('canvas');
      c.width = o; c.height = o;
      c.getContext('2d').drawImage(im, (im.naturalWidth - s) / 2, (im.naturalHeight - s) / 2, s, s, 0, 0, o, o);
      URL.revokeObjectURL(url);
      c.toBlob(function (b) {
        if (!b) { say('Gambar tidak bisa diproses.'); return; }
        var fd = new FormData();
        fd.append('action', 'profil_foto'); fd.append('csrf', CSRF); fd.append('f', b, 'p.jpg');
        say('Mengunggah...');
        kirim(fd, function (r) { if (r.ok) { setFoto(r.url); say('Foto tersimpan.'); } else { say(r.err || 'Gagal.'); } });
      }, 'image/jpeg', 0.88);
    };
    im.onerror = function () { URL.revokeObjectURL(url); say('Berkas bukan gambar.'); };
    im.src = url;
    f.value = '';
  });
  hp.addEventListener('click', function () {
    if (!confirm('Hapus foto profil?')) { return; }
    var fd = new FormData();
    fd.append('action', 'profil_foto'); fd.append('csrf', CSRF); fd.append('hapus', '1');
    kirim(fd, function (r) { if (r.ok) { setFoto(''); say('Foto dihapus.'); } else { say(r.err || 'Gagal.'); } });
  });
  var n1 = document.getElementById('pn1'), j1 = document.getElementById('pj1');
  n1.addEventListener('input', function () { document.getElementById('pvn').textContent = n1.value; });
  j1.addEventListener('input', function () { document.getElementById('pvj').textContent = j1.value; });
})();
</script>

<?php elseif ($m === 'pratinjau'): ?>
<?php $pa = art_baca((string)($_GET['id'] ?? '')); ?>
<?php if (!$pa): ?>
<div class="c"><p class="m">Artikel tidak ditemukan.</p></div>
<?php else: ?>
<div class="row" style="margin-bottom:10px"><span class="tg on">Pratinjau, status: <?= h(art_status($pa)) ?></span><a class="bt o sp" href="./?m=artikel&amp;e=<?= h($pa['id']) ?>"><?= ikon('ubah', 18) ?>Kembali ke editor</a></div>
<div style="border:1px solid var(--ln);border-radius:8px;overflow:hidden;background:#fff">
<style><?= art_css() ?></style>
<?= art_halaman_baca($pa, [], SITE_URL . '/artikel/' . $pa['slug']) ?>
</div>
<?php endif; ?>

<?php elseif ($m === 'artikel' && isset($_GET['e'])): ?>
<?php
$eid = (string)$_GET['e'];
$ed = $editPost ?? ($eid !== 'baru' ? art_baca($eid) : null);
$ed = array_merge(['id' => '', 'judul' => '', 'ringkasan' => '', 'kategori' => '', 'tag' => [], 'penulis' => 'Agus Budiono, S.Pd.', 'foto' => '', 'foto_ket' => '', 'isi' => '', 'status' => 'draf', 'waktu' => date('Y-m-d\TH:i')], $ed ?? []);
$tersimpan = $ed['id'] !== '' ? art_baca($ed['id']) : null;
$awalDraf = !$tersimpan || ($tersimpan['status'] ?? 'draf') === 'draf';
$kats = [];
foreach ($semuaArt as $x) {
    if (($x['kategori'] ?? '') !== '') {
        $kats[$x['kategori']] = true;
    }
}
?>
<style><?= art_css() ?></style>
<form method="post" id="fa">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="artikel_simpan"><input type="hidden" name="id" id="aid" value="<?= h($ed['id']) ?>">
<div class="c">
<label for="jd">Judul</label>
<input type="text" id="jd" name="judul" maxlength="150" required value="<?= h($ed['judul']) ?>">
<label for="rg">Ringkasan</label>
<textarea id="rg" name="ringkasan" maxlength="300" rows="3"><?= h($ed['ringkasan']) ?></textarea>
<div class="g2">
<div><label for="kt">Kategori</label><input type="text" id="kt" name="kategori" maxlength="40" list="dkat" value="<?= h($ed['kategori']) ?>"><datalist id="dkat"><?php foreach (array_keys($kats) as $kk): ?><option value="<?= h($kk) ?>"><?php endforeach; ?></datalist></div>
<div><label for="pn">Penulis</label><input type="text" id="pn" name="penulis" maxlength="80" value="<?= h($ed['penulis']) ?>"></div>
</div>
<label for="tg">Tag (pisahkan dengan koma)</label>
<input type="text" id="tg" name="tag" value="<?= h(implode(', ', (array)$ed['tag'])) ?>">
</div>

<div class="c">
<h2>Foto utama</h2>
<input type="hidden" name="foto" id="foto" value="<?= h($ed['foto']) ?>">
<div class="fp" id="fpb"<?= $ed['foto'] === '' ? ' hidden' : '' ?>><img id="fpi" src="<?= h($ed['foto']) ?>" alt=""><button type="button" class="ib x" id="fpx" title="Hapus foto" aria-label="Hapus foto"><?= ikon('hapus', 18) ?></button></div>
<input type="file" id="ff" accept="image/*">
<label for="fk">Keterangan foto</label>
<input type="text" id="fk" name="foto_ket" maxlength="160" value="<?= h($ed['foto_ket']) ?>">
<p class="st2" id="fpm"></p>
</div>

<div class="c">
<h2>Isi</h2>
<div class="tb" id="tb">
<button type="button" data-c="b" title="Tebal" aria-label="Tebal"><?= ikon('tebal', 18) ?></button>
<button type="button" data-c="i" title="Miring" aria-label="Miring"><?= ikon('miring', 18) ?></button>
<button type="button" data-c="h2" title="Subjudul" aria-label="Subjudul"><?= ikon('h2', 18) ?></button>
<button type="button" data-c="h3" title="Subjudul kecil" aria-label="Subjudul kecil"><?= ikon('h3', 18) ?></button>
<button type="button" data-c="ul" title="Daftar" aria-label="Daftar"><?= ikon('daftar', 18) ?></button>
<button type="button" data-c="ol" title="Daftar bernomor" aria-label="Daftar bernomor"><?= ikon('nomor', 18) ?></button>
<button type="button" data-c="q" title="Kutipan" aria-label="Kutipan"><?= ikon('kutip', 18) ?></button>
<button type="button" data-c="a" title="Tautan" aria-label="Tautan"><?= ikon('tautan', 18) ?></button>
<button type="button" data-c="img" title="Gambar" aria-label="Gambar"><?= ikon('gambar', 18) ?></button>
<button type="button" data-c="x" title="Hapus format" aria-label="Hapus format"><?= ikon('bersih', 18) ?></button>
</div>
<div class="ar"><div id="ed" class="ar-isi" contenteditable="true"><?= $ed['isi'] ?></div></div>
<textarea name="isi" id="isi" hidden></textarea>
<input type="file" id="gf" accept="image/*" hidden>
<p class="st2" id="gm"></p>
</div>

<div class="c">
<h2>Penerbitan</h2>
<div class="g2">
<div><label for="stt">Status</label><select id="stt" name="status"><option value="draf"<?= $ed['status'] !== 'terbit' ? ' selected' : '' ?>>Draf</option><option value="terbit"<?= $ed['status'] === 'terbit' ? ' selected' : '' ?>>Terbit</option></select></div>
<div><label for="wk2">Waktu terbit</label><input type="datetime-local" id="wk2" name="waktu" value="<?= h($ed['waktu']) ?>"></div>
</div>
<p class="m">Waktu di masa depan membuat artikel terjadwal dan baru tampil saat waktunya tiba.</p>
<div class="row">
<button class="bt" type="submit"><?= ikon('simpan', 18) ?>Simpan</button>
<button class="bt o" type="button" id="pv"><?= ikon('lihat', 18) ?>Pratinjau</button>
<a class="bt o" href="./?m=artikel">Batal</a>
<span class="st2 sp" id="st"></span>
</div>
</div>
</form>

<script>
(function () {
  var CSRF = <?= json_encode($csrf) ?>;
  var AWAL_DRAF = <?= $awalDraf ? 'true' : 'false' ?>;
  var $ = function (i) { return document.getElementById(i); };
  var ed = $('ed'), isi = $('isi'), fm = $('fa'), st = $('st'), aid = $('aid'), stt = $('stt');
  var dirty = false, saving = false, rng = null, kirim = false;
  try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}

  function sync() { isi.value = ed.innerHTML; }
  function tandai() { dirty = true; }
  fm.addEventListener('input', tandai);
  ed.addEventListener('input', tandai);
  function simpanRange() {
    var s = window.getSelection();
    if (s && s.rangeCount && ed.contains(s.anchorNode)) { rng = s.getRangeAt(0); }
  }
  document.addEventListener('selectionchange', simpanRange);
  function pulih() {
    ed.focus();
    if (rng) { var s = window.getSelection(); s.removeAllRanges(); s.addRange(rng); }
  }
  function blok(tag) {
    var now = '';
    try { now = String(document.queryCommandValue('formatBlock')).toLowerCase().replace(/[<>]/g, ''); } catch (e) {}
    document.execCommand('formatBlock', false, '<' + (now === tag ? 'p' : tag) + '>');
  }

  $('tb').addEventListener('mousedown', function (e) { e.preventDefault(); });
  $('tb').addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b) return;
    var c = b.getAttribute('data-c');
    if (c === 'img') { simpanRange(); $('gf').click(); return; }
    pulih();
    if (c === 'b') document.execCommand('bold');
    else if (c === 'i') document.execCommand('italic');
    else if (c === 'h2') blok('h2');
    else if (c === 'h3') blok('h3');
    else if (c === 'ul') document.execCommand('insertUnorderedList');
    else if (c === 'ol') document.execCommand('insertOrderedList');
    else if (c === 'q') blok('blockquote');
    else if (c === 'a') {
      var u = window.prompt('Alamat tautan (https://...)', 'https://');
      if (u && /^(https?:\/\/|mailto:)/i.test(u)) { document.execCommand('createLink', false, u); }
    }
    else if (c === 'x') { document.execCommand('removeFormat'); document.execCommand('unlink'); document.execCommand('formatBlock', false, '<p>'); }
    tandai();
  });

  function kecilkan(file, maks, cb) {
    var url = URL.createObjectURL(file), im = new Image();
    im.onload = function () {
      var w = im.naturalWidth, h = im.naturalHeight, r = Math.min(1, maks / Math.max(w, h));
      var c = document.createElement('canvas');
      c.width = Math.max(1, Math.round(w * r)); c.height = Math.max(1, Math.round(h * r));
      var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(im, 0, 0, c.width, c.height);
      URL.revokeObjectURL(url);
      c.toBlob(function (b) { cb(b); }, 'image/jpeg', 0.85);
    };
    im.onerror = function () { URL.revokeObjectURL(url); cb(null); };
    im.src = url;
  }
  function unggah(blob, cb) {
    var fd = new FormData();
    fd.append('action', 'media'); fd.append('csrf', CSRF); fd.append('gambar', blob, 'g.jpg');
    var x = new XMLHttpRequest();
    x.open('POST', 'index.php');
    x.onload = function () {
      var r = {};
      try { r = JSON.parse(x.responseText); } catch (e) { r = { err: 'Balasan server tidak dikenali (kode ' + x.status + ').' }; }
      cb(r);
    };
    x.onerror = function () { cb({ err: 'Koneksi terputus.' }); };
    x.send(fd);
  }
  function proses(input, info, cb) {
    var f = input.files[0]; input.value = '';
    if (!f) return;
    info.textContent = 'Memproses gambar...';
    kecilkan(f, 1600, function (b) {
      if (!b) { info.textContent = 'Gambar tidak bisa dibaca.'; return; }
      unggah(b, function (r) {
        if (r.ok) { info.textContent = ''; cb(r.url); tandai(); } else { info.textContent = r.err || 'Gagal mengunggah.'; }
      });
    });
  }
  $('ff').addEventListener('change', function () {
    proses($('ff'), $('fpm'), function (u) { $('foto').value = u; $('fpi').src = u; $('fpb').hidden = false; });
  });
  $('fpx').addEventListener('click', function () { $('foto').value = ''; $('fpb').hidden = true; tandai(); });
  $('gf').addEventListener('change', function () {
    proses($('gf'), $('gm'), function (u) {
      var alt = window.prompt('Keterangan gambar (boleh kosong)', '') || '';
      pulih();
      var esc = function (t) { return t.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); };
      document.execCommand('insertHTML', false, '<p><img src="' + u + '" alt="' + esc(alt) + '"></p><p><br></p>');
    });
  });

  function auto(cb) {
    cb = cb || function () {};
    if (saving || !AWAL_DRAF || stt.value !== 'draf') { cb(); return; }
    sync();
    if (!$('jd').value.trim() && !ed.textContent.trim()) { cb(); return; }
    saving = true;
    var fd = new FormData(fm);
    fd.set('auto', '1'); fd.set('csrf', CSRF);
    var x = new XMLHttpRequest();
    x.open('POST', 'index.php');
    x.onload = function () {
      saving = false;
      var r = {};
      try { r = JSON.parse(x.responseText); } catch (e) {}
      if (r.ok && r.id) { aid.value = r.id; dirty = false; st.textContent = 'Draf tersimpan otomatis ' + r.t; }
      else if (r.err) { st.textContent = r.err; }
      cb();
    };
    x.onerror = function () { saving = false; st.textContent = 'Koneksi terputus.'; cb(); };
    x.send(fd);
  }
  setInterval(function () { if (dirty) auto(); }, 15000);

  $('pv').addEventListener('click', function () {
    var w = window.open('', '_blank');
    function buka() {
      if (aid.value && w) { w.location = './?m=pratinjau&id=' + encodeURIComponent(aid.value); }
      else if (w) { w.close(); st.textContent = 'Isi judul atau teks dulu.'; }
    }
    if (AWAL_DRAF && stt.value === 'draf') { auto(buka); } else { buka(); }
  });

  fm.addEventListener('submit', function () { sync(); kirim = true; });
  window.addEventListener('beforeunload', function (e) { if (dirty && !kirim) { e.preventDefault(); e.returnValue = ''; } });
})();
</script>

<?php elseif ($m === 'artikel'): ?>
<?php
$q = trim((string)($_GET['q'] ?? ''));
$sf = (string)($_GET['s'] ?? '');
$tampilA = array_values(array_filter($semuaArt, function ($x) use ($q, $sf) {
    if ($sf !== '' && art_status($x) !== $sf) {
        return false;
    }
    if ($q !== '') {
        $g = ($x['judul'] ?? '') . ' ' . ($x['kategori'] ?? '') . ' ' . implode(' ', (array)($x['tag'] ?? []));
        return mb_stripos($g, $q) !== false;
    }
    return true;
}));
?>
<form method="get" class="fl">
<input type="hidden" name="m" value="artikel">
<input type="search" name="q" value="<?= h($q) ?>" placeholder="Cari judul, kategori, tag" aria-label="Cari">
<select name="s" aria-label="Status"><option value="">Semua status</option><?php foreach (['terbit' => 'Terbit', 'terjadwal' => 'Terjadwal', 'draf' => 'Draf'] as $kv => $lb): ?><option value="<?= $kv ?>"<?= $sf === $kv ? ' selected' : '' ?>><?= $lb ?></option><?php endforeach; ?></select>
<button class="ib" type="submit" title="Cari" aria-label="Cari"><?= ikon('cari', 18) ?></button>
<a class="bt sp" href="./?m=artikel&amp;e=baru"><?= ikon('tambah', 18) ?>Artikel baru</a>
</form>
<div class="c">
<div class="row" style="margin-bottom:6px"><h2 style="margin:0">Daftar artikel</h2><span class="m sp" style="margin:0"><?= count($tampilA) ?> dari <?= count($semuaArt) ?></span></div>
<?php if (!$tampilA): ?>
<p class="m"><?= $semuaArt ? 'Tidak ada yang cocok.' : 'Belum ada artikel.' ?></p>
<?php endif; ?>
<?php foreach ($tampilA as $x): $sx = art_status($x); $th = art_thumb((string)($x['foto'] ?? '')); ?>
<div class="v">
<?php if ($th !== ''): ?><img src="<?= h($th) ?>" alt="" loading="lazy" width="64" height="64"><?php else: ?><span class="nt" style="display:flex;align-items:center;justify-content:center;color:#94a3b8;background:#e2e8f0"><?= ikon('artikel', 24) ?></span><?php endif; ?>
<div class="bd">
<b><?= h($x['judul']) ?></b>
<small><span class="tg<?= $sx === 'terbit' ? ' on' : '' ?>"><?= h(ucfirst($sx)) ?></span><?= h(($x['kategori'] ?? '') !== '' ? $x['kategori'] . ', ' : '') ?><?= h(art_tgl((string)$x['waktu'])) ?></small>
</div>
<div class="ac">
<a class="ib" href="./?m=artikel&amp;e=<?= h($x['id']) ?>" title="Ubah" aria-label="Ubah"><?= ikon('ubah', 18) ?></a>
<?php if ($sx === 'terbit'): ?><a class="ib" href="/artikel/<?= h($x['slug']) ?>" target="_blank" rel="noopener" title="Lihat di situs" aria-label="Lihat di situs"><?= ikon('lihat', 18) ?></a><?php else: ?><a class="ib" href="./?m=pratinjau&amp;id=<?= h($x['id']) ?>" target="_blank" rel="noopener" title="Pratinjau" aria-label="Pratinjau"><?= ikon('mata', 18) ?></a><?php endif; ?>
<?= btn($csrf, 'artikel_hapus', $x['id'], 'hapus', 'Hapus', 'x', 'Hapus artikel ini?') ?>
</div>
</div>
<?php endforeach; ?>
</div>

<?php elseif ($m === 'video'): ?>
<div class="c">
<div class="row" style="margin-bottom:6px"><h2 style="margin:0">Daftar video</h2><span class="m sp" style="margin:0"><?= count($daftar) ?> dari <?= MAXN ?>, <?= h(mb_fmt($total)) ?> MB</span></div>
<?php if (!$daftar): ?>
<p class="m">Belum ada video.</p>
<?php endif; ?>
<?php foreach ($daftar as $i => $it): ?>
<div class="v" style="flex-wrap:wrap">
<a href="video/<?= h($it['video']) ?>" target="_blank" rel="noopener" aria-label="Putar video"><img src="video/<?= h($it['sampul']) ?>" alt="" loading="lazy" width="64" height="64"></a>
<div class="bd">
<b><?= h(($it['judul'] ?? '') !== '' ? $it['judul'] : 'Tanpa judul') ?></b>
<small><?= !empty($it['aktif']) ? '<span class="tg on">Tampil</span>' : '<span class="tg">Sembunyi</span>' ?><?= h($it['durasi'] ?? 0) ?> dtk, <?= h(mb_fmt((int)($it['ukuran'] ?? 0))) ?> MB</small>
</div>
<div class="ac">
<?= btn($csrf, 'toggle', $it['id'], !empty($it['aktif']) ? 'mata-tutup' : 'mata', !empty($it['aktif']) ? 'Sembunyikan' : 'Tampilkan') ?>
<?php if ($i > 0): ?><?= btn($csrf, 'naik', $it['id'], 'naik', 'Naik') ?><?php endif; ?>
<?php if ($i < count($daftar) - 1): ?><?= btn($csrf, 'turun', $it['id'], 'turun', 'Turun') ?><?php endif; ?>
<?= btn($csrf, 'hapus', $it['id'], 'hapus', 'Hapus', 'x', 'Hapus video ini dari server?') ?>
</div>
<details class="ed" style="width:100%">
<summary class="m" style="display:flex;gap:6px;align-items:center;color:var(--p);margin:6px 0 0"><?= ikon('ubah', 16) ?>Ubah info</summary>
<form method="post" class="edf">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="info"><input type="hidden" name="id" value="<?= h($it['id']) ?>">
<label>Judul</label>
<input type="text" name="judul" maxlength="80" value="<?= h($it['judul'] ?? '') ?>">
<label>Deskripsi singkat</label>
<textarea name="deskripsi" maxlength="200" rows="3"><?= h($it['deskripsi'] ?? '') ?></textarea>
<label>Tanggal dan waktu</label>
<input type="datetime-local" name="waktu" value="<?= h($it['waktu'] ?? date('Y-m-d\TH:i')) ?>">
<button class="bt o" type="submit"><?= ikon('simpan', 18) ?>Simpan</button>
</form>
</details>
</div>
<?php endforeach; ?>
</div>

<div class="c">
<h2>Tambah video</h2>
<?php if (count($daftar) >= MAXN): ?>
<p class="m">Sudah <?= MAXN ?> video. Hapus salah satu dulu.</p>
<?php else: ?>
<label>Judul</label>
<input type="text" id="j" maxlength="80">
<label>Deskripsi singkat</label>
<textarea id="ds" maxlength="200" rows="3"></textarea>
<label>Tanggal dan waktu</label>
<input type="datetime-local" id="wk" value="<?= h(date('Y-m-d\TH:i')) ?>">
<p class="m">MP4, 30 detik atau kurang, maksimal 19 MB. Setelah dipilih, geser untuk menentukan bingkai sampul.</p>
<input type="file" id="f" accept="video/mp4">
<canvas id="cvs" hidden></canvas>
<input type="range" id="sl" min="0" max="1000" value="0" hidden>
<p class="m" id="pesan"></p>
<progress id="pg" value="0" max="100" hidden></progress>
<div class="row" style="margin-top:8px"><button type="button" id="kirim" class="bt" disabled><?= ikon('unggah', 18) ?>Unggah</button></div>
<?php endif; ?>
</div>

<?php if (count($daftar) < MAXN): ?>
<script>
(function () {
  var CSRF = <?= json_encode($csrf) ?>;
  var LIM = <?= MAXB ?>, MAXD = <?= MAXD ?>;
  var $ = function (i) { return document.getElementById(i); };
  var f = $('f'), p = $('pesan'), b = $('kirim'), pg = $('pg'), cv = $('cvs'), sl = $('sl');
  var j = $('j'), ds = $('ds'), wk = $('wk');
  var vid = null, url = null, meta = null, timer = null;
  function say(t) { p.textContent = t; }
  function fail() { say('Video tidak bisa diperiksa browser. Coba MP4 H.264.'); }

  function draw() {
    var w = Math.min(720, meta.w), h = Math.round(meta.h * w / meta.w);
    cv.width = w; cv.height = h;
    cv.getContext('2d').drawImage(vid, 0, 0, w, h);
    cv.hidden = false; sl.hidden = false; b.disabled = false;
    say('Siap: ' + meta.d.toFixed(1) + ' detik, ' + meta.w + 'x' + meta.h + ', ' + (f.files[0].size / 1048576).toFixed(1) + ' MB. Geser untuk memilih sampul.');
  }

  f.addEventListener('change', function () {
    clearTimeout(timer);
    if (url) { URL.revokeObjectURL(url); url = null; }
    vid = null; meta = null; b.disabled = true; cv.hidden = true; sl.hidden = true;
    var file = f.files[0];
    if (!file) { say(''); return; }
    if (file.size > LIM) { say('Ukuran ' + (file.size / 1048576).toFixed(1) + ' MB melebihi batas 19 MB.'); return; }
    say('Memeriksa video...');
    url = URL.createObjectURL(file);
    var v = document.createElement('video');
    vid = v;
    v.muted = true; v.playsInline = true; v.preload = 'auto';
    timer = setTimeout(fail, 10000);
    v.onerror = function () { clearTimeout(timer); fail(); };
    v.onloadedmetadata = function () {
      if (!isFinite(v.duration) || v.duration <= 0 || !v.videoWidth) { clearTimeout(timer); say('Video tidak valid.'); return; }
      if (v.duration > MAXD) { clearTimeout(timer); say('Durasi ' + v.duration.toFixed(1) + ' detik. Maksimal 30 detik.'); return; }
      meta = { d: v.duration, w: v.videoWidth, h: v.videoHeight };
      var t = Math.min(1, v.duration / 2);
      sl.value = String(Math.round(t / v.duration * 1000));
      v.currentTime = t;
    };
    v.onseeked = function () { clearTimeout(timer); if (meta) draw(); };
    v.src = url;
  });

  sl.addEventListener('input', function () {
    if (!vid || !meta) return;
    var t = Math.min(meta.d - 0.05, Number(sl.value) / 1000 * meta.d);
    vid.currentTime = Math.max(0, t);
  });

  b.addEventListener('click', function () {
    if (!meta) return;
    b.disabled = true;
    cv.toBlob(function (bl) {
      if (!bl) { say('Gagal membuat gambar sampul.'); b.disabled = false; return; }
      var fd = new FormData();
      fd.append('action', 'upload');
      fd.append('csrf', CSRF);
      fd.append('video', f.files[0]);
      fd.append('sampul', bl, 'sampul.jpg');
      fd.append('durasi', meta.d);
      fd.append('lebar', meta.w);
      fd.append('tinggi', meta.h);
      fd.append('judul', j.value);
      fd.append('deskripsi', ds.value);
      fd.append('waktu', wk.value);
      pg.hidden = false; say('Mengunggah...');
      var x = new XMLHttpRequest();
      x.open('POST', 'index.php');
      x.upload.onprogress = function (e) { if (e.lengthComputable) { pg.value = Math.round(e.loaded / e.total * 100); } };
      x.onload = function () {
        var r = {};
        try { r = JSON.parse(x.responseText); } catch (e) { r = { err: 'Balasan server tidak dikenali (kode ' + x.status + ').' }; }
        if (r.ok) { location.href = './?m=video'; } else { say(r.err || 'Gagal.'); b.disabled = false; }
      };
      x.onerror = function () { say('Koneksi terputus.'); b.disabled = false; };
      x.send(fd);
    }, 'image/jpeg', 0.82);
  });
})();
</script>
<?php endif; ?>

<?php else: ?>
<?php $log = muat(DATA . '/log.php'); ?>
<div class="c">
<h2>Ganti kata sandi</h2>
<form method="post" autocomplete="off">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="sandi">
<label>Kata sandi saat ini</label>
<input type="password" name="p0" required>
<label>Kata sandi baru (minimal 10 karakter)</label>
<input type="password" name="p1" minlength="10" required>
<label>Ulangi kata sandi baru</label>
<input type="password" name="p2" minlength="10" required>
<button class="bt" type="submit"><?= ikon('kunci', 18) ?>Ganti</button>
</form>
</div>

<div class="c">
<h2>Perangkat dan cadangan</h2>
<div class="row">
<form method="post" onsubmit="return confirm('Keluarkan semua perangkat lain?')"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="keluar_semua"><button class="bt o" type="submit"><?= ikon('keluar', 18) ?>Keluar dari perangkat lain</button></form>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="ekspor"><button class="bt o" type="submit"><?= ikon('unduh', 18) ?>Unduh cadangan data</button></form>
</div>
<p class="m" style="margin-top:8px">Cadangan berisi daftar video dan riwayat aktivitas, bukan berkas videonya.</p>
</div>

<div class="c">
<h2>Riwayat aktivitas</h2>
<?php if (!$log): ?>
<p class="m">Belum ada aktivitas.</p>
<?php else: ?>
<ul class="lg">
<?php foreach ($log as $l): ?>
<li><time><?= h(tgl((string)$l['w'])) ?></time><span><?= h($l['t']) ?></span></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>

<div class="c">
<h2>Server</h2>
<p class="m">PHP <?= h(PHP_VERSION) ?>, upload_max_filesize <?= h(ini_get('upload_max_filesize')) ?>, post_max_size <?= h(ini_get('post_max_size')) ?>, max_execution_time <?= h(ini_get('max_execution_time')) ?> detik</p>
</div>
<?php endif; ?>
</main>
<script>
(function () {
  var u = document.getElementById('um'), b = document.getElementById('umb');
  if (u && b) {
    b.addEventListener('click', function (e) {
      e.stopPropagation();
      var o = u.classList.toggle('open');
      b.setAttribute('aria-expanded', o ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!u.contains(e.target)) { u.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { u.classList.remove('open'); } });
  }
  var t = document.getElementById('ts');
  if (t) { setTimeout(function () { t.classList.add('h'); setTimeout(function () { t.remove(); }, 400); }, 4200); }
})();
</script>
<?php endif; ?>
</body>
</html>
