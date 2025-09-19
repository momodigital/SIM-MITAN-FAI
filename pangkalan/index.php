<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'operator'])) {
    header("Location: ../login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM pangkalan WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Pangkalan berhasil dihapus!'];
    header("Location: index.php");
    exit;
}

// Ambil semua pangkalan
$stmt = $pdo->query("
    SELECT 
        id,
        no_kontrak,
        nama_pangkalan,
        nama_pemilik,
        kelurahan,
        kecamatan,
        telepon,
        latitude,
        longitude,
        created_at
    FROM pangkalan 
    ORDER BY nama_pangkalan ASC
");
$pangkalan_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏪 Manajemen Pangkalan - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../admin/dashboard/index.php">
                <i class="fas fa-store me-2"></i> Manajemen Pangkalan
            </a>
            <div class="d-flex align-items-center">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="tambah.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-plus me-1"></i> Tambah Pangkalan
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
                <h3><i class="fas fa-list me-2"></i> Daftar Pangkalan</h3>
                <p class="text-muted mb-0">Total: <?= number_format(count($pangkalan_list)) ?> pangkalan terdaftar</p>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['toast'])): ?>
                    <div class="alert alert-<?= $_SESSION['toast']['type'] ?> alert-dismissible fade show">
                        <?= $_SESSION['toast']['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['toast']); ?>
                <?php endif; ?>

                <?php if (empty($pangkalan_list)): ?>
                    <div class="text-center py-5">
                        <div class="display-4 text-muted mb-3">
                            <i class="fas fa-store-slash"></i>
                        </div>
                        <h4>Belum ada pangkalan terdaftar</h4>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <p class="text-muted">Silakan tambah pangkalan baru.</p>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Tambah Pangkalan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>No. Kontrak</th>
                                    <th>Nama Pangkalan</th>
                                    <th>Pemilik</th>
                                    <th>Lokasi</th>
                                    <th>Telepon</th>
                                    <th>Koordinat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pangkalan_list as $index => $p): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($p['no_kontrak']) ?></strong></td>
                                        <td><?= htmlspecialchars($p['nama_pangkalan']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_pemilik']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($p['kelurahan'] ?? '-') ?>, 
                                            <?= htmlspecialchars($p['kecamatan'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['telepon'])): ?>
                                                <a href="tel:<?= $p['telepon'] ?>" class="text-decoration-none">
                                                    <i class="fas fa-phone me-1"></i> <?= htmlspecialchars($p['telepon']) ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['latitude']) && !empty($p['longitude'])): ?>
                                                <a href="https://www.google.com/maps?q=<?= $p['latitude'] ?>,<?= $p['longitude'] ?>" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-map-marker-alt"></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                                <a href="?delete=<?= $p['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus pangkalan ini?')">
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
