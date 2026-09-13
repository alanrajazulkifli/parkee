<?php
// ini petugas
session_start();
include '../lib/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$message = "";

if (isset($_POST['proses_keluar'])) {
    $id_kendaraan = $_POST['id_kendaraan'];
    $waktu_keluar = date('Y-m-d H:i:s');

    $stmt_get = mysqli_prepare($koneksi, "SELECT * FROM kendaraan WHERE id_kendaraan = ?");
    mysqli_stmt_bind_param($stmt_get, "i", $id_kendaraan);
    mysqli_stmt_execute($stmt_get);
    $get_kendaraan = mysqli_stmt_get_result($stmt_get);
    $data = mysqli_fetch_assoc($get_kendaraan);
    mysqli_stmt_close($stmt_get);

    if ($data) {
        $masuk = strtotime($data['waktu_masuk']);
        $keluar = strtotime($waktu_keluar);
        $diff = $keluar - $masuk;
        
        $durasi_jam = ceil($diff / 3600);
        if ($durasi_jam <= 0) $durasi_jam = 1;

        $tarif = 2000;
        if ($data['jenis_kendaraan'] == 'Mobil') {
            $tarif = 5000;
        } else if ($data['jenis_kendaraan'] == 'Truk') {
            $tarif = 8000;
        }

        $total_bayar = $tarif * $durasi_jam;

        $stmt_bayar = mysqli_prepare($koneksi, "INSERT INTO pembayaran (id_kendaraan, waktu_keluar, durasi_jam, total_bayar) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_bayar, "isii", $id_kendaraan, $waktu_keluar, $durasi_jam, $total_bayar);
        
        if (mysqli_stmt_execute($stmt_bayar)) {
            $stmt_update = mysqli_prepare($koneksi, "UPDATE kendaraan SET status = 'Selesai' WHERE id_kendaraan = ?");
            mysqli_stmt_bind_param($stmt_update, "i", $id_kendaraan);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);

            header("Location: petugas.php");
            exit();
        } else {
            $message = "Gagal memproses pembayaran: " . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt_bayar);
    }
}

