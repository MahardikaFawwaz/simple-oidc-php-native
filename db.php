<?php
$host = 'localhost';
$db   = 'oidc_native_db';
$user = 'root';
$pass = ''; // Default password Laragon biasanya kosong

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi helper untuk generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
?>