<?php
// Backup endpoint - manual (admin login required) or automated (token from .env)
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$env_file = __DIR__ . '/../.env';
$stored_token = '';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with($line, 'BACKUP_TOKEN=')) {
            $stored_token = substr($line, strlen('BACKUP_TOKEN='));
            break;
        }
    }
}

if ($token !== $stored_token) {
    require_once __DIR__ . '/../services/Auth.php';
    Auth::start_session();
    if (!Auth::is_admin()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
}

$db_path = __DIR__ . '/../sqlite/database.sqlite';
$backup_dir = __DIR__ . '/../backups';

if (!is_dir($backup_dir)) {
    @mkdir($backup_dir, 0777, true);
}

if (!file_exists($db_path)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database not found']);
    exit;
}

$ts = date('Y-m-d_H-i-s');
$backup_path = $backup_dir . DIRECTORY_SEPARATOR . "db_{$ts}.sqlite";

if (copy($db_path, $backup_path)) {
    $files = glob($backup_dir . DIRECTORY_SEPARATOR . 'db_*.sqlite');
    if (count($files) > 12) {
        usort($files, function($a, $b) { return filemtime($a) - filemtime($b); });
        $to_delete = array_slice($files, 0, count($files) - 12);
        foreach ($to_delete as $f) { @unlink($f); }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => "Backup created: db_{$ts}.sqlite"]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to copy database']);
}
