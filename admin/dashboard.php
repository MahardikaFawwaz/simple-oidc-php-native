<?php

session_name("OIDC_ADMIN_SESSION"); // <-- Tambahkan baris ini
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Logic Buat Client Baru
if (isset($_POST['create_client'])) {
    $name = $_POST['client_name'];
    $client_id = generateRandomString(16);
    $client_secret = generateRandomString(32);
    $stmt = $conn->prepare("INSERT INTO clients (client_name, client_id, client_secret) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $client_id, $client_secret);
    $stmt->execute();
    $msg = "<div class='alert alert-success'>Client berhasil dibuat!</div>";
}

// 2. Logic Approve/Reject Login
if (isset($_POST['action_request'])) {
    $req_id = $_POST['request_id'];
    $action = $_POST['action_type']; // 'approved' atau 'rejected'
    
    $stmt = $conn->prepare("UPDATE login_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $req_id);
    $stmt->execute();
}

// Ambil List Client
$clients = $conn->query("SELECT * FROM clients ORDER BY id DESC");

// Ambil Permintaan Login yang PENDING (Join dengan tabel clients biar tahu namanya)
$requests = $conn->query("
    SELECT r.id, r.created_at, c.client_name, c.client_id 
    FROM login_requests r 
    JOIN clients c ON r.client_id = c.client_id 
    WHERE r.status = 'pending' 
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="10"> </head>
<body>
    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand mb-0 h1">OIDC Provider Admin</span>
        <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </nav>

    <div class="container mt-4">
        <?php if(isset($msg)) echo $msg; ?>
        
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between">
                <span>⚠️ Permintaan Login Masuk (Pending)</span>
                <small>Halaman refresh tiap 10 detik</small>
            </div>
            <div class="card-body">
                <?php if ($requests->num_rows > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nama Aplikasi</th>
                                <th>Client ID</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($req = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?= $req['created_at'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($req['client_name']) ?></td>
                                <td><small><?= $req['client_id'] ?></small></td>
                                <td>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <button type="submit" name="action_request" value="1" onclick="this.form.action_type.value='approved'" class="btn btn-success btn-sm">✅ Izinkan</button>
                                        <button type="submit" name="action_request" value="1" onclick="this.form.action_type.value='rejected'" class="btn btn-danger btn-sm">❌ Tolak</button>
                                        <input type="hidden" name="action_type" value="">
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center m-0">Tidak ada permintaan login yang menunggu.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">Buat Client Baru</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Nama Aplikasi</label>
                                <input type="text" name="client_name" class="form-control" required>
                            </div>
                            <button type="submit" name="create_client" class="btn btn-primary w-100">Generate</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">Daftar Klien Terdaftar</div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>ID</th>
                                    <th>Secret</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $clients->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><small><?= $row['client_id'] ?></small></td>
                                    <td><small class="text-muted">***</small></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>