<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
if (!Auth::is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../sqlite/db.php';

$action = $_POST['action'] ?? '';

if ($action === 'cleanup_duplicates') {
    try {
        $db->beginTransaction();

        // Find duplicate emails
        $dupes = $db->query("SELECT email, COUNT(*) as cnt FROM users GROUP BY email HAVING cnt > 1")->fetchAll();

        $total_deleted = 0;
        foreach ($dupes as $group) {
            $email = $group['email'];
            $users = $db->prepare("SELECT id FROM users WHERE email = ? ORDER BY id ASC");
            $users->execute([$email]);
            $ids = $users->fetchAll(PDO::FETCH_COLUMN);

            // Keep the last (most recent) ID, delete earlier ones with 0 resumes
            $keep_id = array_pop($ids);
            foreach ($ids as $del_id) {
                $resume_count = $db->prepare("SELECT COUNT(*) FROM resumes WHERE user_id = ?");
                $resume_count->execute([$del_id]);
                if ($resume_count->fetchColumn() == 0) {
                    // Transfer orphan data to kept user if any
                    $tables = ['profile_personal', 'profile_academics', 'profile_experience', 'profile_skills', 'profile_projects', 'profile_hobbies', 'profile_achievements'];
                    foreach ($tables as $t) {
                        try {
                            $db->prepare("UPDATE $t SET user_id = ? WHERE user_id = ?")->execute([$keep_id, $del_id]);
                        } catch (Exception $e) {}
                    }
                    $db->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$del_id]);
                    $db->prepare("DELETE FROM tickets WHERE user_id = ?")->execute([$del_id]);
                    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
                    $total_deleted++;
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'deleted' => $total_deleted]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'check_email') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Email required']);
        exit;
    }
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $count = (int)$stmt->fetchColumn();
    echo json_encode(['success' => true, 'exists' => $count > 0, 'count' => $count]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid action']);
