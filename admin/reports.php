<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$adminUsername = $_SESSION['admin_username'] ?? 'Administrator';
$adminInitial  = strtoupper(substr($adminUsername, 0, 1));

$database = new Database();
$db = $database->connect();

$provinces = [
    'Cotabato',
    'Sarangani',
    'South Cotabato',
    'Sultan Kudarat',
];

$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

$filterType = $_GET['type'] ?? '';
$filterProvince = $_GET['province'] ?? '';
$filterYear = $_GET['year'] ?? '';
$filterMonth = $_GET['month'] ?? '';

$query = "SELECT * FROM authority_records WHERE 1=1";
$params = [];

if ($filterType !== '') {
    $query .= " AND type = ?";
    $params[] = $filterType;
}

if ($filterProvince !== '') {
    $query .= " AND provinces = ?";
    $params[] = $filterProvince;
}

if ($filterYear !== '') {
    $query .= " AND YEAR(approved) = ?";
    $params[] = $filterYear;
}

if ($filterMonth !== '') {
    $query .= " AND MONTH(approved) = ?";
    $params[] = $filterMonth;
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalRecords = count($records);

$allStmt = $db->query("SELECT * FROM authority_records");
$allRecords = $allStmt->fetchAll(PDO::FETCH_ASSOC);

$byProvince = array_fill_keys($provinces, 0);
$byType = ['New' => 0, 'Renewal' => 0];
$bySex = ['Male' => 0, 'Female' => 0];
$byYear = [];
$byMonth = array_fill_keys(range(1, 12), 0);

foreach ($allRecords as $record) {
    if (isset($byProvince[$record['provinces']])) {
        $byProvince[$record['provinces']]++;
    }
    if (isset($byType[$record['type']])) {
        $byType[$record['type']]++;
    }
    if (isset($bySex[$record['sex']])) {
        $bySex[$record['sex']]++;
    }
    if (!empty($record['approved'])) {
        $year = substr($record['approved'], 0, 4);
        $byYear[$year] = ($byYear[$year] ?? 0) + 1;

        $month = (int)substr($record['approved'], 5, 2);
        $byMonth[$month] = ($byMonth[$month] ?? 0) + 1;
    }
}

krsort($byYear);

$yearStmt = $db->query("SELECT DISTINCT YEAR(approved) as year FROM authority_records WHERE approved IS NOT NULL ORDER BY year DESC");
$availableYears = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

$maxProvinceCount = max(array_merge($byProvince, [1]));

$reportQuery = "SELECT * FROM authority_records WHERE approved IS NOT NULL";
$reportParams = [];

if ($filterProvince !== '') {
    $reportQuery .= " AND provinces = ?";
    $reportParams[] = $filterProvince;
}
if ($filterYear !== '') {
    $reportQuery .= " AND YEAR(approved) = ?";
    $reportParams[] = $filterYear;
}
if ($filterType !== '') {
    $reportQuery .= " AND type = ?";
    $reportParams[] = $filterType;
}
if ($filterMonth !== '') {
    $reportQuery .= " AND MONTH(approved) = ?";
    $reportParams[] = $filterMonth;
}

$reportStmt = $db->prepare($reportQuery);
$reportStmt->execute($reportParams);
$reportRecords = $reportStmt->fetchAll(PDO::FETCH_ASSOC);

$displayMonths = $filterMonth !== '' ? [(int)$filterMonth] : range(1, 12);

$pmType  = [];
$pmSex   = [];
$pmTotal = [];
$monthType = array_fill_keys(range(1, 12), ['New' => 0, 'Renewal' => 0]);
$monthSex  = array_fill_keys(range(1, 12), ['Male' => 0, 'Female' => 0]);
$monthTotal = array_fill_keys(range(1, 12), 0);
$provinceType = array_fill_keys($provinces, ['New' => 0, 'Renewal' => 0]);
$provinceSex  = array_fill_keys($provinces, ['Male' => 0, 'Female' => 0]);
$provinceTotal = array_fill_keys($provinces, 0);

foreach ($provinces as $p) {
    $pmType[$p]  = array_fill_keys(range(1, 12), ['New' => 0, 'Renewal' => 0]);
    $pmSex[$p]   = array_fill_keys(range(1, 12), ['Male' => 0, 'Female' => 0]);
    $pmTotal[$p] = array_fill_keys(range(1, 12), 0);
}

$grandType = ['New' => 0, 'Renewal' => 0];
$grandSex  = ['Male' => 0, 'Female' => 0];
$grandTotal = 0;

foreach ($reportRecords as $r) {
    $province = $r['provinces'];
    $type = $r['type'];
    $sex = $r['sex'];
    $month = (int)substr($r['approved'], 5, 2);

    if (!isset($pmType[$province])) {
        continue;
    }

    if ($type === 'New' || $type === 'Renewal') {
        $pmType[$province][$month][$type]++;
        $monthType[$month][$type]++;
        $provinceType[$province][$type]++;
        $grandType[$type]++;
    }

    if ($sex === 'Male' || $sex === 'Female') {
        $pmSex[$province][$month][$sex]++;
        $monthSex[$month][$sex]++;
        $provinceSex[$province][$sex]++;
        $grandSex[$sex]++;
    }

    $pmTotal[$province][$month]++;
    $monthTotal[$month]++;
    $provinceTotal[$province]++;
    $grandTotal++;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRASM | Reports</title>

<link href="../assets/vendor/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/vendor/fontawesome-free-7.3.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --psa-blue: #003883;
    --psa-blue-dark: #002a63;
    --psa-red: #CE1126;
    --psa-orange: #F26522;
    --ink: #1c2430;
    --ink-soft: #5b6472;
    --line: #e3e7ee;
    --surface: #ffffff;
    --canvas: #f4f6f9;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    color: var(--ink);
    background-color: var(--canvas);
}

.print-header {
    display: none;
}

.reports-shell {
    max-width: none;
    width: 100%;
}

.toolbar {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 18px;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
}

.toolbar-field label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin-bottom: 4px;
    display: block;
}

