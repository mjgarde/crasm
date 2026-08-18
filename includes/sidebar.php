<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<aside
    class="offcanvas-lg offcanvas-start bg-white border-end d-flex flex-column min-vh-100 flex-shrink-0 sidebar"
    style="width:190px;min-width:190px;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;"
    tabindex="-1"
    id="sidebarOffcanvas"
>

    <div class="d-flex align-items-center justify-content-between border-bottom p-3 sidebar-header position-relative">

        <div class="d-flex align-items-center">

            <img
                src="../assets/img/logo.webp"
                alt="Seal"
                style="width:32px;height:32px;object-fit:contain;flex-shrink:0;"
                class="me-2"
            >

            <div class="sidebar-text">
                <div
                    class="fw-bold"
                    style="font-size:12px;white-space:nowrap;"
                >
                    CRASM
                </div>

                <div
                    class="text-muted"
                    style="font-size:9px;white-space:nowrap;"
                >
                    Authority Records System
                </div>
            </div>

        </div>

        <button
            type="button"
            class="btn-close d-lg-none"
            data-bs-dismiss="offcanvas"
            data-bs-target="#sidebarOffcanvas"
            aria-label="Close"
        ></button>

        <button
            type="button"
            class="btn btn-sm btn-light border-0 text-muted d-none d-lg-flex align-items-center justify-content-center position-absolute"
            id="sidebarCollapseToggle"
            title="Collapse sidebar"
            style="font-size:11px;line-height:1;width:20px;height:20px;padding:0;top:6px;right:6px;border-radius:50%;"
        >
            <i class="fa-solid fa-angles-left"></i>
        </button>

    </div>


    <nav class="flex-grow-1 overflow-auto py-2">

        <div
            class="text-uppercase text-secondary fw-semibold px-3 pt-2 pb-1 sidebar-text"
            style="font-size:.62rem;letter-spacing:.05em;"
        >
            Main
        </div>

        <div class="list-group list-group-flush">

            <a
                href="dashboard.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1"
                style="font-size:12px;"
                title="Dashboard"
            >
                <i
                    class="fa-solid fa-gauge-high"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

        </div>


        <div
            class="text-uppercase text-secondary fw-semibold px-3 pt-3 pb-1 sidebar-text"
            style="font-size:.62rem;letter-spacing:.05em;"
        >
            Records
        </div>

        <div class="list-group list-group-flush">

            <a
                href="authority.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1"
                style="font-size:12px;"
                title="Authority"
            >
                <i
                    class="fa-solid fa-user-shield"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Authority</span>
            </a>

            <a
                href="reports.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1"
                style="font-size:12px;"
                title="Reports"
            >
                <i
                    class="fa-solid fa-chart-column"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Reports</span>
            </a>

        </div>


        <div
            class="text-uppercase text-secondary fw-semibold px-3 pt-3 pb-1 sidebar-text"
            style="font-size:.62rem;letter-spacing:.05em;"
        >
            System
        </div>

        <div class="list-group list-group-flush">

            <a
                href="backup.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1"
                style="font-size:12px;"
                title="Backup &amp; Recovery"
            >
                <i
                    class="fa-solid fa-database"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Backup &amp; Recovery</span>
            </a>

            <a
                href="activity_logs.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1"
                style="font-size:12px;"
                title="Activity Logs"
            >
                <i
                    class="fa-solid fa-clipboard-list"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Activity Logs</span>
            </a>

            <a
                href="settings.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1"
                style="font-size:12px;"
                title="Settings"
            >
                <i
                    class="fa-solid fa-gear"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Settings</span>
            </a>

            <a
                href="../logout.php"
                class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-1 px-3 py-1 text-danger"
                style="font-size:12px;"
                title="Log Out"
            >
                <i
                    class="fa-solid fa-right-from-bracket"
                    style="width:16px;font-size:12px;flex-shrink:0;"
                ></i>
                <span class="sidebar-text">Log Out</span>
            </a>

        </div>

    </nav>

</aside>

<style>
    .sidebar {
        transition: width .2s ease, min-width .2s ease;
    }
    .sidebar.sidebar-collapsed {
        width: 60px !important;
        min-width: 60px !important;
    }
    .sidebar.sidebar-collapsed .sidebar-text {
        display: none;
    }
    .sidebar.sidebar-collapsed .sidebar-header {
        justify-content: center;
        padding: .5rem !important;
    }
    .sidebar.sidebar-collapsed .sidebar-header .me-2 {
        margin-right: 0 !important;
    }
    .sidebar.sidebar-collapsed #sidebarCollapseToggle {
        top: 2px;
        right: 2px;
    }
    .sidebar.sidebar-collapsed .list-group-item {
        justify-content: center;
        padding-left: .5rem !important;
        padding-right: .5rem !important;
    }
    .sidebar.sidebar-collapsed #sidebarCollapseToggle i {
        transform: rotate(180deg);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('sidebarCollapseToggle');
    const STORAGE_KEY = 'crasmSidebarCollapsed';

    if (!sidebar || !toggleBtn) return;

    function applyState(collapsed) {
        sidebar.classList.toggle('sidebar-collapsed', collapsed);
        toggleBtn.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
    }

    const savedState = localStorage.getItem(STORAGE_KEY) === '1';
    applyState(savedState);

    toggleBtn.addEventListener('click', function () {
        const collapsed = !sidebar.classList.contains('sidebar-collapsed');
        applyState(collapsed);
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    });
});
</script>