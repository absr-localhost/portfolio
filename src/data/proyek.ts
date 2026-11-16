export interface Alat {
  nama: string;
  fungsi: string;
  gambar: string;
}

export interface Hasil {
  judul: string;
  isi: string;
}

export interface Proyek {
  judul: string;
  tahun: string;
  untuk: string;
  ringkasan: string;
  sebelum: string;
  sekarang: string;
  hasil: Hasil[];
  alat?: Alat[];
  status: string;
}

export const projects: Proyek[] = [
  {
    judul: 'Sistem absensi digital berbasis kode QR',
    tahun: '2023',
    untuk: 'sebuah sekolah',
    ringkasan: 'Kehadiran siswa tercatat otomatis hanya dengan memindai kartu pelajar.',
    sebelum: 'Absensi dicatat manual di banyak buku, sehingga rekap kehadiran memakan waktu.',
    sekarang: 'Siswa memindai kode QR pada kartu pelajar. Data kehadiran langsung masuk ke spreadsheet.',
    hasil: [
      { judul: 'Data terpusat', isi: 'Kehadiran terkumpul otomatis di satu spreadsheet.' },
      { judul: 'Rekap lebih cepat', isi: 'Rekap tidak perlu diinput ulang.' },
      { judul: 'Siap cetak', isi: 'Laporan dapat dicetak kapan saja.' },
    ],
    alat: [
      {
        nama: 'Pembuat kode QR',
        fungsi: 'Membuat kode QR yang dicetak pada kartu pelajar.',
        gambar: '/gambar/absensi-qr.webp',
      },
      {
        nama: 'Kartu pelajar',
        fungsi: 'Desain kartu pelajar lengkap dengan kode QR.',
        gambar: '/gambar/absensi-kartu.webp',
      },
      {
        nama: 'Scan-IT to Office',
        fungsi: 'Memindai kartu dan mengisi kode, tanggal, dan jam secara otomatis.',
        gambar: '/gambar/absensi-scanit.webp',
      },
      {
        nama: 'Google Spreadsheet',
        fungsi: 'Menyimpan data kehadiran sebagai dasar rekap.',
        gambar: '/gambar/absensi-sheets.webp',
      },
    ],
    status: 'Masih digunakan hingga sekarang.',
  },
  {
    judul: 'Desain kartu pelajar dengan kode QR',
    tahun: '2023',
    untuk: 'sebuah sekolah',
    ringkasan: 'Setiap siswa memiliki kartu dengan ID sendiri yang dapat dipakai untuk absensi digital.',
    sebelum: 'Sekolah belum memiliki kartu pelajar.',
    sekarang: 'Setiap kartu memuat kode QR yang menjadi bagian dari sistem absensi digital.',
    hasil: [
      { judul: 'ID pribadi', isi: 'Setiap siswa memiliki kartu dengan ID sendiri.' },
      { judul: 'Terhubung ke absensi', isi: 'Kartu dapat langsung dipakai untuk absensi digital.' },
    ],
    status: 'Masih digunakan hingga sekarang.',
  },
];
