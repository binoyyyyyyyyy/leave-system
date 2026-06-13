<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

/**
 * @param array<string,mixed> $data
 */
function api_json(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/**
 * @return array<string,mixed>
 */
function api_read_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function api_require_teacher(): int
{
    if (!isset($_SESSION['user_id']) || !isTeacher()) {
        api_json(['success' => false, 'message' => 'Unauthorized.'], 401);
    }

    return (int)$_SESSION['user_id'];
}

function api_require_admin(): int
{
    if (!isset($_SESSION['user_id']) || !isAdmin()) {
        api_json(['success' => false, 'message' => 'Unauthorized.'], 401);
    }

    return (int)$_SESSION['user_id'];
}
