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

$allStmt = $db->query('SELECT * FROM authority_records');
$allRecordsRaw = $allStmt->fetchAll(PDO::FETCH_ASSOC);

$availableYears = [];
foreach ($allRecordsRaw as $r) {
    $refDate = $r['filed'] ?? $r['created_at'] ?? '';
    if ($refDate !== '') {
        $availableYears[substr($refDate, 0, 4)] = true;
    }
}
krsort($availableYears);
$availableYears = array_map('strval', array_keys($availableYears));

$selectedYear = (string) ($_GET['year'] ?? ($availableYears[0] ?? date('Y')));
if ($selectedYear !== 'all' && !in_array($selectedYear, $availableYears, true)) {
    $selectedYear = $availableYears[0] ?? date('Y');
}

$selectedQuarter = $_GET['quarter'] ?? 'all';
if (!in_array($selectedQuarter, ['all', '1', '2', '3', '4'], true)) {
    $selectedQuarter = 'all';
}

$allRecords = array_values(array_filter($allRecordsRaw, function ($r) use ($selectedYear, $selectedQuarter) {
    $refDate = $r['filed'] ?? $r['created_at'] ?? '';
    if ($refDate === '') {
        return false;
    }
    if ($selectedYear !== 'all' && substr($refDate, 0, 4) !== $selectedYear) {
        return false;
    }
    if ($selectedQuarter !== 'all') {
        $refMonth = (int) substr($refDate, 5, 2);
        $refQuarter = (int) ceil($refMonth / 3);
        if ($refQuarter !== (int) $selectedQuarter) {
            return false;
        }
    }
    return true;
}));

$totalRecords = count($allRecords);

$newCount = 0;
$renewalCount = 0;
$pendingCount = 0;
$approvedCount = 0;
$approvedThisMonth = 0;
$processingDaysSum = 0;
$processingDaysN = 0;

$sectCounts = [];
$provinceCounts = array_fill_keys($provinces, 0);
$sexCounts = ['Male' => 0, 'Female' => 0];

$stageCounts = [
    'filed' => 0,
    'received_in_rsso' => 0,
    'processed' => 0,
    'complied' => 0,
    'approved' => 0,
    'transmitted_to_pso' => 0,
];

$currentMonth = date('Y-m');

$monthlyTrend = [];
if ($selectedYear === 'all') {
    for ($i = 5; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $monthlyTrend[$m] = ['New' => 0, 'Renewal' => 0];
    }
} elseif ($selectedQuarter !== 'all') {
    $startMonth = ((int) $selectedQuarter - 1) * 3 + 1;
    for ($i = 0; $i < 3; $i++) {
        $key = $selectedYear . '-' . str_pad($startMonth + $i, 2, '0', STR_PAD_LEFT);
        $monthlyTrend[$key] = ['New' => 0, 'Renewal' => 0];
    }
} else {
    for ($m = 1; $m <= 12; $m++) {
        $key = $selectedYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
        $monthlyTrend[$key] = ['New' => 0, 'Renewal' => 0];
    }
}

$quarterLabels = ['Q1', 'Q2', 'Q3', 'Q4'];
$quarterlyTrend = [
    'Q1' => ['New' => 0, 'Renewal' => 0],
    'Q2' => ['New' => 0, 'Renewal' => 0],
    'Q3' => ['New' => 0, 'Renewal' => 0],
    'Q4' => ['New' => 0, 'Renewal' => 0],
];

