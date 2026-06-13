<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../includes/admin_credit_logic.php';

$adminId = api_require_admin();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $teachers = admin_list_teachers_for_credit($pdo);
    $users = admin_list_users_for_credit($pdo);
    $balances = admin_list_credit_balances($pdo);
    $defaultDate = (new DateTime('today'))->format('Y-m-d');
    $selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $selectedBalance = null;
    if ($selectedUserId > 0) {
        $selectedBalance = admin_get_credit_balance($pdo, $selectedUserId);
    }
    api_json([
        'success' => true,
        'teachers' => $teachers,
        'users' => $users,
        'balances' => $balances,
        'selected_balance' => $selectedBalance,
        'default_as_of_date' => $defaultDate,
    ]);
}

if ($method === 'POST') {
    $data = api_read_json();
    $action = (string)($data['action'] ?? 'credit');
    if ($action === 'credit') {
        $result = admin_credit_teacher_leaves($pdo, $adminId, $data);
    } elseif ($action === 'update_balance') {
        $result = admin_update_credit_balance($pdo, $adminId, $data);
    } elseif ($action === 'delete_balance') {
        $result = admin_delete_credit_balance($pdo, (int)($data['user_id'] ?? 0));
    } else {
        api_json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    if (!empty($result['ok'])) {
        api_json([
            'success' => true,
            'message' => $result['message'],
            'balances' => admin_list_credit_balances($pdo),
        ]);
    }

    api_json(['success' => false, 'message' => $result['message']], 400);
}

api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
