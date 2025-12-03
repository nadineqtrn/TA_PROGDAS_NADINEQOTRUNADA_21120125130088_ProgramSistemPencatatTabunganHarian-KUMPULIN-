<?php
session_start();
require_once 'helpers.php';

$username = $_POST['user'] ?? '';
$amount = (int)($_POST['amount'] ?? 0);
$location = $_POST['location'] ?? 'Lainnya';

if (!$username || $amount <= 0) {
    $_SESSION['message'] = "❌ Jumlah wajib diisi!";
    header('Location: home.php');
    exit;
}

$user = getUserData($username);
if (!$user) {
    $_SESSION['message'] = "❌ User tidak ditemukan.";
    header('Location: index.php');
    exit;
}

$user->addIncome($amount);
$user->save();

$locations = getLocations();
$locationLabel = $locations[$user->getMainWallet()] ?? $user->getMainWallet();

$quotes = [
    "Nabung di {$locationLabel} berhasil!",
    "semangatt trus yaa nabung di {$locationLabel} biar cepet selesai ke target>.<",
    "✅ Transaksi berhasil: +Rp " . number_format($amount) . " di {$locationLabel}",
    "🎉 Yey! Nabung lagi di {$locationLabel} — keren banget sihh! 🎉"
];
$_SESSION['message'] = $quotes[array_rand($quotes)];

header('Location: home.php');
exit;
?>