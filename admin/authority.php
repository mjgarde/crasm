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

<link href="../assets/vendor/bootstrap-5.3.8/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/vendor/fontawesome-free-7.3.1/css/all.min.css">
<style>
.filter-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.filter-bar .search-wrap {
    flex: 1 1 220px;
    min-width: 160px;
}
.filter-toggle-btn {
    position: relative;
    white-space: nowrap;
}
.filter-toggle-btn .filter-count {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #0a1f44;
    color: #fff;
    border-radius: 50%;
    font-size: 10px;
    line-height: 1;
    padding: 3px 5px;
    font-weight: 700;
}
.filter-sort-select {
    width: auto;
    min-width: 150px;
}
.filter-panel {
    display: none;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #eef0f3;
}
.filter-panel.show {
    display: block;
}
.filter-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.filter-panel-header h6 {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6c757d;
    margin: 0;
}
.filter-panel-close {
    background: none;
    border: none;
    color: #6c757d;
    font-size: 14px;
    line-height: 1;
    padding: 4px;
}
.filter-panel-close:hover {
    color: #0a1f44;
}
.filter-card .filter-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: 4px;
    display: block;
}
.filter-card .form-select,
.filter-card .form-control {
    font-size: 13px;
}
.filter-card select:disabled,
.filter-card input:disabled {
    background-color: #eef0f3 !important;
    opacity: 0.6;
    cursor: not-allowed;
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px 18px;
}
@media (max-width: 992px) {
    .filter-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
.date-range-group {
    display: flex;
    align-items: center;
    gap: 6px;
}
.date-range-group input[type="date"] {
    min-width: 0;
    flex: 1 1 auto;
}
.date-range-group span {
    font-size: 11px;
    color: #6c757d;
    flex: 0 0 auto;
}
.filter-panel-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #eef0f3;
}
#authorityTableWrapper {
    min-height: 620px;
}
@media (max-width: 768px) {
    #authorityTableWrapper {
        min-height: 480px;
    }
}
</style>
</head>

<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

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
                    <button type="button" class="btn btn-sm text-white" style="background-color:#0a1f44;white-space:nowrap;" data-bs-toggle="modal" data-bs-target="#addAuthorityModal">
                        <i class="fa-solid fa-plus me-1"></i> Add
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
                                <th class="text-center">Actions</th>
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
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item edit-authority" href="#" data-id="<?php echo htmlspecialchars($record['id']); ?>">
                                                    <i class="fa-solid fa-pen me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger delete-authority" href="#" data-id="<?php echo htmlspecialchars($record['id']); ?>">
                                                    <i class="fa-solid fa-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($authorityRecords)): ?>
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">No authority records found.</td>
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

