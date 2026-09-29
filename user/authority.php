<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$userUsername = $_SESSION['user_username'] ?? 'User';
$userInitial  = strtoupper(substr($userUsername, 0, 1));

$database = new Database();
$db = $database->connect();

$provinces = [
    'Cotabato',
    'Sarangani',
    'South Cotabato',
    'Sultan Kudarat',
];

$stmt = $db->query("SELECT * FROM authority_records ORDER BY no ASC");
$authorityRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sectStmt = $db->query("SELECT DISTINCT religious_sect FROM authority_records WHERE religious_sect IS NOT NULL AND religious_sect <> '' ORDER BY religious_sect ASC");
$religiousSects = $sectStmt->fetchAll(PDO::FETCH_COLUMN);

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
<title>CRASM | Authority</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/authority.css">
</head>

<body>

<?php require __DIR__ . '/../includes/user_navbar.php'; ?>

<div class="bg-light min-vh-100">

    <main class="p-3 p-md-4">

        <div class="card border-0 shadow-sm mb-3 filter-card">
            <div class="card-body py-2">

                <div class="filter-bar">
                    <div class="search-wrap">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white px-2"><i class="fa-solid fa-magnifying-glass text-muted" style="font-size:10px;"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search CRASM# or Name...">
                        </div>
                    </div>

                    <button type="button" id="filterToggleBtn" class="btn btn-sm btn-outline-secondary filter-toggle-btn">
                        <i class="fa-solid fa-filter me-1"></i> Filters <i class="fa-solid fa-chevron-down ms-1" style="font-size:10px;"></i>
                        <span class="filter-count" id="filterCountBadge" style="display:none;">0</span>
                    </button>

                    <select id="filterSort" class="form-select form-select-sm filter-sort-select">
                        <option value="newest">Newest to Oldest</option>
                        <option value="oldest">Oldest to Newest</option>
                    </select>

                    <button type="button" id="exportWordBtn" class="btn btn-sm text-white" style="background-color:#2b5797;white-space:nowrap;">
                        <i class="fa-solid fa-file-word me-1"></i> Word
                    </button>
                </div>

                <div class="filter-panel" id="filterPanel">
                    <div class="filter-panel-header">
                        <h6>Refine Results</h6>
                        <button type="button" class="filter-panel-close" id="filterPanelCloseBtn">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="filter-grid">

                        <div>
                            <label class="filter-label" for="filterProvince">Province</label>
                            <select id="filterProvince" class="form-select form-select-sm">
                                <option value="">All Provinces</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="filterSect">Religious Sect</label>
                            <select id="filterSect" class="form-select form-select-sm">
                                <option value="">All Sects</option>
                                <?php foreach ($religiousSects as $sect): ?>
                                <option value="<?php echo htmlspecialchars($sect); ?>"><?php echo htmlspecialchars($sect); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="filterType">Type</label>
                            <select id="filterType" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="New">New</option>
                                <option value="Renewal">Renewal</option>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="filterSex">Sex</label>
                            <select id="filterSex" class="form-select form-select-sm">
                                <option value="">All Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="filterMonth">Month</label>
                            <select id="filterMonth" class="form-select form-select-sm">
                                <option value="">All Months</option>
                                <?php foreach ($months as $num => $name): ?>
                                <option value="<?php echo $num; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label" for="filterYear">Year</label>
                            <select id="filterYear" class="form-select form-select-sm">
                                <option value="">All Years</option>
                                <?php
                                $years = [];
                                foreach ($authorityRecords as $r) {
                                    if (!empty($r['approved'])) {
                                        $years[substr($r['approved'], 0, 4)] = true;
                                    }
                                }
                                krsort($years);
                                foreach (array_keys($years) as $year):
                                ?>
                                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="grid-column: span 2;">
                            <label class="filter-label">Date Range</label>
                            <div class="date-range-group">
                                <input type="date" id="filterDateFrom" class="form-control form-control-sm" title="From date">
                                <span>to</span>
                                <input type="date" id="filterDateTo" class="form-control form-control-sm" title="To date">
                                <button type="button" id="clearDateRangeBtn" class="btn btn-sm btn-outline-secondary" title="Clear date range" style="display:none;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="filter-panel-footer">
                        <button type="button" id="resetFiltersBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset All
                        </button>
                        <button type="button" class="btn btn-sm text-white" style="background-color:#0a1f44;" id="applyFiltersBtn">
                            Apply
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-1">
                <h6 class="fw-bold mb-0">Authority Records</h6>
                <span class="text-muted small" id="authorityTotal">Total: 0</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" id="authorityTableWrapper">
                    <table class="table table-hover align-middle mb-0" id="authorityTable" style="font-size:11px;">
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
                            <?php foreach ($authorityRecords as $record): ?>
                            <tr
                                class="authority-row"
                                style="cursor:pointer;"
                                data-id="<?php echo htmlspecialchars($record['id']); ?>"
                                data-province="<?php echo htmlspecialchars($record['provinces']); ?>"
                                data-sect="<?php echo htmlspecialchars($record['religious_sect']); ?>"
                                data-type="<?php echo htmlspecialchars($record['type']); ?>"
                                data-sex="<?php echo htmlspecialchars($record['sex']); ?>"
                                data-year="<?php echo htmlspecialchars(substr($record['approved'] ?? '', 0, 4)); ?>"
                                data-month="<?php echo htmlspecialchars(substr($record['approved'] ?? '', 5, 2)); ?>"
                                data-date="<?php echo htmlspecialchars($record['approved'] ?? ''); ?>"
                                data-record="<?php echo htmlspecialchars(json_encode($record), ENT_QUOTES); ?>"
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
                                <td><?php echo $record['approved'] ? htmlspecialchars(date('M d, Y', strtotime($record['approved']))) : '<span class="text-muted">—</span>'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($authorityRecords)): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No authority records found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted small" id="paginationInfo">Showing records</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                </nav>
            </div>
        </div>

    </main>

