<?php
/**
 * CRON JOB: Notifikasi STNK hampir kadaluarsa
 * Jalankan setiap pagi: 0 8 * * *
 */

include '../config.php';
include '../notifikasi/kirim_telegram.php';

try {
    // Cari kendaraan dengan STNK hampir kadaluarsa (7 hari ke depan)
    $stmt = $pdo->prepare("
        SELECT 
            no_polisi,
            tgl_kadaluarsa_stnk
        FROM kendaraan 
        WHERE status_stnk = 'hidup' 
        AND tgl_kadaluarsa_stnk BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY tgl_kadaluarsa_stnk ASC
    ");
    $stmt->execute();
    $kendaraan_list = $stmt->fetchAll();

    if (empty($kendaraan_list)) {
        echo "Tidak ada notifikasi STNK hari ini\n";
        exit;
    }

    // Kirim notifikasi untuk setiap kendaraan
    foreach ($kendaraan_list as $k) {
        $hari_tersisa = (strtotime($k['tgl_kadaluarsa_stnk']) - time()) / (60 * 60 * 24);
        $hari_tersisa = ceil($hari_tersisa);

        $pesan = "⚠️ <b>PERINGATAN STNK HAMPIR KADALUARSA</b>\n";
        $pesan .= "Kendaraan: <b>" . htmlspecialchars($k['no_polisi']) . "</b>\n";
        $pesan .= "Tanggal Kadaluarsa: <b>" . date('d-m-Y', strtotime($k['tgl_kadaluarsa_stnk'])) . "</b>\n";
        $pesan .= "Sisa Waktu: <b>" . $hari_tersisa . " hari</b>\n";
        $pesan .= "Segera lakukan perpanjangan STNK!\n";
        $pesan .= "📅 " . date('d-m-Y H:i');

        // Ganti dengan chat_id admin/grup Telegram kamu
        $chat_id = "MASUKKAN_CHAT_ID_ADMIN_ANDA";
        $result = kirim_notifikasi_telegram($chat_id, $pesan);
        
        echo "Notifikasi terkirim untuk " . $k['no_polisi'] . "\n";
    }

    echo "Proses notifikasi STNK selesai. Total: " . count($kendaraan_list) . " kendaraan\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
