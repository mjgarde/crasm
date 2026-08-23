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

$allStmt = $db->query("SELECT * FROM authority_records");
$allRecords = $allStmt->fetchAll(PDO::FETCH_ASSOC);

$totalRecords = count($allRecords);

$newCount = 0;
$renewalCount = 0;
$pendingCount = 0;

$sectCounts = [];

foreach ($allRecords as $r) {
    if ($r['type'] === 'New') {
        $newCount++;
    } elseif ($r['type'] === 'Renewal') {
        $renewalCount++;
    }

    if (!empty($r['filed']) && empty($r['approved'])) {
        $pendingCount++;
    }

    $sect = trim($r['religious_sect'] ?? '');
    if ($sect !== '') {
        $sectCounts[$sect] = ($sectCounts[$sect] ?? 0) + 1;
    }
}

arsort($sectCounts);
$topSects = array_slice($sectCounts, 0, 6, true);
$topSects = array_reverse($topSects, true);

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
<style>.kpi-card,.kpi-icon{align-items:center;display:flex}.kpi-card,.panel-card{background:var(--surface);box-shadow:0 1px 2px rgba(16,24,40,.04)}.kpi-label,.recent-table th{text-transform:uppercase;letter-spacing:.03em}.recent-table td,.recent-table th{padding:8px 10px;border-bottom:1px solid var(--line)}.panel-card-header,.recent-table td,.recent-table th{border-bottom:1px solid var(--line)}:root{--psa-blue:#003883;--psa-blue-dark:#002a63;--psa-red:#a3202f;--psa-gold:#d4a017;--ink:#1c2430;--ink-soft:#5b6472;--line:#e3e7ee;--surface:#ffffff;--canvas:#f4f6f9}body{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ink);background-color:var(--canvas)}.dash-shell{width:100%;max-width:100%}.kpi-card{border:1px solid var(--line);border-radius:10px;padding:16px 18px;gap:14px;height:100%}.kpi-icon{width:42px;height:42px;border-radius:10px;justify-content:center;font-size:16px;flex-shrink:0;color:#fff}.kpi-label{font-size:11px;color:var(--ink-soft);font-weight:600;margin-bottom:2px}.kpi-value,.panel-card-header h6{font-weight:700;color:var(--ink)}.kpi-value{font-size:20px;line-height:1.1}.kpi-sub{font-size:10.5px;color:var(--ink-soft);margin-top:2px}.panel-card{border:1px solid var(--line);border-radius:10px;margin-bottom:18px;overflow:hidden}.panel-card-header{padding:12px 16px;display:flex;align-items:center;gap:10px}.panel-card-header .bar{width:4px;height:16px;border-radius:2px;background:var(--psa-blue)}.panel-card-header h6{font-size:13.5px;margin:0}.panel-card-body{padding:16px}.recent-table{width:100%;font-size:11.5px;border-collapse:collapse}.recent-table th{background:#f8f9fb;color:var(--ink-soft);font-weight:600;font-size:10px;text-align:left}.recent-table td{vertical-align:middle}.recent-table tbody tr:hover td{background-color:#f5f8fc}

.badge-type-new,
.badge-type-renewal {
    font-weight: 600;
    font-size: 10px;
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
            <hr>
        </div>

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

        <div class="row g-3">
            <div class="col-12">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <span class="bar"></span>
                        <h6>Top Religious Sects</h6>
                    </div>
                    <div class="panel-card-body">
                        <?php if (empty($topSects)): ?>
                            <p class="text-muted small mb-0">No data available.</p>
                        <?php else: ?>
                            <?php
                                $barData = array_reverse($topSects, true);
                                $svgLabels = array_keys($barData);
                                $svgValues = array_values($barData);
                                $svgN = count($svgValues);
                                $svgMaxVal = max($svgValues);

                                $svgYMax = max(5, $svgMaxVal + max(1, (int) ceil($svgMaxVal * 0.25)));
                                $svgStep = max(1, (int) ceil($svgYMax / 5));
                                $svgYMax = $svgStep * 5;

                                $svgW = 1200;
                                $svgH = 420;
                                $padL = 50;
                                $padR = 30;
                                $padT = 30;
                                $padB = 90;
                                $plotW = $svgW - $padL - $padR;
                                $plotH = $svgH - $padT - $padB;

                                $barColors = ['#003883', '#2bb3a3', '#d4a017', '#a3202f', '#6a4c93', '#1b7f4d'];

                                $slot = $plotW / $svgN;
                                $barWidth = min(72, $slot * 0.5);

                                $bars = [];
                                for ($i = 0; $i < $svgN; $i++) {
                                    $centerX = $padL + ($slot * $i) + ($slot / 2);
                                    $barH = ($svgValues[$i] / $svgYMax) * $plotH;
                                    $bars[] = [
                                        'x' => round($centerX - ($barWidth / 2), 1),
                                        'cx' => round($centerX, 1),
                                        'y' => round($padT + $plotH - $barH, 1),
                                        'h' => round($barH, 1),
                                        'label' => $svgLabels[$i],
                                        'value' => $svgValues[$i],
                                        'color' => $barColors[$i % count($barColors)],
                                    ];
                                }

                                $shortLabel = function ($label, $limit = 12) {
                                    return mb_strlen($label) > $limit ? mb_substr($label, 0, $limit - 1) . '…' : $label;
                                };
                            ?>
                            <div style="width:100%;overflow-x:auto;">
                                <div style="position:relative;min-width:620px;">
                                    <svg id="sectSvg" viewBox="0 0 <?php echo $svgW; ?> <?php echo $svgH; ?>"
                                         style="width:100%;height:auto;display:block;font-family:'Inter',sans-serif;">

                                        <?php for ($g = 0; $g <= $svgYMax; $g += $svgStep): ?>
                                            <?php $gy = $padT + $plotH - (($g / $svgYMax) * $plotH); ?>
                                            <line x1="<?php echo $padL; ?>" y1="<?php echo $gy; ?>" x2="<?php echo $svgW - $padR; ?>" y2="<?php echo $gy; ?>" stroke="#e3e7ee" stroke-width="1" stroke-dasharray="<?php echo $g === 0 ? '0' : '3,4'; ?>"/>
                                            <text x="<?php echo $padL - 10; ?>" y="<?php echo $gy + 4; ?>" text-anchor="end" font-size="12" fill="#5b6472"><?php echo $g; ?></text>
                                        <?php endfor; ?>

                                        <line x1="<?php echo $padL; ?>" y1="<?php echo $padT; ?>" x2="<?php echo $padL; ?>" y2="<?php echo $padT + $plotH; ?>" stroke="#8a94a3" stroke-width="1.5"/>
                                        <line x1="<?php echo $padL; ?>" y1="<?php echo $padT + $plotH; ?>" x2="<?php echo $svgW - $padR; ?>" y2="<?php echo $padT + $plotH; ?>" stroke="#8a94a3" stroke-width="1.5"/>

                                        <?php foreach ($bars as $b): ?>
                                            <rect class="sect-bar" data-label="<?php echo htmlspecialchars($b['label']); ?>" data-value="<?php echo $b['value']; ?>"
                                                  x="<?php echo $b['x']; ?>" y="<?php echo $b['y']; ?>"
                                                  width="<?php echo round($barWidth, 1); ?>" height="<?php echo $b['h']; ?>"
                                                  rx="4" fill="<?php echo $b['color']; ?>" style="cursor:pointer;transition:opacity .15s;"/>
                                            <text x="<?php echo $b['cx']; ?>" y="<?php echo $b['y'] - 10; ?>" text-anchor="middle" font-size="12.5" font-weight="700" fill="#1c2430"><?php echo $b['value']; ?></text>
                                            <text x="<?php echo $b['cx']; ?>" y="<?php echo $padT + $plotH + 22; ?>" text-anchor="end" font-size="11.5" fill="#5b6472"
                                                  transform="rotate(-40 <?php echo $b['cx']; ?>,<?php echo $padT + $plotH + 22; ?>)">
                                                <?php echo htmlspecialchars($shortLabel($b['label'])); ?>
                                            </text>
                                        <?php endforeach; ?>
                                    </svg>

                                    <div id="sectTooltip" style="position:absolute;display:none;pointer-events:none;background:#1c2430;color:#fff;padding:8px 12px;border-radius:8px;font-size:12px;box-shadow:0 6px 16px rgba(0,0,0,0.25);z-index:20;white-space:nowrap;">
                                        <div id="sectTooltipLabel" style="font-weight:700;margin-bottom:2px;"></div>
                                        <div id="sectTooltipValue" style="color:#9fd6ff;"></div>
                                    </div>
                                </div>
                            </div>

                            <script>
                            (function () {
                                const svg = document.getElementById('sectSvg');
                                const tooltip = document.getElementById('sectTooltip');
                                const tLabel = document.getElementById('sectTooltipLabel');
                                const tValue = document.getElementById('sectTooltipValue');

                                function showTooltip(bar) {
                                    const label = bar.dataset.label;
                                    const value = bar.dataset.value;
                                    tLabel.textContent = label;
                                    tValue.textContent = value + ' record(s)';
                                    tooltip.style.display = 'block';

                                    const svgRect = svg.getBoundingClientRect();
                                    const scale = svgRect.width / svg.viewBox.baseVal.width;
                                    const bx = parseFloat(bar.getAttribute('x')) * scale;
                                    const by = parseFloat(bar.getAttribute('y')) * scale;
                                    const bw = parseFloat(bar.getAttribute('width')) * scale;

                                    tooltip.style.left = Math.min(bx + (bw / 2) - 60, svgRect.width - 150) + 'px';
                                    tooltip.style.top = Math.max(by - 50, 0) + 'px';

                                    bar.style.opacity = '0.75';
                                }

                                function hideTooltip(bar) {
                                    tooltip.style.display = 'none';
                                    if (bar) bar.style.opacity = '1';
                                }

                                document.querySelectorAll('.sect-bar').forEach(bar => {
                                    bar.addEventListener('mouseenter', () => showTooltip(bar));
                                    bar.addEventListener('mouseleave', () => hideTooltip(bar));
                                    bar.addEventListener('click', () => showTooltip(bar));
                                });

                                svg.addEventListener('mouseleave', () => hideTooltip());
                            })();
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
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
        </div>

    </div>
    </main>

</div>

<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>