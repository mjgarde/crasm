<?php
require_once '../config/session_check.php';
require_once '../config/database.php';

$adminUsername = $_SESSION['admin_username'] ?? 'Administrator';
$adminInitial  = strtoupper(substr($adminUsername, 0, 1));

$database = new Database();
$db = $database->connect();

$stmt = $db->query("SELECT * FROM authority_records WHERE approved IS NOT NULL AND approved <> '' ORDER BY approved DESC, no ASC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$years = [];
foreach ($records as $r) {
    $years[substr($r['approved'], 0, 4)] = true;
}
$years = array_keys($years);
rsort($years);
$latestYear = $years[0] ?? '';

$months = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRASM | DSOIS Reports</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/authority.css">
<style>
    .report-filter-bar {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: .5rem;
    }

    .report-filter-bar .search-wrap {
        flex: 1 1 auto;
        min-width: 0;
    }

    .report-filter-bar .report-select {
        flex: 0 0 160px;
        width: 160px;
    }

    .report-filter-bar .report-year {
        flex: 0 0 120px;
        width: 120px;
    }

    .report-filter-bar .btn {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .report-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }

    .report-scroll {
        max-height: 70vh;
        overflow: auto;
    }

    @media (max-width: 767.98px) {
        .report-filter-bar {
            gap: .35rem;
        }

        .report-filter-bar .report-select,
        .report-filter-bar .report-year {
            flex: 1 1 0;
            width: auto;
            min-width: 0;
        }

        .report-filter-bar .search-wrap {
            flex: 1 1 0;
            min-width: 0;
        }

        .report-filter-bar .btn .btn-label {
            display: none;
        }

        .report-filter-bar input,
        .report-filter-bar select {
            font-size: 16px;
        }

        #paginationControls {
            flex-wrap: wrap;
            justify-content: center;
        }

        .report-footer {
            justify-content: center !important;
            text-align: center;
        }
    }
</style>
</head>

