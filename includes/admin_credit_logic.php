<?php

/**
 * @param array{teacher_id?:int|string,vacation_add?:int|float|string,sick_add?:int|float|string,as_of_date?:string} $input
 * @return array{ok:bool, message:string}
 */
function admin_credit_teacher_leaves(PDO $pdo, int $adminId, array $input): array
{
    $teacherId = (int)($input['teacher_id'] ?? 0);
    $vacAdd = (float)($input['vacation_add'] ?? 0);
    $sickAdd = (float)($input['sick_add'] ?? 0);
    $asOfDate = trim((string)($input['as_of_date'] ?? ''));
    if ($asOfDate === '') {
        $asOfDate = (new DateTime('today'))->format('Y-m-d');
    }

    if ($teacherId <= 0) {
        return ['ok' => false, 'message' => 'Please select a teacher.'];
    }
    if ($vacAdd < 0 || $sickAdd < 0) {
        return ['ok' => false, 'message' => 'Credit amounts cannot be negative.'];
    }
    if ($vacAdd == 0 && $sickAdd == 0) {
        return ['ok' => false, 'message' => 'Please enter at least one credit amount.'];
    }

    try {
        $pdo->beginTransaction();

        $creditStmt = $pdo->prepare("
            SELECT *
            FROM leave_credits
            WHERE teacher_id = ?
            ORDER BY as_of_date DESC, id DESC
            LIMIT 1
            FOR UPDATE
        ");
        $creditStmt->execute([$teacherId]);
        $creditsRow = $creditStmt->fetch();

        if ($creditsRow) {
            $updateSql = 'UPDATE leave_credits SET
                as_of_date = ?,
                vacation_total_earned = vacation_total_earned + ?,
                vacation_balance = vacation_balance + ?,
                sick_total_earned = sick_total_earned + ?,
                sick_balance = sick_balance + ?,
                certified_by = ?
            WHERE id = ?';

            $upd = $pdo->prepare($updateSql);
            $upd->execute([
                $asOfDate,
                $vacAdd,
                $vacAdd,
                $sickAdd,
                $sickAdd,
                $adminId,
                (int)$creditsRow['id'],
            ]);
        } else {
            $insertSql = 'INSERT INTO leave_credits (
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
                ?,
                ?,
                ?,
                0.00,
                ?,
                ?,
                0.00,
                ?,
                ?
            )';

            $ins = $pdo->prepare($insertSql);
            $ins->execute([
                $teacherId,
                $asOfDate,
                $vacAdd,
                $vacAdd,
                $sickAdd,
                $sickAdd,
                $adminId,
            ]);
        }

        $pdo->commit();

        return ['ok' => true, 'message' => 'Leave credits updated successfully.'];
    } catch (Exception $e) {
        $pdo->rollBack();

        return ['ok' => false, 'message' => 'Failed to update credits: ' . $e->getMessage()];
    }
}

/**
 * @return list<array<string,mixed>>
 */
