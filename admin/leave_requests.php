<?php
require '../includes/auth.php';
require '../includes/app_nav.php';

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
    <title>Leave Requests</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #111827;
        }

        .container {
            width: 98%;
            max-width: 1600px;
            margin: 25px auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .topbar h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            color: #111827;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .scroll {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        thead th {
            background: #075db3;
            color: #fff;
            padding: 15px 12px;
            text-align: left;
            font-size: 15px;
            font-weight: 700;
            white-space: nowrap;
        }

        tbody td {
            padding: 13px 12px;
            border-bottom: 1px solid #d9dee7;
            font-size: 15px;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background: #eeeeee;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        tbody tr:hover {
            background: #eaf4ff;
        }

        .teacher-name {
            font-weight: 700;
            color: #111827;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
            margin-top: 3px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-transform: lowercase;
            white-space: nowrap;
        }

        .badge.pending {
            background: #ffb000;
        }

        .badge.approved {
            background: #28a745;
        }

        .badge.rejected {
            background: #dc3545;
        }

        .badge.default {
            background: #6c757d;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: nowrap;
        }

        .icon-btn {
            width: 31px;
            height: 31px;
            border-radius: 5px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
            transition: 0.2s ease;
        }

        .view-btn {
            border: 1px solid #00bfff;
            color: #00a6d6;
        }

        .edit-btn {
            border: 1px solid #ffb000;
            color: #ff9800;
        }

        .delete-btn {
            border: 1px solid #ff4d5e;
            color: #ff3045;
        }

        .icon-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin: 15px;
            display: none;
            font-weight: 600;
        }

        .alert.show {
            display: block;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .page-toolbar {
            padding: 16px 15px 0;
        }

        .filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 14px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        .filter-group input,
        .filter-group select {
            min-height: 38px;
            font-size: 14px;
        }

        .filter-actions {
            display: flex;
            align-items: end;
        }

        .btn-reset {
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            background: #6c757d;
            white-space: nowrap;
        }

        .status-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .status-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 14px;
            background: #fff;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .status-tab:hover {
            border-color: #075db3;
            color: #075db3;
        }

        .status-tab.active {
            color: #fff;
            border-color: transparent;
        }

        .status-tab.active[data-status=""] {
            background: #075db3;
        }

        .status-tab.active[data-status="pending"] {
            background: #ffb000;
        }

        .status-tab.active[data-status="approved"] {
            background: #28a745;
        }

        .status-tab.active[data-status="rejected"] {
            background: #dc3545;
        }

        .status-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.08);
            font-size: 12px;
            font-weight: 700;
        }

        .status-tab.active .status-count {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .live-hint {
            font-size: 12px;
            color: #6b7280;
            margin: 12px 15px;
        }

        .empty-state {
            padding: 25px;
            color: #6b7280;
            font-size: 15px;
        }

        input,
        textarea,
        select {
            width: 100%;
            min-height: 38px;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 13px;
            background: #fff;
        }

        textarea {
            min-height: 70px;
            resize: vertical;
        }

        input[readonly],
        textarea[readonly] {
            background: #f5f5f5;
            color: #444;
            cursor: not-allowed;
        }

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
        }

        .modal.show {
            display: flex;
        }

        .modal-panel {
            width: 100%;
            max-width: 980px;
            max-height: 92vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 22px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .modal-title {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .modal-subtitle {
            margin-top: 5px;
            color: #666;
            font-size: 14px;
        }

        .modal-close {
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
        }

        .section-box {
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            padding: 13px;
            margin-bottom: 12px;
            background: #fcfcfc;
        }

        .section-title {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 10px;
            color: #333;
            text-transform: uppercase;
        }

        .mini-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .mini-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .field-label {
            font-size: 12px;
            color: #555;
            margin-bottom: 4px;
            display: block;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        button.action {
            border: none;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
            font-weight: 600;
        }

        .approve {
            background: #28a745;
        }

        .reject {
            background: #dc3545;
        }

        .update {
            background: #007bff;
        }

        .print-btn {
            background: #17a2b8;
        }

        @media (max-width: 900px) {
            .mini-grid-2,
            .mini-grid-3 {
                grid-template-columns: 1fr;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            table {
                min-width: 950px;
            }
        }
    </style>
</head>

<body data-api-base="../api/">

    <?php render_app_nav('admin', 'requests'); ?>

    <div class="container">
        <div class="topbar">
            <h1>Leave Requests</h1>
        </div>

        <div class="card">
            <div class="page-toolbar">
                <div class="filters">
                    <div class="filter-group">
                        <label for="request_search">Search</label>
                        <input type="text" id="request_search" placeholder="Teacher name, email...">
                    </div>
                    <div class="filter-group">
                        <label for="leave_type_filter">Leave Type</label>
                        <select id="leave_type_filter">
                            <option value="">All leave types</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date_from_filter">Date From</label>
                        <input type="date" id="date_from_filter">
                    </div>
                    <div class="filter-group">
                        <label for="date_to_filter">Date To</label>
                        <input type="date" id="date_to_filter">
                    </div>
                    <div class="filter-actions">
                        <button type="button" class="btn-reset" id="clear_request_filters">Reset</button>
                    </div>
                </div>

                <nav class="status-nav" id="status_nav" aria-label="Filter by status">
                    <button type="button" class="status-tab active" data-status="">All <span class="status-count" id="count_all">0</span></button>
                    <button type="button" class="status-tab" data-status="pending">Pending <span class="status-count" id="count_pending">0</span></button>
                    <button type="button" class="status-tab" data-status="approved">Approved <span class="status-count" id="count_approved">0</span></button>
                    <button type="button" class="status-tab" data-status="rejected">Rejected <span class="status-count" id="count_rejected">0</span></button>
                </nav>
            </div>

            <div class="alert success" id="alert_ok"></div>
            <div class="alert error" id="alert_err"></div>

            <p class="muted" id="load_err" style="display:none; padding:15px;"></p>

            <p class="live-hint" id="live_hint" style="display:none;">
                This list refreshes automatically every few seconds while this tab is open.
            </p>

            <div class="scroll" id="table_wrap">Loading…</div>
        </div>
    </div>

    <div class="modal" id="action_modal" aria-hidden="true">
        <div class="modal-panel">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="modal_title">Action Details</h2>
                    <div class="modal-subtitle" id="modal_subtitle"></div>
                </div>
                <button type="button" class="modal-close" id="modal_close_btn">Close</button>
            </div>

            <div id="modal_body"></div>
            <div class="modal-actions" id="modal_actions"></div>
        </div>
    </div>

    <script src="../assets/js/api_client.js"></script>
    <script src="../assets/js/live_poll.js"></script>

    <script>
        (function () {
            var wrap = document.getElementById('table_wrap');
            var okEl = document.getElementById('alert_ok');
            var errEl = document.getElementById('alert_err');
            var loadErr = document.getElementById('load_err');
            var liveHint = document.getElementById('live_hint');

            var modal = document.getElementById('action_modal');
            var modalBody = document.getElementById('modal_body');
            var modalActions = document.getElementById('modal_actions');
            var modalTitle = document.getElementById('modal_title');
            var modalSubtitle = document.getElementById('modal_subtitle');
            var modalCloseBtn = document.getElementById('modal_close_btn');

            var latestRequests = [];
            var currentModalId = null;
            var activeStatus = '';

            var searchInput = document.getElementById('request_search');
            var leaveTypeFilter = document.getElementById('leave_type_filter');
            var dateFromFilter = document.getElementById('date_from_filter');
            var dateToFilter = document.getElementById('date_to_filter');
            var clearFiltersBtn = document.getElementById('clear_request_filters');
            var statusNav = document.getElementById('status_nav');
            var teacherIdFromUrl = (function () {
                var params = new URLSearchParams(window.location.search);
                var id = parseInt(params.get('teacher_id') || '', 10);
                return Number.isFinite(id) && id > 0 ? id : null;
            })();

            function esc(s) {
                if (s == null) return '';
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function hideAlerts() {
                okEl.classList.remove('show');
                errEl.classList.remove('show');
                okEl.textContent = '';
                errEl.textContent = '';
            }

            function getVal(id) {
                var el = document.getElementById(id);
                return el ? el.value.trim() : '';
            }

            function savedFields() {
                var data = {};
                document.querySelectorAll('[data-save]').forEach(function (el) {
                    data[el.id] = el.value;
                });
                return data;
            }

            function restoreFields(saved) {
                Object.keys(saved).forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el && !el.hasAttribute('readonly')) {
                        el.value = saved[id];
                    }
                });
            }

            function updateStatusCounts(requests) {
                var counts = {
                    all: 0,
                    pending: 0,
                    approved: 0,
                    rejected: 0
                };

                (requests || []).forEach(function (req) {
                    counts.all += 1;
                    var status = String(req.status || '').toLowerCase();
                    if (counts[status] != null) {
                        counts[status] += 1;
                    }
                });

                document.getElementById('count_all').textContent = counts.all;
                document.getElementById('count_pending').textContent = counts.pending;
                document.getElementById('count_approved').textContent = counts.approved;
                document.getElementById('count_rejected').textContent = counts.rejected;
            }

            function populateLeaveTypeFilter(requests) {
                if (!leaveTypeFilter) return;

                var selected = leaveTypeFilter.value;
                var types = {};

                (requests || []).forEach(function (req) {
                    var name = String(req.leave_name || '').trim();
                    if (name) {
                        types[name] = true;
                    }
                });

                var names = Object.keys(types).sort(function (a, b) {
                    return a.localeCompare(b);
                });

                leaveTypeFilter.innerHTML = '<option value="">All leave types</option>';
                names.forEach(function (name) {
                    var opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;
                    leaveTypeFilter.appendChild(opt);
                });

                if (selected && types[selected]) {
                    leaveTypeFilter.value = selected;
                }
            }

            function setActiveStatusTab(status) {
                activeStatus = status || '';
                if (!statusNav) return;

                statusNav.querySelectorAll('.status-tab').forEach(function (tab) {
                    tab.classList.toggle('active', tab.dataset.status === activeStatus);
                });
            }

            function applyRequestFilters(requests) {
                var q = (searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
                var leaveType = (leaveTypeFilter && leaveTypeFilter.value ? leaveTypeFilter.value : '').trim().toLowerCase();
                var dateFrom = (dateFromFilter && dateFromFilter.value ? dateFromFilter.value : '').trim();
                var dateTo = (dateToFilter && dateToFilter.value ? dateToFilter.value : '').trim();

                return (requests || []).filter(function (req) {
                    if (teacherIdFromUrl && parseInt(req.teacher_id, 10) !== teacherIdFromUrl) {
                        return false;
                    }

                    if (activeStatus && String(req.status || '').toLowerCase() !== activeStatus) {
                        return false;
                    }

                    if (leaveType && String(req.leave_name || '').trim().toLowerCase() !== leaveType) {
                        return false;
                    }

                    if (dateFrom && String(req.date_from || '') < dateFrom) {
                        return false;
                    }

                    if (dateTo && String(req.date_to || '') > dateTo) {
                        return false;
                    }

                    if (!q) {
                        return true;
                    }

                    var haystack = [
                        req.first_name,
                        req.middle_name,
                        req.last_name,
                        req.email,
                        req.leave_name
                    ].map(function (v) {
                        return String(v || '').toLowerCase();
                    }).join(' ');

                    return haystack.indexOf(q) !== -1;
                });
            }

            function refreshTable() {
                render(latestRequests);
            }

            function resetFilters() {
                if (searchInput) searchInput.value = '';
                if (leaveTypeFilter) leaveTypeFilter.value = '';
                if (dateFromFilter) dateFromFilter.value = '';
                if (dateToFilter) dateToFilter.value = '';
                teacherIdFromUrl = null;
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', window.location.pathname);
                }
                setActiveStatusTab('');
                refreshTable();
            }

            function statusBadge(status) {
                status = status || '';

                if (status === 'pending') {
                    return '<span class="badge pending">⌛ pending</span>';
                }

                if (status === 'approved') {
                    return '<span class="badge approved">✓ approved</span>';
                }

                if (status === 'rejected') {
                    return '<span class="badge rejected">✕ rejected</span>';
                }

                return '<span class="badge default">' + esc(status) + '</span>';
            }

            function openModal(req, viewOnly) {

    viewOnly = !!viewOnly;
                currentModalId = parseInt(req.id, 10);

                var name = (req.last_name || '') + ', ' + (req.first_name || '');
var leaveName = req.leave_name || '';
var dates = (req.date_from || '') + ' to ' + (req.date_to || '');
var status = req.status || '';

/*
    Only allow editing of Reason / Basis
    when the request is rejected.
*/
var reasonReadonly = (status !== 'rejected') ? 'readonly' : '';

                modalTitle.textContent = 'Action Details';
                modalSubtitle.innerHTML =
                    '<strong>' + esc(name) + '</strong> — ' +
                    esc(leaveName) + ' | ' + esc(dates) + ' | ' + statusBadge(status);

                modalBody.innerHTML =
                    '<div class="section-box">' +
                        '<div class="section-title">7A Certification of Leave Credits</div>' +

                        '<label class="field-label">As of</label>' +
                        '<input readonly type="date" id="credits_as_of_' + currentModalId + '" value="' + esc(req.credits_as_of || '') + '">' +

                        '<div class="mini-grid-3" style="margin-top:10px;">' +
                            '<div>' +
                                '<label class="field-label">Vacation Total Earned</label>' +
                                '<input readonly type="number" step="0.01" value="' + esc(req.vacation_total_earned || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Vacation Less This Application</label>' +
                                '<input readonly type="number" step="0.01" value="' + esc(req.vacation_less_this_application || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Vacation Balance</label>' +
                                '<input readonly type="number" step="0.01" value="' + esc(req.vacation_balance || '') + '">' +
                            '</div>' +
                        '</div>' +

                        '<div class="mini-grid-3" style="margin-top:10px;">' +
                            '<div>' +
                                '<label class="field-label">Sick Total Earned</label>' +
                                '<input readonly type="number" step="0.01" value="' + esc(req.sick_total_earned || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Sick Less This Application</label>' +
                                '<input readonly type="number" step="0.01" value="' + esc(req.sick_less_this_application || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Sick Balance</label>' +
                                '<input readonly type="number" step="0.01" value="' + esc(req.sick_balance || '') + '">' +
                            '</div>' +
                        '</div>' +

                        '<div class="mini-grid-2" style="margin-top:10px;">' +
                            '<div>' +
                                '<label class="field-label">Certification Officer Name</label>' +
                                '<input data-save type="text" autocomplete="off" id="certification_officer_name_' + currentModalId + '" value="' + esc(req.certification_officer_name || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Certification Officer Position</label>' +
                                '<input data-save type="text" autocomplete="off" id="certification_officer_position_' + currentModalId + '" value="' + esc(req.certification_officer_position || '') + '">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<div class="section-box">' +
                        '<div class="section-title">7B Recommendation</div>' +
                        '<input readonly type="text" value="' + esc(req.recommendation || '') + '">' +

                        '<label class="field-label" style="margin-top:10px;">Reason / Basis</label>' +
'<textarea data-save ' + reasonReadonly +
' id="recommendation_reason_' + currentModalId + '">' +
    esc(req.recommendation_reason || '') +
'</textarea>' +
                        '<div class="mini-grid-2" style="margin-top:10px;">' +
                            '<div>' +
                                '<label class="field-label">Recommending Officer Name</label>' +
                                '<input data-save type="text" autocomplete="off" id="recommendation_name_' + currentModalId + '" value="' + esc(req.recommendation_name || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Recommending Officer Position</label>' +
                                '<input data-save type="text" autocomplete="off" id="recommendation_position_' + currentModalId + '" value="' + esc(req.recommendation_position || '') + '">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<div class="section-box">' +
                        '<div class="section-title">7C Approved For</div>' +
                        '<div class="mini-grid-3">' +
                            '<div>' +
                                '<label class="field-label">Days With Pay</label>' +
                                '<input data-save type="number" step="0.01" id="days_with_pay_' + currentModalId + '" value="' + esc(req.days_with_pay || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Days Without Pay</label>' +
                                '<input data-save type="number" step="0.01" id="days_without_pay_' + currentModalId + '" value="' + esc(req.days_without_pay || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Others Specify</label>' +
                                '<input data-save type="text" id="others_specify_' + currentModalId + '" value="' + esc(req.others_specify || '') + '">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<div class="section-box">' +
                        '<div class="section-title">7D Disapproved Due To</div>' +
                        '<textarea data-save id="disapproved_due_to_' + currentModalId + '">' + esc(req.disapproved_due_to || '') + '</textarea>' +

                        '<div class="mini-grid-2" style="margin-top:10px;">' +
                            '<div>' +
                                '<label class="field-label">Authorized Official Name</label>' +
                                '<input data-save type="text" id="final_action_name_' + currentModalId + '" value="' + esc(req.final_action_name || '') + '">' +
                            '</div>' +
                            '<div>' +
                                '<label class="field-label">Authorized Official Position</label>' +
                                '<input data-save type="text" id="final_action_position_' + currentModalId + '" value="' + esc(req.final_action_position || '') + '">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<div class="section-box">' +
                        '<div class="section-title">Admin Remarks</div>' +
                        '<textarea data-save id="admin_remarks_' + currentModalId + '">' + esc(req.admin_remarks || '') + '</textarea>' +
                    '</div>';

                var actionsHtml = '';
                actionsHtml += '<button type="button" class="action print-btn" data-print-id="' + currentModalId + '">Print Form</button>';

                if (status === 'pending') {
                    actionsHtml += '<button type="button" class="action approve" data-modal-action="approve">Approve</button>';
                    actionsHtml += '<button type="button" class="action reject" data-modal-action="reject">Reject</button>';
                } else if (status === 'approved') {
                    actionsHtml += '<button type="button" class="action update" data-modal-action="update_action">Update Action</button>';
                    actionsHtml += '<button type="button" class="action reject" data-modal-action="reject">Reject / Refund</button>';
                } else if (status === 'rejected') {
                    actionsHtml += '<button type="button" class="action update" data-modal-action="update_action">Update Action</button>';
                }

                modalActions.innerHTML = actionsHtml;

                var printBtn = modalActions.querySelector('button[data-print-id]');
                if (printBtn) {
                    printBtn.addEventListener('click', function () {
                        window.open('print_leave_form.php?id=' + currentModalId, '_blank');
                    });
                }

                modalActions.querySelectorAll('button[data-modal-action]').forEach(function (btn) {
                    btn.addEventListener('click', async function () {
                        await submitModalAction(btn.getAttribute('data-modal-action'), btn);
                    });
                });

                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
            }
            

            function closeModal() {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                modalBody.innerHTML = '';
                modalActions.innerHTML = '';
                currentModalId = null;
            }

            async function submitModalAction(action, btn) {
                if (!currentModalId) return;
                var confirmMessage = '';

if (action === 'approve') {
    confirmMessage =
        'Are you sure you want to APPROVE this leave request?';
}
else if (action === 'reject') {
    confirmMessage =
        'Are you sure you want to REJECT this leave request?';
}
else if (action === 'update_action') {
    confirmMessage =
        'Save changes to this leave request?';
}
else if (action === 'soft_delete') {
    confirmMessage =
        'Delete this leave request?\n\nThis is a soft delete.';
}

if (
    confirmMessage &&
    !window.confirm(confirmMessage)
) {
    return;
}

                hideAlerts();
                btn.disabled = true;

                var payload = {
                    application_id: currentModalId,
                    action: action,
                    recommendation_reason: getVal('recommendation_reason_' + currentModalId),
                    recommendation_name: getVal('recommendation_name_' + currentModalId),
                    recommendation_position: getVal('recommendation_position_' + currentModalId),
                    certification_officer_name: getVal('certification_officer_name_' + currentModalId),
                    certification_officer_position: getVal('certification_officer_position_' + currentModalId),
                    days_with_pay: getVal('days_with_pay_' + currentModalId),
                    days_without_pay: getVal('days_without_pay_' + currentModalId),
                    others_specify: getVal('others_specify_' + currentModalId),
                    disapproved_due_to: getVal('disapproved_due_to_' + currentModalId),
                    final_action_name: getVal('final_action_name_' + currentModalId),
                    final_action_position: getVal('final_action_position_' + currentModalId),
                    admin_remarks: getVal('admin_remarks_' + currentModalId)
                };

                var res = await LSApi.post('admin/leave_requests.php', payload);

                if (res.data && res.data.success) {
                    okEl.textContent = res.data.message || 'Done.';
                    okEl.classList.add('show');

                    latestRequests = res.data.requests || [];
                    render(latestRequests);

                    var updatedReq = latestRequests.find(function (r) {
                        return parseInt(r.id, 10) === currentModalId;
                    });

                    if (updatedReq) {
                        openModal(updatedReq);
                    } else {
                        closeModal();
                    }
                } else {
                    errEl.textContent = res.data && res.data.message ? res.data.message : 'Request failed.';
                    errEl.classList.add('show');
                }

                btn.disabled = false;
            }

            function render(requests) {
                var saved = savedFields();
                latestRequests = requests || [];

                populateLeaveTypeFilter(latestRequests);
                updateStatusCounts(latestRequests);

                if (!latestRequests.length) {
                    wrap.innerHTML = '<div class="empty-state">No leave requests to review.</div>';
                    return;
                }

                var filteredRequests = applyRequestFilters(latestRequests);

                if (!filteredRequests || filteredRequests.length === 0) {
                    wrap.innerHTML = '<div class="empty-state">No leave requests match your filters.</div>';
                    return;
                }

                var html =
                    '<table>' +
                        '<thead>' +
                            '<tr>' +
                                '<th>Teacher</th>' +
                                '<th>Leave Type</th>' +
                                '<th>Date Requested</th>' +
                                '<th>Date From</th>' +
                                '<th>Date To</th>' +
                                '<th>Days</th>' +
                                '<th>Status</th>' +
                                '<th>Actions</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';

                filteredRequests.forEach(function (req) {
                    var id = parseInt(req.id, 10);
                    var name = esc((req.last_name || '') + ', ' + (req.first_name || ''));
                    var half = parseInt(req.is_half_day, 10) === 1 ? ' half-day' : '';
                    var days = esc(String(req.working_days_applied || '0')) + half;

                    html +=
                        '<tr>' +
                            '<td>' +
                                '<div class="teacher-name">' + name + '</div>' +
                                '<div class="muted">' + esc(req.email || '') + '</div>' +
                            '</td>' +

                            '<td>' + esc(req.leave_name || '') + '</td>' +

                            '<td>' + esc(req.created_at || req.date_requested || 'N/A') + '</td>' +

                            '<td>' + esc(req.date_from || '') + '</td>' +

                            '<td>' + esc(req.date_to || '') + '</td>' +

                            '<td>' + days + '</td>' +

                            '<td>' + statusBadge(req.status || '') + '</td>' +

                            '<td>' +
    '<div class="actions">' +

        '<button type="button" class="icon-btn view-btn" ' +
        'title="View" data-view-id="' + id + '">👁</button>' +

        '<button type="button" class="icon-btn edit-btn" ' +
        'title="Update" data-edit-id="' + id + '">✏️</button>' +

        '<button type="button" class="icon-btn delete-btn" ' +
        'title="Delete" data-delete-id="' + id + '">🗑️</button>' +

    '</div>' +
'</td>'+
                        '</tr>';
                });

                html += '</tbody></table>';

                wrap.innerHTML = html;
                restoreFields(saved);

/* VIEW */
wrap.querySelectorAll('[data-view-id]').forEach(function(btn){

    btn.addEventListener('click', function(){

        var id = parseInt(btn.dataset.viewId, 10);

        var req = latestRequests.find(function(r){
            return parseInt(r.id,10) === id;
        });

        if(req){
            openModal(req);
        }
    });
});

/* UPDATE */
wrap.querySelectorAll('[data-edit-id]').forEach(function(btn){

    btn.addEventListener('click', function(){

        var id = parseInt(btn.dataset.editId, 10);

        var req = latestRequests.find(function(r){
            return parseInt(r.id,10) === id;
        });

        if(req){
            openModal(req);
        }
    });
});

/* DELETE */
wrap.querySelectorAll('[data-delete-id]').forEach(function(btn){

    btn.addEventListener('click', async function(){

        var id = parseInt(btn.dataset.deleteId,10);

        if(!confirm(
            'Delete this leave request?\n\n' +
            'This is a SOFT DELETE and can be restored later.'
        )){
            return;
        }

        var res = await LSApi.post(
            'admin/leave_requests.php',
            {
                application_id: id,
                action: 'soft_delete'
            }
        );

        if(res.data && res.data.success){

            okEl.textContent =
                res.data.message || 'Deleted';

            okEl.classList.add('show');

            render(res.data.requests || []);

        } else {

            errEl.textContent =
                res.data.message || 'Delete failed';

            errEl.classList.add('show');
        }
    });
});            }

            if (searchInput) {
                searchInput.addEventListener('input', refreshTable);
            }
            if (leaveTypeFilter) {
                leaveTypeFilter.addEventListener('change', refreshTable);
            }
            if (dateFromFilter) {
                dateFromFilter.addEventListener('change', refreshTable);
            }
            if (dateToFilter) {
                dateToFilter.addEventListener('change', refreshTable);
            }
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', resetFilters);
            }
            if (statusNav) {
                statusNav.querySelectorAll('.status-tab').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        setActiveStatusTab(tab.dataset.status || '');
                        refreshTable();
                    });
                });
            }

            (async function load() {
                var res = await LSApi.get('admin/leave_requests.php');

                if (!res.ok || !res.data.success) {
                    loadErr.style.display = 'block';
                    loadErr.textContent = res.data && res.data.message ? res.data.message : 'Could not load requests.';
                    wrap.innerHTML = '';
                    return;
                }

                render(res.data.requests || []);
                liveHint.style.display = 'block';

                if (window.LSLive) {
                    LSLive.pollGet('admin/leave_requests.php', 8000, function (data) {
                        if (currentModalId !== null) {
                            return;
                        }
                        render(data.requests || []);
                    });
                }
            })();

            modalCloseBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>