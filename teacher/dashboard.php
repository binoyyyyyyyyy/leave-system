<?php
require '../includes/auth.php';
require_once '../includes/app_nav.php';

checkLogin();

if (!isTeacher()) {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard</title>
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

.welcome {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-left: 4px solid var(--accent-primary);
    padding: 24px;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 32px;
}

.welcome h2 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.welcome p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.5;
}

.section-title {
    margin: 32px 0 16px;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.3px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
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
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    color: var(--text-secondary);
}

.card p {
    font-size: 28px;
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
    margin-top: 36px;
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
    transition: all 0.15s ease;
    border: 1px solid var(--accent-primary);
}

.actions a:hover {
    background: var(--accent-primary-hover);
    border-color: var(--accent-primary-hover);
}

.actions a.secondary {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--card-border);
}

.actions a.secondary:hover {
    background: #f1f5f9;
    color: var(--text-primary);
    border-color: #cbd5e1;
}

.muted {
    color: var(--text-secondary);
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
    margin-top: 12px;
}
</style>
</head>

<body data-api-base="../api/">

<?php render_app_nav('teacher', 'dashboard'); ?>

<div class="container">
    <div class="topbar">
        <h1>Teacher Dashboard</h1>
    </div>

    <div class="welcome">
        <h2 id="welcome_title">Welcome!</h2>
        <p>Here you can submit your leave application, apply for locator slips, and track their status.</p>
        <p class="muted" id="load_msg" style="display:none;"></p>
        <div class="live-hint" id="live_hint" style="display:none;">Stats update automatically</div>
    </div>

    <h2 class="section-title">Leave Applications</h2>

    <div class="cards">
        <div class="card">
            <h3>Total Leave Applications</h3>
            <p id="stat_total">—</p>
        </div>

        <div class="card">
            <h3>Pending Leave</h3>
            <p id="stat_pending">—</p>
        </div>

        <div class="card">
            <h3>Approved Leave</h3>
            <p id="stat_approved">—</p>
        </div>

        <div class="card">
            <h3>Rejected Leave</h3>
            <p id="stat_rejected">—</p>
        </div>
    </div>

    <h2 class="section-title">Locator Slips</h2>

    <div class="cards">
        <div class="card">
            <h3>Total Locator Slips</h3>
            <p id="locator_total">—</p>
        </div>

        <div class="card">
            <h3>Pending Locator</h3>
            <p id="locator_pending">—</p>
        </div>

        <div class="card">
            <h3>Approved Locator</h3>
            <p id="locator_approved">—</p>
        </div>

        <div class="card">
            <h3>Rejected Locator</h3>
            <p id="locator_rejected">—</p>
        </div>
    </div>

    <div class="actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="apply_leave.php">Apply for Leave</a>
            <a class="secondary" href="apply_locator.php">Apply for Locator Slip</a>
        </div>
    </div>
</div>

<script src="../assets/js/api_client.js"></script>
<script src="../assets/js/live_poll.js"></script>

<script>
function paintTeacherDashboard(data) {
    var u = data.user || {};
    document.getElementById('welcome_title').textContent =
        'Welcome, ' + (u.first_name || '') + ' ' + (u.last_name || '') + '!';

    var s = data.stats || {};
    document.getElementById('stat_total').textContent = s.total_applications ?? '0';
    document.getElementById('stat_pending').textContent = s.pending ?? '0';
    document.getElementById('stat_approved').textContent = s.approved ?? '0';
    document.getElementById('stat_rejected').textContent = s.rejected ?? '0';

    var l = data.locator_stats || {};
    document.getElementById('locator_total').textContent = l.total_locator ?? '0';
    document.getElementById('locator_pending').textContent = l.pending_locator ?? '0';
    document.getElementById('locator_approved').textContent = l.approved_locator ?? '0';
    document.getElementById('locator_rejected').textContent = l.rejected_locator ?? '0';
}

(async function () {
    var res = await LSApi.get('teacher/dashboard.php');

    if (!res.ok || !res.data.success) {
        document.getElementById('load_msg').style.display = 'block';
        document.getElementById('load_msg').textContent =
            (res.data && res.data.message) ? res.data.message : 'Could not load dashboard.';
        return;
    }

    paintTeacherDashboard(res.data);
    document.getElementById('live_hint').style.display = 'block';

    if (window.LSLive) {
        LSLive.pollGet('teacher/dashboard.php', 10000, paintTeacherDashboard);
    }
})();
</script>

</body>
</html>