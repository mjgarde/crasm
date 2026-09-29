<?php
session_start();
require_once 'config/database.php';

$adminHome = 'admin/dashboard.php';
$userHome  = 'user/dashboard.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: ' . $adminHome);
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $userHome);
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
            unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_name']);
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['name'] ?? $admin['username'];
            $_SESSION['last_activity'] = time();
            header('Location: ' . $adminHome);
            exit;
        }

        $user = false;
        try {
            $stmt = $pdo->prepare("SELECT id, username, name, password FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
        } catch (Throwable $e) {
            $user = false;
        }

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name']);
            $_SESSION['role'] = 'user';
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'] ?? $user['username'];
            $_SESSION['last_activity'] = time();
            header('Location: ' . $userHome);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --navy: #0a1f44;
            --blue: #002d62;
            --ink: #1b2433;
            --muted: #5d6879;
            --line: #d7dde6;
        }

        body {
            background: #eef1f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 4px;
            padding: 2rem 2rem 1.75rem;
            box-shadow: 0 8px 24px rgba(10, 31, 68, .08);
        }

        .login-head {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-head img {
            width: 84px;
            height: 84px;
            object-fit: contain;
            margin-bottom: .75rem;
        }

        .login-head .agency {
            font-size: .82rem;
            color: var(--muted);
            margin: 0 0 .15rem;
        }

        .login-head h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--navy);
            margin: 0;
            line-height: 1.3;
        }

        .form-label {
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: .3rem;
        }

        .field { position: relative; }

        .field > i.lead-icon {
            position: absolute;
            left: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #8792a5;
            font-size: .85rem;
            pointer-events: none;
        }

        .field .form-control {
            height: 42px;
            padding-left: 2.4rem;
            font-size: .92rem;
            border: 1px solid #c3ccd9;
            border-radius: 3px;
        }

        .field .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0, 45, 98, .15);
        }

        #password { padding-right: 2.6rem; }

        .toggle-pw {
            position: absolute;
            right: 2px;
            top: 2px;
            height: 38px;
            width: 38px;
            border: 0;
            background: transparent;
            color: #6b7688;
            border-radius: 3px;
        }

        .toggle-pw:hover { color: var(--blue); }
        .toggle-pw:focus-visible { outline: 2px solid var(--blue); outline-offset: -2px; }

        .btn-login {
            height: 44px;
            background: var(--blue);
            border: 1px solid var(--blue);
            color: #fff;
            font-weight: 600;
            font-size: .95rem;
            border-radius: 3px;
        }

        .btn-login:hover { background: var(--navy); color: #fff; }
        .btn-login:focus-visible { outline: 3px solid rgba(0, 45, 98, .35); outline-offset: 2px; }

        .alert {
            font-size: .85rem;
            padding: .55rem .8rem;
            border-radius: 3px;
            margin-bottom: 1rem;
        }

        .login-foot {
            text-align: center;
            font-size: .75rem;
            color: var(--muted);
            margin-top: 1.25rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-head">
        <img src="assets/img/logo.png" alt="Philippine Statistics Authority logo">
        <p class="agency">Philippine Statistics Authority XII</p>
        <h1>CRASM</h1>
    </div>

    <?php if (isset($_GET['timeout'])): ?>
        <div class="alert alert-warning" role="alert">
            <i class="fa-solid fa-clock me-1"></i> Your session has expired. Please log in again.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="field">
                <i class="fa-solid fa-user lead-icon"></i>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" value="<?= htmlspecialchars($username ?? '') ?>" autocomplete="username" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="field">
                <i class="fa-solid fa-lock lead-icon"></i>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                <button type="button" class="toggle-pw" id="togglePw" aria-label="Show password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-login w-100">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Log in
        </button>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var btn = document.getElementById('togglePw');
        var input = document.getElementById('password');
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });
    })();
</script>
</body>
</html>