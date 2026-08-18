<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$adminUsername = $_SESSION['admin_username'] ?? 'Administrator';
$adminInitial  = strtoupper(substr($adminUsername, 0, 1));

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRASM | Dashboard</title>

<link href="../assets/vendor/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/vendor/fontawesome-free-7.3.1/css/all.min.css">
</head>

<body>

<div class="d-flex">

    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 bg-light">

        <div class="d-lg-none border-bottom bg-white px-3 py-3">
            <button
                class="btn btn-outline-secondary btn-sm"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <main class="p-3 p-md-4">

            <div class="mb-4">
                <h4 class="fw-bold mb-1">Dashboard</h4>
                <p class="text-muted small mb-0">Overview of the CRASM records management system.</p>
            </div>

        </main>

    </div>

</div>

<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>