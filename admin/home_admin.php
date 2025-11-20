<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../auth/login.php");
    exit();
}
$nama_admin = $_SESSION['admin_username'] ?? 'Admin'; // Menggunakan username

// --- 1. HITUNG STATISTIK ---
$q_dosen = mysqli_query($conn, "SELECT COUNT(*) as total FROM mstr_akun WHERE role='dosen'");
$total_dosen = ($q_dosen) ? mysqli_fetch_assoc($q_dosen)['total'] : 0;

$q_mhs = mysqli_query($conn, "SELECT COUNT(*) as total FROM mstr_akun WHERE role='mahasiswa'");
$total_mhs = ($q_mhs) ? mysqli_fetch_assoc($q_mhs)['total'] : 0;

$q_skripsi = mysqli_query($conn, "SELECT COUNT(*) as total FROM skripsi");
$total_skripsi = ($q_skripsi) ? mysqli_fetch_assoc($q_skripsi)['total'] : 0;

// --- 2. DATA GRAFIK ---
$labels = [];
$data_grafik = [];
$q_grafik = mysqli_query($conn, "SELECT prodi, COUNT(*) as jumlah FROM data_mahasiswa GROUP BY prodi");
if ($q_grafik) {
    while($row = mysqli_fetch_assoc($q_grafik)) {
        $labels[] = $row['prodi'];
        $data_grafik[] = $row['jumlah'];
    }
}

// --- 3. LOGIKA KALENDER ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

$jadwal_sql = "SELECT tanggal, ruang, status FROM ujian_skripsi 
               WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'";
$q_jadwal = mysqli_query($conn, $jadwal_sql);

$events = [];
if ($q_jadwal) {
    while ($row = mysqli_fetch_assoc($q_jadwal)) {
        $events[$row['tanggal']][] = $row;
    }
}