.form-select-sm,
.form-control-sm {
    border-color: var(--line);
    font-size: 13px;
    border-radius: 6px;
}

.form-select-sm:focus,
.form-control-sm:focus {
    border-color: var(--psa-blue);
    box-shadow: 0 0 0 3px rgba(0, 56, 131, 0.12);
}

.btn-psa-primary {
    background-color: var(--psa-blue);
    border-color: var(--psa-blue);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
}

.btn-psa-primary:hover {
    background-color: var(--psa-blue);
    border-color: var(--psa-blue);
    color: #fff;
}

.btn-psa-outline {
    border: 1px solid var(--line);
    color: var(--ink-soft);
    font-size: 13px;
    border-radius: 6px;
    background: var(--surface);
}

.btn-psa-outline:hover {
    border-color: var(--psa-blue);
    color: var(--psa-blue);
}

.btn-psa-accent {
    background-color: transparent;
    border: 1px solid #000;
    color: #000;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 0 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.btn-psa-accent:hover {
    background-color: transparent;
    border-color: #000;
    color: #000;
}

.active-filter-note {
    font-size: 12.5px;
    color: var(--ink-soft);
}

.metric-strip {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    padding: 8px 10px;
}

.metric-item {
    display: flex;
    align-items: baseline;
    gap: 6px;
    font-size: 10px;
}

.metric-label {
    color: var(--ink-soft);
}

.metric-value {
    font-weight: 700;
    font-size: 11px;
    color: var(--ink);
}

.report-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
}

