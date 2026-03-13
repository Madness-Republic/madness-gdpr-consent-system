<?php
session_start();
set_time_limit(300); // 5 minutes max
include_once __DIR__ . '/../config.php';

// Auth Check
if (!isset($_SESSION['gdpr_admin_logged_in']) || $_SESSION['gdpr_admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if (!class_exists('ZipArchive')) {
    echo json_encode(['success' => false, 'error' => 'ZipArchive extension not enabled on server.']);
    exit;
}

$repo = 'Madness-Republic/madness-gdpr-consent-system';
$zip_url = "https://github.com/{$repo}/archive/refs/heads/master.zip";
$temp_zip = sys_get_temp_dir() . '/gdpr_update.zip';
$extract_to = sys_get_temp_dir() . '/gdpr_update_extract_' . time();

// 1. Download ZIP
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $zip_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Madness-GDPR-Updater');
$zip_data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($zip_data)) {
    echo json_encode(['success' => false, 'error' => 'Failed to download update package.']);
    exit;
}

if (file_put_contents($temp_zip, $zip_data) === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to save temporary update package.']);
    exit;
}

// 2. Extract ZIP
$zip = new ZipArchive;
if ($zip->open($temp_zip) === TRUE) {
    if (!is_dir($extract_to)) mkdir($extract_to, 0755, true);
    $zip->extractTo($extract_to);
    $zip->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to open update ZIP.']);
    exit;
}

// 3. Identify the folder inside the ZIP (e.g. madness-gdpr-consent-system-master)
$files = scandir($extract_to);
$inner_folder = '';
foreach ($files as $f) {
    if ($f !== '.' && $f !== '..' && is_dir($extract_to . '/' . $f)) {
        $inner_folder = $extract_to . '/' . $f;
        break;
    }
}

if (empty($inner_folder)) {
    echo json_encode(['success' => false, 'error' => 'Invalid update package structure.']);
    exit;
}

// 4. Update Files recursively
$source = $inner_folder;
$destination = realpath(__DIR__ . '/../');

function smart_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $src_file = $src . '/' . $file;
            $dst_file = $dst . '/' . $file;

            // EXCLUSIONS
            if ($file === 'config.php') continue;
            if ($file === 'logs' && is_dir($src_file)) continue;
            if ($file === 'content' && is_dir($src_file)) continue;
            if ($file === '.git') continue;

            if ( is_dir($src_file) ) {
                smart_copy($src_file, $dst_file);
            } else {
                copy($src_file, $dst_file);
            }
        }
    }
    closedir($dir);
}

try {
    smart_copy($source, $destination);
    // Cleanup
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }
    rrmdir($extract_to);
    unlink($temp_zip);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'File replacement failed: ' . $e->getMessage()]);
}
