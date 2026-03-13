<?php
session_start();
/**
 * Madness GDPR - System Self-Test
 */

$ui_lang = $_GET['lang'] ?? 'it';
$lang_file = __DIR__ . "/../languages/$ui_lang.json";
if (!file_exists($lang_file))
    $ui_lang = 'it';
$lang_json = json_decode(file_get_contents(__DIR__ . "/../languages/$ui_lang.json"), true);
$t = $lang_json['admin'];

// Simple Auth check (mirrors dashboard/index.php)
if (!isset($_SESSION['gdpr_admin_logged_in']) || $_SESSION['gdpr_admin_logged_in'] !== true) {
    echo $t['unauthorized'] ?? "Unauthorized. Please log in to the dashboard first.";
    exit;
}

$root_dir = __DIR__ . '/../';
$fix_msg = "";
$fix_error = "";

// --- AUTO-FIX LOGIC ---
if (isset($_GET['fix'])) {
    if ($_GET['fix'] === 'htaccess') {
        $logs_dir = $root_dir . 'logs/';
        if (is_dir($logs_dir) && is_writable($logs_dir)) {
            $htaccess_content = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order Deny,Allow\n    Deny from all\n</IfModule>";
            if (file_put_contents($logs_dir . '.htaccess', $htaccess_content)) {
                $fix_msg = $t['sys_fix_success'];
            } else {
                $fix_error = $t['sys_fix_error'];
            }
        } else {
            $fix_error = $t['sys_fix_error'];
        }
    }
}

$results = [];

// 1. PHP Version
$results[] = [
    'id' => 'php',
    'name' => $t['sys_check_php'],
    'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail',
    'msg' => $t['sys_msg_current'] . ' ' . PHP_VERSION . ' (' . $t['sys_msg_req'] . ' 7.4.0+)',
    'fix' => $t['sys_fix_php']
];

// 2. Logs Directory
$logs_dir = $root_dir . 'logs/';
if (!is_dir($logs_dir)) {
    $results[] = [
        'id' => 'logs_missing',
        'name' => $t['sys_check_logs'],
        'status' => 'fail',
        'msg' => $t['sys_check_logs_missing'],
        'fix' => $t['sys_fix_logs_missing']
    ];
} else {
    $results[] = [
        'id' => 'logs_writable',
        'name' => $t['sys_check_logs_writable'],
        'status' => is_writable($logs_dir) ? 'pass' : 'fail',
        'msg' => is_writable($logs_dir) ? $t['sys_msg_ok'] : $t['sys_msg_perm_error'],
        'fix' => $t['sys_fix_logs_writable']
    ];

    // 3. .htaccess Protection
    $htaccess = $logs_dir . '.htaccess';
    $results[] = [
        'id' => 'htaccess',
        'name' => $t['sys_check_htaccess'],
        'status' => file_exists($htaccess) ? 'pass' : 'fail',
        'msg' => file_exists($htaccess) ? $t['sys_msg_ok'] : $t['sys_msg_protection_missing'],
        'fix' => $t['sys_fix_htaccess'],
        'allow_auto_fix' => true
    ];
}

// 4. Config File
$config_file = $root_dir . 'config.php';
if (!file_exists($config_file)) {
    $results[] = [
        'id' => 'config_missing',
        'name' => $t['sys_check_config'],
        'status' => 'fail',
        'msg' => $t['sys_msg_config_missing'],
        'fix' => $t['sys_fix_config_missing']
    ];
} else {
    $results[] = [
        'id' => 'config_writable',
        'name' => $t['sys_check_config_writable'],
        'status' => is_writable($config_file) ? 'pass' : 'fail',
        'msg' => is_writable($config_file) ? $t['sys_msg_ok'] : $t['sys_msg_perm_error'],
        'fix' => $t['sys_fix_config_writable']
    ];
}

// 5. Default Password Check
if (file_exists($config_file))
    include $config_file;
