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

// Auto-assign "no" based on order added (next number after the highest existing one).
$noStmt = $db->query("SELECT MAX(no) AS max_no FROM authority_records");
$maxNo = $noStmt->fetch(PDO::FETCH_ASSOC)['max_no'] ?? 0;
$data['no'] = (int) $maxNo + 1;

$allFields = array_merge(['no'], $fields);

$columns = implode(', ', $allFields);
$placeholders = ':' . implode(', :', $allFields);

$stmt = $db->prepare("INSERT INTO authority_records ($columns) VALUES ($placeholders)");

foreach ($allFields as $field) {
    $stmt->bindValue(':' . $field, $data[$field]);
}

$stmt->execute();

header('Location: ../admin/authority.php');
exit;