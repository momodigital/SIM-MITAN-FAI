<?php
/**
 * CRON JOB: Laporan Mingguan Otomatis
 * Jalankan setiap Senin pagi: 0 8 * * 1
 */

include '../config.php';
include '../notifikasi/kirim_telegram.php';

try {
    // Hitung periode minggu ini
    $hari_ini = new DateTime();
    $senin_minggu_ini = clone $hari_ini;
    $senin_minggu_ini->modify('this week monday');
    $minggu_ini = $senin_minggu_ini->format('Y-m-d');
    
    $minggu_depan = clone $senin_minggu_ini;
    $minggu_depan->modify('+7 days');
    $minggu_depan = $minggu_depan->format('Y-m-d');

    // Ambil data laporan mingguan
    $stmt = $pdo->prepare("
        SELECT 
            p.nama_pangkalan,
            COUNT(*) as total_transaksi,
            SUM(b.jumlah_liter) as total_liter,
            SUM(b.total_harga) as total_pendapatan
        FROM bongkar_muatan b
        JOIN pangkalan p ON b.id_pangkalan = p.id
        WHERE DATE(b.tanggal) >= ? AND DATE(b.tanggal) < ?
        GROUP BY p.id
        ORDER BY total_pendapatan DESC
        LIMIT 10
    ");
    $stmt->execute([$minggu_ini, $minggu_depan]);
    $laporan = $stmt->fetchAll();

    $total_transaksi = 0;
    $total_liter = 0;
    $total_pendapatan = 0;
    
    foreach ($laporan as $row) {
        $total_transaksi += $row['total_transaksi'];
        $total_liter += $row['total_liter'];
        $total_pendapatan += $row['total_pendapatan'];
    }

    // Format pesan Telegram
    $pesan = "📊 <b>LAPORAN MINGGUAN OTOMATIS</b>\n";
    $pesan .= "Periode: <b>" . $senin_minggu_ini->format('d M Y') . "</b> - <b>" . $hari_ini->format('d M Y') . "</b>\n";
    $pesan .= "📅 " . $hari_ini->format('d-m-Y H:i') . "\n\n";
    $pesan .= "📈 <b>Ringkasan:</b>\n";
    $pesan .= "• Total Transaksi: " . number_format($total_transaksi) . "\n";
    $pesan .= "• Total Drom: " . number_format($total_liter / 200, 1) . " drom\n";
    $pesan .= "• Total Pendapatan: Rp " . number_format($total_pendapatan, 0, ',', '.') . "\n\n";
    $pesan .= "🏆 <b>Top 10 Pangkalan:</b>\n";

    $no = 1;
    foreach ($laporan as $row) {
        $drom = $row['total_liter'] / 200;
        $pesan .= $no . ". " . htmlspecialchars($row['nama_pangkalan']) . "\n";
        $pesan .= "   " . number_format($drom, 1) . " drom | Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "\n";
        $no++;
    }

    // Ganti dengan chat_id admin/grup Telegram kamu
    $chat_id = "MASUKKAN_CHAT_ID_ADMIN_ANDA";
    $result = kirim_notifikasi_telegram($chat_id, $pesan);
    
    echo "Laporan mingguan berhasil dikirim!\n";
    echo "Periode: " . $senin_minggu_ini->format('d-m-Y') . " - " . $hari_ini->format('d-m-Y') . "\n";
    echo "Total Transaksi: " . $total_transaksi . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
