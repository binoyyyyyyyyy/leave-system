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
    <title>Apply for Leave (Admin)</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { width: 90%; max-width: 950px; margin: 30px auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .topbar h1 { margin: 0; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h2 { margin-top: 25px; margin-bottom: 15px; font-size: 20px; color: #222; border-bottom: 1px solid #ddd; padding-bottom: 8px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: bold; margin-bottom: 6px; }
        input[type="text"], input[type="date"], input[type="number"], input[type="email"], select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .alert { padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .checkbox-group, .radio-group { display: flex; flex-direction: column; gap: 10px; margin-top: 6px; }
        .checkbox-group label, .radio-group label { font-weight: normal; margin-bottom: 0; }
        .hidden { display: none; }
        button { background: #28a745; color: white; border: none; padding: 12px 18px; border-radius: 6px; cursor: pointer; font-size: 15px; }
        button:hover { background: #218838; }
        .muted { color: #666; font-size: 13px; }
        .target-box { border: 1px solid #e3e8ef; border-radius: 8px; padding: 12px; margin-bottom: 14px; background: #f9fbff; }
        .readonly { background: #f2f3f5 !important; color: #555; }
        @media (max-width: 768px) { .row, .row-3 { grid-template-columns: 1fr; } }
        .alert.hidden { display: none; }
        button:disabled { opacity: 0.65; cursor: not-allowed; }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('admin', 'apply'); ?>
    <div class="container">
        <div class="topbar">
            <h1>Apply for Leave (Admin)</h1>
        </div>
        <div class="card">
            <div class="alert success hidden" id="alert_success" role="status"></div>
            <div class="alert error hidden" id="alert_error" role="alert"></div>
            <p class="muted hidden" id="load_error" style="margin-bottom: 16px;"></p>

            <form id="leave_application_form" action="javascript:void(0)" novalidate>
                <h2>Applicant</h2>
                <div class="target-box">
                    <div class="radio-group">
                        <label><input type="radio" name="apply_for" value="self" checked> Apply for myself</label>
                        <label><input type="radio" name="apply_for" value="other"> Apply for others</label>
                    </div>
                </div>

                <div class="target-box hidden" id="other_target_box">
                    <div class="radio-group">
                        <label><input type="radio" name="other_mode" value="existing" checked> Select existing user</label>
                        <label><input type="radio" name="other_mode" value="manual"> Manual entry</label>
                    </div>
                </div>

                <div class="form-group hidden" id="existing_user_group">
                    <label for="target_user_id">Existing User</label>
                    <select id="target_user_id">
                        <option value="">Select user...</option>
                    </select>
                </div>

                <div id="manual_applicant_group" class="hidden">
                    <div class="row-3">
                        <div class="form-group"><label for="applicant_first_name">First Name *</label><input type="text" id="applicant_first_name"></div>
                        <div class="form-group"><label for="applicant_middle_name">Middle Name</label><input type="text" id="applicant_middle_name"></div>
                        <div class="form-group"><label for="applicant_last_name">Last Name *</label><input type="text" id="applicant_last_name"></div>
                    </div>
                    <div class="row">
                        <div class="form-group"><label for="applicant_email">Email</label><input type="email" id="applicant_email"></div>
                        <div class="form-group"><label for="applicant_employee_no">Employee No</label><input type="text" id="applicant_employee_no"></div>
                    </div>
                </div>

                <div class="row-3">
                    <div class="form-group">
                        <label for="applicant_department">Department</label>
                        <input type="text" id="applicant_department">
                    </div>
                    <div class="form-group">
                        <label for="applicant_position">Position</label>
                        <input type="text" id="applicant_position">
                    </div>
                    <div class="form-group">
                        <label for="applicant_salary">Salary</label>
                        <input type="number" step="0.01" min="0" id="applicant_salary">
                    </div>
                </div>

                <h2>6.A Type of Leave to be Availed Of</h2>
                <div class="form-group">
                    <label for="leave_type_id">Type of Leave *</label>
                    <select name="leave_type_id" id="leave_type_id" required>
                        <option value="">Loading leave types…</option>
                    </select>
                </div>

                <h2>6.B Details of Leave</h2>
                <div class="form-group hidden" id="vacationGroup">
                    <label>In case of Vacation / Special Privilege Leave:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="vacation_detail" value="within_philippines"> Within the Philippines</label>
                        <label><input type="radio" name="vacation_detail" value="abroad"> Abroad (Specify)</label>
                    </div>
                    <div class="form-group hidden" id="abroadGroup" style="margin-top: 12px;">
                        <label for="abroad_specify">Abroad Location</label>
                        <input type="text" name="abroad_specify" id="abroad_specify" value="">
                    </div>
                </div>
                <div class="form-group hidden" id="sickGroup">
                    <label>In case of Sick Leave:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="sick_detail" value="in_hospital"> In Hospital (Specify Illness)</label>
                        <label><input type="radio" name="sick_detail" value="out_patient"> Out Patient (Specify Illness)</label>
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
                        <label><input type="radio" name="study_leave_detail" value="completion_of_masters_degree"> Completion of Master's Degree</label>
                        <label><input type="radio" name="study_leave_detail" value="bar_board_examination_review"> BAR/Board Examination Review</label>
                    </div>
                </div>

                <h2>6.C Leave Duration</h2>
                <div class="row">
                    <div class="form-group"><label for="working_days_applied">Number of Working Days Applied For *</label><input type="number" name="working_days_applied" id="working_days_applied" min="0.5" step="0.5" required></div>
                    <div class="form-group">
                        <label>Option</label>
                        <div class="checkbox-group"><label><input type="checkbox" name="is_half_day" value="1"> Check if it is half-day</label></div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group"><label for="date_from">Inclusive Date From *</label><input type="date" name="date_from" id="date_from" required></div>
                    <div class="form-group"><label for="date_to">Inclusive Date To *</label><input type="date" name="date_to" id="date_to" required></div>
                </div>

                <h2>6.D Commutation</h2>
                <div class="form-group">
                    <div class="radio-group">
                        <label><input type="radio" name="commutation" value="not_requested" checked> Not Requested</label>
                        <label><input type="radio" name="commutation" value="requested"> Requested</label>
                    </div>
                </div>

                <button type="submit" id="leave_submit_btn">Submit Leave Application</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/api_client.js"></script>
    <script>
        (function () {
            var form = document.getElementById('leave_application_form');
            var leaveTypeSelect = document.getElementById('leave_type_id');
            var vacationGroup = document.getElementById('vacationGroup');
            var sickGroup = document.getElementById('sickGroup');
            var womenGroup = document.getElementById('womenGroup');
            var studyGroup = document.getElementById('studyGroup');
            var abroadGroup = document.getElementById('abroadGroup');
            var alertSuccess = document.getElementById('alert_success');
            var alertError = document.getElementById('alert_error');
            var loadErrorEl = document.getElementById('load_error');
            var leaveSubmitBtn = document.getElementById('leave_submit_btn');
            var applyForEls = document.querySelectorAll('input[name="apply_for"]');
            var otherModeEls = document.querySelectorAll('input[name="other_mode"]');
            var otherTargetBox = document.getElementById('other_target_box');
            var existingGroup = document.getElementById('existing_user_group');
            var targetUserSel = document.getElementById('target_user_id');
            var manualGroup = document.getElementById('manual_applicant_group');
            var depInput = document.getElementById('applicant_department');
            var posInput = document.getElementById('applicant_position');
            var salInput = document.getElementById('applicant_salary');
            var users = [];
            var adminUser = null;
            var todayStr = '';

            function getApplyFor() {
                var c = document.querySelector('input[name="apply_for"]:checked');
                return c ? c.value : 'self';
            }
            function getOtherMode() {
                var c = document.querySelector('input[name="other_mode"]:checked');
                return c ? c.value : 'existing';
            }
            function hideAlerts() {
                alertSuccess.classList.add('hidden');
                alertError.classList.add('hidden');
                alertSuccess.textContent = '';
                alertError.textContent = '';
            }
            function showError(msg) {
                hideAlerts();
                alertError.textContent = msg;
                alertError.classList.remove('hidden');
            }
            function showSuccess(msg) {
                hideAlerts();
                alertSuccess.textContent = msg;
                alertSuccess.classList.remove('hidden');
            }
            function setApplicantFields(u, readOnly) {
                depInput.value = (u && u.department) ? u.department : '';
                posInput.value = (u && u.position) ? u.position : '';
                salInput.value = (u && u.salary !== null && u.salary !== undefined) ? String(u.salary) : '';
                depInput.readOnly = !!readOnly;
                posInput.readOnly = !!readOnly;
                salInput.readOnly = !!readOnly;
                depInput.classList.toggle('readonly', !!readOnly);
                posInput.classList.toggle('readonly', !!readOnly);
                salInput.classList.toggle('readonly', !!readOnly);
            }
            function renderTargetUI() {
                var applyFor = getApplyFor();
                var otherMode = getOtherMode();
                var isSelf = applyFor === 'self';
                otherTargetBox.classList.toggle('hidden', isSelf);
                existingGroup.classList.toggle('hidden', isSelf || otherMode !== 'existing');
                manualGroup.classList.toggle('hidden', isSelf || otherMode !== 'manual');

                if (isSelf) {
                    setApplicantFields(adminUser, true);
                    return;
                }
                if (otherMode === 'existing') {
                    var uid = Number(targetUserSel.value || 0);
                    var picked = users.find(function (u) { return Number(u.id) === uid; }) || null;
                    setApplicantFields(picked, true);
                } else {
                    setApplicantFields({ department: depInput.value, position: posInput.value, salary: salInput.value }, false);
                }
            }
            function populateLeaveTypes(types) {
                leaveTypeSelect.innerHTML = '<option value="">Select leave type</option>';
                (types || []).forEach(function (t) {
                    var opt = document.createElement('option');
                    opt.value = String(t.id);
                    opt.textContent = t.leave_name;
                    leaveTypeSelect.appendChild(opt);
                });
            }
            function populateUsers(list) {
                targetUserSel.innerHTML = '<option value="">Select user...</option>';
                (list || []).forEach(function (u) {
                    var opt = document.createElement('option');
                    var emp = u.employee_no ? ' (' + u.employee_no + ')' : '';
                    opt.value = String(u.id);
                    opt.textContent = (u.last_name || '') + ', ' + (u.first_name || '') + emp;
                    targetUserSel.appendChild(opt);
                });
            }
            function hideConditionalGroups() {
                vacationGroup.classList.add('hidden');
                sickGroup.classList.add('hidden');
                womenGroup.classList.add('hidden');
                studyGroup.classList.add('hidden');
            }
            function updateAbroadField() {
                var selectedVacation = document.querySelector('input[name="vacation_detail"]:checked');
                if (selectedVacation && selectedVacation.value === 'abroad' && !vacationGroup.classList.contains('hidden')) {
                    abroadGroup.classList.remove('hidden');
                } else {
                    abroadGroup.classList.add('hidden');
                }
            }
            function updateConditionalFields() {
                hideConditionalGroups();
                var selectedText = leaveTypeSelect.options[leaveTypeSelect.selectedIndex]
                    ? leaveTypeSelect.options[leaveTypeSelect.selectedIndex].text.trim().toLowerCase()
                    : '';
                if (selectedText === 'vacation leave' || selectedText === 'special privilege leave') vacationGroup.classList.remove('hidden');
                if (selectedText === 'sick leave') sickGroup.classList.remove('hidden');
                if (selectedText === 'special leave benefits for women') womenGroup.classList.remove('hidden');
                if (selectedText === 'study leave') studyGroup.classList.remove('hidden');
                updateAbroadField();
            }

            leaveTypeSelect.addEventListener('change', updateConditionalFields);
            document.addEventListener('change', function (e) {
                if (e.target.name === 'vacation_detail') updateAbroadField();
                if (e.target.name === 'apply_for' || e.target.name === 'other_mode') renderTargetUI();
            });
            targetUserSel.addEventListener('change', renderTargetUI);

            function radVal(name) {
                var el = document.querySelector('input[name="' + name + '"]:checked');
                return el ? el.value : '';
            }

            async function load() {
                try {
                    var res = await LSApi.get('admin/leave_apply.php');
                    if (!res.ok || !res.data.success) throw new Error((res.data && res.data.message) ? res.data.message : 'Could not load page data.');
                    users = res.data.users || [];
                    adminUser = res.data.admin_user || null;
                    todayStr = res.data.today || '';
                    populateLeaveTypes(res.data.leave_types || []);
                    populateUsers(users);
                    var dateFromInput = document.getElementById('date_from');
                    var dateToInput = document.getElementById('date_to');
                    dateFromInput.min = todayStr;
                    dateToInput.min = todayStr;
                    renderTargetUI();
                    updateConditionalFields();
                } catch (err) {
                    loadErrorEl.textContent = err.message || 'Failed to load leave data.';
                    loadErrorEl.classList.remove('hidden');
                }
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                hideAlerts();
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                var applyFor = getApplyFor();
                var otherMode = getOtherMode();
                if (applyFor === 'other' && otherMode === 'existing' && !targetUserSel.value) {
                    showError('Please select an existing user.');
                    return;
                }
                if (applyFor === 'other' && otherMode === 'manual') {
                    if (!document.getElementById('applicant_first_name').value.trim() || !document.getElementById('applicant_last_name').value.trim()) {
                        showError('Manual applicant first name and last name are required.');
                        return;
                    }
                }

                leaveSubmitBtn.disabled = true;
                var payload = {
                    apply_for: applyFor,
                    other_mode: otherMode,
                    target_user_id: Number(targetUserSel.value || 0),
                    applicant_first_name: document.getElementById('applicant_first_name').value.trim(),
                    applicant_middle_name: document.getElementById('applicant_middle_name').value.trim(),
                    applicant_last_name: document.getElementById('applicant_last_name').value.trim(),
                    applicant_email: document.getElementById('applicant_email').value.trim(),
                    applicant_employee_no: document.getElementById('applicant_employee_no').value.trim(),
                    applicant_department: depInput.value.trim(),
                    applicant_position: posInput.value.trim(),
                    applicant_salary: salInput.value.trim(),
                    leave_type_id: leaveTypeSelect.value,
                    vacation_detail: radVal('vacation_detail') || null,
                    abroad_specify: document.getElementById('abroad_specify').value.trim(),
                    sick_detail: radVal('sick_detail') || null,
                    illness_details: document.getElementById('illness_details').value.trim(),
                    special_leave_women_details: document.getElementById('special_leave_women_details').value.trim(),
                    study_leave_detail: radVal('study_leave_detail') || null,
                    working_days_applied: document.getElementById('working_days_applied').value,
                    date_from: document.getElementById('date_from').value,
                    date_to: document.getElementById('date_to').value,
                    is_half_day: !!document.querySelector('input[name="is_half_day"]:checked'),
                    commutation: radVal('commutation') || 'not_requested'
                };

                try {
                    var res = await LSApi.post('admin/leave_apply.php', payload);
                    if (res.ok && res.data && res.data.success) {
                        showSuccess(res.data.message || 'Leave application submitted.');
                        form.reset();
                        var selfRadio = document.querySelector('input[name="apply_for"][value="self"]');
                        if (selfRadio) selfRadio.checked = true;
                        var existingRadio = document.querySelector('input[name="other_mode"][value="existing"]');
                        if (existingRadio) existingRadio.checked = true;
                        renderTargetUI();
                        updateConditionalFields();
                    } else {
                        showError((res.data && res.data.message) ? res.data.message : 'Submission failed.');
                    }
                } catch (err) {
                    showError('Network error. Please try again.');
                } finally {
                    leaveSubmitBtn.disabled = false;
                }
            });

            load();
        })();
    </script>
</body>
</html>

