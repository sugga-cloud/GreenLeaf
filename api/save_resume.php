<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "services" . DIRECTORY_SEPARATOR . "Auth.php";
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "sqlite" . DIRECTORY_SEPARATOR . "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = Auth::user_id();
    if (!$user_id || !Auth::is_student()) {
        echo json_encode(["success" => false, "message" => "Not authenticated. Please sign in."]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $job_profile = trim($data["job_profile"] ?? "");
    $template = trim($data["template"] ?? "");

    if (empty($job_profile) || empty($template)) {
        echo json_encode(["success" => false, "message" => "Job profile and template are required."]);
        exit;
    }

    try {
        $check_stmt = $db->prepare("SELECT id FROM resumes WHERE user_id = ? AND job_profile = ? AND template = ?");
        $check_stmt->execute([$user_id, $job_profile, $template]);
        $existing_resume = $check_stmt->fetch();

        if ($existing_resume) {
            echo json_encode(["success" => false, "message" => "Resume with this job profile and template already exists."]);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO resumes (user_id, job_profile, template) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $job_profile, $template]);

        echo json_encode(["success" => true, "message" => "Resume saved successfully!", "resume_id" => $db->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>