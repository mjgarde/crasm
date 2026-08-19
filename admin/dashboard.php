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

$allStmt = $db->query("SELECT * FROM authority_records");
$allRecords = $allStmt->fetchAll(PDO::FETCH_ASSOC);

$totalRecords = count($allRecords);

$currentYear = date('Y');
$currentMonth = date('n');

$newCount = 0;
$renewalCount = 0;
$maleCount = 0;
$femaleCount = 0;
$pendingCount = 0;
$compliancePendingCount = 0;

$byProvince = array_fill_keys($provinces, 0);

$monthlyApproved = array_fill_keys(range(1, 12), 0);

$processingDaysTotal = 0;
$processingDaysCount = 0;

$sectCounts = [];

foreach ($allRecords as $r) {
    if ($r['type'] === 'New') {
        $newCount++;
    } elseif ($r['type'] === 'Renewal') {
        $renewalCount++;
    }

    if ($r['sex'] === 'Male') {
        $maleCount++;
    } elseif ($r['sex'] === 'Female') {
        $femaleCount++;
    }

    if (isset($byProvince[$r['provinces']])) {
        $byProvince[$r['provinces']]++;
    }

    if (!empty($r['filed']) && empty($r['approved'])) {
        $pendingCount++;
    }

    if (!empty($r['return_to_province_for_compliance']) && empty($r['complied'])) {
        $compliancePendingCount++;
    }

    if (!empty($r['approved'])) {
        $approvedYear = (int) substr($r['approved'], 0, 4);
        if ($approvedYear == $currentYear) {
            $approvedMonth = (int) substr($r['approved'], 5, 2);
            $monthlyApproved[$approvedMonth]++;
        }
    }

    if (!empty($r['filed']) && !empty($r['approved'])) {
        $days = (strtotime($r['approved']) - strtotime($r['filed'])) / 86400;
        if ($days >= 0) {
            $processingDaysTotal += $days;
            $processingDaysCount++;
        }
    }

    $sect = trim($r['religious_sect'] ?? '');
    if ($sect !== '') {
        $sectCounts[$sect] = ($sectCounts[$sect] ?? 0) + 1;
    }
}

$avgProcessingDays = $processingDaysCount > 0 ? round($processingDaysTotal / $processingDaysCount) : 0;

arsort($sectCounts);
$topSects = array_slice($sectCounts, 0, 5, true);

usort($allRecords, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$recentRecords = array_slice($allRecords, 0, 8);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRASM | Dashboard</title>

<link href="../assets/vendor/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/vendor/fontawesome-free-7.3.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>

<style>
:root {
    --psa-blue: #003883;
    --psa-blue-dark: #002a63;
    --psa-red: #a3202f;
    --psa-gold: #d4a017;
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

.dash-shell {
    max-width: 1500px;
}

.kpi-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 16px 18px;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
    display: flex;
    align-items: center;
    gap: 14px;
    height: 100%;
}

.kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    color: #fff;
}

.kpi-label {
    font-size: 11px;
    color: var(--ink-soft);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 2px;
}

.kpi-value {
    font-size: 20px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.1;
}

.kpi-sub {
    font-size: 10.5px;
    color: var(--ink-soft);
    margin-top: 2px;
}

.panel-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
}

.panel-card-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    gap: 10px;
}

.panel-card-header .bar {
    width: 4px;
    height: 16px;
    border-radius: 2px;
    background: var(--psa-blue);
}

.panel-card-header h6 {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--ink);
    margin: 0;
}

.panel-card-body {
    padding: 16px;
}

.recent-table {
    width: 100%;
    font-size: 11.5px;
    border-collapse: collapse;
}

.recent-table th {
    background: #f8f9fb;
    color: var(--ink-soft);
    font-weight: 600;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 8px 10px;
    text-align: left;
    border-bottom: 1px solid var(--line);
}

.recent-table td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
}

.recent-table tbody tr:hover td {
    background-color: #f5f8fc;
}

.badge-type-new {
    background-color: rgba(0, 56, 131, 0.1);
    color: var(--psa-blue);
    font-weight: 600;
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 20px;
}

.badge-type-renewal {
    background-color: rgba(163, 32, 47, 0.1);
    color: var(--psa-red);
    font-weight: 600;
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 20px;
}

.sect-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--line);
    font-size: 12px;
}

.sect-item:last-child {
    border-bottom: none;
}

.sect-bar-track {
    flex-grow: 1;
    height: 6px;
    background: #eef1f5;
    border-radius: 4px;
    overflow: hidden;
}

.sect-bar-fill {
    height: 100%;
    background: var(--psa-blue);
    border-radius: 4px;
}

.sect-count {
    font-weight: 700;
    font-size: 12px;
    color: var(--ink);
    min-width: 22px;
    text-align: right;
}
</style>

</head>

