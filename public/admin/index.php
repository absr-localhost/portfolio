<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');

const DATA = __DIR__ . '/data';
const VID = __DIR__ . '/video';
const MAXB = 19 * 1024 * 1024;
const MAXD = 30.5;
const MAXN = 12;
const MAXLOG = 100;

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
function h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
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
    ];
    return '<svg class="ik" width="' . $u . '" height="' . $u . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$n] ?? '') . '</svg>';
}
function btn(string $csrf, string $act, string $id, string $ik, string $label, string $cls = '', string $ask = ''): string
{
    $on = $ask !== '' ? ' onsubmit="return confirm(\'' . h($ask) . '\')"' : '';
    return '<form method="post"' . $on . '><input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="action" value="' . h($act) . '"><input type="hidden" name="id" value="' . h($id) . '"><button class="ib ' . h($cls) . '" type="submit" title="' . h($label) . '" aria-label="' . h($label) . '">' . ikon($ik, 18) . '</button></form>';
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

if (!empty($_SESSION['ok']) && (int)($_SESSION['sv'] ?? -1) !== $sv) {
    unset($_SESSION['ok'], $_SESSION['sv']);
}

if ($post && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    json_out(['err' => 'Ukuran kiriman melebihi batas server.'], 413);
}

$act = $post ? (string)($_POST['action'] ?? '') : '';
if ($act !== '' && !hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
    if ($act === 'upload') {
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
        'riwayat' => muat(DATA . '/log.php'),
    ];
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="cadangan-admin-' . date('Ymd-His') . '.json"');
    echo json_encode($paket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$m = (string)($_GET['m'] ?? 'ringkasan');
if (!in_array($m, ['ringkasan', 'video', 'pengaturan'], true)) {
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
$judulM = ['ringkasan' => 'Ringkasan', 'video' => 'Video beranda', 'pengaturan' => 'Pengaturan'];
$menu = [['ringkasan', 'home', 'Ringkasan'], ['video', 'video', 'Video'], ['pengaturan', 'atur', 'Pengaturan']];
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
:root{--p:#0B2545;--s:#071930;--t:#1f2937;--mu:#4A5568;--ln:#e2e8f0;--bg:#f1f5f9}
body{margin:0;font:14px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--t)}
.ik{flex:none;display:block}
a{color:inherit}
[hidden]{display:none!important}
label{font-size:13px;color:var(--mu)}
input[type=text],input[type=password],input[type=file],input[type=datetime-local],textarea{width:100%;padding:9px 10px;border:1px solid #cbd5e1;border-radius:6px;font:inherit;margin:4px 0 10px;background:#fff;color:var(--t)}
input:focus,textarea:focus{outline:2px solid #93a9c7;outline-offset:0}
button{font:inherit}
.bt{display:inline-flex;align-items:center;gap:6px;background:var(--p);color:#fff;border:1px solid var(--p);border-radius:6px;padding:8px 14px;cursor:pointer;text-decoration:none}
.bt:disabled{opacity:.45;cursor:default}
.bt.o{background:#fff;color:var(--p)}
.bt.x{background:#fff;color:#b91c1c;border-color:#b91c1c}
.ib{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:6px;border:1px solid var(--ln);background:#fff;color:var(--p);cursor:pointer;padding:0}
.ib.x{color:#b91c1c}
.ib:hover{background:#f8fafc}
.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:6px;padding:9px 12px;margin-bottom:14px}
.f{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:6px;padding:9px 12px;margin-bottom:14px}
.c{background:#fff;border:1px solid var(--ln);border-radius:8px;padding:14px;margin-bottom:14px}
.c h2{margin:0 0 10px;font-size:15px}
.m{color:var(--mu);font-size:13px;margin:0 0 8px}
.row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.sp{margin-left:auto}
/* kerangka */
.side{position:fixed;left:0;top:0;bottom:0;width:220px;background:var(--p);color:#fff;display:flex;flex-direction:column;padding:16px 12px;z-index:5}
.side .br{font-weight:700;font-size:16px;padding:4px 10px 16px}
.side nav{display:flex;flex-direction:column;gap:2px;flex:1}
.side a,.side button.lk{display:flex;align-items:center;gap:10px;padding:10px;border-radius:6px;color:#cbd5e1;text-decoration:none;background:none;border:0;cursor:pointer;text-align:left;width:100%}
.side a:hover,.side button.lk:hover{background:rgba(255,255,255,.08);color:#fff}
.side a.on{background:var(--s);color:#fff}
.top{margin-left:220px;background:#fff;border-bottom:1px solid var(--ln);padding:14px 24px;font-weight:600;font-size:16px}
main{margin-left:220px;padding:20px 24px;max-width:960px}
.bar{display:none}
.kt{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
.k{background:#fff;border:1px solid var(--ln);border-radius:8px;padding:12px}
.k b{display:block;font-size:22px;color:var(--p);line-height:1.2}
.k span{font-size:12px;color:var(--mu)}
.v{display:flex;gap:10px;align-items:center;padding:10px 0;border-top:1px solid var(--ln)}
.v:first-of-type{border-top:0}
.v img,.v .nt{width:64px;height:64px;object-fit:cover;border-radius:6px;background:#0f172a;flex:none}
.v .bd{min-width:0;flex:1}
.v .bd b{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.v .bd small{color:var(--mu);font-size:12px}
.tg{display:inline-block;font-size:11px;padding:1px 7px;border-radius:99px;border:1px solid var(--ln);color:var(--mu);margin-right:4px}
.tg.on{background:#eff6ff;border-color:#bfdbfe;color:var(--p)}
.ac{display:flex;gap:6px;flex-wrap:wrap}
details.ed>summary{list-style:none;cursor:pointer}
details.ed>summary::-webkit-details-marker{display:none}
details.ed{margin:0}
.edf{padding:10px 0 4px;border-top:1px dashed var(--ln);margin-top:8px}
.lg{list-style:none;margin:0;padding:0}
.lg li{display:flex;gap:10px;padding:7px 0;border-top:1px solid var(--ln);font-size:13px}
.lg li:first-child{border-top:0}
.lg time{color:var(--mu);white-space:nowrap;font-size:12px;flex:none;width:112px}
progress{width:100%;height:8px}
canvas{display:block;max-width:100%;max-height:280px;margin:0 auto 10px;border-radius:6px;background:#000}
input[type=range]{width:100%;margin:0 0 10px}
.cen{max-width:380px;margin:12vh auto 0;padding:0 16px}
.cen .br{font-weight:700;font-size:17px;color:var(--p);margin-bottom:14px}
@media (max-width:767px){
.side{display:none}
.top{margin-left:0;padding:12px 16px;position:sticky;top:0;z-index:4;font-size:15px}
main{margin-left:0;padding:14px 16px 90px}
.kt{grid-template-columns:repeat(2,1fr)}
.bar{display:flex;position:fixed;left:0;right:0;bottom:0;background:var(--p);z-index:5;padding:6px 4px calc(6px + env(safe-area-inset-bottom))}
.bar a,.bar button{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;color:#cbd5e1;text-decoration:none;font-size:11px;background:none;border:0;padding:6px 0;cursor:pointer}
.bar a.on{color:#fff}
.bar form{flex:1;display:flex}
}
</style>
</head>
<body>
<?php if (!$masuk): ?>
<div class="cen">
<div class="br">Admin</div>
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
<div class="br">Admin</div>
<nav>
<?php foreach ($menu as [$k, $ik, $tx]): ?>
<a href="./?m=<?= h($k) ?>" class="<?= $m === $k ? 'on' : '' ?>"><?= ikon($ik) ?><?= h($tx) ?></a>
<?php endforeach; ?>
</nav>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="keluar"><button class="lk" type="submit"><?= ikon('keluar') ?>Keluar</button></form>
</aside>
<div class="top"><?= h($judulM[$m]) ?></div>
<nav class="bar">
<?php foreach ($menu as [$k, $ik, $tx]): ?>
<a href="./?m=<?= h($k) ?>" class="<?= $m === $k ? 'on' : '' ?>"><?= ikon($ik, 22) ?><?= h($tx) ?></a>
<?php endforeach; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="keluar"><button type="submit"><?= ikon('keluar', 22) ?>Keluar</button></form>
</nav>
<main>
<?php if ($err !== ''): ?><div class="e"><?= h($err) ?></div><?php endif; ?>
<?php if ($flash !== ''): ?><div class="f"><?= h($flash) ?></div><?php endif; ?>

<?php if ($m === 'ringkasan'): ?>
<?php $log = muat(DATA . '/log.php'); ?>
<div class="kt">
<div class="k"><b><?= count($daftar) ?></b><span>Video dari <?= MAXN ?></span></div>
<div class="k"><b><?= $aktifN ?></b><span>Tampil di beranda</span></div>
<div class="k"><b><?= count($daftar) - $aktifN ?></b><span>Disembunyikan</span></div>
<div class="k"><b><?= h(mb_fmt($total)) ?> MB</b><span>Ruang video</span></div>
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
<?php endif; ?>
</body>
</html>
