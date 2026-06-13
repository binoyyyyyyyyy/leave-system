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

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
}

.container {
    width: 90%;
    max-width: 1100px;
    margin: 30px auto;
}

.topbar {
    margin-bottom: 25px;
}

.topbar h1 {
    margin: 0;
}

.welcome {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.section-title {
    margin: 25px 0 15px;
    font-size: 20px;
    color: #222;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.card h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #333;
}

.card p {
    font-size: 28px;
    font-weight: bold;
    margin: 0;
    color: #222;
}

.actions {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.actions h2 {
    margin-top: 0;
}

.actions a {
    display: inline-block;
    margin: 10px 10px 0 0;
    text-decoration: none;
    background: #007bff;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
}

.actions a.secondary {
    background: #28a745;
}

.muted {
    color: #666;
}

.live-hint {
    font-size: 12px;
    color: #888;
    margin-top: 8px;
    display: none;
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
        <p class="live-hint" id="live_hint">Stats update automatically while this tab is open.</p>
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
        <a href="apply_leave.php">Apply for Leave</a>
        <a class="secondary" href="apply_locator.php">Locator Slip</a>
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