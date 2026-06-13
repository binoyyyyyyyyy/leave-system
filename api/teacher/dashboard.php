<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$userId = api_require_teacher();

$userStmt = $pdo->prepare('SELECT first_name, last_name FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Leave Application Stats
|--------------------------------------------------------------------------
*/
$totalStmt = $pdo->prepare('SELECT COUNT(*) FROM leave_applications WHERE teacher_id = ?');
$totalStmt->execute([$userId]);
$totalApplications = (int)$totalStmt->fetchColumn();

$pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM leave_applications WHERE teacher_id = ? AND status = 'pending'");
$pendingStmt->execute([$userId]);
$pendingApplications = (int)$pendingStmt->fetchColumn();

$approvedStmt = $pdo->prepare("SELECT COUNT(*) FROM leave_applications WHERE teacher_id = ? AND status = 'approved'");
$approvedStmt->execute([$userId]);
$approvedApplications = (int)$approvedStmt->fetchColumn();

$rejectedStmt = $pdo->prepare("SELECT COUNT(*) FROM leave_applications WHERE teacher_id = ? AND status = 'rejected'");
$rejectedStmt->execute([$userId]);
$rejectedApplications = (int)$rejectedStmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Locator Slip Stats
|--------------------------------------------------------------------------
*/
$locatorTotalStmt = $pdo->prepare('SELECT COUNT(*) FROM locator_slips WHERE user_id = ?');
$locatorTotalStmt->execute([$userId]);
$totalLocator = (int)$locatorTotalStmt->fetchColumn();

$locatorPendingStmt = $pdo->prepare("SELECT COUNT(*) FROM locator_slips WHERE user_id = ? AND status = 'pending'");
$locatorPendingStmt->execute([$userId]);
$pendingLocator = (int)$locatorPendingStmt->fetchColumn();

$locatorApprovedStmt = $pdo->prepare("SELECT COUNT(*) FROM locator_slips WHERE user_id = ? AND status = 'approved'");
$locatorApprovedStmt->execute([$userId]);
$approvedLocator = (int)$locatorApprovedStmt->fetchColumn();

$locatorRejectedStmt = $pdo->prepare("SELECT COUNT(*) FROM locator_slips WHERE user_id = ? AND status = 'rejected'");
$locatorRejectedStmt->execute([$userId]);
$rejectedLocator = (int)$locatorRejectedStmt->fetchColumn();

api_json([
    'success' => true,

    'user' => $user ?: [
        'first_name' => '',
        'last_name' => '',
    ],

    'stats' => [
        'total_applications' => $totalApplications,
        'pending' => $pendingApplications,
        'approved' => $approvedApplications,
        'rejected' => $rejectedApplications,
    ],

    'locator_stats' => [
        'total_locator' => $totalLocator,
        'pending_locator' => $pendingLocator,
        'approved_locator' => $approvedLocator,
        'rejected_locator' => $rejectedLocator,
    ],
]);