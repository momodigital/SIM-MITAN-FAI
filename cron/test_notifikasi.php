<?php
/**
 * File untuk testing notifikasi
 * Akses via browser: http://domain.com/cron/test_notifikasi.php
 */

include '../config.php';
include '../notifikasi/kirim_telegram.php';

// Ganti dengan chat_id kamu
$chat_id = "MASUKKAN_CHAT_ID_KAMU";

// Test notifikasi STNK
$pesan_stnk = "✅ <b>TEST NOTIFIKASI STNK</b>\n";
$pesan_stnk .= "Ini adalah pesan test notifikasi STNK\n";
$pesan_stnk .= "Kendaraan: B 1234 ABC\n";
$pesan_stnk .= "Sisa Waktu: 5 hari\n";
$result1 = kirim_notifikasi_telegram($chat_id, $pesan_stnk);

// Test laporan mingguan
$pesan_laporan = "✅ <b>TEST LAPORAN MINGGUAN</b>\n";
$pesan_laporan .= "Ini adalah pesan test laporan mingguan\n";
$pesan_laporan .= "Total Transaksi: 25\n";
$pesan_laporan .= "Total Pendapatan: Rp 75.000.000\n";
$result2 = kirim_notifikasi_telegram($chat_id, $pesan_laporan);

echo "<h3>Hasil Test Notifikasi</h3>";
echo "<p>Notifikasi STNK Test: " . ($result1 ? "BERHASIL" : "GAGAL") . "</p>";
echo "<p>Laporan Mingguan Test: " . ($result2 ? "BERHASIL" : "GAGAL") . "</p>";
echo "<p>Periksa Telegram Anda untuk melihat hasilnya.</p>";
?>
