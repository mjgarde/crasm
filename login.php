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

        $stmt = $pdo->prepare("
            SELECT id, username, password
            FROM administrator
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: admin/dashboard.php');
            exit;
        }

        $error = 'Invalid username or password. Please try again.';
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
</head>

<body class="d-flex flex-column min-vh-100" style="background-color:#eef1f5;">

<div class="w-100" style="background-color:#002d62;">
    <div class="container py-2">
        <div class="d-flex align-items-center flex-wrap">
            <img src="assets/img/logo.png"
                 alt="Agency Seal"
                 class="me-2"
                 style="width:36px;height:36px;object-fit:contain;">

            <div>
                <div class="text-white small fw-semibold" style="letter-spacing:.5px;">
                    PHILIPPINE STATISTICS AUTHORITY
                </div>

                <div class="text-white fw-bold" style="font-size:13px;letter-spacing:.5px;">
                    CERTIFICATE OF REGISTRATION OF AUTHORITY TO SOLEMNIZE MARRIAGE (CRASM)
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w-100" style="height:5px;background-color:#ffb81c;"></div>

<main class="flex-grow-1 d-flex align-items-center py-4">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-11 col-sm-8 col-md-6 col-lg-5 col-xl-4">

                <div class="card shadow-sm border">

                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h4 class="fw-bold mb-0" style="color:#002d62;">
                                Sign In
                            </h4>
                        </div>

                        <?php if ($error): ?>

                            <div class="alert alert-light border small" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>

                        <?php endif; ?>

                        <form method="POST" id="loginForm" novalidate>

                            <div class="mb-3">

                                <label for="username" class="form-label fw-semibold small">
                                    Username <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-white">
                                        <i class="fa-solid fa-user"></i>
                                    </span>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="username"
                                        name="username"
                                        placeholder="Enter your username"
                                        autocomplete="username"
                                        required
                                    >

                                    <div class="invalid-feedback">
                                        Username is required.
                                    </div>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label for="password" class="form-label fw-semibold small">
                                    Password <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-white">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                        required
                                    >

                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        id="togglePassword"
                                        tabindex="-1"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <div class="invalid-feedback">
                                        Password is required.
                                    </div>

                                </div>

                            </div>
<hr>
                            <div class="d-flex justify-content-between align-items-center mb-4">

                            <button
                                type="submit"
                                class="btn w-100 fw-semibold text-white"
                                style="background-color:#002d62;"
                            >
                                <i class="fa-solid fa-right-to-bracket me-1"></i>
                                Log In
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

<script src="assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

const form = document.getElementById('loginForm');

form.addEventListener('submit', function (event) {
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }

    form.classList.add('was-validated');
});
</script>

</body>
</html>