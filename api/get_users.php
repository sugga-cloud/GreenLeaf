<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
if (!Auth::is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../sqlite/db.php';

header('Content-Type: application/json');

try {
    $stmt = $db->query("SELECT users.*, COALESCE(rc.cnt, 0) AS resume_count FROM users LEFT JOIN (SELECT user_id, COUNT(*) AS cnt FROM resumes GROUP BY user_id) rc ON users.id = rc.user_id ORDER BY users.created_at DESC");
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
