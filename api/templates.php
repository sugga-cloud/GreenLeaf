<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $user_id = Auth::user_id();

    if ($method === 'GET') {
        $user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch();

        $plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
        $plan_stmt->execute([$user['current_plan'] ?? 'Starter Launch']);
        $user_plan = $plan_stmt->fetch() ?: ['id' => 1, 'access_paid_templates' => 0];

        $scope = $_GET['scope'] ?? 'accessible';

        if ($scope === 'all') {
            $rows = $db->query("
                SELECT id, name, description, type, accent_color, icon, image_url
                FROM templates
                WHERE LOWER(status) = 'active'
                ORDER BY (LOWER(type) = 'free') DESC, name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $rows = $db->prepare("
                SELECT id, name, description, type, accent_color, icon, image_url
                FROM templates
                WHERE LOWER(status) = 'active'
                  AND (
                    LOWER(type) = 'free'
                    OR id IN (SELECT template_id FROM plan_templates WHERE plan_id = ?)
                  )
                ORDER BY (LOWER(type) = 'free') DESC, name ASC
            ");
            $rows->execute([$user_plan['id']]);
            $rows = $rows->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($rows as &$t) {
            $t['locked'] = false;
        }
        unset($t);

        echo json_encode([
            'success' => true,
            'data' => $rows,
            'plan' => [
                'name' => $user_plan['name'] ?? 'Starter Launch',
                'access_paid_templates' => Auth::beta_perm('perm_paid_templates') ?? (int)($user_plan['access_paid_templates'] ?? 0)
            ]
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;
        $action = $input['action'] ?? '';

        if ($action === 'seed') {
            $defaults = [
                ['Minimalist', 'Clean, simple, high-readability layout with centered header. Perfect for traditional industries.', 'Free', '#006C49', 'menu'],
                ['Standard Modern', 'Universal two-column structure for dynamic student internships and job applications.', 'Free', '#006C49', 'grid_view'],
                ['Creative Leaf', 'Vibrant layout featuring forest accents and premium asymmetric curves to stand out instantly.', 'Free', '#006C49', 'spa'],
                ['Modern Tech Pro', 'Engineered specific sections for tech stacks, dynamic project links, and code highlights.', 'Paid', '#0F2C59', 'terminal'],
                ['Executive Elite', 'High-end classy serif alignments tailored for product managers, directors, and strategic leadership roles.', 'Paid', '#1A1A2E', 'military_tech'],
            ];
            $stmt = $db->prepare("INSERT OR IGNORE INTO templates (name, description, type, accent_color, icon) VALUES (?, ?, ?, ?, ?)");
            foreach ($defaults as $d) $stmt->execute($d);
            echo json_encode(['success' => true, 'message' => 'Templates seeded.']);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
