# Simple OIDC PHP Native

Sistem OpenID Connect (OIDC) sederhana menggunakan PHP Native.

## Fitur
- Dashboard Admin untuk manajemen klien.
- Pembuatan Client ID otomatis berdasarkan nama aplikasi.
- Client Secret dengan kombinasi keamanan tinggi.
- Fitur Approve/Reject permintaan login.
- Kemudahan bagi admin untuk mengelola user

## Cara Instalasi
1. Clone repositori ini.
2. Impor file database `oidc_native_db.sql` ke MySQL.
3. Buat file `db.php` secara manual (lihat contoh di bawah).
4. Sesuaikan konfigurasi database.

### Contoh isi db.php:
```php
<?php
$conn = mysqli_connect("localhost", "root", "", "oidc_db");

