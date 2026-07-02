<?php
require '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/app_nav.php';

checkLogin();

if (!isAdmin()) {
    die('Access denied');
}

$stmt = $pdo->query("
    SELECT ls.*, u.username
    FROM locator_slips ls
    LEFT JOIN users u ON u.id = ls.user_id
    ORDER BY ls.created_at DESC
");
$slips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Locator Requests</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        --bg-color: #f8fafc;
        --accent-primary: #0f766e;
        --accent-primary-hover: #115e59;
        --card-bg: #ffffff;
        --card-border: #e2e8f0;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --input-bg: #ffffff;
        --input-border: #cbd5e1;
        --input-focus-border: #0f766e;
        --error-color: #991b1b;
        --error-bg: #fef2f2;
        --error-border: #fca5a5;
        --success-color: #166534;
        --success-bg: #f0fdf4;
        --success-border: #bbf7d0;
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        --danger-bg: #ef4444;
        --danger-hover: #dc2626;
        --info-bg: #3b82f6;
        --info-hover: #2563eb;
        --warn-bg: #eab308;
        --warn-hover: #ca8a04;
        --secondary-bg: #64748b;
        --secondary-hover: #475569;
    }

    * { box-sizing: border-box; }

    body { font-family: var(--font-main); background: var(--bg-color); margin: 0; color: var(--text-primary); }

    .container { width: 95%; max-width: 1250px; margin: 30px auto; }

    .box { background: var(--card-bg); padding: 26px; border-radius: 16px; border: 1px solid var(--card-border); box-shadow: var(--shadow-card); }

    .page-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; }
    .page-header h1 { margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.5px; }

    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }

    thead th { background: #f8fafc; padding: 14px; font-size: 12px; text-align: left; color: var(--text-secondary); border-bottom: 2px solid var(--card-border); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    tbody tr { background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease; }
    tbody tr:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }

    tbody td { padding: 16px 14px; font-size: 14px; vertical-align: middle; border-top: 1px solid var(--card-border); border-bottom: 1px solid var(--card-border); }
    tbody td:first-child { border-left: 1px solid var(--card-border); border-radius: 12px 0 0 12px; }
    tbody td:last-child { border-right: 1px solid var(--card-border); border-radius: 0 12px 12px 0; }

    .name-cell { font-weight: 600; line-height: 1.4; color: var(--text-primary); }
    .date-cell { white-space: nowrap; color: var(--text-secondary); }
    .purpose-cell, .destination-cell { max-width: 180px; word-break: break-word; color: var(--text-secondary); }

    .type-pill { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }

    .badge { display: inline-block; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
    .pending { background: var(--warn-bg); color: #854d0e; }
    .approved { background: var(--success-bg); color: var(--success-color); }
    .rejected { background: var(--error-bg); color: var(--error-color); }

    .remarks-text { color: var(--text-secondary); font-size: 13px; max-width: 180px; }
    .action-box { min-width: 150px; }
    .action-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .btn { display: inline-block; border: none; padding: 10px 16px; border-radius: 8px; color: white; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; font-family: var(--font-main); transition: background 0.15s ease; }
    .btn-print { background: #8b5cf6; }
    .btn-print:hover { background: #7c3aed; }
    .btn-approve { background: var(--accent-primary); margin-top: 0; }
    .btn-approve:hover { background: var(--accent-primary-hover); }
    .btn-reject { background: var(--danger-bg); margin-top: 0; }
    .btn-reject:hover { background: var(--danger-hover); }

    .modal { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; padding: 20px; z-index: 9999; }
    .modal.show { display: flex; }
    .modal-panel { width: 100%; max-width: 520px; background: var(--card-bg); border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); padding: 32px; box-sizing: border-box; }
    
    .modal-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--card-border); }
    .modal-title { margin: 0; font-size: 20px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.5px; }
    .modal-subtitle { margin-top: 6px; color: var(--text-secondary); font-size: 14px; line-height: 1.5; }
    .modal-close { background: var(--secondary-bg); color: #fff; border: none; border-radius: 8px; padding: 10px 16px; cursor: pointer; font-weight: 600; font-family: var(--font-main); transition: background 0.15s ease; }
    .modal-close:hover { background: var(--secondary-hover); }

    .modal-field { margin-bottom: 20px; }
    .modal-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-secondary); }
    .modal-field textarea { width: 100%; min-height: 90px; padding: 12px 14px; border: 1px solid var(--input-border); border-radius: 8px; resize: vertical; font-family: var(--font-main); font-size: 14px; box-sizing: border-box; background: var(--input-bg); outline: none; transition: all 0.15s ease; }
    .modal-field textarea:focus { border-color: var(--input-focus-border); box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12); }
    
    .modal-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
    
    .empty { text-align: center; padding: 40px; color: var(--text-secondary); font-size: 15px; }

    .page-toolbar { margin-bottom: 24px; }
    .filters { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 16px; margin-bottom: 20px; align-items: end; }
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-group label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
    .filter-group input, .filter-group select { width: 100%; min-height: 40px; padding: 10px 14px; border: 1px solid var(--input-border); border-radius: 8px; font-size: 14px; font-family: var(--font-main); background: var(--input-bg); outline: none; transition: all 0.15s ease; box-sizing: border-box; }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--input-focus-border); box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12); }
    
    .filter-actions { display: flex; align-items: end; }
    .btn-reset { border: none; border-radius: 8px; padding: 12px 18px; cursor: pointer; color: #fff; font-size: 14px; font-weight: 600; background: var(--secondary-bg); white-space: nowrap; font-family: var(--font-main); transition: background 0.15s ease; }
    .btn-reset:hover { background: var(--secondary-hover); }

    .status-nav { display: flex; flex-wrap: wrap; gap: 8px; padding-bottom: 16px; border-bottom: 1px solid var(--card-border); }
    .status-tab { display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--card-border); border-radius: 20px; padding: 8px 16px; background: #fff; color: var(--text-secondary); font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.15s ease; font-family: var(--font-main); }
    .status-tab:hover { border-color: var(--input-border); background: #f8fafc; }
    .status-tab.active { color: #fff; border-color: transparent; }
    
    .status-tab.active[data-status=""] { background: var(--accent-primary); }
    .status-tab.active[data-status="pending"] { background: var(--warn-bg); color: #fff; }
    .status-tab.active[data-status="approved"] { background: var(--success-bg); }
    .status-tab.active[data-status="rejected"] { background: var(--danger-bg); }

    .status-count { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: #f1f5f9; font-size: 12px; font-weight: 700; color: var(--text-secondary); }
    .status-tab.active .status-count { background: rgba(255, 255, 255, 0.25); color: #fff; }

    .filter-empty { display: none; text-align: center; padding: 40px; color: var(--text-secondary); font-size: 15px; }
    .filter-empty.show { display: block; }

    @media (max-width: 900px) {
        .box { padding: 20px; }
        .filters { grid-template-columns: 1fr; }
        table { min-width: 950px; }
        .filter-actions button { width: 100%; }
    }
</style>
</head>

<body>
<?php render_app_nav('admin', 'locator_requests'); ?>

<div class="container">
    <div class="box">
        <div class="page-header">
            <h1>Locator Slip Requests</h1>
        </div>

        <div class="page-toolbar">
            <div class="filters">
                <div class="filter-group">
                    <label for="locator_search">Search</label>
                    <input type="text" id="locator_search" placeholder="Name, purpose, destination...">
                </div>
                <div class="filter-group">
                    <label for="locator_type_filter">Type</label>
                    <select id="locator_type_filter">
                        <option value="">All types</option>
                        <option value="official_business">Official Business</option>
                        <option value="official_time">Official Time</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="locator_date_from">Date From</label>
                    <input type="date" id="locator_date_from">
                </div>
                <div class="filter-group">
                    <label for="locator_date_to">Date To</label>
                    <input type="date" id="locator_date_to">
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn-reset" id="clear_locator_filters">Reset</button>
                </div>
            </div>

            <nav class="status-nav" id="locator_status_nav" aria-label="Filter by status">
                <button type="button" class="status-tab active" data-status="">All <span class="status-count" id="count_all">0</span></button>
                <button type="button" class="status-tab" data-status="pending">Pending <span class="status-count" id="count_pending">0</span></button>
                <button type="button" class="status-tab" data-status="approved">Approved <span class="status-count" id="count_approved">0</span></button>
                <button type="button" class="status-tab" data-status="rejected">Rejected <span class="status-count" id="count_rejected">0</span></button>
            </nav>
        </div>

        <div class="filter-empty" id="filter_empty">No locator slip requests match your filters.</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date/Time</th>
                        <th>Purpose</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!$slips): ?>
                    <tr>
                        <td colspan="8" class="empty">No locator slip requests found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($slips as $slip): ?>
                    <?php
                    $slipDate = date('Y-m-d', strtotime($slip['date_time']));
                    $checkType = $slip['check_type'] === 'official_business' ? 'official_business' : 'official_time';
                    ?>
                    <tr class="slip-row"
                        data-status="<?= htmlspecialchars($slip['status']) ?>"
                        data-type="<?= htmlspecialchars($checkType) ?>"
                        data-date="<?= htmlspecialchars($slipDate) ?>"
                        data-search="<?= htmlspecialchars(strtolower(trim(
                            ($slip['name'] ?? '') . ' ' .
                            ($slip['username'] ?? '') . ' ' .
                            ($slip['purpose'] ?? '') . ' ' .
                            ($slip['destination'] ?? '')
                        ))) ?>">
                        <td class="name-cell"><?= htmlspecialchars($slip['name']) ?></td>

                        <td class="date-cell">
                            <?= htmlspecialchars(date('M d, Y', strtotime($slip['date_time']))) ?><br>
                            <?= htmlspecialchars(date('h:i A', strtotime($slip['date_time']))) ?>
                        </td>

                        <td class="purpose-cell"><?= htmlspecialchars($slip['purpose']) ?></td>

                        <td class="destination-cell"><?= htmlspecialchars($slip['destination']) ?></td>

                        <td>
                            <span class="type-pill">
                                <?= $slip['check_type'] === 'official_business' ? 'Official Business' : 'Official Time' ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= htmlspecialchars($slip['status']) ?>">
                                <?= ucfirst($slip['status']) ?>
                            </span>
                        </td>

                        <td>
                            <div class="remarks-text">
                                <?= !empty($slip['admin_remarks']) ? htmlspecialchars($slip['admin_remarks']) : '—' ?>
                            </div>
                        </td>

                        <td class="action-box">
                            <div class="action-row">
                                <a class="btn btn-print" href="print_locator.php?id=<?= $slip['id'] ?>" target="_blank">Print</a>
                                <?php if ($slip['status'] === 'pending'): ?>
                                    <button type="button"
                                        class="btn btn-approve open-action-modal"
                                        data-action="approve"
                                        data-id="<?= (int)$slip['id'] ?>"
                                        data-name="<?= htmlspecialchars($slip['name'], ENT_QUOTES) ?>"
                                        data-datetime="<?= htmlspecialchars(date('M d, Y h:i A', strtotime($slip['date_time'])), ENT_QUOTES) ?>"
                                        data-purpose="<?= htmlspecialchars($slip['purpose'], ENT_QUOTES) ?>"
                                        data-destination="<?= htmlspecialchars($slip['destination'], ENT_QUOTES) ?>">
                                        Approve
                                    </button>
                                    <button type="button"
                                        class="btn btn-reject open-action-modal"
                                        data-action="reject"
                                        data-id="<?= (int)$slip['id'] ?>"
                                        data-name="<?= htmlspecialchars($slip['name'], ENT_QUOTES) ?>"
                                        data-datetime="<?= htmlspecialchars(date('M d, Y h:i A', strtotime($slip['date_time'])), ENT_QUOTES) ?>"
                                        data-purpose="<?= htmlspecialchars($slip['purpose'], ENT_QUOTES) ?>"
                                        data-destination="<?= htmlspecialchars($slip['destination'], ENT_QUOTES) ?>">
                                        Reject
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="action_modal" aria-hidden="true">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="modal_title">Review Request</h2>
                <div class="modal-subtitle" id="modal_subtitle"></div>
            </div>
            <button type="button" class="modal-close" id="modal_close_btn">Close</button>
        </div>

        <form method="POST" action="../includes/locator_logic.php" id="action_form">
            <input type="hidden" name="action" id="modal_action" value="">
            <input type="hidden" name="id" id="modal_id" value="">

            <div class="modal-field">
                <label for="modal_remarks" id="modal_remarks_label">Remarks</label>
                <textarea name="admin_remarks" id="modal_remarks" placeholder="Optional remarks"></textarea>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn" id="modal_submit_btn">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.slip-row'));
    var searchInput = document.getElementById('locator_search');
    var typeFilter = document.getElementById('locator_type_filter');
    var dateFromFilter = document.getElementById('locator_date_from');
    var dateToFilter = document.getElementById('locator_date_to');
    var clearFiltersBtn = document.getElementById('clear_locator_filters');
    var statusNav = document.getElementById('locator_status_nav');
    var filterEmpty = document.getElementById('filter_empty');
    var activeStatus = '';

    function updateStatusCounts() {
        var counts = { all: 0, pending: 0, approved: 0, rejected: 0 };

        rows.forEach(function (row) {
            counts.all += 1;
            var status = String(row.dataset.status || '').toLowerCase();
            if (counts[status] != null) {
                counts[status] += 1;
            }
        });

        document.getElementById('count_all').textContent = counts.all;
        document.getElementById('count_pending').textContent = counts.pending;
        document.getElementById('count_approved').textContent = counts.approved;
        document.getElementById('count_rejected').textContent = counts.rejected;
    }

    function setActiveStatusTab(status) {
        activeStatus = status || '';
        if (!statusNav) return;

        statusNav.querySelectorAll('.status-tab').forEach(function (tab) {
            tab.classList.toggle('active', tab.dataset.status === activeStatus);
        });
    }

    function applyFilters() {
        var q = (searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
        var type = (typeFilter && typeFilter.value ? typeFilter.value : '').trim();
        var dateFrom = (dateFromFilter && dateFromFilter.value ? dateFromFilter.value : '').trim();
        var dateTo = (dateToFilter && dateToFilter.value ? dateToFilter.value : '').trim();
        var visibleCount = 0;

        rows.forEach(function (row) {
            var show = true;

            if (activeStatus && String(row.dataset.status || '').toLowerCase() !== activeStatus) {
                show = false;
            }

            if (show && type && String(row.dataset.type || '') !== type) {
                show = false;
            }

            if (show && dateFrom && String(row.dataset.date || '') < dateFrom) {
                show = false;
            }

            if (show && dateTo && String(row.dataset.date || '') > dateTo) {
                show = false;
            }

            if (show && q && String(row.dataset.search || '').indexOf(q) === -1) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
            if (show) {
                visibleCount += 1;
            }
        });

        if (filterEmpty) {
            filterEmpty.classList.toggle('show', rows.length > 0 && visibleCount === 0);
        }
    }

    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (typeFilter) typeFilter.value = '';
        if (dateFromFilter) dateFromFilter.value = '';
        if (dateToFilter) dateToFilter.value = '';
        setActiveStatusTab('');
        applyFilters();
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (typeFilter) {
        typeFilter.addEventListener('change', applyFilters);
    }
    if (dateFromFilter) {
        dateFromFilter.addEventListener('change', applyFilters);
    }
    if (dateToFilter) {
        dateToFilter.addEventListener('change', applyFilters);
    }
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', resetFilters);
    }
    if (statusNav) {
        statusNav.querySelectorAll('.status-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                setActiveStatusTab(tab.dataset.status || '');
                applyFilters();
            });
        });
    }

    updateStatusCounts();
    applyFilters();

    var modal = document.getElementById('action_modal');
    var modalTitle = document.getElementById('modal_title');
    var modalSubtitle = document.getElementById('modal_subtitle');
    var modalCloseBtn = document.getElementById('modal_close_btn');
    var actionForm = document.getElementById('action_form');
    var modalAction = document.getElementById('modal_action');
    var modalId = document.getElementById('modal_id');
    var modalRemarks = document.getElementById('modal_remarks');
    var modalRemarksLabel = document.getElementById('modal_remarks_label');
    var modalSubmitBtn = document.getElementById('modal_submit_btn');

    function openActionModal(btn) {
        var action = btn.dataset.action || '';
        var name = btn.dataset.name || '';
        var datetime = btn.dataset.datetime || '';
        var purpose = btn.dataset.purpose || '';
        var destination = btn.dataset.destination || '';

        modalAction.value = action;
        modalId.value = btn.dataset.id || '';
        modalRemarks.value = '';

        if (action === 'approve') {
            modalTitle.textContent = 'Approve Locator Slip';
            modalRemarksLabel.textContent = 'Approval remarks (optional)';
            modalRemarks.placeholder = 'Approval remarks optional';
            modalSubmitBtn.textContent = 'Approve';
            modalSubmitBtn.className = 'btn btn-approve';
        } else {
            modalTitle.textContent = 'Reject Locator Slip';
            modalRemarksLabel.textContent = 'Reason for rejection (optional)';
            modalRemarks.placeholder = 'Reason for rejection optional';
            modalSubmitBtn.textContent = 'Reject';
            modalSubmitBtn.className = 'btn btn-reject';
        }

        modalSubtitle.innerHTML =
            '<strong>' + name + '</strong><br>' +
            datetime + '<br>' +
            'Purpose: ' + purpose + '<br>' +
            'Destination: ' + destination;

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        modalRemarks.focus();
    }

    function closeActionModal() {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('.open-action-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openActionModal(btn);
        });
    });

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeActionModal);
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeActionModal();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeActionModal();
        }
    });

    if (actionForm) {
        actionForm.addEventListener('submit', function (e) {
            var action = modalAction.value;
            var label = action === 'approve' ? 'approve' : 'reject';
            if (!confirm('Are you sure you want to ' + label + ' this locator slip?')) {
                e.preventDefault();
            }
        });
    }
})();
</script>

</body>
</html>