<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="bg-light min-vh-100">

    <main class="p-3 p-md-4">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="report-filter-bar">
                    <div class="search-wrap">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white px-2"><i class="fa-solid fa-magnifying-glass text-muted" style="font-size:10px;"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search CRASM# or Name...">
                        </div>
                    </div>

                    <select id="reportMonth" class="form-select form-select-sm report-select">
                        <option value="">All Months</option>
                        <?php foreach ($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="reportYear" class="form-select form-select-sm report-year">
                        <option value="">All Years</option>
                        <?php foreach ($years as $year): ?>
                        <option value="<?php echo htmlspecialchars($year); ?>"<?php echo $year === $latestYear ? ' selected' : ''; ?>><?php echo htmlspecialchars($year); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="button" id="exportWordBtn" class="btn btn-sm text-white" style="background-color:#2b5797;">
                        <i class="fa-solid fa-file-word me-1"></i><span class="btn-label"> Convert to Word</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-1">
                <h6 class="fw-bold mb-0" id="reportTitle">DSOIS Reports</h6>
                <span class="text-muted small" id="reportTotal">Total: 0</span>
            </div>
            <div class="card-body p-0">
                <div class="report-scroll">
                    <table class="table table-hover align-middle mb-0 report-table" id="reportTable" style="font-size:12px;">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>CRASM#</th>
                                <th>Name of SO</th>
                                <th>Province</th>
                                <th>Type</th>
                                <th>Religious Sect</th>
                                <th>Sex</th>
                                <th>Church Address</th>
                                <th>Contact No.</th>
                                <th>Position</th>
                                <th>Approved</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $record): ?>
                            <tr
                                class="report-row"
                                data-year="<?php echo htmlspecialchars(substr($record['approved'], 0, 4)); ?>"
                                data-month="<?php echo htmlspecialchars(substr($record['approved'], 5, 2)); ?>"
                                data-date="<?php echo htmlspecialchars($record['approved']); ?>"
                            >
                                <td><?php echo htmlspecialchars($record['no']); ?></td>
                                <td><?php echo htmlspecialchars($record['crasm_no']); ?></td>
                                <td><?php echo htmlspecialchars($record['name_of_so']); ?></td>
                                <td><?php echo htmlspecialchars($record['provinces']); ?></td>
                                <td><?php echo htmlspecialchars($record['type']); ?></td>
                                <td><?php echo htmlspecialchars($record['religious_sect']); ?></td>
                                <td><?php echo htmlspecialchars($record['sex']); ?></td>
                                <td><?php echo htmlspecialchars($record['church_address']); ?></td>
                                <td><?php echo htmlspecialchars($record['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($record['position']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($record['approved']))); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="report-empty" style="display:none;">
                                <td colspan="11" class="text-center text-muted py-4">No records found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 report-footer">
                <span class="text-muted small" id="paginationInfo">Showing records</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                </nav>
            </div>
        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const rowsPerPage = 100;
    const monthNames = {
        '01': 'January', '02': 'February', '03': 'March', '04': 'April',
        '05': 'May', '06': 'June', '07': 'July', '08': 'August',
        '09': 'September', '10': 'October', '11': 'November', '12': 'December'
    };

    const searchInput = document.getElementById('searchInput');
    const reportMonth = document.getElementById('reportMonth');
    const reportYear = document.getElementById('reportYear');
    const exportWordBtn = document.getElementById('exportWordBtn');
    const reportTitle = document.getElementById('reportTitle');
    const reportTotal = document.getElementById('reportTotal');
    const paginationControls = document.getElementById('paginationControls');
    const paginationInfo = document.getElementById('paginationInfo');
    const emptyRow = document.querySelector('#reportTable tr.report-empty');
    const allRows = Array.from(document.querySelectorAll('#reportTable tr.report-row'));
    let currentPage = 1;
    let searchTimer = null;

    function getFilteredRows() {
        const term = searchInput.value.trim().toLowerCase();
        const month = reportMonth.value;
        const year = reportYear.value;

        return allRows.filter(function (row) {
            const cells = row.querySelectorAll('td');
            const crasm = cells[1].textContent.toLowerCase();
            const name = cells[2].textContent.toLowerCase();
            const matchesSearch = !term || crasm.includes(term) || name.includes(term);
            const matchesMonth = !month || row.dataset.month === month;
            const matchesYear = !year || row.dataset.year === year;
            return matchesSearch && matchesMonth && matchesYear;
        });
    }

    function updateTitle() {
        const month = reportMonth.value;
        const year = reportYear.value;
        let label = 'DSOIS Reports';
        if (month && year) {
            label += ' - ' + monthNames[month] + ' ' + year;
        } else if (month) {
            label += ' - ' + monthNames[month];
        } else if (year) {
            label += ' - ' + year;
        }
        reportTitle.textContent = label;
    }

    function renderTable() {
        const filtered = getFilteredRows();
        const totalPages = Math.max(1, Math.ceil(filtered.length / rowsPerPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        allRows.forEach(function (row) {
            row.style.display = 'none';
        });

        const start = (currentPage - 1) * rowsPerPage;
        filtered.slice(start, start + rowsPerPage).forEach(function (row) {
            row.style.display = '';
        });

        emptyRow.style.display = filtered.length === 0 ? '' : 'none';

        if (filtered.length === 0) {
            paginationInfo.textContent = 'No records found';
        } else {
            paginationInfo.textContent = 'Showing ' + (start + 1) + ' to ' + Math.min(start + rowsPerPage, filtered.length) + ' of ' + filtered.length + ' records';
        }

        reportTotal.textContent = 'Total: ' + filtered.length;
        updateTitle();
        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationControls.innerHTML = '';

        function addItem(label, page, disabled, active) {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            li.innerHTML = '<a class="page-link" href="#">' + label + '</a>';
            li.addEventListener('click', function (e) {
                e.preventDefault();
                if (disabled || page === currentPage) return;
                currentPage = page;
                renderTable();
            });
            paginationControls.appendChild(li);
        }

        addItem('Prev', currentPage - 1, currentPage === 1, false);

        const start = Math.max(1, currentPage - 1);
        const end = Math.min(totalPages, currentPage + 1);

        if (start > 1) {
            addItem('1', 1, false, false);
            if (start > 2) addItem('&hellip;', 0, true, false);
        }

        for (let i = start; i <= end; i++) {
            addItem(String(i), i, false, i === currentPage);
        }

        if (end < totalPages) {
            if (end < totalPages - 1) addItem('&hellip;', 0, true, false);
            addItem(String(totalPages), totalPages, false, false);
        }

        addItem('Next', currentPage + 1, currentPage === totalPages, false);
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            currentPage = 1;
            renderTable();
        }, 200);
    });

    [reportMonth, reportYear].forEach(function (control) {
        control.addEventListener('change', function () {
            currentPage = 1;
            renderTable();
        });
    });

    exportWordBtn.addEventListener('click', function () {
        const params = new URLSearchParams();
        if (reportMonth.value) params.set('month', reportMonth.value);
        if (reportYear.value) params.set('year', reportYear.value);
        const query = params.toString();
        window.location.href = '../actions/authority_export_word.php' + (query ? '?' + query : '');
    });

    renderTable();

});
</script>

</body>
</html>