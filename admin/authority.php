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
@media (max-width: 768px) {
    #filterProvince, #filterSect, #filterType, #filterSex, #filterYear {
        min-width: 0 !important;
        max-width: none !important;
        flex: 1 1 45%;
    }
    #searchInput, .input-group.input-group-sm.flex-grow-1 {
        max-width: none !important;
        flex: 1 1 100%;
    }
}
</style>
</head>

<body>

<div class="d-flex">

<?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-grow-1 bg-light">

        <div class="d-lg-none border-bottom bg-white px-3 py-3">
            <button
class="btn btn-outline-secondary btn-sm"
type="button"
data-bs-toggle="offcanvas"
data-bs-target="#sidebarOffcanvas"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <main class="p-3 p-md-4">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center gap-2" style="font-size:11px;">
                        <div class="input-group input-group-sm flex-grow-1" style="min-width:160px;max-width:260px;">
                            <span class="input-group-text bg-white px-2"><i class="fa-solid fa-magnifying-glass text-muted" style="font-size:10px;"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search" style="font-size:11px;">
                        </div>
                        <select id="filterProvince" class="form-select form-select-sm" style="font-size:11px;width:auto;min-width:110px;max-width:140px;">
                            <option value="">All Provinces</option>
                            <?php foreach ($provinces as $province): ?>
                            <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filterSect" class="form-select form-select-sm" style="font-size:11px;width:auto;min-width:120px;max-width:160px;">
                            <option value="">All Religious Sects</option>
                            <?php foreach ($religiousSects as $sect): ?>
                            <option value="<?php echo htmlspecialchars($sect); ?>"><?php echo htmlspecialchars($sect); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filterType" class="form-select form-select-sm" style="font-size:11px;width:auto;min-width:90px;max-width:110px;">
                            <option value="">All Types</option>
                            <option value="New">New</option>
                            <option value="Renewal">Renewal</option>
                        </select>
                        <select id="filterSex" class="form-select form-select-sm" style="font-size:11px;width:auto;min-width:85px;max-width:100px;">
                            <option value="">All Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        <select id="filterYear" class="form-select form-select-sm" style="font-size:11px;width:auto;min-width:110px;max-width:140px;">
                            <option value="">All Approved Years</option>
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
                        <button type="button" class="btn btn-sm text-white ms-auto" style="background-color:#0a1f44;font-size:11px;" data-bs-toggle="modal" data-bs-target="#addAuthorityModal">
                            <i class="fa-solid fa-plus me-1"></i> Add Authority
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <h6 class="fw-bold mb-0">Authority Records</h6>
                    <span class="text-muted small" id="authorityTotal">Total: 0</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
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

</div>

