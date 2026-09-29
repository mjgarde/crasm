<?php
require_once __DIR__ . '/../config/session_check.php';
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

require_once __DIR__ . '/../config/database.php';
const ADMIN_TABLE = 'administrator';
const USER_TABLE  = 'users';
$pdo = (new Database())->connect();

function h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['um_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $out = function (bool $ok, string $msg, int $code = 200) {
        http_response_code($code);
        echo json_encode(['success' => $ok, 'message' => $msg]);
        exit;
    };
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $out(false, 'Invalid security token. Please refresh the page.', 403);
    }
    $verifyAdmin = function () use ($pdo, $out) {
        $pw = $_POST['admin_password'] ?? '';
        if ($pw === '') { $out(false, 'Administrator password is required.'); }
        $st = $pdo->prepare('SELECT password FROM ' . ADMIN_TABLE . ' WHERE id = ?');
        $st->execute([(int) $_SESSION['admin_id']]);
        $hash = (string) $st->fetchColumn();
        if ($hash === '' || !password_verify($pw, $hash)) { $out(false, 'Incorrect administrator password.'); }
    };
    $validate = function () use ($out) {
        $name = trim($_POST['name'] ?? '');
        $user = trim($_POST['username'] ?? '');
        if ($name === '' || strlen($name) > 255) { $out(false, 'Please enter a valid full name.'); }
        if (!preg_match('/^[A-Za-z0-9._@-]{3,50}$/', $user)) {
            $out(false, 'Username must be 3-50 characters (letters, numbers, . _ @ -).');
        }
        return [$name, $user];
    };
    $id = (int) ($_POST['id'] ?? 0);

    try {
        switch ($_POST['um_action']) {
            case 'add':
                [$name, $user] = $validate();
                $pass = $_POST['password'] ?? '';
                if (strlen($pass) < 4) { $out(false, 'Password must be at least 4 characters.'); }
                $st = $pdo->prepare('SELECT id FROM ' . USER_TABLE . ' WHERE username = ?');
                $st->execute([$user]);
                if ($st->fetch()) { $out(false, 'That username is already taken.'); }
                $pdo->prepare('INSERT INTO ' . USER_TABLE . ' (name, username, password) VALUES (?, ?, ?)')
                    ->execute([$name, $user, password_hash($pass, PASSWORD_DEFAULT)]);
                $out(true, 'User added successfully.');

            case 'edit':
                $verifyAdmin();
                [$name, $user] = $validate();
                $pass = $_POST['password'] ?? '';
                if ($pass !== '' && strlen($pass) < 4) { $out(false, 'Password must be at least 4 characters.'); }
                $st = $pdo->prepare('SELECT id FROM ' . USER_TABLE . ' WHERE id = ?');
                $st->execute([$id]);
                if (!$st->fetch()) { $out(false, 'User not found.', 404); }
                $st = $pdo->prepare('SELECT id FROM ' . USER_TABLE . ' WHERE username = ? AND id <> ?');
                $st->execute([$user, $id]);
                if ($st->fetch()) { $out(false, 'That username is already taken.'); }
                if ($pass !== '') {
                    $pdo->prepare('UPDATE ' . USER_TABLE . ' SET name = ?, username = ?, password = ? WHERE id = ?')
                        ->execute([$name, $user, password_hash($pass, PASSWORD_DEFAULT), $id]);
                } else {
                    $pdo->prepare('UPDATE ' . USER_TABLE . ' SET name = ?, username = ? WHERE id = ?')
                        ->execute([$name, $user, $id]);
                }
                $out(true, 'User updated successfully.');

            case 'delete':
                $verifyAdmin();
                $st = $pdo->prepare('DELETE FROM ' . USER_TABLE . ' WHERE id = ?');
                $st->execute([$id]);
                if (!$st->rowCount()) { $out(false, 'User not found.', 404); }
                $out(true, 'User deleted successfully.');

            default:
                $out(false, 'Unknown action.', 400);
        }
    } catch (Throwable $e) {
        $out(false, 'Database error: ' . $e->getMessage(), 500);
    }
}

