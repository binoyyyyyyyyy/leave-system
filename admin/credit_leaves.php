<?php
require '../includes/auth.php';
require_once '../includes/app_nav.php';
checkLogin();
if (!isAdmin()) {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Credits</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; color: #333; }
        .container { width: 92%; max-width: 1250px; margin: 30px auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px; flex-wrap: wrap; }
        .topbar h1 { margin: 0; }
        .card { background: white; padding: 22px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 18px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: bold; margin-bottom: 6px; }
        input[type="number"], select, input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; background: #fff; }
        button { color: #fff; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        button:disabled { opacity: 0.65; cursor: not-allowed; }
        .btn-primary { background: #007bff; }
        .btn-save { background: #28a745; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        .btn-close { background: #6c757d; }
        .btn-reset { background: #6c757d; }
        .muted { color: #666; font-size: 13px; }
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 16px; display: none; }
        .alert.show { display: block; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 10px; }
        .stat-card { border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; background: #fafbfc; }
        .stat-card .k { font-size: 12px; color: #666; margin-bottom: 4px; }
        .stat-card .v { font-size: 18px; font-weight: bold; }
        .table-wrap { overflow-x: auto; }
        .filters { display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; margin-bottom: 12px; align-items: end; }
        .filter-actions { display: flex; align-items: end; }
        table { width: 100%; border-collapse: collapse; min-width: 1050px; font-size: 14px; }
        th, td { border-bottom: 1px solid #eee; text-align: left; padding: 10px 8px; vertical-align: top; }
        th { background: #fafafa; }
        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        .empty { text-align: center; color: #666; padding: 18px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: bold; text-transform: capitalize; }
        .badge-admin { background: #d1ecf1; color: #0c5460; }
        .badge-teacher { background: #e2e3e5; color: #383d41; }
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 999; overflow-y: auto; padding: 20px; }
        .modal-backdrop.open { display: block; }
        .modal { background: #fff; max-width: 900px; margin: 20px auto; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.2); padding: 20px; }
        .modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .modal-head h2 { margin: 0; font-size: 1.2rem; }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: 1fr; }
            .filters { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('admin', 'credits'); ?>
    <div class="container">
        <div class="topbar">
            <h1>Manage Leave Credits</h1>
            <button type="button" class="btn-primary" id="open_credit_modal">Credit Leaves</button>
        </div>

        <div class="card" style="display:none;">
            <div class="alert success" id="alert_ok" role="status"></div>
            <div class="alert error" id="alert_err" role="alert"></div>
            <p class="muted" id="load_err" style="display:none;"></p>
        </div>

        <div class="card">
            <h2 style="margin-top:0;">Users / Teachers Credit Balances</h2>
            <div class="filters">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="balance_search">Search</label>
                    <input type="text" id="balance_search" placeholder="Name, employee no, role...">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="balance_role_filter">Role</label>
                    <select id="balance_role_filter">
                        <option value="">All roles</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn-reset" id="clear_balance_filters">Reset</button>
                </div>
            </div>
            <div class="table-wrap" id="balances_table_wrap">
                <div class="empty">Loading balances...</div>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="credit_modal">
        <div class="modal">
            <div class="modal-head">
                <h2>Credit Leave Balances</h2>
                <button type="button" class="btn-close" data-close-modal="credit_modal">Close</button>
            </div>
            <form id="credit_form" action="javascript:void(0)">
                <div class="form-group">
                    <label for="teacher_id">Teacher / User *</label>
                    <select name="teacher_id" id="teacher_id" required>
                        <option value="">Loading…</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="as_of_date">As of Date *</label>
                    <input type="date" name="as_of_date" id="as_of_date" required>
                </div>
                <div class="form-group">
                    <label for="vacation_add">Vacation Leave Earned (Add) *</label>
                    <input type="number" name="vacation_add" id="vacation_add" min="0" step="0.5" value="0" required>
                    <div class="muted">Adds to earned total and available balance.</div>
                </div>
                <div class="form-group">
                    <label for="sick_add">Sick Leave Earned (Add) *</label>
                    <input type="number" name="sick_add" id="sick_add" min="0" step="0.5" value="0" required>
                    <div class="muted">Adds to earned total and available balance.</div>
                </div>
                <button type="submit" id="submit_btn" class="btn-save">Credit Leaves</button>
                <button type="reset" class="btn-reset">Reset</button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="edit_balance_modal">
        <div class="modal">
            <div class="modal-head">
                <h2>Edit Credit Balance</h2>
                <button type="button" class="btn-close" data-close-modal="edit_balance_modal">Close</button>
            </div>
            <form id="edit_balance_form" action="javascript:void(0)">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="grid">
                    <div class="form-group">
                        <label for="edit_as_of_date">As of Date *</label>
                        <input type="date" id="edit_as_of_date" name="as_of_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_vacation_total_earned">Vacation Total Earned *</label>
                        <input type="number" id="edit_vacation_total_earned" name="vacation_total_earned" min="0" step="0.5" required>
                    </div>
                    <div class="form-group">
                        <label>Vacation Used (Read-only)</label>
                        <input type="number" id="edit_vacation_used" min="0" step="0.5" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_sick_total_earned">Sick Total Earned *</label>
                        <input type="number" id="edit_sick_total_earned" name="sick_total_earned" min="0" step="0.5" required>
                    </div>
                    <div class="form-group">
                        <label>Sick Used (Read-only)</label>
                        <input type="number" id="edit_sick_used" min="0" step="0.5" readonly>
                    </div>
                </div>
                <div class="muted">Balances are auto-calculated as <strong>Total Earned - Used</strong> and cannot go below 0.</div>
                <button type="submit" class="btn-save">Save Changes</button>
                <button type="button" class="btn-close" data-close-modal="edit_balance_modal">Cancel</button>
            </form>
        </div>
    </div>
    <script src="../assets/js/api_client.js"></script>
    <script src="../assets/js/live_poll.js"></script>
    <script>
        (function () {
            var form = document.getElementById('credit_form');
            var editForm = document.getElementById('edit_balance_form');
            var okEl = document.getElementById('alert_ok');
            var errEl = document.getElementById('alert_err');
            var loadErr = document.getElementById('load_err');
            var teacherSel = document.getElementById('teacher_id');
            var tableWrap = document.getElementById('balances_table_wrap');
            var asOf = document.getElementById('as_of_date');
            var balanceSearch = document.getElementById('balance_search');
            var balanceRoleFilter = document.getElementById('balance_role_filter');
            var clearBalanceFiltersBtn = document.getElementById('clear_balance_filters');
            var openCreditModalBtn = document.getElementById('open_credit_modal');
            var creditModal = document.getElementById('credit_modal');
            var editModal = document.getElementById('edit_balance_modal');
            var defaultAsOfDate = '';
            var usersCache = [];
            var balancesCache = [];

            function hideAlerts() {
                okEl.classList.remove('show');
                errEl.classList.remove('show');
                okEl.textContent = '';
                errEl.textContent = '';
            }
            function esc(s) {
                if (s == null) return '';
                var d = document.createElement('div');
                d.textContent = String(s);
                return d.innerHTML;
            }
            function roleBadge(role) {
                var cls = role === 'admin' ? 'badge-admin' : 'badge-teacher';
                return '<span class="badge ' + cls + '">' + esc(role || '') + '</span>';
            }
            function fmtN(v) {
                var n = Number(v || 0);
                if (Number.isNaN(n)) n = 0;
                return n.toFixed(2);
            }
            function modalOpen(el) {
                if (!el) return;
                el.classList.add('open');
            }
            function modalClose(el) {
                if (!el) return;
                el.classList.remove('open');
            }
            function userDisplayName(u) {
                var emp = u.employee_no ? ' (' + u.employee_no + ')' : '';
                return (u.last_name || '') + ', ' + (u.first_name || '') + emp;
            }
            function fillUserOptions(users) {
                teacherSel.innerHTML = '<option value="">Choose user</option>';
                users.forEach(function (u) {
                    var label = userDisplayName(u);
                    var o1 = document.createElement('option');
                    o1.value = String(u.id);
                    o1.textContent = label;
                    teacherSel.appendChild(o1);
                });
            }
            function renderBalancesTable(rows) {
                var query = (balanceSearch && balanceSearch.value ? balanceSearch.value : '').trim().toLowerCase();
                var role = (balanceRoleFilter && balanceRoleFilter.value ? balanceRoleFilter.value : '').trim().toLowerCase();
                var filteredRows = (rows || []).filter(function (r) {
                    if (role && String(r.role || '').toLowerCase() !== role) return false;
                    if (!query) return true;
                    var haystack = [
                        r.first_name,
                        r.last_name,
                        r.employee_no,
                        r.role,
                        r.as_of_date
                    ].map(function (v) { return String(v || '').toLowerCase(); }).join(' ');
                    return haystack.indexOf(query) !== -1;
                });
                if (!filteredRows || filteredRows.length === 0) {
                    tableWrap.innerHTML = '<div class="empty">No users found.</div>';
                    return;
                }
                var html = '<table><thead><tr>' +
                    '<th>User</th><th>Role</th><th>As Of</th><th>Vacation Total</th><th>Vacation Used</th><th>Vacation Balance</th><th>Sick Total</th><th>Sick Used</th><th>Sick Balance</th><th>Actions</th>' +
                    '</tr></thead><tbody>';
                filteredRows.forEach(function (r) {
                    var hasCredit = !!r.credit_id;
                    var editB64 = btoa(unescape(encodeURIComponent(JSON.stringify(r))));
                    html += '<tr>' +
                        '<td><strong>' + esc((r.last_name || '') + ', ' + (r.first_name || '')) + '</strong><div class="muted">' + (r.employee_no ? esc(r.employee_no) : '—') + '</div></td>' +
                        '<td>' + roleBadge(r.role) + '</td>' +
                        '<td>' + esc(r.as_of_date || '—') + '</td>' +
                        '<td>' + (hasCredit ? fmtN(r.vacation_total_earned) : '<span class="muted">—</span>') + '</td>' +
                        '<td>' + (hasCredit ? fmtN(r.vacation_less_this_application) : '<span class="muted">—</span>') + '</td>' +
                        '<td>' + (hasCredit ? fmtN(r.vacation_balance) : '<span class="muted">—</span>') + '</td>' +
                        '<td>' + (hasCredit ? fmtN(r.sick_total_earned) : '<span class="muted">—</span>') + '</td>' +
                        '<td>' + (hasCredit ? fmtN(r.sick_less_this_application) : '<span class="muted">—</span>') + '</td>' +
                        '<td>' + (hasCredit ? fmtN(r.sick_balance) : '<span class="muted">—</span>') + '</td>' +
                        '<td><div class="action-buttons">' +
                        '<button type="button" class="btn-edit" data-action="edit" data-row-b64="' + editB64 + '">Edit</button>' +
                        (hasCredit ? '<button type="button" class="btn-delete" data-action="delete" data-user-id="' + esc(r.user_id) + '">Delete</button>' : '') +
                        '</div></td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                tableWrap.innerHTML = html;
            }
            [balanceSearch, balanceRoleFilter].forEach(function (el) {
                if (!el) return;
                el.addEventListener('input', function () {
                    renderBalancesTable(balancesCache);
                });
                el.addEventListener('change', function () {
                    renderBalancesTable(balancesCache);
                });
            });
            if (clearBalanceFiltersBtn) {
                clearBalanceFiltersBtn.addEventListener('click', function () {
                    if (balanceSearch) balanceSearch.value = '';
                    if (balanceRoleFilter) balanceRoleFilter.value = '';
                    renderBalancesTable(balancesCache);
                });
            }
            function decodeB64Json(b64) {
                try {
                    return JSON.parse(decodeURIComponent(escape(atob(b64))));
                } catch (e1) {
                    return JSON.parse(atob(b64));
                }
            }
            function fillEditForm(row) {
                editForm.elements.user_id.value = String(row.user_id || '');
                editForm.elements.as_of_date.value = row.as_of_date || defaultAsOfDate || '';
                editForm.elements.vacation_total_earned.value = fmtN(row.vacation_total_earned);
                document.getElementById('edit_vacation_used').value = fmtN(row.vacation_less_this_application);
                editForm.elements.sick_total_earned.value = fmtN(row.sick_total_earned);
                document.getElementById('edit_sick_used').value = fmtN(row.sick_less_this_application);
            }

            function applyData(data) {
                usersCache = data.users || [];
                balancesCache = data.balances || [];
                defaultAsOfDate = data.default_as_of_date || defaultAsOfDate;
                fillUserOptions(usersCache);
                renderBalancesTable(balancesCache);
                if (!asOf.value) asOf.value = defaultAsOfDate;
            }

            async function loadData() {
                var res = await LSApi.get('admin/credit_leaves.php');
                if (!res.ok || !res.data.success) {
                    loadErr.style.display = 'block';
                    loadErr.textContent = (res.data && res.data.message) ? res.data.message : 'Could not load teachers.';
                    teacherSel.innerHTML = '<option value="">Error</option>';
                    tableWrap.innerHTML = '<div class="empty">Failed to load balances.</div>';
                    return;
                }
                loadErr.style.display = 'none';
                applyData(res.data);
            }
            loadData();
            if (window.LSLive) {
                LSLive.pollGet('admin/credit_leaves.php', 12000, function (data) {
                    var currentCreditUser = teacherSel.value;
                    applyData(data);
                    if (currentCreditUser) teacherSel.value = currentCreditUser;
                });
            }
            if (openCreditModalBtn) {
                openCreditModalBtn.addEventListener('click', function () {
                    modalOpen(creditModal);
                });
            }
            document.addEventListener('click', function (e) {
                var c = e.target.closest('[data-close-modal]');
                if (c) modalClose(document.getElementById(c.getAttribute('data-close-modal')));
                if (e.target === creditModal) modalClose(creditModal);
                if (e.target === editModal) modalClose(editModal);
            });
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                hideAlerts();
                var btn = document.getElementById('submit_btn');
                btn.disabled = true;
                var body = {
                    action: 'credit',
                    teacher_id: parseInt(teacherSel.value, 10),
                    as_of_date: asOf.value,
                    vacation_add: parseFloat(document.getElementById('vacation_add').value) || 0,
                    sick_add: parseFloat(document.getElementById('sick_add').value) || 0
                };
                var res = await LSApi.post('admin/credit_leaves.php', body);
                if (res.data && res.data.success) {
                    okEl.textContent = res.data.message || 'Saved.';
                    okEl.classList.add('show');
                    document.getElementById('vacation_add').value = '0';
                    document.getElementById('sick_add').value = '0';
                    balancesCache = res.data.balances || balancesCache;
                    renderBalancesTable(balancesCache);
                    modalClose(creditModal);
                } else {
                    errEl.textContent = (res.data && res.data.message) ? res.data.message : 'Failed.';
                    errEl.classList.add('show');
                }
                btn.disabled = false;
            });
            editForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                hideAlerts();
                var fd = new FormData(editForm);
                var body = {
                    action: 'update_balance',
                    user_id: parseInt(fd.get('user_id'), 10),
                    as_of_date: fd.get('as_of_date') || '',
                    vacation_total_earned: fd.get('vacation_total_earned') || '0',
                    sick_total_earned: fd.get('sick_total_earned') || '0'
                };
                var res = await LSApi.post('admin/credit_leaves.php', body);
                if (!res.ok || !res.data.success) {
                    errEl.textContent = (res.data && res.data.message) ? res.data.message : 'Failed to update balance.';
                    errEl.classList.add('show');
                    return;
                }
                okEl.textContent = res.data.message || 'Balance updated.';
                okEl.classList.add('show');
                balancesCache = res.data.balances || balancesCache;
                renderBalancesTable(balancesCache);
                modalClose(editModal);
            });
            tableWrap.addEventListener('click', async function (e) {
                var btn = e.target.closest('button[data-action]');
                if (!btn) return;
                var action = btn.getAttribute('data-action');
                hideAlerts();
                if (action === 'edit') {
                    try {
                        var row = decodeB64Json(btn.getAttribute('data-row-b64') || '');
                        if (!row.as_of_date) row.as_of_date = defaultAsOfDate || '';
                        fillEditForm(row);
                        modalOpen(editModal);
                    } catch (ex) {
                        errEl.textContent = 'Failed to open edit balance form.';
                        errEl.classList.add('show');
                    }
                    return;
                }
                if (action === 'delete') {
                    var userId = parseInt(btn.getAttribute('data-user-id'), 10);
                    if (!confirm('Delete this credit record?')) return;
                    var res = await LSApi.post('admin/credit_leaves.php', {
                        action: 'delete_balance',
                        user_id: userId
                    });
                    if (!res.ok || !res.data.success) {
                        errEl.textContent = (res.data && res.data.message) ? res.data.message : 'Failed to delete balance.';
                        errEl.classList.add('show');
                        return;
                    }
                    okEl.textContent = res.data.message || 'Balance deleted.';
                    okEl.classList.add('show');
                    balancesCache = res.data.balances || balancesCache;
                    renderBalancesTable(balancesCache);
                }
            });

        })();
    </script>
</body>
</html>
