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
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .app-nav-inner {
        width: 95%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        padding: 14px 0;
    }
    .app-nav-brand {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #0f766e;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .app-nav-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .app-nav-link {
        text-decoration: none;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 13px;
        font-weight: 500;
        padding: 6px 14px;
        border-radius: 6px;
        color: #475569;
        background: transparent;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
    .app-nav-link:hover {
        color: #0f172a;
        background: #f1f5f9;
        border-color: #e2e8f0;
    }
    .app-nav-link.active {
        color: #ffffff;
        background: #0f766e;
        border-color: #0f766e;
        font-weight: 600;
    }
    .app-nav-logout {
        text-decoration: none;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 6px;
        color: #b91c1c;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        transition: all 0.15s ease;
    }
    .app-nav-logout:hover {
        color: #ffffff;
        background: #dc2626;
        border-color: #dc2626;
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