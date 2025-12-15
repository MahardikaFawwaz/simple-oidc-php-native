<?php
session_name("OIDC_ADMIN_SESSION"); // <-- Tambahkan baris ini
session_start();
session_destroy();
header("Location: login.php");