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

$repo = 'Madness-Republic/madness-gdpr-consent-system';
$local_version_file = __DIR__ . '/../VERSION';
$local_version = trim(@file_get_contents($local_version_file) ?: '1.0.0');

// Use GitHub API to get the latest release or just the VERSION file from master
$github_version_url = "https://raw.githubusercontent.com/{$repo}/master/VERSION";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $github_version_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Madness-GDPR-Updater');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$remote_version = trim(curl_exec($ch));
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($remote_version)) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not fetch remote version',
        'local_version' => $local_version
    ]);
    exit;
}

$update_available = version_compare($remote_version, $local_version, '>');

echo json_encode([
    'success' => true,
    'update_available' => $update_available,
    'local_version' => $local_version,
    'remote_version' => $remote_version
]);
