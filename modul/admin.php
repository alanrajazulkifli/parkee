<?php
// ini admin
session_start();
include '../lib/koneksi.php';

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

if (isset($_POST['hapus_kendaraan'])) {
    $id_kendaraan = $_POST['id_kendaraan'];

    mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id_kendaraan = '$id_kendaraan'");
    $query_hapus = "DELETE FROM kendaraan WHERE id_kendaraan = '$id_kendaraan'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

$q_pemasukan = mysqli_query($koneksi, "
    SELECT SUM(total_bayar) AS total 
    FROM pembayaran 
    WHERE DATE(waktu_keluar) = '$today'
");
$d_pemasukan = mysqli_fetch_assoc($q_pemasukan);
$totalPemasukan = $d_pemasukan['total'] ? $d_pemasukan['total'] : 0;

$q_keluar = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total 
    FROM pembayaran 
    WHERE DATE(waktu_keluar) = '$today'
");
$d_keluar = mysqli_fetch_assoc($q_keluar);
$totalKeluar = $d_keluar['total'];

$query_list = mysqli_query($koneksi, "
    SELECT k.*, p.waktu_keluar, p.total_bayar 
    FROM kendaraan k 
    LEFT JOIN pembayaran p ON k.id_kendaraan = p.id_kendaraan 
    ORDER BY k.id_kendaraan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Parkee Admin - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="../gambar/1.png" type="image/icon">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-slate-50 antialiased text-slate-800">

  <div class="flex h-screen overflow-hidden">
    
    <!-- Sidebar Dimensi Diperbesar (w-72) & Diperbagus -->
    <aside class="w-72 bg-[#8ab6fd] flex flex-col justify-between p-6 hidden md:flex border-r border-blue-200/60 shadow-lg relative z-10">
      <div>
        <!-- Brand Title & Avatar -->
        <div class="mb-10 px-2 flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-700 text-white rounded-xl flex items-center justify-center font-black text-xl shadow-md">
            P
          </div>
          <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight leading-tight">Parkee Admin</h1>
            <p class="text-xs font-semibold text-slate-700 tracking-wider uppercase">Control Panel</p>
          </div>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="space-y-2">
          <a href="#" class="flex items-center space-x-3 px-4 py-3 bg-[#3b82f6] text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 transition-all hover:translate-x-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span>Dashboard</span>
          </a>
        </nav>
      </div>

      <!-- Logout Button -->
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
        <h2 class="text-lg font-bold text-slate-900">Dashboard Admin</h2>
        <div class="flex items-center space-x-4">
          <div class="text-right">
            <span class="block text-sm font-bold text-slate-900 leading-none mb-1">
              <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Admin Parkee'; ?>
            </span>
            <span class="text-[11px] font-semibold text-slate-600">Administrator</span>
          </div>
          <div class="w-9 h-9 bg-blue-700 text-white font-bold rounded-xl flex items-center justify-center text-sm shadow-md ring-2 ring-white">
            A
          </div>
        </div>
      </header>

      <!-- Main Body -->
      <main class="p-8 space-y-6 max-w-7xl">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Card Total Pemasukan -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-blue-300 transition-all">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Pemasukan (Hari Ini)</p>
            <h3 class="text-3xl font-black text-slate-900">
              Rp <?= number_format($totalPemasukan, 0, ',', '.'); ?>
            </h3>
          </div>
          
          <!-- Card Kendaraan Keluar -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-blue-300 transition-all">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Kendaraan Keluar (Hari Ini)</p>
            <h3 class="text-3xl font-black text-slate-900">
              <?= $totalKeluar; ?>
            </h3>
          </div>

        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          
          <!-- Table Header / Title -->
          <div class="p-6 flex justify-between items-center border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Daftar Seluruh Kendaraan</h3>
            <span class="text-xs font-bold bg-blue-100 text-blue-800 px-3.5 py-1.5 rounded-full border border-blue-200">
              Monitoring Petugas
            </span>
          </div>

          <!-- Data Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#bfdbfe] text-slate-900 text-xs font-bold uppercase tracking-wider border-b border-blue-200">
                  <th class="py-3.5 px-6">NO. PLAT</th>
                  <th class="py-3.5 px-6">JENIS</th>
                  <th class="py-3.5 px-6">WAKTU MASUK</th>
                  <th class="py-3.5 px-6">STATUS</th>
                  <th class="py-3.5 px-6">TOTAL BAYAR</th>
                  <th class="py-3.5 px-6 text-center">AKSI ADMIN</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs">
                <?php if (mysqli_num_rows($query_list) == 0): ?>
                  <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                      Belum ada riwayat transaksi kendaraan.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php while ($row = mysqli_fetch_assoc($query_list)): ?>
                    <?php 
                      $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                      if ($row['jenis_kendaraan'] == 'Mobil') $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                      if ($row['jenis_kendaraan'] == 'Truk')  $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';

                      $statusClass = $row['status'] == 'Parkir' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200';
                    ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                      <td class="py-4 px-6 font-bold text-slate-900 tracking-wide"><?= htmlspecialchars($row['plat_nomor']); ?></td>
                      <td class="py-4 px-6">
                        <span class="px-3 py-1 text-[11px] font-bold rounded-lg border <?= $badgeClass; ?>">
                          <?= htmlspecialchars($row['jenis_kendaraan']); ?>
                        </span>
                      </td>
                      <td class="py-4 px-6 font-semibold text-slate-700"><?= date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?> WIB</td>
                      <td class="py-4 px-6">
                        <span class="px-3 py-1 text-[11px] font-bold rounded-full border <?= $statusClass; ?>">
                          <?= htmlspecialchars($row['status']); ?>
                        </span>
                      </td>
                      <td class="py-4 px-6 font-bold text-slate-900">
                        <?= $row['total_bayar'] ? 'Rp ' . number_format($row['total_bayar'], 0, ',', '.') : '-'; ?>
                      </td>
                      <td class="py-4 px-6 text-center">
                        <form action="" method="POST" onsubmit="return confirm('Apakah kamu yakin ingin menghapus data kendaraan ini beserta riwayat pembayarannya?')">
                          <input type="hidden" name="id_kendaraan" value="<?= $row['id_kendaraan']; ?>">
                          <button type="submit" name="hapus_kendaraan" class="px-3.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg text-xs font-bold transition-all shadow-sm active:scale-95">
                            Hapus Data
                          </button>
                        </form>
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