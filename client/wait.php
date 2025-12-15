<?php
session_name("OIDC_CLIENT_SESSION"); // <-- Nama beda untuk klien
session_start();
if (!isset($_SESSION['pending_request_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Menunggu Persetujuan Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loader { border: 5px solid #f3f3f3; border-top: 5px solid #3498db; border-radius: 50%; width: 50px; height: 50px; animation: spin 2s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100 text-white">
    <div class="text-center">
        <h1>Menunggu Persetujuan Admin...</h1>
        <p class="lead">Klien: <strong><?= $_SESSION['pending_client_name'] ?></strong></p>
        <div class="loader"></div>
        <p>Mohon jangan tutup halaman ini. Admin sedang meninjau permintaan Anda.</p>
        <a href="login.php" class="btn btn-outline-light btn-sm mt-3">Batalkan</a>
    </div>

    <script>
        // Cek status setiap 2 detik
        setInterval(function() {
            fetch('check_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'approved') {
                    window.location.href = 'dashboard.php'; // Masuk jika disetujui
                } else if (data.status === 'rejected') {
                    alert('Permintaan Login Ditolak oleh Admin!');
                    window.location.href = 'login.php';
                }
            });
        }, 2000);
    </script>
</body>
</html>