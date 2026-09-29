<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName    = $_SESSION['user_name'] ?? ($_SESSION['user_username'] ?? 'User');
$userInitial = strtoupper(mb_substr($userName, 0, 1));

$docRoot = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$appRoot = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$base    = rtrim(str_replace($docRoot, '', $appRoot), '/');

$currentPage = basename($_SERVER['SCRIPT_NAME']);

$navItems = [
    ['file' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'fa-gauge-high'],
    ['file' => 'authority.php', 'label' => 'Authority', 'icon' => 'fa-user-shield'],
    ['file' => 'reports.php',   'label' => 'Reports',   'icon' => 'fa-chart-column'],
];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<nav class="navbar navbar-expand-lg crasm-navbar" style="font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background-color:#002d62;">
    <div class="container-fluid px-3">

        <a class="navbar-brand d-flex align-items-center" href="<?= htmlspecialchars($base) ?>/user/dashboard.php">
            <img src="<?= htmlspecialchars($base) ?>/assets/img/logo.png" alt="Seal" class="me-2" style="width:32px;height:32px;object-fit:contain;flex-shrink:0;">
            <div class="min-w-0">
                <div class="fw-bold text-white brand-title">PHILIPPINE STATISTICS AUTHORITY XII</div>
                <div class="brand-sub">Certificate of Registration of Authority to Solemnize Marriage</div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#crasmUserNavbarCollapse" aria-controls="crasmUserNavbarCollapse" aria-expanded="false" aria-label="Toggle navigation" style="font-size:12px;padding:.25rem .5rem;border-color:rgba(255,255,255,.5);">
            <span class="navbar-toggler-icon" style="filter:invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="crasmUserNavbarCollapse">

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0" style="font-size:12px;">
                <?php foreach ($navItems as $i => $item): ?>
                    <?php if ($i > 0): ?>
                    <li class="nav-item d-none d-lg-flex align-items-center">
                        <span style="color:rgba(255,255,255,.3);">|</span>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1<?= $currentPage === $item['file'] ? ' is-current' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/user/<?= $item['file'] ?>"
                           title="<?= htmlspecialchars($item['label']) ?>"
                           <?= $currentPage === $item['file'] ? 'aria-current="page"' : '' ?>>
                            <i class="fa-solid <?= $item['icon'] ?>" style="width:16px;font-size:12px;flex-shrink:0;"></i>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:12px;">
                        <i class="fa-solid fa-circle-user" style="font-size:20px;"></i>
                        <span class="d-lg-none">Account</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0 overflow-hidden" style="font-size:12px;min-width:220px;">
                        <li class="d-flex align-items-center gap-2 px-3 py-3" style="background-color:#f8f9fa;">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-semibold flex-shrink-0" style="width:36px;height:36px;font-size:14px;background-color:#002d62;">
                                <?= htmlspecialchars($userInitial) ?>
                            </span>
                            <div class="text-truncate">
                                <div class="text-muted" style="font-size:10px;">Signed in as</div>
                                <div class="fw-semibold text-truncate" style="font-size:13px;color:#111;">
                                    <?= htmlspecialchars($userName) ?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2 px-3 py-2" href="<?= htmlspecialchars($base) ?>/logout.php">
                                <i class="fa-solid fa-right-from-bracket"></i> Log Out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>

    </div>
</nav>

<div style="height:4px;background-color:#002d62;"></div>
<div style="height:4px;background-color:#d4a017;"></div>
<div style="height:4px;background-color:#a3202f;"></div>

<style>
    .crasm-navbar .nav-link {
        color: rgba(255, 255, 255, .75);
        padding: .5rem .65rem;
        transition: color .15s ease;
    }

    .crasm-navbar .nav-link:hover {
        color: #ffffff;
    }

    .crasm-navbar .dropdown-toggle::after {
        display: none;
    }

    .crasm-navbar .brand-title {
        font-size: 12px;
        line-height: 1.1;
        white-space: nowrap;
    }

    .crasm-navbar .brand-sub {
        font-size: 9px;
        white-space: nowrap;
        color: rgba(255, 255, 255, .7);
    }

    @media (max-width: 991.98px) {
        .crasm-navbar .navbar-brand {
            min-width: 0;
            max-width: calc(100% - 60px);
            margin-right: 0;
        }

        .crasm-navbar .navbar-brand .min-w-0 {
            min-width: 0;
        }

        .crasm-navbar .brand-sub {
            display: none;
        }

        .crasm-navbar .brand-title {
            font-size: 11px;
            line-height: 1.15;
            white-space: normal;
        }

        .crasm-navbar .navbar-collapse {
            margin-top: .5rem;
        }

        .crasm-navbar .collapsing {
            transition: none;
        }

        .crasm-navbar .navbar-nav .nav-link {
            font-size: 14px;
            padding: .7rem .25rem;
            border-top: 1px solid rgba(255, 255, 255, .12);
            transition: none;
        }

        .crasm-navbar .navbar-nav .nav-link i {
            font-size: 14px;
        }

        .crasm-navbar .navbar-nav .nav-link.is-current {
            color: #fff;
            font-weight: 600;
        }
    }
</style>