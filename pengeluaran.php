<?php
session_start();
require_once 'helpers.php';

$username = $_POST['user'] ?? '';
$amount = (int)($_POST['amount'] ?? 0);
$source = $_POST['source'] ?? 'DANA';

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

if ($user->getBalance() < $amount) {
    $_SESSION['message'] = "❌ Saldo tidak cukup! Saldo: Rp " . number_format($user->getBalance());
    header('Location: home.php');
    exit;
}

$user->addExpense($amount);
$user->save();

$locations = getLocations();
$locationLabel = $locations[$user->getMainWallet()] ?? $user->getMainWallet();

$quotes = [
    "Tarik dana Rp " . number_format($amount) . " dari {$locationLabel} berhasil!",
    "Waduh, keluar dari {$locationLabel} — jangan jajan mulu ya!",
    "Pengeluaran tercatat: -Rp " . number_format($amount) . " (sumber: {$locationLabel})",
    "Dana keluar dari {$locationLabel} — semoga ngga nyesel sii "
];
$_SESSION['message'] = $quotes[array_rand($quotes)];

header('Location: home.php');
exit;
?>