<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Simple OIDC System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container text-center mt-5">
        <h1 class="mb-4">Sistem Login OIDC Native</h1>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm mb-3">
                    <h3>Admin Provider</h3>
                    <p>Login untuk mengelola Client ID & Secret.</p>
                    <a href="admin/login.php" class="btn btn-primary w-100">Login Admin</a>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card p-4 shadow-sm mb-3">
                    <h3>Client App</h3>
                    <p>Login menggunakan Client ID & Secret.</p>
                    <a href="client/login.php" class="btn btn-dark w-100">Login Client</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>