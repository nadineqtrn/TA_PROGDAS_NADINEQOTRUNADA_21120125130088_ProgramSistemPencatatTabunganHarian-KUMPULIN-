<?php

class User {
    private $username;
    private $target;
    private $reason;
    private $balance;
    private $transactions;
    private $lastActivity;
    private $congratsShown;
    private $mainWallet;

    public function __construct($username, $target, $reason, $mainWallet = 'DANA') {
        $this->username = $username;
        $this->target = $target;
        $this->reason = $reason;
        $this->balance = 0;
        $this->transactions = [];
        $this->lastActivity = date('Y-m-d');
        $this->congratsShown = false;
        $this->mainWallet = $mainWallet;
    }

    // === GETTER ===
    public function getUsername() { return $this->username; }
    public function getTarget() { return $this->target; }
    public function getReason() { return $this->reason; }
    public function getBalance() { return $this->balance; }
    public function getTransactions() { return $this->transactions; }
    public function getLastActivity() { return $this->lastActivity; }
    public function getMainWallet() { return $this->mainWallet; }

    // === SETTER  ===
    public function setTarget($target) {
        if ($target > 0) {
            $this->target = $target;
        }
    }

    public function addIncome($amount, $location = null) {
        $location = $location ?? $this->mainWallet;
        $this->balance += $amount;
        $this->transactions[] = [
            'type' => 'Masuk',
            'amount' => $amount,
            'location' => $location,
            'date' => date('Y-m-d H:i:s')
        ];
        $this->lastActivity = date('Y-m-d');
    }

    public function addExpense($amount, $source = null) {
        $source = $source ?? $this->mainWallet;
        $this->balance -= $amount;
        $this->transactions[] = [
            'type' => 'Keluar',
            'amount' => $amount,
            'source' => $source,
            'date' => date('Y-m-d H:i:s')
        ];
        $this->lastActivity = date('Y-m-d');
    }

    public function save() {
        $data = [
            'username' => $this->username,
            'target' => $this->target,
            'reason' => $this->reason,
            'balance' => $this->balance,
            'transactions' => $this->transactions,
            'last_activity' => $this->lastActivity,
            'congrats_shown' => $this->congratsShown,
            'main_wallet' => $this->mainWallet
        ];

        $safeUser = preg_replace('/[^a-zA-Z0-9_\-]/', '', $this->username);
        $dir = __DIR__ . '/data';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents("$dir/{$safeUser}.json", json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function load($username) {
        $safeUser = preg_replace('/[^a-zA-Z0-9_\-]/', '', $username);
        $file = __DIR__ . "/data/{$safeUser}.json";
        if (!file_exists($file)) return null;

        $json = json_decode(file_get_contents($file), true);
        if (!$json) return null;

        $mainWallet = $json['main_wallet'] ?? 'DANA';
        $user = new User(
            $json['username'],
            $json['target'],
            $json['reason'],
            $mainWallet
        );

        $user->balance = $json['balance'] ?? 0;
        $user->transactions = $json['transactions'] ?? [];
        $user->lastActivity = $json['last_activity'] ?? date('Y-m-d');
        $user->congratsShown = $json['congrats_shown'] ?? false;
        $user->mainWallet = $mainWallet;

        return $user;
    }
}