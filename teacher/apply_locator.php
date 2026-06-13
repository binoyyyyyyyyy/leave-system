<?php
require '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/app_nav.php';

checkLogin();

if (!isTeacher()) {
    die('Access denied');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$now = date('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Apply Locator Slip</title>

<style>
body {
    font-family: "Times New Roman", serif;
    background: #f4f6f9;
    margin: 0;
}

.page {
    width: 8.5in;
    min-height: 11in;
    margin: 30px auto;
    background: #fff;
    padding: 0.65in 0.75in;
    box-sizing: border-box;
    box-shadow: 0 2px 10px rgba(0,0,0,0.12);
}

.annex {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 15px;
}

.header {
    text-align: center;
    margin-bottom: 12px;
}

.logo {
    width: 70px;
    height: 70px;
    object-fit: contain;
    margin-bottom: 6px;
}

.head-rp {
    font-family: "Old English Text MT", serif;
    font-size: 16px;
}

.head-deped {
    font-family: "Old English Text MT", serif;
    font-size: 19px;
    font-weight: bold;
}

.form-title {
    text-align: center;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 6px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

td {
    border: 1px solid #000;
    padding: 4px 6px;
    vertical-align: top;
}

.label {
    width: 30%;
    font-weight: bold;
}

input[type="text"],
input[type="datetime-local"],
textarea {
    width: 100%;
    border: none;
    outline: none;
    font-family: "Times New Roman", serif;
    font-size: 14px;
    box-sizing: border-box;
    background: transparent;
}

textarea {
    min-height: 55px;
    resize: vertical;
}

.check-line {
    display: flex;
    gap: 90px;
}

.check-line label {
    font-weight: normal;
}

.signature-row td {
    height: 70px;
    text-align: center;
    vertical-align: bottom;
    font-size: 13px;
    padding-bottom: 8px;
}

.signature-line {
    width: 80%;
    border-top: 1px solid #000;
    margin: 0 auto 4px auto;
}

.cert-box {
    border: 1px solid #000;
    margin-top: 35px;
    padding: 18px;
    min-height: 170px;
    font-size: 14px;
}

.cert-title {
    text-align: center;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 25px;
}

.cert-text {
    text-indent: 40px;
    line-height: 1.4;
}

.cert-signature {
    margin-top: 35px;
    margin-left: 55%;
}

.submit-btn {
    margin-top: 20px;
    background: #007bff;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-family: Arial, sans-serif;
}

@media print {
    .app-nav,
    .submit-btn {
        display: none !important;
    }

    @page {
        size: letter;
        margin: 0;
    }

    body {
        background: #fff;
    }

    .page {
        margin: 0;
        width: 8.5in;
        min-height: 11in;
        padding: 0.65in 0.75in;
        box-shadow: none;
    }
}
</style>
</head>

<body>

<?php render_app_nav('teacher', 'apply_locator'); ?>

<div class="page">
<form method="POST" action="../includes/locator_logic.php">
    <input type="hidden" name="action" value="apply">

    <div class="annex">REVISED ANNEX E</div>

    <div class="header">
        <img src="../includes/deped_logo.png" class="logo" alt="DepEd Logo">
        <div class="head-rp">Republic of the Philippines</div>
        <div class="head-deped">Department of Education</div>
    </div>

    <div class="form-title">LOCATOR SLIP</div>

    <table>
        <tr>
            <td class="label">NAME</td>
            <td>
                <input type="text" name="name" value="<?= htmlspecialchars($fullName) ?>" required>
            </td>
        </tr>

        <tr>
            <td class="label">Position/Designation</td>
            <td>
                <input type="text" name="position" value="<?= htmlspecialchars($user['position'] ?? '') ?>" required>
            </td>
        </tr>

        <tr>
            <td class="label">Permanent Station</td>
            <td>
                <input type="text" name="permanent_station" value="<?= htmlspecialchars($user['department'] ?? '') ?>" required>
            </td>
        </tr>

        <tr>
            <td class="label">
                Purpose of Travel<br>
                <span style="font-weight: normal;">(must be supported by attachments)</span>
            </td>
            <td>
                <textarea name="purpose" required></textarea>
            </td>
        </tr>

        <tr>
            <td class="label">Please Check</td>
            <td>
                <div class="check-line">
                    <label>
                        <input type="radio" name="check_type" value="official_business" required>
                        Official Business
                    </label>

                    <label>
                        <input type="radio" name="check_type" value="official_time" required>
                        Official Time
                    </label>
                </div>
            </td>
        </tr>

        <tr>
            <td class="label">Date and Time</td>
            <td>
                <input type="datetime-local" name="date_time" value="<?= $now ?>" required>
            </td>
        </tr>

        <tr>
            <td class="label">Destination</td>
            <td>
                <input type="text" name="destination" required>
            </td>
        </tr>

        <tr class="signature-row">
            <td>
                <div class="signature-line"></div>
                Signature of Requesting Employee
            </td>
            <td>
                <div class="signature-line"></div>
                Signature of Head of Office
            </td>
        </tr>
    </table>

    <div class="cert-box">
        <div class="cert-title">CERTIFICATION</div>

        <p>To the concerned:</p>

        <p class="cert-text">
            This is to certify that the above-named DepEd official/personnel has visited
            or appeared in this Office/place for the purpose and during the date and time
            stated above.
        </p>

        <div class="cert-signature">
            Name and Signature:<br>
            Position/Designation:<br>
            Office:
        </div>
    </div>

    <button class="submit-btn" type="submit">Submit Locator Slip</button>
</form>
</div>

</body>
</html>