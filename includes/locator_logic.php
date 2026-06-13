<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

checkLogin();

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| TEACHER APPLY (existing)
|--------------------------------------------------------------------------
*/
if ($action === 'apply') {

    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $permanentStation = trim($_POST['permanent_station'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $dateTime = trim($_POST['date_time'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $checkType = $_POST['check_type'] ?? '';

    if (
        $name === '' ||
        $position === '' ||
        $permanentStation === '' ||
        $purpose === '' ||
        $dateTime === '' ||
        $destination === '' ||
        !in_array($checkType, ['official_business', 'official_time'], true)
    ) {
        die('Please complete all required fields.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO locator_slips
        (user_id, name, position, permanent_station, purpose, date_time, destination, check_type)
        VALUES
        (:user_id, :name, :position, :permanent_station, :purpose, :date_time, :destination, :check_type)
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $name,
        ':position' => $position,
        ':permanent_station' => $permanentStation,
        ':purpose' => $purpose,
        ':date_time' => $dateTime,
        ':destination' => $destination,
        ':check_type' => $checkType,
    ]);

    header('Location: ../teacher/my_locator.php?success=1');
    exit;
}


/*
|--------------------------------------------------------------------------
| ADMIN APPLY (NEW FEATURE)
|--------------------------------------------------------------------------
*/
if ($action === 'apply_admin' && isAdmin()) {

    $applyFor = $_POST['apply_for'] ?? '';

    $purpose = trim($_POST['purpose'] ?? '');
    $dateTime = trim($_POST['date_time'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $checkType = $_POST['check_type'] ?? '';

    if (
        $purpose === '' ||
        $dateTime === '' ||
        $destination === '' ||
        !in_array($checkType, ['official_business', 'official_time'], true)
    ) {
        die('Please complete all required fields.');
    }

    /*
    |--------------------------------------------------------------------------
    | CASE 1: ADMIN (SELF)
    |--------------------------------------------------------------------------
    */
    if ($applyFor === 'self') {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $u = $stmt->fetch();

        if (!$u) {
            die('User not found.');
        }

        $targetUserId = $userId;
        $name = $u['first_name'] . ' ' . $u['last_name'];
        $position = $u['position'];
        $station = $u['department'];
    }

    /*
    |--------------------------------------------------------------------------
    | CASE 2: OTHER (teacher OR manual)
    |--------------------------------------------------------------------------
    */
    elseif ($applyFor === 'other') {

        $selectedTeacher = $_POST['teacher_select'] ?? '';

        if (!empty($selectedTeacher)) {
            // existing teacher
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$selectedTeacher]);
            $u = $stmt->fetch();

            if (!$u) {
                die('Selected teacher not found.');
            }

            $targetUserId = $u['id'];
            $name = $u['first_name'] . ' ' . $u['last_name'];
            $position = $u['position'];
            $station = $u['department'];

        } else {
            // manual input
            $name = trim($_POST['name'] ?? '');
            $position = trim($_POST['position'] ?? '');
            $station = trim($_POST['permanent_station'] ?? '');

            if ($name === '' || $position === '' || $station === '') {
                die('Please complete manual details.');
            }

            $targetUserId = null; // important
        }
    }

    else {
        die('Invalid apply option.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO locator_slips
        (user_id, name, position, permanent_station, purpose, date_time, destination, check_type)
        VALUES
        (:user_id, :name, :position, :station, :purpose, :date_time, :destination, :check_type)
    ");

    $stmt->execute([
        ':user_id' => $targetUserId,
        ':name' => $name,
        ':position' => $position,
        ':station' => $station,
        ':purpose' => $purpose,
        ':date_time' => $dateTime,
        ':destination' => $destination,
        ':check_type' => $checkType,
    ]);

    header('Location: ../admin/locator_requests.php?success=1');
    exit;
}


/*
|--------------------------------------------------------------------------
| APPROVE
|--------------------------------------------------------------------------
*/
if ($action === 'approve' && isAdmin()) {

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE locator_slips
        SET status = 'approved',
            approved_by = :approved_by,
            approved_at = NOW(),
            rejected_at = NULL,
            admin_remarks = :remarks
        WHERE id = :id
    ");

    $stmt->execute([
        ':approved_by' => $userId,
        ':remarks' => trim($_POST['admin_remarks'] ?? ''),
        ':id' => $id,
    ]);

    header('Location: ../admin/locator_requests.php?approved=1');
    exit;
}


/*
|--------------------------------------------------------------------------
| REJECT
|--------------------------------------------------------------------------
*/
if ($action === 'reject' && isAdmin()) {

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE locator_slips
        SET status = 'rejected',
            approved_by = NULL,
            approved_at = NULL,
            rejected_at = NOW(),
            admin_remarks = :remarks
        WHERE id = :id
    ");

    $stmt->execute([
        ':remarks' => trim($_POST['admin_remarks'] ?? ''),
        ':id' => $id,
    ]);

    header('Location: ../admin/locator_requests.php?rejected=1');
    exit;
}

die('Invalid request.');