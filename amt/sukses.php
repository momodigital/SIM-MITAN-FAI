<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['amt1', 'amt2'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil pesan dari session
$message = $_SESSION['toast']['message'] ?? 'Data berhasil disimpan!';
$type = $_SESSION['toast']['type'] ?? 'success';

// Hapus toast dari session
unset($_SESSION['toast']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ Sukses! - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { 
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }
        .btn-home {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border: none;
            padding: 12px 24px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Sukses!</h2>
        <p class="lead"><?= htmlspecialchars($message) ?></p>
        
        <div class="mt-4">
            <a href="input_bongkar.php" class="btn btn-home text-white">
                <i class="fas fa-plus-circle me-2"></i> Input Bongkar Lagi
            </a>
        </div>
        
        <div class="mt-3">
            <a href="../logout.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <!-- Auto redirect after 10 seconds -->
    <script>
        setTimeout(function() {
            window.location.href = "input_bongkar.php";
        }, 10000);
    </script>
</body>
</html>
