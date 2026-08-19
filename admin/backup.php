<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../config/database.php';
$database = new Database();
$db = $database->connect();
$backupDir = __DIR__ . '/../backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
$action = $_GET['action'] ?? $_POST['action'] ?? '';
if ($action === 'generate_backup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $timestamp = date('Y-m-d_His');
    $filename = 'crasm_backup_' . $timestamp . '.sql';
    $filepath = $backupDir . $filename;
    $output = "-- CRASM Database Backup\n";
    $output .= "-- Philippine Statistics Authority Region XII\n";
    $output .= "-- Generated: " . date('F d, Y h:i A') . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "START TRANSACTION;\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";
    $tablesStmt = $db->query('SHOW TABLES');
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $createStmt = $db->query("SHOW CREATE TABLE `$table`");
        $createRow = $createStmt->fetch(PDO::FETCH_ASSOC);
        $output .= $createRow['Create Table'] . ";\n\n";
        $dataStmt = $db->query("SELECT * FROM `$table`");
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            $output .= "INSERT INTO `$table` ($columnList) VALUES\n";
            $valueLines = [];
            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $value) {
                    $values[] = $value === null ? 'NULL' : $db->quote($value);
                }
                $valueLines[] = '(' . implode(', ', $values) . ')';
            }
            $output .= implode(",\n", $valueLines) . ";\n\n";
        }
    }
    $output .= "COMMIT;\n";
    @file_put_contents($filepath, $output);
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($output));
    echo $output;
    exit;
}
$sizeStmt = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
$sizeRow = $sizeStmt->fetch(PDO::FETCH_ASSOC);
$databaseSize = $sizeRow['size_mb'] ?? 0;
$recordsStmt = $db->query("SELECT COUNT(*) AS total FROM authority_records");
$totalRecords = $recordsStmt->fetch(PDO::FETCH_ASSOC)['total'];
$statusType = $_GET['status'] ?? '';
$statusMessage = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRASM | Backup</title>
    <link href="../assets/vendor/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-free-7.3.1/css/all.min.css">
</head>
<body class="bg-light">
<?php require __DIR__ . '/../includes/navbar.php'; ?>
<div class="min-vh-100 py-4">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm rounded-3 p-3 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-database text-secondary me-2"></i>Database Backup</h4> <hr>
                    <p class="text-muted small mb-0">Generate a complete SQL dump of the CRASM database for disaster recovery or migration.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-regular fa-clock me-1"></i> Last backup: <?php echo date('M d, Y h:i A'); ?></span>
                </div>
            </div>
        </div>
        <?php if ($statusMessage !== ''): ?>
            <div class="alert alert-<?php echo $statusType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid <?php echo $statusType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
                <?php echo htmlspecialchars($statusMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="bg-light rounded p-3 text-secondary mb-2">
                            <i class="fa-solid fa-server fs-4"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-semibold">Database Size</div>
                            <div class="fs-4 fw-bold text-dark"><?php echo $databaseSize; ?> MB</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="bg-light rounded p-3 text-secondary mb-2">
                            <i class="fa-solid fa-folder-open fs-4"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-semibold">Total Records</div>
                            <div class="fs-4 fw-bold text-dark"><?php echo $totalRecords; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-light rounded p-3 text-secondary">
                                <i class="fa-solid fa-download fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0 text-dark">Generate Backup</h5>
                        </div>
                        <p class="text-muted small flex-grow-1">Create a full SQL dump including all tables, records, and structure. The file will be saved and downloaded immediately.</p>
                        <form method="POST" action="backup.php?action=generate_backup">
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="fa-solid fa-database me-2"></i> Generate Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>