function admin_list_teachers_for_credit(PDO $pdo): array
{
    return $pdo->query("SELECT id, employee_no, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY last_name ASC, first_name ASC")
        ->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @return list<array<string,mixed>>
 */
function admin_list_users_for_credit(PDO $pdo): array
{
    return $pdo->query("
        SELECT id, employee_no, first_name, last_name, role, status
        FROM users
        WHERE role IN ('teacher', 'admin')
        ORDER BY last_name ASC, first_name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @return list<array<string,mixed>>
 */
function admin_list_credit_balances(PDO $pdo): array
{
    $sql = "
        SELECT
            u.id AS user_id,
            u.employee_no,
            u.first_name,
            u.last_name,
            u.role,
            u.status,
            lc.id AS credit_id,
            lc.as_of_date,
            lc.vacation_total_earned,
            lc.vacation_less_this_application,
            lc.vacation_balance,
            lc.sick_total_earned,
            lc.sick_less_this_application,
            lc.sick_balance,
            lc.certified_by,
            lc.updated_at
        FROM users u
        LEFT JOIN leave_credits lc ON lc.teacher_id = u.id
        WHERE u.role IN ('teacher', 'admin')
        ORDER BY u.last_name ASC, u.first_name ASC
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @return array<string,mixed>|null
 */
function admin_get_credit_balance(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            u.id AS user_id,
            u.employee_no,
            u.first_name,
            u.last_name,
            u.role,
            u.status,
            lc.id AS credit_id,
            lc.as_of_date,
            lc.vacation_total_earned,
            lc.vacation_less_this_application,
            lc.vacation_balance,
            lc.sick_total_earned,
            lc.sick_less_this_application,
            lc.sick_balance,
            lc.certified_by,
            lc.updated_at
        FROM users u
        LEFT JOIN leave_credits lc ON lc.teacher_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool, message:string}
 */
function admin_update_credit_balance(PDO $pdo, int $adminId, array $input): array
{
    $userId = (int)($input['user_id'] ?? 0);
    $asOfDate = trim((string)($input['as_of_date'] ?? ''));
    $vacTotal = trim((string)($input['vacation_total_earned'] ?? ''));
    $sickTotal = trim((string)($input['sick_total_earned'] ?? ''));

    if ($userId <= 0) {
        return ['ok' => false, 'message' => 'Invalid user selected.'];
    }
    if ($asOfDate === '') {
        $asOfDate = (new DateTime('today'))->format('Y-m-d');
    }
    foreach ([
        'Vacation total earned' => $vacTotal,
        'Sick total earned' => $sickTotal,
    ] as $label => $value) {
        if ($value === '' || !is_numeric($value)) {
            return ['ok' => false, 'message' => $label . ' must be a valid number.'];
        }
        if ((float)$value < 0) {
            return ['ok' => false, 'message' => $label . ' cannot be negative.'];
        }
    }

    $userStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role IN ('teacher', 'admin') LIMIT 1");
    $userStmt->execute([$userId]);
    if (!$userStmt->fetch()) {
        return ['ok' => false, 'message' => 'User not found.'];
    }

    $stmt = $pdo->prepare("SELECT id FROM leave_credits WHERE teacher_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $creditStmt = $pdo->prepare("
            SELECT vacation_less_this_application, sick_less_this_application
            FROM leave_credits
            WHERE teacher_id = ?
            LIMIT 1
        ");
        $creditStmt->execute([$userId]);
        $credit = $creditStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $vacUsed = (float)($credit['vacation_less_this_application'] ?? 0);
        $sickUsed = (float)($credit['sick_less_this_application'] ?? 0);
        $computedVacBalance = max(0.0, (float)$vacTotal - $vacUsed);
        $computedSickBalance = max(0.0, (float)$sickTotal - $sickUsed);

        $upd = $pdo->prepare("
            UPDATE leave_credits SET
                as_of_date = ?,
                vacation_total_earned = ?,
                vacation_balance = ?,
                sick_total_earned = ?,
                sick_balance = ?,
                certified_by = ?
            WHERE teacher_id = ?
            LIMIT 1
        ");
        $upd->execute([
            $asOfDate,
            (float)$vacTotal,
            $computedVacBalance,
            (float)$sickTotal,
            $computedSickBalance,
            $adminId,
            $userId,
        ]);
    } else {
        $ins = $pdo->prepare("
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $userId,
            $asOfDate,
            (float)$vacTotal,
            0.00,
            (float)$vacTotal,
            (float)$sickTotal,
            0.00,
            (float)$sickTotal,
            $adminId,
        ]);
    }

    return ['ok' => true, 'message' => 'Credit balance updated successfully.'];
}

/**
 * @return array{ok:bool, message:string}
 */
function admin_delete_credit_balance(PDO $pdo, int $userId): array
{
    if ($userId <= 0) {
        return ['ok' => false, 'message' => 'Invalid user selected.'];
    }

    $stmt = $pdo->prepare("SELECT id FROM leave_credits WHERE teacher_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        return ['ok' => false, 'message' => 'No credit record found for selected user.'];
    }

    $del = $pdo->prepare("DELETE FROM leave_credits WHERE teacher_id = ?");
    $del->execute([$userId]);

    return ['ok' => true, 'message' => 'Credit record deleted successfully.'];
}