foreach ($allRecords as $r) {
    if ($r['type'] === 'New') {
        $newCount++;
    } elseif ($r['type'] === 'Renewal') {
        $renewalCount++;
    }

    if (!empty($r['filed']) && empty($r['approved'])) {
        $pendingCount++;
    }

    if (!empty($r['approved'])) {
        $approvedCount++;
        if (substr($r['approved'], 0, 7) === $currentMonth) {
            $approvedThisMonth++;
        }
        if (!empty($r['filed'])) {
            $days = (strtotime($r['approved']) - strtotime($r['filed'])) / 86400;
            if ($days >= 0) {
                $processingDaysSum += $days;
                $processingDaysN++;
            }
        }
    }

    $sect = trim($r['religious_sect'] ?? '');
    if ($sect !== '') {
        $sectCounts[$sect] = ($sectCounts[$sect] ?? 0) + 1;
    }

    $prov = trim($r['provinces'] ?? '');
    if (isset($provinceCounts[$prov])) {
        $provinceCounts[$prov]++;
    }

    $sex = $r['sex'] ?? '';
    if (isset($sexCounts[$sex])) {
        $sexCounts[$sex]++;
    }

    foreach (array_keys($stageCounts) as $stage) {
        if (!empty($r[$stage])) {
            $stageCounts[$stage]++;
        }
    }

    if (!empty($r['filed']) && isset($r['type']) && in_array($r['type'], ['New', 'Renewal'], true)) {
        $m = substr($r['filed'], 0, 7);
        if (isset($monthlyTrend[$m])) {
            $monthlyTrend[$m][$r['type']]++;
        }

        $filedMonth = (int) substr($r['filed'], 5, 2);
        $q = 'Q' . (int) ceil($filedMonth / 3);
        if (isset($quarterlyTrend[$q])) {
            $quarterlyTrend[$q][$r['type']]++;
        }
    }
}

$avgProcessingDays = $processingDaysN > 0 ? round($processingDaysSum / $processingDaysN, 1) : 0;

arsort($sectCounts);
$topSects = array_slice($sectCounts, 0, 6, true);
$topSects = array_reverse($topSects, true);

