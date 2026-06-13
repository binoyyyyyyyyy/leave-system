<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/**
 * Discovery index for JSON endpoints. Add new routes here when you create them.
 */
echo json_encode([
    'name' => 'leave-system-api',
    'version' => 1,
    'conventions' => [
        'Authenticated requests use the PHP session cookie (same-origin fetch with credentials).',
        'POST bodies are JSON unless noted; responses are JSON with a success boolean.',
        'HTML pages set <body data-api-base="../api/"> (or api/ from site root) and load assets/js/api_client.js plus assets/js/live_poll.js for auto-refresh.',
    ],
    'endpoints' => [
        ['path' => 'auth/session.php', 'methods' => ['GET'], 'auth' => false, 'description' => 'Current session / user snapshot.'],
        ['path' => 'auth/login.php', 'methods' => ['POST'], 'auth' => false, 'body' => ['username', 'password'], 'description' => 'Login; sets session.'],
        ['path' => 'auth/logout.php', 'methods' => ['POST'], 'auth' => false, 'description' => 'Destroy session.'],
        ['path' => 'teacher/dashboard.php', 'methods' => ['GET'], 'auth' => 'teacher', 'description' => 'Welcome name + application counts.'],
        ['path' => 'teacher/leave_apply.php', 'methods' => ['GET', 'POST'], 'auth' => 'teacher', 'description' => 'GET: leave types + balances; POST: submit application JSON.'],
        ['path' => 'teacher/my_leaves.php', 'methods' => ['GET'], 'auth' => 'teacher', 'description' => 'All applications for the logged-in teacher.'],
        ['path' => 'admin/dashboard.php', 'methods' => ['GET'], 'auth' => 'admin', 'description' => 'System-wide stats.'],
        ['path' => 'admin/leave_requests.php', 'methods' => ['GET', 'POST'], 'auth' => 'admin', 'description' => 'GET: pending+approved list; POST: approve/reject.'],
        ['path' => 'admin/credit_leaves.php', 'methods' => ['GET', 'POST'], 'auth' => 'admin', 'description' => 'GET: teachers + default date; POST: add credits.'],
        ['path' => 'admin/users.php', 'methods' => ['GET', 'POST'], 'auth' => 'admin', 'description' => 'GET: list users; POST action create|update|delete with JSON body.'],
    ],
], JSON_PRETTY_PRINT);
