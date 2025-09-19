<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'operator'])) {
    header("Location: ../login.php");
    exit;
}

// Handle delete (hanya admin)
if (isset($_GET['delete']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM kendaraan WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Kendaraan berhasil dihapus!'];
    header("Location: index.php");
    exit;
}

// Ambil semua kendaraan
$stmt = $pdo->query("
    SELECT 
        id,
        no_polisi,
        status_stnk,
        tgl_kadaluarsa_stnk,
        catatan,
        created_at
    FROM kendaraan 
    ORDER BY no_polisi ASC
");
$kendaraan_list = $stmt->fetchAll();

// Hitung total trip per kendaraan
$total_trip = [];
foreach ($kendaraan_list as $k) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bongkar_muatan WHERE id_kendaraan = ?");
    $stmt->execute([$k['id']]);
    $total_trip[$k['id']] = $stmt->fetch()['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚚 Manajemen Kendaraan - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../admin/dashboard/index.php">
                <i class="fas fa-truck me-2"></i> Manajemen Kendaraan
            </a>
            <div class="d-flex align-items-center">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="tambah.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-plus me-1"></i> Tambah Kendaraan
                    </a>
                <?php endif; ?>
                <a href="../admin/dashboard/index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list me-2"></i> Daftar Kendaraan</h3>
                <p class="text-muted mb-0">Total: <?= number_format(count($kendaraan_list)) ?> kendaraan terdaftar</p>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['toast'])): ?>
                    <div class="alert alert-<?= $_SESSION['toast']['type'] ?> alert-dismissible fade show">
                        <?= $_SESSION['toast']['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['toast']); ?>
                <?php endif; ?>

                <?php if (empty($kendaraan_list)): ?>
                    <div class="text-center py-5">
                        <div class="display-4 text-muted mb-3">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <h4>Belum ada kendaraan terdaftar</h4>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <p class="text-muted">Silakan tambah kendaraan baru.</p>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Tambah Kendaraan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Plat Nomor</th>
                                    <th>Status STNK</th>
                                    <th>Kadaluarsa STNK</th>
                                    <th>Total Trip</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kendaraan_list as $index => $k): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($k['no_polisi']) ?></strong></td>
                                        <td>
                                            <?php if ($k['status_stnk'] == 'hidup'): ?>
                                                <span class="badge bg-success">Hidup</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Mati</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($k['tgl_kadaluarsa_stnk'])): ?>
                                                <?= date('d-m-Y', strtotime($k['tgl_kadaluarsa_stnk'])) ?>
                                                <?php 
                                                $kadaluarsa = strtotime($k['tgl_kadaluarsa_stnk']);
                                                $hari_ini = time();
                                                $selisih_hari = ($kadaluarsa - $hari_ini) / (60 * 60 * 24);
                                                if ($selisih_hari <= 7 && $selisih_hari >= 0): ?>
                                                    <span class="badge bg-warning text-dark">Segera Perpanjang!</span>
                                                <?php elseif ($selisih_hari < 0): ?>
                                                    <span class="badge bg-danger">Sudah Kadaluarsa!</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="laporan_perjalanan.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <?= number_format($total_trip[$k['id']]) ?> trip
                                            </a>
                                        </td>
                                        <td>
                                            <?= !empty($k['catatan']) ? htmlspecialchars($k['catatan']) : '-' ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                                <a href="?delete=<?= $k['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus kendaraan ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
