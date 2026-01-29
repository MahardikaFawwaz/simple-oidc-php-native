<?php
session_name("OIDC_ADMIN_SESSION");
session_start();
require_once '../db.php';

// 1. Proteksi Admin Login & Perbaikan Error Session
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// 2. Logika Approve, Reject (Disable), & Delete
if (isset($_GET['approve_id'])) {
    $stmt = $conn->prepare("UPDATE clients SET is_approved = 1 WHERE client_id = ?");
    $stmt->bind_param("s", $_GET['approve_id']);
    $stmt->execute();
    header("Location: dashboard.php"); exit;
}

if (isset($_GET['reject_id'])) {
    // Status 2 = Rejected / Disabled
    $stmt = $conn->prepare("UPDATE clients SET is_approved = 2 WHERE client_id = ?");
    $stmt->bind_param("s", $_GET['reject_id']);
    $stmt->execute();
    header("Location: dashboard.php"); exit;
}

if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
    $stmt->bind_param("s", $_GET['delete_id']);
    $stmt->execute();
    header("Location: dashboard.php"); exit;
}

// 3. Ambil Data Klien & Log Aktivitas
$clients = $conn->query("SELECT * FROM clients ORDER BY id DESC");
$logs = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>OIDC Provider Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .navbar { background: linear-gradient(90deg, #0d6efd 0%, #0043a8 100%); }
        
        /* Container Riwayat Aktivitas dengan Scroll */
        .log-container {
            max-height: 250px;
            overflow-y: auto;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        .table-log thead {
            position: sticky;
            top: 0;
            background-color: #ffffff;
            z-index: 10;
            border-bottom: 2px solid #dee2e6;
        }
        .badge-success-light { background-color: #d1e7dd; color: #0f5132; }
        .badge-danger-light { background-color: #f8d7da; color: #842029; }
        
        /* Perbaikan lebar input secret */
        .secret-input { font-family: monospace; background-color: #f8f9fa !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3 shadow-sm mb-4">
    <span class="navbar-brand fw-bold mb-0"><i class="bi bi-shield-check"></i> OIDC Provider Admin</span>
    <div class="d-flex align-items-center">
        <span class="text-white me-3 small">Admin: <strong><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Administrator') ?></strong></span>
        <a href="logout.php" class="btn btn-light btn-sm fw-bold px-3 rounded-pill">Logout</a>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill text-primary"></i> Daftar Klien Terdaftar</h5>
                    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>NAMA & BIDANG</th>
                                <th style="width: 45%;">CLIENT CREDENTIALS</th>
                                <th>STATUS</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $clients->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['client_name']) ?></div>
                                    <span class="badge bg-secondary opacity-75 small" style="font-size: 10px;"><?= $row['client_job'] ?></span>
                                </td>
                                <td>
                                    <div class="mb-1 small">ID: <code class="text-danger fw-bold"><?= $row['client_id'] ?></code></div>
                                    <div class="input-group input-group-sm">
                                        <input type="password" id="secret-<?= $row['id'] ?>" class="form-control secret-input" value="<?= htmlspecialchars($row['client_secret']) ?>" readonly>
                                        <button class="btn btn-light border" onclick="toggleSecret(<?= $row['id'] ?>)">
                                            <i id="icon-<?= $row['id'] ?>" class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-primary" onclick="copySecret(<?= $row['id'] ?>)">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['is_approved'] == 1): ?>
                                        <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-circle"></i> Approved</span>
                                    <?php elseif($row['is_approved'] == 2): ?>
                                        <span class="badge bg-danger rounded-pill px-3"><i class="bi bi-x-circle"></i> Disabled</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3"><i class="bi bi-hourglass-split"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm rounded">
                                        <?php if($row['is_approved'] != 1): ?>
                                            <a href="?approve_id=<?= $row['client_id'] ?>" class="btn btn-sm btn-success">Approve</a>
                                        <?php else: ?>
                                            <a href="?reject_id=<?= $row['client_id'] ?>" class="btn btn-sm btn-outline-danger">Disable</a>
                                        <?php endif; ?>
                                        <a href="?delete_id=<?= $row['client_id'] ?>" class="btn btn-sm btn-light border" onclick="return confirm('Hapus klien ini secara permanen?')">
                                            <i class="bi bi-trash text-danger"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-primary"></i> Riwayat Aktivitas Sistem</h5>
                    <span class="badge bg-light text-dark border fw-normal">20 Data Terakhir</span>
                </div>
                <div class="log-container">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr class="small fw-bold">
                                <th class="ps-3">WAKTU</th>
                                <th>CLIENT ID</th>
                                <th>AKTIVITAS</th>
                                <th>STATUS</th>
                                <th>IP ADDRESS</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php while($l = $logs->fetch_assoc()): 
                                $status_class = ($l['status'] == 'Success') ? 'badge-success-light' : 'badge-danger-light';
                            ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= date('d M, H:i:s', strtotime($l['created_at'])) ?></td>
                                <td><code class="text-primary"><?= $l['client_id'] ?></code></td>
                                <td><?= htmlspecialchars($l['activity']) ?></td>
                                <td><span class="badge <?= $status_class ?> px-2"><?= $l['status'] ?></span></td>
                                <td class="text-muted"><?= $l['ip_address'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($logs->num_rows == 0): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted small">Belum ada aktivitas yang dicatat.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 sticky-top" style="top: 20px;">
                <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-plus-circle-fill text-primary"></i> Buat Client Baru</h5>
                <form action="create_client.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Aplikasi</label>
                        <input type="text" name="client_name" class="form-control py-2 shadow-sm" placeholder="Contoh: App UMKM Kita" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Jenis Pekerjaan (Scope)</label>
                        <select name="client_job" class="form-select py-2 shadow-sm" required>
                            <option value="Accounting">Accounting (Keuangan)</option>
                            <option value="UMKM">UMKM (Pemilik Usaha)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow">
                        <i class="bi bi-key-fill"></i> Generate Client Credentials
                    </button>
                </form>
                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <h6 class="small fw-bold mb-2 text-muted uppercase">Info OIDC</h6>
                    <p class="small text-muted mb-0">Client ID dan Secret digunakan oleh aplikasi klien untuk meminta akses token dari provider ini.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Fungsi Show/Hide Client Secret
function toggleSecret(id) {
    const input = document.getElementById('secret-' + id);
    const icon = document.getElementById('icon-' + id);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Fungsi Salin ke Clipboard
function copySecret(id) {
    const input = document.getElementById('secret-' + id);
    const originalType = input.type;
    input.type = "text";
    input.select();
    document.execCommand("copy");
    input.type = originalType;
    alert("Client Secret berhasil disalin ke clipboard!");
}
</script>

</body>
</html>