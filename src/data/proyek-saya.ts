// Uraian pengalaman (sudut pandang orang pertama) untuk tiap item proyek.
// Kuncinya HARUS sama persis dengan judul item di profil.ts (projects) dan layanan-detail.ts.
// Dipakai oleh halaman /proyek/[kategori]/[item].
export interface Saya {
  pengantar: string;
  teknologi: string[];
}

const s = (pengantar: string, teknologi: string[]): Saya => ({ pengantar, teknologi });

export const proyekSaya: Record<string, Saya> = {
  // ===== Pengembangan Web & Aplikasi =====
  'Aplikasi Android': s(
    'Saya telah mengembangkan aplikasi Android dari nol: mulai dari memetakan kebutuhan pengguna, menggambar alur layar, membangun antarmuka, sampai menghubungkannya ke server lewat API. Saya menjaga aplikasinya tetap ringan agar nyaman dipakai di ponsel kelas menengah, lalu mengujinya di beberapa ukuran layar sebelum diserahkan dalam bentuk berkas pemasangan.',
    ['Android', 'Java', 'Flutter', 'Dart', 'REST API', 'MySQL'],
  ),
  'Pengembangan Website': s(
    'Saya membangun website responsif dengan struktur halaman yang jelas, tampilan konsisten, dan kode yang bersih agar mudah dirawat. Saya juga mengurus hal-hal di balik layar seperti kecepatan muat, pengaturan dasar SEO, hingga pemasangan di hosting dan domain, sehingga pemilik tinggal memakainya.',
    ['HTML', 'CSS', 'JavaScript', 'Tailwind', 'Astro', 'PHP'],
  ),
  'Sistem Informasi Terpadu': s(
    'Saya merancang dan membangun sistem informasi berbasis web yang menyatukan data yang tadinya tersebar di banyak berkas menjadi satu aplikasi. Setiap pengguna mendapat hak akses sesuai tugasnya, dan laporan yang dibutuhkan bisa dicetak langsung tanpa rekap ulang. Saya menangani semuanya, dari basis data, backend, antarmuka, sampai pemasangan di server.',
    ['PHP', 'Laravel', 'MySQL', 'SQL', 'JavaScript', 'Linux'],
  ),
  'REST API & Layanan Backend': s(
    'Saya membuat layanan backend dan REST API yang menjadi sumber data bersama bagi aplikasi web dan seluler. Fokus saya pada hal yang sering terlewat: autentikasi, pembatasan akses, validasi data masuk, pesan kesalahan yang jelas, dan dokumentasi endpoint agar tim lain bisa melanjutkan tanpa menebak-nebak.',
    ['PHP', 'Laravel', 'Python', 'MySQL', 'SQL', 'GitHub'],
  ),
  'Aplikasi Penjualan': s(
    'Saya membuat aplikasi pencatatan penjualan yang menggantikan buku catatan dan spreadsheet yang berserakan. Transaksi, data pelanggan, dan daftar produk tersimpan rapi, sehingga rekap harian maupun bulanan tersedia otomatis dan pemilik usaha bisa melihat produk mana yang laku tanpa menghitung manual.',
    ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Tailwind'],
  ),
  'Aplikasi Inventaris & Gudang': s(
    'Saya membangun aplikasi pengelolaan stok yang mencatat setiap barang masuk dan keluar, lengkap dengan peringatan saat stok mendekati batas minimum. Dengan begitu jumlah di sistem selalu sama dengan jumlah di gudang, riwayat pergerakan tiap barang bisa ditelusuri, dan laporan nilai persediaan tersedia kapan saja.',
    ['PHP', 'Laravel', 'MySQL', 'SQL', 'JavaScript'],
  ),
  'Company Profile & Landing Page': s(
    'Saya mengerjakan website profil perusahaan dan landing page yang bertujuan jelas: memperkenalkan usaha dengan meyakinkan dan mengarahkan pengunjung ke satu tindakan, misalnya menghubungi atau memesan. Saya menyusun alur halaman, merapikan tampilan di ponsel, dan memastikan halamannya cepat dimuat.',
    ['Astro', 'HTML', 'CSS', 'Tailwind', 'JavaScript', 'Cloudflare'],
  ),
  'Dashboard Laporan Interaktif': s(
    'Saya membuat dashboard yang mengubah tabel data mentah menjadi grafik dan ringkasan yang langsung terbaca. Pengguna dapat menyaring data menurut periode atau kategori, sehingga pimpinan bisa mengambil keputusan dari angka terbaru tanpa menunggu laporan disusun manual.',
    ['JavaScript', 'PHP', 'MySQL', 'Python', 'Google Sheets'],
  ),
  'Aplikasi Kasir (POS) & Struk': s(
    'Saya membangun aplikasi kasir yang mempercepat transaksi di meja kasir: pilih produk, hitung total, terima pembayaran, lalu cetak struk. Data penjualan tersimpan otomatis dan hasil akhir hari bisa dicocokkan dengan uang di laci, sehingga selisih lebih mudah dilacak.',
    ['PHP', 'MySQL', 'JavaScript', 'Android', 'Tailwind'],
  ),
  'Aplikasi Booking & Reservasi': s(
    'Saya membuat sistem pemesanan daring yang menampilkan jadwal tersedia dan mencegah pemesanan ganda. Pelanggan memilih waktu sendiri, pengelola menerima daftar reservasi yang rapi, dan pengingat bisa dikirim agar jumlah pelanggan yang tidak datang berkurang.',
    ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Telegram'],
  ),
  'Portal Berita & Blog (CMS)': s(
    'Saya membangun portal berita dan blog dengan panel pengelola, sehingga penulis bisa menerbitkan, menjadwalkan, dan mengubah artikel tanpa menyentuh kode. Situs ini sendiri memakai pendekatan yang sama: konten dikelola lewat panel admin dan tampil di halaman depan tanpa build ulang.',
    ['PHP', 'JavaScript', 'HTML', 'CSS', 'Astro', 'GitHub'],
  ),

  // ===== Aplikasi Pendidikan =====
  'Absensi Digital Murid': s(
    'Saya membuat sistem absensi murid berbasis kartu pelajar berkode QR. Setiap murid punya kartu dengan kode sendiri; cukup dipindai, kehadiran langsung masuk ke spreadsheet lengkap dengan tanggal dan jam. Rekap tidak perlu diinput ulang dan bisa dicetak kapan saja. Proyek ini nyata dan sudah berjalan di sebuah sekolah, rinciannya bisa dilihat di halaman Proyek.',
    ['Google Sheets', 'Kode QR', 'Scan-IT to Office', 'Excel', 'Android'],
  ),
  'Absensi Guru': s(
    'Saya menyusun sistem absensi guru yang mencatat jam datang dan pulang secara otomatis, lalu merekapnya per hari, per bulan, dan per guru. Kepala sekolah mendapat laporan kehadiran siap cetak tanpa menunggu tata usaha menghitung satu per satu.',
    ['Google Sheets', 'Apps Script', 'Kode QR', 'Excel', 'Android'],
  ),
  'Aplikasi Sekolah': s(
    'Saya membangun aplikasi sekolah yang menyatukan kebutuhan administrasi harian: data murid dan guru, kelas, pengumuman, dan laporan. Tujuannya sederhana, yaitu mengurangi berkas yang tercecer dan membuat informasi sekolah mudah dicari oleh siapa pun yang berhak.',
    ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Tailwind'],
  ),
  'Aplikasi Rapor Sekolah': s(
    'Saya membuat aplikasi penyusun rapor yang menerima nilai dari guru, menghitung nilai akhir dan peringkat, lalu mencetak rapor dengan format yang rapi. Pekerjaan yang biasanya memakan berhari-hari di akhir semester menjadi jauh lebih singkat dan minim salah hitung.',
    ['PHP', 'MySQL', 'Excel', 'VBA', 'Word'],
  ),
  'Aplikasi AI untuk Guru': s(
    'Saya membuat alat bantu berbasis kecerdasan buatan untuk meringankan pekerjaan administratif guru, seperti menyusun draf soal, rangkuman materi, dan komentar rapor. Guru tetap memeriksa dan mengubah hasilnya, sehingga AI berperan sebagai asisten penghemat waktu, bukan pengganti guru.',
    ['Python', 'JavaScript', 'Grok', 'Google Sheets', 'Telegram'],
  ),
  'Pendaftaran Online Sekolah': s(
    'Saya membangun formulir pendaftaran daring untuk penerimaan murid baru. Calon murid mengisi data dan mengunggah berkas dari rumah, sementara panitia menerima daftar pendaftar yang terkumpul rapi, bisa disaring, dan siap diekspor untuk proses seleksi.',
    ['PHP', 'MySQL', 'JavaScript', 'Google Sheets', 'HTML'],
  ),
  'Ujian Online (CBT) Sederhana': s(
    'Saya membuat sistem ujian berbasis komputer yang ringan dan bisa berjalan di jaringan sekolah dengan perangkat seadanya. Soal diacak, waktu dihitung, dan nilai keluar otomatis, sehingga guru tidak lagi mengoreksi lembar jawaban satu per satu.',
    ['PHP', 'MySQL', 'JavaScript', 'Linux', 'HTML'],
  ),
  'Perpustakaan Digital Sekolah': s(
    'Saya membangun aplikasi perpustakaan yang mencatat koleksi buku, anggota, serta peminjaman dan pengembalian. Pustakawan bisa langsung melihat buku yang sedang dipinjam dan yang terlambat, dan murid dapat mencari koleksi tanpa membuka lemari satu per satu.',
    ['PHP', 'Laravel', 'MySQL', 'Kode QR', 'JavaScript'],
  ),
  'Keuangan & SPP Sekolah': s(
    'Saya membuat aplikasi pencatatan SPP dan keuangan sekolah yang menunjukkan siapa sudah membayar, siapa menunggak, dan berapa total pemasukan. Bendahara menerima laporan yang rapi dan kuitansi yang bisa dicetak, sehingga pembukuan lebih mudah diperiksa.',
    ['PHP', 'MySQL', 'Excel', 'Google Sheets', 'Word'],
  ),
  'Penyusun Jadwal Pelajaran Otomatis': s(
    'Saya membuat alat penyusun jadwal pelajaran yang menyusun jam secara otomatis dengan memperhatikan ketersediaan guru, ruang kelas, dan jam yang bentrok. Pekerjaan yang dulu memakan berhari-hari dengan coba-coba kini menghasilkan draf jadwal dalam hitungan menit, tinggal disesuaikan.',
    ['Python', 'Excel', 'VBA', 'Google Sheets', 'Apps Script'],
  ),

  // ===== Automasi & Low-code =====
  'Automasi VBA': s(
    'Saya menulis makro VBA yang mengotomatiskan pekerjaan berulang di Excel, Word, dan aplikasi Office lain: merapikan data, menggabungkan berkas, dan menyusun laporan dalam satu klik. Pekerjaan yang tadinya berjam-jam menjadi hitungan detik dan hasilnya lebih konsisten.',
    ['VBA', 'Excel', 'Word', 'PowerPoint'],
  ),
  'Macro Excel Rekap Laporan': s(
    'Saya membuat makro Excel yang mengumpulkan data dari banyak lembar atau berkas lalu merekapnya menjadi satu laporan siap cetak. Admin cukup memasukkan data mentah, dan ringkasan, tabel, serta formatnya tersusun otomatis tanpa salin-tempel.',
    ['VBA', 'Excel', 'Google Sheets'],
  ),
  'Generator Dokumen Otomatis': s(
    'Saya membangun generator dokumen yang mengisi surat, sertifikat, kuitansi, atau kontrak dari data tabel. Satu templat dan satu daftar data cukup untuk menghasilkan ratusan dokumen yang rapi dan seragam, lengkap dengan nama dan nomor yang benar.',
    ['VBA', 'Word', 'Excel', 'Apps Script', 'Google Drive'],
  ),
  'Power Apps Formulir & Persetujuan': s(
    'Saya membuat aplikasi formulir dan alur persetujuan dengan Power Apps, misalnya pengajuan izin, cuti, atau permintaan barang. Pemohon mengisi dari ponsel, atasan menyetujui dengan satu ketukan, dan seluruh riwayatnya tercatat tanpa kertas.',
    ['Power Apps', 'Power Automate', 'OneDrive', 'Excel'],
  ),
  'Power Automate Alur Kerja': s(
    'Saya merancang alur kerja otomatis dengan Power Automate yang menghubungkan email, berkas, formulir, dan spreadsheet. Misalnya, berkas yang masuk otomatis disimpan di folder yang benar, dicatat, dan pemilik tugasnya langsung diberi tahu.',
    ['Power Automate', 'OneDrive', 'Excel', 'Google Drive'],
  ),
  'Power BI Dashboard': s(
    'Saya membuat dashboard Power BI yang mengubah data dari Excel atau basis data menjadi grafik interaktif. Pimpinan bisa menyaring per periode, wilayah, atau kategori, dan angka diperbarui otomatis sehingga rapat dimulai dari data yang sama.',
    ['Power BI', 'Excel', 'SQL', 'OneDrive'],
  ),
  'Balasan Chat Otomatis AI': s(
    'Saya membuat sistem balasan chat otomatis berbasis AI yang menjawab pertanyaan yang sering muncul, misalnya jam layanan, harga, dan cara pemesanan. Pertanyaan yang sulit diteruskan ke manusia, sehingga pelanggan dilayani cepat tanpa pemilik usaha harus terus memegang ponsel.',
    ['Python', 'Telegram', 'Grok', 'JavaScript', 'Apps Script'],
  ),
  'Automasi Administrasi': s(
    'Saya mengotomatiskan pekerjaan administrasi yang berulang: pengingat tenggat, pengelompokan berkas, pencatatan surat masuk dan keluar, serta rekap bulanan. Yang tadinya dikerjakan manual setiap minggu kini berjalan sendiri dan hanya perlu diperiksa sekilas.',
    ['Power Automate', 'VBA', 'Apps Script', 'Google Sheets', 'Excel'],
  ),
  'Bot Telegram Layanan Otomatis': s(
    'Saya membuat bot Telegram yang melayani permintaan secara otomatis, seperti cek status, kirim notifikasi, dan menjawab pertanyaan umum. Pengguna cukup mengirim pesan seperti biasa, tanpa perlu memasang aplikasi tambahan.',
    ['Telegram', 'Python', 'PHP', 'Google Sheets', 'Apps Script'],
  ),
  'Integrasi Google Sheets & Apps Script': s(
    'Saya menghubungkan Google Sheets dengan formulir, email, kalender, dan layanan lain memakai Apps Script. Spreadsheet berubah dari sekadar tabel menjadi sistem kecil yang bisa menerima data, mengirim notifikasi, dan menghasilkan laporan otomatis.',
    ['Google Sheets', 'Apps Script', 'JavaScript', 'Google Drive'],
  ),
  'Pengambilan & Rekap Data Terjadwal': s(
    'Saya membuat proses yang mengambil data dari sumber tertentu pada jadwal tetap, membersihkannya, lalu merekapnya ke spreadsheet atau basis data. Laporan selalu terbarui tanpa ada yang harus ingat menjalankannya.',
    ['Python', 'Google Sheets', 'Apps Script', 'MySQL', 'Linux'],
  ),

  // ===== Server, Jaringan & Dukungan IT =====
  'Administrasi Server Linux & Windows': s(
    'Saya mengelola server Linux dan Windows: instalasi, pengaturan layanan, pembaruan, pengelolaan pengguna, hingga pemantauan agar tetap sehat. Saya menjaga konfigurasinya rapi dan terdokumentasi supaya masalah mudah dilacak dan server tidak bergantung pada ingatan satu orang.',
    ['Linux', 'Windows', 'GitHub', 'Cloudflare', 'SQL'],
  ),
  'Jaringan LAN & Wi-Fi Kantor': s(
    'Saya merancang dan memasang jaringan LAN dan Wi-Fi kantor: tata letak kabel, penempatan titik akses, pembagian segmen jaringan, dan pengaturan keamanan. Hasilnya sinyal merata, koneksi stabil, dan perangkat tamu terpisah dari jaringan kerja.',
    ['Windows', 'Linux', 'Jaringan LAN', 'Wi-Fi'],
  ),
  'Helpdesk & Dukungan Teknis': s(
    'Saya menangani dukungan teknis harian: komputer yang lambat, printer yang macet, akun yang terkunci, hingga aplikasi yang error. Setiap masalah dicatat beserta solusinya, sehingga kasus yang sama berikutnya bisa diselesaikan lebih cepat dan pengguna tidak perlu menjelaskan dari awal.',
    ['Windows', 'Linux', 'Word', 'Excel', 'Telegram'],
  ),
  'Backup & Pemulihan Data': s(
    'Saya menyusun strategi cadangan data yang berjalan otomatis dan benar-benar bisa dipulihkan. Saya tidak berhenti pada membuat salinan, tetapi juga menguji pemulihannya secara berkala, karena cadangan yang tidak pernah diuji belum bisa dipercaya.',
    ['Linux', 'Windows', 'Google Drive', 'OneDrive', 'SQL'],
  ),
  'Manajemen Akun & Hak Akses': s(
    'Saya mengatur akun pengguna dan hak akses agar setiap orang hanya bisa membuka yang memang menjadi tugasnya. Akun karyawan yang keluar segera ditutup, kata sandi dikelola dengan aman, dan perubahan akses tercatat.',
    ['Windows', 'Linux', 'Google Drive', 'OneDrive'],
  ),
  'Monitoring Server & Uptime': s(
    'Saya memasang pemantauan yang mengecek kondisi server dan ketersediaan layanan secara berkala, lalu mengirim peringatan saat ada yang bermasalah. Gangguan diketahui lebih dulu oleh saya daripada oleh pelanggan, dan penyebabnya lebih mudah dilacak lewat riwayatnya.',
    ['Linux', 'Python', 'Telegram', 'Cloudflare'],
  ),
  'Manajemen Aset & Inventaris IT': s(
    'Saya mendata seluruh perangkat IT kantor: komputer, printer, jaringan, dan lisensi, lengkap dengan lokasi, pemegang, dan kondisinya. Dengan data itu, perencanaan penggantian, pengecekan garansi, dan audit aset menjadi jauh lebih mudah.',
    ['Excel', 'Google Sheets', 'Kode QR', 'Windows'],
  ),

  // ===== Keamanan Siber (Berizin & Defensif) =====
  'Audit Keamanan Website (Berizin)': s(
    'Saya melakukan audit keamanan website hanya dengan izin tertulis dari pemiliknya. Saya memeriksa celah umum pada konfigurasi, autentikasi, dan penanganan data, lalu menyusun laporan yang menjelaskan temuan, tingkat risikonya, dan langkah perbaikan yang jelas.',
    ['Linux', 'PHP', 'JavaScript', 'Cloudflare', 'SQL'],
  ),
  'Penilaian Kerentanan Jaringan': s(
    'Saya menilai kerentanan jaringan dengan izin tertulis: memetakan perangkat dan layanan yang terbuka, mencari konfigurasi lemah dan perangkat lunak usang, lalu mengurutkan temuan berdasarkan risiko supaya perbaikan dikerjakan mulai dari yang paling mendesak.',
    ['Linux', 'Windows', 'Python', 'Jaringan LAN'],
  ),
  'Hardening Server & Firewall': s(
    'Saya memperkuat server dengan menutup layanan yang tidak dipakai, membatasi akses, memperbarui perangkat lunak, dan menyetel firewall agar hanya lalu lintas yang dibutuhkan yang diizinkan. Hasilnya permukaan serangan lebih kecil tanpa mengganggu layanan yang berjalan.',
    ['Linux', 'Windows', 'Cloudflare', 'SQL'],
  ),
  'Dashboard OSINT Defensif': s(
    'Saya membuat dashboard yang mengumpulkan informasi dari sumber terbuka untuk kepentingan defensif, misalnya memantau apakah domain atau data milik organisasi muncul di tempat yang tidak semestinya. Tujuannya mendeteksi paparan lebih awal agar bisa ditindak sebelum disalahgunakan.',
    ['Python', 'JavaScript', 'Google Sheets', 'Telegram'],
  ),
  'Rekayasa Jaringan': s(
    'Saya merancang arsitektur jaringan dengan keamanan sebagai pertimbangan sejak awal: pemisahan segmen, kontrol akses antarsegmen, dan jalur akses jarak jauh yang aman. Rancangan ini membuat satu perangkat yang bermasalah tidak langsung membahayakan seluruh jaringan.',
    ['Linux', 'Windows', 'Jaringan LAN', 'Wi-Fi', 'Cloudflare'],
  ),
  'Simulasi Kesadaran Phishing': s(
    'Saya menyelenggarakan simulasi phishing yang terkendali dan berizin untuk melatih kewaspadaan karyawan. Hasilnya dipakai untuk belajar, bukan untuk menyalahkan: saya menyajikan ringkasan tanpa mempermalukan individu dan menindaklanjutinya dengan pelatihan singkat.',
    ['Python', 'Google Sheets', 'Word', 'Telegram'],
  ),
  'Analisis Malware Defensif': s(
    'Saya menganalisis berkas mencurigakan secara defensif di lingkungan terisolasi untuk memahami apa yang dilakukannya dan bagaimana mencegah dampaknya. Keluarannya berupa tanda-tanda infeksi dan langkah pembersihan, bukan perangkat yang dapat dipakai menyerang.',
    ['Linux', 'Windows', 'Python'],
  ),
  'SOP Keamanan & Respons Insiden': s(
    'Saya menyusun prosedur keamanan dan alur respons insiden yang tertulis dan realistis: siapa dihubungi, apa yang diisolasi lebih dulu, bagaimana bukti diamankan, dan kapan layanan boleh dipulihkan. Saat insiden terjadi, tim tidak perlu panik dan menebak langkah berikutnya.',
    ['Word', 'Excel', 'Google Drive', 'Telegram'],
  ),
  'Pelatihan Kesadaran Keamanan Siber': s(
    'Saya mengajar kesadaran keamanan siber dengan bahasa yang mudah dipahami orang non-teknis: mengenali phishing, mengelola kata sandi, mengamankan ponsel, dan melaporkan hal yang mencurigakan. Latar belakang pendidikan saya membantu menyampaikannya dengan contoh yang dekat dengan kerja sehari-hari peserta.',
    ['PowerPoint', 'Word', 'Google Classroom', 'Telegram'],
  ),
  'Pemantauan Log & Deteksi Anomali': s(
    'Saya mengumpulkan dan membaca log dari server dan aplikasi untuk menemukan pola yang tidak wajar, seperti percobaan masuk berulang atau akses di jam yang janggal. Peringatan dikirim ketika pola itu muncul, sehingga tindakan bisa diambil sebelum menjadi insiden.',
    ['Linux', 'Python', 'Telegram', 'SQL'],
  ),

  // ===== Multimedia =====
  'Desain Grafis & Identitas Visual': s(
    'Saya mengerjakan desain grafis dan identitas visual: logo, palet warna, pilihan huruf, dan aturan pemakaiannya. Tujuannya agar sebuah usaha atau lembaga tampil konsisten di kartu nama, media sosial, spanduk, maupun situs web, bukan sekadar tampak bagus sekali jadi.',
    ['Desain grafis', 'Logo', 'PowerPoint', 'Word'],
  ),
  'Editing Video Promosi & Profil': s(
    'Saya menyunting video promosi dan profil dari bahan mentah menjadi cerita yang rapi: memilih adegan, menyusun alur, menambahkan teks dan musik, dan mengatur ukuran untuk tiap platform. Hasilnya singkat, jelas, dan sesuai citra yang ingin ditampilkan.',
    ['Editing video', 'Motion graphic', 'Desain grafis'],
  ),
  'Konten Edukasi Multimedia': s(
    'Saya membuat konten belajar yang memadukan gambar, animasi, suara, dan teks agar materi sulit menjadi lebih mudah dipahami. Latar belakang saya sebagai pendidik membantu menyusun urutan penjelasan yang memang cocok untuk pemula.',
    ['PowerPoint', 'Editing video', 'Google Classroom', 'Desain grafis'],
  ),
  'Konten Media Sosial & Motion Graphic': s(
    'Saya membuat konten media sosial dan motion graphic yang singkat dan menarik perhatian dalam beberapa detik pertama. Setiap konten disesuaikan dengan ukuran dan gaya platformnya, dengan tampilan yang seragam supaya akun terlihat konsisten.',
    ['Motion graphic', 'Desain grafis', 'Editing video'],
  ),
  'Dokumentasi Foto & Video Kegiatan': s(
    'Saya mendokumentasikan kegiatan lewat foto dan video, lalu menyuntingnya menjadi arsip dan bahan publikasi yang rapi. Hasilnya bisa dipakai untuk laporan, situs web, dan media sosial, dengan penamaan dan penyimpanan yang tertata agar mudah dicari kembali.',
    ['Fotografi', 'Editing video', 'Google Drive', 'Desain grafis'],
  ),

  // ===== Konsultasi & Produk Digital =====
  'Konsultasi Transformasi Digital': s(
    'Saya membantu menganalisis cara kerja yang ada, menemukan bagian yang paling menghabiskan waktu, lalu merekomendasikan solusi yang tepat guna dan sesuai anggaran. Saya lebih memilih langkah kecil yang benar-benar dipakai daripada sistem besar yang akhirnya tidak disentuh.',
    ['Excel', 'Google Sheets', 'Power Automate', 'Laravel', 'Word'],
  ),
  'Produk Digital Mandiri': s(
    'Saya mengembangkan produk digital milik sendiri, dari ide, perancangan, pembuatan, sampai peluncuran dan perawatannya. Pengalaman ini membuat saya memahami seluruh siklus produk dan membantu saya memberi saran yang realistis kepada orang lain.',
    ['Astro', 'PHP', 'Android', 'Flutter', 'Cloudflare', 'GitHub'],
  ),
  'Prototipe & MVP Startup': s(
    'Saya membantu mewujudkan ide startup menjadi prototipe dan produk minimum yang bisa diuji ke pengguna sungguhan dengan cepat. Saya memangkas fitur sampai tinggal inti yang menjawab masalah, agar ide divalidasi sebelum biaya besar dikeluarkan.',
    ['Flutter', 'React', 'Laravel', 'PHP', 'MySQL', 'Cloudflare'],
  ),
  'Pelatihan & Workshop IT': s(
    'Saya mengajar pelatihan dan workshop IT dengan praktik langsung, mulai dari Excel dan VBA, automasi, hingga dasar web dan keamanan. Materi disusun bertahap dan bahasanya sederhana, karena saya terbiasa menjelaskan hal teknis kepada orang yang baru mengenalnya.',
    ['Excel', 'VBA', 'Google Sheets', 'PowerPoint', 'Google Classroom'],
  ),
};