<div class="modal fade" id="addAuthorityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <form id="authorityForm" method="POST" action="../actions/authority_save.php">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold mb-0">Add Authority</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y:auto;font-size:13px;">

                    <input type="hidden" name="id" id="authorityId">

                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Basic Information</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label for="crasm_no" class="form-label mb-1" style="font-size:13px;">CRASM#</label>
                            <input type="text" class="form-control form-control-sm" id="crasm_no" name="crasm_no" style="font-size:13px;" required>
                        </div>
                        <div class="col-md-8">
                            <label for="name_of_so" class="form-label mb-1" style="font-size:13px;">Name of SO</label>
                            <input type="text" class="form-control form-control-sm" id="name_of_so" name="name_of_so" style="font-size:13px;" required>
                        </div>
                        <div class="col-md-4">
                            <label for="provinces" class="form-label mb-1" style="font-size:13px;">Province</label>
                            <select class="form-select form-select-sm" id="provinces" name="provinces" style="font-size:13px;" required>
                                <option value="">Select Province</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="municipality" class="form-label mb-1" style="font-size:13px;">City/Municipality</label>
                            <select class="form-select form-select-sm" id="municipality" name="municipality" style="font-size:13px;">
                                <option value="">Select Province First</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label mb-1" style="font-size:13px;">Type</label>
                            <select class="form-select form-select-sm" id="type" name="type" style="font-size:13px;" required>
                                <option value="">Select Type</option>
                                <option value="New">New</option>
                                <option value="Renewal">Renewal</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="sex" class="form-label mb-1" style="font-size:13px;">Sex</label>
                            <select class="form-select form-select-sm" id="sex" name="sex" style="font-size:13px;" required>
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="religious_sect" class="form-label mb-1" style="font-size:13px;">Religious Sect</label>
                            <input type="text" class="form-control form-control-sm" id="religious_sect" name="religious_sect" list="religiousSectList" autocomplete="off" style="font-size:13px;" required>
                            <datalist id="religiousSectList">
                                <?php foreach ($religiousSects as $sect): ?>
                                <option value="<?php echo htmlspecialchars($sect); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label for="position" class="form-label mb-1" style="font-size:13px;">Position</label>
                            <input type="text" class="form-control form-control-sm" id="position" name="position" style="font-size:13px;">
                        </div>
                    </div>

                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Contact Information</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label for="church_address" class="form-label mb-1" style="font-size:13px;">Church Address</label>
                            <input type="text" class="form-control form-control-sm" id="church_address" name="church_address" style="font-size:13px;">
                        </div>
                        <div class="col-md-4">
                            <label for="contact_number" class="form-label mb-1" style="font-size:13px;">Contact Number</label>
                            <input type="text" class="form-control form-control-sm" id="contact_number" name="contact_number" style="font-size:13px;">
                        </div>
                    </div>

                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Processing Timeline</h6>

                    <div class="mb-2 d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input status-toggle" type="radio" name="status_type" id="statusEncoding" value="encoding">
                            <label class="form-check-label" for="statusEncoding" style="font-size:13px;">Encoding</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input status-toggle" type="radio" name="status_type" id="statusCompliant" value="compliant">
                            <label class="form-check-label" for="statusCompliant" style="font-size:13px;">Compliant</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-2">
                            <label for="filed" class="form-label mb-1" style="font-size:13px;">Filed</label>
                            <input type="date" class="form-control form-control-sm" id="filed" name="filed" style="font-size:13px;">
                        </div>
                        <div class="col-md-2">
                            <label for="payment" class="form-label mb-1" style="font-size:13px;">Payment</label>
                            <input type="date" class="form-control form-control-sm" id="payment" name="payment" style="font-size:13px;">
                        </div>
                        <div class="col-md-2">
                            <label for="received_in_rsso" class="form-label mb-1" style="font-size:13px;">Received in RSSO</label>
                            <input type="date" class="form-control form-control-sm" id="received_in_rsso" name="received_in_rsso" style="font-size:13px;">
                        </div>
                        <div class="col-md-2">
                            <label for="processed" class="form-label mb-1" style="font-size:13px;">Processed</label>
                            <input type="date" class="form-control form-control-sm" id="processed" name="processed" style="font-size:13px;">
                        </div>
                        <div class="col-md-2">
                            <label for="approved" class="form-label mb-1" style="font-size:13px;">Approved</label>
                            <input type="date" class="form-control form-control-sm" id="approved" name="approved" style="font-size:13px;">
                        </div>
                        <div class="col-md-2">
                            <label for="transmitted_to_pso" class="form-label mb-1" style="font-size:13px;">Transmitted to PSO</label>
                            <input type="date" class="form-control form-control-sm" id="transmitted_to_pso" name="transmitted_to_pso" style="font-size:13px;">
                        </div>
                    </div>

                    <div id="complianceFieldsGroup" class="row g-2 mt-1" style="display:none;">
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Compliance</h6>
                        </div>
                        <div class="col-md-4">
                            <label for="return_to_province_for_compliance" class="form-label mb-1" style="font-size:13px;">Return to Province for Compliance</label>
                            <input type="date" class="form-control form-control-sm" id="return_to_province_for_compliance" name="return_to_province_for_compliance" style="font-size:13px;">
                        </div>
                        <div class="col-md-4">
                            <label for="complied" class="form-label mb-1" style="font-size:13px;">Complied</label>
                            <input type="date" class="form-control form-control-sm" id="complied" name="complied" style="font-size:13px;">
                        </div>
                        <div class="col-md-4">
                            <label for="received_in_rsso_after_compliance" class="form-label mb-1" style="font-size:13px;">Received in RSSO After Compliance</label>
                            <input type="date" class="form-control form-control-sm" id="received_in_rsso_after_compliance" name="received_in_rsso_after_compliance" style="font-size:13px;">
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-sm text-white" style="background-color:#0a1f44;font-size:13px;">Save</button>
                </div>
            </form>
        </div>
    </div>
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

