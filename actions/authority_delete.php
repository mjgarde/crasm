<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: ../admin/authority.php');
    exit;
}

$database = new Database();
$db = $database->connect();

$stmt = $db->prepare("DELETE FROM authority_records WHERE id = :id");
$stmt->bindValue(':id', $id);
$stmt->execute();

header('Location: ../admin/authority.php');
exit;