<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../includes/admin_users_logic.php';

$adminId = api_require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    api_json([
        'success' => true,
        'users' => admin_list_users_safe($pdo),
        'current_admin_id' => $adminId,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = api_read_json();
    $action = $data['action'] ?? '';

    if ($action === 'create') {
        $result = admin_create_user($pdo, $data);
    } elseif ($action === 'update') {
        $result = admin_update_user($pdo, $adminId, $data);
    } elseif ($action === 'toggle_status') {
        $targetId = (int)($data['id'] ?? 0);
        $newStatus = (string)($data['new_status'] ?? '');
        if ($targetId <= 0 || !in_array($newStatus, ['active', 'inactive'], true)) {
            api_json(['success' => false, 'message' => 'Invalid status update request.'], 400);
        }
        $roleStmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        $roleStmt->execute([$targetId]);
        $targetRole = $roleStmt->fetchColumn();
        if (!$targetRole) {
            api_json(['success' => false, 'message' => 'User not found.'], 404);
        }
        if ($targetRole === 'admin' && $newStatus === 'inactive') {
            api_json(['success' => false, 'message' => 'Admin accounts must remain active.'], 400);
        }
        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ? LIMIT 1');
        $stmt->execute([$newStatus, $targetId]);
        $result = ['ok' => true, 'message' => 'User status updated successfully.'];
    } elseif ($action === 'delete') {
        $targetId = (int)($data['id'] ?? 0);
        $result = admin_delete_user($pdo, $adminId, $targetId);
    } else {
        api_json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    if (!empty($result['ok'])) {
        api_json([
            'success' => true,
            'message' => $result['message'],
            'users' => admin_list_users_safe($pdo),
            'current_admin_id' => $adminId,
        ]);
    }

    api_json(['success' => false, 'message' => $result['message'] ?? 'Request failed.'], 400);
}

api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
