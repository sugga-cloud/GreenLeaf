<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
if (!Auth::is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$zip_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'backup_' . date('Y-m-d_His') . '.zip';
$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to create backup']);
    exit;
}

$base = dirname(__DIR__);
$files_to_backup = [
    'sqlite/database.sqlite',
    '.env',
    'models/schema.php',
];
foreach ($files_to_backup as $f) {
    $full = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $f);
    if (file_exists($full)) {
        $zip->addFile($full, basename($f));
    }
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
header('Content-Length: ' . filesize($zip_path));
readfile($zip_path);
unlink($zip_path);
exit;
