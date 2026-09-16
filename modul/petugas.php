<?php
// ini petugas
session_start();
include '../lib/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

// Query untuk data Statistik Dashboard
$q_masuk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kendaraan");
$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;

$q_keluar = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kendaraan WHERE status = 'Selesai'");
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;

// Statistik Tambahan: Kendaraan yang masih parkir
$total_parkir = $total_masuk - $total_keluar;

// Query 5 Transaksi / Kendaraan Terbaru
$q_terbaru = mysqli_query($koneksi, "SELECT plat_nomor, jenis_kendaraan, waktu_masuk, status FROM kendaraan ORDER BY id_kendaraan DESC LIMIT 5");
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
          <a href="petugas.php" class="flex items-center space-x-3 px-4 py-3 bg-[#3b82f6] text-white rounded-xl text-sm font-bold shadow-md shadow-blue-500/20 transition-all hover:translate-x-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span>Dashboard Petugas</span>
          </a>
          <a href="inputpetugas.php" class="flex items-center space-x-3 px-4 py-3 text-slate-800 hover:bg-white/40 rounded-xl text-sm font-bold transition-all hover:translate-x-1">
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

        <!-- Kartu Ringkasan Statistik (3 Kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Masuk</p>
              <h3 class="text-3xl font-bold text-blue-600 mt-1"><?= $total_masuk; ?></h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
            </div>
          </div>
          
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sedang Parkir</p>
              <h3 class="text-3xl font-bold text-amber-500 mt-1"><?= $total_parkir; ?></h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Keluar</p>
              <h3 class="text-3xl font-bold text-emerald-600 mt-1"><?= $total_keluar; ?></h3>
            </div>
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </div>
          </div>
        </div>

        <!-- Banner Aksi Cepat & Pencarian -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
          <div>
            <h3 class="text-lg font-bold">Input atau Cek Kendaraan Parkir</h3>
            <p class="text-blue-100 text-sm">Tambahkan data masuk atau verifikasi nomor plat dengan cepat.</p>
          </div>
          <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="inputpetugas.php" class="px-5 py-2.5 bg-white text-blue-600 hover:bg-blue-50 rounded-xl text-sm font-bold transition-all shadow-sm whitespace-nowrap text-center w-full md:w-auto">
              + Input Kendaraan
            </a>
          </div>
        </div>

        <!-- Tabel Kendaraan Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
              <h3 class="font-bold text-slate-900">Aktivitas Terakhir</h3>
              <p class="text-xs text-slate-500">5 Kendaraan terbaru yang tercatat di sistem</p>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
              <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-100">
                <tr>
                  <th class="px-6 py-3">Plat Nomor</th>
                  <th class="px-6 py-3">Jenis</th>
                  <th class="px-6 py-3">Jam Masuk</th>
                  <th class="px-6 py-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php if ($q_terbaru && mysqli_num_rows($q_terbaru) > 0): ?>
                  <?php while ($row = mysqli_fetch_assoc($q_terbaru)): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                      <td class="px-6 py-4 font-bold text-slate-900">
                        <?= htmlspecialchars($row['plat_nomor'] ?? $row['no_plat'] ?? '-'); ?>
                      </td>
                      <td class="px-6 py-4">
                        <?= htmlspecialchars($row['jenis_kendaraan'] ?? $row['jenis'] ?? '-'); ?>
                      </td>
                      <td class="px-6 py-4">
                        <?= htmlspecialchars($row['waktu_masuk'] ?? '-'); ?>
                      </td>
                      <td class="px-6 py-4">
                        <?php 
                          $status = $row['status'] ?? 'Parkir';
                          if ($status === 'Selesai' || $status === 'Keluar') {
                            echo '<span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Keluar</span>';
                          } else {
                            echo '<span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">Sedang Parkir</span>';
                          }
                        ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">Belum ada data kendaraan tercatat.</td>
                  </tr>
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