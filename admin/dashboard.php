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
$allRecords = $allStmt->fetchAll(PDO::FETCH_ASSOC);

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
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $monthlyTrend[$m] = ['New' => 0, 'Renewal' => 0];
}

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

    if (!empty($r['filed'])) {
        $m = substr($r['filed'], 0, 7);
        if (isset($monthlyTrend[$m]) && isset($r['type'])) {
            $monthlyTrend[$m][$r['type']]++;
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
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="chart.umd.js"></script>
<style>
:root {
  --psa-blue: #0B3D7A;
  --psa-blue-deep: #082C58;
  --psa-blue-tint: #E8EEF6;
  --psa-gold: #B8873A;
  --psa-gold-tint: #F6EFE1;
  --psa-red: #A3202F;
  --psa-red-tint: #F5E6E7;
  --psa-green: #2F7D5A;
  --psa-green-tint: #E6F0EA;
  --ink: #1B2530;
  --ink-soft: #5C6773;
  --ink-faint: #8A93A0;
  --line: #E3E7EE;
  --line-soft: #EEF1F5;
  --surface: #FFFFFF;
  --canvas: #F4F6F9;
}

* { font-variant-numeric: tabular-nums; }

body {
  font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: var(--ink);
  background-color: var(--canvas);
}

.dash-shell { width: 100%; max-width: 100%; }

.page-heading {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .5rem;
  margin-bottom: 1.25rem;
}
.page-heading h1 {
  font-size: 1.4rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  color: var(--ink);
  margin: 0;
}
.page-heading .as-of { font-size: .78rem; color: var(--ink-faint); }

.kpi-strip {
  background-color: var(--surface);
  border: 1px solid var(--line);
  border-radius: 10px;
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 1.25rem;
  overflow: hidden;
}
.kpi-cell {
  flex: 1 1 0;
  min-width: 150px;
  padding: 1rem 1.15rem;
  border-right: 1px solid var(--line-soft);
  display: flex;
  align-items: center;
  gap: .75rem;
}
.kpi-cell:last-child { border-right: none; }
.kpi-icon {
  width: 38px; height: 38px;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: .9rem;
  color: #fff;
  flex-shrink: 0;
}
.kpi-label { font-size: .7rem; color: var(--ink-soft); font-weight: 500; }
.kpi-value { font-size: 1.35rem; font-weight: 700; line-height: 1.15; color: var(--ink); }

.panel {
  background-color: var(--surface);
  border: 1px solid var(--line);
  border-radius: 10px;
  margin-bottom: 1.1rem;
  overflow: hidden;
}
.panel-head {
  padding: 1rem 1.2rem;
  border-bottom: 1px solid var(--line);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  flex-wrap: wrap;
}
.panel-head h2 { font-size: .95rem; font-weight: 600; color: var(--ink); margin: 0; }
.panel-head p { font-size: .76rem; color: var(--ink-faint); margin: .15rem 0 0; }
.panel-body { padding: 1.15rem 1.2rem; }

.legend-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.1rem;
  margin-top: .9rem;
}
.legend-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: var(--ink-soft); }
.legend-dot { width: 8px; height: 8px; border-radius: 999px; }

.stage-row {
  display: flex;
  align-items: center;
  gap: .9rem;
  padding: .55rem 0;
}
.stage-label { width: 190px; flex-shrink: 0; font-size: .78rem; color: var(--ink-soft); }
.stage-track { flex-grow: 1; height: 8px; background-color: var(--line-soft); border-radius: 999px; overflow: hidden; }
.stage-fill { height: 100%; border-radius: 999px; background-color: var(--psa-blue); }
.stage-value { width: 44px; text-align: right; font-size: .82rem; font-weight: 600; color: var(--ink); flex-shrink: 0; }

.recent-table { width: 100%; font-size: .78rem; border-collapse: collapse; }
.recent-table th {
  text-align: left;
  font-size: .68rem;
  font-weight: 500;
  color: var(--ink-faint);
  padding: .6rem 1.2rem;
  border-bottom: 1px solid var(--line);
}
.recent-table td { padding: .7rem 1.2rem; border-bottom: 1px solid var(--line-soft); vertical-align: middle; }
.recent-table tbody tr:last-child td { border-bottom: none; }
.recent-table tbody tr:hover td { background-color: var(--canvas); }

.type-flag { display: inline-flex; align-items: center; gap: .4rem; font-weight: 500; }
.type-dot { width: 7px; height: 7px; border-radius: 999px; }
.status-note { font-size: .78rem; color: var(--ink-soft); display: inline-flex; align-items: center; gap: .4rem; }

.link-quiet {
  font-size: .78rem;
  font-weight: 500;
  color: var(--psa-blue);
  text-decoration: none;
}
.link-quiet:hover { text-decoration: underline; }

@media (max-width: 767px) {
  .kpi-cell { flex: 1 1 50%; border-bottom: 1px solid var(--line-soft); }
  .kpi-cell:nth-child(2n) { border-right: none; }
  .stage-label { width: 130px; font-size: .72rem; }
}
</style>
</head>
<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="min-vh-100">

  <main class="p-3 p-md-4">
  <div class="dash-shell">

    <div class="page-heading">
      <h1>Dashboard</h1>
      <span class="as-of">As of <?= date('F j, Y') ?></span>
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
              <p>New vs. renewal applications filed, last 6 months.</p>
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

<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
<script>
Chart.defaults.font = { family: 'IBM Plex Sans', size: 11 };
Chart.defaults.color = '#5C6773';

new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($monthLabels) ?>,
    datasets: [
      {
        label: 'New',
        data: <?= json_encode($monthNewData) ?>,
        borderColor: '#B8873A',
        backgroundColor: 'rgba(184,135,58,0.1)',
        fill: true,
        tension: .3,
        pointRadius: 3,
      },
      {
        label: 'Renewal',
        data: <?= json_encode($monthRenewalData) ?>,
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

new Chart(document.getElementById('sectChart'), {
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