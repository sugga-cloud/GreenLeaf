<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
if (!Auth::is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    require_once __DIR__ . '/../models/schema.php';
    Schema::init();
    echo json_encode(['success' => true, 'message' => 'Migrations complete']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
