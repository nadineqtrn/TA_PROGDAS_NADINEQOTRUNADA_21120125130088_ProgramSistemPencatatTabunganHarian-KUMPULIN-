<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // matikan error display di production, tapi masih log
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $target = (int)$_POST['target'];
    $reason = trim($_POST['reason']);

    if ($username && $target > 0 && $reason) {
        $user = new User($username, $target, $reason);
        $user->save();
        $_SESSION['user'] = $username;
    }
}

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['user'];
$user = getUserData($username);

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$data = [
    'username'      => $user->getUsername() ?? $username,
    'target'        => $user->getTarget() ?? 0,
    'reason'        => $user->getReason() ?? 'Belum diisi',
    'balance'       => $user->getBalance() ?? 0,
    'transactions'  => $user->getTransactions() ?? [],
    'last_activity' => $user->getLastActivity() ?? date('Y-m-d'),
    'mainWallet'    => $user->getMainWallet() ?? 'Lainnya',
];

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kumpulin • Home</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
    body { background: #f0f8ff; padding: 20px; min-height: 100vh; }
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 0 10px; }
    header h1 { color: #6bbfeb; font-size: 1.8rem; }
    .greeting { font-weight: bold; color: #333; }

    /* Progress */
    .progress-section { background: white; border-radius: 16px; padding: 25px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .progress-stats { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; text-align: center; }
    .stat .label { font-size: 0.9rem; color: #666; }
    .stat .value { font-size: 1.3rem; font-weight: bold; color: #333; }
    .progress-bar { height: 12px; background: #e0e0e0; border-radius: 6px; overflow: hidden; }
    .progress-fill { height: 100%; background: #6bbfeb; width: 0%; border-radius: 6px; }
    .progress-percent { text-align: center; margin-top: 8px; font-weight: bold; color: #6bbfeb; }

    /* Alasan Nabung */
    .reason-section { background: #fff8e1; border-radius: 12px; padding: 16px; margin: 25px auto; max-width: 500px; text-align: center; border-left: 4px solid #ffc107; box-shadow: 0 2px 8px rgba(0,0,0,0.05); animation: fadeIn 0.8s; }
    .reason-section div:first-child { font-size: 1.2rem; color: #e65100; margin-bottom: 8px; }
    .reason-section div:last-child { font-weight: bold; color: #333; font-size: 1.1rem; line-height: 1.5; }

    /* Buttons */
    .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; max-width: 500px; margin: 30px auto; }
    .action-btn { padding: 20px; border: none; border-radius: 16px; font-size: 16px; font-weight: bold; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.2s; }
    .action-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
    .income { background: #e3f2fd; color: #1976d2; }
    .expense { background: #ffebee; color: #d32f2f; }
    .history { background: #f3e5f5; color: #7b1fa2; }
    .settings { background: #fff8e1; color: #e65100; text-decoration: none; }

    /* Dialog (Pop-up) */
    dialog { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); border: none; border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.3); padding: 30px; width: 90%; max-width: 500px; z-index: 1000; background: white; max-height: 80vh; overflow-y: auto; }
    dialog::backdrop { background: rgba(0,0,0,0.7); }
    .close-btn { position: absolute; top: 15px; right: 15px; font-size: 24px; background: none; border: none; cursor: pointer; color: #888; }
    dialog h2 { margin-bottom: 20px; color: #333; text-align: center; }
    input, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
    .quick-amounts { display: flex; gap: 8px; flex-wrap: wrap; margin: 12px 0; }
    .btn-quick { padding: 8px 12px; background: #e0e0e0; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; transition: background 0.2s; }
    .btn-quick:hover { background: #d0d0d0; }
    .modal-actions { display: flex; gap: 10px; margin-top: 20px; width: 100%; }
    .btn-outline { flex: 1; background: #eee; color: #555; border: none; padding: 10px; border-radius: 8px; cursor: pointer; }
    .btn-primary { flex: 2; background: #6bbfeb; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .btn-danger { flex: 2; background: #d32f2f; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold; }

    /* Notification */
    .notification { position: fixed; top: 60px; left: 50%; transform: translateX(-50%); background: #6bbfeb; color: white; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 2000; animation: slideIn 0.3s ease-out; max-width: 90%; text-align: center; font-weight: bold; }
    @keyframes slideIn { from { opacity: 0; transform: translate(-50%, -20px); } to { opacity: 1; transform: translate(-50%, 0); } }

    /* Reminder */
    .reminder-banner { background: #fff8e1; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px auto; max-width: 500px; text-align: center; font-weight: bold; color: #e65100; }

    /* Logout */
    .logout { display: block; margin: 30px auto 0; padding: 12px 30px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; color: #666; text-decoration: none; width: fit-content; }
    .logout:hover { background: #e9ecef; }

    /* Congrats Modal */
    #congratsModal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); display: flex; justify-content: center; align-items: center; z-index: 3000; animation: fadeIn 0.5s; }
    #congratsModal .content { background: white; padding: 40px; border-radius: 20px; text-align: center; max-width: 90%; width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); animation: popIn 0.6s; position: relative; overflow: hidden; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes popIn { 0% { transform: scale(0.7); opacity: 0; } 70% { transform: scale(1.05); } 100% { transform: scale(1); opacity: 1; } }
  </style>
</head>
<body>
<header>
  <h1> 🐓 Kumpulin </h1>
  <div class="greeting">Halo, <strong><?= htmlspecialchars($username) ?></strong>!</div>
</header>

<!-- Reminder -->
<?php
$last = new DateTime($data['last_activity'] ?? '');
$today = new DateTime();
if ($last->diff($today)->days > 1): ?>
<div class="reminder-banner">
  📅 Ihh! Lupa nabung ya kemarin… Ayo hari ini lebih semangat! 💪
</div>
<?php endif; ?>

<!-- Progress -->
<?php
$target = (int)($data['target'] ?? 0);
$balance = (int)($data['balance'] ?? 0);
$progressPercent = $target > 0 ? min(100, ($balance / $target) * 100) : 0;
?>

<div class="progress-section">
  <?php if ($target == 0): ?>
    <div style="text-align: center; padding: 20px; color: #e65100; font-weight: bold; line-height: 1.5;">
      ⚠️ Target belum diatur.<br>
      <a href="pengaturan.php" style="color: #6bbfeb; font-weight: bold;">Atur target dulu yuk! 😊</a>
    </div>
  <?php else: ?>
    <div class="progress-stats">
      <div class="stat">
        <div class="label">Target</div>
        <div class="value">Rp <?= number_format($target) ?></div>
      </div>
      <div class="stat">
        <div class="label">Saldo</div>
        <div class="value">Rp <?= number_format($balance) ?></div>
      </div>
      <div class="stat">
        <div class="label">Kurang</div>
        <div class="value">Rp <?= number_format(max(0, $target - $balance)) ?></div>
      </div>
    </div>

    <div class="progress-bar">
      <div class="progress-fill" style="width: <?= $progressPercent ?>%;"></div>
    </div>
    <div class="progress-percent">
      <?= number_format($progressPercent, 1) ?>% tercapai
    </div>
  <?php endif; ?>
</div>

<!-- Alasan Nabung -->
<div class="reason-section">
  <div>Nabung buat...</div>
  <div>“<?= htmlspecialchars($data['reason']) ?>”</div>
</div>

<!-- Tombol Aksi -->
<div class="action-grid">
  <button class="action-btn income" onclick="document.getElementById('incomeDialog').showModal()">
    📥<br>Pemasukan
  </button>
  <button class="action-btn expense" onclick="document.getElementById('expenseDialog').showModal()">
    📤<br>Pengeluaran
  </button>
  <button class="action-btn history" onclick="document.getElementById('historyDialog').showModal()">
    📋<br>Riwayat
  </button>
  <a href="pengaturan.php" class="action-btn settings">
    ⚙️<br>Pengaturan
  </a>
</div>

<a href="logout.php" class="logout"> 🚪 Keluar </a>

<!-- === POP-UP PEMASUKAN === -->
<dialog id="incomeDialog" onshow="document.getElementById('incomeAmount').focus()">
  <button class="close-btn" onclick="this.parentElement.close()">×</button>
  <h2> ➕ Tambah Pemasukan </h2>
  <form method="POST" action="pemasukan.php">
    <input type="hidden" name="user" value="<?= htmlspecialchars($username) ?>">

    <label>Jumlah (Rp)</label>
    <input 
      type="text" 
      id="incomeAmount" 
      name="amount_raw" 
      required 
      placeholder="e.g. 10000"
      oninput="formatRp(this)"
    >
    <input type="hidden" name="amount" id="incomeAmountValue">

    <!-- Opsi Cepat -->
    <div class="quick-amounts">
      <button type="button" class="btn-quick" onclick="setQuickIncome(10000)">10rb</button>
      <button type="button" class="btn-quick" onclick="setQuickIncome(50000)">50rb</button>
      <button type="button" class="btn-quick" onclick="setQuickIncome(100000)">100rb</button>
      <button type="button" class="btn-quick" onclick="setQuickIncome(500000)">500rb</button>
      <button type="button" class="btn-quick" onclick="setQuickIncome(1000000)">1jt</button>
    </div>

    <input type="hidden" name="location" value="<?= htmlspecialchars($data['mainWallet']) ?>">
    <div style="margin: 10px 0; padding: 10px; background: #e3f2fd; border-radius: 8px; font-size: 0.9rem;">
      💰 Nabung di: <strong><?= htmlspecialchars($data['mainWallet']) ?></strong>
    </div>

    <div class="modal-actions">
      <button type="button" class="btn-outline" onclick="document.getElementById('incomeDialog').close()">Batal</button>
      <button type="submit" class="btn-primary">Simpan & Nabung!</button>
    </div>
  </form>
</dialog>

<script>
function formatRp(input) {
  let val = input.value.replace(/[^0-9]/g, '');
  if (val) {
    input.value = 'Rp ' + Number(val).toLocaleString('id-ID');
    document.getElementById('incomeAmountValue').value = val;
  } else {
    input.value = '';
    document.getElementById('incomeAmountValue').value = '';
  }
}

function setQuickIncome(val) {
  const input = document.getElementById('incomeAmount');
  input.value = 'Rp ' + val.toLocaleString('id-ID');
  document.getElementById('incomeAmountValue').value = val;
  input.focus();
}
</script>

<!-- === POP-UP PENGELUARAN === -->
<dialog id="expenseDialog" onshow="document.getElementById('expenseAmount').focus()">
  <button class="close-btn" onclick="this.parentElement.close()">×</button>
  <h2> ➖ Tambah Pengeluaran </h2>
  <form method="POST" action="pengeluaran.php">
    <input type="hidden" name="user" value="<?= htmlspecialchars($username) ?>">

    <label>Jumlah (Rp)</label>
    <input 
      type="text"
      id="expenseAmount" 
      name="amount_raw" 
      required 
      placeholder="e.g. 5000"
      oninput="formatRp(this)"
    >
    <input type="hidden" name="amount" id="expenseAmountValue">

    <!-- Opsi Cepat -->
    <div class="quick-amounts">
      <button type="button" class="btn-quick" onclick="setQuick(10000)">10rb</button>
      <button type="button" class="btn-quick" onclick="setQuick(50000)">50rb</button>
      <button type="button" class="btn-quick" onclick="setQuick(100000)">100rb</button>
      <button type="button" class="btn-quick" onclick="setQuick(500000)">500rb</button>
      <button type="button" class="btn-quick" onclick="setQuick(1000000)">1jt</button>
    </div>

    <input type="hidden" name="source" value="<?= htmlspecialchars($data['mainWallet']) ?>">
    <div style="margin: 10px 0; padding: 10px; background: #ffebee; border-radius: 8px; font-size: 0.9rem;">
      💸 Diambil dari: <strong><?= htmlspecialchars($data['mainWallet']) ?></strong>
    </div>

    <div class="modal-actions">
      <button type="button" class="btn-outline" onclick="document.getElementById('expenseDialog').close()">Batal</button>
      <button type="submit" class="btn-danger">Tarik Dana</button>
    </div>
  </form>
</dialog>

<script>
function formatRp(input) {
  let val = input.value.replace(/[^0-9]/g, '');
  if (val) {
    input.value = 'Rp ' + Number(val).toLocaleString('id-ID');
    document.getElementById('expenseAmountValue').value = val;
  } else {
    input.value = '';
    document.getElementById('expenseAmountValue').value = '';
  }
}

function setQuick(val) {
  const el = document.getElementById('expenseAmount');
  el.value = 'Rp ' + val.toLocaleString('id-ID');
  document.getElementById('expenseAmountValue').value = val;
}
</script>

<!-- === POP-UP RIWAYAT === -->
<dialog id="historyDialog">
  <button class="close-btn" onclick="document.getElementById('historyDialog').close()">×</button>
  <h2> 📋 Riwayat Transaksi </h2>
  <div style="max-height: 300px; overflow-y: auto; margin-top: 15px;">
  <?php if (empty($data['transactions'])): ?>
    <p style="text-align: center; color: #888; padding: 20px;">
      Belum ada transaksi. Yuk, nabung dulu! 🐓
    </p>
  <?php else: ?>
    <ul style="list-style: none; padding: 0;">
    <?php foreach (array_reverse($data['transactions']) as $t):
      $color = $t['type'] === 'Masuk' ? '#1976d2' : '#d32f2f';
      $icon = $t['type'] === 'Masuk' ? '➕' : '➖';
    ?>
      <li style="padding: 12px; border-bottom: 1px solid #eee;">
        <strong style="color: <?= $color ?>"><?= $icon ?> Rp <?= number_format($t['amount']) ?></strong><br>
        <small style="color: #666;">
        <?php if ($t['type'] === 'Masuk'): ?>
          <?= htmlspecialchars($t['location']) ?>
        <?php else: ?>
          <?= htmlspecialchars($t['source']) ?>
        <?php endif; ?>
          • <?= date('d M Y, H:i', strtotime($t['date'])) ?>
        </small>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  </div>
</dialog>

<!-- Notifikasi -->
<?php if ($message): ?>
<div class="notification" id="notif"><?= htmlspecialchars($message) ?></div>
<script>
setTimeout(() => { document.getElementById('notif').style.display = 'none'; }, 3000);
</script>
<?php endif; ?>

<!-- Congrats Modal -->
<?php if ($target > 0 && $balance >= $target): ?>
<div id="congratsModal">
  <div class="content">
    <div style="font-size: 4.5rem; margin-bottom: 15px;"> 🎉 </div>
    <h2 style="color: #6bbfeb; margin: 0 0 10px; font-size: 1.8rem;"> SELAMAT! </h2>
    <p style="font-size: 1.5rem; color: #333; margin: 15px 0; font-weight: bold; line-height: 1.4;">
      Udah cukup buat<br>
      <span style="color: #ffc107; font-size: 1.7rem;">
        “<?= htmlspecialchars($data['reason']) ?>”
      </span>
    </p>
    <p style="font-size: 1.3rem; color: #4caf50; margin: 20px 0;"> ✅ Target tercapai! </p>
    <p style="color: #666; font-style: italic; margin: 20px 0; line-height: 1.5;">
      Kamu keren banget nih! Jadi bisa tidur nyenyak deh. 👏<br>
      Lanjut ke mimpi berikutnya?
    </p>
    <button onclick="closeCongrats()" style="
      background: #6bbfeb; color: white; border: none;
      padding: 14px 45px; font-size: 18px; border-radius: 50px;
      cursor: pointer; font-weight: bold; box-shadow: 0 4px 12px rgba(107, 191, 235, 0.4);
      transition: all 0.2s;
    ">Lanjut Nabung!</button>
  </div>
</div>
<script>
function closeCongrats() {
  document.getElementById('congratsModal').style.display = 'none';
  fetch('save_congrats.php?user=<?= urlencode($username) ?>');
}
// Confetti
function launchConfetti() {
  const c = document.createElement('div');
  c.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:2999';
  document.body.appendChild(c);

  for (let i = 0; i < 100; i++) {
    const p = document.createElement('div');
    p.style.cssText = `position:absolute;width:8px;height:8px;background: ${['#ff5252','#6bbfeb','#ffc107','#4caf50'][Math.floor(Math.random()*4)]};border-radius:50%;left:${Math.random()*100}vw;top:-20px;animation:fall ${Math.random()*3+2}s linear forwards`;
    c.appendChild(p);
  }

  document.head.insertAdjacentHTML('beforeend', `
    <style>@keyframes fall{to{transform:translateY(105vh) rotate(${Math.random()>0.5?'360deg':'-360deg'})}}</style>
  `);

  setTimeout(() => c.remove(), 5000);
}
if (document.getElementById('congratsModal')) setTimeout(launchConfetti, 300);
</script>
<?php endif; ?>
</body>
</html>