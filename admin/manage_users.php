 <?php
require '../includes/auth.php';
require '../includes/db.php';
require_once '../includes/app_nav.php';

checkLogin();
if (!isAdmin()) {
    die('Access denied');
}

$message = '';
$error = '';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| FETCH USERS
|--------------------------------------------------------------------------
*/
$users = [];
try {
    $stmt = $pdo->query("
        SELECT id, employee_no, first_name, middle_name, last_name, email, username, role, department, position, salary, status, created_at
        FROM users
        ORDER BY created_at DESC, id DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $ex) {
    $error = 'Failed to load users: ' . $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }
        .container {
            width: 92%;
            max-width: 1200px;
            margin: 30px auto;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .topbar h1 {
            margin: 0;
            font-size: 1.7rem;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 22px;
        }
        .card h2 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 1.2rem;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
        }
        .required {
            color: #d9534f;
        }
        .actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        button {
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-save { background: #28a745; }
        .btn-reset { background: #6c757d; }
        .btn-primary { background: #007bff; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        .btn-view { background: #6f42c1; }
        .btn-status-active { background: #dc3545; }
        .btn-status-inactive { background: #28a745; }
        .btn-close { background: #6c757d; }
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 14px;
            align-items: end;
        }
        .filter-actions {
            display: flex;
            align-items: end;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
            font-size: 14px;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #fafafa;
            font-weight: bold;
        }
        .muted {
            color: #666;
            font-size: 13px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .badge-admin {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-teacher {
            background: #e2e3e5;
            color: #383d41;
        }
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .name-cell {
            line-height: 1.45;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .empty {
            padding: 24px;
            text-align: center;
            color: #666;
        }
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1000;
            padding: 20px;
            overflow-y: auto;
        }
        .modal-backdrop.open {
            display: block;
        }
        .modal {
            background: #fff;
            border-radius: 10px;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }
        @media (max-width: 800px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .filters {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('admin', 'users'); ?>
    <div class="container">
        <div class="topbar">
            <h1>Manage Users</h1>
            <button type="button" class="btn-primary" id="open_add_modal">Add User</button>
        </div>

        <div class="alert alert-success" id="alert_ok" style="<?= $message ? '' : 'display:none;' ?>"><?= e($message) ?></div>
        <div class="alert alert-error" id="alert_err" style="<?= $error ? '' : 'display:none;' ?>"><?= e($error) ?></div>

        <div class="card">
            <h2>Users List</h2>
            <div class="filters">
                <div class="form-group">
                    <label for="user_search">Search</label>
                    <input type="text" id="user_search" placeholder="Name, email, username, employee no, department...">
                </div>
                <div class="form-group">
                    <label for="user_role_filter">Role</label>
                    <select id="user_role_filter">
                        <option value="">All roles</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="user_status_filter">Status</label>
                    <select id="user_status_filter">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn-reset" id="clear_user_filters">Reset</button>
                </div>
            </div>
            <div class="table-wrap" id="users_table_wrap">
                <?php if (!$users): ?>
                    <div class="empty">No users found.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee No</th>
                                <th>Name</th>
                                <th>Email / Username</th>
                                <th>Role</th>
                                <th>Department / Position</th>
                                <th>Salary</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= e($user['id']) ?></td>
                                    <td><?= $user['employee_no'] ? e($user['employee_no']) : '<span class="muted">—</span>' ?></td>
                                    <td class="name-cell">
                                        <strong>
                                            <?= e($user['first_name']) ?>
                                            <?= $user['middle_name'] ? e(' ' . $user['middle_name']) : '' ?>
                                            <?= e(' ' . $user['last_name']) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div><?= $user['email'] ? e($user['email']) : '<span class="muted">—</span>' ?></div>
                                        <div class="muted">@<?= e($user['username']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-teacher' ?>">
                                            <?= e($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= $user['department'] ? e($user['department']) : '<span class="muted">—</span>' ?></div>
                                        <div class="muted"><?= $user['position'] ? e($user['position']) : '—' ?></div>
                                    </td>
                                    <td>
                                        <?= ($user['salary'] !== null && $user['salary'] !== '') ? e(number_format((float)$user['salary'], 2)) : '<span class="muted">—</span>' ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $user['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= e($user['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= e($user['created_at']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($user['role'] === 'teacher'): ?>
                                                <button type="button" class="btn-view" onclick="window.location.href='leave_requests.php?teacher_id=<?= e($user['id']) ?>'">View Requests</button>
                                            <?php endif; ?>
                                            <button type="button" class="btn-edit" data-action="edit" data-user-b64="<?= e(base64_encode(json_encode($user))) ?>">Edit</button>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <button type="button" class="btn-status-inactive" disabled title="Admin accounts must remain active.">Always Active</button>
                                            <?php else: ?>
                                                <button type="button" class="<?= $user['status'] === 'active' ? 'btn-status-active' : 'btn-status-inactive' ?>" data-action="toggle_status" data-user-id="<?= e($user['id']) ?>" data-next-status="<?= $user['status'] === 'active' ? 'inactive' : 'active' ?>"><?= $user['status'] === 'active' ? 'Set Inactive' : 'Set Active' ?></button>
                                            <?php endif; ?>
                                            <button type="button" class="btn-delete" data-action="delete" data-user-id="<?= e($user['id']) ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="add_user_modal" aria-hidden="true">
        <div class="modal">
            <div class="modal-header">
                <h2>Add User</h2>
                <button type="button" class="btn-close" data-close-modal="add_user_modal">Close</button>
            </div>
            <form id="add_user_form">
                <div class="grid">
                    <div class="form-group"><label for="add_employee_no">Employee No</label><input type="text" id="add_employee_no" name="employee_no"></div>
                    <div class="form-group"><label for="add_email">Email</label><input type="email" id="add_email" name="email"></div>
                    <div class="form-group"><label for="add_first_name">First Name <span class="required">*</span></label><input type="text" id="add_first_name" name="first_name" required></div>
                    <div class="form-group"><label for="add_middle_name">Middle Name</label><input type="text" id="add_middle_name" name="middle_name"></div>
                    <div class="form-group"><label for="add_last_name">Last Name <span class="required">*</span></label><input type="text" id="add_last_name" name="last_name" required></div>
                    <div class="form-group"><label for="add_username">Username <span class="required">*</span></label><input type="text" id="add_username" name="username" required></div>
                    <div class="form-group"><label for="add_password">Password <span class="required">*</span></label><input type="password" id="add_password" name="password" required></div>
                    <div class="form-group"><label for="add_role">Role <span class="required">*</span></label><select id="add_role" name="role" required><option value="teacher">Teacher</option><option value="admin">Admin</option></select></div>
                    <div class="form-group"><label for="add_department">Department</label><input type="text" id="add_department" name="department"></div>
                    <div class="form-group"><label for="add_position">Position</label><input type="text" id="add_position" name="position"></div>
                    <div class="form-group"><label for="add_salary">Salary</label><input type="number" step="0.01" min="0" id="add_salary" name="salary"></div>
                    <div class="form-group"><label for="add_status">Status <span class="required">*</span></label><select id="add_status" name="status" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn-save">Save User</button>
                    <button type="reset" class="btn-reset">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="edit_user_modal" aria-hidden="true">
        <div class="modal">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button type="button" class="btn-close" data-close-modal="edit_user_modal">Close</button>
            </div>
            <form id="edit_user_form">
                <input type="hidden" id="edit_id" name="id">
                <div class="grid">
                    <div class="form-group"><label for="edit_employee_no">Employee No</label><input type="text" id="edit_employee_no" name="employee_no"></div>
                    <div class="form-group"><label for="edit_email">Email</label><input type="email" id="edit_email" name="email"></div>
                    <div class="form-group"><label for="edit_first_name">First Name <span class="required">*</span></label><input type="text" id="edit_first_name" name="first_name" required></div>
                    <div class="form-group"><label for="edit_middle_name">Middle Name</label><input type="text" id="edit_middle_name" name="middle_name"></div>
                    <div class="form-group"><label for="edit_last_name">Last Name <span class="required">*</span></label><input type="text" id="edit_last_name" name="last_name" required></div>
                    <div class="form-group"><label for="edit_username">Username <span class="required">*</span></label><input type="text" id="edit_username" name="username" required></div>
                    <div class="form-group"><label for="edit_password">Password</label><input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current password"></div>
                    <div class="form-group"><label for="edit_role">Role <span class="required">*</span></label><select id="edit_role" name="role" required><option value="teacher">Teacher</option><option value="admin">Admin</option></select></div>
                    <div class="form-group"><label for="edit_department">Department</label><input type="text" id="edit_department" name="department"></div>
                    <div class="form-group"><label for="edit_position">Position</label><input type="text" id="edit_position" name="position"></div>
                    <div class="form-group"><label for="edit_salary">Salary</label><input type="number" step="0.01" min="0" id="edit_salary" name="salary"></div>
                    <div class="form-group"><label for="edit_status">Status <span class="required">*</span></label><select id="edit_status" name="status" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn-save">Update User</button>
                    <button type="button" class="btn-close" data-close-modal="edit_user_modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/api_client.js"></script>
    <script src="../assets/js/live_poll.js"></script>
    <script>
        (function () {
            var wrap = document.getElementById('users_table_wrap');
            var addForm = document.getElementById('add_user_form');
            var editForm = document.getElementById('edit_user_form');
            var okEl = document.getElementById('alert_ok');
            var errEl = document.getElementById('alert_err');
            var addModal = document.getElementById('add_user_modal');
            var editModal = document.getElementById('edit_user_modal');
            var openAddModalBtn = document.getElementById('open_add_modal');
            var searchInput = document.getElementById('user_search');
            var roleFilter = document.getElementById('user_role_filter');
            var statusFilter = document.getElementById('user_status_filter');
            var clearFiltersBtn = document.getElementById('clear_user_filters');
            var currentAdminId = 0;
            var latestUsers = [];
            var editingOriginalUser = null;

            function esc(s) {
                if (s == null) return '';
                var d = document.createElement('div');
                d.textContent = String(s);
                return d.innerHTML;
            }
            function showOk(msg) {
                if (!okEl) return;
                okEl.textContent = msg || 'Done.';
                okEl.style.display = 'block';
                if (errEl) errEl.style.display = 'none';
            }
            function showErr(msg) {
                if (!errEl) return;
                errEl.textContent = msg || 'Request failed.';
                errEl.style.display = 'block';
                if (okEl) okEl.style.display = 'none';
            }
            function modalOpen(modalEl) {
                if (!modalEl) return;
                modalEl.classList.add('open');
                modalEl.setAttribute('aria-hidden', 'false');
            }
            function modalClose(modalEl) {
                if (!modalEl) return;
                modalEl.classList.remove('open');
                modalEl.setAttribute('aria-hidden', 'true');
            }
            function nullable(v) {
                return (v == null) ? '' : String(v);
            }
            function fillEditForm(user) {
                if (!editForm || !user) return;
                editingOriginalUser = user;
                editForm.elements.id.value = nullable(user.id);
                editForm.elements.employee_no.value = nullable(user.employee_no);
                editForm.elements.email.value = nullable(user.email);
                editForm.elements.first_name.value = nullable(user.first_name);
                editForm.elements.middle_name.value = nullable(user.middle_name);
                editForm.elements.last_name.value = nullable(user.last_name);
                editForm.elements.username.value = nullable(user.username);
                editForm.elements.password.value = '';
                editForm.elements.role.value = nullable(user.role || 'teacher');
                editForm.elements.department.value = nullable(user.department);
                editForm.elements.position.value = nullable(user.position);
                editForm.elements.salary.value = nullable(user.salary);
                editForm.elements.status.value = nullable(user.status || 'active');
            }
            function norm(v) {
                if (v == null) return '';
                return String(v).trim();
            }
            function normSalary(v) {
                var s = norm(v);
                if (s === '') return '';
                var n = Number(s);
                return Number.isNaN(n) ? s : n.toFixed(2);
            }
            function collectEditChanges(fd) {
                var original = editingOriginalUser || {};
                var fields = [
                    { key: 'employee_no', label: 'Employee No', normalize: norm },
                    { key: 'first_name', label: 'First Name', normalize: norm },
                    { key: 'middle_name', label: 'Middle Name', normalize: norm },
                    { key: 'last_name', label: 'Last Name', normalize: norm },
                    { key: 'email', label: 'Email', normalize: norm },
                    { key: 'username', label: 'Username', normalize: norm },
                    { key: 'role', label: 'Role', normalize: norm },
                    { key: 'department', label: 'Department', normalize: norm },
                    { key: 'position', label: 'Position', normalize: norm },
                    { key: 'salary', label: 'Salary', normalize: normSalary },
                    { key: 'status', label: 'Status', normalize: norm }
                ];
                var changes = [];
                fields.forEach(function (f) {
                    var nextRaw = fd.get(f.key);
                    var prevRaw = original[f.key];
                    var nextVal = f.normalize(nextRaw);
                    var prevVal = f.normalize(prevRaw);
                    if (nextVal !== prevVal) {
                        changes.push({
                            label: f.label,
                            from: prevVal || 'blank',
                            to: nextVal || 'blank'
                        });
                    }
                });
                var nextPassword = norm(fd.get('password'));
                if (nextPassword !== '') {
                    changes.push({
                        label: 'Password',
                        from: 'unchanged',
                        to: 'updated'
                    });
                }
                return changes;
            }
            function decodeBase64Json(b64) {
                try {
                    return JSON.parse(decodeURIComponent(escape(atob(b64))));
                } catch (e1) {
                    return JSON.parse(atob(b64));
                }
            }
            function applyUserFilters(users) {
                var q = (searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
                var role = (roleFilter && roleFilter.value ? roleFilter.value : '').trim().toLowerCase();
                var status = (statusFilter && statusFilter.value ? statusFilter.value : '').trim().toLowerCase();
                return (users || []).filter(function (u) {
                    if (role && String(u.role || '').toLowerCase() !== role) return false;
                    if (status && String(u.status || '').toLowerCase() !== status) return false;
                    if (!q) return true;
                    var haystack = [
                        u.id,
                        u.employee_no,
                        u.first_name,
                        u.middle_name,
                        u.last_name,
                        u.email,
                        u.username,
                        u.department,
                        u.position
                    ].map(function (v) {
                        return String(v || '').toLowerCase();
                    }).join(' ');
                    return haystack.indexOf(q) !== -1;
                });
            }
            function renderUsers(users) {
                latestUsers = users || [];
                var filteredUsers = applyUserFilters(latestUsers);
                if (!filteredUsers || filteredUsers.length === 0) {
                    wrap.innerHTML = '<div class="empty">No users found.</div>';
                    return;
                }
                var html = '<table><thead><tr>' +
                    '<th>ID</th><th>Employee No</th><th>Name</th><th>Email / Username</th><th>Role</th><th>Department / Position</th><th>Salary</th><th>Status</th><th>Created</th><th>Actions</th>' +
                    '</tr></thead><tbody>';
                filteredUsers.forEach(function (u) {
                    var roleBadge = (u.role === 'admin') ? 'badge-admin' : 'badge-teacher';
                    var statusBadge = (u.status === 'active') ? 'badge-active' : 'badge-inactive';
                    var nextStatus = (u.status === 'active') ? 'inactive' : 'active';
                    var btnClass = (u.status === 'active') ? 'btn-status-active' : 'btn-status-inactive';
                    var btnLabel = (u.status === 'active') ? 'Set Inactive' : 'Set Active';
                    var fullName = [u.first_name || '', u.middle_name || '', u.last_name || ''].join(' ').trim();
                    var salary = (u.salary !== null && u.salary !== '') ? Number(u.salary).toFixed(2) : '<span class="muted">—</span>';
                    var editB64 = btoa(unescape(encodeURIComponent(JSON.stringify(u))));
                    var isSelf = Number(u.id) === Number(currentAdminId);
                    var viewBtn = (u.role === 'teacher')
                        ? '<button type="button" class="btn-view" onclick="window.location.href=\'leave_requests.php?teacher_id=' + esc(u.id) + '\'">View Requests</button>'
                        : '';
                    var statusBtn = (u.role === 'admin')
                        ? '<button type="button" class="btn-status-inactive" disabled title="Admin accounts must remain active.">Always Active</button>'
                        : '<button type="button" class="' + btnClass + '" data-action="toggle_status" data-user-id="' + esc(u.id) + '" data-next-status="' + nextStatus + '">' + btnLabel + '</button>';
                    var deleteBtn = isSelf
                        ? '<button type="button" class="btn-delete" disabled title="You cannot delete your own account.">Delete</button>'
                        : '<button type="button" class="btn-delete" data-action="delete" data-user-id="' + esc(u.id) + '">Delete</button>';
                    html += '<tr>' +
                        '<td>' + esc(u.id) + '</td>' +
                        '<td>' + (u.employee_no ? esc(u.employee_no) : '<span class="muted">—</span>') + '</td>' +
                        '<td class="name-cell"><strong>' + esc(fullName) + '</strong></td>' +
                        '<td><div>' + (u.email ? esc(u.email) : '<span class="muted">—</span>') + '</div><div class="muted">@' + esc(u.username || '') + '</div></td>' +
                        '<td><span class="badge ' + roleBadge + '">' + esc(u.role || '') + '</span></td>' +
                        '<td><div>' + (u.department ? esc(u.department) : '<span class="muted">—</span>') + '</div><div class="muted">' + (u.position ? esc(u.position) : '—') + '</div></td>' +
                        '<td>' + salary + '</td>' +
                        '<td><span class="badge ' + statusBadge + '">' + esc(u.status || '') + '</span></td>' +
                        '<td>' + esc(u.created_at || '') + '</td>' +
                        '<td><div class="action-buttons">' +
                        viewBtn +
                        '<button type="button" class="btn-edit" data-action="edit" data-user-b64="' + editB64 + '">Edit</button>' +
                        statusBtn +
                        deleteBtn +
                        '</div></td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                wrap.innerHTML = html;
            }
            async function loadUsers() {
                var res = await LSApi.get('admin/users.php');
                if (!res.ok || !res.data.success) {
                    showErr((res.data && res.data.message) ? res.data.message : 'Could not load users.');
                    return;
                }
                currentAdminId = Number(res.data.current_admin_id || 0);
                renderUsers(res.data.users || []);
            }

            if (openAddModalBtn) {
                openAddModalBtn.addEventListener('click', function () {
                    modalOpen(addModal);
                });
            }
            [searchInput, roleFilter, statusFilter].forEach(function (el) {
                if (!el) return;
                el.addEventListener('input', function () {
                    renderUsers(latestUsers);
                });
                el.addEventListener('change', function () {
                    renderUsers(latestUsers);
                });
            });
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (roleFilter) roleFilter.value = '';
                    if (statusFilter) statusFilter.value = '';
                    renderUsers(latestUsers);
                });
            }
            document.addEventListener('click', function (e) {
                var closeBtn = e.target.closest('[data-close-modal]');
                if (closeBtn) {
                    var modalId = closeBtn.getAttribute('data-close-modal');
                    modalClose(document.getElementById(modalId));
                }
                if (e.target === addModal) modalClose(addModal);
                if (e.target === editModal) modalClose(editModal);
            });

            if (addForm) {
                addForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    var fd = new FormData(addForm);
                    var body = {
                        action: 'create',
                        employee_no: fd.get('employee_no') || '',
                        first_name: fd.get('first_name') || '',
                        middle_name: fd.get('middle_name') || '',
                        last_name: fd.get('last_name') || '',
                        email: fd.get('email') || '',
                        username: fd.get('username') || '',
                        password: fd.get('password') || '',
                        role: fd.get('role') || 'teacher',
                        department: fd.get('department') || '',
                        position: fd.get('position') || '',
                        salary: fd.get('salary') || '',
                        status: fd.get('status') || 'active'
                    };
                    var res = await LSApi.post('admin/users.php', body);
                    if (!res.ok || !res.data.success) {
                        showErr((res.data && res.data.message) ? res.data.message : 'Failed to save user.');
                        return;
                    }
                    showOk(res.data.message || 'User added successfully.');
                    addForm.reset();
                    currentAdminId = Number(res.data.current_admin_id || currentAdminId);
                    renderUsers(res.data.users || []);
                    modalClose(addModal);
                });
            }

            if (editForm) {
                editForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    var fd = new FormData(editForm);
                    var changes = collectEditChanges(fd);
                    if (changes.length === 0) {
                        showErr('No changes were made.');
                        return;
                    }
                    var changesText = changes.map(function (c) {
                        return '- ' + c.label + ': ' + c.from + ' -> ' + c.to;
                    }).join('\n');
                    if (!confirm('Please confirm these changes:\n\n' + changesText + '\n\nProceed with update?')) {
                        return;
                    }
                    var body = {
                        action: 'update',
                        id: Number(fd.get('id') || 0),
                        employee_no: fd.get('employee_no') || '',
                        first_name: fd.get('first_name') || '',
                        middle_name: fd.get('middle_name') || '',
                        last_name: fd.get('last_name') || '',
                        email: fd.get('email') || '',
                        username: fd.get('username') || '',
                        password: fd.get('password') || '',
                        role: fd.get('role') || 'teacher',
                        department: fd.get('department') || '',
                        position: fd.get('position') || '',
                        salary: fd.get('salary') || '',
                        status: fd.get('status') || 'active'
                    };
                    var res = await LSApi.post('admin/users.php', body);
                    if (!res.ok || !res.data.success) {
                        showErr((res.data && res.data.message) ? res.data.message : 'Failed to update user.');
                        return;
                    }
                    showOk(res.data.message || 'User updated successfully.');
                    currentAdminId = Number(res.data.current_admin_id || currentAdminId);
                    renderUsers(res.data.users || []);
                    modalClose(editModal);
                });
            }

            wrap.addEventListener('click', async function (e) {
                var btn = e.target.closest('button[data-action]');
                if (!btn) return;
                var action = btn.getAttribute('data-action');

                if (action === 'edit') {
                    try {
                        var userB64 = btn.getAttribute('data-user-b64') || '';
                        if (!userB64) throw new Error('missing user payload');
                        var userData = decodeBase64Json(userB64);
                        fillEditForm(userData);
                        modalOpen(editModal);
                    } catch (ex) {
                        showErr('Failed to open edit form.');
                    }
                    return;
                }

                if (action === 'toggle_status') {
                    var id = parseInt(btn.getAttribute('data-user-id'), 10);
                    var nextStatus = btn.getAttribute('data-next-status') || '';
                    if (!confirm('Change this user status to "' + nextStatus + '"?')) {
                        return;
                    }
                    var statusRes = await LSApi.post('admin/users.php', {
                        action: 'toggle_status',
                        id: id,
                        new_status: nextStatus
                    });
                    if (!statusRes.ok || !statusRes.data.success) {
                        showErr((statusRes.data && statusRes.data.message) ? statusRes.data.message : 'Failed to update status.');
                        return;
                    }
                    showOk(statusRes.data.message || 'Status updated.');
                    currentAdminId = Number(statusRes.data.current_admin_id || currentAdminId);
                    renderUsers(statusRes.data.users || []);
                    return;
                }

                if (action === 'delete') {
                    var deleteId = parseInt(btn.getAttribute('data-user-id'), 10);
                    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                        return;
                    }
                    var deleteRes = await LSApi.post('admin/users.php', {
                        action: 'delete',
                        id: deleteId
                    });
                    if (!deleteRes.ok || !deleteRes.data.success) {
                        showErr((deleteRes.data && deleteRes.data.message) ? deleteRes.data.message : 'Failed to delete user.');
                        return;
                    }
                    showOk(deleteRes.data.message || 'User deleted.');
                    currentAdminId = Number(deleteRes.data.current_admin_id || currentAdminId);
                    renderUsers(deleteRes.data.users || []);
                }
            });

            loadUsers();
            if (window.LSLive) {
                LSLive.pollGet('admin/users.php', 10000, function (data) {
                    currentAdminId = Number(data.current_admin_id || currentAdminId);
                    renderUsers(data.users || []);
                });
            }
        })();
    </script>
</body>
</html>