$is_default_pass = password_verify('password', $gdpr_admin_pass ?? '');
$results[] = [
    'id' => 'pass',
    'name' => $t['sys_check_admin_pass'],
    'status' => $is_default_pass ? 'warning' : 'pass',
    'msg' => $is_default_pass ? $t['sys_check_pass_default'] : $t['sys_check_pass_ok'],
    'fix' => $t['sys_fix_pass']
];

// 6. Content Files
$content_dir = $root_dir . 'content/';
$policy_files = glob($content_dir . 'policy_*.html');
$results[] = [
    'id' => 'policy',
    'name' => $t['sys_check_policy'],
    'status' => count($policy_files) > 0 ? 'pass' : 'fail',
    'msg' => count($policy_files) . ' ' . $t['sys_check_policy_msg'],
    'fix' => $t['sys_fix_policy']
];

?>
<!DOCTYPE html>
<html lang="<?php echo $ui_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['sys_check_title']; ?> - Madness GDPR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Oswald:wght@500;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --accent: #f09100;
            --text: #f8fafc;
            --text-dim: #94a3b8;
            --code-bg: #000;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        header {
            text-align: center;
            margin-bottom: 50px;
        }

        header h1 {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
            font-size: 2.5rem;
            color: var(--accent);
            margin: 0;
        }

        .card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 30px;
        }

        h2 {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 10px;
            margin-top: 0;
            font-size: 1.4rem;
        }

        .result-item {
            padding: 15px 0;
            border-bottom: 1px solid #334155;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .status {
            font-weight: bold;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .pass {
            background: #064e3b;
            color: #34d399;
        }

        .fail {
            background: #7f1d1d;
            color: #f87171;
        }

        .warning {
            background: #78350f;
            color: #fbbf24;
        }

        .fix-box {
            background: rgba(0, 0, 0, 0.2);
            border-left: 3px solid #f59e0b;
            padding: 10px 15px;
            margin-top: 10px;
            font-size: 0.85rem;
            color: #cbd5e1;
        }

        .btn-small {
            background: #f59e0b;
            color: #000;
            padding: 4px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.75rem;
            display: inline-block;
            margin-top: 10px;
        }

        .btn-small:hover {
            background: #d97706;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-btn:hover {
            color: white;
        }

        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .alert-success {
            background: #064e3b;
            color: #34d399;
        }

        .alert-error {
            background: #7f1d1d;
            color: #f87171;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php?lang=<?php echo $ui_lang; ?>" class="back-link">← <?php echo $t['sys_check_back']; ?></a>

        <header>
            <h1>🔍 <?php echo $t['sys_check_title']; ?></h1>
            <p style="color: #94a3b8; font-size: 0.9rem;"><?php echo $t['sys_check_subtitle']; ?></p>
        </header>



        <div class="card">
            <?php if ($fix_msg): ?>
                <div class="alert alert-success"><?php echo $fix_msg; ?></div>
            <?php endif; ?>
            <?php if ($fix_error): ?>
                <div class="alert alert-error"><?php echo $fix_error; ?></div>
            <?php endif; ?>

            <?php foreach ($results as $res): ?>
                <div class="result-item">
                    <div class="result-header">
                        <div>
                            <div style="font-weight: 600; font-size: 1rem;"><?php echo $res['name']; ?></div>
                            <div style="font-size: 0.85rem; color: #94a3b8;"><?php echo $res['msg']; ?></div>
                        </div>
                        <div>
                            <span class="status <?php echo $res['status']; ?>"><?php echo $res['status']; ?></span>
                        </div>
                    </div>

                    <?php if ($res['status'] !== 'pass'): ?>
                        <div class="fix-box">
                            <strong><?php echo $t['sys_fix_title']; ?></strong><br>
                            <?php echo $res['fix']; ?>
                            <br>
                            <?php if (isset($res['allow_auto_fix']) && $res['allow_auto_fix']): ?>
                                <a href="?fix=<?php echo $res['id']; ?>&lang=<?php echo $ui_lang; ?>"
                                    class="btn-small"><?php echo $t['sys_fix_btn']; ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div> <!-- Close .card -->
    </div> <!-- Close .container -->
</body>

</html>