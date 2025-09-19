<?php
// HALAMAN PUBLIK - TIDAK BUTUH LOGIN
include 'config.php';

// Ambil semua pangkalan yang punya koordinat
$stmt = $pdo->query("
    SELECT 
        nama_pangkalan,
        nama_pemilik,
        kelurahan,
        kecamatan,
        telepon,
        latitude,
        longitude
    FROM pangkalan 
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
");
$pangkalan_list = $stmt->fetchAll();

// Hitung jumlah pangkalan
$total_pangkalan = count($pangkalan_list);

// Tentukan center map (default Jakarta jika tidak ada data)
$center_lat = -6.2088;
$center_lng = 106.8456;

// Jika ada data, gunakan rata-rata koordinat
if ($total_pangkalan > 0) {
    $sum_lat = 0;
    $sum_lng = 0;
    foreach ($pangkalan_list as $p) {
        $sum_lat += $p['latitude'];
        $sum_lng += $p['longitude'];
    }
    $center_lat = $sum_lat / $total_pangkalan;
    $center_lng = $sum_lng / $total_pangkalan;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📍 Peta Lokasi Semua Pangkalan Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        #map { 
            height: 80vh; 
            width: 100%; 
            z-index: 0;
        }
        .info-popup { 
            font-size: 14px; 
            max-width: 300px;
        }
        .info-popup h5 { 
            margin: 5px 0; 
            color: #0d6efd;
        }
        .info-popup p { 
            margin: 3px 0; 
            font-size: 13px;
        }
        .header-section {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 2rem 0;
            margin-bottom: 1rem;
        }
        .legend {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 1rem;
            z-index: 1000;
            position: relative;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section text-center">
        <div class="container">
            <h1><i class="fas fa-map-marked-alt me-2"></i> Peta Lokasi Semua Pangkalan Minyak Tanah</h1>
            <p class="lead mb-0">Total: <?= number_format($total_pangkalan) ?> pangkalan terdaftar</p>
            <p class="mb-0">Dikelola oleh [Nama Agen Anda] — Data terupdate <?= date('d M Y') ?></p>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <h5><i class="fas fa-map me-2"></i> Legenda</h5>
        <div class="legend-item">
            <div class="legend-color" style="background: #0d6efd;"></div>
            <span>Pangkalan Minyak Tanah</span>
        </div>
        <small class="text-muted">Klik marker untuk melihat detail pangkalan</small>
    </div>

    <!-- Map Container -->
    <div id="map"></div>

    <!-- Footer -->
    <div class="bg-dark text-white text-center py-3">
        <p class="mb-0">
            © <?= date('Y') ?> [Nama Agen Minyak Tanah] — 
            Dibangun untuk Ketahanan Energi Rakyat
        </p>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Inisialisasi peta
        const map = L.map('map').setView([<?= $center_lat ?>, <?= $center_lng ?>], 12);

        // Tile Layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Tambahkan marker untuk setiap pangkalan
        <?php foreach ($pangkalan_list as $p): ?>
            L.marker([<?= $p['latitude'] ?>, <?= $p['longitude'] ?>], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background: #0d6efd; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">⛽</div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            })
            .addTo(map)
            .bindPopup(`
                <div class="info-popup">
                    <h5>⛽ <?= '<?= addslashes($p['nama_pangkalan']) ?>' ?></h5>
                    <p><strong>Pemilik:</strong> <?= addslashes($p['nama_pemilik']) ?></p>
                    <p><strong>Lokasi:</strong> <?= addslashes($p['kelurahan']) ?>, <?= addslashes($p['kecamatan']) ?></p>
                    <?php if (!empty($p['telepon'])): ?>
                        <p><strong>Telepon:</strong> <a href="tel:<?= $p['telepon'] ?>" style="color: #0d6efd; text-decoration: none;"><?= $p['telepon'] ?></a></p>
                    <?php endif; ?>
                    <div style="margin-top: 10px;">
                        <a href="https://www.google.com/maps?q=<?= $p['latitude'] ?>,<?= $p['longitude'] ?>" target="_blank" style="display: inline-block; background: #0d6efd; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 12px;">
                            <i class="fas fa-directions me-1"></i> Navigasi
                        </a>
                    </div>
                </div>
            `);
        <?php endforeach; ?>

        // Fit bounds jika ada lebih dari 1 marker
        <?php if ($total_pangkalan > 1): ?>
            const bounds = [];
            <?php foreach ($pangkalan_list as $p): ?>
                bounds.push([<?= $p['latitude'] ?>, <?= $p['longitude'] ?>]);
            <?php endforeach; ?>
            map.fitBounds(bounds, { padding: [50, 50] });
        <?php endif; ?>

        // Tambahkan kontrol zoom
        L.control.zoom({ position: 'topright' }).addTo(map);

        // Tambahkan scale
        L.control.scale().addTo(map);

        // Responsif untuk mobile
        if (window.innerWidth <= 768) {
            map.invalidateSize();
        }

        // Handle resize
        window.addEventListener('resize', function() {
            map.invalidateSize();
        });
    </script>
</body>
</html>
