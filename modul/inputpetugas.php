<?php
session_start();
include '../lib/koneksi.php';

date_default_timezone_set('Asia/Jakarta');

$message = '';

if (isset($_POST['simpan_masuk'])) {
    $plat_nomor = strtoupper(trim($_POST['plat_nomor']));
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $waktu_masuk = date('Y-m-d') . ' ' . $_POST['waktu_masuk'] . ':00';

    $stmt = mysqli_prepare($koneksi, "INSERT INTO kendaraan (plat_nomor, jenis_kendaraan, waktu_masuk, status) VALUES (?, ?, ?, 'Parkir')");
    mysqli_stmt_bind_param($stmt, "sss", $plat_nomor, $jenis_kendaraan, $waktu_masuk);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: petugas.php');
        exit();
    }

    $message = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parkee Petugas - Input Kendaraan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="../gambar/1.png" type="image/icon">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 antialiased text-slate-800">
  <div class="flex h-screen overflow-hidden">
    <aside class="w-72 bg-[#8ab6fd] flex flex-col justify-between p-6 hidden md:flex border-r border-blue-200/60 shadow-lg">
      <div>
        <div class="mb-10 px-2 flex items-center space-x-3">
          <div class="w-10 h-10 bg-white/30 border border-white/40 rounded-xl flex items-center justify-center text-slate-900 font-black text-xl">P</div>
          <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight leading-tight">Parkee</h1>
            <p class="text-xs font-semibold text-slate-700 tracking-wider uppercase">Portal Petugas</p>
          </div>
        </div>
        <nav class="space-y-2">
          <a href="petugas.php" class="flex items-center space-x-3 px-4 py-3 text-slate-900 rounded-xl text-sm font-bold transition-all hover:bg-white/30">
            <span>Dashboard Petugas</span>
          </a>
          <a href="inputpetugas.php" class="flex items-center space-x-3 px-4 py-3 bg-[#3b82f6] text-white rounded-xl text-sm font-bold shadow-md">
            <span>Input Kendaraan Parkir</span>
          </a>
        </nav>
      </div>
      <div class="pt-6 border-t border-blue-300/40">
        <a href="../logout.php" class="flex items-center justify-center py-3 px-4 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold">Keluar System</a>
      </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-y-auto">
      <header class="bg-[#bfdbfe] py-4 px-8 flex justify-between items-center border-b border-blue-200/80">
        <h2 class="text-lg font-bold text-slate-900">Input Kendaraan Parkir</h2>
        <span class="text-sm font-bold text-slate-900"><?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Petugas Parkir'; ?></span>
      </header>

      <main class="p-8 max-w-5xl">
        <?php if ($message !== ''): ?>
          <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-xl"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
          <h3 class="text-base font-bold text-slate-900 mb-5">Masukkan Data Kendaraan</h3>
          <form action="inputpetugas.php" method="POST">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
              <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nomor Plat</label>
                <input type="text" name="plat_nomor" required placeholder="B 1234 ABC" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-900 uppercase">
              </div>
              <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jenis Kendaraan</label>
                <div class="grid grid-cols-3 gap-2">
                  <?php foreach (['Motor', 'Mobil', 'Truk'] as $jenis): ?>
                    <label class="cursor-pointer">
                      <input type="radio" name="jenis_kendaraan" value="<?= $jenis; ?>" <?= $jenis === 'Motor' ? 'checked' : ''; ?> class="peer hidden">
                      <div class="py-2.5 text-center border border-slate-300 rounded-xl text-xs font-bold text-slate-700 peer-checked:bg-[#fde2e4] peer-checked:border-pink-300 peer-checked:text-pink-900"><?= $jenis; ?></div>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Waktu Masuk</label>
                <input type="time" name="waktu_masuk" value="<?= date('H:i'); ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-900">
              </div>
              <div class="lg:col-span-2">
                <button type="submit" name="simpan_masuk" class="w-full py-2.5 px-4 bg-[#6366f1] hover:bg-indigo-600 text-white font-bold rounded-xl text-xs shadow-md">+ Simpan Masuk</button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
