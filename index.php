<?php
include 'config.php';

// Ambil pengaturan situs
$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $stmt->fetch();

// Ambil pengumuman aktif
$stmt = $pdo->query("SELECT * FROM pengumuman WHERE status = 'aktif' ORDER BY created_at DESC LIMIT 1");
$pengumuman = $stmt->fetch();

// Statistik real-time
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pangkalan");
$total_pangkalan = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bongkar_muatan WHERE DATE(tanggal) = CURDATE()");
$bongkar_hari_ini = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(jumlah_liter) as total_liter FROM bongkar_muatan WHERE DATE(tanggal) = CURDATE()");
$total_liter = $stmt->fetch()['total_liter'] ?? 0;
$drom_hari_ini = $total_liter > 0 ? $total_liter / 200 : 0;

$stmt = $pdo->query("SELECT COUNT(DISTINCT kecamatan) as total FROM pangkalan WHERE kecamatan IS NOT NULL");
$total_kecamatan = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛢️ <?= htmlspecialchars($settings['nama_agen'] ?? 'Agen Minyak Tanah') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-section { background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; padding: 4rem 0; }
        .stat-card { transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn { border-radius: 50px; padding: 12px 24px; }
        .section-title { position: relative; padding-bottom: 1rem; }
        .section-title:after { 
            content: ''; 
            position: absolute; 
            bottom: 0; 
            left: 0; 
            width: 60px; 
            height: 4px; 
            background: #0d6efd; 
            border-radius: 2px; 
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<div class="hero-section text-center">
    <?php if (!empty($settings['logo_path'])): ?>
        <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="Logo" height="80" class="mb-4">
    <?php endif; ?>
    <h1 class="display-5 fw-bold">🛢️ <?= htmlspecialchars($settings['nama_agen'] ?? 'Agen Minyak Tanah Resmi') ?></h1>
    <p class="lead"><?= htmlspecialchars($settings['slogan'] ?? 'Melayani Pangkalan dengan Profesional & Transparan') ?></p>
</div>

<!-- Pengumuman -->
<?php if ($pengumuman): ?>
<div class="bg-warning bg-gradient text-dark py-3 text-center fw-bold">
    <i class="fas fa-bullhorn me-2"></i> <?= htmlspecialchars($pengumuman['isi']) ?>
</div>
<?php endif; ?>

<!-- Statistik -->
<div class="container my-5">
    <h2 class="text-center section-title">📊 Statistik Hari Ini</h2>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-light">
                <h3 class="display-6"><?= number_format($total_pangkalan) ?></h3>
                <p class="text-muted mb-0">Pangkalan Terdaftar</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-light">
                <h3 class="display-6"><?= number_format($bongkar_hari_ini) ?></h3>
                <p class="text-muted mb-0">Bongkar Hari Ini</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-light">
                <h3 class="display-6"><?= number_format($drom_hari_ini, 1) ?></h3>
                <p class="text-muted mb-0">Drom Terdistribusi</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-light">
                <h3 class="display-6"><?= number_format($total_kecamatan) ?></h3>
                <p class="text-muted mb-0">Kecamatan Terlayani</p>
            </div>
        </div>
    </div>
</div>

<!-- Layanan Publik -->
<div class="container my-5">
    <h2 class="text-center section-title">📍 Layanan Publik</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="display-4 mb-3"><i class="fas fa-map-marked-alt text-primary"></i></div>
                    <h5>Peta Lokasi Pangkalan</h5>
                    <p class="text-muted">Temukan pangkalan terdekat di wilayah Anda</p>
                    <a href="peta-pangkalan.php" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-map me-2"></i> Lihat Peta
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="display-4 mb-3"><i class="fas fa-headset text-success"></i></div>
                    <h5>Hubungi Kami</h5>
                    <p class="text-muted">Butuh bantuan? Hubungi call center kami</p>
                    <p class="h5"><i class="fas fa-phone me-2"></i> <?= htmlspecialchars($settings['telepon_pusat'] ?? '0812-XXXX-XXXX') ?></p>
                    <p class="text-muted small">Jam operasional: 07.00 - 17.00 WIB</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Login Section -->
<div class="bg-light py-5">
    <div class="container text-center">
        <h2 class="section-title">👤 Area Login</h2>
        <p class="text-muted">Silakan login sesuai peran Anda</p>
        <div class="row justify-content-center g-4 mt-4">
            <div class="col-md-4">
                <a href="login.php?role=admin" class="btn btn-success w-100 py-3">
                    <i class="fas fa-user-shield me-2"></i> Login Admin
                </a>
            </div>
            <div class="col-md-4">
                <a href="login.php?role=amt" class="btn btn-info w-100 py-3 text-white">
                    <i class="fas fa-truck me-2"></i> Login AMT
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <p class="mb-0">
        © <?= date('Y') ?> <?= htmlspecialchars($settings['nama_agen'] ?? 'Agen Minyak Tanah') ?> — 
        Dibangun untuk Ketahanan Energi Rakyat
    </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