$users = []; $dbError = '';
try {
    $users = $pdo->query('SELECT id, name, username, created_at, updated_at FROM ' . USER_TABLE . ' ORDER BY id DESC')->fetchAll();
} catch (Throwable $e) {
    $dbError = 'Table "' . USER_TABLE . '" not found. Run users.sql in phpMyAdmin first.';
}
$csrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRASM | Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
    .btn-navy { background:#002d62; color:#fff; border:0; } .btn-navy:hover { background:#01204a; color:#fff; }
    .btn-add { background:#0B3D7A; color:#fff; border:0; border-radius:6px; padding:.3rem .85rem; white-space:nowrap; font-size:13px; font-weight:500; display:inline-flex; align-items:center; gap:8px; }
    .btn-add:hover { background:#082f5f; color:#fff; }
    .avatar { width:34px; height:34px; border-radius:50%; color:#fff; font-weight:600; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px; }
    .act { width:32px; height:32px; padding:0; border-radius:7px; border:0; color:#fff; box-shadow:0 1px 3px rgba(0,0,0,.18); transition:background .15s, transform .1s; }
    .act:hover { transform:translateY(-1px); color:#fff; }
    .act.edit { background:#0B3D7A; }
    .act.edit:hover { background:#082C59; }
    .act.del { background:#8E1B27; }
    .act.del:hover { background:#6E1420; }
    .count-pill { display:inline-block; margin-left:6px; padding:1px 10px; border-radius:999px; background:#0B3D7A; color:#fff; font-size:12px; font-weight:600; vertical-align:middle; }
    .um-search { max-width:280px; }
    .form-control:focus { border-color:#0B3D7A; box-shadow:0 0 0 .2rem rgba(11,61,122,.15); }
    .modal-header.navy { background:#002d62; color:#fff; border:0; }
    .admin-box { background:#FFF8E6; border:1px solid #F3DFA2; border-radius:8px; padding:12px; }
    .user-badge { display:inline-block; padding:2px 9px; border:1px solid #E3E8EF; border-radius:999px; background:#F7F9FC; font-size:12px; }
</style>
</head>
<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="min-vh-100">
  <main class="p-3 p-md-4">
  <div class="dash-shell">

    <?php if ($dbError): ?><div class="alert alert-danger py-2"><?= h($dbError) ?></div><?php endif; ?>

    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Total users <span class="count-pill"><?= count($users) ?></span></h2>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm um-search">
            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" id="search" class="form-control" placeholder="Search name or username...">
          </div>
          <button class="btn-add" id="btnAdd"><i class="fa-solid fa-user-plus"></i> Add user</button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="recent-table">
          <thead>
            <tr><th style="width:56px;">#</th><th>Name</th><th>Username</th><th>Created</th><th>Last updated</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody id="tbody">
          <?php $colors = ['#0B3D7A', '#B8873A', '#A3202F', '#2F7D5A', '#5B4B8A', '#0F766E']; $n = count($users); ?>
          <?php foreach ($users as $i => $u): ?>
            <tr data-id="<?= (int) $u['id'] ?>" data-name="<?= h($u['name']) ?>" data-username="<?= h($u['username']) ?>">
              <td class="text-muted"><?= $n - $i ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar" style="background:<?= $colors[$u['id'] % count($colors)] ?>;"><?= h(strtoupper(mb_substr($u['name'], 0, 1))) ?></span>
                  <span class="fw-semibold"><?= h($u['name']) ?></span>
                </div>
              </td>
              <td><span class="user-badge"><?= h($u['username']) ?></span></td>
              <td class="text-muted"><?= h(date('M d, Y', strtotime($u['created_at']))) ?></td>
              <td class="text-muted"><?= h(date('M d, Y h:i A', strtotime($u['updated_at']))) ?></td>
              <td class="text-end">
                <button class="act edit me-1" title="Edit"><i class="fa-solid fa-pen"></i></button>
                <button class="act del" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div id="empty" class="text-center text-muted py-4 <?= $users ? 'd-none' : '' ?>">
          <i class="fa-solid fa-user-slash mb-2" style="font-size:26px;opacity:.4;"></i>
          <div id="emptyText"><?= $users ? 'No matching users.' : 'No users yet. Click "Add user" to create one.' ?></div>
        </div>
      </div>
    </section>

  </div>
  </main>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header navy">
                <h5 class="modal-title" id="userModalTitle" style="font-size:15px;font-weight:600;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" class="modal-body" autocomplete="off" novalidate>
                <div id="userAlert" class="alert alert-danger py-2 px-3 d-none" style="font-size:12px;"></div>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="um_action" id="uAction"><input type="hidden" name="id" id="uId">
                <div class="mb-3"><label class="form-label fw-semibold mb-1">Full Name</label>
                    <input type="text" class="form-control form-control-sm" name="name" id="uName" maxlength="255" required></div>
                <div class="mb-3"><label class="form-label fw-semibold mb-1">Username</label>
                    <input type="text" class="form-control form-control-sm" name="username" id="uUser" maxlength="50" required></div>
                <div class="mb-3"><label class="form-label fw-semibold mb-1">Password <span id="uPassNote" class="text-muted fw-normal"></span></label>
                    <div class="input-group input-group-sm"><input type="password" class="form-control" name="password" id="uPass" autocomplete="new-password">
                    <button class="btn btn-outline-secondary tg" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button></div></div>
                <div class="admin-box mb-3 d-none" id="uAdminBox">
                    <label class="form-label fw-semibold mb-1"><i class="fa-solid fa-shield-halved me-1"></i>Administrator Password</label>
                    <div class="input-group input-group-sm"><input type="password" class="form-control" name="admin_password" id="uAdminPass" autocomplete="current-password">
                    <button class="btn btn-outline-secondary tg" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button></div>
                </div>
                <button type="submit" class="btn btn-navy btn-sm w-100 py-2" id="uSubmit"><span class="spinner-border spinner-border-sm d-none me-1"></span><span>Save</span></button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;background:#fdecee;color:#a3202f;font-size:22px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h5 class="fw-bold mb-1" style="font-size:16px;">Delete this user?</h5>
                    <div class="text-muted">You are about to delete <strong id="delName"></strong>. This action cannot be undone.</div>
                </div>
                <form id="delForm" autocomplete="off" novalidate>
                    <div id="delAlert" class="alert alert-danger py-2 px-3 d-none" style="font-size:12px;"></div>
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="um_action" value="delete"><input type="hidden" name="id" id="delId">
                    <div class="admin-box mb-3">
                        <label class="form-label fw-semibold mb-1"><i class="fa-solid fa-shield-halved me-1"></i>Administrator Password</label>
                        <div class="input-group input-group-sm"><input type="password" class="form-control" name="admin_password" id="delPass" autocomplete="current-password">
                        <button class="btn btn-outline-secondary tg" type="button" tabindex="-1"><i class="fa-solid fa-eye"></i></button></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm w-50 text-white" style="background:#a3202f;" id="delSubmit"><span class="spinner-border spinner-border-sm d-none me-1"></span>Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
    <div id="toast" class="toast align-items-center text-white border-0" role="alert">
        <div class="d-flex"><div class="toast-body" id="toastMsg"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const $ = id => document.getElementById(id);
    const userModal = new bootstrap.Modal($('userModal')), delModal = new bootstrap.Modal($('delModal'));

    function toast(msg, ok = true) {
        $('toast').className = 'toast align-items-center text-white border-0 ' + (ok ? 'bg-success' : 'bg-danger');
        $('toastMsg').textContent = msg;
        bootstrap.Toast.getOrCreateInstance($('toast'), { delay: 3000 }).show();
    }
    const flash = sessionStorage.getItem('um_toast');
    if (flash) { sessionStorage.removeItem('um_toast'); toast(flash); }

    async function send(form, alertEl, btn) {
        alertEl.classList.add('d-none'); btn.disabled = true; btn.querySelector('.spinner-border').classList.remove('d-none');
        let r;
        try {
            const res = await fetch(location.href, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
            r = await res.json();
        } catch (e) { r = { success: false, message: 'Unexpected server response.' }; }
        btn.disabled = false; btn.querySelector('.spinner-border').classList.add('d-none');
        if (r.success) { sessionStorage.setItem('um_toast', r.message); location.reload(); }
        else { alertEl.textContent = r.message; alertEl.classList.remove('d-none'); }
    }

    $('btnAdd').onclick = () => {
        $('userForm').reset(); $('userAlert').classList.add('d-none');
        $('uAction').value = 'add'; $('uId').value = '';
        $('userModalTitle').textContent = 'Add New User';
        $('uPassNote').textContent = ''; $('uAdminBox').classList.add('d-none');
        $('uSubmit').lastElementChild.textContent = 'Create User';
        userModal.show();
    };
    $('tbody').addEventListener('click', e => {
        const tr = e.target.closest('tr'); if (!tr) return;
        if (e.target.closest('.edit')) {
            $('userForm').reset(); $('userAlert').classList.add('d-none');
            $('uAction').value = 'edit'; $('uId').value = tr.dataset.id;
            $('uName').value = tr.dataset.name; $('uUser').value = tr.dataset.username;
            $('userModalTitle').textContent = 'Edit User';
            $('uPassNote').textContent = '(leave blank to keep current)'; $('uAdminBox').classList.remove('d-none');
            $('uSubmit').lastElementChild.textContent = 'Save Changes';
            userModal.show();
        } else if (e.target.closest('.del')) {
            $('delForm').reset(); $('delAlert').classList.add('d-none');
            $('delId').value = tr.dataset.id; $('delName').textContent = tr.dataset.name;
            delModal.show();
        }
    });
    $('userForm').addEventListener('submit', e => { e.preventDefault(); send($('userForm'), $('userAlert'), $('uSubmit')); });
    $('delForm').addEventListener('submit', e => { e.preventDefault(); send($('delForm'), $('delAlert'), $('delSubmit')); });

    document.querySelectorAll('.tg').forEach(b => b.addEventListener('click', () => {
        const i = b.parentElement.querySelector('input'), show = i.type === 'password';
        i.type = show ? 'text' : 'password';
        b.innerHTML = '<i class="fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
    }));

    $('search').addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase(); let shown = 0;
        document.querySelectorAll('#tbody tr').forEach(tr => {
            const m = (tr.dataset.name + ' ' + tr.dataset.username).toLowerCase().includes(q);
            tr.style.display = m ? '' : 'none'; if (m) shown++;
        });
        $('empty').classList.toggle('d-none', shown > 0);
        $('emptyText').textContent = 'No matching users.';
    });
})();
</script>
</body>
</html>