usort($allRecords, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$recentRecords = array_slice($allRecords, 0, 8);

$monthLabels = array_map(fn($m) => date('M', strtotime($m . '-01')), array_keys($monthlyTrend));
$monthNewData = array_map(fn($v) => $v['New'], array_values($monthlyTrend));
$monthRenewalData = array_map(fn($v) => $v['Renewal'], array_values($monthlyTrend));

$quarterNewData = array_map(fn($v) => $v['New'], array_values($quarterlyTrend));
$quarterRenewalData = array_map(fn($v) => $v['Renewal'], array_values($quarterlyTrend));

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRASM | Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<script src="chart.umd.js"></script>
</head>
<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="min-vh-100">

  <main class="p-3 p-md-4">
  <div class="dash-shell">

    <div class="page-heading">
      <h1>Dashboard</h1>
      <form method="GET" class="d-flex gap-2">
        <select name="year" class="year-select" onchange="this.form.submit()">
          <option value="all" <?= $selectedYear === 'all' ? 'selected' : '' ?>>All years</option>
          <?php foreach ($availableYears as $y): ?>
            <option value="<?= htmlspecialchars($y) ?>" <?= $selectedYear === $y ? 'selected' : '' ?>>Year <?= htmlspecialchars($y) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="quarter" class="year-select" onchange="this.form.submit()">
          <option value="all" <?= $selectedQuarter === 'all' ? 'selected' : '' ?>>All quarters</option>
          <option value="1" <?= $selectedQuarter === '1' ? 'selected' : '' ?>>Q1 (Jan&ndash;Mar)</option>
          <option value="2" <?= $selectedQuarter === '2' ? 'selected' : '' ?>>Q2 (Apr&ndash;Jun)</option>
          <option value="3" <?= $selectedQuarter === '3' ? 'selected' : '' ?>>Q3 (Jul&ndash;Sep)</option>
          <option value="4" <?= $selectedQuarter === '4' ? 'selected' : '' ?>>Q4 (Oct&ndash;Dec)</option>
        </select>
      </form>
    </div>

    <div class="kpi-strip">
      <div class="kpi-cell">
        <span class="kpi-icon" style="background-color:var(--psa-blue);"><i class="fa-solid fa-folder-open"></i></span>
        <div>
          <div class="kpi-label">Total records</div>
          <div class="kpi-value"><?= $totalRecords ?></div>
        </div>
      </div>
      <div class="kpi-cell">
        <span class="kpi-icon" style="background-color:var(--psa-gold);"><i class="fa-solid fa-file-circle-plus"></i></span>
        <div>
          <div class="kpi-label">New applications</div>
          <div class="kpi-value"><?= $newCount ?></div>
        </div>
      </div>
      <div class="kpi-cell">
        <span class="kpi-icon" style="background-color:var(--psa-red);"><i class="fa-solid fa-rotate"></i></span>
        <div>
          <div class="kpi-label">Renewals</div>
          <div class="kpi-value"><?= $renewalCount ?></div>
        </div>
      </div>
      <div class="kpi-cell">
        <span class="kpi-icon" style="background-color:var(--ink-soft);"><i class="fa-solid fa-hourglass-half"></i></span>
        <div>
          <div class="kpi-label">Pending approval</div>
          <div class="kpi-value"><?= $pendingCount ?></div>
        </div>
      </div>
      <div class="kpi-cell">
        <span class="kpi-icon" style="background-color:var(--psa-green);"><i class="fa-solid fa-circle-check"></i></span>
        <div>
          <div class="kpi-label">Approved</div>
          <div class="kpi-value"><?= $approvedCount ?></div>
        </div>
      </div>
      <div class="kpi-cell">
        <span class="kpi-icon" style="background-color:var(--psa-blue-deep);"><i class="fa-solid fa-stopwatch"></i></span>
        <div>
          <div class="kpi-label">Avg. processing days</div>
          <div class="kpi-value"><?= $avgProcessingDays ?></div>
        </div>
      </div>
    </div>

    <div class="row g-3">

      <div class="col-lg-7">
        <section class="panel">
          <div class="panel-head">
            <div>
              <h2>Filing trend</h2>
              <p>New vs. renewal applications filed<?php
                if ($selectedYear === 'all') {
                    echo ', last 6 months';
                } else {
                    echo ', ' . htmlspecialchars($selectedYear);
                    if ($selectedQuarter !== 'all') {
                        echo ' Q' . htmlspecialchars($selectedQuarter);
                    }
                }
              ?>.</p>
            </div>
            <div class="granularity-toggle">
              <button type="button" id="btnMonthly" class="active" onclick="setTrendView('monthly')">Monthly</button>
              <button type="button" id="btnQuarterly" onclick="setTrendView('quarterly')">Quarterly</button>
            </div>
          </div>
          <div class="panel-body">
            <div style="height:240px;">
              <canvas id="trendChart"></canvas>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <h2>Top religious sects</h2>
              <p>Highest volume of authority records by sect.</p>
            </div>
          </div>
          <div class="panel-body">
            <?php if (empty($topSects)): ?>
              <p class="text-muted small mb-0">No data available.</p>
            <?php else: ?>
              <div style="height:220px;">
                <canvas id="sectChart"></canvas>
              </div>
            <?php endif; ?>
          </div>
        </section>
      </div>

      <div class="col-lg-5">
        <section class="panel">
          <div class="panel-head">
            <div>
              <h2>Records by province</h2>
            </div>
          </div>
          <div class="panel-body">
            <div style="height:200px;">
              <canvas id="provinceChart"></canvas>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <h2>Processing pipeline</h2>
              <p>Records that have reached each stage.</p>
            </div>
          </div>
          <div class="panel-body">
            <?php
              $stageLabels = [
                  'filed' => 'Filed',
                  'received_in_rsso' => 'Received in RSSO',
                  'processed' => 'Processed',
                  'complied' => 'Complied',
                  'approved' => 'Approved',
                  'transmitted_to_pso' => 'Transmitted to PSO',
              ];
              $stageMax = max(1, $stageCounts['filed']);
            ?>
            <?php foreach ($stageLabels as $key => $label): ?>
              <?php $pct = round(($stageCounts[$key] / $stageMax) * 100); ?>
              <div class="stage-row">
                <span class="stage-label"><?= $label ?></span>
                <span class="stage-track"><span class="stage-fill" style="width:<?= $pct ?>%;"></span></span>
                <span class="stage-value"><?= $stageCounts[$key] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <h2>Applicants by sex</h2>
            </div>
          </div>
          <div class="panel-body d-flex align-items-center gap-3">
            <div style="width:120px; height:120px; flex-shrink:0;">
              <canvas id="sexChart"></canvas>
            </div>
            <div class="legend-row flex-column gap-2 mt-0">
              <span class="legend-item"><span class="legend-dot" style="background-color:var(--psa-blue);"></span>Male &middot; <?= $sexCounts['Male'] ?></span>
              <span class="legend-item"><span class="legend-dot" style="background-color:var(--psa-gold);"></span>Female &middot; <?= $sexCounts['Female'] ?></span>
            </div>
          </div>
        </section>
      </div>

      <div class="col-12">
        <section class="panel">
          <div class="panel-head">
            <div>
              <h2>Recent activity</h2>
            </div>
            <a href="authority.php" class="link-quiet">View all <i class="fa-solid fa-arrow-right ms-1"></i></a>
          </div>
          <div class="table-responsive">
            <table class="recent-table">
              <thead>
                <tr>
                  <th>CRASM #</th>
                  <th>Name of SO</th>
                  <th>Province</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentRecords)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No records yet.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($recentRecords as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['crasm_no']) ?></td>
                  <td><?= htmlspecialchars($r['name_of_so']) ?></td>
                  <td><?= htmlspecialchars($r['provinces']) ?></td>
                  <td>
                    <span class="type-flag">
                      <span class="type-dot" style="background-color:<?= $r['type'] === 'New' ? 'var(--psa-gold)' : 'var(--psa-red)' ?>;"></span>
                      <?= htmlspecialchars($r['type']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if (!empty($r['approved'])): ?>
                      <span class="status-note"><i class="fa-solid fa-circle-check" style="color:var(--psa-green);"></i>Approved</span>
                    <?php elseif (!empty($r['filed'])): ?>
                      <span class="status-note"><i class="fa-solid fa-hourglass-half" style="color:var(--psa-gold);"></i>Processing</span>
                    <?php else: ?>
                      <span class="status-note">&mdash;</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>

    </div>

  </div>
  </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
Chart.defaults.font = { family: 'IBM Plex Sans', size: 11 };
Chart.defaults.color = '#5C6773';

const trendData = {
  monthly: {
    labels: <?= json_encode($monthLabels) ?>,
    newData: <?= json_encode($monthNewData) ?>,
    renewalData: <?= json_encode($monthRenewalData) ?>,
  },
  quarterly: {
    labels: <?= json_encode($quarterLabels) ?>,
    newData: <?= json_encode($quarterNewData) ?>,
    renewalData: <?= json_encode($quarterRenewalData) ?>,
  }
};

const trendChart = new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: trendData.monthly.labels,
    datasets: [
      {
        label: 'New',
        data: trendData.monthly.newData,
        borderColor: '#B8873A',
        backgroundColor: 'rgba(184,135,58,0.1)',
        fill: true,
        tension: .3,
        pointRadius: 3,
      },
      {
        label: 'Renewal',
        data: trendData.monthly.renewalData,
        borderColor: '#0B3D7A',
        backgroundColor: 'rgba(11,61,122,0.08)',
        fill: true,
        tension: .3,
        pointRadius: 3,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'top', align: 'end', labels: { boxWidth: 9, boxHeight: 9 } } },
    scales: {
      y: { beginAtZero: true, grid: { color: '#EEF1F5' }, ticks: { precision: 0 } },
      x: { grid: { display: false } }
    }
  }
});

