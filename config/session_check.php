<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout_duration = 1200;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: ../login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}