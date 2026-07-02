<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../includes/leave_applicant_schema.php';
ensure_leave_applicant_columns($pdo);
/**
 * Lock and get latest leave credit row for teacher.
 */
function admin_leave_get_latest_credit_row_for_update(PDO $pdo, int $teacherId): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM leave_credits
        WHERE teacher_id = ?
        ORDER BY as_of_date DESC, id DESC
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$teacherId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/**
 * Create zero credit row if missing.
 */
function admin_leave_create_zero_credit_row(PDO $pdo, int $teacherId, int $adminId): array
{
    $stmt = $pdo->prepare("
        INSERT INTO leave_credits (
            teacher_id,
            as_of_date,
            vacation_total_earned,
            vacation_less_this_application,
            vacation_balance,
            sick_total_earned,
            sick_less_this_application,
            sick_balance,
            certified_by
        ) VALUES (
            :teacher_id,
            CURDATE(),
            0.00, 0.00, 0.00,
            0.00, 0.00, 0.00,
            :certified_by
        )
    ");
    $stmt->execute([
        ':teacher_id' => $teacherId,
        ':certified_by' => $adminId,
    ]);

    return [
        'id' => (int)$pdo->lastInsertId(),
        'teacher_id' => $teacherId,
        'as_of_date' => date('Y-m-d'),
        'vacation_total_earned' => '0.00',
        'vacation_less_this_application' => '0.00',
        'vacation_balance' => '0.00',
        'sick_total_earned' => '0.00',
        'sick_less_this_application' => '0.00',
        'sick_balance' => '0.00',
        'certified_by' => $adminId,
    ];
}

/**
 * Snapshot system-controlled credit values into leave_applications.
 */
function admin_leave_update_application_credit_snapshot(
    PDO $pdo,
    int $applicationId,
    array $creditRow,
    ?string $officerName,
    ?string $officerPosition,
    float $workingDays,
    bool $deductVacation,
    bool $deductSick
): void {
    $vacTotal = (float)($creditRow['vacation_total_earned'] ?? 0);
    $vacBal = (float)($creditRow['vacation_balance'] ?? 0);
    $sickTotal = (float)($creditRow['sick_total_earned'] ?? 0);
    $sickBal = (float)($creditRow['sick_balance'] ?? 0);

    $vacLessThisApp = $deductVacation ? $workingDays : 0.0;
    $sickLessThisApp = $deductSick ? $workingDays : 0.0;

    $stmt = $pdo->prepare("
        UPDATE leave_applications
        SET
            credits_as_of = :credits_as_of,
            vacation_total_earned = :vac_total,
            vacation_less_this_application = :vac_less_this_application,
            vacation_balance = :vac_balance,
            sick_total_earned = :sick_total,
            sick_less_this_application = :sick_less_this_application,
            sick_balance = :sick_balance,
            certification_officer_name = :officer_name,
            certification_officer_position = :officer_position
        WHERE id = :id
    ");

    $stmt->execute([
        ':credits_as_of' => $creditRow['as_of_date'] ?? date('Y-m-d'),
        ':vac_total' => number_format($vacTotal, 2, '.', ''),
        ':vac_less_this_application' => number_format($vacLessThisApp, 2, '.', ''),
        ':vac_balance' => number_format($vacBal, 2, '.', ''),
        ':sick_total' => number_format($sickTotal, 2, '.', ''),
        ':sick_less_this_application' => number_format($sickLessThisApp, 2, '.', ''),
        ':sick_balance' => number_format($sickBal, 2, '.', ''),
        ':officer_name' => $officerName,
        ':officer_position' => $officerPosition,
        ':id' => $applicationId,
    ]);
}

/**
 * Process admin action for one leave application.
 *
 * @param array<string,mixed> $input
 * @return array{ok:bool, message:string}
 */
function admin_leave_request_action(PDO $pdo, int $adminId, array $input): array
{
    $id = (int)($input['application_id'] ?? 0);
    $action = trim((string)($input['action'] ?? ''));

    $adminRemarks = trim((string)($input['admin_remarks'] ?? ''));
    $recommendationReason = trim((string)($input['recommendation_reason'] ?? ''));
    $daysWithPayRaw = trim((string)($input['days_with_pay'] ?? ''));
    $daysWithoutPayRaw = trim((string)($input['days_without_pay'] ?? ''));
    $othersSpecify = trim((string)($input['others_specify'] ?? ''));
    $disapprovedDueTo = trim((string)($input['disapproved_due_to'] ?? ''));

    $certificationOfficerName = trim((string)($input['certification_officer_name'] ?? ''));
    $certificationOfficerPosition = trim((string)($input['certification_officer_position'] ?? ''));

    $recommendationName = trim((string)($input['recommendation_name'] ?? ''));
    $recommendationPosition = trim((string)($input['recommendation_position'] ?? ''));

    $finalActionName = trim((string)($input['final_action_name'] ?? ''));
    $finalActionPosition = trim((string)($input['final_action_position'] ?? ''));

    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Invalid request.'];
    }

    if (!in_array(
    $action,
    ['approve', 'reject', 'update_action', 'soft_delete'],
    true
)) {
        return ['ok' => false, 'message' => 'Invalid action.'];
    }

    $daysWithPay = null;
    if ($daysWithPayRaw !== '') {
        if (!is_numeric($daysWithPayRaw)) {
            return ['ok' => false, 'message' => 'Days with pay must be numeric.'];
        }
        $daysWithPay = round((float)$daysWithPayRaw, 2);
        if ($daysWithPay < 0) {
            return ['ok' => false, 'message' => 'Days with pay cannot be negative.'];
        }
    }

    $daysWithoutPay = null;
    if ($daysWithoutPayRaw !== '') {
        if (!is_numeric($daysWithoutPayRaw)) {
            return ['ok' => false, 'message' => 'Days without pay must be numeric.'];
        }
        $daysWithoutPay = round((float)$daysWithoutPayRaw, 2);
        if ($daysWithoutPay < 0) {
            return ['ok' => false, 'message' => 'Days without pay cannot be negative.'];
        }
    }

    try {
        $pdo->beginTransaction();

        $appStmt = $pdo->prepare("
            SELECT la.*, lt.leave_name
            FROM leave_applications la
            JOIN leave_types lt ON la.leave_type_id = lt.id
            WHERE la.id = ?
            FOR UPDATE
        ");
        $appStmt->execute([$id]);
        $app = $appStmt->fetch(PDO::FETCH_ASSOC);

        if (!$app) {
            throw new Exception('Leave request not found.');
        }

        $currentStatus = (string)$app['status'];
        $teacherId = (int)$app['teacher_id'];
        $workingDays = round((float)$app['working_days_applied'], 2);
        $leaveTypeId = (int)$app['leave_type_id'];
        $leaveTypeName = strtolower(trim((string)($app['leave_name'] ?? '')));

        $isVacation = ($leaveTypeId === 1) || ($leaveTypeName === 'vacation leave');
        $isSick = ($leaveTypeId === 3) || ($leaveTypeName === 'sick leave');

        if ($daysWithPay !== null && $daysWithoutPay !== null) {
            if (($daysWithPay + $daysWithoutPay) > ($workingDays + 0.0001)) {
                throw new Exception('Days with pay plus days without pay cannot exceed the requested days.');
            }
        }

        $finalOfficerName = $certificationOfficerName !== '' ? $certificationOfficerName : null;
        $finalOfficerPosition = $certificationOfficerPosition !== '' ? $certificationOfficerPosition : null;

        $finalRecName = $recommendationName !== '' ? $recommendationName : null;
        $finalRecPosition = $recommendationPosition !== '' ? $recommendationPosition : null;

        $finalActName = $finalActionName !== '' ? $finalActionName : null;
        $finalActPosition = $finalActionPosition !== '' ? $finalActionPosition : null;
if ($action === 'soft_delete') {

    $stmt = $pdo->prepare("
        UPDATE leave_applications
        SET deleted_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $pdo->commit();

    return [
        'ok' => true,
        'message' => 'Request deleted successfully.'
    ];
}
        if ($action === 'update_action') {
            if (!in_array($currentStatus, ['approved', 'rejected'], true)) {
                throw new Exception('Only processed requests can be updated.');
            }

            $creditRow = admin_leave_get_latest_credit_row_for_update($pdo, $teacherId);
            if (!$creditRow) {
                $creditRow = admin_leave_create_zero_credit_row($pdo, $teacherId, $adminId);
            }

            admin_leave_update_application_credit_snapshot(
                $pdo,
                $id,
                $creditRow,
                $finalOfficerName,
                $finalOfficerPosition,
                $workingDays,
                $isVacation,
                $isSick
            );

            $recommendation = $currentStatus === 'approved' ? 'approved' : 'disapproved';

            $stmt = $pdo->prepare("
                UPDATE leave_applications
                SET
                    admin_remarks = :admin_remarks,
                    recommendation = :recommendation,
                    recommendation_reason = :recommendation_reason,
                    days_with_pay = :days_with_pay,
                    days_without_pay = :days_without_pay,
                    others_specify = :others_specify,
                    disapproved_due_to = :disapproved_due_to,
                    rejected_reason = :rejected_reason,
                    recommendation_name = :recommendation_name,
                    recommendation_position = :recommendation_position,
                    final_action_name = :final_action_name,
                    final_action_position = :final_action_position
                WHERE id = :id
            ");

            $stmt->execute([
                ':admin_remarks' => $adminRemarks ?: null,
                ':recommendation' => $recommendation,
                ':recommendation_reason' => $recommendationReason ?: null,
                ':days_with_pay' => $daysWithPay,
                ':days_without_pay' => $daysWithoutPay,
                ':others_specify' => $othersSpecify ?: null,
                ':disapproved_due_to' => $disapprovedDueTo ?: null,
                ':rejected_reason' => ($currentStatus === 'rejected') ? ($disapprovedDueTo ?: $adminRemarks ?: null) : null,
                ':recommendation_name' => $finalRecName,
                ':recommendation_position' => $finalRecPosition,
                ':final_action_name' => $finalActName,
                ':final_action_position' => $finalActPosition,
                ':id' => $id,
            ]);

            $pdo->commit();
            return ['ok' => true, 'message' => 'Action details updated.'];
        }

        if ($action === 'approve') {
            if (!in_array($currentStatus, ['pending', 'rejected'], true)) {
                throw new Exception('This request cannot be approved from its current status.');
            }

            $creditRow = admin_leave_get_latest_credit_row_for_update($pdo, $teacherId);
            if (!$creditRow) {
                $creditRow = admin_leave_create_zero_credit_row($pdo, $teacherId, $adminId);
            }

            $vacBalanceBefore = (float)$creditRow['vacation_balance'];
            $sickBalanceBefore = (float)$creditRow['sick_balance'];

            if ($isVacation && ($vacBalanceBefore - $workingDays) < -0.0001) {
                throw new Exception('Insufficient Vacation Leave credits.');
            }

            if ($isSick && ($sickBalanceBefore - $workingDays) < -0.0001) {
                throw new Exception('Insufficient Sick Leave credits.');
            }

            if ($isVacation) {
                $newVacUsed = (float)$creditRow['vacation_less_this_application'] + $workingDays;
                $newVacBalance = max(0.0, (float)$creditRow['vacation_total_earned'] - $newVacUsed);

                $upd = $pdo->prepare("
                    UPDATE leave_credits
                    SET
                        as_of_date = CURDATE(),
                        vacation_less_this_application = :vac_used,
                        vacation_balance = :vac_balance,
                        certified_by = :certified_by
                    WHERE id = :id
                ");
                $upd->execute([
                    ':vac_used' => $newVacUsed,
                    ':vac_balance' => $newVacBalance,
                    ':certified_by' => $adminId,
                    ':id' => $creditRow['id'],
                ]);
            }

            if ($isSick) {
                $newSickUsed = (float)$creditRow['sick_less_this_application'] + $workingDays;
                $newSickBalance = max(0.0, (float)$creditRow['sick_total_earned'] - $newSickUsed);

                $upd = $pdo->prepare("
                    UPDATE leave_credits
                    SET
                        as_of_date = CURDATE(),
                        sick_less_this_application = :sick_used,
                        sick_balance = :sick_balance,
                        certified_by = :certified_by
                    WHERE id = :id
                ");
                $upd->execute([
                    ':sick_used' => $newSickUsed,
                    ':sick_balance' => $newSickBalance,
                    ':certified_by' => $adminId,
                    ':id' => $creditRow['id'],
                ]);
            }

            $creditRow = admin_leave_get_latest_credit_row_for_update($pdo, $teacherId);
            if (!$creditRow) {
                throw new Exception('Unable to reload leave credits after approval.');
            }

            admin_leave_update_application_credit_snapshot(
                $pdo,
                $id,
                $creditRow,
                $finalOfficerName,
                $finalOfficerPosition,
                $workingDays,
                $isVacation,
                $isSick
            );

            $updateApp = $pdo->prepare("
                UPDATE leave_applications
                SET
                    status = 'approved',
                    admin_remarks = :admin_remarks,
                    recommendation = 'approved',
                    recommendation_reason = :recommendation_reason,
                    days_with_pay = :days_with_pay,
                    days_without_pay = :days_without_pay,
                    others_specify = :others_specify,
                    disapproved_due_to = NULL,
                    rejected_reason = NULL,
                    recommendation_name = :recommendation_name,
                    recommendation_position = :recommendation_position,
                    final_action_name = :final_action_name,
                    final_action_position = :final_action_position,
                    approved_by = :admin_id,
                    approved_at = NOW(),
                    rejected_at = NULL
                WHERE id = :id AND status IN ('pending', 'rejected')
            ");
            $updateApp->execute([
                ':admin_remarks' => $adminRemarks ?: null,
                ':recommendation_reason' => $recommendationReason ?: null,
                ':days_with_pay' => $daysWithPay,
                ':days_without_pay' => $daysWithoutPay,
                ':others_specify' => $othersSpecify ?: null,
                ':recommendation_name' => $finalRecName,
                ':recommendation_position' => $finalRecPosition,
                ':final_action_name' => $finalActName,
                ':final_action_position' => $finalActPosition,
                ':admin_id' => $adminId,
                ':id' => $id,
            ]);

            if ($updateApp->rowCount() !== 1) {
                throw new Exception('Failed to approve request (it may have been processed already).');
            }

            $pdo->commit();

            $success = ['Request approved.'];
            if ($isVacation) {
                $success[] = 'Vacation balance updated.';
            }
            if ($isSick) {
                $success[] = 'Sick balance updated.';
            }

            return ['ok' => true, 'message' => implode(' ', $success)];
        }

        if (!in_array($currentStatus, ['pending', 'approved'], true)) {
            throw new Exception("This request cannot be rejected from status: {$currentStatus}");
        }

        $creditRow = admin_leave_get_latest_credit_row_for_update($pdo, $teacherId);
        if (!$creditRow) {
            $creditRow = admin_leave_create_zero_credit_row($pdo, $teacherId, $adminId);
        }

        if ($currentStatus === 'approved') {
            if ($isVacation) {
                $newVacUsed = max(0.0, (float)$creditRow['vacation_less_this_application'] - $workingDays);
                $newVacBalance = max(0.0, (float)$creditRow['vacation_total_earned'] - $newVacUsed);
                $upd = $pdo->prepare("
                    UPDATE leave_credits
                    SET
                        as_of_date = CURDATE(),
                        vacation_less_this_application = :vac_used,
                        vacation_balance = :vac_balance,
                        certified_by = :certified_by
                    WHERE id = :id
                ");
                $upd->execute([
                    ':vac_used' => $newVacUsed,
                    ':vac_balance' => $newVacBalance,
                    ':certified_by' => $adminId,
                    ':id' => $creditRow['id'],
                ]);
            }

            if ($isSick) {
                $newSickUsed = max(0.0, (float)$creditRow['sick_less_this_application'] - $workingDays);
                $newSickBalance = max(0.0, (float)$creditRow['sick_total_earned'] - $newSickUsed);
                $upd = $pdo->prepare("
                    UPDATE leave_credits
                    SET
                        as_of_date = CURDATE(),
                        sick_less_this_application = :sick_used,
                        sick_balance = :sick_balance,
                        certified_by = :certified_by
                    WHERE id = :id
                ");
                $upd->execute([
                    ':sick_used' => $newSickUsed,
                    ':sick_balance' => $newSickBalance,
                    ':certified_by' => $adminId,
                    ':id' => $creditRow['id'],
                ]);
            }

            $creditRow = admin_leave_get_latest_credit_row_for_update($pdo, $teacherId);
            if (!$creditRow) {
                throw new Exception('Unable to reload leave credits after refund.');
            }
        }

        admin_leave_update_application_credit_snapshot(
            $pdo,
            $id,
            $creditRow,
            $finalOfficerName,
            $finalOfficerPosition,
            $workingDays,
            $isVacation,
            $isSick
        );

        $updateApp = $pdo->prepare("
            UPDATE leave_applications
            SET
                status = 'rejected',
                admin_remarks = :admin_remarks,
                rejected_reason = :rejected_reason,
                recommendation = 'disapproved',
                recommendation_reason = :recommendation_reason,
                days_with_pay = :days_with_pay,
                days_without_pay = :days_without_pay,
                others_specify = :others_specify,
                disapproved_due_to = :disapproved_due_to,
                recommendation_name = :recommendation_name,
                recommendation_position = :recommendation_position,
                final_action_name = :final_action_name,
                final_action_position = :final_action_position,
                approved_by = NULL,
                rejected_at = NOW()
            WHERE id = :id AND status IN ('pending', 'approved')
        ");
        $updateApp->execute([
            ':admin_remarks' => $adminRemarks ?: null,
            ':rejected_reason' => $disapprovedDueTo ?: $adminRemarks ?: null,
            ':recommendation_reason' => $recommendationReason ?: null,
            ':days_with_pay' => $daysWithPay,
            ':days_without_pay' => $daysWithoutPay,
            ':others_specify' => $othersSpecify ?: null,
            ':disapproved_due_to' => $disapprovedDueTo ?: null,
            ':recommendation_name' => $finalRecName,
            ':recommendation_position' => $finalRecPosition,
            ':final_action_name' => $finalActName,
            ':final_action_position' => $finalActPosition,
            ':id' => $id,
        ]);

        if ($updateApp->rowCount() !== 1) {
            throw new Exception('Failed to reject request (it may have been processed already).');
        }

        $pdo->commit();

        $msg = ($currentStatus === 'approved')
            ? 'Request rejected and credits refunded.'
            : 'Request rejected.';

        return ['ok' => true, 'message' => $msg];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

/**
 * @return list<array<string,mixed>>
 */
function admin_leave_requests_list(PDO $pdo): array
{
    $requestsStmt = $pdo->query("
        SELECT
            la.id,
            la.teacher_id,
            la.leave_type_id,
            COALESCE(la.applicant_first_name, u.first_name) AS first_name,
            COALESCE(la.applicant_middle_name, u.middle_name) AS middle_name,
            COALESCE(la.applicant_last_name, u.last_name) AS last_name,
            COALESCE(la.applicant_email, u.email) AS email,
            lt.leave_name,
            la.working_days_applied,
            la.date_from,
            la.date_to,
            la.is_half_day,
            la.status,
            la.vacation_detail,
            la.abroad_specify,
            la.sick_detail,
            la.illness_details,
            la.special_leave_women_details,
            la.study_leave_detail,
            la.commutation,

            la.admin_remarks,
            la.rejected_reason,
            la.recommendation,
            la.recommendation_reason,
            la.credits_as_of,
            la.vacation_total_earned,
            la.vacation_less_this_application,
            la.vacation_balance,
            la.sick_total_earned,
            la.sick_less_this_application,
            la.sick_balance,
            la.certification_officer_name,
            la.certification_officer_position,
            la.recommendation_name,
            la.recommendation_position,
            la.final_action_name,
            la.final_action_position,
            la.days_with_pay,
            la.days_without_pay,
            la.others_specify,
            la.disapproved_due_to,
            la.created_at,
            la.date_filed AS date_requested,

            lc.as_of_date AS live_as_of_date,
            lc.vacation_total_earned AS live_vacation_total_earned,
            lc.vacation_balance AS live_vacation_balance,
            lc.sick_total_earned AS live_sick_total_earned,
            lc.sick_balance AS live_sick_balance
        FROM leave_applications la
        JOIN users u ON la.teacher_id = u.id
        JOIN leave_types lt ON la.leave_type_id = lt.id
        LEFT JOIN leave_credits lc
            ON lc.id = (
                SELECT lc2.id
                FROM leave_credits lc2
                WHERE lc2.teacher_id = la.teacher_id
                ORDER BY lc2.as_of_date DESC, lc2.id DESC
                LIMIT 1
            )
        WHERE la.status IN ('pending', 'approved', 'rejected')
        AND la.deleted_at IS NULL
        ORDER BY la.created_at DESC, la.id DESC
    ");

    $rows = $requestsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $leaveTypeId = (int)$row['leave_type_id'];
        $leaveTypeName = strtolower(trim((string)($row['leave_name'] ?? '')));
        $workingDays = round((float)$row['working_days_applied'], 2);

        $isVacation = ($leaveTypeId === 1) || ($leaveTypeName === 'vacation leave');
        $isSick = ($leaveTypeId === 3) || ($leaveTypeName === 'sick leave');

        $liveVacTotal = (float)($row['live_vacation_total_earned'] ?? 0);
        $liveVacBalance = (float)($row['live_vacation_balance'] ?? 0);
        $liveSickTotal = (float)($row['live_sick_total_earned'] ?? 0);
        $liveSickBalance = (float)($row['live_sick_balance'] ?? 0);

        if (($row['status'] ?? '') === 'pending') {
            $row['credits_as_of'] = $row['live_as_of_date'] ?? null;

            $row['vacation_total_earned'] = number_format($liveVacTotal, 2, '.', '');
            $row['vacation_less_this_application'] = number_format($isVacation ? $workingDays : 0, 2, '.', '');
            $row['vacation_balance'] = number_format($isVacation ? ($liveVacBalance - $workingDays) : $liveVacBalance, 2, '.', '');

            $row['sick_total_earned'] = number_format($liveSickTotal, 2, '.', '');
            $row['sick_less_this_application'] = number_format($isSick ? $workingDays : 0, 2, '.', '');
            $row['sick_balance'] = number_format($isSick ? ($liveSickBalance - $workingDays) : $liveSickBalance, 2, '.', '');
        } else {
            $row['credits_as_of'] = $row['credits_as_of'] ?: ($row['live_as_of_date'] ?? null);
            $row['vacation_total_earned'] = $row['vacation_total_earned'] ?? number_format($liveVacTotal, 2, '.', '');
            $row['vacation_less_this_application'] = $row['vacation_less_this_application'] ?? number_format($isVacation ? $workingDays : 0, 2, '.', '');
            $row['vacation_balance'] = $row['vacation_balance'] ?? number_format($liveVacBalance, 2, '.', '');

            $row['sick_total_earned'] = $row['sick_total_earned'] ?? number_format($liveSickTotal, 2, '.', '');
            $row['sick_less_this_application'] = $row['sick_less_this_application'] ?? number_format($isSick ? $workingDays : 0, 2, '.', '');
            $row['sick_balance'] = $row['sick_balance'] ?? number_format($liveSickBalance, 2, '.', '');
        }
    }
    unset($row);

    return $rows;
}

$adminId = api_require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    api_json([
        'success' => true,
        'requests' => admin_leave_requests_list($pdo),
    ]);
}

if ($method === 'POST') {
    $data = api_read_json();
    $result = admin_leave_request_action($pdo, $adminId, $data);

    if (!empty($result['ok'])) {
        api_json([
            'success' => true,
            'message' => $result['message'] ?? 'Request updated.',
            'requests' => admin_leave_requests_list($pdo),
        ]);
    }

    api_json(['success' => false, 'message' => $result['message'] ?? 'Request failed.'], 400);
}

api_json(['success' => false, 'message' => 'Method not allowed.'], 405);