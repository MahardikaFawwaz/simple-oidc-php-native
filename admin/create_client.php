    <?php
// 1. Hubungkan ke database
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['client_name'])) {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);

    // --- LOGIKA BARU UNTUK CLIENT ID ---
    // Mengubah "Nama Aplikasi" menjadi "nama-aplikasi-xxxxx"
    $clean_name = strtolower(str_replace(' ', '-', $client_name));
    $random_id = substr(md5(uniqid()), 0, 5); // Tambahan 5 karakter unik
    $client_id = $clean_name . '-' . $random_id;

    // --- LOGIKA BARU UNTUK CLIENT SECRET ---
    function generateStrongSecret($length = 20) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $secret = '';
        // Pastikan minimal ada 1 kapital dan 1 angka
        $secret .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[rand(0, 25)];
        $secret .= "0123456789"[rand(0, 9)];
        
        for ($i = 0; $i < $length - 2; $i++) {
            $secret .= $chars[rand(0, strlen($chars) - 1)];
        }
        return str_shuffle($secret); // Acak urutan agar posisi kapital/angka tidak selalu di depan
    }
    
    $client_secret = generateStrongSecret();

    // 2. Simpan ke Database
    // Sesuaikan nama tabel 'clients' dan kolomnya dengan database Anda
    $query = "INSERT INTO clients (client_name, client_id, client_secret) VALUES ('$client_name', '$client_id', '$client_secret')";

    if (mysqli_query($conn, $query)) {
        // Jika berhasil, balikkan ke dashboard
        header("Location: dashboard.php?success=1");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    // Jika diakses tanpa POST, lempar balik ke dashboard
    header("Location: dashboard.php");
}
?>