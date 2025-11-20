<?php
// templates/sidebar_dosen.php
// File ini di-include di home_dosen.php, sehingga variabel $current_page sudah tersedia.

// --- FUNGSI EMBEDDED UNTUK AKTIVASI MENU ---
// Kita buat fungsi ini lokal di sini, seperti yang Anda lakukan sebelumnya.
if (!function_exists('is_active')) {
    function is_active($target_page, $current_page) {
        if (is_array($target_page)) {
            return in_array($current_page, $target_page) ? 'active' : '';
        }
        return ($current_page === $target_page) ? 'active' : '';
    }
}
?>
<div class="sidebar">
    <h4 class="text-center mb-4">Panel Dosen</h4>
    
    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">MENU</h6>
    <a href="home_dosen.php" class="<?= is_active(['home_dosen.php', 'index.php'], $current_page) ?>">
        <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
    </a>
    
    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">KELOLA TUGAS AKHIR</h6>
    <a href="progres_mhs.php" class="<?= is_active('progres_mhs.php', $current_page) ?>">
        <i class="bi bi-file-earmark-text-fill me-2"></i> Bimbingan
    </a>
    <a href="persetujuan.php" class="<?= is_active('persetujuan.php', $current_page) ?>">
        <i class="bi bi-file-earmark-check-fill me-2"></i> Persetujuan Tugas Akhir
    </a>
    <a href="penilaian.php" class="<?= is_active('penilaian.php', $current_page) ?>">
        <i class="bi bi-clipboard-check-fill me-2"></i> Penilaian Tugas Akhir
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">BERITA ACARA</h6>
    <a href="ba_proposal.php" class="<?= is_active('ba_proposal.php', $current_page) ?>">
        <i class="bi bi-file-earmark-pdf-fill me-2"></i> Proposal Tugas Akhir
    </a>
    <a href="ba_pendadaran.php" class="<?= is_active('ba_pendadaran.php', $current_page) ?>">
        <i class="bi bi-file-earmark-pdf-fill me-2"></i> Pendadaran Tugas Akhir
    </a>

    <h6 class="text-uppercase mx-3 mt-4 mb-2" style="font-size: 10px;">PENGATURAN</h6>
    <a href="profile_dosen.php" class="<?= is_active('profile_dosen.php', $current_page) ?>">
        <i class="bi bi-person-circle me-2"></i> Profile
    </a>

    <a href="../auth/login.php?action=logout" class="text-danger mt-4 border-top pt-3">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>
    
    <div class="text-center mt-5" style="font-size: 12px; color: #aaa;">&copy; 2025 UNIMMA</div>
</div>