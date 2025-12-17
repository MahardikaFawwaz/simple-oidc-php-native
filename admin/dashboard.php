<?php
session_name("OIDC_ADMIN_SESSION");
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- 1. LOGIC BUAT CLIENT BARU (DIPERBAIKI) ---
if (isset($_POST['create_client'])) {
    $name = mysqli_real_escape_string($conn, $_POST['client_name']);
    
    // Client ID dari nama (kecil, spasi jadi strip) + suffix unik
    $clean_name = strtolower(str_replace(' ', '-', $name));
    $random_suffix = substr(md5(uniqid()), 0, 5); 
    $client_id = $clean_name . '-' . $random_suffix;

    // Client Secret Kuat (Kapital, Angka, Huruf)
    function generateStrongSecret($length = 24) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $secret = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[rand(0, 25)] . "0123456789"[rand(0, 9)];
        for ($i = 0; $i < $length - 2; $i++) {
            $secret .= $chars[rand(0, strlen($chars) - 1)];
        }
        return str_shuffle($secret);
    }
    $client_secret = generateStrongSecret();

    $stmt = $conn->prepare("INSERT INTO clients (client_name, client_id, client_secret) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $client_id, $client_secret);
    
    if ($stmt->execute()) {
        // Redirect setelah POST untuk mencegah duplikasi saat refresh
        header("Location: dashboard.php?msg=created");
        exit;
    }
}

// --- 2. LOGIC HAPUS CLIENT ---
if (isset($_POST['delete_client'])) {
    $id_to_delete = $_POST['client_id_delete'];
    $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
    $stmt->bind_param("s", $id_to_delete);
    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=deleted");
        exit;
    }
}

// --- 3. LOGIC APPROVE/REJECT LOGIN ---
if (isset($_POST['action_request'])) {
    $req_id = $_POST['request_id'];
    $action = $_POST['action_type']; 
    $stmt = $conn->prepare("UPDATE login_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $req_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

// Ambil Data untuk Tampilan
$clients = $conn->query("SELECT * FROM clients ORDER BY id DESC");
$requests = $conn->query("SELECT r.id, r.created_at, c.client_name, c.client_id FROM login_requests r JOIN clients c ON r.client_id = c.client_id WHERE r.status = 'pending' ORDER BY r.created_at DESC");

// Pesan Notifikasi
$alert = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'created') $alert = "<div class='alert alert-success'>Client baru berhasil dibuat!</div>";
    if($_GET['msg'] == 'deleted') $alert = "<div class='alert alert-danger'>Client telah dihapus!</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>OIDC Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="15"> 
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4 shadow">
        <span class="navbar-brand mb-0 h1">OIDC Provider Admin</span>
        <a href="logout.php" class="btn btn-light btn-sm fw-bold">Logout</a>
    </nav>

    <div class="container mt-4">
        <?= $alert ?>

        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between">
                <span>⚠️ Permintaan Login Masuk (Pending)</span>
                <small>Auto-refresh 15 detik</small>
            </div>
            <div class="card-body">
                <?php if ($requests->num_rows > 0): ?>
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>Waktu</th><th>Nama Aplikasi</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php while($req = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?= $req['created_at'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($req['client_name']) ?></td>
                                <td>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="action_type" id="act_<?= $req['id'] ?>" value="">
                                        <button type="submit" name="action_request" onclick="document.getElementById('act_<?= $req['id'] ?>').value='approved'" class="btn btn-success btn-sm">✅ Izinkan</button>
                                        <button type="submit" name="action_request" onclick="document.getElementById('act_<?= $req['id'] ?>').value='rejected'" class="btn btn-danger btn-sm">❌ Tolak</button>
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
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Buat Client Baru</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small">Nama Aplikasi</label>
                                <input type="text" name="client_name" class="form-control" placeholder="Contoh: App Keuangan" required>
                            </div>
                            <button type="submit" name="create_client" class="btn btn-primary w-100">Generate Client</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Daftar Klien Terdaftar</div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr><th>Nama</th><th>Client ID</th><th>Client Secret</th></tr>
                            </thead>
                            <tbody>
                                <?php while($row = $clients->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-light" value="<?= $row['client_id'] ?>" readonly id="id_<?= $row['id'] ?>">
                                            <button class="btn btn-outline-primary" onclick="copyText('id_<?= $row['id'] ?>')">Salin</button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <div class="input-group input-group-sm">
                                                <input type="password" class="form-control" value="<?= $row['client_secret'] ?>" readonly id="sec_<?= $row['id'] ?>">
                                                <button class="btn btn-outline-secondary" onclick="toggleView('sec_<?= $row['id'] ?>')">Lihat</button>
                                                <button class="btn btn-outline-primary" onclick="copyText('sec_<?= $row['id'] ?>')">Salin</button>
                                            </div>
                                            <form method="POST" onsubmit="return confirm('Hapus klien <?= htmlspecialchars($row['client_name']) ?>?')">
                                                <input type="hidden" name="client_id_delete" value="<?= $row['client_id'] ?>">
                                                <button type="submit" name="delete_client" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyText(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.type = 'text'; // Sementara ubah type agar bisa di-copy jika itu password
        copyText.select();
        document.execCommand("copy");
        if(elementId.includes('sec')) copyText.type = 'password'; // Kembalikan jika itu secret
        alert("Teks berhasil disalin ke clipboard!");
    }

    function toggleView(elementId) {
        var x = document.getElementById(elementId);
        x.type = (x.type === "password") ? "text" : "password";
    }
    </script>
</body>
</html>