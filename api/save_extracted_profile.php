<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['profile_data'])) {
    echo json_encode(['success' => false, 'error' => 'No profile data provided']);
    exit;
}

$user_id = Auth::user_id();
if (!$user_id || !Auth::is_student()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please sign in.']);
    exit;
}
$data = $input['profile_data'];

try {
    // Save personal details
    if (!empty($data['personal'])) {
        $p = $data['personal'];
        $stmt_ex = $db->prepare("SELECT id FROM profile_personal WHERE user_id = ? LIMIT 1");
        $stmt_ex->execute([$user_id]);
        $existing = $stmt_ex->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE profile_personal SET full_name=?, email=?, phone=?, city=?, linkedin=?, github=?, portfolio=?, summary=? WHERE user_id=?");
            $stmt->execute([
                $p['full_name'] ?? '', $p['email'] ?? '', $p['phone'] ?? '',
                $p['location'] ?? '', $p['linkedin'] ?? '', $p['github'] ?? '',
                $p['portfolio'] ?? '', $p['summary'] ?? '', $user_id
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO profile_personal (user_id, full_name, email, phone, city, linkedin, github, portfolio, summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id, $p['full_name'] ?? '', $p['email'] ?? '', $p['phone'] ?? '',
                $p['location'] ?? '', $p['linkedin'] ?? '', $p['github'] ?? '',
                $p['portfolio'] ?? '', $p['summary'] ?? ''
            ]);
        }
    }

    // Save academics
    if (!empty($data['academics']) && is_array($data['academics'])) {
        // Clear existing
        $db->prepare("DELETE FROM profile_academics WHERE user_id = ?")->execute([$user_id]);
        
        $stmt = $db->prepare("INSERT INTO profile_academics (user_id, degree, institution, board_university, start_year, end_year, grade, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($data['academics'] as $a) {
            $stmt->execute([
                $user_id, $a['degree'] ?? '', $a['institution'] ?? '', $a['board_university'] ?? '',
                $a['start_year'] ?? '', $a['end_year'] ?? '', $a['grade'] ?? '', $a['description'] ?? ''
            ]);
        }
    }

    // Save experience
    if (!empty($data['experience']) && is_array($data['experience'])) {
        $db->prepare("DELETE FROM profile_experience WHERE user_id = ?")->execute([$user_id]);
        
        $stmt = $db->prepare("INSERT INTO profile_experience (user_id, job_title, company, location, start_date, end_date, is_current, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($data['experience'] as $e) {
            $is_current = ($e['is_current'] ?? false) ? 1 : 0;
            // Also check if end_date contains "Present"
            if (stripos($e['end_date'] ?? '', 'present') !== false) {
                $is_current = 1;
            }
            $stmt->execute([
                $user_id, $e['job_title'] ?? '', $e['company'] ?? '', $e['location'] ?? '',
                $e['start_date'] ?? '', $e['end_date'] ?? '', $is_current, $e['description'] ?? ''
            ]);
        }
    }

    // Save skills
    if (!empty($data['skills']) && is_array($data['skills'])) {
        $db->prepare("DELETE FROM profile_skills WHERE user_id = ?")->execute([$user_id]);
        
        $stmt = $db->prepare("INSERT INTO profile_skills (user_id, skill_name, proficiency) VALUES (?, ?, ?)");
        foreach ($data['skills'] as $s) {
            $proficiency = $s['proficiency'] ?? 'Intermediate';
            // Normalize proficiency
            $valid_levels = ['Beginner', 'Intermediate', 'Advanced', 'Expert'];
            if (!in_array($proficiency, $valid_levels)) {
                $proficiency = 'Intermediate';
            }
            $stmt->execute([$user_id, $s['skill_name'] ?? '', $proficiency]);
        }
    }

    // Save projects
    if (!empty($data['projects']) && is_array($data['projects'])) {
        $db->prepare("DELETE FROM profile_projects WHERE user_id = ?")->execute([$user_id]);
        
        $stmt = $db->prepare("INSERT INTO profile_projects (user_id, title, tech_stack, url, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($data['projects'] as $p) {
            $stmt->execute([
                $user_id, $p['title'] ?? '', $p['tech_stack'] ?? '', $p['url'] ?? '',
                $p['start_date'] ?? '', $p['end_date'] ?? '', $p['description'] ?? ''
            ]);
        }
    }

    // Save achievements
    if (!empty($data['achievements']) && is_array($data['achievements'])) {
        $db->prepare("DELETE FROM profile_achievements WHERE user_id = ?")->execute([$user_id]);
        
        $stmt = $db->prepare("INSERT INTO profile_achievements (user_id, title, issuer, date, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($data['achievements'] as $a) {
            $stmt->execute([
                $user_id, $a['title'] ?? '', $a['issuer'] ?? '', $a['date'] ?? '', $a['description'] ?? ''
            ]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Profile data imported successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to save profile: ' . $e->getMessage()]);
}
