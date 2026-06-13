<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../includes/leave_apply_logic.php';
require_once __DIR__ . '/../../includes/leave_applicant_schema.php';

ensure_leave_applicant_columns($pdo);

$userId = api_require_teacher();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $today = new DateTime('today');
    $todayStr = $today->format('Y-m-d');

    $stmt = $pdo->query("SELECT id, leave_name FROM leave_types WHERE leave_name <> 'Others' ORDER BY id ASC");
    $leaveTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $balances = teacher_leave_balance_snapshot($pdo, $userId);

    api_json([
        'success' => true,
        'today' => $todayStr,
        'leave_types' => $leaveTypes,
        'balances' => $balances,
    ]);
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        api_json(['success' => false, 'message' => 'Invalid JSON body.'], 400);
    }

    $result = teacher_apply_leave_from_input($pdo, $userId, $data);

    if (!empty($result['ok'])) {
        api_json([
            'success' => true,
            'message' => $result['message'],
            'balances' => $result['balances'],
        ]);
    }

    api_json([
        'success' => false,
        'message' => $result['message'] ?? 'Request could not be processed.',
    ], 400);
}

api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
