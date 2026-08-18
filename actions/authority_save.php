<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/authority.php');
    exit;
}

$database = new Database();
$db = $database->connect();

$fields = [
    'no',
    'crasm_no',
    'name_of_so',
    'provinces',
    'type',
    'religious_sect',
    'sex',
    'church_address',
    'contact_number',
    'position',
    'filed',
    'payment',
    'received_in_rsso',
    'processed',
    'return_to_province_for_compliance',
    'complied',
    'received_in_rsso_after_compliance',
    'approved',
    'transmitted_to_pso',
];

$data = [];

foreach ($fields as $field) {
    $value = trim($_POST[$field] ?? '');
    $data[$field] = $value === '' ? null : $value;
}

$columns = implode(', ', $fields);
$placeholders = ':' . implode(', :', $fields);

$stmt = $db->prepare("INSERT INTO authority_records ($columns) VALUES ($placeholders)");

foreach ($fields as $field) {
    $stmt->bindValue(':' . $field, $data[$field]);
}

$stmt->execute();

header('Location: ../admin/authority.php');
exit;