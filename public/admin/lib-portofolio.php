<?php
declare(strict_types=1);
// Modul Portofolio: penyimpanan JSON, dipakai admin (/admin/?m=portofolio) dan /portofolio/data.php.
require_once __DIR__ . '/lib-artikel.php';

defined('POR_DIR') || define('POR_DIR', __DIR__ . '/data/portofolio');
defined('POR_MAX_FOTO') || define('POR_MAX_FOTO', 12);
defined('POR_MAX_HASIL') || define('POR_MAX_HASIL', 8);
defined('POR_MAX_ALAT') || define('POR_MAX_ALAT', 12);

function por_siapkan(): void
{
    if (!is_dir(POR_DIR)) {
        mkdir(POR_DIR, 0755, true);
    }
    $f = POR_DIR . '/.htaccess';
    if (!is_file($f)) {
        file_put_contents($f, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
    }
}

/** Semua proyek, urut sesuai tampilan di situs (urutan kecil di atas). */
function por_semua(): array
{
    $hasil = [];
    foreach (glob(POR_DIR . '/*.json') ?: [] as $f) {
        $a = json_decode((string)file_get_contents($f), true);
        if (is_array($a) && !empty($a['id'])) {
            $hasil[] = $a;
        }
    }
    usort($hasil, function ($x, $y) {
        $c = (int)($x['urutan'] ?? 0) <=> (int)($y['urutan'] ?? 0);
        return $c !== 0 ? $c : strcmp((string)($y['dibuat'] ?? ''), (string)($x['dibuat'] ?? ''));
    });
    return $hasil;
}

function por_baca(string $id): ?array
{
    if (!preg_match('/^[a-f0-9]{16}$/', $id)) {
        return null;
    }
    $f = POR_DIR . '/' . $id . '.json';
    if (!is_file($f)) {
        return null;
    }
    $a = json_decode((string)file_get_contents($f), true);
    return is_array($a) ? $a : null;
}

function por_tulis(array $a): void
{
    por_siapkan();
    file_put_contents(POR_DIR . '/' . $a['id'] . '.json', json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function por_hapus(string $id): void
{
    if (preg_match('/^[a-f0-9]{16}$/', $id)) {
        @unlink(POR_DIR . '/' . $id . '.json');
    }
}

/** Hanya foto hasil unggahan admin atau gambar bawaan situs yang boleh dipakai. */
function por_url_ok(string $u): bool
{
    return (bool)preg_match('#^/admin/media/[a-z]_[a-f0-9]{16}\.jpg$#', $u) || (bool)preg_match('#^/gambar/[a-z0-9._-]{1,80}\.webp$#', $u);
}

/** Bersihkan teks kiriman: tanpa tag HTML, dibatasi panjangnya. $satuBaris=true meratakan spasi dan baris baru. */
function por_teks($s, int $maks, bool $satuBaris = true): string
{
    if (!is_scalar($s)) {
        return '';
    }
    $t = strip_tags((string)$s);
    $t = str_replace(["\r\n", "\r"], "\n", $t);
    if ($satuBaris) {
        $t = (string)preg_replace('/\s+/u', ' ', $t);
    } else {
        $t = (string)preg_replace("/[ \t]+/u", ' ', $t);
        $t = (string)preg_replace("/\n{3,}/", "\n\n", $t);
    }
    return mb_substr(trim($t), 0, $maks);
}

function por_dari_post(): array
{
    $foto = [];
    $fu = (array)($_POST['foto_url'] ?? []);
    $fk = (array)($_POST['foto_ket'] ?? []);
    foreach ($fu as $i => $u) {
        $u = is_scalar($u) ? trim((string)$u) : '';
        if ($u === '' || !por_url_ok($u)) {
            continue;
        }
        $foto[] = ['url' => $u, 'ket' => por_teks($fk[$i] ?? '', 160)];
        if (count($foto) >= POR_MAX_FOTO) {
            break;
        }
    }

    $hasil = [];
    $hj = (array)($_POST['hasil_judul'] ?? []);
    $hi = (array)($_POST['hasil_isi'] ?? []);
    foreach ($hj as $i => $j) {
        $j = por_teks($j, 60);
        $is = por_teks($hi[$i] ?? '', 240);
        if ($j === '' && $is === '') {
            continue;
        }
        $hasil[] = ['judul' => $j, 'isi' => $is];
        if (count($hasil) >= POR_MAX_HASIL) {
            break;
        }
    }

    $alat = [];
    $an = (array)($_POST['alat_nama'] ?? []);
    $af = (array)($_POST['alat_fungsi'] ?? []);
    $ag = (array)($_POST['alat_gambar'] ?? []);
    foreach ($an as $i => $n) {
        $n = por_teks($n, 60);
        if ($n === '') {
            continue;
        }
        $g = isset($ag[$i]) && is_scalar($ag[$i]) ? trim((string)$ag[$i]) : '';
        $alat[] = ['nama' => $n, 'fungsi' => por_teks($af[$i] ?? '', 200), 'gambar' => ($g !== '' && por_url_ok($g)) ? $g : ''];
        if (count($alat) >= POR_MAX_ALAT) {
            break;
        }
    }

    $tautan = trim((string)($_POST['tautan'] ?? ''));
    if ($tautan !== '' && (!preg_match('#^https?://#i', $tautan) || !filter_var($tautan, FILTER_VALIDATE_URL) || strlen($tautan) > 300)) {
        $tautan = '';
    }

    return [
        'judul' => por_teks($_POST['judul'] ?? '', 150),
        'kategori' => por_teks($_POST['kategori'] ?? '', 40),
        'tahun' => por_teks($_POST['tahun'] ?? '', 20),
        'untuk' => por_teks($_POST['untuk'] ?? '', 80),
        'ringkasan' => por_teks($_POST['ringkasan'] ?? '', 300),
        'keterangan' => por_teks($_POST['keterangan'] ?? '', 4000, false),
        'sebelum' => por_teks($_POST['sebelum'] ?? '', 500),
        'sekarang' => por_teks($_POST['sekarang'] ?? '', 500),
        'hasil' => $hasil,
        'foto' => $foto,
        'alat' => $alat,
        'tautan' => $tautan,
        'tampil' => ($_POST['tampil'] ?? '1') === '1',
    ];
}

/** Bentuk data untuk situs publik (hanya proyek yang ditampilkan). */
function por_publik(): array
{
    $hasil = [];
    foreach (por_semua() as $a) {
        if (empty($a['tampil'])) {
            continue;
        }
        $foto = [];
        foreach ((array)($a['foto'] ?? []) as $f) {
            $foto[] = ['url' => (string)$f['url'], 'thumb' => art_thumb((string)$f['url']), 'ket' => (string)($f['ket'] ?? '')];
        }
        $hasil[] = [
            'id' => (string)$a['id'],
            'judul' => (string)$a['judul'],
            'kategori' => (string)($a['kategori'] ?? ''),
            'tahun' => (string)($a['tahun'] ?? ''),
            'untuk' => (string)($a['untuk'] ?? ''),
            'ringkasan' => (string)($a['ringkasan'] ?? ''),
            'keterangan' => (string)($a['keterangan'] ?? ''),
            'sebelum' => (string)($a['sebelum'] ?? ''),
            'sekarang' => (string)($a['sekarang'] ?? ''),
            'hasil' => array_values((array)($a['hasil'] ?? [])),
            'foto' => $foto,
            'alat' => array_values((array)($a['alat'] ?? [])),
            'tautan' => (string)($a['tautan'] ?? ''),
        ];
    }
    return $hasil;
}

/** Nomor urutan yang menaruh proyek baru di paling atas. */
function por_urutan_atas(): int
{
    $u = 0;
    foreach (por_semua() as $x) {
        $u = min($u, (int)($x['urutan'] ?? 0) - 1);
    }
    return $u;
}

function por_pindah(string $id, int $arah): void
{
    $semua = por_semua();
    $i = null;
    foreach ($semua as $n => $a) {
        if ($a['id'] === $id) {
            $i = $n;
        }
    }
    $j = $i === null ? null : $i + $arah;
    if ($i === null || $j < 0 || $j >= count($semua)) {
        return;
    }
    [$semua[$i], $semua[$j]] = [$semua[$j], $semua[$i]];
    foreach ($semua as $n => $a) {
        if ((int)($a['urutan'] ?? 0) !== $n) {
            $a['urutan'] = $n;
            por_tulis($a);
        }
    }
}

/** Proyek bawaan situs (dulu ditulis di kode), supaya bisa diimpor sekali klik lalu diubah dari admin. */
function por_bawaan(): array
{
    return [
        'judul' => 'Sistem absensi digital dan kartu pelajar berkode QR',
        'kategori' => 'Aplikasi pendidikan',
        'tahun' => '2023',
        'untuk' => 'sebuah sekolah',
        'ringkasan' => 'Kartu pelajar berkode QR yang mencatat kehadiran siswa secara otomatis.',
        'keterangan' => '',
        'sebelum' => 'Sekolah belum memiliki kartu pelajar, dan absensi dicatat manual di banyak buku sehingga rekap kehadiran memakan waktu.',
        'sekarang' => 'Setiap siswa memiliki kartu pelajar dengan kode QR sendiri. Cukup dipindai, kehadiran langsung masuk ke spreadsheet.',
        'hasil' => [
            ['judul' => 'ID pribadi', 'isi' => 'Setiap siswa memiliki kartu pelajar dengan ID sendiri.'],
            ['judul' => 'Data terpusat', 'isi' => 'Kehadiran terkumpul otomatis di satu spreadsheet.'],
            ['judul' => 'Rekap siap cetak', 'isi' => 'Rekap tidak perlu diinput ulang dan laporan dapat dicetak kapan saja.'],
        ],
        'foto' => [
            ['url' => '/gambar/absensi-kartu.webp', 'ket' => 'Desain kartu pelajar berkode QR'],
        ],
        'alat' => [
            ['nama' => 'Pembuat kode QR', 'fungsi' => 'Membuat kode QR yang dicetak pada kartu pelajar.', 'gambar' => '/gambar/absensi-qr.webp'],
            ['nama' => 'Kartu pelajar', 'fungsi' => 'Desain kartu pelajar lengkap dengan kode QR.', 'gambar' => '/gambar/absensi-kartu.webp'],
            ['nama' => 'Scan-IT to Office', 'fungsi' => 'Memindai kartu dan mengisi kode, tanggal, dan jam secara otomatis.', 'gambar' => '/gambar/absensi-scanit.webp'],
            ['nama' => 'Google Spreadsheet', 'fungsi' => 'Menyimpan data kehadiran sebagai dasar rekap.', 'gambar' => '/gambar/absensi-sheets.webp'],
        ],
        'tautan' => '',
        'tampil' => true,
    ];
}

/** Aksi admin untuk portofolio. Dipanggil dari index.php setelah login terverifikasi dan CSRF valid. */
function por_aksi(string $act): void
{
    global $err;
    $id = (string)($_POST['id'] ?? '');

    if ($act === 'por_simpan') {
        $lama = $id !== '' ? por_baca($id) : null;
        $d = por_dari_post();
        $galat = '';
        if ($d['judul'] === '') {
            $galat = 'Judul wajib diisi.';
        } elseif ($d['tampil'] && !$d['foto']) {
            $galat = 'Tambahkan minimal satu foto, atau pilih Sembunyikan.';
        }
        if ($galat !== '') {
            $err = $galat;
            $GLOBALS['porPost'] = $d + ['id' => $id];
            $_GET['m'] = 'portofolio';
            $_GET['e'] = $id !== '' ? $id : 'baru';
            return;
        }
        $baru = $lama === null;
        if ($baru) {
            $lama = ['id' => bin2hex(random_bytes(8)), 'dibuat' => date('Y-m-d H:i'), 'urutan' => por_urutan_atas()];
        }
        $a = array_merge($lama, $d);
        $a['diperbarui'] = date('Y-m-d H:i');
        por_tulis($a);
        catat(($baru ? 'Portofolio dibuat: ' : 'Portofolio disimpan: ') . $a['judul']);
        $_SESSION['flash'] = 'Portofolio tersimpan.';
        header('Location: ./?m=portofolio');
        exit;
    }

    if ($act === 'por_hapus') {
        $a = por_baca($id);
        if ($a) {
            por_hapus($a['id']);
            catat('Portofolio dihapus: ' . $a['judul']);
            $_SESSION['flash'] = 'Portofolio dihapus.';
        }
        header('Location: ./?m=portofolio');
        exit;
    }

    if ($act === 'por_toggle') {
        $a = por_baca($id);
        if ($a) {
            $a['tampil'] = empty($a['tampil']);
            por_tulis($a);
            catat(($a['tampil'] ? 'Portofolio ditampilkan: ' : 'Portofolio disembunyikan: ') . $a['judul']);
        }
        header('Location: ./?m=portofolio');
        exit;
    }

    if ($act === 'por_naik' || $act === 'por_turun') {
        por_pindah($id, $act === 'por_naik' ? -1 : 1);
        header('Location: ./?m=portofolio');
        exit;
    }

    if ($act === 'por_impor') {
        foreach (por_semua() as $x) {
            if (!empty($x['bawaan'])) {
                $_SESSION['flash_err'] = 'Proyek bawaan sudah pernah diimpor.';
                header('Location: ./?m=portofolio');
                exit;
            }
        }
        $a = por_bawaan() + ['id' => bin2hex(random_bytes(8)), 'dibuat' => date('Y-m-d H:i'), 'urutan' => por_urutan_atas(), 'bawaan' => true];
        por_tulis($a);
        catat('Portofolio bawaan diimpor: ' . $a['judul']);
        $_SESSION['flash'] = 'Proyek bawaan sudah diimpor. Silakan ubah sesukamu.';
        header('Location: ./?m=portofolio');
        exit;
    }
}
