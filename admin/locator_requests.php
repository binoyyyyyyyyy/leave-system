<?php
require '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/app_nav.php';

checkLogin();

if (!isAdmin()) {
    die('Access denied');
}

$stmt = $pdo->query("
    SELECT ls.*, u.username
    FROM locator_slips ls
    LEFT JOIN users u ON u.id = ls.user_id
    ORDER BY ls.created_at DESC
");
$slips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Locator Requests</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #1f2937;
}

.container {
    width: 95%;
    max-width: 1250px;
    margin: 30px auto;
}

.box {
    background: #fff;
    padding: 26px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.page-header h1 {
    margin: 0;
    font-size: 28px;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

thead th {
    background: #eef2f7;
    padding: 13px 14px;
    font-size: 13px;
    text-align: left;
    color: #111827;
    border-bottom: 1px solid #d9dee7;
}

tbody tr {
    background: #ffffff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

tbody td {
    padding: 14px;
    font-size: 14px;
    vertical-align: top;
    border-top: 1px solid #eef0f3;
    border-bottom: 1px solid #eef0f3;
}

tbody td:first-child {
    border-left: 1px solid #eef0f3;
    border-radius: 10px 0 0 10px;
}

tbody td:last-child {
    border-right: 1px solid #eef0f3;
    border-radius: 0 10px 10px 0;
}

.name-cell {
    font-weight: 600;
    line-height: 1.3;
}

.date-cell {
    white-space: nowrap;
}

.purpose-cell,
.destination-cell {
    max-width: 180px;
    word-break: break-word;
}

.type-pill {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.badge {
    display: inline-block;
    padding: 6px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.approved {
    background: #d4edda;
    color: #155724;
}

.rejected {
    background: #f8d7da;
    color: #721c24;
}

.remarks-text {
    color: #555;
    font-size: 13px;
    max-width: 180px;
}

.action-box {
    min-width: 230px;
}

.action-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-start;
}

.action-form {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    padding: 10px;
    border-radius: 10px;
    margin-top: 8px;
}

textarea {
    width: 100%;
    min-height: 58px;
    padding: 9px;
    border: 1px solid #cfd6df;
    border-radius: 8px;
    resize: vertical;
    font-family: Arial, sans-serif;
    font-size: 13px;
    box-sizing: border-box;
}

.btn {
    display: inline-block;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    color: white;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.btn-print {
    background: #007bff;
}

.btn-approve {
    background: #22a55e;
    margin-top: 8px;
}

.btn-reject {
    background: #dc3545;
    margin-top: 8px;
}

.empty {
    text-align: center;
    padding: 25px;
    color: #666;
}

@media (max-width: 900px) {
    .box {
        padding: 18px;
    }

    table {
        min-width: 950px;
    }
}
</style>
</head>

<body>
<?php render_app_nav('admin', 'locator_requests'); ?>

<div class="container">
    <div class="box">
        <div class="page-header">
            <h1>Locator Slip Requests</h1>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date/Time</th>
                        <th>Purpose</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!$slips): ?>
                    <tr>
                        <td colspan="8" class="empty">No locator slip requests found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($slips as $slip): ?>
                    <tr>
                        <td class="name-cell"><?= htmlspecialchars($slip['name']) ?></td>

                        <td class="date-cell">
                            <?= htmlspecialchars(date('M d, Y', strtotime($slip['date_time']))) ?><br>
                            <?= htmlspecialchars(date('h:i A', strtotime($slip['date_time']))) ?>
                        </td>

                        <td class="purpose-cell"><?= htmlspecialchars($slip['purpose']) ?></td>

                        <td class="destination-cell"><?= htmlspecialchars($slip['destination']) ?></td>

                        <td>
                            <span class="type-pill">
                                <?= $slip['check_type'] === 'official_business' ? 'Official Business' : 'Official Time' ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= htmlspecialchars($slip['status']) ?>">
                                <?= ucfirst($slip['status']) ?>
                            </span>
                        </td>

                        <td>
                            <div class="remarks-text">
                                <?= !empty($slip['admin_remarks']) ? htmlspecialchars($slip['admin_remarks']) : '—' ?>
                            </div>
                        </td>

                        <td class="action-box">
                            <div class="action-row">
                                <a class="btn btn-print" href="print_locator.php?id=<?= $slip['id'] ?>" target="_blank">Print</a>
                            </div>

                            <?php if ($slip['status'] === 'pending'): ?>
                                <form class="action-form" method="POST" action="../includes/locator_logic.php">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?= $slip['id'] ?>">
                                    <textarea name="admin_remarks" placeholder="Approval remarks optional"></textarea>
                                    <button class="btn btn-approve" type="submit">Approve</button>
                                </form>

                                <form class="action-form" method="POST" action="../includes/locator_logic.php">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="id" value="<?= $slip['id'] ?>">
                                    <textarea name="admin_remarks" placeholder="Reason for rejection optional"></textarea>
                                    <button class="btn btn-reject" type="submit">Reject</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>