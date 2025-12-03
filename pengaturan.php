<?php
session_start();
require_once 'helpers.php';

$username = $_SESSION['user'] ?? null;
$user = $username ? getUserData($username) : null;

if (!$user) {
    header('Location: index.php');
    exit;
}

// Handle ganti target
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_target'])) {
    $new_target = (int)$_POST['target'];
    $new_reason = trim($_POST['reason']) ?: $user->getReason();

    $user->setTarget($new_target);
    $user->setReason($new_reason); // ✅ pakai setter, bukan $user->reason =
    $user->save();
    
    $_SESSION['message'] = "✅ Target & alasan berhasil diubah!";
    header('Location: home.php');
    exit;
}

// Handle reset target
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_target'])) {
    $user->setTarget(0);
    $user->setReason('Belum diisi'); // ✅ pakai setter
    $user->save();
    
    $_SESSION['message'] = "✅ Target direset ke 0. Riwayat tetap tersimpan.";
    header('Location: home.php');
    exit;
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
      display: flex; align-items: center; gap: 10px; margin-bottom: 25px;
    }
    .back-btn {
      background: none; border: none; font-size: 1.2rem; color: #6bbfeb;
      cursor: pointer; padding: 8px 12px; border-radius: 50%;
    }
    .back-btn:hover { background: #e3f2fd; }
    .header-title { font-size: 1.6rem; color: #6bbfeb; font-weight: 500; }

    .section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }
    .section h2 {
      display: flex; align-items: center; gap: 8px;
      color: #333; font-size: 1.1rem; margin-bottom: 10px; font-weight: bold;
    }
    .section p, .section .hint {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 15px;
      line-height: 1.5;
    }
    .hint {
      background: #fff8e1;
      padding: 12px;
      border-radius: 8px;
      font-style: italic;
      border-left: 4px solid #ffc107;
    }

    label {
      display: block; margin: 12px 0 5px; font-weight: bold; color: #333;
    }
    input {
      width: 100%; padding: 12px 16px; border: 1px solid #ddd;
      border-radius: 8px; font-size: 16px; outline: none;
    }
    input:focus { border-color: #6bbfeb; }

    .btn {
      width: 100%; padding: 12px;
      background: #e0e0e0; color: #333;
      border: none; border-radius: 8px;
      font-size: 16px; font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn:hover { background: #d0d0d0; }
    .btn-primary { background: #6bbfeb; color: white; }
    .btn-primary:hover { background: #4da1d1; }
    .btn-danger { background: #d32f2f; color: white; }
    .btn-danger:hover { background: #b71c1c; }

    a.back-link {
      display: block; text-align: center;
      color: #6bbfeb; text-decoration: underline;
      margin-top: 20px; font-size: 0.95rem;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="header">
    <a href="home.php" class="back-btn">←</a>
    <h1 class="header-title">Pengaturan</h1>
  </div>

  <!-- 🔄 Ganti Pengguna -->
  <div class="section">
    <h2><span style="font-size:1.2rem;">🚪</span> Ganti Pengguna</h2>
    <div class="hint">
      Klik tombol <strong>“Keluar”</strong> (gambar pintu) di halaman beranda untuk mengisi data baru di halaman awal.
    </div>
    <p style="font-size:0.85rem; color:#888; margin-top:10px;">
      👉 Data pengguna saat ini (<strong><?= htmlspecialchars($user->getUsername()) ?></strong>) tetap tersimpan.
    </p>
  </div>

  <!-- ✏ Ganti Target Tabungan -->
  <div class="section">
    <h2><span style="font-size:1.2rem;">🎯</span> Ganti Target Tabungan</h2>
    <p>Ubah target atau alasan tanpa menghapus riwayat.</p>
    
    <form method="POST">
      <input type="hidden" name="change_target" value="1">

      <label>Target Baru (Rp)</label>
      <input type="number" name="target" 
             value="<?= htmlspecialchars($user->getTarget()) ?>" 
             min="0" required autocomplete="off">

      <label>Alasan Baru (Opsional)</label>
      <input type="text" name="reason" 
             value="<?= htmlspecialchars($user->getReason()) ?>" 
             placeholder="e.g. Beli laptop baru">

      <button type="submit" class="btn btn-primary">Ubah Target</button>
    </form>
  </div>

  <!-- 🗑 Reset Target -->
  <div class="section">
    <h2><span style="font-size:1.2rem;">🧹</span> Reset Target</h2>
    <p>Kembalikan target ke 0 tanpa menghapus riwayat transaksi.</p>
    
    <form method="POST">
      <input type="hidden" name="reset_target" value="1">
      <button type="submit" class="btn btn-danger">Reset Target ke 0</button>
    </form>
  </div>

  <a href="home.php" class="back-link">← Kembali ke Beranda</a>
</div>

</body>
</html>