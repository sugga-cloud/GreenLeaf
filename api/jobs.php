<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $profiles = $db->query("SELECT * FROM job_profiles ORDER BY id ASC")->fetchAll();
        echo json_encode(['success' => true, 'data' => $profiles]);
        exit;
    }

    if ($method === 'POST') {
        $userId = Auth::user_id();
        if (!$userId || !Auth::is_student()) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $action = $input['action'] ?? '';

        if ($action === 'add_custom_profile') {
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $icon = trim($input['icon'] ?? 'work');

            if (empty($name)) {
                echo json_encode(['success' => false, 'error' => 'Profile name is required.']);
                exit;
            }

            // Check duplicate
            $stmt = $db->prepare("SELECT id FROM job_profiles WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn()) {
                echo json_encode(['success' => false, 'error' => 'A profile with this name already exists.']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO job_profiles (name, description, icon) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $icon]);

            echo json_encode(['success' => true, 'message' => 'Custom profile added successfully.']);
            exit;
        }

        if ($action === 'delete_profile') {
            $id = intval($input['id'] ?? 0);
            
            // Do not delete default ones if you want, but letting users delete custom ones is standard
            $db->prepare("DELETE FROM job_profiles WHERE id = ?")->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Profile deleted successfully.']);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
