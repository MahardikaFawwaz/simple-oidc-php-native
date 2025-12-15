<?php
session_name("OIDC_CLIENT_SESSION");
session_start();
if (!isset($_SESSION['client_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand">Client App: <strong><?= $_SESSION['client_name'] ?></strong></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </nav>

    <div class="container mt-5">
        <div class="alert alert-success">
            <h4>Login Berhasil!</h4>
            <p>Anda telah berhasil melakukan autentikasi menggunakan kredensial yang diberikan admin.</p>
        </div>

        <div class="card mt-4">
            <div class="card-header">Session Info</div>
            <div class="card-body">
                <h5 class="card-title">Access Token (Simulasi)</h5>
                <div class="bg-light p-3 border rounded font-monospace text-break">
                    <?= $_SESSION['access_token'] ?>
                </div>
                <p class="mt-3 text-muted">
                    Dalam implementasi OIDC nyata, token ini (JWT) akan digunakan untuk mengakses API resource.
                </p>
            </div>
        </div>
    </div>
</body>
</html>