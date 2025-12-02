<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}


// Proses form setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $target = (int)$_POST['target'];
    $reason = trim($_POST['reason']);
    $mainWallet = trim($_POST['main_wallet']) ?: 'DANA';


    if ($username && $target > 0 && $reason) {
        require_once 'helpers.php';
        // ✅ Kirim $mainWallet langsung ke constructor
        $user = new User($username, $target, $reason, $mainWallet);
        $user->save();
        $_SESSION['user'] = $username;
        header('Location: home.php');
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kumpulin • Mulai Nabung</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background: #f0f8ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
    .container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 500px; width: 100%; }
    .logo { font-size: 3rem; margin-bottom: 10px; color: #6bbfeb; }
    h1 { color: #333; margin: 15px 0; }
    .tagline { color: #666; margin-bottom: 30px; font-style: italic; }
    .btn { background: #ffc107; color: #333; border: none; padding: 12px 30px; font-size: 18px; border-radius: 50px; cursor: pointer; font-weight: bold; margin-top: 20px; }
    .btn:hover { background: #e0a800; }
    /* Pop-up Setup */
    dialog { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); border: none; border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.3); padding: 30px; width: 90%; max-width: 500px; z-index: 1000; background: white; max-height: 80vh; overflow-y: auto; }
    dialog::backdrop { background: rgba(0,0,0,0.7); }
    .close-btn { position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #888; }
    dialog h2 { margin-bottom: 20px; color: #333; text-align: center; }
    input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
    .quick-targets { display: flex; gap: 8px; flex-wrap: wrap; margin: 12px 0; }
    .btn-quick { padding: 8px 12px; background: #e0e0e0; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; transition: background 0.2s; }
    .btn-quick:hover { background: #d0d0d0; }
    .actions { display: flex; gap: 10px; margin-top: 20px; width: 100%; }
    .btn-outline { flex: 1; background: #eee; color: #555; border: none; padding: 10px; border-radius: 8px; cursor: pointer; }
    .btn-primary { flex: 2; background: #6bbfeb; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    /* Link login lama */
    .login-hint { margin-top: 25px; font-size: 14px; color: #666; }
    .login-hint a { color: #6bbfeb; text-decoration: underline; font-weight: bold; }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo"> 🐓 </div>
    <h1> Kumpulin </h1>
    <p style="color: #e65100; font-weight: bold;">
      Nabung dikit-dikit aja, ntar banyak kok<br>
      <span style="line-height: 1.6; color: #555;">kalau ngga dijajanin kopi ya xixixi</span>
    </p>
    <button class="btn" onclick="document.getElementById('setupDialog').showModal()">
      Mulai Nabung Sekarang
    </button>
    <!-- TOMBOL MASUK AKUN LAMA -->
    <div class="login-hint">
      Sudah punya akun? <a href="login.php">Masuk dengan username lama</a>
    </div>
  </div>


  <dialog id="setupDialog">
    <button class="close-btn" onclick="this.parentElement.close()">×</button>
    <h2> Hai, orang yang mau rajin nabung! 😎 </h2>
    <p> Ayo atur target nabungmu </p>
    <form method="POST">
      <label>Username</label>
      <input type="text" name="username" placeholder="Misal: SiKayaMendadak" required maxlength="20">
      <label>Target Nabung (dalam Rupiah)</label>
      <input type="number" id="targetInput" name="target" placeholder="Misal: 500000" min="1000" required>
      <!-- ✅ Opsi Cepat Target -->
      <div class="quick-targets">
        <button type="button" class="btn-quick" onclick="document.getElementById('targetInput').value = 100000">100rb</button>
        <button type="button" class="btn-quick" onclick="document.getElementById('targetInput').value = 500000">500rb</button>
        <button type="button" class="btn-quick" onclick="document.getElementById('targetInput').value = 1000000">1jt</button>
        <button type="button" class="btn-quick" onclick="document.getElementById('targetInput').value = 5000000">5jt</button>
        <button type="button" class="btn-quick" onclick="document.getElementById('targetInput').value = 10000000">10jt</button>
      </div>
      <label>Mau buat apa nichh</label>
      <textarea name="reason" placeholder="Misal: Biar bisa beli nasi padang 10 porsi" rows="3" required></textarea>
      <!-- ✅ Sesuaikan value dengan getLocations() -->
      <label>Dompet Utama</label>
      <select name="main_wallet" required>
        <option value="">-- Pilih --</option>
        <option value="Celengan">Celengan</option>
        <option value="BCA">Bank BCA</option>
        <option value="Mandiri">Bank Mandiri</option>
        <option value="DANA" selected>DANA</option>
        <option value="OVO">OVO</option>
        <option value="ShopeePay">ShopeePay</option>
        <option value="Gopay">GoPay</option>
        <option value="Tunai">Uang Tunai</option>
      </select>
      <div class="actions">
        <button type="button" class="btn-outline" onclick="document.getElementById('setupDialog').close()">Nanti Aja</button>
        <button type="submit" class="btn-primary">Simpan & Lanjutkan</button>
      </div>
    </form>
  </dialog>
</body>
</html>