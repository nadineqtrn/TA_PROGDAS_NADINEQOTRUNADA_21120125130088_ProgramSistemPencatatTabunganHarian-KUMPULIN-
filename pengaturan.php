<?php
session_start();
require_once 'helpers.php';


$username = $_SESSION['user'] ?? null;
$user = $username ? getUserData($username) : null;


if (!$user) {
    header('Location: index.php');
    exit;
}


// Handle ganti user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_user'])) {
    $new_username = trim($_POST['username']);
    $target = (int)$_POST['target'];
    $reason = trim($_POST['reason']);
    $main_wallet = $_POST['main_wallet'] ?? 'DANA';


    if ($new_username && $target > 0 && $reason) {
        $new_user = new User($new_username, $target, $reason, $main_wallet);
        $new_user->save();
        $_SESSION['user'] = $new_username;
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
  <title>⚙ Pengaturan • Kumpulin</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
    body { background: #f0f8ff; padding: 20px; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
    .container { background: white; border-radius: 16px; padding: 30px; width: 100%; max-width: 450px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }


    .header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 25px;
    }
    .back-btn {
      background: none;
      border: none;
      font-size: 1.2rem;
      color: #6bbfeb;
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 50%;
      transition: background 0.2s;
    }
    .back-btn:hover {
      background: #e3f2fd;
    }
    .header-title {
      font-size: 1.6rem;
      color: #6bbfeb;
      font-weight: 500;
    }


    .section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }
    .section h2 {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #333;
      font-size: 1.1rem;
      margin-bottom: 10px;
      font-weight: bold;
    }
    .section p {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 15px;
    }


    .input-field {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      margin-bottom: 15px;
      outline: none;
      transition: border 0.2s;
    }
    .input-field:focus {
      border-color: #6bbfeb;
      box-shadow: 0 0 0 2px rgba(107, 191, 235, 0.2);
    }
    select.input-field {
      appearance: none;
      background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%23666'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 12px center;
      padding-right: 36px;
    }


    .btn {
      width: 100%;
      padding: 12px;
      background: #e0e0e0;
      color: #333;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn:hover {
      background: #d0d0d0;
    }
    .btn-primary {
      background: #6bbfeb;
      color: white;
    }
    .btn-primary:hover {
      background: #4da1d1;
    }


    a.back-link {
      display: block;
      text-align: center;
      color: #6bbfeb;
      text-decoration: underline;
      margin-top: 20px;
      font-size: 0.95rem;
    }


    .label {
      display: block;
      font-weight: bold;
      margin-bottom: 6px;
      color: #333;
    }
  </style>
</head>
<body>


<div class="container">
  <div class="header">
    <a href="home.php" class="back-btn">←</a>
    <h1 class="header-title">Pengaturan</h1>
  </div>


  <div class="section">
    <h2><span style="font-size:1.2rem;">🔄</span> Ganti Pengguna</h2>
    <p>Mulai data baru dengan username berbeda.</p>
   
    <form method="POST">
      <input type="hidden" name="switch_user" value="1">


      <label class="label">Username baru</label>
      <input type="text" name="username" placeholder="Contoh: Rina"
             class="input-field" required autofocus>


      <label class="label">Target nabung (Rp)</label>
      <input type="number" name="target" placeholder="1000000"
             class="input-field" min="1000" required>


      <label class="label">Kenapa nabung?</label>
      <input type="text" name="reason" placeholder="Beli HP baru, liburan, dll"
             class="input-field" required>


      <label class="label">Dompet Utama</label>
      <select name="main_wallet" class="input-field" required>
        <option value="">-- Pilih dompet --</option>
        <option value="Celengan">Celengan</option>
        <option value="BCA">Bank BCA</option>
        <option value="Mandiri">Bank Mandiri</option>
        <option value="DANA" selected>DANA</option>
        <option value="OVO">OVO</option>
        <option value="ShopeePay">ShopeePay</option>
        <option value="Gopay">GoPay</option>
        <option value="Tunai">Uang Tunai</option>
        <option value="Lainnya">Lainnya</option>
      </select>


      <button type="submit" class="btn btn-primary">Ganti Pengguna</button>
    </form>
  </div>


  <a href="home.php" class="back-link">← Kembali ke Beranda</a>
</div>


</body>
</html>