<div class="modal fade" id="addAuthorityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <form id="authorityForm" method="POST" action="../actions/authority_save.php">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold mb-0">Add Authority</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y:auto;font-size:11px;">

                    <input type="hidden" name="id" id="authorityId">

                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Basic Information</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-2">
                            <label for="no" class="form-label mb-1" style="font-size:11px;">No.</label>
                            <input type="number" class="form-control form-control-sm" id="no" name="no" style="font-size:11px;" required>
                        </div>
                        <div class="col-md-4">
                            <label for="crasm_no" class="form-label mb-1" style="font-size:11px;">CRASM#</label>
                            <input type="text" class="form-control form-control-sm" id="crasm_no" name="crasm_no" style="font-size:11px;" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name_of_so" class="form-label mb-1" style="font-size:11px;">Name of SO</label>
                            <input type="text" class="form-control form-control-sm" id="name_of_so" name="name_of_so" style="font-size:11px;" required>
                        </div>
                        <div class="col-md-4">
                            <label for="provinces" class="form-label mb-1" style="font-size:11px;">Province</label>
                            <select class="form-select form-select-sm" id="provinces" name="provinces" style="font-size:11px;" required>
                                <option value="">Select Province</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?php echo htmlspecialchars($province); ?>"><?php echo htmlspecialchars($province); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="municipality" class="form-label mb-1" style="font-size:11px;">City/Municipality</label>
                            <select class="form-select form-select-sm" id="municipality" name="municipality" style="font-size:11px;">
                                <option value="">Select Province First</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label mb-1" style="font-size:11px;">Type</label>
                            <select class="form-select form-select-sm" id="type" name="type" style="font-size:11px;" required>
                                <option value="">Select Type</option>
                                <option value="New">New</option>
                                <option value="Renewal">Renewal</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="sex" class="form-label mb-1" style="font-size:11px;">Sex</label>
                            <select class="form-select form-select-sm" id="sex" name="sex" style="font-size:11px;" required>
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="religious_sect" class="form-label mb-1" style="font-size:11px;">Religious Sect</label>
                            <input type="text" class="form-control form-control-sm" id="religious_sect" name="religious_sect" list="religiousSectList" autocomplete="off" style="font-size:11px;" required>
                            <datalist id="religiousSectList">
                                <?php foreach ($religiousSects as $sect): ?>
                                <option value="<?php echo htmlspecialchars($sect); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label for="position" class="form-label mb-1" style="font-size:11px;">Position</label>
                            <input type="text" class="form-control form-control-sm" id="position" name="position" style="font-size:11px;">
                        </div>
                    </div>

                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Contact Information</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label for="church_address" class="form-label mb-1" style="font-size:11px;">Church Address</label>
                            <input type="text" class="form-control form-control-sm" id="church_address" name="church_address" style="font-size:11px;">
                        </div>
                        <div class="col-md-4">
                            <label for="contact_number" class="form-label mb-1" style="font-size:11px;">Contact Number</label>
                            <input type="text" class="form-control form-control-sm" id="contact_number" name="contact_number" style="font-size:11px;">
                        </div>
                    </div>

                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Processing Timeline</h6>

                    <div class="mb-2 d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input status-toggle" type="radio" name="status_type" id="statusEncoding" value="encoding">
                            <label class="form-check-label" for="statusEncoding" style="font-size:11px;">Encoding</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input status-toggle" type="radio" name="status_type" id="statusCompliant" value="compliant">
                            <label class="form-check-label" for="statusCompliant" style="font-size:11px;">Compliant</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-2">
                            <label for="filed" class="form-label mb-1" style="font-size:11px;">Filed</label>
                            <input type="date" class="form-control form-control-sm" id="filed" name="filed" style="font-size:11px;">
                        </div>
                        <div class="col-md-2">
                            <label for="payment" class="form-label mb-1" style="font-size:11px;">Payment</label>
                            <input type="date" class="form-control form-control-sm" id="payment" name="payment" style="font-size:11px;">
                        </div>
                        <div class="col-md-2">
                            <label for="received_in_rsso" class="form-label mb-1" style="font-size:11px;">Received in RSSO</label>
                            <input type="date" class="form-control form-control-sm" id="received_in_rsso" name="received_in_rsso" style="font-size:11px;">
                        </div>
                        <div class="col-md-2">
                            <label for="processed" class="form-label mb-1" style="font-size:11px;">Processed</label>
                            <input type="date" class="form-control form-control-sm" id="processed" name="processed" style="font-size:11px;">
                        </div>
                        <div class="col-md-2">
                            <label for="approved" class="form-label mb-1" style="font-size:11px;">Approved</label>
                            <input type="date" class="form-control form-control-sm" id="approved" name="approved" style="font-size:11px;">
                        </div>
                        <div class="col-md-2">
                            <label for="transmitted_to_pso" class="form-label mb-1" style="font-size:11px;">Transmitted to PSO</label>
                            <input type="date" class="form-control form-control-sm" id="transmitted_to_pso" name="transmitted_to_pso" style="font-size:11px;">
                        </div>
                    </div>

                    <div id="complianceFieldsGroup" class="row g-2 mt-1" style="display:none;">
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:.6rem;letter-spacing:.05em;">Compliance</h6>
                        </div>
                        <div class="col-md-4">
                            <label for="return_to_province_for_compliance" class="form-label mb-1" style="font-size:11px;">Return to Province for Compliance</label>
                            <input type="date" class="form-control form-control-sm" id="return_to_province_for_compliance" name="return_to_province_for_compliance" style="font-size:11px;">
                        </div>
                        <div class="col-md-4">
                            <label for="complied" class="form-label mb-1" style="font-size:11px;">Complied</label>
                            <input type="date" class="form-control form-control-sm" id="complied" name="complied" style="font-size:11px;">
                        </div>
                        <div class="col-md-4">
                            <label for="received_in_rsso_after_compliance" class="form-label mb-1" style="font-size:11px;">Received in RSSO After Compliance</label>
                            <input type="date" class="form-control form-control-sm" id="received_in_rsso_after_compliance" name="received_in_rsso_after_compliance" style="font-size:11px;">
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-2">
                    <button type="submit" class="btn btn-sm text-white" style="background-color:#0a1f44;font-size:11px;">Save</button>
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

    const rowsPerPage = 15;
    const searchInput = document.getElementById('searchInput');
    const filterProvince = document.getElementById('filterProvince');
    const filterSect = document.getElementById('filterSect');
    const filterType = document.getElementById('filterType');
    const filterSex = document.getElementById('filterSex');
    const filterYear = document.getElementById('filterYear');
    const tableBody = document.querySelector('#authorityTable tbody');
    const allRows = Array.from(tableBody.querySelectorAll('tr.authority-row'));
    const paginationControls = document.getElementById('paginationControls');
    const paginationInfo = document.getElementById('paginationInfo');
    const authorityTotal = document.getElementById('authorityTotal');
    let currentPage = 1;

    function getFilteredRows() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const province = filterProvince.value;
        const sect = filterSect.value;
        const type = filterType.value;
        const sex = filterSex.value;
        const year = filterYear.value;

        return allRows.filter(function (row) {
            const cells = row.querySelectorAll('td');
            const crasmNo = cells[1] ? cells[1].textContent.toLowerCase() : '';
            const nameOfSo = cells[2] ? cells[2].textContent.toLowerCase() : '';
            const matchesSearch = !searchTerm || crasmNo.includes(searchTerm) || nameOfSo.includes(searchTerm);
            const matchesProvince = !province || row.dataset.province === province;
            const matchesSect = !sect || row.dataset.sect === sect;
            const matchesType = !type || row.dataset.type === type;
            const matchesSex = !sex || row.dataset.sex === sex;
            const matchesYear = !year || row.dataset.year === year;
            return matchesSearch && matchesProvince && matchesSect && matchesType && matchesSex && matchesYear;
        });
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

    [searchInput, filterProvince, filterSect, filterType, filterSex, filterYear].forEach(function (control) {
        control.addEventListener('input', function () {
            currentPage = 1;
            renderTable();
        });
        control.addEventListener('change', function () {
            currentPage = 1;
            renderTable();
        });
    });

    renderTable();

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
                    if (key === 'municipality') return;
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