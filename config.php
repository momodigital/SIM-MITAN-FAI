<?php
// Konfigurasi Database
$host = 'localhost';
$dbname = 'agen_minyak';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Konfigurasi Telegram Bot (ganti dengan token bot kamu)
define('TELEGRAM_BOT_TOKEN', 'MASUKKAN_TOKEN_BOT_TELEGRAM_KAMU_DI_SINI');

// Mulai session
session_start();
?>
