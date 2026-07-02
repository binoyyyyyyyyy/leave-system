<?php
require '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/app_nav.php';

checkLogin();

if (!isAdmin()) {
    die('Access denied');
}

// get all teachers
$teachers = $pdo->query("SELECT id, first_name, last_name, position, department FROM users WHERE role='teacher'")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Apply Locator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        --bg-color: #f8fafc;
        --accent-primary: #0f766e;
        --accent-primary-hover: #115e59;
        --card-bg: #ffffff;
        --card-border: #e2e8f0;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --input-bg: #ffffff;
        --input-border: #cbd5e1;
        --input-focus-border: #0f766e;
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-main); background-color: var(--bg-color); color: var(--text-primary); min-height: 100vh; }
    .container { width: 90%; max-width: 700px; margin: 40px auto; }
    .topbar { margin-bottom: 24px; }
    .topbar h1 { font-size: 24px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.5px; margin: 0; }
    .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 32px; box-shadow: var(--shadow-card); }
    .form-group { margin-bottom: 20px; }
    label { display: block; font-size: 14px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
    input[type="text"], input[type="datetime-local"], select, textarea { width: 100%; padding: 12px 14px; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; font-family: var(--font-main); font-size: 14px; color: var(--text-primary); outline: none; transition: all 0.15s ease; box-sizing: border-box; }
    input:focus, select:focus, textarea:focus { border-color: var(--input-focus-border); box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12); }
    textarea { resize: vertical; min-height: 100px; }
    input[readonly] { background: #f1f5f9; color: var(--text-secondary); cursor: not-allowed; }
    .hidden { display: none !important; }
    button { background: var(--accent-primary); color: white; border: none; padding: 14px 24px; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; font-family: var(--font-main); transition: background 0.15s ease; width: 100%; margin-top: 24px; }
    button:hover { background: var(--accent-primary-hover); }
    .target-box { border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #f8fafc; }
</style>
<script>
function toggleMode() {
    let mode = document.getElementById('apply_for').value;
    document.getElementById('self_fields').classList.toggle('hidden', mode !== 'self');
    document.getElementById('other_fields').classList.toggle('hidden', mode !== 'other');
}

function fillTeacher() {
    let select = document.getElementById('teacher_select');
    let selected = select.options[select.selectedIndex];
    if (selected.value === '') return;
    document.getElementById('name').value = selected.dataset.name;
    document.getElementById('position').value = selected.dataset.position;
    document.getElementById('station').value = selected.dataset.station;
}
</script>
</head>
<body>
<?php render_app_nav('admin', 'apply_locator'); ?>
<div class="container">
    <div class="topbar">
        <h1>Apply Locator Slip</h1>
    </div>
    <div class="card">
        <form method="POST" action="../includes/locator_logic.php">
            <input type="hidden" name="action" value="apply_admin">

            <div class="form-group">
                <label>Apply For</label>
                <select name="apply_for" id="apply_for" onchange="toggleMode()" required>
                    <option value="">Select...</option>
                    <option value="self">Myself</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- SELF -->
            <div id="self_fields" class="target-box hidden">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" value="<?= htmlspecialchars($admin['first_name'].' '.$admin['last_name']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" value="<?= htmlspecialchars($admin['position']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Permanent Station</label>
                    <input type="text" value="<?= htmlspecialchars($admin['department']) ?>" readonly>
                </div>
            </div>

            <!-- OTHER -->
            <div id="other_fields" class="target-box hidden">
                <div class="form-group">
                    <label>Select Teacher (optional)</label>
                    <select id="teacher_select" onchange="fillTeacher()">
                        <option value="">Manual Entry...</option>
                        <?php foreach($teachers as $t): ?>
                        <option 
                            value="<?= htmlspecialchars($t['id']) ?>"
                            data-name="<?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?>"
                            data-position="<?= htmlspecialchars($t['position']) ?>"
                            data-station="<?= htmlspecialchars($t['department']) ?>"
                        >
                            <?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="name">
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" id="position">
                </div>
                <div class="form-group">
                    <label>Permanent Station</label>
                    <input type="text" name="permanent_station" id="station">
                </div>
            </div>

            <div class="form-group">
                <label>Purpose</label>
                <textarea name="purpose" required></textarea>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="check_type" required>
                    <option value="official_business">Official Business</option>
                    <option value="official_time">Official Time</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="date_time" required>
            </div>

            <div class="form-group">
                <label>Destination</label>
                <input type="text" name="destination" required>
            </div>

            <button type="submit">Submit Locator Slip</button>
        </form>
    </div>
</div>
</body>
</html>