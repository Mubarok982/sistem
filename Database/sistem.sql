-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Sep 2025 pada 08.06
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`) VALUES
(1, 'admin', 'admin123', 'Admin UNIMMA');

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun_dosen`
--

CREATE TABLE `akun_dosen` (
  `nip` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akun_dosen`
--

INSERT INTO `akun_dosen` (`nip`, `password`) VALUES
('1101', 'dGfnXx'),
('1102', 'yqMjNS'),
('1103', 'g5dyTo'),
('1104', 'eBm5Uv'),
('2201', 'a1yxXr'),
('2202', 'HLvZs4');

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun_mahasiswa`
--

CREATE TABLE `akun_mahasiswa` (
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status_skripsi` enum('belum','sudah') DEFAULT 'belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akun_mahasiswa`
--

INSERT INTO `akun_mahasiswa` (`npm`, `nama`, `password`, `status_skripsi`) VALUES
('1001', 'Alvin Candra', '1', 'sudah'),
('1002', 'Berno Falalangi', '2', 'sudah'),
('1003', 'Ikhbal Khasodiq', '3', 'sudah'),
('1004', 'Helmi Naufal', '4', 'sudah'),
('1007', 'Budi Santoso', '7', 'sudah'),
('2001', 'Elin Herlina', '1', 'sudah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `biodata_dosen`
--

CREATE TABLE `biodata_dosen` (
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `biodata_dosen`
--

INSERT INTO `biodata_dosen` (`nip`, `nama`, `prodi`, `no_hp`, `foto`) VALUES
('1101', 'Rini Hartati, S.kom., M.t.', 'Teknik Informatika', '085867746180', '688f02963fe5e_wallpaperflare.com_wallpaper(106).jpg'),
('1102', 'Fajar Mahendra, S.Kom., M.Kom', 'Teknik Informatika', '085867746180', '688716f5c96f8_desktop-wallpaper-splashes-of-color-miscellaneous-cool-fluorescent-paint-color.jpg'),
('1103', 'Irfan Hidayat, S.Kom., M.Cs.', 'Teknik Informatika', '085867746180', ''),
('1104', 'Budi Santoso, S.kom', 'Teknik Informatika', '0895621103215', ''),
('2201', 'Raden Gunawan, S.T., M.T.', 'Teknik Industri', '085867746180', ''),
('2202', 'Dewi Anggraini, S.T.', 'Teknik Industri', '085867746180', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `biodata_mahasiswa`
--

CREATE TABLE `biodata_mahasiswa` (
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `judul_skripsi` text NOT NULL,
  `nip_pembimbing1` varchar(30) NOT NULL,
  `nip_pembimbing2` varchar(30) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `biodata_mahasiswa`
--

INSERT INTO `biodata_mahasiswa` (`npm`, `nama`, `no_hp`, `prodi`, `judul_skripsi`, `nip_pembimbing1`, `nip_pembimbing2`, `foto`) VALUES
('1001', 'Alvin Candra', '085867746180', 'Teknik Informatika', 'Penerapan Algoritma K-Means untuk Klasifikasi Data Mahasiswa Berdasarkan Minat Studi', '1101', '1102', '1001_1754377569.jpg'),
('1002', 'Berno Falalangi', '0895621103215', 'Teknik Informatika', 'Analisis Performa Jaringan dengan Metode Load Balancing pada Sistem Terdistribusi', '1103', '1101', '1002_1752651461.jpg'),
('1003', 'Ikhbal Khasodiq', '0895621103215', 'Teknik Informatika', 'Prediksi Harga Saham Menggunakan Algoritma Long Short-Term Memory (LSTM)', '1101', '1103', '1003_1754206567.jpg'),
('1007', 'Budi Santoso', '085867746180', 'Teknik Informatika', 'Sistem Monitoring Skripsi Berbasis Website', '1101', '1103', '1007_1754470352.jpg'),
('2001', 'Elin Herlina', '085867746180', 'Teknik Industri', 'Perancangan Tata Letak Fasilitas Gudang dengan Menggunakan Metode Activity Relationship Chart (ARC) untuk Mengurangi Material Handling', '2201', '2202', '2001_1756170928.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa_skripsi`
--

CREATE TABLE `mahasiswa_skripsi` (
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `prodi` varchar(100) NOT NULL,
  `semester` int(2) NOT NULL,
  `periode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa_skripsi`
--

INSERT INTO `mahasiswa_skripsi` (`npm`, `nama`, `prodi`, `semester`, `periode`) VALUES
('1001', 'Alvin Candra', 'Teknik Informatika', 8, '2025/2026 (Genap)'),
('1002', 'Berno Falalangi', 'Teknik Informatika', 8, '2025/2026 (Genap)'),
('1003', 'Ikhbal Khasodiq', 'Teknik Informatika', 8, '2025/2026 (Genap)'),
('1004', 'Helmi Naufal', 'Teknik Informatika', 8, '2025/2026 (Genap)'),
('1005', 'Herfiandika', 'Teknik Informatika', 8, '2025/2026 (Genap)'),
('1006', 'Reynaldi Ahmad', 'Teknik Informatika', 8, '2025/2026 (Genap)'),
('1007', 'Budi Santoso', 'Teknik Informatika', 7, '2025/2026 (Genap)'),
('2001', 'Elin Herlina', 'Teknik Industri', 8, '2025/2026 (Genap)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `progres_skripsi`
--

CREATE TABLE `progres_skripsi` (
  `id` int(11) NOT NULL,
  `npm` varchar(20) DEFAULT NULL,
  `bab` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `komentar_dosen1` text DEFAULT NULL,
  `komentar_dosen2` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `nilai_dosen1` varchar(20) DEFAULT NULL,
  `nilai_dosen2` varchar(20) DEFAULT NULL,
  `progres_dosen1` int(11) DEFAULT 0,
  `progres_dosen2` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `progres_skripsi`
--

INSERT INTO `progres_skripsi` (`id`, `npm`, `bab`, `file`, `komentar_dosen1`, `komentar_dosen2`, `created_at`, `nilai_dosen1`, `nilai_dosen2`, `progres_dosen1`, `progres_dosen2`) VALUES
(48, '1001', 1, 'Progres_Alvin_Candra_BAB1_1754204617.pdf', 'Latar Belakang\r\n- Hindari penggunaan kata-kata subjektif seperti \"menurut penulis\", ganti dengan argumen ilmiah yang didukung referensi\r\n- Uraian masih terlalu umum, fokuskan pada masalah yang lebih spesifik terkait topik yang diambil\r\nRumusan Masalah\r\n- Rumusan masalah terlalu luas. Buatlah dalam bentuk pertanyaan dan sesuaikan dengan fokus penelitian.', '1. Uraian masih terlalu umum, fokuskan pada masalah yang lebih spesifik terkait topik yang diambil.\r\n2. Tambahkan data pendukung (misalnya statistik, hasil survei, atau kutipan dari jurnal) untuk menguatkan urgensi penelitian.\r\n3. Hindari penggunaan kata-kata subjektif seperti \"menurut penulis\", ganti dengan argumen ilmiah yang didukung referensi.', '2025-08-03 14:03:37', 'Revisi', 'Revisi', 0, 0),
(53, '1001', 2, 'Progres_Alvin_Candra_BAB2_1754445990.pdf', 'Revisi', 'dilanjutkan bab 3', '2025-08-06 09:06:30', 'Revisi', 'ACC', 0, 10),
(55, '1007', 1, 'Progres_Budi_Santoso_BAB1_1754470400.pdf', 'Perbaiki Pada Latar Belakang dan Tujuan', NULL, '2025-08-06 15:53:20', 'Revisi', NULL, 0, 0),
(56, '1007', 1, 'Progres_Budi_Santoso_BAB1_1754470553.pdf', 'Ok ', 'ok', '2025-08-06 15:55:53', 'ACC', 'ACC', 10, 10),
(57, '1007', 2, 'Progres_Budi_Santoso_BAB2_1754470679.pdf', 'ok', 'ok', '2025-08-06 15:57:59', 'ACC', 'ACC', 10, 10),
(58, '1007', 3, 'Progres_Budi_Santoso_BAB3_1754470693.pdf', 'ok', 'ok', '2025-08-06 15:58:13', 'ACC', 'ACC', 10, 10),
(59, '1007', 4, 'Progres_Budi_Santoso_BAB4_1754470709.pdf', 'ok', 'ok', '2025-08-06 15:58:29', 'ACC', 'ACC', 10, 10),
(60, '1007', 5, 'Progres_Budi_Santoso_BAB5_1754470727.pdf', 'ok', 'ok', '2025-08-06 15:58:47', 'ACC', 'ACC', 10, 10),
(61, '1002', 1, 'Progres_Berno_Falalangi_BAB1_1756167397.pdf', NULL, 'naskah ditolak', '2025-08-26 07:16:37', NULL, 'Belum Disetujui', 0, 0),
(62, '2001', 1, 'Progres_Elin_Herlina_BAB1_1756170946.pdf', 'setuju', 'setuju', '2025-08-26 08:15:46', 'ACC', 'ACC', 10, 10),
(63, '2001', 2, 'Progres_Elin_Herlina_BAB2_1756170971.pdf', 'setuju', 'setuju', '2025-08-26 08:16:11', 'ACC', 'ACC', 10, 10),
(64, '2001', 3, 'Progres_Elin_Herlina_BAB3_1756170975.pdf', 'setuju', 'setuju', '2025-08-26 08:16:15', 'ACC', 'ACC', 10, 10),
(68, '1001', 1, 'Progres_Alvin_Candra_BAB1_1757397026.pdf', NULL, 'Sudah bagus silahkan dilanjutkan', '2025-09-09 12:50:26', NULL, 'ACC', 0, 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int(11) NOT NULL,
  `tahun` varchar(10) DEFAULT NULL,
  `semester` enum('Gasal','Genap') DEFAULT NULL,
  `is_aktif` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `tahun`, `semester`, `is_aktif`) VALUES
(1, '2025/2026', 'Genap', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `akun_dosen`
--
ALTER TABLE `akun_dosen`
  ADD PRIMARY KEY (`nip`);

--
-- Indeks untuk tabel `akun_mahasiswa`
--
ALTER TABLE `akun_mahasiswa`
  ADD PRIMARY KEY (`npm`);

--
-- Indeks untuk tabel `biodata_dosen`
--
ALTER TABLE `biodata_dosen`
  ADD PRIMARY KEY (`nip`);

--
-- Indeks untuk tabel `biodata_mahasiswa`
--
ALTER TABLE `biodata_mahasiswa`
  ADD PRIMARY KEY (`npm`);

--
-- Indeks untuk tabel `mahasiswa_skripsi`
--
ALTER TABLE `mahasiswa_skripsi`
  ADD PRIMARY KEY (`npm`);

--
-- Indeks untuk tabel `progres_skripsi`
--
ALTER TABLE `progres_skripsi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `progres_skripsi`
--
ALTER TABLE `progres_skripsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
