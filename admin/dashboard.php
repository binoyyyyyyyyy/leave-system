<?php
require '../includes/auth.php';
require_once '../includes/app_nav.php';
checkLogin();
if (!isAdmin()) {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { width: 90%; max-width: 1100px; margin: 30px auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .topbar h1 { margin: 0; }
        .logout-btn { text-decoration: none; background: #d9534f; color: white; padding: 10px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card h3 { margin: 0 0 10px; font-size: 18px; color: #333; }
        .card p { font-size: 28px; font-weight: bold; margin: 0; color: #222; }
        .actions { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .actions h2 { margin-top: 0; }
        .actions a { display: inline-block; margin: 10px 10px 0 0; text-decoration: none; background: #007bff; color: white; padding: 10px 16px; border-radius: 6px; }
        .muted { color: #666; font-size: 14px; }
        .live-hint { font-size: 12px; color: #888; margin: 0 0 16px; display: none; }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('admin', 'dashboard'); ?>
    <div class="container">
        <div class="topbar">
            <h1>Admin Dashboard</h1>
        </div>

        <p class="muted" id="load_msg" style="display:none;"></p>
        <p class="live-hint" id="live_hint">Stats update automatically while this tab is open.</p>

        <div class="cards">
            <div class="card">
                <h3>Total Teachers</h3>
                <p id="stat_teachers">—</p>
            </div>
            <div class="card">
                <h3>Total Leave Applications</h3>
                <p id="stat_leaves">—</p>
            </div>
            <div class="card">
                <h3>Pending Requests</h3>
                <p id="stat_pending">—</p>
            </div>
            <div class="card">
                <h3>Approved Requests</h3>
                <p id="stat_approved">—</p>
            </div>
            <div class="card">
                <h3>Rejected Requests</h3>
                <p id="stat_rejected">—</p>
            </div>
        </div>

        <div class="actions">
            <h2>Quick Actions</h2>
            <a href="manage_users.php">Manage Users</a>
            <a href="leave_requests.php">View Leave Requests</a>
            <a href="credit_leaves.php">Credit Leave Balances</a>
        </div>
    </div>
    <script src="../assets/js/api_client.js"></script>
    <script src="../assets/js/live_poll.js"></script>
    <script>
        function paintAdminDashboard(data) {
            var s = data.stats || {};
            document.getElementById('stat_teachers').textContent = s.total_teachers != null ? s.total_teachers : '0';
            document.getElementById('stat_leaves').textContent = s.total_leave_applications != null ? s.total_leave_applications : '0';
            document.getElementById('stat_pending').textContent = s.pending_requests != null ? s.pending_requests : '0';
            document.getElementById('stat_approved').textContent = s.approved_requests != null ? s.approved_requests : '0';
            document.getElementById('stat_rejected').textContent = s.rejected_requests != null ? s.rejected_requests : '0';
        }

        (async function () {
            var res = await LSApi.get('admin/dashboard.php');
            if (!res.ok || !res.data.success) {
                var el = document.getElementById('load_msg');
                el.style.display = 'block';
                el.textContent = (res.data && res.data.message) ? res.data.message : 'Could not load dashboard.';
                return;
            }
            paintAdminDashboard(res.data);
            document.getElementById('live_hint').style.display = 'block';
            if (window.LSLive) {
                LSLive.pollGet('admin/dashboard.php', 10000, paintAdminDashboard);
            }
        })();
    </script>
</body>
</html>
