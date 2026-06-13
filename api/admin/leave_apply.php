<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../includes/leave_apply_logic.php';
require_once __DIR__ . '/../../includes/leave_applicant_schema.php';

ensure_leave_applicant_columns($pdo);

$adminId = api_require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function admin_user_brief(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, employee_no, first_name, middle_name, last_name, email, department, position, salary, status
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

if ($method === 'GET') {
    $today = new DateTime('today');
    $todayStr = $today->format('Y-m-d');

    $typesStmt = $pdo->query("SELECT id, leave_name FROM leave_types WHERE leave_name <> 'Others' ORDER BY id ASC");
    $leaveTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);

    $usersStmt = $pdo->query("
        SELECT id, employee_no, first_name, middle_name, last_name, email, role, department, position, salary, status
        FROM users
        WHERE status = 'active'
        ORDER BY last_name ASC, first_name ASC, id ASC
    ");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    $adminUser = admin_user_brief($pdo, $adminId);
    $balances = teacher_leave_balance_snapshot($pdo, $adminId);

    api_json([
        'success' => true,
        'today' => $todayStr,
        'leave_types' => $leaveTypes,
        'users' => $users,
        'admin_user' => $adminUser,
        'balances' => $balances,
    ]);
}

if ($method === 'POST') {
    $data = api_read_json();

    $applyFor = (string)($data['apply_for'] ?? 'self');
    $otherMode = (string)($data['other_mode'] ?? 'existing');
    $targetUserId = 0;
    $skipCreditCheck = false;
    $applicant = [];

    if ($applyFor === 'self') {
        $targetUserId = $adminId;
        $row = admin_user_brief($pdo, $targetUserId);
        if (!$row) {
            api_json(['success' => false, 'message' => 'Current admin account was not found.'], 400);
        }
        $applicant = [
            'first_name' => $row['first_name'] ?? '',
            'middle_name' => $row['middle_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'email' => $row['email'] ?? '',
            'employee_no' => $row['employee_no'] ?? '',
            'department' => $row['department'] ?? '',
            'position' => $row['position'] ?? '',
            'salary' => $row['salary'] ?? '',
        ];
    } elseif ($applyFor === 'other' && $otherMode === 'existing') {
        $targetUserId = (int)($data['target_user_id'] ?? 0);
        if ($targetUserId <= 0) {
            api_json(['success' => false, 'message' => 'Please select an existing user.'], 400);
        }
        $row = admin_user_brief($pdo, $targetUserId);
        if (!$row) {
            api_json(['success' => false, 'message' => 'Selected user was not found.'], 400);
        }
        $applicant = [
            'first_name' => $row['first_name'] ?? '',
            'middle_name' => $row['middle_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'email' => $row['email'] ?? '',
            'employee_no' => $row['employee_no'] ?? '',
            'department' => $row['department'] ?? '',
            'position' => $row['position'] ?? '',
            'salary' => $row['salary'] ?? '',
        ];
    } elseif ($applyFor === 'other' && $otherMode === 'manual') {
        $targetUserId = $adminId;
        $skipCreditCheck = true;
        $applicant = [
            'first_name' => trim((string)($data['applicant_first_name'] ?? '')),
            'middle_name' => trim((string)($data['applicant_middle_name'] ?? '')),
            'last_name' => trim((string)($data['applicant_last_name'] ?? '')),
            'email' => trim((string)($data['applicant_email'] ?? '')),
            'employee_no' => trim((string)($data['applicant_employee_no'] ?? '')),
            'department' => trim((string)($data['applicant_department'] ?? '')),
            'position' => trim((string)($data['applicant_position'] ?? '')),
            'salary' => trim((string)($data['applicant_salary'] ?? '')),
        ];
        if ($applicant['first_name'] === '' || $applicant['last_name'] === '') {
            api_json(['success' => false, 'message' => 'Manual applicant first name and last name are required.'], 400);
        }
    } else {
        api_json(['success' => false, 'message' => 'Invalid application target option.'], 400);
    }

    $result = leave_apply_from_input_for_user($pdo, $targetUserId, $data, [
        'applicant' => $applicant,
        'skip_credit_check' => $skipCreditCheck,
    ]);

    if (!empty($result['ok'])) {
        api_json([
            'success' => true,
            'message' => $result['message'] ?? 'Submitted.',
            'balances' => $result['balances'] ?? teacher_leave_balance_snapshot($pdo, $targetUserId),
        ]);
    }

    api_json([
        'success' => false,
        'message' => $result['message'] ?? 'Request could not be processed.',
    ], 400);
}

api_json(['success' => false, 'message' => 'Method not allowed.'], 405);

