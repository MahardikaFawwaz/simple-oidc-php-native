<?php
session_name("OIDC_CLIENT_SESSION");
session_start();
require_once '../db.php';

if (!isset($_SESSION['client_logged_in'])) {
    header("Location: login.php"); exit;
}

$client_id = $_SESSION['logged_client_id'];
$stmt = $conn->prepare("SELECT client_name, client_job, is_approved FROM clients WHERE client_id = ?");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$clientData = $stmt->get_result()->fetch_assoc();

if (!$clientData || $clientData['is_approved'] != 1) {
    session_destroy();
    header("Location: login.php?status=rejected"); exit;
}

$job_category = $clientData['client_job'];
$theme_color = ($job_category === 'Accounting') ? 'primary' : 'success';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard <?= $job_category ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .card-custom:hover { transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .btn-work { border-radius: 10px; font-weight: 600; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4 shadow-sm mb-4">
    <span class="navbar-brand fw-bold mb-0"><i class="bi bi-shop"></i> Panel <?= $job_category ?></span>
    <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Keluar</a>
</nav>

<div class="container">
    <div class="alert alert-white bg-white border-start border-<?= $theme_color ?> border-4 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-person-circle fs-4 me-3 text-<?= $theme_color ?>"></i>
            <div>
                <h6 class="mb-0 fw-bold">Selamat Datang, <?= htmlspecialchars($clientData['client_name']) ?>!</h6>
                <small class="text-muted">Status Akun: <span class="badge bg-<?= $theme_color ?>-light text-<?= $theme_color ?> small">Verified UMKM</span></small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <h5 class="fw-bold mb-3"><i class="bi bi-list-task"></i> Daftar Tugas UMKM</h5>
            
            <div class="card card-custom mb-3 p-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 me-3 text-success"><i class="bi bi-box-seam fs-4"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Update Stok Barang Toko</h6>
                            <small class="text-muted">Batas waktu: Segera</small>
                        </div>
                    </div>
                    <button class="btn btn-success btn-work px-4" data-bs-toggle="modal" data-bs-target="#modalStok">Kerjakan</button>
                </div>
            </div>

            <div class="card card-custom mb-3 p-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 me-3 text-info"><i class="bi bi-graph-up-arrow fs-4"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Cetak Laporan Laba Rugi</h6>
                            <small class="text-muted">Batas waktu: Minggu ini</small>
                        </div>
                    </div>
                    <button class="btn btn-info text-white btn-work px-4" data-bs-toggle="modal" data-bs-target="#modalLaba">Buka Laporan</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom p-4 bg-white">
                <h6 class="fw-bold text-muted mb-3 small uppercase text-center">OIDC Authentication Token</h6>
                <div class="p-3 bg-light border rounded small font-monospace text-break mb-3" style="border-left: 5px solid #198754 !important;">
                    eyJhToken_UMKM_<?= bin2hex(random_bytes(12)) ?>
                </div>
                <p class="small text-muted mb-0">Token di atas adalah bukti autentikasi sah Anda untuk mengakses resource UMKM di sistem ini.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalStok" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Update Inventaris Toko</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Barang</label>
                    <input type="text" class="form-control bg-light" value="Produk Kerajinan Lokal" readonly>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Stok Saat Ini</label>
                        <input type="text" class="form-control" value="24" readonly>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Tambah Stok</label>
                        <input type="number" class="form-control border-success" placeholder="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4" onclick="alert('Stok Berhasil Diupdate!')">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLaba" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">Simulasi Laporan Laba Rugi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Deskripsi</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Total Pendapatan Januari 2026</td><td class="text-end fw-bold">Rp 15.000.000</td></tr>
                            <tr><td>Beban Operasional</td><td class="text-end text-danger">- Rp 4.500.000</td></tr>
                            <tr class="table-success fw-bold">
                                <td>Laba Bersih Estimasi</td>
                                <td class="text-end">Rp 10.500.000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted mt-2 italic">*Data ini dihasilkan secara otomatis berdasarkan simulasi login OIDC Anda.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>