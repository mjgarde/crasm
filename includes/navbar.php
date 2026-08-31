<?php
$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminInitial  = strtoupper(substr($adminName, 0, 1));
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<nav
    class="navbar navbar-expand-lg crasm-navbar"
    style="font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background-color:#002d62;"
>
    <div class="container-fluid px-3">

        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img
                src="../assets/img/logo.png"
                alt="Seal"
                style="width:32px;height:32px;object-fit:contain;flex-shrink:0;"
                class="me-2"
            >
            <div>
                <div class="fw-bold text-white" style="font-size:12px;white-space:nowrap;line-height:1.1;">
                    PHILIPPINE STATISTICS AUTHORITY XII
                </div>
                <div style="font-size:9px;white-space:nowrap;color:rgba(255,255,255,.7);">
                    Certificate of Registration of Authority to Solemnize Marriage
                </div>
            </div>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#crasmNavbarCollapse"
            aria-controls="crasmNavbarCollapse"
            aria-expanded="false"
            aria-label="Toggle navigation"
            style="font-size:12px;padding:.25rem .5rem;border-color:rgba(255,255,255,.5);"
        >
            <span class="navbar-toggler-icon" style="filter:invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="crasmNavbarCollapse">

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0" style="font-size:12px;">

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="dashboard.php" title="Dashboard">
                        <i class="fa-solid fa-gauge-high" style="width:16px;font-size:12px;flex-shrink:0;"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item d-flex align-items-center">
                    <span style="color:rgba(255,255,255,.3);">|</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="authority.php" title="Authority">
                        <i class="fa-solid fa-user-shield" style="width:16px;font-size:12px;flex-shrink:0;"></i>
                        Authority
                    </a>
                </li>

                <li class="nav-item d-flex align-items-center">
                    <span style="color:rgba(255,255,255,.3);">|</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="reports.php" title="Reports">
                        <i class="fa-solid fa-chart-column" style="width:16px;font-size:12px;flex-shrink:0;"></i>
                        Reports
                    </a>
                </li>

                <li class="nav-item d-flex align-items-center">
                    <span style="color:rgba(255,255,255,.3);">|</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="backup.php" title="Backup &amp; Recovery">
                        <i class="fa-solid fa-database" style="width:16px;font-size:12px;flex-shrink:0;"></i>
                        Backup &amp; Recovery
                    </a>
                </li>

            </ul>

            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a
                        class="nav-link dropdown-toggle d-flex align-items-center justify-content-center"
                        href="#"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="font-size:12px;"
                    >
                        <i class="fa-solid fa-circle-user" style="font-size:20px;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0 overflow-hidden" style="font-size:12px;min-width:220px;">
                        <li class="d-flex align-items-center gap-2 px-3 py-3" style="background-color:#f8f9fa;">
                            <span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-semibold flex-shrink-0"
                                style="width:36px;height:36px;font-size:14px;background-color:#002d62;"
                            >
                                <?= htmlspecialchars($adminInitial) ?>
                            </span>
                            <div class="text-truncate">
                                <div class="text-muted" style="font-size:10px;">Signed in as</div>
                                <div class="fw-semibold text-truncate" style="font-size:13px;color:#111;">
                                    <?= htmlspecialchars($adminName) ?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2 px-3 py-2" href="../logout.php">
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
        color: rgba(255,255,255,.75);
        padding: .5rem .65rem;
        transition: color .15s ease;
    }
    .crasm-navbar .nav-link:hover {
        color: #ffffff;
    }
    .crasm-navbar .dropdown-toggle::after {
        display: none;
    }
</style>