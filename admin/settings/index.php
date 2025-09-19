<?php
session_start();
include '../../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil data pengaturan
$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$setting = $stmt->fetch();

$sukses = '';
$error = '';

if ($_POST) {
    $nama_agen = trim($_POST['nama_agen']);
    $slogan = trim($_POST['slogan']);
    $telepon_pusat = trim($_POST['telepon_pusat']);
    $alamat_pusat = trim($_POST['alamat_pusat']);

    // Handle upload logo
    $logo_path = $setting['logo_path'] ?? '';
    if ($_FILES['logo']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            $logo_filename = 'logo.' . $file_ext;
            $logo_path = '../uploads/' . $logo_filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                // Hapus logo lama jika ada
                if (!empty($setting['logo_path']) && file_exists($setting['logo_path']) && $setting['logo_path'] != $logo_path) {
                    unlink($setting['logo_path']);
                }
            } else {
                $error = "Gagal mengupload logo.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE settings 
                SET nama_agen = ?, slogan = ?, telepon_pusat = ?, alamat_pusat = ?, logo_path = ?
                WHERE id = 1
            ");
            $stmt->execute([$nama_agen, $slogan, $telepon_pusat, $alamat_pusat, $logo_path]);
            $sukses = "✅ Pengaturan berhasil disimpan!";
        } catch (Exception $e) {
            $error = "❌ Gagal menyimpan pengaturan: " . $e->getMessage();
        }
    }
}

// Jika belum ada data, buat default
if (!$setting) {
    $pdo->query("INSERT INTO settings (nama_agen, slogan, telepon_pusat) VALUES ('Agen Minyak Tanah', 'Melayani dengan Profesional', '0812-XXXX-XXXX')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚙️ Kelola Pengaturan Situs - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard/index.php">
                <i class="fas fa-cog me-2"></i> Pengaturan Situs
            </a>
            <div class="d-flex align-items-center">
                <a href="../dashboard/index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
                <a href="../../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cog me-2"></i> Kelola Pengaturan Situs</h3>
                        <p class="text-muted mb-0">Atur identitas dan kontak agen minyak tanah Anda</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($sukses): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $sukses ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-building me-2"></i> Nama Agen</label>
                                <input type="text" name="nama_agen" class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($setting['nama_agen'] ?? '') ?>" required>
                                <div class="invalid-feedback">Nama agen wajib diisi</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-comment me-2"></i> Slogan</label>
                                <input type="text" name="slogan" class="form-control" 
                                       value="<?= htmlspecialchars($setting['slogan'] ?? '') ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-phone me-2"></i> Telepon Pusat</label>
                                <input type="text" name="telepon_pusat" class="form-control" 
                                       value="<?= htmlspecialchars($setting['telepon_pusat'] ?? '') ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i> Alamat Pusat</label>
                                <textarea name="alamat_pusat" class="form-control" rows="3"><?= htmlspecialchars($setting['alamat_pusat'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-image me-2"></i> Logo Agen (Opsional)</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <?php if (!empty($setting['logo_path']) && file_exists($setting['logo_path'])): ?>
                                    <div class="mt-2">
                                        <img src="<?= $setting['logo_path'] ?>" alt="Logo Saat Ini" height="80" class="rounded">
                                        <p class="text-muted small mt-2">Logo saat ini</p>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">Ukuran maksimal 2MB. Format: JPG, PNG, GIF</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                                </button>
                                <a href="../dashboard/index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview Halaman Depan -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-eye me-2"></i> Preview Halaman Depan</h5>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-4 rounded">
                            <h4 class="mb-3">🛢️ <?= htmlspecialchars($setting['nama_agen'] ?? 'Agen Minyak Tanah') ?></h4>
                            <p class="text-muted"><?= htmlspecialchars($setting['slogan'] ?? 'Slogan belum diatur') ?></p>
                            <p><i class="fas fa-phone me-2"></i> <?= htmlspecialchars($setting['telepon_pusat'] ?? 'Belum diatur') ?></p>
                            <?php if (!empty($setting['logo_path']) && file_exists($setting['logo_path'])): ?>
                                <img src="<?= $setting['logo_path'] ?>" alt="Logo" height="60" class="mt-2">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validasi form Bootstrap
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
