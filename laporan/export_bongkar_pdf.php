<?php
include '../config.php';

// Ambil parameter
$awal = $_GET['awal'] ?? date('Y-m-01');
$akhir = $_GET['akhir'] ?? date('Y-m-d');

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT 
        b.tanggal,
        k.no_polisi,
        s.nama_sopir,
        b.jumlah_liter,
        p.nama_pangkalan,
        p.nama_pemilik
    FROM bongkar_muatan b
    JOIN kendaraan k ON b.id_kendaraan = k.id
    JOIN sopir s ON b.id_sopir = s.id
    JOIN pangkalan p ON b.id_pangkalan = p.id
    WHERE DATE(b.tanggal) BETWEEN ? AND ?
    ORDER BY b.tanggal ASC
");
$stmt->execute([$awal, $akhir]);
$transaksi = $stmt->fetchAll();

// Load DomPDF
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

// Buat HTML untuk PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Bongkar Muatan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; color: #0d6efd; }
        .periode { text-align: center; margin-bottom: 30px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .signature { height: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN BONGKAR MUATAN MINYAK TANAH</h2>
        <p>' . (isset($_GET['awal']) ? "Periode: " . date('d-m-Y', strtotime($awal)) . " s/d " . date('d-m-Y', strtotime($akhir)) : "") . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Plat Kendaraan</th>
                <th>Sopir (AMT1)</th>
                <th>Jumlah (Drom)</th>
                <th>Pangkalan (Pemilik)</th>
                <th style="width: 150px;">Tanda Tangan Pemilik Pangkalan</th>
            </tr>
        </thead>
        <tbody>';

foreach ($transaksi as $t):
    $drom = $t['jumlah_liter'] / 200;
    $html .= '<tr>
        <td>' . date('d-m-Y H:i', strtotime($t['tanggal'])) . '</td>
        <td>' . htmlspecialchars($t['no_polisi']) . '</td>
        <td>' . htmlspecialchars($t['nama_sopir']) . '</td>
        <td>' . number_format($drom, 1) . ' drom (' . number_format($t['jumlah_liter'], 0) . ' L)</td>
        <td>' . htmlspecialchars($t['nama_pangkalan']) . ' (' . htmlspecialchars($t['nama_pemilik']) . ')</td>
        <td class="signature">&nbsp;</td>
    </tr>';
endforeach;

$html .= '</tbody>
    </table>
</body>
</html>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("Laporan_Bongkar_Minyak_Tanah_" . date('Ymd') . ".pdf", ["Attachment" => false]);
?>
