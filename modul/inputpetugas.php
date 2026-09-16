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
        header('Location: inputpetugas.php');
        exit();
    }

    $message = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
    mysqli_stmt_close($stmt);
}

if (isset($_POST['proses_keluar'])) {
    $id_kendaraan = $_POST['id_kendaraan'];
    $waktu_keluar = date('Y-m-d H:i:s');

    $stmt_get = mysqli_prepare($koneksi, "SELECT * FROM kendaraan WHERE id_kendaraan = ?");
    mysqli_stmt_bind_param($stmt_get, "i", $id_kendaraan);
    mysqli_stmt_execute($stmt_get);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_get));
    mysqli_stmt_close($stmt_get);

    if ($data) {
        $durasi_jam = max(1, (int) ceil((strtotime($waktu_keluar) - strtotime($data['waktu_masuk'])) / 3600));
        $tarif = $data['jenis_kendaraan'] === 'Mobil' ? 5000 : ($data['jenis_kendaraan'] === 'Truk' ? 8000 : 2000);
        $total_bayar = $tarif * $durasi_jam;

        $stmt_bayar = mysqli_prepare($koneksi, "INSERT INTO pembayaran (id_kendaraan, waktu_keluar, durasi_jam, total_bayar) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_bayar, "isii", $id_kendaraan, $waktu_keluar, $durasi_jam, $total_bayar);

        if (mysqli_stmt_execute($stmt_bayar)) {
            $stmt_update = mysqli_prepare($koneksi, "UPDATE kendaraan SET status = 'Selesai' WHERE id_kendaraan = ?");
            mysqli_stmt_bind_param($stmt_update, "i", $id_kendaraan);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            header('Location: inputpetugas.php');
            exit();
        }

        $message = 'Gagal memproses pembayaran: ' . mysqli_error($koneksi);
        mysqli_stmt_close($stmt_bayar);
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $query_sql = "SELECT k.*, p.waktu_keluar, p.total_bayar FROM kendaraan k LEFT JOIN pembayaran p ON k.id_kendaraan = p.id_kendaraan WHERE k.plat_nomor LIKE ? OR k.jenis_kendaraan LIKE ? ORDER BY k.id_kendaraan DESC";
    $stmt_list = mysqli_prepare($koneksi, $query_sql);
    $param_search = '%' . $search . '%';
    mysqli_stmt_bind_param($stmt_list, "ss", $param_search, $param_search);
    mysqli_stmt_execute($stmt_list);
    $query_list = mysqli_stmt_get_result($stmt_list);
} else {
    $query_list = mysqli_query($koneksi, "SELECT k.*, p.waktu_keluar, p.total_bayar FROM kendaraan k LEFT JOIN pembayaran p ON k.id_kendaraan = p.id_kendaraan ORDER BY k.id_kendaraan DESC");
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

        <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100">
            <div>
              <h3 class="text-base font-bold text-slate-900">Daftar Kendaraan Parkir Saat Ini</h3>
              <p class="text-xs text-slate-500 mt-0.5">Kelola transaksi kendaraan masuk dan keluar</p>
            </div>
            <form action="inputpetugas.php" method="GET" class="flex items-center space-x-2 w-full md:w-auto">
              <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari Plat / Jenis..." class="w-full md:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button type="submit" class="px-3.5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold">Cari</button>
              <?php if ($search !== ''): ?>
                <a href="inputpetugas.php" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold">Reset</a>
              <?php endif; ?>
            </form>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#bfdbfe] text-slate-900 text-xs font-bold uppercase tracking-wider border-b border-blue-200">
                  <th class="py-3.5 px-6">No. Plat</th><th class="py-3.5 px-6">Jenis</th><th class="py-3.5 px-6">Waktu Masuk</th><th class="py-3.5 px-6">Waktu Keluar</th><th class="py-3.5 px-6 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs">
                <?php if (mysqli_num_rows($query_list) === 0): ?>
                  <tr><td colspan="5" class="py-12 text-center text-slate-500 font-medium"><?= $search !== '' ? 'Tidak ada data kendaraan yang cocok dengan "' . htmlspecialchars($search) . '"' : 'Belum ada data kendaraan.'; ?></td></tr>
                <?php else: ?>
                  <?php while ($row = mysqli_fetch_assoc($query_list)): ?>
                    <?php $badgeClass = $row['jenis_kendaraan'] === 'Mobil' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($row['jenis_kendaraan'] === 'Truk' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200'); ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                      <td class="py-4 px-6 font-bold text-slate-900"><?= htmlspecialchars($row['plat_nomor']); ?></td>
                      <td class="py-4 px-6"><span class="px-3 py-1 text-[11px] font-bold rounded-lg border <?= $badgeClass; ?>"><?= htmlspecialchars($row['jenis_kendaraan']); ?></span></td>
                      <td class="py-4 px-6 font-semibold text-slate-700"><?= date('H:i', strtotime($row['waktu_masuk'])); ?> WIB</td>
                      <td class="py-4 px-6 font-semibold text-slate-700"><?= !empty($row['waktu_keluar']) ? date('H:i', strtotime($row['waktu_keluar'])) . ' WIB' : '-'; ?></td>
                      <td class="py-4 px-6 text-center">
                        <?php if ($row['status'] === 'Selesai'): ?>
                          <span class="inline-block px-3 py-1 bg-slate-100 text-slate-500 rounded-lg font-bold text-[11px]">Selesai (Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?>)</span>
                        <?php else: ?>
                          <form action="inputpetugas.php" method="POST" onsubmit="return confirm('Proses keluar untuk plat <?= htmlspecialchars($row['plat_nomor'], ENT_QUOTES); ?>?')">
                            <input type="hidden" name="id_kendaraan" value="<?= $row['id_kendaraan']; ?>">
                            <button type="submit" name="proses_keluar" class="px-3.5 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-bold">Proses Keluar</button>
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
