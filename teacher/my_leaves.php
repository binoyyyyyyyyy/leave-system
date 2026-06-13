<?php
require '../includes/auth.php';
require_once '../includes/app_nav.php';
checkLogin();
if (!isTeacher()) {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Applications</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { width: 90%; max-width: 1100px; margin: 30px auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 25px; }
        .topbar h1 { margin: 0; font-size: 1.5rem; }
        .top-links { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .top-links a {
            text-decoration: none; padding: 10px 14px; border-radius: 6px; color: #fff; font-size: 14px;
        }
        .back-btn { background: #007bff; }
        .apply-btn { background: #28a745; }
        .logout-btn { background: #d9534f; }
        .card {
            background: #fff; padding: 25px; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .muted { color: #666; font-size: 14px; }
        .live-hint { font-size: 12px; color: #888; margin: 0 0 16px; display: none; }
        .load-err { color: #721c24; margin-bottom: 16px; display: none; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top; }
        th { background: #fafafa; font-weight: bold; color: #333; }
        .badge {
            display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;
            text-transform: capitalize;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        .details { font-size: 13px; color: #555; line-height: 1.45; max-width: 280px; }
        .details div { margin-top: 4px; }
        .empty { padding: 24px; text-align: center; color: #666; }
        @media (max-width: 900px) {
            .scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table { min-width: 640px; }
        }
    </style>
</head>
<body data-api-base="../api/">
    <?php render_app_nav('teacher', 'my_leaves'); ?>
    <div class="container">
        <div class="topbar">
            <h1>My Leave Applications</h1>
        </div>

        <div class="card">
            <p class="live-hint" id="live_hint">List refreshes while this tab is open so status updates when admin acts.</p>
            <p class="load-err" id="load_err"></p>
            <div class="scroll" id="table_wrap">Loading…</div>
        </div>
    </div>
    <script src="../assets/js/api_client.js"></script>
    <script src="../assets/js/live_poll.js"></script>
    <script>
        (function () {
            var wrap = document.getElementById('table_wrap');
            var loadErr = document.getElementById('load_err');
            var liveHint = document.getElementById('live_hint');

            function esc(s) {
                if (s == null || s === '') return '';
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function badgeClass(status) {
                if (status === 'approved') return 'badge-approved';
                if (status === 'rejected') return 'badge-rejected';
                return 'badge-pending';
            }

            function formatDate(ymd) {
                if (!ymd) return '—';
                var t = new Date(ymd + 'T00:00:00');
                if (isNaN(t.getTime())) return esc(ymd);
                return esc(t.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
            }

            function detailLines(row) {
                var parts = [];
                if (row.vacation_detail) {
                    if (row.vacation_detail === 'abroad' && row.abroad_specify) {
                        parts.push('Abroad: ' + esc(row.abroad_specify));
                    } else if (row.vacation_detail === 'within_philippines') {
                        parts.push('Within the Philippines');
                    }
                }
                if (row.sick_detail) {
                    var line = row.sick_detail.replace(/_/g, ' ');
                    if (row.illness_details) line += ' — ' + row.illness_details;
                    parts.push('Sick: ' + esc(line));
                }
                if (row.special_leave_women_details) {
                    parts.push('Details: ' + esc(row.special_leave_women_details));
                }
                if (row.study_leave_detail) {
                    parts.push('Study: ' + esc(row.study_leave_detail.replace(/_/g, ' ')));
                }
                var comm = row.commutation === 'requested' ? 'Requested' : 'Not requested';
                parts.push('Commutation: ' + comm);
                if (!parts.length) return '<span class="muted">—</span>';
                return '<div class="details">' + parts.map(function (p) { return '<div>' + p + '</div>'; }).join('') + '</div>';
            }

            function adminNote(row) {
                var bits = [];
                if (row.admin_remarks) bits.push('<strong>Admin remarks:</strong> ' + esc(row.admin_remarks));
                if (row.rejected_reason && row.rejected_reason !== row.admin_remarks) {
                    bits.push('<strong>Reason:</strong> ' + esc(row.rejected_reason));
                }
                if (!bits.length) return '<span class="muted">—</span>';
                return bits.join('<br>');
            }

            function renderRows(applications) {
                if (!applications || applications.length === 0) {
                    wrap.innerHTML = '<div class="empty">You have not submitted any leave applications yet. <a href="apply_leave.php">Apply for leave</a>.</div>';
                    return;
                }
                var html = '<table><thead><tr>' +
                    '<th>Filed</th><th>Leave type</th><th>Dates</th><th>Days</th><th>Status</th><th>Details</th><th>Admin</th>' +
                    '</tr></thead><tbody>';
                applications.forEach(function (row) {
                    var half = parseInt(row.is_half_day, 10) === 1 ? ' <span class="muted">(half-day)</span>' : '';
                    var days = esc(String(row.working_days_applied)) + half;
                    var status = esc(row.status || '');
                    html += '<tr>' +
                        '<td>' + formatDate(row.date_filed) + '</td>' +
                        '<td><b>' + esc(row.leave_name) + '</b></td>' +
                        '<td>' + formatDate(row.date_from) + ' – ' + formatDate(row.date_to) + '</td>' +
                        '<td>' + days + '</td>' +
                        '<td><span class="badge ' + badgeClass(row.status) + '">' + status + '</span></td>' +
                        '<td>' + detailLines(row) + '</td>' +
                        '<td>' + adminNote(row) + '</td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                wrap.innerHTML = html;
            }

            async function load() {
                loadErr.style.display = 'none';
                var res = await LSApi.get('teacher/my_leaves.php');
                if (!res.ok || !res.data.success) {
                    loadErr.style.display = 'block';
                    loadErr.textContent = (res.data && res.data.message) ? res.data.message : 'Could not load your applications.';
                    wrap.innerHTML = '';
                    return;
                }
                renderRows(res.data.applications || []);
                liveHint.style.display = 'block';
                if (window.LSLive && !load._pollStarted) {
                    load._pollStarted = true;
                    LSLive.pollGet('teacher/my_leaves.php', 12000, function (data) {
                        renderRows(data.applications || []);
                    });
                }
            }

            load();
        })();
    </script>
</body>
</html>
