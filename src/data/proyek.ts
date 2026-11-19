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
}

export const projects: Proyek[] = [
  {
    judul: 'Sistem absensi digital dan kartu pelajar berkode QR',
    tahun: '2023',
    untuk: 'sebuah sekolah',
    ringkasan: 'Kartu pelajar berkode QR yang mencatat kehadiran siswa secara otomatis.',
    sebelum:
      'Sekolah belum memiliki kartu pelajar, dan absensi dicatat manual di banyak buku sehingga rekap kehadiran memakan waktu.',
    sekarang:
      'Setiap siswa memiliki kartu pelajar dengan kode QR sendiri. Cukup dipindai, kehadiran langsung masuk ke spreadsheet.',
    hasil: [
      { judul: 'ID pribadi', isi: 'Setiap siswa memiliki kartu pelajar dengan ID sendiri.' },
      { judul: 'Data terpusat', isi: 'Kehadiran terkumpul otomatis di satu spreadsheet.' },
      { judul: 'Rekap siap cetak', isi: 'Rekap tidak perlu diinput ulang dan laporan dapat dicetak kapan saja.' },
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
  },
];