</div>

<div class="modal fade" id="viewAuthorityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Authority Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewAuthorityBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const rowsPerPage = 100;
    const searchInput = document.getElementById('searchInput');
    const filterProvince = document.getElementById('filterProvince');
    const filterSect = document.getElementById('filterSect');
    const filterType = document.getElementById('filterType');
    const filterSex = document.getElementById('filterSex');
    const filterMonth = document.getElementById('filterMonth');
    const filterYear = document.getElementById('filterYear');
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');
    const clearDateRangeBtn = document.getElementById('clearDateRangeBtn');
    const filterSort = document.getElementById('filterSort');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const filterPanel = document.getElementById('filterPanel');
    const filterPanelCloseBtn = document.getElementById('filterPanelCloseBtn');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const filterCountBadge = document.getElementById('filterCountBadge');
    const tableBody = document.querySelector('#authorityTable tbody');
    const allRows = Array.from(tableBody.querySelectorAll('tr.authority-row'));
    const paginationControls = document.getElementById('paginationControls');
    const paginationInfo = document.getElementById('paginationInfo');
    const authorityTotal = document.getElementById('authorityTotal');
    let currentPage = 1;

    function isDateRangeActive() {
        return !!(filterDateFrom.value || filterDateTo.value);
    }

    function isMonthYearActive() {
        return !!(filterMonth.value || filterYear.value);
    }

    function syncFilterAvailability() {
        if (isDateRangeActive()) {
            filterMonth.disabled = true;
            filterYear.disabled = true;
            filterMonth.value = '';
            filterYear.value = '';
            clearDateRangeBtn.style.display = '';
        } else if (isMonthYearActive()) {
            filterDateFrom.disabled = true;
            filterDateTo.disabled = true;
            clearDateRangeBtn.style.display = 'none';
        } else {
            filterMonth.disabled = false;
            filterYear.disabled = false;
            filterDateFrom.disabled = false;
            filterDateTo.disabled = false;
            clearDateRangeBtn.style.display = 'none';
        }
    }

    clearDateRangeBtn.addEventListener('click', function () {
        filterDateFrom.value = '';
        filterDateTo.value = '';
        syncFilterAvailability();
        currentPage = 1;
        updateFilterCount();
        renderTable();
    });

    resetFiltersBtn.addEventListener('click', function () {
        searchInput.value = '';
        filterProvince.value = '';
        filterSect.value = '';
        filterType.value = '';
        filterSex.value = '';
        filterMonth.value = '';
        filterYear.value = '';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        filterSort.value = 'newest';
        syncFilterAvailability();
        currentPage = 1;
        updateFilterCount();
        renderTable();
    });

    function updateFilterCount() {
        let count = 0;
        if (filterProvince.value) count++;
        if (filterSect.value) count++;
        if (filterType.value) count++;
        if (filterSex.value) count++;
        if (filterMonth.value) count++;
        if (filterYear.value) count++;
        if (filterDateFrom.value) count++;
        if (filterDateTo.value) count++;

        if (count > 0) {
            filterCountBadge.textContent = count;
            filterCountBadge.style.display = '';
        } else {
            filterCountBadge.style.display = 'none';
        }
    }

    function openFilterPanel() {
        filterPanel.classList.add('show');
    }

    function closeFilterPanel() {
        filterPanel.classList.remove('show');
    }

    filterToggleBtn.addEventListener('click', function () {
        filterPanel.classList.contains('show') ? closeFilterPanel() : openFilterPanel();
    });

    filterPanelCloseBtn.addEventListener('click', closeFilterPanel);

    applyFiltersBtn.addEventListener('click', function () {
        closeFilterPanel();
    });

    document.addEventListener('click', function (e) {
        if (!filterPanel.contains(e.target) && !filterToggleBtn.contains(e.target) && filterPanel.classList.contains('show')) {
            closeFilterPanel();
        }
    });

    filterPanel.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    function getFilteredRows() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const province = filterProvince.value;
        const sect = filterSect.value;
        const type = filterType.value;
        const sex = filterSex.value;
        const month = filterMonth.value;
        const year = filterYear.value;
        const dateFrom = filterDateFrom.value;
        const dateTo = filterDateTo.value;
        const sort = filterSort.value;

        let filtered = allRows.filter(function (row) {
            const cells = row.querySelectorAll('td');
            const crasmNo = cells[1] ? cells[1].textContent.toLowerCase() : '';
            const nameOfSo = cells[2] ? cells[2].textContent.toLowerCase() : '';
            const matchesSearch = !searchTerm || crasmNo.includes(searchTerm) || nameOfSo.includes(searchTerm);
            const matchesProvince = !province || row.dataset.province === province;
            const matchesSect = !sect || row.dataset.sect === sect;
            const matchesType = !type || row.dataset.type === type;
            const matchesSex = !sex || row.dataset.sex === sex;

            const rowDate = row.dataset.date || '';

            let matchesDate = true;
            if (dateFrom || dateTo) {
                if (!rowDate) {
                    matchesDate = false;
                } else {
                    if (dateFrom && rowDate < dateFrom) matchesDate = false;
                    if (dateTo && rowDate > dateTo) matchesDate = false;
                }
            } else {
                const matchesMonth = !month || row.dataset.month === month;
                const matchesYear = !year || row.dataset.year === year;
                matchesDate = matchesMonth && matchesYear;
            }

            return matchesSearch && matchesProvince && matchesSect && matchesType && matchesSex && matchesDate;
        });

        filtered.sort(function (a, b) {
            const dateA = a.dataset.date || '';
            const dateB = b.dataset.date || '';
            if (sort === 'newest') {
                return dateB.localeCompare(dateA);
            } else {
                return dateA.localeCompare(dateB);
            }
        });

        return filtered;
    }

    function renderTable() {
        const filteredRows = getFilteredRows();
        const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        allRows.forEach(function (row) {
            row.style.display = 'none';
        });

        const start = (currentPage - 1) * rowsPerPage;
        const pageRows = filteredRows.slice(start, start + rowsPerPage);
        pageRows.forEach(function (row) {
            row.style.display = '';
        });

        if (filteredRows.length === 0) {
            paginationInfo.textContent = 'No records found';
        } else {
            paginationInfo.textContent = 'Showing ' + (start + 1) + ' to ' + Math.min(start + rowsPerPage, filteredRows.length) + ' of ' + filteredRows.length + ' records';
        }

        authorityTotal.textContent = 'Total: ' + filteredRows.length;

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationControls.innerHTML = '';

        const prevItem = document.createElement('li');
        prevItem.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
        prevItem.innerHTML = '<a class="page-link" href="#">Previous</a>';
        prevItem.addEventListener('click', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
        paginationControls.appendChild(prevItem);

        for (let i = 1; i <= totalPages; i++) {
            const pageItem = document.createElement('li');
            pageItem.className = 'page-item' + (currentPage === i ? ' active' : '');
            pageItem.innerHTML = '<a class="page-link" href="#">' + i + '</a>';
            pageItem.addEventListener('click', function (e) {
                e.preventDefault();
                currentPage = i;
                renderTable();
            });
            paginationControls.appendChild(pageItem);
        }

        const nextItem = document.createElement('li');
        nextItem.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
        nextItem.innerHTML = '<a class="page-link" href="#">Next</a>';
        nextItem.addEventListener('click', function (e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
        paginationControls.appendChild(nextItem);
    }

    [searchInput, filterProvince, filterSect, filterType, filterSex, filterSort].forEach(function (control) {
        control.addEventListener('input', function () {
            currentPage = 1;
            updateFilterCount();
            renderTable();
        });
        control.addEventListener('change', function () {
            currentPage = 1;
            updateFilterCount();
            renderTable();
        });
    });

    [filterMonth, filterYear].forEach(function (control) {
        control.addEventListener('change', function () {
            syncFilterAvailability();
            currentPage = 1;
            updateFilterCount();
            renderTable();
        });
    });

    [filterDateFrom, filterDateTo].forEach(function (control) {
        control.addEventListener('change', function () {
            syncFilterAvailability();
            currentPage = 1;
            updateFilterCount();
            renderTable();
        });
    });

    syncFilterAvailability();
    updateFilterCount();
    renderTable();

    const exportWordBtn = document.getElementById('exportWordBtn');
    exportWordBtn.addEventListener('click', function () {
        const params = new URLSearchParams();
        if (filterProvince.value) params.set('province', filterProvince.value);
        if (filterSect.value) params.set('sect', filterSect.value);
        if (filterType.value) params.set('type', filterType.value);
        if (filterSex.value) params.set('sex', filterSex.value);

        if (isDateRangeActive()) {
            if (filterDateFrom.value) params.set('date_from', filterDateFrom.value);
            if (filterDateTo.value) params.set('date_to', filterDateTo.value);
        } else {
            if (filterMonth.value) params.set('month', filterMonth.value);
            if (filterYear.value) params.set('year', filterYear.value);
        }

        const query = params.toString();
        window.location.href = '../actions/authority_export_word.php' + (query ? '?' + query : '');
    });

    const viewModal = new bootstrap.Modal(document.getElementById('viewAuthorityModal'));

    function showRowDetails(row) {
        const cells = row.querySelectorAll('td');
        const labels = ['No.', 'CRASM#', 'Name of SO', 'Province', 'Type', 'Religious Sect', 'Sex', 'Church Address', 'Contact No.', 'Position', 'Approved'];
        let html = '<div class="row g-3">';
        labels.forEach(function (label, index) {
            html += '<div class="col-md-6"><div class="text-muted small">' + label + '</div><div class="fw-semibold">' + cells[index].textContent.trim() + '</div></div>';
        });
        html += '</div>';
        document.getElementById('viewAuthorityBody').innerHTML = html;
        viewModal.show();
    }

    allRows.forEach(function (row) {
        row.addEventListener('click', function () {
            showRowDetails(row);
        });
    });

});
</script>

</body>
</html>