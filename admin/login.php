<?php
// Gunakan nama session khusus admin agar tidak bentrok dengan session klien
session_name("OIDC_ADMIN_SESSION"); 
session_start();
require_once '../db.php';

// Jika admin sudah login, langsung arahkan ke dashboard admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. SQL Injection Prevention menggunakan Prepared Statements
    // Mengambil data dari tabel 'admins'
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // 2. Verifikasi password (asumsi password di database sudah di-hash)
        if (password_verify($password, $row['password'])) {
            // Set session data
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            
            // Redirect ke dashboard admin
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Simple OIDC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .login-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-primary { border-radius: 8px; padding: 10px; font-weight: 600; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold mt-2">OIDC Admin</h3>
                    <p class="text-muted small">Silakan masuk untuk mengelola klien</p>
                </div>

                <div class="card login-card p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger d-flex align-items-center small py-2" role="alert">
                            <i class="bi bi-excl-triangle-fill me-2"></i>
                            <div><?= $error ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Masuk ke Panel</button>
                    </form>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../index.php" class="text-decoration-none small text-muted">
                        <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>