<div class="modal fade" id="deleteAuthorityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Delete Authority Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this authority record? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteAuthority">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
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

    let deleteTargetId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteAuthorityModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewAuthorityModal'));
    const addModal = document.getElementById('addAuthorityModal');
    const authorityForm = document.getElementById('authorityForm');

    document.querySelectorAll('.delete-authority').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            deleteTargetId = this.dataset.id;
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteAuthority').addEventListener('click', function () {
        if (deleteTargetId) {
            window.location.href = '../actions/authority_delete.php?id=' + encodeURIComponent(deleteTargetId);
        }
    });

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
        row.addEventListener('click', function (e) {
            if (e.target.closest('.dropdown')) {
                return;
            }
            showRowDetails(row);
        });
    });

    document.querySelectorAll('.edit-authority').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = this.dataset.id;
            const row = document.querySelector('tr.authority-row[data-id="' + id + '"]');
            const record = row ? JSON.parse(row.dataset.record) : null;

            authorityForm.reset();
            document.getElementById('authorityId').value = id;

            if (record) {
                Object.keys(record).forEach(function (key) {
                    if (key === 'municipality' || key === 'no') return;
                    const field = authorityForm.elements[key];
                    if (field) {
                        field.value = record[key] || '';
                    }
                });
                populateMunicipalities(record.provinces || '', record.municipality || '');
            }

            authorityForm.action = '../actions/authority_update.php';
            document.querySelector('#addAuthorityModal .modal-title').textContent = 'Edit Authority';
            new bootstrap.Modal(addModal).show();
            updateComplianceFieldsVisibility();
        });
    });

    addModal.addEventListener('hidden.bs.modal', function () {
        authorityForm.reset();
        document.getElementById('authorityId').value = '';
        authorityForm.action = '../actions/authority_save.php';
        document.querySelector('#addAuthorityModal .modal-title').textContent = 'Add Authority';
        updateComplianceFieldsVisibility();
        populateMunicipalities('', null);
    });

    const municipalitiesByProvince = {
        'Cotabato': [
            'Alamada', 'Aleosan', 'Antipas', 'Arakan', 'Banisilan', 'Carmen',
            'Kabacan', 'Kidapawan City', 'Libungan', "M'lang", 'Magpet',
            'Makilala', 'Matalam', 'Midsayap', 'Pigcawayan', 'Pikit',
            'President Roxas', 'Tulunan'
        ],
        'Sarangani': [
            'Alabel', 'Glan', 'Kiamba', 'Maasim', 'Maitum', 'Malapatan', 'Malungon'
        ],
        'South Cotabato': [
            'Banga', 'General Santos City', 'Koronadal City', 'Lake Sebu',
            'Norala', 'Polomolok', 'Santo Niño', 'Surallah', "T'boli",
            'Tampakan', 'Tantangan', 'Tupi'
        ],
        'Sultan Kudarat': [
            'Bagumbayan', 'Columbio', 'Esperanza', 'Isulan', 'Kalamansig',
            'Lambayong', 'Lebak', 'Lutayan', 'Palimbang', 'President Quirino',
            'Senator Ninoy Aquino', 'Tacurong City'
        ]
    };

    const provinceSelect = document.getElementById('provinces');
    const municipalitySelect = document.getElementById('municipality');

    function populateMunicipalities(selectedProvince, selectedMunicipality) {
        municipalitySelect.innerHTML = '';

        if (!selectedProvince || !municipalitiesByProvince[selectedProvince]) {
            municipalitySelect.innerHTML = '<option value="">Select Province First</option>';
            return;
        }

        municipalitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        municipalitiesByProvince[selectedProvince].forEach(function (municipality) {
            const option = document.createElement('option');
            option.value = municipality;
            option.textContent = municipality;
            if (selectedMunicipality && selectedMunicipality === municipality) {
                option.selected = true;
            }
            municipalitySelect.appendChild(option);
        });
    }

    provinceSelect.addEventListener('change', function () {
        populateMunicipalities(this.value, null);
    });

    populateMunicipalities(provinceSelect.value, null);

    const statusToggles = document.querySelectorAll('.status-toggle');
    const complianceFieldsGroup = document.getElementById('complianceFieldsGroup');

    function updateComplianceFieldsVisibility() {
        const compliantChecked = document.getElementById('statusCompliant').checked;
        complianceFieldsGroup.style.display = compliantChecked ? '' : 'none';
        if (!compliantChecked) {
            complianceFieldsGroup.querySelectorAll('input').forEach(function (input) {
                input.value = '';
            });
        }
    }

    statusToggles.forEach(function (radio) {
        radio.addEventListener('change', updateComplianceFieldsVisibility);
    });

    updateComplianceFieldsVisibility();

});
</script>

</body>
</html>