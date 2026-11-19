<?php
// Tampilan menu Portofolio. Di-include dari index.php (memakai $csrf, $err, btn(), ikon(), h()).
$semuaPor = por_semua();
$sudahImpor = false;
foreach ($semuaPor as $x) {
    if (!empty($x['bawaan'])) {
        $sudahImpor = true;
    }
}
?>
<?php if (isset($_GET['e'])): ?>
<?php
$pid = (string)$_GET['e'];
$pe = $porPost ?? ($pid !== 'baru' ? por_baca($pid) : null);
$pe = array_merge([
    'id' => '', 'judul' => '', 'kategori' => '', 'tahun' => date('Y'), 'untuk' => '', 'ringkasan' => '', 'keterangan' => '',
    'sebelum' => '', 'sekarang' => '', 'hasil' => [], 'foto' => [], 'alat' => [], 'tautan' => '', 'tampil' => true,
], $pe ?? []);
$katP = [];
foreach ($semuaPor as $x) {
    if (($x['kategori'] ?? '') !== '') {
        $katP[$x['kategori']] = true;
    }
}
$js = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<style>
.pr-fg{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin:8px 0}
.pr-f{border:1px solid var(--ln);border-radius:8px;background:#fff;overflow:hidden;display:flex;flex-direction:column}
.pr-f .im{position:relative;aspect-ratio:4/3;background:#e5e8ee}
.pr-f .im img{width:100%;height:100%;object-fit:cover;display:block}
.pr-f .no{position:absolute;left:6px;top:6px;background:var(--p);color:#fff;font-size:11px;font-weight:700;border-radius:999px;min-width:22px;height:22px;display:flex;align-items:center;justify-content:center;padding:0 6px}
.pr-f .bd{padding:8px;display:flex;flex-direction:column;gap:6px}
.pr-f .bd input{margin:0;font-size:13px}
.pr-f .ac{display:flex;gap:4px;justify-content:flex-end}
.pr-r{border:1px solid var(--ln);border-radius:8px;padding:10px;margin:8px 0;background:#fafbfc}
.pr-r input,.pr-r textarea{margin:0 0 8px}
.pr-r .ac{display:flex;gap:6px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.pr-r .mini{display:flex;align-items:center;gap:8px}
.pr-r .mini img{width:64px;height:48px;object-fit:cover;border-radius:6px;background:#e5e8ee}
.pr-tip{background:#f1f5ff;border:1px solid #d6e2fb;color:#1c3a7a;border-radius:8px;padding:10px 12px;font-size:13px;margin:0 0 12px}
</style>
<form method="post" id="fpor">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="por_simpan"><input type="hidden" name="id" value="<?= h($pe['id']) ?>">

<div class="c">
<h2>Informasi proyek</h2>
<label for="p-jd">Judul</label>
<input type="text" id="p-jd" name="judul" maxlength="150" required value="<?= h($pe['judul']) ?>">
<div class="g2">
<div><label for="p-th">Tahun</label><input type="text" id="p-th" name="tahun" maxlength="20" value="<?= h($pe['tahun']) ?>"></div>
<div><label for="p-un">Untuk (klien atau instansi)</label><input type="text" id="p-un" name="untuk" maxlength="80" placeholder="mis. sebuah sekolah" value="<?= h($pe['untuk']) ?>"></div>
</div>
<label for="p-kt">Kategori (opsional)</label>
<input type="text" id="p-kt" name="kategori" maxlength="40" list="p-dk" value="<?= h($pe['kategori']) ?>"><datalist id="p-dk"><?php foreach (array_keys($katP) as $kk): ?><option value="<?= h($kk) ?>"><?php endforeach; ?></datalist>
<label for="p-rg">Ringkasan (satu kalimat, tampil di bawah judul)</label>
<textarea id="p-rg" name="ringkasan" maxlength="300" rows="2"><?= h($pe['ringkasan']) ?></textarea>
</div>

<div class="c">
<h2>Foto</h2>
<p class="pr-tip">Foto pertama menjadi foto utama. Anda bisa menambah sampai <?= POR_MAX_FOTO ?> foto, mengubah urutannya, dan memberi keterangan tiap foto.</p>
<div class="pr-fg" id="pf-grid"></div>
<input type="file" id="pf-file" accept="image/*" multiple>
<p class="st2" id="pf-info"></p>
</div>

<div class="c">
<h2>Keterangan</h2>
<label for="p-kg">Cerita proyek (pisahkan paragraf dengan baris kosong)</label>
<textarea id="p-kg" name="keterangan" maxlength="4000" rows="7"><?= h($pe['keterangan']) ?></textarea>
<div class="g2">
<div><label for="p-sb">Sebelum</label><textarea id="p-sb" name="sebelum" maxlength="500" rows="4"><?= h($pe['sebelum']) ?></textarea></div>
<div><label for="p-sk">Sekarang</label><textarea id="p-sk" name="sekarang" maxlength="500" rows="4"><?= h($pe['sekarang']) ?></textarea></div>
</div>
<label for="p-tt">Tautan proyek (opsional, https://...)</label>
<input type="text" id="p-tt" name="tautan" maxlength="300" value="<?= h($pe['tautan']) ?>">
</div>

<div class="c">
<h2>Hasil</h2>
<div id="ph-list"></div>
<button type="button" class="bt o" id="ph-add"><?= ikon('tambah', 18) ?>Tambah hasil</button>
</div>

<div class="c">
<h2>Alat yang digunakan</h2>
<div id="pa-list"></div>
<input type="file" id="pa-file" accept="image/*" hidden>
<button type="button" class="bt o" id="pa-add"><?= ikon('tambah', 18) ?>Tambah alat</button>
<p class="st2" id="pa-info"></p>
</div>

<div class="c">
<h2>Penerbitan</h2>
<label for="p-tp">Tampilan di situs</label>
<select id="p-tp" name="tampil"><option value="1"<?= !empty($pe['tampil']) ? ' selected' : '' ?>>Tampilkan</option><option value="0"<?= empty($pe['tampil']) ? ' selected' : '' ?>>Sembunyikan (draf)</option></select>
<div class="row" style="margin-top:12px">
<button class="bt" type="submit"><?= ikon('simpan', 18) ?>Simpan</button>
<a class="bt o" href="./?m=portofolio">Batal</a>
<span class="st2 sp" id="p-st"></span>
</div>
</div>
</form>

<script>
(function () {
  var CSRF = <?= json_encode($csrf) ?>;
  var MAXF = <?= (int)POR_MAX_FOTO ?>, MAXH = <?= (int)POR_MAX_HASIL ?>, MAXA = <?= (int)POR_MAX_ALAT ?>;
  var FOTO = <?= json_encode(array_values((array)$pe['foto']), $js) ?>;
  var HASIL = <?= json_encode(array_values((array)$pe['hasil']), $js) ?>;
  var ALAT = <?= json_encode(array_values((array)$pe['alat']), $js) ?>;
  var $ = function (i) { return document.getElementById(i); };
  var IK = {
    naik: <?= json_encode(ikon('naik', 16)) ?>, turun: <?= json_encode(ikon('turun', 16)) ?>,
    hapus: <?= json_encode(ikon('hapus', 16)) ?>, gambar: <?= json_encode(ikon('gambar', 16)) ?>
  };

  function el(tag, attrs, html) {
    var e = document.createElement(tag);
    if (attrs) { for (var k in attrs) { e.setAttribute(k, attrs[k]); } }
    if (html != null) { e.innerHTML = html; }
    return e;
  }
  function thumb(u) { return u.indexOf('/admin/media/m_') === 0 ? u.replace('/m_', '/s_') : u; }
  function gambar(u) {
    var i = el('img', { alt: '', loading: 'lazy', src: thumb(u) });
    i.onerror = function () { i.onerror = null; i.src = u; };
    return i;
  }
  function ib(label, html, fn, cls) {
    var b = el('button', { type: 'button', 'class': 'ib' + (cls ? ' ' + cls : ''), title: label, 'aria-label': label }, html);
    b.addEventListener('click', fn);
    return b;
  }

  /* ----- unggah foto (diperkecil di browser, sama seperti editor artikel) ----- */
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
  function satuFoto(file, cb) {
    kecilkan(file, 1600, function (b) {
      if (!b) { cb({ err: 'Gambar tidak bisa dibaca.' }); return; }
      unggah(b, cb);
    });
  }

  /* ----- galeri foto ----- */
  function gFoto() {
    var g = $('pf-grid'); g.innerHTML = '';
    FOTO.forEach(function (f, i) {
      var k = el('div', { 'class': 'pr-f' });
      var im = el('div', { 'class': 'im' });
      im.appendChild(gambar(f.url));
      im.appendChild(el('span', { 'class': 'no' }, i === 0 ? 'Utama' : String(i + 1)));
      var bd = el('div', { 'class': 'bd' });
      var h1 = el('input', { type: 'hidden', name: 'foto_url[]', value: f.url });
      var ket = el('input', { type: 'text', name: 'foto_ket[]', maxlength: '160', placeholder: 'Keterangan foto', 'aria-label': 'Keterangan foto ' + (i + 1) });
      ket.value = f.ket || '';
      ket.addEventListener('input', function () { f.ket = ket.value; });
      var ac = el('div', { 'class': 'ac' });
      if (i > 0) { ac.appendChild(ib('Geser maju', IK.naik.replace('<svg', '<svg style="transform:rotate(-90deg)"'), function () { FOTO.splice(i - 1, 0, FOTO.splice(i, 1)[0]); gFoto(); })); }
      if (i < FOTO.length - 1) { ac.appendChild(ib('Geser mundur', IK.turun.replace('<svg', '<svg style="transform:rotate(-90deg)"'), function () { FOTO.splice(i + 1, 0, FOTO.splice(i, 1)[0]); gFoto(); })); }
      ac.appendChild(ib('Hapus foto', IK.hapus, function () { FOTO.splice(i, 1); gFoto(); }, 'x'));
      bd.appendChild(h1); bd.appendChild(ket); bd.appendChild(ac);
      k.appendChild(im); k.appendChild(bd);
      g.appendChild(k);
    });
    $('pf-file').disabled = FOTO.length >= MAXF;
  }
  $('pf-file').addEventListener('change', function () {
    var fs = Array.prototype.slice.call(this.files || []); this.value = '';
    var sisa = MAXF - FOTO.length, info = $('pf-info');
    if (!fs.length) return;
    if (fs.length > sisa) { info.textContent = 'Hanya ' + sisa + ' foto lagi yang muat. Sisanya diabaikan.'; fs = fs.slice(0, sisa); }
    var n = 0, gagal = '';
    (function lanjut() {
      if (n >= fs.length) { info.textContent = gagal; return; }
      info.textContent = 'Mengunggah foto ' + (n + 1) + ' dari ' + fs.length + '...';
      satuFoto(fs[n], function (r) {
        if (r.ok) { FOTO.push({ url: r.url, ket: '' }); gFoto(); } else { gagal = r.err || 'Gagal mengunggah.'; }
        n++; lanjut();
      });
    })();
  });

  /* ----- hasil ----- */
  function gHasil() {
    var w = $('ph-list'); w.innerHTML = '';
    HASIL.forEach(function (h, i) {
      var r = el('div', { 'class': 'pr-r' });
      var j = el('input', { type: 'text', name: 'hasil_judul[]', maxlength: '60', placeholder: 'Judul singkat, mis. Data terpusat', 'aria-label': 'Judul hasil ' + (i + 1) }); j.value = h.judul || '';
      var s = el('textarea', { name: 'hasil_isi[]', maxlength: '240', rows: '2', placeholder: 'Penjelasan', 'aria-label': 'Isi hasil ' + (i + 1) }); s.value = h.isi || '';
      j.addEventListener('input', function () { h.judul = j.value; });
      s.addEventListener('input', function () { h.isi = s.value; });
      var ac = el('div', { 'class': 'ac' }); ac.appendChild(el('span', { 'class': 'st2' }, 'Hasil ' + (i + 1)));
      ac.appendChild(ib('Hapus hasil', IK.hapus, function () { HASIL.splice(i, 1); gHasil(); }, 'x'));
      r.appendChild(j); r.appendChild(s); r.appendChild(ac);
      w.appendChild(r);
    });
    $('ph-add').disabled = HASIL.length >= MAXH;
  }
  $('ph-add').addEventListener('click', function () { if (HASIL.length < MAXH) { HASIL.push({ judul: '', isi: '' }); gHasil(); } });

  /* ----- alat ----- */
  var alatIdx = -1;
  function gAlat() {
    var w = $('pa-list'); w.innerHTML = '';
    ALAT.forEach(function (a, i) {
      var r = el('div', { 'class': 'pr-r' });
      var n = el('input', { type: 'text', name: 'alat_nama[]', maxlength: '60', placeholder: 'Nama alat', 'aria-label': 'Nama alat ' + (i + 1) }); n.value = a.nama || '';
      var f = el('textarea', { name: 'alat_fungsi[]', maxlength: '200', rows: '2', placeholder: 'Fungsinya apa', 'aria-label': 'Fungsi alat ' + (i + 1) }); f.value = a.fungsi || '';
      n.addEventListener('input', function () { a.nama = n.value; });
      f.addEventListener('input', function () { a.fungsi = f.value; });
      var g = el('input', { type: 'hidden', name: 'alat_gambar[]', value: a.gambar || '' });
      var ac = el('div', { 'class': 'ac' });
      var mini = el('div', { 'class': 'mini' });
      if (a.gambar) { mini.appendChild(gambar(a.gambar)); }
      var bt = el('button', { type: 'button', 'class': 'bt o' }, IK.gambar + (a.gambar ? 'Ganti foto' : 'Tambah foto'));
      bt.addEventListener('click', function () { alatIdx = i; $('pa-file').click(); });
      mini.appendChild(bt);
      if (a.gambar) { mini.appendChild(ib('Hapus foto alat', IK.hapus, function () { a.gambar = ''; gAlat(); }, 'x')); }
      ac.appendChild(mini);
      ac.appendChild(ib('Hapus alat', IK.hapus, function () { ALAT.splice(i, 1); gAlat(); }, 'x'));
      r.appendChild(n); r.appendChild(f); r.appendChild(g); r.appendChild(ac);
      w.appendChild(r);
    });
    $('pa-add').disabled = ALAT.length >= MAXA;
  }
  $('pa-add').addEventListener('click', function () { if (ALAT.length < MAXA) { ALAT.push({ nama: '', fungsi: '', gambar: '' }); gAlat(); } });
  $('pa-file').addEventListener('change', function () {
    var f = this.files && this.files[0]; this.value = '';
    if (!f || alatIdx < 0) return;
    var i = alatIdx, info = $('pa-info');
    info.textContent = 'Mengunggah foto alat...';
    satuFoto(f, function (r) {
      if (r.ok && ALAT[i]) { ALAT[i].gambar = r.url; info.textContent = ''; gAlat(); } else { info.textContent = r.err || 'Gagal mengunggah.'; }
    });
  });

  /* ----- peringatan sebelum pergi dan tombol simpan ganda ----- */
  var kotor = false, kirim = false, fm = $('fpor');
  fm.addEventListener('input', function () { kotor = true; });
  fm.addEventListener('click', function (e) { if (e.target.closest && e.target.closest('.ib,.bt.o,#pf-file')) { kotor = true; } });
  fm.addEventListener('submit', function () { kirim = true; var b = fm.querySelector('button[type=submit]'); if (b) { b.disabled = true; } });
  window.addEventListener('beforeunload', function (e) { if (kotor && !kirim) { e.preventDefault(); e.returnValue = ''; } });

  gFoto(); gHasil(); gAlat();
})();
</script>

<?php else: ?>
<div class="fl">
<?php if (!$sudahImpor): ?>
<form method="post" onsubmit="return confirm('Impor proyek bawaan (sistem absensi digital) ke daftar ini?')"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="por_impor"><button class="bt o" type="submit"><?= ikon('unduh', 18) ?>Impor proyek bawaan</button></form>
<?php endif; ?>
<a class="bt sp" href="./?m=portofolio&amp;e=baru"><?= ikon('tambah', 18) ?>Proyek baru</a>
</div>
<div class="c p0">
<?php if (!$semuaPor): ?>
<p class="m" style="padding:18px;margin:0">Belum ada proyek di admin. Halaman portofolio di situs masih memakai isi bawaan. Klik Impor proyek bawaan untuk mulai dari proyek yang sudah ada, atau Proyek baru untuk menulis dari awal.</p>
<?php else: ?>
<table class="tbl">
<thead><tr><th>Proyek</th><th>Kategori</th><th>Status</th><th><span class="sr">Aksi</span></th></tr></thead>
<tbody>
<?php foreach ($semuaPor as $i => $x): $f0 = (string)(($x['foto'][0]['url'] ?? '')); $th = $f0 !== '' ? art_thumb($f0) : ''; $on = !empty($x['tampil']); $nf = count((array)($x['foto'] ?? [])); ?>
<tr>
<td class="t-j"><div class="tj">
<?php if ($th !== ''): ?><img src="<?= h($th) ?>" alt="" loading="lazy" width="40" height="40"><?php else: ?><span class="nt"><?= ikon('gambar', 20) ?></span><?php endif; ?>
<div class="bd"><b><?= h($x['judul']) ?></b><small class="mo"><span class="tg<?= $on ? ' on' : '' ?>"><?= $on ? 'Tampil' : 'Disembunyikan' ?></span><?= h(trim(($x['tahun'] ?? '') . ', ' . $nf . ' foto', ', ')) ?></small></div>
</div></td>
<td class="t-k"><?= h(($x['kategori'] ?? '') !== '' ? $x['kategori'] : '-') ?></td>
<td class="t-s"><span class="tg<?= $on ? ' on' : '' ?>"><?= $on ? 'Tampil' : 'Disembunyikan' ?></span></td>
<td class="t-a"><div class="ac">
<?php if ($i > 0): ?><?= btn($csrf, 'por_naik', $x['id'], 'naik', 'Naik') ?><?php endif; ?>
<?php if ($i < count($semuaPor) - 1): ?><?= btn($csrf, 'por_turun', $x['id'], 'turun', 'Turun') ?><?php endif; ?>
<?= btn($csrf, 'por_toggle', $x['id'], $on ? 'mata-tutup' : 'mata', $on ? 'Sembunyikan' : 'Tampilkan') ?>
<a class="ib" href="./?m=portofolio&amp;e=<?= h($x['id']) ?>" title="Ubah" aria-label="Ubah"><?= ikon('ubah', 18) ?></a>
<?= btn($csrf, 'por_hapus', $x['id'], 'hapus', 'Hapus', 'x', 'Hapus proyek ini?') ?>
</div></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
<p class="tf"><?= count($semuaPor) ?> proyek. Urutan di sini sama dengan urutan di situs. Lihat hasilnya di <a href="/proyek" target="_blank" rel="noopener">halaman portofolio</a>.</p>
</div>
<?php endif; ?>
