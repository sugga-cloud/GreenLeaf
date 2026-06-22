<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

header('Content-Type: application/json');

$userId = Auth::user_id();
if (!$userId || !Auth::is_student()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please sign in.']);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $db->prepare("SELECT * FROM profile_personal WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $personal = $stmt->fetch() ?: null;

        $stmt = $db->prepare("SELECT * FROM profile_academics WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $academics = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM profile_experience WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $experience = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM profile_skills WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $skills = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM profile_projects WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $projects = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM profile_achievements WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $achievements = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT * FROM profile_hobbies WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        $hobbies = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'data' => [
                'personal' => $personal,
                'academics' => $academics,
                'experience' => $experience,
                'skills' => $skills,
                'projects' => $projects,
                'achievements' => $achievements,
                'hobbies' => $hobbies
            ]
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $action = $input['action'] ?? '';

        if ($action === 'save_personal') {
            $fields = ['full_name','email','phone','dob','gender','nationality','address','city','linkedin','github','portfolio','summary'];
            $check = $db->prepare("SELECT id FROM profile_personal WHERE user_id = ? LIMIT 1");
            $check->execute([$userId]);
            $exists = $check->fetchColumn();

            if ($exists) {
                $sets = implode(',', array_map(fn($f) => "$f = :$f", $fields));
                $stmt = $db->prepare("UPDATE profile_personal SET $sets WHERE user_id = :uid");
                $stmt->bindValue(':uid', $userId);
            } else {
                $cols = implode(',', $fields);
                $vals = implode(',', array_map(fn($f) => ":$f", $fields));
                $stmt = $db->prepare("INSERT INTO profile_personal (user_id, $cols) VALUES (:uid, $vals)");
                $stmt->bindValue(':uid', $userId);
            }

            foreach ($fields as $f) {
                $stmt->bindValue(":$f", $input[$f] ?? '');
            }
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Personal details updated.']);
            exit;
        }

        if ($action === 'add_academic') {
            $stmt = $db->prepare("INSERT INTO profile_academics (user_id, degree, institution, board_university, start_year, end_year, grade, description) VALUES (:uid, :d, :i, :b, :sy, :ey, :g, :desc)");
            $stmt->execute([
                ':uid' => $userId,
                ':d'   => $input['degree'] ?? '',
                ':i'   => $input['institution'] ?? '',
                ':b'   => $input['board_university'] ?? '',
                ':sy'  => $input['start_year'] ?? '',
                ':ey'  => $input['end_year'] ?? '',
                ':g'   => $input['grade'] ?? '',
                ':desc'=> $input['description'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Academic record added.']);
            exit;
        }

        if ($action === 'delete_academic') {
            $id = $input['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM profile_academics WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Academic record deleted.']);
            exit;
        }

        if ($action === 'add_experience') {
            $stmt = $db->prepare("INSERT INTO profile_experience (user_id, job_title, company, location, start_date, end_date, is_current, description) VALUES (:uid, :jt, :co, :loc, :sd, :ed, :ic, :desc)");
            $stmt->execute([
                ':uid' => $userId,
                ':jt'  => $input['job_title'] ?? '',
                ':co'  => $input['company'] ?? '',
                ':loc' => $input['location'] ?? '',
                ':sd'  => $input['start_date'] ?? '',
                ':ed'  => $input['end_date'] ?? '',
                ':ic'  => !empty($input['is_current']) ? 1 : 0,
                ':desc'=> $input['description'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Experience record added.']);
            exit;
        }

        if ($action === 'delete_experience') {
            $id = $input['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM profile_experience WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Experience record deleted.']);
            exit;
        }

        if ($action === 'add_skill') {
            $stmt = $db->prepare("INSERT INTO profile_skills (user_id, skill_name, proficiency) VALUES (:uid, :s, :p)");
            $stmt->execute([
                ':uid' => $userId,
                ':s'   => $input['skill_name'] ?? '',
                ':p'   => $input['proficiency'] ?? 'Intermediate'
            ]);
            echo json_encode(['success' => true, 'message' => 'Skill added.']);
            exit;
        }

        if ($action === 'delete_skill') {
            $id = $input['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM profile_skills WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Skill deleted.']);
            exit;
        }

        if ($action === 'add_project') {
            $stmt = $db->prepare("INSERT INTO profile_projects (user_id, title, tech_stack, url, start_date, end_date, description) VALUES (:uid, :t, :ts, :u, :sd, :ed, :desc)");
            $stmt->execute([
                ':uid' => $userId,
                ':t'   => $input['title'] ?? '',
                ':ts'  => $input['tech_stack'] ?? '',
                ':u'   => $input['url'] ?? '',
                ':sd'  => $input['start_date'] ?? '',
                ':ed'  => $input['end_date'] ?? '',
                ':desc'=> $input['description'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Project added successfully.']);
            exit;
        }

        if ($action === 'delete_project') {
            $id = $input['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM profile_projects WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Project deleted.']);
            exit;
        }

        if ($action === 'add_achievement') {
            $stmt = $db->prepare("INSERT INTO profile_achievements (user_id, title, issuer, date, description) VALUES (:uid, :t, :i, :d, :desc)");
            $stmt->execute([
                ':uid' => $userId,
                ':t'   => $input['title'] ?? '',
                ':i'   => $input['issuer'] ?? '',
                ':d'   => $input['date'] ?? '',
                ':desc'=> $input['description'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Achievement added.']);
            exit;
        }

        if ($action === 'delete_achievement') {
            $id = $input['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM profile_achievements WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Achievement deleted.']);
            exit;
        }

        if ($action === 'add_hobby') {
            $stmt = $db->prepare("INSERT INTO profile_hobbies (user_id, hobby) VALUES (:uid, :h)");
            $stmt->execute([
                ':uid' => $userId,
                ':h'   => $input['hobby'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Hobby added.']);
            exit;
        }

        if ($action === 'delete_hobby') {
            $id = $input['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM profile_hobbies WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Hobby deleted.']);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
