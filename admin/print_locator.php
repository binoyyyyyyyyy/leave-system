<?php
require '../includes/auth.php';
require_once '../includes/db.php';

checkLogin();

$id = (int)($_GET['id'] ?? 0);

if (isAdmin()) {
    $stmt = $pdo->prepare("
        SELECT ls.*, 
               approver.first_name AS approver_first, 
               approver.last_name AS approver_last, 
               approver.position AS approver_position
        FROM locator_slips ls
        LEFT JOIN users approver ON approver.id = ls.approved_by
        WHERE ls.id = ?
    ");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("
        SELECT ls.*, 
               approver.first_name AS approver_first, 
               approver.last_name AS approver_last, 
               approver.position AS approver_position
        FROM locator_slips ls
        LEFT JOIN users approver ON approver.id = ls.approved_by
        WHERE ls.id = ? AND ls.user_id = ?
    ");
    $stmt->execute([$id, $_SESSION['user_id']]);
}

$slip = $stmt->fetch();

if (!$slip) {
    die('Locator slip not found.');
}

$officialBusiness = $slip['check_type'] === 'official_business' ? '☑' : '☐';
$officialTime = $slip['check_type'] === 'official_time' ? '☑' : '☐';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Locator Slip</title>

<style>
body {
    font-family: "Times New Roman", serif;
    background: #fff;
    margin: 0;
}

.page {
    width: 8.5in;
    min-height: 11in;
    margin: 0 auto;
    padding: 0.65in 0.75in;
}

/* PRINT BUTTON */
.print-btn {
    font-family: Arial, sans-serif;
    background: #007bff;
    color: white;
    border: none;
    padding: 9px 14px;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;
}

/* HEADER */
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
    font-weight: bold;
}

.head-deped {
    font-family: "Old English Text MT", serif;
    font-size: 19px;
    font-weight: bold;
}
.signature-line {
    width: 80%;
    border-top: 1px solid #000;
    margin: 0 auto 4px auto;
}

/* TITLE */
.annex {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 15px;
}

.form-title {
    text-align: center;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 6px;
}

/* TABLE */
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

.purpose-cell {
    min-height: 55px;
}

.check-line {
    display: flex;
    gap: 90px;
}

/* SIGNATURE */
.signature-row td {
    height: 55px;
    text-align: center;
    vertical-align: bottom;
    font-size: 13px;
}

/* CERTIFICATION */
.cert-box {
    border: 1px solid #000;
    margin-top: 35px;
    padding: 18px;
    min-height: 170px;
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

/* PRINT */
@media print {
    .print-btn { display: none; }

    @page {
        size: letter;
        margin: 0;
    }

    .page {
        margin: 0;
        width: 8.5in;
        min-height: 11in;
        padding: 0.65in 0.75in;
    }
}
</style>
</head>

<body>

<div class="page">

    <button class="print-btn" onclick="window.print()">Print</button>

    <div class="annex">REVISED ANNEX E</div>

    <!-- ✅ FIXED HEADER -->
    <div class="header">
        <img src="../includes/deped_logo.png" class="logo" alt="DepEd Logo">
        <div class="head-rp">Republic of the Philippines</div>
        <div class="head-deped">Department of Education</div>
    </div>

    <div class="form-title">LOCATOR SLIP</div>

    <table>
        <tr>
            <td class="label">NAME</td>
            <td><?= htmlspecialchars($slip['name']) ?></td>
        </tr>

        <tr>
            <td class="label">Position/Designation</td>
            <td><?= htmlspecialchars($slip['position']) ?></td>
        </tr>

        <tr>
            <td class="label">Permanent Station</td>
            <td><?= htmlspecialchars($slip['permanent_station']) ?></td>
        </tr>

        <tr>
            <td class="label">
                Purpose of Travel<br>
                <span style="font-weight: normal;">(must be supported by attachments)</span>
            </td>
            <td class="purpose-cell">
                <?= nl2br(htmlspecialchars($slip['purpose'])) ?>
            </td>
        </tr>

        <tr>
            <td class="label">Please Check</td>
            <td>
                <div class="check-line">
                    <span><?= $officialBusiness ?> Official Business</span>
                    <span><?= $officialTime ?> Official Time</span>
                </div>
            </td>
        </tr>

        <tr>
            <td class="label">Date and Time</td>
            <td><?= htmlspecialchars(date('F d, Y h:i A', strtotime($slip['date_time']))) ?></td>
        </tr>

        <tr>
            <td class="label">Destination</td>
            <td><?= htmlspecialchars($slip['destination']) ?></td>
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

</div>

</body>
</html>