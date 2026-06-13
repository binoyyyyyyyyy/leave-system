<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'authenticated' => false,
    ]);
    exit;
}

$uid = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id, username, first_name, last_name, role, status FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        'success' => true,
        'authenticated' => false,
    ]);
    exit;
}

if (($user['status'] ?? 'active') !== 'active') {
    session_unset();
    session_destroy();
    echo json_encode([
        'success' => true,
        'authenticated' => false,
        'message' => 'Your account was set to inactive. Please contact the admin.',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'authenticated' => true,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'role' => $user['role'],
    ],
]);