function build_calendar($month, $year, $events) {
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $dayOfWeek = $dateComponents['wday'];
    $monthName = date('F', $firstDayOfMonth);
    
    $calendar = "<div class='calendar-header mb-3 fw-bold text-center'>$monthName $year</div>";
    $calendar .= "<table class='table table-bordered table-sm text-center calendar-table'>";
    $calendar .= "<thead class='table-light'><tr>";
    $calendar .= "<th class='text-danger'>M</th><th>S</th><th>S</th><th>R</th><th>K</th><th>J</th><th>S</th>";
    $calendar .= "</tr></thead><tbody><tr>";

    if ($dayOfWeek > 0) { 
        for ($k = 0; $k < $dayOfWeek; $k++) { $calendar .= "<td class='bg-light'></td>"; }
    }
    
    $currentDay = 1;
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    
    while ($currentDay <= $numberDays) {
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }
        
        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";
        $today = date('Y-m-d');
        
        $hasEvent = isset($events[$date]);
        $isToday = ($date == $today) ? "bg-primary text-white fw-bold" : "";
        $eventDot = $hasEvent ? "<div class='event-dot'></div>" : "";
        
        $onclick = "";
        $cursorStyle = "cursor:default;";
        
        if ($hasEvent) {
            $jsonDetails = htmlspecialchars(json_encode($events[$date]), ENT_QUOTES, 'UTF-8');
            $onclick = "onclick=\"showSchedule('$date', $jsonDetails)\"";
            $cursorStyle = "cursor:pointer;";
        }

        $calendar .= "<td class='$isToday position-relative' $onclick style='$cursorStyle height: 50px; vertical-align: middle;'>
                        <span>$currentDay</span>
                        $eventDot
                      </td>";
        
        $currentDay++;
        $dayOfWeek++;
    }
    
    if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek;
        for ($l = 0; $l < $remainingDays; $l++) { $calendar .= "<td class='bg-light'></td>"; } 
    }
    
    $calendar .= "</tr></tbody></table>";
    return $calendar;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Statistik</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="ccsprogres.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* --- LAYOUT FIXED POSITION (ACUAN FILE JADWAL) --- */
    body { background-color: #f4f6f9; margin: 0; padding: 0; overflow-x: hidden; }

    /* Header Fixed */
    .header { 
        position: fixed; top: 0; left: 0; width: 100%; height: 70px; 
        background-color: #ffffff; border-bottom: 1px solid #dee2e6; z-index: 1050; 
        display: flex; align-items: center; justify-content: space-between; padding: 0 25px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
    }
    
    /* Sidebar Fixed (Offset 70px dari atas) */
    .sidebar { 
        position: fixed; top: 70px; left: 0; width: 250px; height: calc(100vh - 70px); 
        background-color: #343a40; color: white; overflow-y: auto; padding-top: 20px; 
        z-index: 1040; 
    }
    
    /* Main Content (Offset dari Header dan Sidebar) */
    .main-content { 
        margin-top: 70px; 
        margin-left: 250px; 
        padding: 30px; 
        width: auto; 
        background-color: #f4f6f9;
    }
    
    /* Styling Tambahan */
    .stat-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .icon-box { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .calendar-table th { font-size: 12px; }
    .calendar-table td { font-size: 14px; position: relative; }
    .event-dot { width: 6px; height: 6px; background-color: #dc3545; border-radius: 50%; margin: 2px auto 0 auto; }
  </style>
</head>
<body>

<div class="header">
    <div class="d-flex align-items-center">
        <img src="unimma.png" alt="Logo" style="height: 40px;">
        <h5 class="m-0 ms-3 fw-bold text-dark">FAKULTAS TEKNIK</h5>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end lh-sm">
            <small class="text-muted d-block">Admin</small>
            <span class="fw-bold"><?= htmlspecialchars($nama_admin) ?></span>
        </div>
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">👤</div>
    </div>
</div>

<?php 
    $page = 'home_admin'; // Penanda halaman aktif
    include "../templates/sidebar_admin.php";
?>

<div class="main-content">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1">Jumlah Dosen</p><h2 class="fw-bold m-0"><?= $total_dosen ?></h2></div>
                    <div class="icon-box bg-primary text-white">👨‍🏫</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1">Jumlah Mahasiswa</p><h2 class="fw-bold m-0"><?= $total_mhs ?></h2></div>
                    <div class="icon-box bg-info text-white">🎓</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1">Judul Skripsi Aktif</p><h2 class="fw-bold m-0"><?= $total_skripsi ?></h2></div>
                    <div class="icon-box bg-success text-white">📚</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card stat-card p-4 h-100">
                <h5 class="mb-3">Statistik Prodi</h5>
                <div style="flex: 1; min-height: 300px; position: relative;">
                    <canvas id="prodiChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card p-4 h-100">
                <h5 class="mb-3">Jadwal Ujian Skripsi</h5>
                
                <?= build_calendar($bulan_ini, $tahun_ini, $events); ?>
                
                <div class="mt-3">
                    <div class="d-flex align-items-center gap-2 legend-item">
                        <div style="width: 10px; height: 10px; background:#0d6efd;"></div> Hari Ini
                    </div>
                    <div class="d-flex align-items-center gap-2 legend-item">
                        <div style="width: 6px; height: 6px; background:#dc3545; border-radius:50%;"></div> Ada Jadwal
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
  </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Chart JS Config
  const ctx = document.getElementById('prodiChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Mahasiswa',
        data: <?= json_encode($data_grafik) ?>,
        backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545'],
        borderWidth: 1
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  // FUNCTION UNTUK MENAMPILKAN MODAL (Hanya disertakan jika Anda memerlukannya)
  function showSchedule(dateStr, eventsArray) {
      const dateObj = new Date(dateStr);
      const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
      document.getElementById('modalDate').innerText = formattedDate;

      let listHtml = '';
      if (eventsArray.length > 0) {
          eventsArray.forEach(item => {
              listHtml += `<li class="list-group-item d-flex justify-content-between align-items-center"><div><span class="fw-bold text-primary">🚪 Ruang: ${item.ruang}</span><br><small class="text-muted">Status: ${item.status}</small></div></li>`;
          });
      } else {
          listHtml = '<li class="list-group-item text-center">Tidak ada jadwal detail.</li>';
      }

      document.getElementById('scheduleList').innerHTML = listHtml;
      const myModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
      myModal.show();
  }
</script>

</body>
</html>