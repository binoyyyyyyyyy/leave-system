<?php
require '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/app_nav.php';

checkLogin();

if (!isTeacher()) {
    die('Access denied');
}

$stmt = $pdo->prepare("SELECT * FROM locator_slips WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$slips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Locator Slips</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .container { width: 95%; max-width: 1100px; margin: 30px auto; }
        .box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background: #f1f3f5; }
        .badge { padding: 5px 9px; border-radius: 999px; font-size: 12px; font-weight: bold; }
        .pending { background: #fff3cd; color: #856404; }
        .approved { background: #d4edda; color: #155724; }
        .rejected { background: #f8d7da; color: #721c24; }
        a.btn { background: #007bff; color: white; padding: 7px 10px; border-radius: 5px; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
<?php render_app_nav('teacher', 'my_locator'); ?>

<div class="container">
    <div class="box">
        <h1>My Locator Slips</h1>

        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Purpose</th>
                    <th>Destination</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Print</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$slips): ?>
                <tr><td colspan="6">No locator slips found.</td></tr>
            <?php endif; ?>

            <?php foreach ($slips as $slip): ?>
                <tr>
                    <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($slip['date_time']))) ?></td>
                    <td><?= htmlspecialchars($slip['purpose']) ?></td>
                    <td><?= htmlspecialchars($slip['destination']) ?></td>
                    <td><?= $slip['check_type'] === 'official_business' ? 'Official Business' : 'Official Time' ?></td>
                    <td>
                        <span class="badge <?= htmlspecialchars($slip['status']) ?>">
                            <?= ucfirst($slip['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn" href="../admin/print_locator.php?id=<?= $slip['id'] ?>" target="_blank">Print</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>