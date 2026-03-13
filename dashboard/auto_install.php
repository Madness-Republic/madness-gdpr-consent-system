<?php
session_start();
include_once __DIR__ . '/../config.php';

// Auth Check
if (!isset($_SESSION['gdpr_admin_logged_in']) || $_SESSION['gdpr_admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$target_file = $_POST['target_file'] ?? '';

// Root of the website (assuming gdpr is in root/gdpr)
$base_dir = realpath(__DIR__ . '/../../');

// Allowed files to scan/modify
// Allowed files to scan/modify
$allowed_files = [
    'footer.php',
    'index.php',
    'header.php',
    'includes/footer.php',
    'includes/header.php',
    'templates/footer.php',
    'templates/header.php',
    'layout/footer.php',
    'layouts/footer.php',
    'partials/footer.php',
    'common/footer.php'
];

function get_target_path($file)
{
    global $base_dir;
    // Simple verification
    $path = realpath($base_dir . '/' . $file);
    if ($path && strpos($path, $base_dir) === 0) {
        return $path;
    }
    return false;
}

function get_relative_gdpr_path($target_abs_path)
{
    global $base_dir;

    // Relative directory of target file from root
    $rel_dir = trim(substr(dirname($target_abs_path), strlen($base_dir)), '/');

    // Calculate depth
    $depth = $rel_dir === '' ? 0 : count(explode('/', $rel_dir));

    // Generate prefix (e.g. "../")
    $prefix = $depth == 0 ? '' : str_repeat('../', $depth);

    return $prefix . 'gdpr/banner.php';
}

// ACTION: SCAN
if ($action === 'scan') {
    $results = [];
    $custom_file = trim($_POST['custom_file'] ?? '');

    // If custom file provided, add it to scan list temporarily
    if ($custom_file && pathinfo($custom_file, PATHINFO_EXTENSION) === 'php') {
        // Prevent directory traversal characters in simple check, though realpath handles it
        // and add to the BEGINNING of the list so it appears first
        if (strpos($custom_file, '..') === false) {
            array_unshift($allowed_files, $custom_file);
        }
    }

    foreach ($allowed_files as $f) {
        $path = $base_dir . '/' . $f;

        if (file_exists($path)) {
            $content = file_get_contents($path);
            $installed = strpos($content, 'gdpr/banner.php') !== false;
            $writable = is_writable($path);

            $results[] = [
                'file' => $f,
                'exists' => true,
                'installed' => $installed,
                'writable' => $writable,
                'backup' => file_exists($path . '.bak')
            ];
        }
    }
    echo json_encode(['files' => $results]);
    exit;
}

// ACTION: INSTALL
if ($action === 'install') {
    $path = get_target_path($target_file);
    if (!$path || !is_writable($path)) {
        echo json_encode(['error' => 'File not writable or invalid.']);
        exit;
    }

    $content = file_get_contents($path);
    if (strpos($content, 'gdpr/banner.php') !== false) {
        echo json_encode(['error' => 'Already installed.']);
        exit;
    }

    // Create Backup
    if (!copy($path, $path . '.bak')) {
        echo json_encode(['error' => 'Backup creation failed. Aborting.']);
        exit;
    }

    // Determine injection code
    $rel_path = get_relative_gdpr_path($path);
    $code = "\n<?php include_once __DIR__ . '/{$rel_path}'; ?>\n";

    $injected = false;

    // Strategy 1: Before </body>
    if (strpos($content, '</body>') !== false) {
        $content = str_replace('</body>', $code . '</body>', $content);
        $injected = true;
    } else {
        // Fallback: Append
        $content .= $code;
        $injected = true;
    }

    if ($injected) {
        file_put_contents($path, $content);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Injection failed.']);
    }
    exit;
}

// ACTION: RESTORE
if ($action === 'restore') {
    $path = get_target_path($target_file);
    $bak_path = $path . '.bak';

    if ($path && file_exists($bak_path) && is_writable($path)) {
        if (copy($bak_path, $path)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Restore failed.']);
        }
    } else {
        echo json_encode(['error' => 'Backup not found or file not writable.']);
    }
    exit;
}
?>