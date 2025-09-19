<?php
session_start();
include '../../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Statistik untuk dashboard
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pangkalan");
$total_pangkalan = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM kendaraan WHERE status = 'aktif'");
$total_kendaraan_aktif = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM sopir WHERE status = 'aktif'");
$total_sopir_aktif = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM kondektur WHERE status = 'aktif'");
$total_kondektur_aktif = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bongkar_muatan WHERE DATE(tanggal) = CURDATE()");
$bongkar_hari_ini = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(jumlah_liter) as total_liter FROM bongkar_muatan WHERE DATE(tanggal) = CURDATE()");
$total_liter = $stmt->fetch()['total_liter'] ?? 0;
$drom_hari_ini = $total_liter > 0 ? $total_liter / 200 : 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_user = $stmt->fetch()['total'];

// Transaksi terbaru (5)
$stmt = $pdo->prepare("
    SELECT 
        b.tanggal,
        p.nama_pangkalan,
        k.no_polisi,
        s.nama_sopir,
        b.jumlah_liter
    FROM bongkar_muatan b
    LEFT JOIN pangkalan p ON b.id_pangkalan = p.id
    LEFT JOIN kendaraan k ON b.id_kendaraan = k.id
    LEFT JOIN sopir s ON b.id_sopir = s.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute();
$transaksi_terbaru = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Dashboard Admin - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?></span>
                <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold">🛢️ Dashboard Agen Minyak Tanah</h1>
            <p class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?> — Berikut ringkasan operasional hari ini</p>
        </div>

        <!-- Statistik Cards -->
        <div class="row g-4 mb-5">
            <!-- Card 1: Pangkalan -->
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="fas fa-store"></i>
                        </div>
                        <h3 class="display-6"><?= number_format($total_pangkalan) ?></h3>
                        <p class="text-muted mb-0">Total Pangkalan</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Kendaraan Aktif -->
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="display-4 text-success mb-3">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="display-6"><?= number_format($total_kendaraan_aktif) ?></h3>
                        <p class="text-muted mb-0">Kendaraan Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Card 3: SDM Aktif -->
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="display-4 text-info mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="display-6"><?= number_format($total_sopir_aktif + $total_kondektur_aktif) ?></h3>
                        <p class="text-muted mb-0">SDM Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Card 4: Transaksi Hari Ini -->
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="display-4 text-warning mb-3">
                            <i class="fas fa-gas-pump"></i>
                        </div>
                        <h3 class="display-6"><?= number_format($bongkar_hari_ini) ?></h3>
                        <p class="text-muted mb-0">Transaksi Hari Ini</p>
                        <small class="text-success"><?= number_format($drom_hari_ini, 1) ?> drom</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcut Menu -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="display-4 mb-3 text-primary">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h5>Input Bongkar</h5>
                        <p class="text-muted">Catat transaksi baru</p>
                        <a href="../../bongkar/tambah.php" class="btn btn-outline-primary">Mulai Input</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="display-4 mb-3 text-success">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5>Laporan</h5>
                        <p class="text-muted">Lihat & export laporan</p>
                        <a href="../../laporan/bongkar.php" class="btn btn-outline-success">Lihat Laporan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="display-4 mb-3 text-info">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5>Pengaturan</h5>
                        <p class="text-muted">Kelola konfigurasi situs</p>
                        <a href="../settings/index.php" class="btn btn-outline-info">Kelola Pengaturan</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i> 5 Transaksi Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pangkalan</th>
                                <th>Kendaraan</th>
                                <th>Sopir</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transaksi_terbaru)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada transaksi</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transaksi_terbaru as $t): 
                                    $drom = $t['jumlah_liter'] / 200;
                                ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($t['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($t['nama_pangkalan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($t['no_polisi'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($t['nama_sopir'] ?? '-') ?></td>
                                    <td><?= number_format($drom, 1) ?> drom</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-bars me-2"></i> Navigasi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="../../pangkalan/index.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-store me-2"></i> Kelola Pangkalan
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../../kendaraan/index.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-truck me-2"></i> Kelola Kendaraan
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../../sopir/index.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-user-tie me-2"></i> Kelola Sopir
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../../kondektur/index.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-user me-2"></i> Kelola Kondektur
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../../user/index.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-users-cog me-2"></i> Kelola Pengguna
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../../peta-pangkalan.php" target="_blank" class="btn btn-outline-danger w-100">
                            <i class="fas fa-map-marked-alt me-2"></i> Peta Pangkalan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
