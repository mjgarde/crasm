<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('pm_handle_profile_request')) {
    function pm_handle_profile_request(): void
    {
        $adminTable = 'administrator';
        $dbFile     = __DIR__ . '/../config/database.php';

        $respond = function (bool $ok, string $msg, array $extra = []): void {
            while (ob_get_level() > 0) { ob_end_clean(); }
            if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
            echo '@@PMJSON@@' . json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra)) . '@@PMEND@@';
            exit;
        };

        ini_set('display_errors', '0');
        register_shutdown_function(function () use ($respond) {
            $e = error_get_last();
            if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $respond(false, 'PHP error: ' . $e['message'] . ' (' . basename($e['file']) . ':' . $e['line'] . ')');
            }
        });

        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $respond(false, 'Invalid security token. Please refresh the page.');
        }
        if (empty($_SESSION['admin_id'])) {
            $respond(false, 'Session expired. Please log in again.', ['expired' => true]);
        }
        $adminId = (int) $_SESSION['admin_id'];

        if (!is_file($dbFile)) { $respond(false, 'DB file not found: ' . $dbFile); }
        require_once $dbFile;
        if (!class_exists('Database')) { $respond(false, 'Class Database not found.'); }
        try {
            $pdo = (new Database())->connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            $respond(false, 'Database connection failed.');
        }

        $action = $_POST['pm_action'] ?? '';

        try {
            switch ($action) {

                case 'get':
                    $st = $pdo->prepare("SELECT name FROM {$adminTable} WHERE id = ?");
                    $st->execute([$adminId]);
                    $row = $st->fetch(PDO::FETCH_ASSOC);
                    if (!$row) { $respond(false, 'Account not found.'); }
                    $respond(true, 'OK', ['data' => $row]);

                case 'update_profile':
                    $name     = trim($_POST['name'] ?? '');
                    $username = trim($_POST['username'] ?? '');

                    if ($name === '' || strlen($name) > 255) {
                        $respond(false, 'Please enter a valid name (max 255 characters).');
                    }

                    $wantsNewUsername = ($username !== '' && $username !== '********');

                    $st = $pdo->prepare("SELECT username, password FROM {$adminTable} WHERE id = ?");
                    $st->execute([$adminId]);
                    $me = $st->fetch(PDO::FETCH_ASSOC);
                    if (!$me) { $respond(false, 'Account not found.'); }

                    if ($wantsNewUsername && $username === $me['username']) {
                        $wantsNewUsername = false;
                    }

                    if ($wantsNewUsername) {
                        if (strlen($username) > 50) {
                            $respond(false, 'Username must be 50 characters or less.');
                        }
                        $pw = $_POST['current_password'] ?? '';
                        if ($pw === '') {
                            $respond(false, 'Please enter your current password to change the username.');
                        }
                        if (!password_verify($pw, (string) $me['password'])) {
                            $respond(false, 'Current password is incorrect.');
                        }
                        $chk = $pdo->prepare("SELECT id FROM {$adminTable} WHERE username = ? AND id <> ?");
                        $chk->execute([$username, $adminId]);
                        if ($chk->fetch()) { $respond(false, 'That username is already in use.'); }

                        $pdo->prepare("UPDATE {$adminTable} SET name = ?, username = ? WHERE id = ?")
                            ->execute([$name, $username, $adminId]);
                    } else {
                        $pdo->prepare("UPDATE {$adminTable} SET name = ? WHERE id = ?")
                            ->execute([$name, $adminId]);
                    }

                    $_SESSION['admin_name'] = $name;
                    $respond(true, 'Profile updated successfully.', [
                        'name'    => $name,
                        'initial' => strtoupper(substr($name, 0, 1)),
                    ]);

                case 'change_password':
                    $current = $_POST['current_password'] ?? '';
                    $new     = $_POST['new_password'] ?? '';
                    $confirm = $_POST['confirm_password'] ?? '';

                    if ($current === '' || $new === '' || $confirm === '') {
                        $respond(false, 'Please fill in all password fields.');
                    }
                    if ($new !== $confirm) {
                        $respond(false, 'New password and confirmation do not match.');
                    }
                    if (strlen($new) < 4) {
                        $respond(false, 'New password must be at least 4 characters.');
                    }

                    $st = $pdo->prepare("SELECT password FROM {$adminTable} WHERE id = ?");
                    $st->execute([$adminId]);
                    $hash = (string) $st->fetchColumn();

                    if ($hash === '' || !password_verify($current, $hash)) {
                        $respond(false, 'Current password is incorrect.');
                    }
                    if (password_verify($new, $hash)) {
                        $respond(false, 'New password must be different from the current password.');
                    }

                    $pdo->prepare("UPDATE {$adminTable} SET password = ? WHERE id = ?")
                        ->execute([password_hash($new, PASSWORD_DEFAULT), $adminId]);

                    session_regenerate_id(true);
                    $respond(true, 'Password changed successfully.');

                default:
                    $respond(false, 'Unknown action.');
            }
        } catch (Throwable $e) {
            $respond(false, 'Something went wrong: ' . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pm_action'])) {
    pm_handle_profile_request();
}

$adminName    = $_SESSION['admin_name'] ?? 'Administrator';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$csrfToken    = $_SESSION['csrf_token'];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<nav
    class="navbar navbar-expand-lg crasm-navbar"
    style="font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background-color:#002d62;"
>
    <div class="container-fluid px-3">

        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="../assets/img/logo.png" alt="Seal"
                 style="width:32px;height:32px;object-fit:contain;flex-shrink:0;" class="me-2">
            <div>
                <div class="fw-bold text-white" style="font-size:12px;white-space:nowrap;line-height:1.1;">
                    PHILIPPINE STATISTICS AUTHORITY XII
                </div>
                <div style="font-size:9px;white-space:nowrap;color:rgba(255,255,255,.7);">
                    Certificate of Registration of Authority to Solemnize Marriage
                </div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#crasmNavbarCollapse" aria-controls="crasmNavbarCollapse"
                aria-expanded="false" aria-label="Toggle navigation"
                style="font-size:12px;padding:.25rem .5rem;border-color:rgba(255,255,255,.5);">
            <span class="navbar-toggler-icon" style="filter:invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="crasmNavbarCollapse">

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0" style="font-size:12px;">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="dashboard.php" title="Dashboard">
                        <i class="fa-solid fa-gauge-high" style="width:16px;font-size:12px;flex-shrink:0;"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center"><span style="color:rgba(255,255,255,.3);">|</span></li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="authority.php" title="Authority">
                        <i class="fa-solid fa-user-shield" style="width:16px;font-size:12px;flex-shrink:0;"></i> Authority
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center"><span style="color:rgba(255,255,255,.3);">|</span></li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="reports.php" title="Reports">
                        <i class="fa-solid fa-chart-column" style="width:16px;font-size:12px;flex-shrink:0;"></i> Reports
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center"><span style="color:rgba(255,255,255,.3);">|</span></li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="users.php" title="Users">
                        <i class="fa-solid fa-users" style="width:16px;font-size:12px;flex-shrink:0;"></i> Users
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center"><span style="color:rgba(255,255,255,.3);">|</span></li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-1" href="backup.php" title="Backup &amp; Recovery">
                        <i class="fa-solid fa-database" style="width:16px;font-size:12px;flex-shrink:0;"></i> Backup &amp; Recovery
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-center"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:12px;">
                        <i class="fa-solid fa-circle-user" style="font-size:20px;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0 overflow-hidden" style="font-size:12px;min-width:220px;">
                        <li class="d-flex align-items-center gap-2 px-3 py-3" style="background-color:#f8f9fa;">
                            <span id="navAvatar"
                                  class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-semibold flex-shrink-0"
                                  style="width:36px;height:36px;font-size:14px;background-color:#002d62;">
                                <?= htmlspecialchars($adminInitial) ?>
                            </span>
                            <div class="text-truncate">
                                <div class="text-muted" style="font-size:10px;">Signed in as</div>
                                <div id="navAdminName" class="fw-semibold text-truncate" style="font-size:13px;color:#111;">
                                    <?= htmlspecialchars($adminName) ?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2" href="#"
                               data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="fa-solid fa-user-pen" style="width:14px;"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2 px-3 py-2" href="../logout.php">
                                <i class="fa-solid fa-right-from-bracket" style="width:14px;"></i> Log Out
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

<!-- ===================== PROFILE MODAL ===================== -->
<div class="modal fade crasm-profile" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true"
     style="font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-0 text-white" style="background-color:#002d62;">
                <div class="d-flex align-items-center gap-3">
                    <span id="pmAvatar"
                          class="d-inline-flex align-items-center justify-content-center rounded-circle fw-semibold"
                          style="width:44px;height:44px;font-size:18px;background:#d4a017;color:#002d62;">
                        <?= htmlspecialchars($adminInitial) ?>
                    </span>
                    <div>
                        <h5 class="modal-title mb-0" id="profileModalLabel" style="font-size:15px;font-weight:600;">My Profile</h5>
                        <div style="font-size:11px;color:rgba(255,255,255,.7);">Administrator account</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3" style="font-size:13px;">

                <div id="pmAlert" class="alert d-none py-2 px-3 mb-3" role="alert" style="font-size:12px;"></div>

                <ul class="nav nav-tabs mb-3" role="tablist" style="font-size:12px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pmTabInfo" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-1"></i> Account Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pmTabPass" type="button" role="tab">
                            <i class="fa-solid fa-key me-1"></i> Change Password
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- Account Info -->
                    <div class="tab-pane fade show active" id="pmTabInfo" role="tabpanel">
                        <form id="pmInfoForm" novalidate autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="pm_action" value="update_profile">

                            <div class="mb-3">
                                <label for="pmName" class="form-label fw-semibold mb-1">Full Name</label>
                                <input type="text" class="form-control form-control-sm" id="pmName" name="name" maxlength="255" required>
                            </div>
                            <div class="mb-3">
                                <label for="pmUsername" class="form-label fw-semibold mb-1">Username</label>
                                <input type="text" class="form-control form-control-sm" id="pmUsername" name="username" maxlength="50" value="********" autocomplete="off" required>
                            </div>
                            <div class="mb-3 d-none" id="pmInfoPassGroup">
                                <label for="pmInfoPass" class="form-label fw-semibold mb-1">Current Password</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control" id="pmInfoPass" name="current_password" autocomplete="current-password">
                                    <button class="btn btn-outline-secondary pm-toggle" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-sm text-white pm-submit" style="background-color:#002d62;">
                                    <span class="spinner-border spinner-border-sm d-none me-1"></span>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="tab-pane fade" id="pmTabPass" role="tabpanel">
                        <form id="pmPassForm" novalidate autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="pm_action" value="change_password">

                            <div class="mb-3">
                                <label for="pmCurrent" class="form-label fw-semibold mb-1">Current Password</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control" id="pmCurrent" name="current_password" autocomplete="current-password" required>
                                    <button class="btn btn-outline-secondary pm-toggle" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="pmNew" class="form-label fw-semibold mb-1">New Password</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control" id="pmNew" name="new_password" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary pm-toggle" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                </div>
                                <div class="form-text" style="font-size:11px;">Minimum 4 characters.</div>
                            </div>
                            <div class="mb-3">
                                <label for="pmConfirm" class="form-label fw-semibold mb-1">Confirm New Password</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" class="form-control" id="pmConfirm" name="confirm_password" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary pm-toggle" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-sm text-white pm-submit" style="background-color:#a3202f;">
                                    <span class="spinner-border spinner-border-sm d-none me-1"></span>Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .crasm-navbar .nav-link { color: rgba(255,255,255,.75); padding: .5rem .65rem; transition: color .15s ease; }
    .crasm-navbar .nav-link:hover { color:
    .crasm-navbar .dropdown-toggle::after { display: none; }
    .crasm-profile .nav-tabs .nav-link { color:
    .crasm-profile .nav-tabs .nav-link.active { color:
    .crasm-profile .form-control:focus { border-color:
</style>

<script>
(function () {
    const ENDPOINT = window.location.href;
    const modalEl  = document.getElementById('profileModal');
    const alertBox = document.getElementById('pmAlert');
    const infoForm = document.getElementById('pmInfoForm');
    const passForm = document.getElementById('pmPassForm');
    const csrf     = '<?= $csrfToken ?>';

    function showAlert(type, msg) {
        alertBox.className = 'alert py-2 px-3 mb-3 alert-' + type;
        alertBox.textContent = msg;
    }
    function hideAlert() { alertBox.className = 'alert d-none'; }

    const MASK = '********';
    const usernameInput = document.getElementById('pmUsername');
    const infoPassGroup = document.getElementById('pmInfoPassGroup');
    const infoPass      = document.getElementById('pmInfoPass');
    function syncUsernamePass() {
        const v = usernameInput.value.trim();
        const changed = v !== '' && v !== MASK;
        infoPassGroup.classList.toggle('d-none', !changed);
        if (!changed) { infoPass.value = ''; }
    }
    function resetUsernameMask() { usernameInput.value = MASK; syncUsernamePass(); }
    usernameInput.addEventListener('focus', () => { if (usernameInput.value === MASK) usernameInput.value = ''; });
    usernameInput.addEventListener('blur',  () => { if (usernameInput.value.trim() === '') resetUsernameMask(); });
    usernameInput.addEventListener('input', syncUsernamePass);

    async function post(formData) {
        const res = await fetch(ENDPOINT, { method: 'POST', body: formData, credentials: 'same-origin' });
        const raw = await res.text();
        const m = raw.match(/@@PMJSON@@([\s\S]*?)@@PMEND@@/);
        let json;
        try { json = JSON.parse(m[1]); } catch (e) {
            console.error('Profile request failed (HTTP ' + res.status + '):', raw);
            json = { success: false, message: 'Unexpected server response (HTTP ' + res.status + '). Check browser console.' };
        }
        if (json.expired) { window.location.href = '../login.php'; }
        return json;
    }

    function setLoading(form, on) {
        const btn = form.querySelector('.pm-submit');
        btn.disabled = on;
        btn.querySelector('.spinner-border').classList.toggle('d-none', !on);
    }

    modalEl.addEventListener('show.bs.modal', async () => {
        hideAlert();
        passForm.reset();
        const fd = new FormData();
        fd.append('csrf_token', csrf);
        fd.append('pm_action', 'get');
        const r = await post(fd);
        if (r.success) {
            document.getElementById('pmName').value     = r.data.name;
            resetUsernameMask();
        } else {
            showAlert('danger', r.message);
        }
    });

    infoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();
        setLoading(infoForm, true);
        const r = await post(new FormData(infoForm));
        setLoading(infoForm, false);
        showAlert(r.success ? 'success' : 'danger', r.message);
        if (r.success) {
            document.getElementById('navAdminName').textContent = r.name;
            document.getElementById('navAvatar').textContent    = r.initial;
            document.getElementById('pmAvatar').textContent     = r.initial;
            resetUsernameMask();
        }
    });

    passForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();
        setLoading(passForm, true);
        const r = await post(new FormData(passForm));
        setLoading(passForm, false);
        showAlert(r.success ? 'success' : 'danger', r.message);
        if (r.success) { passForm.reset(); }
    });

    document.querySelectorAll('.pm-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            const show  = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
        });
    });

})();
</script>