// Logical Search Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $query_sql = "
        SELECT k.*, p.waktu_keluar, p.total_bayar 
        FROM kendaraan k 
        LEFT JOIN pembayaran p ON k.id_kendaraan = p.id_kendaraan 
        WHERE k.plat_nomor LIKE ? OR k.jenis_kendaraan LIKE ?
        ORDER BY k.id_kendaraan DESC
    ";
    $stmt_list = mysqli_prepare($koneksi, $query_sql);
    $param_search = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt_list, "ss", $param_search, $param_search);
    mysqli_stmt_execute($stmt_list);
    $query_list = mysqli_stmt_get_result($stmt_list);
} else {
    $query_list = mysqli_query($koneksi, "
        SELECT k.*, p.waktu_keluar, p.total_bayar 
        FROM kendaraan k 
        LEFT JOIN pembayaran p ON k.id_kendaraan = p.id_kendaraan 
        ORDER BY k.id_kendaraan DESC
    ");
}

// Query untuk data Statistik Dashboard
$q_masuk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kendaraan");
$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;


$q_keluar = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kendaraan WHERE status = 'Selesai'");
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Parkee Petugas - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="../gambar/1.png" type="image/icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-slate-50 antialiased text-slate-800">

  <div class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <aside class="w-72 bg-[#8ab6fd] flex flex-col justify-between p-6 hidden md:flex border-r border-blue-200/60 shadow-lg relative z-10">
      <div>
        <div class="mb-10 px-2 flex items-center space-x-3">
          <div class="w-10 h-10 bg-white/30 backdrop-blur-md border border-white/40 rounded-xl flex items-center justify-center text-slate-900 font-black text-xl shadow-sm">
            P
          </div>
          <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight leading-tight">Parkee</h1>
            <p class="text-xs font-semibold text-slate-700 tracking-wider uppercase">Portal Petugas</p>
          </div>
        </div>
        
        <nav class="space-y-2">
          <a href="inputpetugas.php" class="flex items-center space-x-3 px-4 py-3 bg-[#3b82f6] text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 transition-all hover:translate-x-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span>Dashboard Petugas</span>
          </a>
          <a href="#" class="flex items-center space-x-3 px-4 py-3 bg-[#3b82f6] text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 transition-all hover:translate-x-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Input Kendaraan Parkir</span>
        </a>
        </nav>
      </div>

      <div class="pt-6 border-t border-blue-300/40">
        <a href="../logout.php" class="flex items-center justify-center space-x-2 py-3 px-4 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20 transition-all active:scale-[0.98]">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          <span>Keluar System</span>
        </a>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-y-auto">
      
      <!-- Top Header -->
      <header class="bg-[#bfdbfe] py-4 px-8 flex justify-between items-center border-b border-blue-200/80 sticky top-0 z-20 backdrop-blur-md bg-opacity-90">
        <div class="flex items-center space-x-3">
          <h2 class="text-lg font-bold text-slate-900">Dashboard Petugas</h2>
        </div>
        <div class="flex items-center space-x-4">
          <div class="text-right">
            <span class="block text-sm font-bold text-slate-900 leading-none mb-1">
              <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Petugas Parkir'; ?>
            </span>
            <span class="text-[11px] font-semibold text-slate-600">Shift Aktif</span>
          </div>
          <div class="w-9 h-9 bg-blue-500 text-white font-bold rounded-xl flex items-center justify-center text-sm shadow-md ring-2 ring-white">
            P
          </div>
        </div>
      </header>

      <!-- Main Body -->
      <main class="p-8 space-y-6 max-w-7xl">

        <!-- Kartu Ringkasan Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Kendaraan Masuk</p>
            <h3 class="text-3xl font-bold text-blue-600 mt-2" id="statMasuk"><?= $total_masuk; ?></h3>
          </div>
          
          <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Kendaraan Keluar</p>
            <h3 class="text-3xl font-bold text-slate-700 mt-2" id="statKeluar"><?= $total_keluar; ?></h3>
          </div>
        </div>

        <?php if ($message != ""): ?>
          <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-xl">
            <?= htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <!-- Card Tabel Kendaraan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          
          <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100">
            <div>
              <h3 class="text-base font-bold text-slate-900">Daftar Kendaraan Parkir Saat Ini</h3>
              <p class="text-xs text-slate-500 mt-0.5">Kelola transaksi kendaraan masuk dan keluar</p>
            </div>

            <!-- Form Search Input -->
            <form action="" method="GET" class="flex items-center space-x-2 w-full md:w-auto">
              <div class="relative w-full md:w-64">
                <input 
                  type="text" 
                  name="search" 
                  value="<?= htmlspecialchars($search); ?>" 
                  placeholder="Cari Plat / Jenis..." 
                  class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              
              <button type="submit" class="px-3.5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                Cari
              </button>

              <?php if (!empty($search)): ?>
                <a href="petugas.php" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all">
                  Reset
                </a>
              <?php endif; ?>
            </form>
          </div>

          <!-- Data Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#bfdbfe] text-slate-900 text-xs font-bold uppercase tracking-wider border-b border-blue-200">
                  <th class="py-3.5 px-6">No. Plat</th>
                  <th class="py-3.5 px-6">Jenis</th>
                  <th class="py-3.5 px-6">Waktu Masuk</th>
                  <th class="py-3.5 px-6">Waktu Keluar</th>
                  <th class="py-3.5 px-6 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs">
                <?php if (mysqli_num_rows($query_list) == 0): ?>
                  <tr>
                    <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                      <?= !empty($search) ? 'Tidak ada data kendaraan yang cocok dengan "' . htmlspecialchars($search) . '"' : 'Belum ada data kendaraan.'; ?>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php while ($row = mysqli_fetch_assoc($query_list)): ?>
                    <?php 
                      $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                      if ($row['jenis_kendaraan'] == 'Mobil') $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                      if ($row['jenis_kendaraan'] == 'Truk')  $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                    ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                      <td class="py-4 px-6 font-bold text-slate-900 tracking-wide"><?= htmlspecialchars($row['plat_nomor']); ?></td>
                      <td class="py-4 px-6">
                        <span class="px-3 py-1 text-[11px] font-bold rounded-lg border <?= $badgeClass; ?>">
                          <?= htmlspecialchars($row['jenis_kendaraan']); ?>
                        </span>
                      </td>
                      <td class="py-4 px-6 font-semibold text-slate-700"><?= date('H:i', strtotime($row['waktu_masuk'])); ?> WIB</td>
                      <td class="py-4 px-6 font-semibold text-slate-700">
                        <?= !empty($row['waktu_keluar']) ? date('H:i', strtotime($row['waktu_keluar'])) . ' WIB' : '-'; ?>
                      </td>
                      <td class="py-4 px-6 text-center">
                        <?php if ($row['status'] == 'Selesai'): ?>
                          <span class="inline-block px-3 py-1 bg-slate-100 text-slate-500 rounded-lg font-bold text-[11px]">
                            Selesai (Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?>)
                          </span>
                        <?php else: ?>
                          <form action="" method="POST" onsubmit="return confirm('Proses keluar untuk plat <?= $row['plat_nomor']; ?>?')">
                            <input type="hidden" name="id_kendaraan" value="<?= $row['id_kendaraan']; ?>">
                            <button type="submit" name="proses_keluar" class="px-3.5 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-bold transition-all shadow-sm shadow-red-500/20 active:scale-95">
                              Proses Keluar
                            </button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </main>

    </div>
  </div>

</body>
</html>