.report-card-header {
    padding: 10px 16px;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.report-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.report-card-header .bar {
    width: 4px;
    height: 16px;
    border-radius: 2px;
    background: var(--psa-blue);
}

.report-card-header h6 {
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: 0.01em;
    color: var(--ink);
    margin: 0;
}

.table-scroll {
    overflow-x: auto;
}

.report-table {
    width: auto;
    font-size: 12px;
    border-collapse: collapse;
    margin: 0;
}

.report-table th,
.report-table td {
    white-space: nowrap;
    text-align: center !important;
    padding: 6px 10px;
}

.report-table thead th {
    background: #f8f9fb;
    color: var(--ink-soft);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-bottom: 1px solid var(--line);
    position: sticky;
    top: 0;
}

.report-table td:first-child,
.report-table th:first-child {
    text-align: left !important;
    position: sticky;
    left: 0;
    background: var(--surface);
    font-weight: 600;
    z-index: 1;
}

.report-table thead th:first-child {
    background: #f8f9fb;
    z-index: 2;
}

.report-table tbody tr {
    border-bottom: 1px solid var(--line);
}

.report-table tbody tr:hover td {
    background-color: #f5f8fc;
}

.report-table tbody tr:hover td:first-child {
    background-color: #f5f8fc;
}

.report-table tfoot td {
    font-weight: 700;
    background: #f8f9fb;
    border-top: 2px solid var(--line);
}

@media (min-width: 1200px) {
    .report-table:not(.report-table-compact) {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .report-table {
        font-size: 8px;
    }
    .report-table th,
    .report-table td {
        padding: 3px 6px;
    }
    .report-table thead th {
        font-size: 8px;
    }
}

@media print {
    @page {
        size: landscape;
        margin: 10mm;
    }
    body {
        background: #fff;
    }
    .no-print {
        display: none !important;
    }
    .crasm-navbar {
        display: none !important;
    }
    main {
        padding: 0 !important;
    }
    .print-header {
        display: flex !important;
        align-items: center;
        gap: 12px;
        border-bottom: 2px solid var(--psa-blue);
        padding-bottom: 10px;
        margin-bottom: 16px;
    }
    .report-card {
        border: 1px solid #ccc;
        box-shadow: none;
        page-break-inside: avoid;
    }
    .table-scroll {
        overflow: visible !important;
    }
    .report-table {
        width: 100%;
        table-layout: fixed;
        font-size: 10px;
    }
    .report-table thead th {
        font-size: 9px;
    }
    .report-table th,
    .report-table td {
        padding: 4px 3px;
        white-space: normal;
        text-align: center !important;
    }
    .report-table th:first-child,
    .report-table td:first-child {
        width: 11%;
        text-align: left !important;
    }
    .report-table th:nth-child(2),
    .report-table td:nth-child(2),
    .report-table th:last-child,
    .report-table td:last-child {
        width: 6%;
    }
    .report-table-compact {
        width: auto;
        table-layout: auto;
    }
    .report-table-compact th,
    .report-table-compact td {
        width: auto !important;
    }
    .report-table-compact th:first-child,
    .report-table-compact td:first-child {
        text-align: left !important;
    }
    .report-card-compact {
        display: inline-block;
        width: auto;
    }
}
</style>

</head>

<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="bg-light min-vh-100">

    <main class="p-3 p-md-4">
    <div class="reports-shell">

        <div class="print-header">
            <img src="../assets/img/logo.png" alt="PSA Seal" style="width:56px;height:56px;object-fit:contain;">
            <div>
                <div class="fw-bold" style="font-size:16px;">Philippine Statistics Authority</div>
                <div class="text-muted" style="font-size:12px;">Region XII</div>
            </div>
        </div>

        <div class="toolbar no-print">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-6 col-md-2 toolbar-field">
                    <label>Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="New" <?php echo $filterType === 'New' ? 'selected' : ''; ?>>New</option>
                        <option value="Renewal" <?php echo $filterType === 'Renewal' ? 'selected' : ''; ?>>Renewal</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 toolbar-field">
                    <label>Province</label>
                    <select name="province" class="form-select form-select-sm">
                        <option value="">All Provinces</option>
                        <?php foreach ($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province); ?>" <?php echo $filterProvince === $province ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($province); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2 toolbar-field">
                    <label>Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $filterYear == $year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2 toolbar-field">
                    <label>Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">All Months</option>
                        <?php foreach ($monthNames as $index => $name): ?>
                        <option value="<?php echo $index + 1; ?>" <?php echo $filterMonth == ($index + 1) ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-psa-primary flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-psa-accent" onclick="window.print();" title="Print Report">
                        <i class="fa-solid fa-print me-1"></i> Print
                    </button>
                </div>
            </form>
            <?php if ($filterType !== '' || $filterProvince !== '' || $filterYear !== '' || $filterMonth !== ''): ?>
            <div class="mt-3 d-flex align-items-center gap-2">
                <a href="reports.php" class="btn btn-sm btn-psa-outline">
                    <i class="fa-solid fa-xmark me-1"></i> Clear Filters
                </a>
                <span class="active-filter-note">Showing filtered results — <?php echo $totalRecords; ?> record<?php echo $totalRecords === 1 ? '' : 's'; ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>Summary Overview</h6>
                </div>
            </div>
            <div class="table-scroll">
                <div class="metric-strip">
                    <div class="metric-item">
                        <span class="metric-label">Total Records</span>
                        <span class="metric-value"><?php echo $totalRecords; ?></span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">New Applications</span>
                        <span class="metric-value" style="color:var(--psa-blue);"><?php echo $byType['New']; ?></span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Renewals</span>
                        <span class="metric-value" style="color:var(--psa-red);"><?php echo $byType['Renewal']; ?></span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Male / Female</span>
                        <span class="metric-value"><?php echo $bySex['Male']; ?> / <?php echo $bySex['Female']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>CRASM Status by Province and Month</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table<?php echo count($displayMonths) === 1 ? ' report-table-compact' : ''; ?>">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle">Province</th>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th colspan="2"><?php echo $name; ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th>New</th>
                            <th>Renewal</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($provinces as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p); ?></td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $pmType[$p][$m]['New']; ?></td>
                            <td><?php echo $pmType[$p][$m]['Renewal']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthType[$m]['New']; ?></td>
                            <td><?php echo $monthType[$m]['Renewal']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>Gender by Month by Provinces</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table<?php echo count($displayMonths) === 1 ? ' report-table-compact' : ''; ?>">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle">Province</th>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th colspan="2"><?php echo $name; ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th>Male</th>
                            <th>Female</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($provinces as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p); ?></td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $pmSex[$p][$m]['Male']; ?></td>
                            <td><?php echo $pmSex[$p][$m]['Female']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthSex[$m]['Male']; ?></td>
                            <td><?php echo $monthSex[$m]['Female']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>CRASM Status by Month</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table<?php echo count($displayMonths) === 1 ? ' report-table-compact' : ''; ?>">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th><?php echo $name; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>New</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthType[$m]['New']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Renewal</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthType[$m]['Renewal']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthTotal[$m]; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>CRASM by Month and by Provinces</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table<?php echo count($displayMonths) === 1 ? ' report-table-compact' : ''; ?>">
                    <thead>
                        <tr>
                            <th>Province</th>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th><?php echo $name; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($provinces as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p); ?></td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $pmTotal[$p][$m]; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthTotal[$m]; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>Gender by Month</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table<?php echo count($displayMonths) === 1 ? ' report-table-compact' : ''; ?>">
                    <thead>
                        <tr>
                            <th>Gender</th>
                            <?php foreach ($displayMonths as $m): $name = $monthNames[$m - 1]; ?>
                            <th><?php echo $name; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Male</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthSex[$m]['Male']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Female</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthSex[$m]['Female']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <?php foreach ($displayMonths as $m): ?>
                            <td><?php echo $monthTotal[$m]; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-card report-card-compact">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>CRASM Status by Province Total</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table report-table-compact">
                    <thead>
                        <tr>
                            <th>Province</th>
                            <th>New</th>
                            <th>Renewal</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($provinces as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p); ?></td>
                            <td><?php echo $provinceType[$p]['New']; ?></td>
                            <td><?php echo $provinceType[$p]['Renewal']; ?></td>
                            <td><?php echo $provinceTotal[$p]; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?php echo $grandType['New']; ?></td>
                            <td><?php echo $grandType['Renewal']; ?></td>
                            <td><?php echo $grandTotal; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="report-card report-card-compact">
            <div class="report-card-header">
                <div class="report-card-header-left">
                    <span class="bar"></span>
                    <h6>Gender Total by Provinces</h6>
                </div>
            </div>
            <div class="table-scroll">
                <table class="report-table report-table-compact">
                    <thead>
                        <tr>
                            <th>Province</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($provinces as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p); ?></td>
                            <td><?php echo $provinceSex[$p]['Male']; ?></td>
                            <td><?php echo $provinceSex[$p]['Female']; ?></td>
                            <td><?php echo $provinceTotal[$p]; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?php echo $grandSex['Male']; ?></td>
                            <td><?php echo $grandSex['Female']; ?></td>
                            <td><?php echo $grandTotal; ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
    </main>

</div>

<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>