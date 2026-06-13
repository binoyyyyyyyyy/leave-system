<?php
require '../includes/auth.php';
require '../includes/db.php';
require_once '../includes/leave_applicant_schema.php';

checkLogin();
if (!isAdmin()) {
    die('Access denied');
}
ensure_leave_applicant_columns($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid application ID.');
}

$stmt = $pdo->prepare("
    SELECT
        la.*,
        lt.leave_name,
        COALESCE(la.applicant_employee_no, u.employee_no) AS employee_no,
        COALESCE(la.applicant_first_name, u.first_name) AS first_name,
        COALESCE(la.applicant_middle_name, u.middle_name) AS middle_name,
        COALESCE(la.applicant_last_name, u.last_name) AS last_name,
        COALESCE(la.applicant_department, u.department) AS department,
        COALESCE(la.applicant_position, u.position) AS position,
        COALESCE(la.applicant_salary, u.salary) AS salary,
        COALESCE(la.applicant_email, u.email) AS email,
        approver.first_name AS approver_first_name,
        approver.middle_name AS approver_middle_name,
        approver.last_name AS approver_last_name,
        approver.position AS approver_position
    FROM leave_applications la
    JOIN leave_types lt ON la.leave_type_id = lt.id
    JOIN users u ON la.teacher_id = u.id
    LEFT JOIN users approver ON la.approved_by = approver.id
    WHERE la.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Leave application not found.');
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function format_date_long($date): string
{
    if (!$date) return '';
    $ts = strtotime((string)$date);
    if (!$ts) return '';
    return date('F j, Y', $ts);
}

function format_date_short($date): string
{
    if (!$date) return '';
    $ts = strtotime((string)$date);
    if (!$ts) return '';
    return date('m/d/Y', $ts);
}

function full_name(array $r, string $prefix = ''): string
{
    $first = trim((string)($r[$prefix . 'first_name'] ?? ''));
    $middle = trim((string)($r[$prefix . 'middle_name'] ?? ''));
    $last = trim((string)($r[$prefix . 'last_name'] ?? ''));

    $parts = array_filter([$last, $first, $middle], fn($v) => $v !== '');
    return implode(', ', $parts);
}

function yn_check(bool $condition): string
{
    return $condition ? 'checked-box' : 'box';
}

function line_value($value): string
{
    return trim((string)$value) !== '' ? e((string)$value) : '&nbsp;';
}

$employeeName = full_name($row);
$dateFiled = format_date_long($row['date_filed'] ?? null);
$dateFrom = format_date_long($row['date_from'] ?? null);
$dateTo = format_date_long($row['date_to'] ?? null);
$inclusiveDates = $dateFrom;
if ($dateTo && $dateTo !== $dateFrom) {
    $inclusiveDates .= ' to ' . $dateTo;
}

$leaveName = strtolower(trim((string)($row['leave_name'] ?? '')));
$otherLeave = trim((string)($row['other_leave_type'] ?? ''));

$isVacation = $leaveName === 'vacation leave';
$isMandatory = $leaveName === 'mandatory/forced leave';
$isSick = $leaveName === 'sick leave';
$isMaternity = $leaveName === 'maternity leave';
$isPaternity = $leaveName === 'paternity leave';
$isSpecialPrivilege = $leaveName === 'special privilege leave';
$isSoloParent = $leaveName === 'solo parent leave';
$isStudy = $leaveName === 'study leave';
$isVawc = $leaveName === '10-day vawc leave';
$isRehab = $leaveName === 'rehabilitation privilege';
$isWomen = $leaveName === 'special leave benefits for women';
$isCalamity = $leaveName === 'special emergency (calamity) leave';
$isAdoption = $leaveName === 'adoption leave';
$isWellness = $leaveName === 'wellness leave';
$isMonetization = $leaveName === 'monetization of leave credits';
$isTerminal = $leaveName === 'terminal leave';

$vacWithin = ($row['vacation_detail'] ?? '') === 'within_philippines';
$vacAbroad = ($row['vacation_detail'] ?? '') === 'abroad';

$sickHospital = ($row['sick_detail'] ?? '') === 'in_hospital';
$sickOutPatient = ($row['sick_detail'] ?? '') === 'out_patient';

$studyMasters = ($row['study_leave_detail'] ?? '') === 'completion_of_masters_degree';
$studyBar = ($row['study_leave_detail'] ?? '') === 'bar_board_examination_review';

$commutationRequested = ($row['commutation'] ?? '') === 'requested';
$commutationNotRequested = ($row['commutation'] ?? '') === 'not_requested';

$recommendApproved = ($row['recommendation'] ?? '') === 'approved';
$recommendDisapproved = ($row['recommendation'] ?? '') === 'disapproved';

$adminName = trim((string)($row['certification_officer_name'] ?? ''));
$adminPosition = trim((string)($row['certification_officer_position'] ?? ''));

$recommendationName = trim((string)($row['recommendation_name'] ?? ''));
$recommendationPosition = trim((string)($row['recommendation_position'] ?? ''));

$finalActionName = trim((string)($row['final_action_name'] ?? ''));
$finalActionPosition = trim((string)($row['final_action_position'] ?? ''));

$workingDaysApplied = trim((string)($row['working_days_applied'] ?? ''));
$isHalfDay = (int)($row['is_half_day'] ?? 0) === 1;

$salary = $row['salary'];
$salaryDisplay = ($salary !== null && $salary !== '') ? number_format((float)$salary, 2) : '';

$officeDepartment = trim((string)($row['department'] ?? ''));
$position = trim((string)($row['position'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Leave Form</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
            color: #111;
        }

        .toolbar {
            max-width: 900px;
            margin: 0 auto 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar button,
        .toolbar a {
            border: none;
            background: #007bff;
            color: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .toolbar .download-btn {
            background: #28a745;
        }

        .toolbar .back-btn {
            background: #6c757d;
        }

        .paper {
            width: 850px;
            margin: 0 auto;
            background: #fff;
            padding: 12px 16px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
            border: 1px solid #ccc;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .small { font-size: 11px; }
        .tiny { font-size: 10px; }

        .title {
            font-family: Arial, sans-serif;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0.2px;
            margin: 8px 0 8px;
            text-align: center;
            width: 100%;
            display: block;
        }

        .header-row {
            display: grid;
            grid-template-columns: 170px 1fr 160px;
            align-items: start;
            column-gap: 8px;
            margin-bottom: 4px;
        }

        .cs-form-note {
            font-size: 11px;
            line-height: 1.25;
            font-family: Arial, sans-serif;
            white-space: pre-line;
            padding-top: 4px;
        }

        .stamp-note {
            font-size: 10px;
            text-align: right;
            font-family: Arial, sans-serif;
            padding-top: 4px;
        }

        .header-center {
            text-align: center;
        }

        .agency-head {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
        .deped-logo {
            width: 62px;
            height: 62px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .head-lines {
            line-height: 1.15;
        }

        .head-rp {
            font-size: 14px;
            font-weight: bold;
        }

        .head-deped {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .head-region,
        .head-sdo {
            font-size: 12px;
            font-family: Arial, sans-serif;
            font-weight: bold;
        }

        .top-section-label {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .top-section-value {
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
            letter-spacing: 0.1px;
            text-align: center;
        }

        .top-name-hints {
            display: inline-flex;
            gap: 64px;
            margin-left:  60px;
        }

        .top-split {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .top-split td {
    border: none; /* REMOVE ALL BORDERS */
    padding: 6px 8px;
}

        .top-split-row {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .top-split-row td {
    border: none; /* REMOVE INTERNAL LINES */
    padding: 6px 8px;
}

        table.form-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }

        .form-table td,
        .form-table th {
            border: 1px solid #444;
            padding: 4px 5px;
            vertical-align: top;
        }

        .section-head {
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            background: #f5f5f5;
        }

        .subhead {
            font-family: Arial, sans-serif;
            font-size: 11px;
            font-weight: bold;
        }

        .line {
            display: inline-block;
            border-bottom: 1px solid #222;
            min-width: 140px;
            padding: 0 3px 1px;
        }

        .line-full {
            display: block;
            border-bottom: 1px solid #222;
            min-height: 16px;
            padding: 0 3px 1px;
        }

        .line-short {
            display: inline-block;
            border-bottom: 1px solid #222;
            min-width: 80px;
            padding: 0 3px 1px;
        }

        .checkbox-line {
            margin: 1px 0;
            line-height: 1.3;
        }

        .box,
        .checked-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #222;
            margin-right: 6px;
            vertical-align: middle;
            position: relative;
        }

        .checked-box::after {
            content: "✓";
            position: absolute;
            left: 1px;
            top: -3px;
            font-size: 13px;
            font-weight: bold;
        }

        .credits-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 12px;
        }

        .credits-table th,
        .credits-table td {
            border: 1px solid #444;
            padding: 3px;
            text-align: center;
        }

        .signature-block {
            margin-top: 16px;
            text-align: center;
        }

        .signature-name {
            font-weight: bold;
            text-transform: uppercase;
            display: block;
        }

        .signature-line {
            border-top: 1px solid #222;
            margin-top: 22px;
            padding-top: 3px;
        }
        .signature-line-under {
    border-bottom: 1px solid #222;
    width: 220px;
    margin: 4px auto 4px; /* space above & below line */
}
.signature-position {
    font-size: 12px;
}

        .approval-lines {
            margin-top: 6px;
        }

        .approval-lines .row {
            margin: 3px 0;
            white-space: nowrap;
        }

        .approval-lines .fill-line {
            display: inline-block;
            border-bottom: 1px solid #222;
            width: 120px;
            height: 11px;
            vertical-align: middle;
            margin-right: 4px;
        }

        .action-merged-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .action-shared-signatory {
            margin-top: 10px;
            text-align: center;
        }

        .action-shared-signatory .signature-line {
            margin: 0 auto;
            max-width: 300px;
        }

        .section-7-cell {
            min-height: 220px;
        }

        .section-7b-cell {
            min-height: 220px;
        }

        .bottom-signature-cell {
            height: 90px;
            vertical-align: bottom !important;
            text-align: center;
        }

        .spacer-8 { height: 8px; }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .paper {
                box-shadow: none;
                border: none;
                margin: 0;
                width: 100%;
                padding: 6px 8px;
            }

            @page {
                size: A4 portrait;
                margin: 6mm;
            }
        }
        .head-lines {
    text-align: center;
    width: 100%;
}
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print Form</button>
        <button type="button" class="download-btn" onclick="window.print()">Download PDF</button>
        <a href="leave_requests.php" class="back-btn">Back</a>
    </div>

    <div class="paper">
        <div class="header-row">
            <div class="cs-form-note"><i>Civil Service Form No. 6
            Revised 2020</i></div>

            <div class="header-center">
            <div class="agency-head">
    <img src="../includes/deped_logo.png" class="deped-logo" style="margin-bottom:5px;">

    <div class="head-lines">
        <div class="head-rp" style="font-family: 'Old English Text MT', serif;">Republic of the Philippines</div>
        <div class="head-deped" style="font-family: 'Old English Text MT', serif;">Department of Education</div>
        <div class="head-region">REGION III – CENTRAL LUZON</div>
        <div class="head-sdo">SCHOOLS DIVISION OFFICE OF NUEVA ECIJA</div>
    </div>
</div>
            </div>

            <div class="stamp-note">Stamp of Date of Receipt</div>
        </div>

        <div class="center title">APPLICATION FOR LEAVE</div>

        <table class="form-table">
            <tr>
                <td colspan="2" style="padding:0;border:none;">
                <table class="top-split" style="border:1px solid #444;">
                        <tr>
                            <td style="width:37%;">
                                <span class="top-section-label">1. &nbsp; OFFICE/DEPARTMENT</span>
                                <div class="top-section-value"><?php echo e(strtoupper($officeDepartment)); ?></div>
                            </td>
                            <td style="width:63%;">
                                <span class="top-section-label">2. &nbsp; NAME :</span>
                                <span class="top-name-hints tiny">
                                    <span>(Last)</span>
                                    <span>(First)</span>
                                    <span>(Middle)</span>
                                </span>
                                <div class="top-section-value"><?php echo e(strtoupper($employeeName)); ?></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding:0;border:none;">
                <table class="top-split-row" style="border:1px solid #444;">
                        <tr>
                            <td style="width:37%;">
                                <span class="top-section-label">3. &nbsp; DATE OF FILING</span>
                                <span class="line" style="min-width:170px; font-weight:bold;"><?php echo e($dateFiled); ?></span>
                            </td>
                            <td style="width:43%;">
                                <span class="top-section-label">4. &nbsp; POSITION</span>
                                <span class="line" style="min-width:180px; font-weight:bold;"><?php echo line_value($position); ?></span>
                            </td>
                            <td style="width:20%;">
                                <span class="top-section-label">5. &nbsp; SALARY</span>
                                <span class="line-short" style="min-width:90px; font-weight:bold;"><?php echo line_value($salaryDisplay); ?></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="section-head">6. DETAILS OF APPLICATION</td>
            </tr>

            <tr>
                <td style="width:55%;">
                    <div class="subhead">6.A TYPE OF LEAVE TO BE AVAILED OF</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isVacation); ?>"></span>Vacation Leave <i>(Sec.51,Rule XVI, Omnibus Rules Implementing E.O No. 292)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isMandatory); ?>"></span>Mandatory/Forced Leave <i>Sec. 25, Rule XVI, Omnibus Rules Implementing E.O No. 292</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isSick); ?>"></span>Sick Leave <i>(Sec. 43, Rule XVI, Omnibus Rules Implementing E.O No. 292)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isMaternity); ?>"></span>Maternity Leave <i>(R.A No. 11210/IRR issued by CSC, DOLE and SSS)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isPaternity); ?>"></span>Paternity Leave <i>(R.A No. 8187/CSC MC No. 71,s. 1998 as amended)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isSpecialPrivilege); ?>"></span>Special Privilege Leave <i>(Sec.21, Rule XVI, Omnibus Rules Implementing E.O No. 292)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isSoloParent); ?>"></span>Solo Parent Leave <i>(RA No. 8972/CSC MC No, 8, s. 2004)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isStudy); ?>"></span>Study Leave <i>(Sec. 68, Rule XVI, Omnibus Rules Implementing E.O No. 292)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isVawc); ?>"></span>10-Day VAWC Leave <i>(RA No. 9262/CSC MC No. 15, s. 2005)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isRehab); ?>"></span>Rehabilitation Privilege <i>(Sec. 55 Rule XVI, Omnibus Rules Implementing E.O No. 292)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isWomen); ?>"></span>Special Leave Benefits for Women <i>(RA No. 9710/CSC MC No. 25, s. 2010)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isCalamity); ?>"></span>Special Emergency (Calamity) Leave <i>(CSC MC No. 2, s. 2012 as amended)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isAdoption); ?>"></span>Adoption Leave <i>(R.A. No. 8552)</i></div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isMonetization); ?>"></span>Monetization of Leave Credits</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isTerminal); ?>"></span>Terminal Leave</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($isWellness); ?>"></span>Wellness Leave</div>

                    <?php if ($otherLeave !== ''): ?>
                        <div class="checkbox-line" style="margin-top:4px;">
                            <span class="checked-box"></span>Other: <span class="line" style="min-width:220px;"><?php echo e($otherLeave); ?></span>
                        </div>
                    <?php endif; ?>
                </td>

                <td style="width:45%;">
                    <div class="subhead">6.B DETAILS OF LEAVE</div>

                    <div class="checkbox-line bold">In case of Vacation/Special Privilege Leave:</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($vacWithin); ?>"></span>Within the Philippines</div>
                    <div class="checkbox-line">
                        <span class="<?php echo yn_check($vacAbroad); ?>"></span>Abroad (Specify)
                        <span class="line" style="min-width:180px;"><?php echo line_value($row['abroad_specify'] ?? ''); ?></span>
                    </div>

                    <div class="spacer-8"></div>

                    <div class="checkbox-line bold">In case of Sick Leave:</div>
                    <div class="checkbox-line">
                        <span class="<?php echo yn_check($sickHospital); ?>"></span>In Hospital (Specify Illness)
                        <span class="line" style="min-width:140px;"><?php echo $sickHospital ? line_value($row['illness_details'] ?? '') : '&nbsp;'; ?></span>
                    </div>
                    <div class="checkbox-line">
                        <span class="<?php echo yn_check($sickOutPatient); ?>"></span>Out Patient (Specify Illness)
                        <span class="line" style="min-width:132px;"><?php echo $sickOutPatient ? line_value($row['illness_details'] ?? '') : '&nbsp;'; ?></span>
                    </div>

                    <div class="spacer-8"></div>

                    <div class="checkbox-line bold">In case of Special Leave Benefits for Women:</div>
                    <div class="checkbox-line">
                        (Specify Illness)
                        <span class="line" style="min-width:230px;"><?php echo line_value($row['special_leave_women_details'] ?? ''); ?></span>
                    </div>

                    <div class="spacer-8"></div>

                    <div class="checkbox-line bold">In case of Study Leave:</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($studyMasters); ?>"></span>Completion of Master's Degree</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($studyBar); ?>"></span>BAR/Board Examination Review</div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="subhead">6.C NUMBER OF WORKING DAYS APPLIED FOR</div>
                    <div class="line-full"><?php echo e($workingDaysApplied . ($isHalfDay ? ' (half-day)' : '')); ?></div>

                    <div class="spacer-8"></div>

                    <div class="subhead">INCLUSIVE DATES</div>
                    <div class="line-full"><?php echo line_value($inclusiveDates); ?></div>
                </td>
                <td>
                    <div class="subhead">6.D COMMUTATION</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($commutationNotRequested); ?>"></span>Not Requested</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($commutationRequested); ?>"></span>Requested</div>

                    <div class="signature-block">
                        <div class="signature-line tiny">(Signature of Applicant)</div>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="section-head">7. DETAILS OF ACTION ON APPLICATION</td>
            </tr>

            <tr>
                <td style="width:50%;" class="section-7-cell">
                    <div class="subhead">7.A CERTIFICATION OF LEAVE CREDITS</div>
                    <div class="center" style="margin:4px 0 6px;">
                        As of <span class="line" style="min-width:120px;"><?php echo line_value(format_date_short($row['credits_as_of'] ?? null)); ?></span>
                    </div>

                    <table class="credits-table">
                        <tr>
                            <th></th>
                            <th>Vacation Leave</th>
                            <th>Sick Leave</th>
                        </tr>
                        <tr>
                            <td>Total Earned</td>
                            <td><?php echo e((string)($row['vacation_total_earned'] ?? '')); ?></td>
                            <td><?php echo e((string)($row['sick_total_earned'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <td>Less this application</td>
                            <td><?php echo e((string)($row['vacation_less_this_application'] ?? '')); ?></td>
                            <td><?php echo e((string)($row['sick_less_this_application'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <td>Balance</td>
                            <td><?php echo e((string)($row['vacation_balance'] ?? '')); ?></td>
                            <td><?php echo e((string)($row['sick_balance'] ?? '')); ?></td>
                        </tr>
                    </table>

                    <div class="signature-block">
    <div class="signature-name"><?php echo line_value($adminName); ?></div>
    <div class="signature-line-under"></div>
    <div class="signature-position"><?php echo line_value($adminPosition); ?></div>
</div>
                </td>

                <td style="width:50%;" class="section-7b-cell">
                    <div class="subhead">7.B RECOMMENDATION</div>
                    <div class="checkbox-line"><span class="<?php echo yn_check($recommendApproved); ?>"></span>Approved</div>
                    <div class="checkbox-line">
                        <span class="<?php echo yn_check($recommendDisapproved); ?>"></span>For disapproval due to
                        <span class="line" style="min-width:220px;"><?php echo line_value($row['recommendation_reason'] ?? ''); ?></span>
                    </div>

                    <div class="line-full" style="margin-top:8px;"><?php echo '&nbsp;'; ?></div>
                    <div class="line-full"><?php echo '&nbsp;'; ?></div>

                    <div class="signature-block">
    <div class="signature-name"><?php echo line_value($recommendationName); ?></div>
    <div class="signature-line-under"></div>
    <div class="signature-position"><?php echo line_value($recommendationPosition); ?></div>
</div>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <div class="action-merged-grid">
                        <div>
                            <div class="subhead">7.C APPROVED FOR:</div>
                            <div class="approval-lines">
                                <div class="row"><span class="fill-line"></span> days with pay</div>
                                <div class="row"><span class="fill-line"></span> days without pay</div>
                                <div class="row"><span class="fill-line"></span> others (Specify)</div>
                            </div>

                            <div style="margin-top:8px;">
                                <?php if (trim((string)($row['days_with_pay'] ?? '')) !== ''): ?>
                                    <div><?php echo e((string)$row['days_with_pay']); ?> day(s) with pay</div>
                                <?php endif; ?>

                                <?php if (trim((string)($row['days_without_pay'] ?? '')) !== ''): ?>
                                    <div><?php echo e((string)$row['days_without_pay']); ?> day(s) without pay</div>
                                <?php endif; ?>

                                <?php if (trim((string)($row['others_specify'] ?? '')) !== ''): ?>
                                    <div><?php echo e((string)$row['others_specify']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <div class="subhead">7.D DISAPPROVED DUE TO:</div>
                            <div class="line-full"><?php echo line_value($row['disapproved_due_to'] ?? ''); ?></div>
                            <div class="line-full"><?php echo '&nbsp;'; ?></div>
                            <div class="line-full"><?php echo '&nbsp;'; ?></div>
                        </div>
                    </div>

                    <div class="action-shared-signatory">
                    <div class="signature-block">
    <div class="signature-name"><?php echo line_value($finalActionName); ?></div>
    <div class="signature-line-under"></div>
    <div class="signature-position"><?php echo line_value($finalActionPosition); ?></div>
</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <script>
        // The "Download PDF" button uses the browser print dialog.
        // Choose "Save as PDF" in the print destination.
    </script>
</body>
</html>