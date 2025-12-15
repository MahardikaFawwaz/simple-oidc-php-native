<?php
session_name("OIDC_CLIENT_SESSION"); // <-- Nama beda untuk klien
session_start();
session_start();
session_destroy();
header("Location: login.php");