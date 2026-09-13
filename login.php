<?php
// ini login
session_start();

// 1. Sertakan file koneksi database eksternal
require_once __DIR__ . '/lib/koneksi.php';
$error_message = "";

// 2. Proses Form Login saat tombol 'Masuk' diklik
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role     = $_POST['role'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query mencari data user berdasarkan username dan password di tb_user
    $query  = "SELECT * FROM tb_user WHERE username = '$username' AND pass = '$password'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // Validasi Role berdasarkan data database (min = admin, al = petugas)
        if ($role == 'admin' && $data['username'] == 'min') {
            $_SESSION['role'] = 'admin';
            header("Location: modul/admin.php");
            exit();
        } else if ($role == 'petugas' && $data['username'] == 'al') {
            $_SESSION['role'] = 'petugas';
            header("Location: modul/petugas.php");
            exit();
        } else {
            $error_message = "Role yang dipilih tidak sesuai dengan akun Anda!";
        }
    } else {
        $error_message = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Parkee</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#c2e0ff] flex items-center justify-center min-h-screen font-sans p-4">

  <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-sm">
    
    <!-- Logo & Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-[#0052ad] text-white rounded-full font-extrabold text-3xl mb-3 shadow-inner">
        P
      </div>
      <h1 class="text-3xl font-extrabold text-black tracking-tight">Parkee</h1>
      <p class="text-xs text-slate-700 mt-2 font-mono">Silakan masuk ke akun anda</p>
    </div>

    <!-- Notifikasi Error -->
    <?php if ($error_message != ""): ?>
      <div class="mb-5 p-3 bg-red-100 border border-red-300 text-red-700 text-xs rounded-xl text-center font-medium">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-5">
      
      <!-- Pilihan Role -->
      <div class="grid grid-cols-2 gap-3">
        <label class="cursor-pointer">
          <input type="radio" name="role" value="admin" class="peer hidden" checked />
          <div class="py-2.5 px-4 text-center border border-black rounded-xl text-xs font-bold text-black peer-checked:bg-slate-100 peer-checked:border-2 transition-all">
            Admin
          </div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="role" value="petugas" class="peer hidden" />
          <div class="py-2.5 px-4 text-center border border-black rounded-xl text-xs font-bold text-black peer-checked:bg-slate-100 peer-checked:border-2 transition-all">
            Pekerja / Petugas
          </div>
        </label>
      </div>

      <!-- Username -->
      <div>
        <label for="username" class="block text-xs font-bold text-black mb-1.5">Username / ID</label>
        <input 
          type="text" 
          id="username" 
          name="username" 
          required 
          class="w-full px-4 py-3 bg-[#beadff]/30 border border-[#85aeff] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition-all" 
        />
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-xs font-bold text-black mb-1.5">Kata Sandi</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          required 
          class="w-full px-4 py-3 bg-[#beadff]/30 border border-[#85aeff] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition-all" 
        />
      </div>

      <!-- Options -->
      <div class="flex items-center justify-between text-xs pt-1">
        <label class="flex items-center text-black font-semibold cursor-pointer">
          <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-400 text-blue-600 focus:ring-blue-500 mr-2" />
          Ingat Saya
        </label>
        <a href="#" class="text-[#0088ff] font-bold hover:underline">Lupa Password?</a>
      </div>

      <!-- Submit Button -->
      <div class="pt-2">
        <button type="submit" class="w-full py-3 px-4 bg-[#007eff] hover:bg-blue-600 text-white font-bold rounded-2xl text-sm transition-colors shadow-sm">
          Masuk
        </button>
      </div>

    </form>
  </div>

</body>
</html>