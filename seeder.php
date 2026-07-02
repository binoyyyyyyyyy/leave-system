<?php
/**
 * Admin Seeder
 * 
 * Seeds a default admin account into the users table.
 * Safe to run multiple times — skips if admin already exists.
 * 
 * Usage: php seeder.php  (CLI)
 *    or: http://localhost/leave-system/seeder.php  (Browser)
 */

require_once __DIR__ . '/includes/db.php';

// ── Admin credentials ──────────────────────────────────
$admin = [
    'employee_no' => null,
    'first_name' => 'Admin',
    'middle_name' => null,
    'last_name' => 'User',
    'email' => null,
    'username' => 'admin',
    'password' => 'admin@123',          // plain-text → hashed below
    'role' => 'admin',
    'department' => 'Administration',
    'position' => 'Admin',
    'salary' => null,
    'status' => 'active',
];
// ────────────────────────────────────────────────────────

try {
    // Check if admin already exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM `users` WHERE `username` = :username');
    $stmt->execute(['username' => $admin['username']]);

    if ((int) $stmt->fetchColumn() > 0) {
        echo "⚠  Admin user '{$admin['username']}' already exists. Skipping.\n";
        exit(0);
    }

    // Hash the password
    $hashedPassword = password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 12]);

    // Insert admin
    $sql = "INSERT INTO `users` 
            (`employee_no`, `first_name`, `middle_name`, `last_name`, `email`, 
             `username`, `password_hash`, `role`, `department`, `position`, `salary`, `status`)
            VALUES 
            (:employee_no, :first_name, :middle_name, :last_name, :email, 
             :username, :password_hash, :role, :department, :position, :salary, :status)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'employee_no' => $admin['employee_no'],
        'first_name' => $admin['first_name'],
        'middle_name' => $admin['middle_name'],
        'last_name' => $admin['last_name'],
        'email' => $admin['email'],
        'username' => $admin['username'],
        'password_hash' => $hashedPassword,
        'role' => $admin['role'],
        'department' => $admin['department'],
        'position' => $admin['position'],
        'salary' => $admin['salary'],
        'status' => $admin['status'],
    ]);

    echo "✅ Admin user '{$admin['username']}' seeded successfully.\n";
    echo "   Username: {$admin['username']}\n";
    echo "   Password: {$admin['password']}\n";

} catch (PDOException $e) {
    echo "❌ Seeder failed: " . $e->getMessage() . "\n";
    exit(1);
}
