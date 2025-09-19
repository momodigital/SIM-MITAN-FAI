<?php
include 'config.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'operator';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

        // Redirect berdasarkan role
        if ($user['role'] === 'amt1' || $user['role'] === 'amt2') {
            header("Location: amt/input_bongkar.php");
        } else {
            header("Location: admin/dashboard/index.php");
        }
        exit;
    } else {
        $error = "Username, password, atau role salah!";
    }
}

// Ambil role dari URL (jika ada)
$selected_role = $_GET['role'] ?? 'admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Login - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; }
        .login-container { max-width: 450px; margin: auto; padding-top: 5rem; }
        .login-card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .role-btn { border-radius: 50px; margin: 5px; transition: all 0.3s; }
        .role-btn.active { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h3 class="card-title"><i class="fas fa-lock me-2"></i> Login Agen Minyak Tanah</h3>
                    <p class="text-muted">Silakan masuk dengan akun Anda</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="fas fa-user-tag me-2"></i> Pilih Peran Anda</label>
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary role-btn <?= $selected_role == 'admin' ? 'active' : '' ?>" 
                                    onclick="document.getElementById('role').value='admin'; updateActive(this)">
                                <i class="fas fa-user-shield me-1"></i> Admin
                            </button>
                            <button type="button" class="btn btn-outline-info role-btn <?= $selected_role == 'amt' ? 'active' : '' ?>" 
                                    onclick="document.getElementById('role').value='amt1'; updateActive(this)">
                                <i class="fas fa-truck me-1"></i> AMT
                            </button>
                        </div>
                        <input type="hidden" name="role" id="role" value="<?= $selected_role == 'amt' ? 'amt1' : 'admin' ?>">
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user me-2"></i> Username</label>
                        <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-key me-2"></i> Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Masukkan password" required>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sign-in-alt me-2"></i> MASUK
                    </button>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">© <?= date('Y') ?> Agen Minyak Tanah</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateActive(btn) {
            document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
