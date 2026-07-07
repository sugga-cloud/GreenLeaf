<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'submit') {
    $user_id = Auth::user_id();
    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'Login required']);
        exit;
    }
    $message = trim($_POST['message'] ?? '');
    if (strlen($message) < 3) {
        echo json_encode(['success' => false, 'error' => 'Too short']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO feedbacks (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $message]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'list') {
    if (!Auth::is_admin()) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    $stmt = $db->query("SELECT f.*, u.email, u.first_name, u.last_name FROM feedbacks f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

if ($action === 'delete') {
    if (!Auth::is_admin()) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db->prepare("DELETE FROM feedbacks WHERE id = ?")->execute([$id]);
    }
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
