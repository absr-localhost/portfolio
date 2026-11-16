<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');

const DATA = __DIR__ . '/data';
const VID = __DIR__ . '/video';
const MAXB = 19 * 1024 * 1024;
const MAXD = 30.5;

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
function baca_video(): array
{
    $f = VID . '/video.json';
    if (!is_file($f)) {
        return [];
    }
    $j = json_decode((string)file_get_contents($f), true);
    return is_array($j) ? $j : [];
}
function tulis_video(array $a): void
{
    file_put_contents(VID . '/video.json', json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
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
$csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
$err = '';
$info = '';
$post = $_SERVER['REQUEST_METHOD'] === 'POST';

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

if ($act === 'setup' && $belumAtur) {
    $p1 = (string)($_POST['p1'] ?? '');
    $p2 = (string)($_POST['p2'] ?? '');
    if (strlen($p1) < 10) {
        $err = 'Kata sandi minimal 10 karakter.';
    } elseif ($p1 !== $p2) {
        $err = 'Kedua isian kata sandi tidak sama.';
    } else {
        simpan(DATA . '/auth.php', ['hash' => password_hash($p1, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['ok'] = 1;
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
        session_regenerate_id(true);
        $_SESSION['ok'] = 1;
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
    $lama = baca_video();
    tulis_video([
        'aktif' => $lama ? (bool)($lama['aktif'] ?? true) : true,
        'judul' => bersih_judul((string)($_POST['judul'] ?? '')),
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
    hapus_berkas($lama);
    json_out(['ok' => true]);
}

if ($masuk && in_array($act, ['toggle', 'info', 'hapus'], true)) {
    $vd = baca_video();
    if ($vd) {
        if ($act === 'toggle') {
            $vd['aktif'] = empty($vd['aktif']);
            tulis_video($vd);
        } elseif ($act === 'info') {
            $vd['judul'] = bersih_judul((string)($_POST['judul'] ?? ''));
            $vd['deskripsi'] = bersih_desk((string)($_POST['deskripsi'] ?? ''));
            $vd['waktu'] = bersih_waktu((string)($_POST['waktu'] ?? ''));
            tulis_video($vd);
        } else {
            hapus_berkas($vd);
            tulis_video([]);
        }
    }
    header('Location: ./');
    exit;
}

$vd = baca_video();
$ada = !empty($vd['video']) && is_file(VID . '/' . $vd['video']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin video beranda</title>
<style>
*{box-sizing:border-box}
body{margin:0;font:15px/1.5 system-ui,sans-serif;background:#f1f5f9;color:#1f2937}
header{background:#0B2545;color:#fff;padding:14px 16px;display:flex;justify-content:space-between;align-items:center}
header h1{margin:0;font-size:17px}
main{max-width:560px;margin:0 auto;padding:16px}
.c{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:14px}
.c h2{margin:0 0 10px;font-size:16px}
.m{color:#4A5568;font-size:13px}
input[type=text],input[type=password],input[type=file],input[type=datetime-local],textarea{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:6px;font:inherit;margin:4px 0 10px;background:#fff}
[hidden]{display:none!important}
canvas{display:block;max-width:100%;max-height:300px;margin:0 auto 10px;border-radius:6px;background:#000}
input[type=range]{width:100%;margin:0 0 10px}
button{background:#0B2545;color:#fff;border:0;border-radius:6px;padding:10px 14px;font-size:15px;cursor:pointer}
button:disabled{opacity:.45}
button.x{background:#fff;color:#b91c1c;border:1px solid #b91c1c}
button.o{background:#fff;color:#0B2545;border:1px solid #0B2545}
.row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:6px;padding:10px;margin-bottom:14px}
video{display:block;max-width:100%;max-height:340px;margin:0 auto 10px;background:#000;border-radius:6px}
progress{width:100%;height:10px}
.st{font-size:13px;color:#4A5568;margin:0}
.st b{color:#1f2937}
</style>
</head>
<body>
<header>
<h1>Admin video beranda</h1>
<?php if ($masuk): ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="keluar"><button class="o" type="submit">Keluar</button></form>
<?php endif; ?>
</header>
<main>
<?php if ($err !== ''): ?><div class="e"><?= h($err) ?></div><?php endif; ?>

<?php if ($belumAtur): ?>
<div class="c">
<h2>Pengaturan awal</h2>
<p class="m">Buat kata sandi admin. Halaman ini tertutup sendiri setelah kata sandi tersimpan.</p>
<form method="post" autocomplete="off">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="setup">
<label>Kata sandi (minimal 10 karakter)</label>
<input type="password" name="p1" minlength="10" required>
<label>Ulangi kata sandi</label>
<input type="password" name="p2" minlength="10" required>
<button type="submit">Simpan</button>
</form>
</div>

<?php elseif (!$masuk): ?>
<div class="c">
<h2>Masuk</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="login">
<label>Kata sandi</label>
<input type="password" name="p" required autofocus>
<button type="submit">Masuk</button>
</form>
</div>

<?php else: ?>
<div class="c">
<h2>Video saat ini</h2>
<?php if ($ada): ?>
<video controls playsinline preload="metadata" poster="video/<?= h($vd['sampul']) ?>" src="video/<?= h($vd['video']) ?>"></video>
<p class="st">
<b><?= !empty($vd['aktif']) ? 'Tampil di beranda' : 'Disembunyikan' ?></b><br>
Durasi <?= h($vd['durasi']) ?> detik, <?= ($vd['tinggi'] > $vd['lebar']) ? 'tegak' : 'mendatar' ?> (<?= h($vd['lebar']) ?>x<?= h($vd['tinggi']) ?>), <?= h(round(($vd['ukuran'] ?? 0) / 1048576, 1)) ?> MB<br>
Diperbarui <?= h($vd['diperbarui'] ?? '-') ?>
</p>
<div class="row">
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="toggle"><button type="submit"><?= !empty($vd['aktif']) ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
<form method="post" onsubmit="return confirm('Hapus video ini dari server?')"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="hapus"><button class="x" type="submit">Hapus</button></form>
</div>
<?php else: ?>
<p class="m">Belum ada video.</p>
<?php endif; ?>
</div>

<div class="c">
<h2>Detail video</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="info">
<label>Judul</label>
<input type="text" name="judul" id="j" maxlength="80" value="<?= h($vd['judul'] ?? '') ?>">
<label>Deskripsi singkat</label>
<textarea name="deskripsi" id="ds" maxlength="200" rows="3"><?= h($vd['deskripsi'] ?? '') ?></textarea>
<label>Tanggal dan waktu</label>
<input type="datetime-local" name="waktu" id="wk" value="<?= h($vd['waktu'] ?? date('Y-m-d\TH:i')) ?>">
<?php if ($ada): ?>
<button class="o" type="submit">Simpan detail</button>
<?php else: ?>
<p class="m">Detail ini ikut tersimpan saat video diunggah.</p>
<?php endif; ?>
</form>
</div>

<div class="c">
<h2><?= $ada ? 'Ganti video' : 'Unggah video' ?></h2>
<p class="m">MP4, 30 detik atau kurang, maksimal 19 MB. Setelah dipilih, geser untuk menentukan bingkai yang jadi gambar sampul.</p>
<input type="file" id="f" accept="video/mp4">
<canvas id="cvs" hidden></canvas>
<input type="range" id="sl" min="0" max="1000" value="0" hidden>
<p class="st" id="pesan"></p>
<progress id="pg" value="0" max="100" hidden></progress>
<div class="row"><button type="button" id="kirim" disabled>Unggah</button></div>
</div>

<div class="c">
<h2>Server</h2>
<p class="st">PHP <?= h(PHP_VERSION) ?>, upload_max_filesize <?= h(ini_get('upload_max_filesize')) ?>, post_max_size <?= h(ini_get('post_max_size')) ?>, max_execution_time <?= h(ini_get('max_execution_time')) ?> detik</p>
<p class="st">Pengecekan durasi dilakukan di browser. Cukup untuk pemilik, bukan penjaga ketat.</p>
</div>

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
    var w = Math.min(960, meta.w), h = Math.round(meta.h * w / meta.w);
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
        if (r.ok) { location.reload(); } else { say(r.err || 'Gagal.'); b.disabled = false; }
      };
      x.onerror = function () { say('Koneksi terputus.'); b.disabled = false; };
      x.send(fd);
    }, 'image/jpeg', 0.82);
  });
})();
</script>
<?php endif; ?>
</main>
</body>
</html>
