<?php
function render_app_nav(string $role, string $current = ''): void
{
    $role = ($role === 'admin') ? 'admin' : 'teacher';
    $current = trim($current);

    $items = $role === 'admin'
        ? [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['id' => 'apply', 'label' => 'Apply Leave', 'href' => 'apply_leave.php'],

            // ✅ ADDED: Admin can now apply locator slip
            ['id' => 'apply_locator', 'label' => 'Apply Locator Slip', 'href' => 'apply_locator.php'],

            ['id' => 'users', 'label' => 'Manage Users', 'href' => 'manage_users.php'],
            ['id' => 'requests', 'label' => 'Leave Requests', 'href' => 'leave_requests.php'],
            ['id' => 'locator_requests', 'label' => 'Locator Requests', 'href' => 'locator_requests.php'],
            ['id' => 'credits', 'label' => 'Credit Leaves', 'href' => 'credit_leaves.php'],
        ]
        : [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['id' => 'apply', 'label' => 'Apply Leave', 'href' => 'apply_leave.php'],
            ['id' => 'my_leaves', 'label' => 'My Leaves', 'href' => 'my_leaves.php'],
            ['id' => 'apply_locator', 'label' => 'Apply Locator Slip', 'href' => 'apply_locator.php'],
            ['id' => 'my_locator', 'label' => 'My Locator Slips', 'href' => 'my_locator.php'],
        ];
?>
<style>
    .app-nav {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: #ffffff;
        border-bottom: 1px solid #e8e8e8;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .app-nav-inner {
        width: 95%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 10px 0;
    }
    .app-nav-brand {
        font-family: Arial, sans-serif;
        font-size: 15px;
        font-weight: bold;
        color: #1f2d3d;
        text-decoration: none;
    }
    .app-nav-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .app-nav-link {
        text-decoration: none;
        font-family: Arial, sans-serif;
        font-size: 13px;
        padding: 7px 11px;
        border-radius: 999px;
        color: #34495e;
        background: #f6f8fb;
        border: 1px solid #e3e8ef;
    }
    .app-nav-link.active {
        color: #fff;
        background: #007bff;
        border-color: #007bff;
    }
    .app-nav-logout {
        text-decoration: none;
        font-family: Arial, sans-serif;
        font-size: 13px;
        padding: 7px 11px;
        border-radius: 999px;
        color: #fff;
        background: #d9534f;
        border: 1px solid #d9534f;
    }
</style>

<div class="app-nav">
    <div class="app-nav-inner">
        <a class="app-nav-brand" href="dashboard.php">Leave System</a>

        <div class="app-nav-links">
            <?php foreach ($items as $item): ?>
                <a class="app-nav-link <?= $current === $item['id'] ? 'active' : ''; ?>"
                   href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>

            <a class="app-nav-logout" href="../logout.php">Logout</a>
        </div>
    </div>
</div>

<?php
}