<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $database = new Database();
        $pdo = $database->connect();

        $stmt = $pdo->prepare("SELECT id, username, name, password FROM administrator WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'] ?? $admin['username'];
            header('Location: admin/dashboard.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRASM | Login</title>
    <link href="assets/vendor/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/fontawesome-free-7.3.1/css/all.min.css">
    <style>
        body {
            background: #eef1f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card-login {
            max-width: 320px;
            width: 100%;
        }
        .card-login .card-body {
            padding: 1.25rem 1.25rem 1.5rem;
        }
        .btn-login {
            background: #002d62;
            color: #fff;
            padding: 0.3rem 0.5rem;
            font-size: 0.85rem;
        }
        .btn-login:hover {
            background: #001f45;
            color: #fff;
        }
        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.15rem;
        }
        .form-control {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }
        .mb-2 {
            margin-bottom: 0.5rem !important;
        }
        h4 {
            font-size: 1rem;
            margin-bottom: 0.75rem;
        }
        .alert {
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-10 col-md-8 col-lg-4 col-xl-3">
            <div class="card card-login shadow-sm border-0">
                <div class="card-body">
                    <h4 class="text-center fw-bold" style="color:#002d62;">Sign In</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-2">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autofocus>
                        </div>

                        <div class="mb-2">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>

                        <button type="submit" class="btn btn-login w-100 fw-semibold">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Log In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>