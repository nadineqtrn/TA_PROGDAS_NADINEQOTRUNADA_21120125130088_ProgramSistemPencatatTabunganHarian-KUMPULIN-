<?php
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'User.php';

function getUserData($username) {
    return User::load($username);
}

function getLocations() {
    return [
        'BRI'       => 'Bank BRI',
        'BCA'       => 'Bank BCA',
        'Mandiri'   => 'Bank Mandiri',
        'BNI'       => 'Bank BNI',
        'DANA'      => 'DANA',
        'OVO'       => 'OVO',
        'ShopeePay' => 'ShopeePay',
        'Gopay'     => 'Gopay',
        'Lainnya'   => 'Lainnya'
    ];
}