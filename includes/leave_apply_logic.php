<?php

/**
 * Recorded balances from leave_credits, pending vacation/sick days from applications,
 * and available = recorded minus pending (matches admin credit rules on approve).
 */
function teacher_leave_balance_snapshot(PDO $pdo, int $userId): array
{
    $vacationRecorded = 0.0;
    $sickRecorded = 0.0;
    $creditsStmt = $pdo->prepare("
        SELECT vacation_balance, sick_balance
        FROM leave_credits
        WHERE teacher_id = ?
        ORDER BY as_of_date DESC, id DESC
        LIMIT 1
    ");
    $creditsStmt->execute([$userId]);
    $creditsRow = $creditsStmt->fetch();
    if ($creditsRow) {
        $vacationRecorded = (float)$creditsRow['vacation_balance'];
        $sickRecorded = (float)$creditsRow['sick_balance'];
    }

    $pendingVacStmt = $pdo->prepare("
        SELECT COALESCE(SUM(la.working_days_applied), 0)
        FROM leave_applications la
        INNER JOIN leave_types lt ON la.leave_type_id = lt.id
        WHERE la.teacher_id = ?
          AND la.status = 'pending'
          AND (la.leave_type_id = 1 OR LOWER(TRIM(lt.leave_name)) = 'vacation leave')
    ");
    $pendingVacStmt->execute([$userId]);
    $vacationPending = (float)$pendingVacStmt->fetchColumn();

    $pendingSickStmt = $pdo->prepare("
        SELECT COALESCE(SUM(la.working_days_applied), 0)
        FROM leave_applications la
        INNER JOIN leave_types lt ON la.leave_type_id = lt.id
        WHERE la.teacher_id = ?
          AND la.status = 'pending'
          AND (la.leave_type_id = 3 OR LOWER(TRIM(lt.leave_name)) = 'sick leave')
    ");
    $pendingSickStmt->execute([$userId]);
    $sickPending = (float)$pendingSickStmt->fetchColumn();

    return [
        'vacation_recorded' => $vacationRecorded,
        'sick_recorded' => $sickRecorded,
        'vacation_pending' => $vacationPending,
        'sick_pending' => $sickPending,
        'vacation_available' => $vacationRecorded - $vacationPending,
        'sick_available' => $sickRecorded - $sickPending,
    ];
}

/**
 * Validate input and insert leave application. $input matches JSON body from API.
 *
 * @return array{ok:bool, message?:string, balances?:array}
 */
function teacher_apply_leave_from_input(PDO $pdo, int $userId, array $input): array
{
    return leave_apply_from_input_for_user($pdo, $userId, $input, []);
}

/**
 * @param array<string,mixed> $input
 * @param array<string,mixed> $options
 * @return array{ok:bool, message?:string, balances?:array}
 */
function leave_apply_from_input_for_user(PDO $pdo, int $userId, array $input, array $options = []): array
{
    $today = new DateTime('today');

    $leave_type_id = $input['leave_type_id'] ?? '';
    $date_filed = date('Y-m-d');

    $vacation_detail = isset($input['vacation_detail']) && $input['vacation_detail'] !== ''
        ? $input['vacation_detail'] : null;
    $abroad_specify = trim((string)($input['abroad_specify'] ?? ''));

    $sick_detail = isset($input['sick_detail']) && $input['sick_detail'] !== ''
        ? $input['sick_detail'] : null;
    $illness_details = trim((string)($input['illness_details'] ?? ''));

    $special_leave_women_details = trim((string)($input['special_leave_women_details'] ?? ''));
    $study_leave_detail = isset($input['study_leave_detail']) && $input['study_leave_detail'] !== ''
        ? $input['study_leave_detail'] : null;

    $working_days_applied = $input['working_days_applied'] ?? '';
    $date_from = $input['date_from'] ?? '';
    $date_to = $input['date_to'] ?? '';
    $is_half_day = !empty($input['is_half_day']) ? 1 : 0;

    $commutation = $input['commutation'] ?? 'not_requested';
    if (!in_array($commutation, ['not_requested', 'requested'], true)) {
        $commutation = 'not_requested';
    }

    $leaveTypeName = '';

    if (!empty($leave_type_id)) {
        $typeStmt = $pdo->prepare('SELECT leave_name FROM leave_types WHERE id = ?');
        $typeStmt->execute([$leave_type_id]);
        $selectedType = $typeStmt->fetch();

        if ($selectedType) {
            $leaveTypeName = strtolower(trim($selectedType['leave_name']));
        }
    }

    $bal = teacher_leave_balance_snapshot($pdo, $userId);
    $skipCreditCheck = !empty($options['skip_credit_check']);

    $applicant = isset($options['applicant']) && is_array($options['applicant']) ? $options['applicant'] : [];
    $applicantFirstName = trim((string)($applicant['first_name'] ?? ''));
    $applicantMiddleName = trim((string)($applicant['middle_name'] ?? ''));
    $applicantLastName = trim((string)($applicant['last_name'] ?? ''));
    $applicantEmail = trim((string)($applicant['email'] ?? ''));
    $applicantEmployeeNo = trim((string)($applicant['employee_no'] ?? ''));
    $applicantDepartment = trim((string)($applicant['department'] ?? ''));
    $applicantPosition = trim((string)($applicant['position'] ?? ''));
    $applicantSalaryRaw = trim((string)($applicant['salary'] ?? ''));
    $applicantSalary = ($applicantSalaryRaw !== '' && is_numeric($applicantSalaryRaw)) ? (float)$applicantSalaryRaw : null;

    if (
        empty($leave_type_id)
        || $working_days_applied === ''
        || empty($date_from)
        || empty($date_to)
    ) {
        return ['ok' => false, 'message' => 'Please fill in all required fields.'];
    }

    if (!is_numeric($working_days_applied) || (float)$working_days_applied < 0.5) {
        return ['ok' => false, 'message' => 'Number of working days must be a valid number (minimum 0.5 for a half-day).'];
    }

    try {
        $fromDt = new DateTime($date_from);
        $toDt = new DateTime($date_to);
    } catch (Exception $e) {
        return ['ok' => false, 'message' => 'Invalid date selected.'];
    }

    if ($fromDt < $today) {
        return ['ok' => false, 'message' => 'Date From cannot be earlier than today.'];
    }
    if ($toDt < $fromDt) {
        return ['ok' => false, 'message' => 'Date To cannot be earlier than Date From.'];
    }

    $fromDow = (int)$fromDt->format('N');
    $toDow = (int)$toDt->format('N');
    if ($fromDow >= 6 || $toDow >= 6) {
        return ['ok' => false, 'message' => 'Date From and Date To must be Monday to Friday only.'];
    }

    $baseWorkingDays = 0;
    $cur = clone $fromDt;
    while ($cur <= $toDt) {
        $dayOfWeek = (int)$cur->format('N');
        if ($dayOfWeek <= 5) {
            $baseWorkingDays++;
        }
        $cur->modify('+1 day');
    }

    $sameDay = ($fromDt->format('Y-m-d') === $toDt->format('Y-m-d'));

    if ($is_half_day && !$sameDay) {
        return ['ok' => false, 'message' => 'For half-day leave (0.5 day), inclusive Date From and Date To must be the same working day.'];
    }

    if ($is_half_day) {
        $expectedDays = 0.5;
    } else {
        $expectedDays = (float)$baseWorkingDays;
    }
    $expectedDays = round($expectedDays * 2) / 2;

    $postedDays = (float)$working_days_applied;
    $epsilon = 0.01;

    if (abs($postedDays - $expectedDays) < $epsilon) {
        $working_days_applied = $is_half_day
            ? number_format($expectedDays, 1, '.', '')
            : (string)(int)$expectedDays;
    } else {
        $expectedFormatted = $is_half_day
            ? number_format($expectedDays, 1, '.', '')
            : (string)(int)$expectedDays;

        return ['ok' => false, 'message' => "Number of Working Days Applied For must be exactly {$expectedFormatted} based on the selected Date From and Date To."];
    }

    if (!$skipCreditCheck && ($leaveTypeName === 'vacation leave' || $leaveTypeName === 'sick leave')) {
        $neededDays = (float)$working_days_applied;
        $vacAvail = $bal['vacation_available'];
        $sickAvail = $bal['sick_available'];
        if ($leaveTypeName === 'vacation leave' && $neededDays > $vacAvail + 0.0001) {
            return ['ok' => false, 'message' => 'Insufficient Vacation Leave credits. Available (after pending): ' . number_format($vacAvail, 1, '.', '')
                . ' — recorded ' . number_format($bal['vacation_recorded'], 1, '.', '')
                . ', pending ' . number_format($bal['vacation_pending'], 1, '.', '') . ' day(s).'];
        }
        if ($leaveTypeName === 'sick leave' && $neededDays > $sickAvail + 0.0001) {
            return ['ok' => false, 'message' => 'Insufficient Sick Leave credits. Available (after pending): ' . number_format($sickAvail, 1, '.', '')
                . ' — recorded ' . number_format($bal['sick_recorded'], 1, '.', '')
                . ', pending ' . number_format($bal['sick_pending'], 1, '.', '') . ' day(s).'];
        }
    }

    if ($leaveTypeName === 'vacation leave' || $leaveTypeName === 'special privilege leave') {
        if (!empty($vacation_detail) && $vacation_detail === 'abroad' && $abroad_specify === '') {
            return ['ok' => false, 'message' => 'Please specify the abroad location.'];
        }
    }

    if ($leaveTypeName === 'sick leave') {
        if (!empty($sick_detail) && $illness_details === '') {
            return ['ok' => false, 'message' => 'Please specify the illness details.'];
        }
    }

    if ($leaveTypeName === 'special leave benefits for women') {
        if ($special_leave_women_details === '') {
            return ['ok' => false, 'message' => 'Please specify the illness/details.'];
        }
    }

    $sql = 'INSERT INTO leave_applications (
                teacher_id,
                applicant_first_name,
                applicant_middle_name,
                applicant_last_name,
                applicant_email,
                applicant_employee_no,
                applicant_department,
                applicant_position,
                applicant_salary,
                leave_type_id,
                date_filed,
                vacation_detail,
                abroad_specify,
                sick_detail,
                illness_details,
                special_leave_women_details,
                study_leave_detail,
                working_days_applied,
                date_from,
                date_to,
                is_half_day,
                commutation,
                status
            ) VALUES (
                :teacher_id,
                :applicant_first_name,
                :applicant_middle_name,
                :applicant_last_name,
                :applicant_email,
                :applicant_employee_no,
                :applicant_department,
                :applicant_position,
                :applicant_salary,
                :leave_type_id,
                :date_filed,
                :vacation_detail,
                :abroad_specify,
                :sick_detail,
                :illness_details,
                :special_leave_women_details,
                :study_leave_detail,
                :working_days_applied,
                :date_from,
                :date_to,
                :is_half_day,
                :commutation,
                \'pending\'
            )';

    $stmt = $pdo->prepare($sql);
    $saved = $stmt->execute([
        ':teacher_id' => $userId,
        ':applicant_first_name' => $applicantFirstName !== '' ? $applicantFirstName : null,
        ':applicant_middle_name' => $applicantMiddleName !== '' ? $applicantMiddleName : null,
        ':applicant_last_name' => $applicantLastName !== '' ? $applicantLastName : null,
        ':applicant_email' => $applicantEmail !== '' ? $applicantEmail : null,
        ':applicant_employee_no' => $applicantEmployeeNo !== '' ? $applicantEmployeeNo : null,
        ':applicant_department' => $applicantDepartment !== '' ? $applicantDepartment : null,
        ':applicant_position' => $applicantPosition !== '' ? $applicantPosition : null,
        ':applicant_salary' => $applicantSalary,
        ':leave_type_id' => $leave_type_id,
        ':date_filed' => $date_filed,
        ':vacation_detail' => $vacation_detail ?: null,
        ':abroad_specify' => $abroad_specify ?: null,
        ':sick_detail' => $sick_detail ?: null,
        ':illness_details' => $illness_details ?: null,
        ':special_leave_women_details' => $special_leave_women_details ?: null,
        ':study_leave_detail' => $study_leave_detail ?: null,
        ':working_days_applied' => $working_days_applied,
        ':date_from' => $date_from,
        ':date_to' => $date_to,
        ':is_half_day' => $is_half_day,
        ':commutation' => $commutation,
    ]);

    if (!$saved) {
        return ['ok' => false, 'message' => 'Something went wrong while saving the application.'];
    }

    $bal = teacher_leave_balance_snapshot($pdo, $userId);

    return [
        'ok' => true,
        'message' => 'Leave application submitted successfully.',
        'balances' => $bal,
    ];
}