function setTrendView(view) {
  trendChart.data.labels = trendData[view].labels;
  trendChart.data.datasets[0].data = trendData[view].newData;
  trendChart.data.datasets[1].data = trendData[view].renewalData;
  trendChart.update();

  document.getElementById('btnMonthly').classList.toggle('active', view === 'monthly');
  document.getElementById('btnQuarterly').classList.toggle('active', view === 'quarterly');
}

const sectCanvas = document.getElementById('sectChart');
if (sectCanvas) {
  new Chart(sectCanvas, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($topSects)) ?>,
    datasets: [{
      data: <?= json_encode(array_values($topSects)) ?>,
      backgroundColor: '#0B3D7A',
      borderRadius: 5,
      maxBarThickness: 34,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, grid: { color: '#EEF1F5' }, ticks: { precision: 0 } },
      y: { grid: { display: false } }
    }
  }
  });
}

new Chart(document.getElementById('provinceChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($provinceCounts)) ?>,
    datasets: [{
      data: <?= json_encode(array_values($provinceCounts)) ?>,
      backgroundColor: ['#0B3D7A', '#B8873A', '#A3202F', '#2F7D5A'],
      borderRadius: 5,
      maxBarThickness: 40,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: '#EEF1F5' }, ticks: { precision: 0 } },
      x: { grid: { display: false }, ticks: { font: { size: 10 } } }
    }
  }
});

new Chart(document.getElementById('sexChart'), {
  type: 'doughnut',
  data: {
    labels: ['Male', 'Female'],
    datasets: [{
      data: [<?= $sexCounts['Male'] ?>, <?= $sexCounts['Female'] ?>],
      backgroundColor: ['#0B3D7A', '#B8873A'],
      borderWidth: 0,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: { legend: { display: false } }
  }
});
</script>

</body>
</html>