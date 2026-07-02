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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --accent-primary: #0f766e;
            --accent-primary-hover: #115e59;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-color);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 32px auto;
        }

        .topbar {
            margin-bottom: 24px;
        }

        .topbar h1 {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            margin: 0;
        }

        .info-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .muted {
            color: var(--text-secondary);
            font-size: 14px;
            margin: 0;
        }

        .live-hint {
            font-size: 12px;
            color: #0f766e;
            background: rgba(15, 118, 110, 0.08);
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: #cbd5e1;
        }

        .card h3 {
            margin: 0 0 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .card p {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .actions {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 32px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }

        .actions h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 16px;
            color: var(--text-primary);
        }

        .actions-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .actions a {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            background: var(--accent-primary);
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            transition: background 0.15s ease;
        }

        .actions a:hover {
            background: var(--accent-primary-hover);
        }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('admin', 'dashboard'); ?>
    <div class="container">
        <div class="topbar">
            <h1>Admin Dashboard</h1>
        </div>

        <div class="info-bar">
            <p class="muted" id="load_msg" style="display:none;"></p>
            <div class="live-hint" id="live_hint" style="display:none;">
                <span class="pulse-indicator"></span>
                Stats update automatically
            </div>
        </div>

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
            <div class="actions-grid">
                <a href="manage_users.php">Manage Users</a>
                <a href="leave_requests.php">View Leave Requests</a>
                <a href="credit_leaves.php">Credit Leave Balances</a>
            </div>
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
            document.getElementById('live_hint').style.display = 'inline-flex';
            if (window.LSLive) {
                LSLive.pollGet('admin/dashboard.php', 10000, paintAdminDashboard);
            }
        })();
    </script>
</body>
</html>
