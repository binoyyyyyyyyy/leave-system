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
<html>
<head>
<title>Admin Apply Locator</title>

<style>
body { font-family: Arial; background:#f4f6f9; margin:0; }

.container {
    max-width: 700px;
    margin: 30px auto;
    background:white;
    padding:20px;
    border-radius:10px;
}

label { display:block; margin-top:10px; font-weight:bold; }

input, textarea, select {
    width:100%;
    padding:8px;
    margin-top:5px;
    border-radius:6px;
    border:1px solid #ccc;
}

.hidden { display:none; }

button {
    margin-top:15px;
    padding:10px;
    background:#007bff;
    color:white;
    border:none;
    border-radius:6px;
}
</style>

<script>
function toggleMode() {
    let mode = document.getElementById('apply_for').value;

    document.getElementById('self_fields').style.display = (mode === 'self') ? 'block' : 'none';
    document.getElementById('other_fields').style.display = (mode === 'other') ? 'block' : 'none';
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

<h2>Apply Locator Slip</h2>

<form method="POST" action="../includes/locator_logic.php">

<input type="hidden" name="action" value="apply_admin">

<label>Apply For</label>
<select name="apply_for" id="apply_for" onchange="toggleMode()" required>
    <option value="">Select</option>
    <option value="self">Myself</option>
    <option value="other">Other</option>
</select>

<!-- SELF -->
<div id="self_fields" class="hidden">
    <label>Name</label>
    <input type="text" value="<?= $admin['first_name'].' '.$admin['last_name'] ?>" readonly>

    <label>Position</label>
    <input type="text" value="<?= $admin['position'] ?>" readonly>

    <label>Permanent Station</label>
    <input type="text" value="<?= $admin['department'] ?>" readonly>
</div>

<!-- OTHER -->
<div id="other_fields" class="hidden">

    <label>Select Teacher (optional)</label>
    <select id="teacher_select" onchange="fillTeacher()">
        <option value="">Manual Entry</option>

        <?php foreach($teachers as $t): ?>
        <option 
            value="<?= $t['id'] ?>"
            data-name="<?= $t['first_name'].' '.$t['last_name'] ?>"
            data-position="<?= $t['position'] ?>"
            data-station="<?= $t['department'] ?>"
        >
            <?= $t['first_name'].' '.$t['last_name'] ?>
        </option>
        <?php endforeach; ?>
    </select>

    <label>Name</label>
    <input type="text" name="name" id="name">

    <label>Position</label>
    <input type="text" name="position" id="position">

    <label>Permanent Station</label>
    <input type="text" name="permanent_station" id="station">
</div>

<label>Purpose</label>
<textarea name="purpose" required></textarea>

<label>Type</label>
<select name="check_type" required>
    <option value="official_business">Official Business</option>
    <option value="official_time">Official Time</option>
</select>

<label>Date & Time</label>
<input type="datetime-local" name="date_time" required>

<label>Destination</label>
<input type="text" name="destination" required>

<button type="submit">Submit</button>

</form>

</div>

</body>
</html>