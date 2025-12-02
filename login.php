<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $file = "data/" . preg_replace('/[^a-zA-Z0-9_\-]/', '', $username) . ".json";
    if (file_exists($file)) {
        $_SESSION['user'] = $username;
        header('Location: home.php');
        exit;
    } else {
        $error = "Username tidak ditemukan. Cek kembali atau buat akun baru.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk • Kumpulin</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
    body { background: #f0f8ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
    .container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 500px; width: 100%; }
    .logo { font-size: 2.5rem; margin-bottom: 15px; color: #6bbfeb; }
    h1 { color: #333; margin: 15px 0; }
    .tagline { color: #666; margin-bottom: 30px; font-style: italic; }
    input, button { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
    button { background: #6bbfeb; color: white; border: none; padding: 12px 30px; font-size: 18px; border-radius: 50px; cursor: pointer; font-weight: bold; }
    button:hover { background: #4a90b3; }
    .error { color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 8px; margin: 15px 0; }
    .back-link { display: inline-block; margin-top: 20px; color: #6bbfeb; text-decoration: underline; }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">🐓</div>
    <h1>🔐 Masuk ke Akunmu</h1>
    <p class="tagline">Masukkan username yang pernah kamu gunakan sebelumnya.</p>
   
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
   
    <form method="POST">
      <input type="text" name="username" placeholder="Username (misal: SiKayaMendadak)" required maxlength="20" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      <button type="submit">Masuk ke Akun</button>
    </form>
   
    <a href="index.php" class="back-link">← Kembali ke halaman utama</a>
  </div>
</body>
</html>