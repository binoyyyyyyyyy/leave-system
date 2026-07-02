<?php
require 'includes/auth.php';
if (isset($_SESSION['user_id'])) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: teacher/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; padding: 28px 32px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); width: 100%; max-width: 380px; box-sizing: border-box; }
        h2 { margin: 0 0 20px; }
        input { width: 100%; padding: 10px 12px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #0069d9; }
        button:disabled { opacity: 0.65; cursor: not-allowed; }
        .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 14px; font-size: 14px; display: none; }
        .alert.show { display: block; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body data-api-base="api/">
    <div class="card">
        <h2>Login</h2>
        <div class="alert error" id="login_error" role="alert"></div>
        <form id="login_form" action="javascript:void(0)">
            <input type="text" name="username" id="username" placeholder="Username" required autocomplete="username">
            <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password">
            <button type="submit" id="login_btn">Login</button>
        </form>
    </div>
    <script src="assets/js/api_client.js"></script>
    <script>
        (function () {
            var form = document.getElementById('login_form');
            var err = document.getElementById('login_error');
            var btn = document.getElementById('login_btn');
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                err.classList.remove('show');
                err.textContent = '';
                btn.disabled = true;
                var username = document.getElementById('username').value.trim();
                var password = document.getElementById('password').value;
                var res = await LSApi.post('auth/login.php', { username: username, password: password });
                if (res.data && res.data.success && res.data.redirect) {
                    window.location.href = res.data.redirect;
                    return;
                }
                err.textContent = (res.data && res.data.message) ? res.data.message : 'Login failed.';
                err.classList.add('show');
                btn.disabled = false;
            });
        })();
    </script>
</body>
</html>
