<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../includes/teacher_my_leaves.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$userId = api_require_teacher();
$applications = teacher_my_leaves_list($pdo, $userId);

api_json([
    'success' => true,
    'applications' => $applications,
]);
