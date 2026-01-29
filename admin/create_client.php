<?php
// 1. Hubungkan ke database
include '../db.php'; 

// Fungsi untuk membuat Client ID (Slug)
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text . '-' . bin2hex(random_bytes(3)); // Tambah suffix acak
}

// Fungsi untuk generate Secret 24 karakter (Huruf Besar, Kecil, Angka)
function generateSecret($length = 24) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $secret;
}

$message = "";

// 2. Proses Form saat tombol Simpan ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitasi Input
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $redirect_uri = mysqli_real_escape_string($conn, $_POST['redirect_uri']);
    $client_job = mysqli_real_escape_string($conn, $_POST['client_job']);
    
    $client_id = createSlug($client_name);
    $client_secret = generateSecret();

    // Gunakan Prepared Statement untuk keamanan
    $stmt = $conn->prepare("INSERT INTO clients (client_id, client_secret, client_name, redirect_uri, client_job) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $client_id, $client_secret, $client_name, $redirect_uri, $client_job);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Klien berhasil didaftarkan!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Gagal: " . $conn->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Klien OIDC Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Registrasi Aplikasi Klien Baru</h4>
                </div>
                <div class="card-body">
                    <?= $message; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nama Aplikasi</label>
                            <input type="text" name="client_name" class="form-control" placeholder="Contoh: Aplikasi Keuangan" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Redirect URI (Callback URL)</label>
                            <input type="url" name="redirect_uri" class="form-control" placeholder="http://localhost/client-app/callback.php" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Pekerjaan (Scope)</label>
                            <select name="client_job" class="form-select" required>
                                <option value="">-- Pilih Bidang Pekerjaan --</option>
                                <option value="Accounting">Manajemen Keuangan & Akuntansi</option>
                                <option value="HRD">Manajemen SDM (Human Resources)</option>
                                <option value="Inventory">Manajemen Stok & Gudang</option>
                                <option value="Sales">Manajemen Penjualan</option>
                            </select>
                            <div class="form-text">User akan melihat tugas sesuai bidang ini setelah login.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Simpan & Generate Kredensial</button>
                            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>