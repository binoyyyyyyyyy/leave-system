<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body.']);
    exit;
}

$username = trim((string)($data['username'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    exit;
}

if (($user['status'] ?? 'active') !== 'active') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Your account was set to inactive. Please contact the admin.',
    ]);
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role'] = $user['role'];

$redirect = $user['role'] === 'admin' ? 'admin/dashboard.php' : 'teacher/dashboard.php';

echo json_encode([
    'success' => true,
    'message' => 'Logged in.',
    'role' => $user['role'],
    'redirect' => $redirect,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
    ],
]);
