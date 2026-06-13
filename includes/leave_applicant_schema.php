<?php

declare(strict_types=1);

function ensure_leave_applicant_columns(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $existing = [];
    $stmt = $pdo->query('SHOW COLUMNS FROM leave_applications');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $existing[strtolower((string)$row['Field'])] = true;
    }

    $wanted = [
        'applicant_first_name' => "ALTER TABLE leave_applications ADD COLUMN applicant_first_name VARCHAR(100) NULL AFTER teacher_id",
        'applicant_middle_name' => "ALTER TABLE leave_applications ADD COLUMN applicant_middle_name VARCHAR(100) NULL AFTER applicant_first_name",
        'applicant_last_name' => "ALTER TABLE leave_applications ADD COLUMN applicant_last_name VARCHAR(100) NULL AFTER applicant_middle_name",
        'applicant_email' => "ALTER TABLE leave_applications ADD COLUMN applicant_email VARCHAR(191) NULL AFTER applicant_last_name",
        'applicant_employee_no' => "ALTER TABLE leave_applications ADD COLUMN applicant_employee_no VARCHAR(100) NULL AFTER applicant_email",
        'applicant_department' => "ALTER TABLE leave_applications ADD COLUMN applicant_department VARCHAR(150) NULL AFTER applicant_employee_no",
        'applicant_position' => "ALTER TABLE leave_applications ADD COLUMN applicant_position VARCHAR(150) NULL AFTER applicant_department",
        'applicant_salary' => "ALTER TABLE leave_applications ADD COLUMN applicant_salary DECIMAL(12,2) NULL AFTER applicant_position",
    ];

    foreach ($wanted as $col => $sql) {
        if (!isset($existing[$col])) {
            $pdo->exec($sql);
        }
    }

    $done = true;
}

