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
    <title>Sign In | Teacher Leave Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --bg-color: #f8fafc;
            --accent-primary: #0f766e;
            --accent-primary-hover: #115e59;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --input-bg: #ffffff;
            --input-border: #cbd5e1;
            --input-focus-border: #0f766e;
            --error-color: #991b1b;
            --error-bg: #fef2f2;
            --error-border: #fca5a5;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: var(--shadow-card);
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-placeholder {
            width: 52px;
            height: 52px;
            background: rgba(15, 118, 110, 0.1);
            border-radius: 12px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-primary);
        }

        .logo-placeholder svg {
            width: 28px;
            height: 28px;
        }

        h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-family: var(--font-main);
            font-size: 14px;
            color: var(--text-primary);
            outline: none;
            transition: all 0.15s ease;
        }

        input::placeholder {
            color: #94a3b8;
        }

        input:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
        }

        input:focus + .input-icon {
            color: var(--accent-primary);
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.15s ease;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--accent-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: var(--font-main);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        button:hover {
            background: var(--accent-primary-hover);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        button:disabled .spinner {
            display: block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.4;
            display: none;
            align-items: flex-start;
            gap: 8px;
        }

        .alert.show {
            display: flex;
        }

        .error {
            background: var(--error-bg);
            color: var(--error-color);
            border: 1px solid var(--error-border);
        }

        .alert svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="header">
                <div class="logo-placeholder">
                    <!-- Calendar-leave icon -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <path d="M8 14h.01"></path>
                        <path d="M12 14h.01"></path>
                        <path d="M16 14h.01"></path>
                        <path d="M8 18h.01"></path>
                        <path d="M12 18h.01"></path>
                        <path d="M16 18h.01"></path>
                    </svg>
                </div>
                <h2>Teacher Leave System</h2>
                <div class="subtitle">Sign in to manage leave applications</div>
            </div>

            <div class="alert error" id="login_error" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span id="error_text"></span>
            </div>

            <form id="login_form" action="javascript:void(0)">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" id="username" placeholder="Enter your username" required autocomplete="username">
                        <div class="input-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required autocomplete="current-password">
                        <div class="input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <button type="submit" id="login_btn">
                    <div class="spinner"></div>
                    <span>Sign In</span>
                </button>
            </form>
        </div>
        <div class="footer-text">
            &copy; 2026 Teacher Leave System. All rights reserved.
        </div>
    </div>

    <script src="assets/js/api_client.js"></script>
    <script>
        (function () {
            var form = document.getElementById('login_form');
            var err = document.getElementById('login_error');
            var errText = document.getElementById('error_text');
            var btn = document.getElementById('login_btn');
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                err.classList.remove('show');
                errText.textContent = '';
                btn.disabled = true;
                var username = document.getElementById('username').value.trim();
                var password = document.getElementById('password').value;
                var res = await LSApi.post('auth/login.php', { username: username, password: password });
                if (res.data && res.data.success && res.data.redirect) {
                    window.location.href = res.data.redirect;
                    return;
                }
                errText.textContent = (res.data && res.data.message) ? res.data.message : 'Login failed.';
                err.classList.add('show');
                btn.disabled = false;
            });
        })();
    </script>
</body>
</html>
