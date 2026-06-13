<?php
require '../includes/auth.php';
require_once '../includes/app_nav.php';
checkLogin();

if (!isTeacher()) {
    die('Access denied');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 950px;
            margin: 30px auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 {
            margin: 0;
        }

        .top-links a {
            text-decoration: none;
            margin-left: 10px;
            padding: 10px 14px;
            border-radius: 6px;
            color: white;
        }

        .back-btn {
            background: #007bff;
        }

        .logout-btn {
            background: #d9534f;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 20px;
            color: #222;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .checkbox-group,
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 6px;
        }

        .checkbox-group label,
        .radio-group label {
            font-weight: normal;
            margin-bottom: 0;
        }

        .hidden {
            display: none;
        }

        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #218838;
        }

        .muted {
            color: #666;
            font-size: 13px;
        }

        .balance-pending-note {
            display: inline;
            color: #856404;
            font-size: 13px;
            font-weight: normal;
            margin-left: 6px;
        }

        .balance-recorded-line {
            font-size: 12px;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .row {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .top-links a {
                margin-left: 0;
                margin-right: 10px;
            }
        }

        .confirm-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .confirm-modal-overlay.is-open {
            display: flex;
        }

        .confirm-modal {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            max-width: 520px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .confirm-modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 18px;
            font-weight: bold;
            color: #222;
        }

        .confirm-modal-body {
            padding: 18px 22px;
            overflow-y: auto;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }

        .confirm-summary dl {
            margin: 0;
        }

        .confirm-summary dt {
            font-weight: bold;
            color: #555;
            margin-top: 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .confirm-summary dt:first-child {
            margin-top: 0;
        }

        .confirm-summary dd {
            margin: 4px 0 0 0;
        }

        .confirm-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 14px 22px 18px;
            border-top: 1px solid #e5e5e5;
            flex-wrap: wrap;
        }

        .confirm-modal-actions button {
            margin: 0;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .alert.hidden {
            display: none;
        }

        button:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('teacher', 'apply'); ?>
    <div class="container">
        <div class="topbar">
            <h1>Apply for Leave</h1>
        </div>

        <div class="card">
            <div class="alert success hidden" id="alert_success" role="status"></div>
            <div class="alert error hidden" id="alert_error" role="alert"></div>
            <p class="muted hidden" id="load_error" style="margin-bottom: 16px;"></p>

            <form id="leave_application_form" action="javascript:void(0)" novalidate>
                <h2>6.A Type of Leave to be Availed Of</h2>

                <div class="form-group">
                    <label for="leave_type_id">Type of Leave *</label>
                    <select name="leave_type_id" id="leave_type_id" required>
                        <option value="">Loading leave types…</option>
                    </select>
                    <div class="muted">Date filed is automatically recorded by the system.</div>
                </div>

                <h2>6.B Details of Leave</h2>

                <div class="form-group hidden" id="vacationGroup">
                    <label>In case of Vacation / Special Privilege Leave:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="vacation_detail" value="within_philippines">
                            Within the Philippines
                        </label>
                        <label>
                            <input type="radio" name="vacation_detail" value="abroad">
                            Abroad (Specify)
                        </label>
                    </div>

                    <div class="form-group hidden" id="abroadGroup" style="margin-top: 12px;">
                        <label for="abroad_specify">Abroad Location</label>
                        <input type="text" name="abroad_specify" id="abroad_specify" value="">
                    </div>
                </div>

                <div class="form-group hidden" id="sickGroup">
                    <label>In case of Sick Leave:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="sick_detail" value="in_hospital">
                            In Hospital (Specify Illness)
                        </label>
                        <label>
                            <input type="radio" name="sick_detail" value="out_patient">
                            Out Patient (Specify Illness)
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 12px;">
                        <label for="illness_details">Illness Details</label>
                        <input type="text" name="illness_details" id="illness_details" value="">
                    </div>
                </div>

                <div class="form-group hidden" id="womenGroup">
                    <label for="special_leave_women_details">In case of Special Leave Benefits for Women (Specify Illness)</label>
                    <input type="text" name="special_leave_women_details" id="special_leave_women_details" value="">
                </div>

                <div class="form-group hidden" id="studyGroup">
                    <label>In case of Study Leave:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="study_leave_detail" value="completion_of_masters_degree">
                            Completion of Master's Degree
                        </label>
                        <label>
                            <input type="radio" name="study_leave_detail" value="bar_board_examination_review">
                            BAR/Board Examination Review
                        </label>
                    </div>
                </div>

                <h2>6.C Leave Duration</h2>

                <div class="form-group">
                    <label>Current Leave Balances</label>
                    <p class="muted" style="margin: 0 0 10px;">Balances shown are what you can still apply for now: recorded credits minus days tied up in <strong>pending</strong> vacation/sick requests.</p>
                    <div id="balance_panel" class="muted">Loading balances…</div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="working_days_applied">Number of Working Days Applied For *</label>
                        <input type="number" name="working_days_applied" id="working_days_applied" min="0.5" step="0.5" value="" required>
                    </div>

                    <div class="form-group">
                        <label>Option</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="is_half_day" value="1">
                                Check if it is half-day (0.5 day — use the same date for From and To)
                            </label>
                        </div>
                        <div class="muted" style="margin-top: 8px;">Half-day leave uses 0.5 working day on a single weekday; set both dates to that day.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="date_from">Inclusive Date From *</label>
                        <input type="date" name="date_from" id="date_from" min="" value="" required>
                    </div>

                    <div class="form-group">
                        <label for="date_to">Inclusive Date To *</label>
                        <input type="date" name="date_to" id="date_to" min="" value="" required>
                    </div>
                </div>

                <h2>6.D Commutation</h2>

                <div class="form-group">
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="commutation" value="not_requested" checked>
                            Not Requested
                        </label>
                        <label>
                            <input type="radio" name="commutation" value="requested">
                            Requested
                        </label>
                    </div>
                </div>

                <button type="submit" id="leave_submit_btn">Submit Leave Application</button>
            </form>
        </div>
    </div>

    <div class="confirm-modal-overlay" id="confirm_modal" role="dialog" aria-modal="true" aria-labelledby="confirm_modal_title">
        <div class="confirm-modal">
            <div class="confirm-modal-header" id="confirm_modal_title">Review your application</div>
            <div class="confirm-modal-body">
                <p class="muted" style="margin-top: 0;">Please check the details below. Nothing will be sent until you confirm.</p>
                <div class="confirm-summary" id="confirm_summary"></div>
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="btn-secondary" id="confirm_cancel_btn">Go back and edit</button>
                <button type="button" id="confirm_submit_btn">Confirm and submit</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/api_client.js"></script>
    <script src="../assets/js/live_poll.js"></script>
    <script>
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const vacationGroup = document.getElementById('vacationGroup');
        const sickGroup = document.getElementById('sickGroup');
        const womenGroup = document.getElementById('womenGroup');
        const studyGroup = document.getElementById('studyGroup');
        const abroadGroup = document.getElementById('abroadGroup');
        const balancePanel = document.getElementById('balance_panel');
        const alertSuccess = document.getElementById('alert_success');
        const alertError = document.getElementById('alert_error');
        const loadErrorEl = document.getElementById('load_error');
        const leaveSubmitBtn = document.getElementById('leave_submit_btn');

        const workingDaysInput = document.getElementById('working_days_applied');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const halfDayCheckbox = document.querySelector('input[name="is_half_day"]');
        let todayStr = '';
        let lastBalancesJson = '';
        let leaveApplyBalancesPollStarted = false;

        function fmt1(n) {
            return Number(n).toFixed(1);
        }

        function renderBalances(bal) {
            if (!balancePanel || !bal) return;
            const vacP = bal.vacation_pending > 0.0001
                ? '<span class="balance-pending-note">' + fmt1(bal.vacation_pending) + ' day(s) pending approval (included in this deduction).</span>'
                : '';
            const sickP = bal.sick_pending > 0.0001
                ? '<span class="balance-pending-note">' + fmt1(bal.sick_pending) + ' day(s) pending approval (included in this deduction).</span>'
                : '';
            balancePanel.innerHTML =
                '<div>Vacation Leave available: <b>' + fmt1(bal.vacation_available) + '</b> ' + vacP + '</div>' +
                '<div class="balance-recorded-line">Recorded vacation balance: ' + fmt1(bal.vacation_recorded) + '</div>' +
                '<div style="margin-top:12px;">Sick Leave available: <b>' + fmt1(bal.sick_available) + '</b> ' + sickP + '</div>' +
                '<div class="balance-recorded-line">Recorded sick balance: ' + fmt1(bal.sick_recorded) + '</div>';
        }

        function hideAlerts() {
            alertSuccess.classList.add('hidden');
            alertError.classList.add('hidden');
            alertSuccess.textContent = '';
            alertError.textContent = '';
        }

        function showSuccess(msg) {
            hideAlerts();
            alertSuccess.textContent = msg;
            alertSuccess.classList.remove('hidden');
        }

        function showError(msg) {
            hideAlerts();
            alertError.textContent = msg;
            alertError.classList.remove('hidden');
        }

        function populateLeaveTypes(types) {
            leaveTypeSelect.innerHTML = '<option value="">Select leave type</option>';
            (types || []).forEach(function (t) {
                const opt = document.createElement('option');
                opt.value = String(t.id);
                opt.textContent = t.leave_name;
                leaveTypeSelect.appendChild(opt);
            });
        }

        async function loadPageData() {
            loadErrorEl.classList.add('hidden');
            try {
                const { ok, data } = await LSApi.get('teacher/leave_apply.php');
                if (!ok || !data.success) {
                    throw new Error(data.message || 'Could not load data.');
                }
                todayStr = data.today || '';
                populateLeaveTypes(data.leave_types);
                renderBalances(data.balances);
                lastBalancesJson = JSON.stringify(data.balances || null);
                dateFromInput.min = todayStr;
                dateToInput.min = todayStr;
                updateConditionalFields();
                syncDatesForHalfDay();
                updateWorkingDaysUI();
                if (window.LSLive && !leaveApplyBalancesPollStarted) {
                    leaveApplyBalancesPollStarted = true;
                    LSLive.pollGet('teacher/leave_apply.php', 10000, function (d) {
                        const j = JSON.stringify(d.balances || null);
                        if (j !== lastBalancesJson) {
                            lastBalancesJson = j;
                            renderBalances(d.balances);
                        }
                        if (d.today && d.today !== todayStr) {
                            todayStr = d.today;
                            dateFromInput.min = todayStr;
                            dateToInput.min = todayStr;
                        }
                    });
                }
            } catch (err) {
                loadErrorEl.textContent = err.message || 'Failed to load leave data.';
                loadErrorEl.classList.remove('hidden');
                leaveTypeSelect.innerHTML = '<option value="">Unable to load</option>';
            }
        }

        function hideConditionalGroups() {
            vacationGroup.classList.add('hidden');
            sickGroup.classList.add('hidden');
            womenGroup.classList.add('hidden');
            studyGroup.classList.add('hidden');
        }

        function updateConditionalFields() {
            hideConditionalGroups();

            const selectedText = leaveTypeSelect.options[leaveTypeSelect.selectedIndex]
                ? leaveTypeSelect.options[leaveTypeSelect.selectedIndex].text.trim().toLowerCase()
                : '';

            if (selectedText === 'vacation leave' || selectedText === 'special privilege leave') {
                vacationGroup.classList.remove('hidden');
            }

            if (selectedText === 'sick leave') {
                sickGroup.classList.remove('hidden');
            }

            if (selectedText === 'special leave benefits for women') {
                womenGroup.classList.remove('hidden');
            }

            if (selectedText === 'study leave') {
                studyGroup.classList.remove('hidden');
            }

            updateAbroadField();
        }

        function updateAbroadField() {
            const selectedVacation = document.querySelector('input[name="vacation_detail"]:checked');
            if (selectedVacation && selectedVacation.value === 'abroad' && !vacationGroup.classList.contains('hidden')) {
                abroadGroup.classList.remove('hidden');
            } else {
                abroadGroup.classList.add('hidden');
            }
        }

        leaveTypeSelect.addEventListener('change', updateConditionalFields);

        document.addEventListener('change', function(e) {
            if (e.target.name === 'vacation_detail') {
                updateAbroadField();
            }
        });

        updateConditionalFields();

        function computeExpectedWorkingDays(fromStr, toStr) {
            if (!fromStr || !toStr) return null;

            const from = new Date(fromStr + 'T00:00:00');
            const to = new Date(toStr + 'T00:00:00');
            if (isNaN(from.getTime()) || isNaN(to.getTime())) return null;
            if (to < from) return { error: 'Date To cannot be earlier than Date From.' };

            const fromDay = from.getDay(); // 0=Sun ... 6=Sat
            const toDay = to.getDay();
            // Date From/To must themselves be Mon-Fri; weekends inside the range are ignored.
            if (fromDay === 0 || fromDay === 6 || toDay === 0 || toDay === 6) {
                return { error: 'Date From and Date To must be Monday to Friday only.' };
            }

            const sameDay = fromStr === toStr;
            if (halfDayCheckbox && halfDayCheckbox.checked) {
                if (!sameDay) {
                    return { error: 'For half-day leave, Date From and Date To must be the same working day.' };
                }
                return { expected: 0.5 };
            }

            let count = 0;
            const cur = new Date(from);
            while (cur <= to) {
                const day = cur.getDay(); // 0=Sun ... 6=Sat
                if (day !== 0 && day !== 6) count++; // count only Mon-Fri
                cur.setDate(cur.getDate() + 1);
            }

            return { expected: count };
        }

        function updateWorkingDaysUI() {
            if (!workingDaysInput || !dateFromInput || !dateToInput) return;

            // Enforce min dates on the UI as well (server still validates on submit).
            dateFromInput.min = todayStr;
            dateToInput.min = todayStr;

            const res = computeExpectedWorkingDays(dateFromInput.value, dateToInput.value);
            if (!res || res.error) return;

            const expected = res.expected;
            workingDaysInput.value = Number.isInteger(expected) ? String(expected) : expected.toFixed(1);
        }

        function syncDatesForHalfDay() {
            if (!halfDayCheckbox || !halfDayCheckbox.checked || !dateFromInput || !dateToInput) return;
            if (dateFromInput.value) {
                dateToInput.value = dateFromInput.value;
            }
        }

        if (halfDayCheckbox) {
            halfDayCheckbox.addEventListener('change', function () {
                if (halfDayCheckbox.checked) {
                    syncDatesForHalfDay();
                }
                updateWorkingDaysUI();
            });
        }

        ['change', 'input'].forEach(evt => {
            dateFromInput.addEventListener(evt, function () {
                syncDatesForHalfDay();
                updateWorkingDaysUI();
            });
            dateToInput.addEventListener(evt, function () {
                if (halfDayCheckbox && halfDayCheckbox.checked && dateFromInput && dateFromInput.value) {
                    dateToInput.value = dateFromInput.value;
                }
                updateWorkingDaysUI();
            });
        });

        loadPageData();

        (function () {
            const form = document.getElementById('leave_application_form');
            const modal = document.getElementById('confirm_modal');
            const summaryEl = document.getElementById('confirm_summary');
            const cancelBtn = document.getElementById('confirm_cancel_btn');
            const confirmBtn = document.getElementById('confirm_submit_btn');

            function esc(s) {
                if (s == null || s === '') return '';
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function formatDateDisplay(ymd) {
                if (!ymd) return '';
                const d = new Date(ymd + 'T00:00:00');
                if (isNaN(d.getTime())) return ymd;
                return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
            }

            function row(label, value) {
                const empty = value == null || String(value).trim() === '';
                const v = empty ? '—' : String(value);
                return '<dt>' + esc(label) + '</dt><dd>' + (v === '—' ? '—' : esc(v)) + '</dd>';
            }

            const vacationDetailLabels = {
                within_philippines: 'Within the Philippines',
                abroad: 'Abroad'
            };
            const sickDetailLabels = {
                in_hospital: 'In Hospital (specify illness)',
                out_patient: 'Out Patient (specify illness)'
            };
            const studyLabels = {
                completion_of_masters_degree: "Completion of Master's Degree",
                bar_board_examination_review: 'BAR/Board Examination Review'
            };
            const commutationLabels = {
                not_requested: 'Not Requested',
                requested: 'Requested'
            };

            function buildSummaryHtml() {
                const leaveSel = leaveTypeSelect;
                const leaveName = leaveSel.options[leaveSel.selectedIndex]
                    ? leaveSel.options[leaveSel.selectedIndex].text.trim()
                    : '—';

                let html = '';
                html += row('Type of leave', leaveName);
                html += row('Date filed (system)', 'Recorded on submit');

                const selectedText = leaveName.toLowerCase();

                if (selectedText === 'vacation leave' || selectedText === 'special privilege leave') {
                    const vd = document.querySelector('input[name="vacation_detail"]:checked');
                    const vdVal = vd ? vacationDetailLabels[vd.value] || vd.value : '—';
                    html += row('Vacation / special privilege detail', vdVal);
                    if (vd && vd.value === 'abroad') {
                        html += row('Abroad location', document.getElementById('abroad_specify').value);
                    }
                }

                if (selectedText === 'sick leave') {
                    const sd = document.querySelector('input[name="sick_detail"]:checked');
                    html += row('Sick leave detail', sd ? sickDetailLabels[sd.value] || sd.value : '—');
                    html += row('Illness details', document.getElementById('illness_details').value);
                }

                if (selectedText === 'special leave benefits for women') {
                    html += row('Illness / details', document.getElementById('special_leave_women_details').value);
                }

                if (selectedText === 'study leave') {
                    const st = document.querySelector('input[name="study_leave_detail"]:checked');
                    html += row('Study leave detail', st ? studyLabels[st.value] || st.value : '—');
                }

                const days = workingDaysInput ? workingDaysInput.value : '';
                html += row('Working days applied for', days);
                html += row('Half-day', halfDayCheckbox && halfDayCheckbox.checked ? 'Yes' : 'No');
                html += row('Inclusive date from', formatDateDisplay(dateFromInput ? dateFromInput.value : ''));
                html += row('Inclusive date to', formatDateDisplay(dateToInput ? dateToInput.value : ''));

                const comm = document.querySelector('input[name="commutation"]:checked');
                html += row('Commutation', comm ? commutationLabels[comm.value] || comm.value : '—');

                return '<dl>' + html + '</dl>';
            }

            function openModal() {
                summaryEl.innerHTML = buildSummaryHtml();
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                confirmBtn.focus();
            }

            function closeModal() {
                modal.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                openModal();
            });

            cancelBtn.addEventListener('click', closeModal);

            confirmBtn.addEventListener('click', async function () {
                hideAlerts();
                closeModal();
                confirmBtn.disabled = true;
                if (leaveSubmitBtn) leaveSubmitBtn.disabled = true;

                function radVal(name) {
                    const el = document.querySelector('input[name="' + name + '"]:checked');
                    return el ? el.value : '';
                }

                const payload = {
                    leave_type_id: leaveTypeSelect.value,
                    vacation_detail: radVal('vacation_detail') || null,
                    abroad_specify: document.getElementById('abroad_specify').value.trim(),
                    sick_detail: radVal('sick_detail') || null,
                    illness_details: document.getElementById('illness_details').value.trim(),
                    special_leave_women_details: document.getElementById('special_leave_women_details').value.trim(),
                    study_leave_detail: radVal('study_leave_detail') || null,
                    working_days_applied: workingDaysInput.value,
                    date_from: dateFromInput.value,
                    date_to: dateToInput.value,
                    is_half_day: !!(halfDayCheckbox && halfDayCheckbox.checked),
                    commutation: radVal('commutation') || 'not_requested'
                };

                try {
                    const { data } = await LSApi.post('teacher/leave_apply.php', payload);
                    if (data.success && data.balances) {
                        renderBalances(data.balances);
                        lastBalancesJson = JSON.stringify(data.balances || null);
                        showSuccess(data.message || 'Submitted.');
                        form.reset();
                        const notReq = document.querySelector('input[name="commutation"][value="not_requested"]');
                        if (notReq) notReq.checked = true;
                        if (halfDayCheckbox) halfDayCheckbox.checked = false;
                        updateConditionalFields();
                        updateAbroadField();
                        dateFromInput.min = todayStr;
                        dateToInput.min = todayStr;
                        syncDatesForHalfDay();
                        updateWorkingDaysUI();
                    } else {
                        showError(data.message || 'Submission failed.');
                    }
                } catch (err) {
                    showError('Network error. Please try again.');
                } finally {
                    confirmBtn.disabled = false;
                    if (leaveSubmitBtn) leaveSubmitBtn.disabled = false;
                }
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>