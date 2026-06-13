<?php

/**
 * @return list<array<string,mixed>>
 */
function admin_list_users_safe(PDO $pdo): array
{
    $stmt = $pdo->query('
        SELECT
            id,
            employee_no,
            first_name,
            middle_name,
            last_name,
            email,
            username,
            role,
            department,
            position,
            salary,
            status,
            created_at
        FROM users
        ORDER BY created_at DESC, id DESC
    ');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool, message?:string}
 */
function admin_create_user(PDO $pdo, array $input): array
{
    $username = trim((string)($input['username'] ?? ''));
    $password = (string)($input['password'] ?? '');
    $firstName = trim((string)($input['first_name'] ?? ''));
    $middleName = trim((string)($input['middle_name'] ?? ''));
    $lastName = trim((string)($input['last_name'] ?? ''));
    $employeeNo = trim((string)($input['employee_no'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $role = $input['role'] ?? 'teacher';
    $department = trim((string)($input['department'] ?? ''));
    $position = trim((string)($input['position'] ?? ''));
    $salaryRaw = trim((string)($input['salary'] ?? ''));
    $status = $input['status'] ?? 'active';

    if (!in_array($role, ['teacher', 'admin'], true)) {
        return ['ok' => false, 'message' => 'Invalid role.'];
    }
    if ($username === '' || strlen($username) < 3) {
        return ['ok' => false, 'message' => 'Username must be at least 3 characters.'];
    }
    if (strlen($password) < 6) {
        return ['ok' => false, 'message' => 'Password must be at least 6 characters.'];
    }
    if ($firstName === '' || $lastName === '') {
        return ['ok' => false, 'message' => 'First and last name are required.'];
    }
    if ($role === 'teacher' && $employeeNo === '') {
        return ['ok' => false, 'message' => 'Employee number is required for teachers.'];
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Invalid email address.'];
    }
    if (!in_array($status, ['active', 'inactive'], true)) {
        return ['ok' => false, 'message' => 'Invalid status selected.'];
    }
    if ($role === 'admin' && $status !== 'active') {
        return ['ok' => false, 'message' => 'Admin accounts must remain active.'];
    }
    if ($salaryRaw !== '' && !is_numeric($salaryRaw)) {
        return ['ok' => false, 'message' => 'Salary must be a valid number.'];
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $check->execute([$username]);
    if ($check->fetch()) {
        return ['ok' => false, 'message' => 'That username is already taken.'];
    }
    if ($email !== '') {
        $checkEmail = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $checkEmail->execute([$email]);
        if ($checkEmail->fetch()) {
            return ['ok' => false, 'message' => 'That email is already taken.'];
        }
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('
        INSERT INTO users (
            username, password_hash, first_name, middle_name, last_name,
            employee_no, email, role, department, position, salary, status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $username,
        $hash,
        $firstName,
        $middleName !== '' ? $middleName : null,
        $lastName,
        $employeeNo !== '' ? $employeeNo : null,
        $email !== '' ? $email : null,
        $role,
        $department !== '' ? $department : null,
        $position !== '' ? $position : null,
        $salaryRaw !== '' ? (float)$salaryRaw : null,
        $status
    ]);

    return ['ok' => true, 'message' => 'User created.'];
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool, message?:string}
 */
function admin_update_user(PDO $pdo, int $actingAdminId, array $input): array
{
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Invalid user.'];
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        return ['ok' => false, 'message' => 'User not found.'];
    }

    $username = trim((string)($input['username'] ?? $existing['username']));
    $firstName = trim((string)($input['first_name'] ?? ''));
    $middleName = trim((string)($input['middle_name'] ?? ''));
    $lastName = trim((string)($input['last_name'] ?? ''));
    $employeeNo = trim((string)($input['employee_no'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $role = $input['role'] ?? $existing['role'];
    $department = trim((string)($input['department'] ?? ''));
    $position = trim((string)($input['position'] ?? ''));
    $salaryRaw = trim((string)($input['salary'] ?? ''));
    $status = $input['status'] ?? $existing['status'];
    $password = (string)($input['password'] ?? '');

    if (!in_array($role, ['teacher', 'admin'], true)) {
        return ['ok' => false, 'message' => 'Invalid role.'];
    }
    if ($username === '' || strlen($username) < 3) {
        return ['ok' => false, 'message' => 'Username must be at least 3 characters.'];
    }
    if ($firstName === '' || $lastName === '') {
        return ['ok' => false, 'message' => 'First and last name are required.'];
    }
    if ($role === 'teacher' && $employeeNo === '') {
        return ['ok' => false, 'message' => 'Employee number is required for teachers.'];
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Invalid email address.'];
    }
    if (!in_array($status, ['active', 'inactive'], true)) {
        return ['ok' => false, 'message' => 'Invalid status selected.'];
    }
    if ($role === 'admin' && $status !== 'active') {
        return ['ok' => false, 'message' => 'Admin accounts must remain active.'];
    }
    if ($salaryRaw !== '' && !is_numeric($salaryRaw)) {
        return ['ok' => false, 'message' => 'Salary must be a valid number.'];
    }

    $dup = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
    $dup->execute([$username, $id]);
    if ($dup->fetch()) {
        return ['ok' => false, 'message' => 'That username is already taken.'];
    }
    if ($email !== '') {
        $dupEmail = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $dupEmail->execute([$email, $id]);
        if ($dupEmail->fetch()) {
            return ['ok' => false, 'message' => 'That email is already taken.'];
        }
    }

    if ($existing['role'] === 'admin' && $role !== 'admin') {
        $cnt = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($cnt <= 1) {
            return ['ok' => false, 'message' => 'Cannot change the only remaining admin to a different role.'];
        }
    }

    if ($password !== '' && strlen($password) < 6) {
        return ['ok' => false, 'message' => 'Password must be at least 6 characters if provided.'];
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('
            UPDATE users SET
                username = ?, password_hash = ?, first_name = ?, middle_name = ?, last_name = ?,
                employee_no = ?, email = ?, role = ?, department = ?, position = ?, salary = ?, status = ?
            WHERE id = ?
        ');
        $upd->execute([
            $username, $hash, $firstName, $middleName !== '' ? $middleName : null, $lastName,
            $employeeNo !== '' ? $employeeNo : null, $email !== '' ? $email : null, $role,
            $department !== '' ? $department : null, $position !== '' ? $position : null,
            $salaryRaw !== '' ? (float)$salaryRaw : null, $status, $id
        ]);
    } else {
        $upd = $pdo->prepare('
            UPDATE users SET
                username = ?, first_name = ?, middle_name = ?, last_name = ?,
                employee_no = ?, email = ?, role = ?, department = ?, position = ?, salary = ?, status = ?
            WHERE id = ?
        ');
        $upd->execute([
            $username, $firstName, $middleName !== '' ? $middleName : null, $lastName,
            $employeeNo !== '' ? $employeeNo : null, $email !== '' ? $email : null, $role,
            $department !== '' ? $department : null, $position !== '' ? $position : null,
            $salaryRaw !== '' ? (float)$salaryRaw : null, $status, $id
        ]);
    }

    return ['ok' => true, 'message' => 'User updated.'];
}

/**
 * @return array{ok:bool, message?:string}
 */
function admin_delete_user(PDO $pdo, int $actingAdminId, int $targetId): array
{
    if ($targetId === $actingAdminId) {
        return ['ok' => false, 'message' => 'You cannot delete your own account.'];
    }

    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute([$targetId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return ['ok' => false, 'message' => 'User not found.'];
    }

    if ($user['role'] === 'admin') {
        $cnt = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($cnt <= 1) {
            return ['ok' => false, 'message' => 'Cannot delete the only admin account.'];
        }
    }

    if ($user['role'] === 'teacher') {
        $la = $pdo->prepare('SELECT COUNT(*) FROM leave_applications WHERE teacher_id = ?');
        $la->execute([$targetId]);
        if ((int)$la->fetchColumn() > 0) {
            return ['ok' => false, 'message' => 'Cannot delete a teacher who has leave applications.'];
        }
        $lc = $pdo->prepare('SELECT COUNT(*) FROM leave_credits WHERE teacher_id = ?');
        $lc->execute([$targetId]);
        if ((int)$lc->fetchColumn() > 0) {
            return ['ok' => false, 'message' => 'Cannot delete a teacher who has leave credit records.'];
        }
    }

    $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$targetId]);

    return ['ok' => true, 'message' => 'User deleted.'];
}