<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="bg-light min-vh-100">

    <main class="p-3 p-md-4">
    <div class="dash-shell">

        <div class="mb-4">
            <h4 class="fw-bold mb-1">Dashboard</h4>
            <p class="text-muted small mb-0">Overview of the CRASM records management system.</p>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background-color:var(--psa-blue);">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Total Records</div>
                        <div class="kpi-value"><?php echo $totalRecords; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background-color:var(--psa-gold);">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </div>
                    <div>
                        <div class="kpi-label">New Applications</div>
                        <div class="kpi-value"><?php echo $newCount; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background-color:var(--psa-red);">
                        <i class="fa-solid fa-rotate"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Renewals</div>
                        <div class="kpi-value"><?php echo $renewalCount; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background-color:#5b6472;">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="kpi-label">Pending Approval</div>
                        <div class="kpi-value"><?php echo $pendingCount; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-1">
            <div class="col-lg-7">
                <div class="panel-card h-100">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>Monthly Approved Records (<?php echo $currentYear; ?>)</h6>
                    </div>
                    <div class="panel-card-body">
                        <canvas id="monthlyChart" height="90"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel-card h-100">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>Records by Province</h6>
                    </div>
                    <div class="panel-card-body">
                        <canvas id="provinceChart" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-1">
            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>New vs Renewal</h6>
                    </div>
                    <div class="panel-card-body">
                        <canvas id="typeChart" height="140"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>Gender Distribution</h6>
                    </div>
                    <div class="panel-card-body">
                        <canvas id="genderChart" height="140"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>Processing Snapshot</h6>
                    </div>
                    <div class="panel-card-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <div class="kpi-label">Avg. Processing Time</div>
                                <div class="kpi-value"><?php echo $avgProcessingDays; ?> <span style="font-size:12px;font-weight:600;color:var(--ink-soft);">days</span></div>
                                <div class="kpi-sub">From filing to approval</div>
                            </div>
                            <div>
                                <div class="kpi-label">Awaiting Compliance</div>
                                <div class="kpi-value"><?php echo $compliancePendingCount; ?></div>
                                <div class="kpi-sub">Returned to province, not yet complied</div>
                            </div>
                            <div>
                                <div class="kpi-label">Male / Female</div>
                                <div class="kpi-value"><?php echo $maleCount; ?> / <?php echo $femaleCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="panel-card">
                    <div class="panel-card-header d-flex justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="bar"></span>
                            <h6>Recent Activity</h6>
                        </div>
                        <a href="authority.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px;">
                            View All <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="recent-table">
                            <thead>
                                <tr>
                                    <th>CRASM#</th>
                                    <th>Name of SO</th>
                                    <th>Province</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentRecords)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No records yet.</td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ($recentRecords as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['crasm_no']); ?></td>
                                    <td><?php echo htmlspecialchars($r['name_of_so']); ?></td>
                                    <td><?php echo htmlspecialchars($r['provinces']); ?></td>
                                    <td>
                                        <span class="<?php echo $r['type'] === 'New' ? 'badge-type-new' : 'badge-type-renewal'; ?>">
                                            <?php echo htmlspecialchars($r['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['approved'])): ?>
                                            <span class="text-muted small"><i class="fa-solid fa-circle-check me-1" style="color:#2f9e44;"></i>Approved</span>
                                        <?php elseif (!empty($r['filed'])): ?>
                                            <span class="text-muted small"><i class="fa-solid fa-hourglass-half me-1" style="color:var(--psa-gold);"></i>Processing</span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>Top Religious Sects</h6>
                    </div>
                    <div class="panel-card-body">
                        <?php if (empty($topSects)): ?>
                            <p class="text-muted small mb-0">No data available.</p>
                        <?php else: ?>
                            <?php $maxSectCount = max($topSects); ?>
                            <?php foreach ($topSects as $sect => $count): ?>
                            <div class="sect-item">
                                <span style="flex:0 0 40%;" class="text-truncate" title="<?php echo htmlspecialchars($sect); ?>">
                                    <?php echo htmlspecialchars($sect); ?>
                                </span>
                                <div class="sect-bar-track">
                                    <div class="sect-bar-fill" style="width:<?php echo round(($count / $maxSectCount) * 100); ?>%;"></div>
                                </div>
                                <span class="sect-count"><?php echo $count; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </main>

</div>

<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
<script>
const psaBlue = '#003883';
const psaGold = '#d4a017';
const psaRed = '#a3202f';
const inkSoft = '#5b6472';

// Monthly Approved Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($monthNames); ?>,
        datasets: [{
            label: 'Approved',
            data: <?php echo json_encode(array_values($monthlyApproved)); ?>,
            backgroundColor: psaBlue,
            borderRadius: 4,
            maxBarThickness: 28
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f5' } },
            x: { grid: { display: false } }
        }
    }
});

// Province Chart
new Chart(document.getElementById('provinceChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($byProvince)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($byProvince)); ?>,
            backgroundColor: [psaBlue, psaGold, psaRed, inkSoft],
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f5' } },
            y: { grid: { display: false } }
        }
    }
});

// Type Chart
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: ['New', 'Renewal'],
        datasets: [{
            data: [<?php echo $newCount; ?>, <?php echo $renewalCount; ?>],
            backgroundColor: [psaBlue, psaRed],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});

// Gender Chart
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: ['Male', 'Female'],
        datasets: [{
            data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
            backgroundColor: [psaBlue, psaGold],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});
</script>

</body>
</html>