export interface Proyek {
  judul: string;
  tahun: string;
  untuk: string;
  masalah: string;
  dibuat: string;
  hasil: string;
  teknologi: string[];
  status: string;
  gambar?: { src: string; alt: string; keterangan: string; lebar: number; tinggi: number };
}

export const projects: Proyek[] = [
  {
    judul: 'Pembuatan absensi digital',
    tahun: '2023',
    untuk: 'sebuah sekolah',
    masalah: 'Absensi masih dicatat manual, sehingga rekap memakan waktu dan datanya tersebar di banyak buku.',
    dibuat:
      'Sistem absensi digital otomatis. Kehadiran dipindai dari kode QR di kartu pelajar, lalu datanya langsung tersambung ke spreadsheet.',
    hasil: 'Data kehadiran terkoneksi otomatis ke basis data dan dapat dicetak kapan saja.',
    teknologi: ['Kartu pelajar digital', 'Scan IT to Office', 'QR Code', 'Google Spreadsheet', 'Microsoft Excel'],
    status: 'Masih dipakai sebagai investasi jangka panjang.',
    gambar: {
      src: '/gambar/absensi-scan.webp',
      alt: 'Tampilan aplikasi pemindai untuk data Kelas 11 dengan kolom kode, tanggal, dan jam',
      keterangan: 'Aplikasi pemindai untuk data Kelas 11. Kolom kode, tanggal, dan jam terisi otomatis saat kartu dipindai.',
      lebar: 640,
      tinggi: 1281,
    },
  },
  {
    judul: 'Desain kartu pelajar dengan QR code',
    tahun: '2023',
    untuk: 'sebuah sekolah',
    masalah: 'Sekolah belum memiliki kartu pelajar.',
    dibuat:
      'Desain kartu pelajar lengkap dengan kode QR pada tiap kartu. Kode itu menjadi bagian dari sistem absensi digital.',
    hasil: 'Setiap murid memiliki kartu dengan ID sendiri yang bisa dipakai untuk absensi digital.',
    teknologi: ['Kartu pelajar digital', 'Scan IT to Office', 'QR Code', 'Google Spreadsheet', 'Microsoft Excel'],
    status: 'Masih dipakai sebagai investasi masa depan.',
  },
];
