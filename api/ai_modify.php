<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
Auth::start_session();
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
require_once __DIR__ . '/../services/AIService.php';
require_once __DIR__ . '/../services/Logger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['resume_id']) || empty($input['instruction'])) {
    echo json_encode(['success' => false, 'error' => 'Missing resume_id or instruction']);
    exit;
}

$resume_id = (int) $input['resume_id'];
$instruction = trim($input['instruction']);
$history = $input['history'] ?? [];
$user_id = Auth::user_id();

log_info('ai_modify', '=== AI Modify request ===', [
    'resume_id' => $resume_id,
    'user_id'   => $user_id,
    'instruction_len' => strlen($instruction),
    'history_count'   => count($history)
]);

// Fetch user
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$student = $user_stmt->fetch();

if (!$student) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Check plan permission (with beta override)
$beta_perm = Auth::beta_perm('perm_ai_modify');
if ($beta_perm === false) {
    echo json_encode([
        'success' => false,
        'error' => 'AI Modify is disabled in beta settings.',
        'upgrade_required' => true
    ]);
    exit;
}
if ($beta_perm === true) {
    // Beta override: permission granted
} else {
    // Normal plan check
    $plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
    $plan_stmt->execute([$student['current_plan'] ?? 'Starter Launch']);
    $user_plan = $plan_stmt->fetch() ?: ['perm_ai_modify' => 0];

    if (empty($user_plan['perm_ai_modify'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Your current plan does not include AI Modify. Upgrade to Pro or Elite to use this feature.',
            'upgrade_required' => true
        ]);
        exit;
    }
}

// Check credits (with beta global override)
$beta_credits = Auth::beta_global_credits();
$available_credits = $beta_credits !== null ? $beta_credits : (int)($student['ai_credits'] ?? 0);
if ($available_credits <= 0) {
    echo json_encode(['success' => false, 'error' => 'No AI credits remaining', 'no_credits' => true]);
    exit;
}

// Fetch resume
$resume_stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$resume_stmt->execute([$resume_id, $user_id]);
$resume = $resume_stmt->fetch();

if (!$resume) {
    echo json_encode(['success' => false, 'error' => 'Resume not found']);
    exit;
}

$current_content = $resume['ai_content'];
if (empty($current_content)) {
    echo json_encode(['success' => false, 'error' => 'No AI content to modify. Generate a resume first.']);
    exit;
}

$parsed_content = json_decode($current_content, true);
if (!$parsed_content) {
    echo json_encode(['success' => false, 'error' => 'Resume content is corrupted']);
    exit;
}

// Build system prompt with conversation context
$system_rules = 'You are a professional resume editing agent. You help users modify their resumes based on natural language instructions.

CRITICAL RULES:
1. Return ONLY valid JSON. No markdown, no code blocks, no explanation text.
2. Use ONLY the data already present in the resume. Do NOT invent or fabricate new information.
3. Apply the user requested changes precisely.
4. Keep all other sections unchanged unless the user specifically asks to modify them.
5. The JSON structure MUST match exactly what was provided.

JSON Structure:
{
    "header": {"full_name": "string", "job_title": "string", "email": "string", "phone": "string", "location": "string", "linkedin": "string or null", "github": "string or null", "portfolio": "string or null"},
    "summary": "string",
    "experience": [{"job_title": "string", "company": "string", "location": "string", "start_date": "string", "end_date": "string", "is_current": boolean, "description": "string"}],
    "academics": [{"degree": "string", "institution": "string", "board_university": "string", "start_year": "string", "end_year": "string", "grade": "string", "description": "string"}],
    "skills": [{"skill_name": "string", "proficiency": "Beginner or Intermediate or Advanced or Expert"}],
    "projects": [{"title": "string", "tech_stack": "string", "url": "string or null", "start_date": "string", "end_date": "string", "description": "string"}],
    "achievements": [{"title": "string", "issuer": "string", "date": "string", "description": "string"}],
    "generated_for": "string",
    "generated_at": "string"
}';

$context = "Current Resume JSON:\n" . json_encode($parsed_content, JSON_PRETTY_PRINT) . "\n\nJob Profile: {$resume['job_profile']}";

// Build messages array with conversation history
$messages = [
    ['role' => 'system', 'content' => "Rules:\n$system_rules\n\nContext:\n$context"]
];

// Add conversation history (last 10 turns to stay within token limits)
$recent_history = array_slice($history, -10);
foreach ($recent_history as $turn) {
    if (!empty($turn['role']) && !empty($turn['content'])) {
        $role = in_array($turn['role'], ['user', 'assistant']) ? $turn['role'] : 'user';
        $messages[] = ['role' => $role, 'content' => $turn['content']];
    }
}

// Add current instruction
$messages[] = ['role' => 'user', 'content' => "Apply this modification: $instruction\n\nReturn the complete updated resume JSON."];

try {
    $model = env('AI_MODEL', 'llama-3.3-70b-versatile');
    $apiUrl = env('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
    $apiKey = env('AI_API_KEY');

    // Try DB key override
    try {
        if (!isset($db)) {
            $db = new PDO('sqlite:' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'database.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $dbKey = $db->query("SELECT value FROM settings WHERE key = 'groq_api_key'")->fetchColumn();
        if (!empty($dbKey)) {
            $apiKey = $dbKey;
        }
    } catch (Exception $e) {}

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => 3000,
        'temperature' => 0.3,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT => 90,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Connection error: $curlError");
    }

    if ($httpCode !== 200) {
        $data = json_decode($response, true);
        throw new Exception("API Error ($httpCode): " . ($data['error']['message'] ?? 'Unknown'));
    }

    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
    $content = trim($content);

    if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $m)) {
        $content = $m[1];
    }
    $content = trim($content);

    $decoded = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        throw new Exception("AI returned invalid JSON: " . json_last_error_msg());
    }

    // Validate minimal structure
    if (empty($decoded['header']['full_name']) && empty($decoded['header']['email'])) {
        throw new Exception("AI returned empty resume data");
    }

    // Update resume
    $db->prepare("UPDATE resumes SET ai_content = ? WHERE id = ? AND user_id = ?")
       ->execute([$content, $resume_id, $user_id]);

    // Deduct credit (skip if beta global credits active)
    if ($beta_credits === null) {
        $db->prepare("UPDATE users SET ai_credits = ai_credits - 1 WHERE id = ?")->execute([$user_id]);
    }

    log_info('ai_modify', '=== AI Modify SUCCESS ===', [
        'resume_id'   => $resume_id,
        'credits_left' => $beta_credits !== null ? $beta_credits : ($student['ai_credits'] - 1)
    ]);

    echo json_encode([
        'success' => true,
        'ai_content' => $decoded,
        'credits_remaining' => $beta_credits !== null ? $beta_credits : ($student['ai_credits'] - 1)
    ]);

} catch (Exception $e) {
    log_error('ai_modify', '=== AI Modify FAILED ===', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'error' => 'Modification failed: ' . $e->getMessage()]);
}
