<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

api_require_admin();

$totalTeachersStmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'teacher'");
$totalTeachers = (int)$totalTeachersStmt->fetchColumn();

$totalLeavesStmt = $pdo->query('SELECT COUNT(*) AS total FROM leave_applications');
$totalLeaves = (int)$totalLeavesStmt->fetchColumn();

$pendingStmt = $pdo->query("SELECT COUNT(*) AS total FROM leave_applications WHERE status = 'pending'");
$pendingLeaves = (int)$pendingStmt->fetchColumn();

$approvedStmt = $pdo->query("SELECT COUNT(*) AS total FROM leave_applications WHERE status = 'approved'");
$approvedLeaves = (int)$approvedStmt->fetchColumn();

$rejectedStmt = $pdo->query("SELECT COUNT(*) AS total FROM leave_applications WHERE status = 'rejected'");
$rejectedLeaves = (int)$rejectedStmt->fetchColumn();

api_json([
    'success' => true,
    'stats' => [
        'total_teachers' => $totalTeachers,
        'total_leave_applications' => $totalLeaves,
        'pending_requests' => $pendingLeaves,
        'approved_requests' => $approvedLeaves,
        'rejected_requests' => $rejectedLeaves,
    ],
]);
