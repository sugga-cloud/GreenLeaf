<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$profile = $_POST['profile'] ?? '';
$template = $_POST['template'] ?? '';
$user_id = Auth::user_id();

if (empty($profile) || empty($template)) {
    echo json_encode(['success' => false, 'error' => 'Missing profile or template']);
    exit;
}

// Fetch user
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$student = $user_stmt->fetch();

if (!$student) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Check AI credits (with beta global override)
$beta_credits = Auth::beta_global_credits();
$available_credits = $beta_credits !== null ? $beta_credits : (int)($student['ai_credits'] ?? 0);
$is_beta_override = $beta_credits !== null;
if ($available_credits <= 0) {
    echo json_encode(['success' => false, 'error' => 'No AI credits remaining. Please upgrade your plan.', 'no_credits' => true]);
    exit;
}

// Check resume limit
$plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
$plan_stmt->execute([$student['current_plan'] ?? 'Starter Launch']);
$user_plan = $plan_stmt->fetch() ?: ['name' => 'Starter Launch', 'max_resumes' => 2];

$count_stmt = $db->prepare("SELECT COUNT(*) FROM resumes WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$current_resumes = $count_stmt->fetchColumn();

if ($current_resumes >= intval($user_plan['max_resumes'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Resume limit reached! Your plan (' . htmlspecialchars($user_plan['name']) . ') allows up to ' . $user_plan['max_resumes'] . ' resumes. Upgrade to create more.',
        'limit_reached' => true
    ]);
    exit;
}

// Create resume with "pending" status (async generation)
try {
    $stmt = $db->prepare("INSERT INTO resumes (user_id, job_profile, template, status) VALUES (:user_id, :job_profile, :template, 'pending')");
    $stmt->execute([
        ':user_id' => $user_id,
        ':job_profile' => $profile,
        ':template' => $template,
    ]);

    $resume_id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $resume_id,
        'status' => 'pending',
        'message' => 'Resume queued for AI generation'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to create resume: ' . $e->getMessage()]);
}
