<?php
// Log file for debugging
$logFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'upload_debug.log';
@mkdir(dirname($logFile), 0777, true);
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] === REQUEST START ===\n", FILE_APPEND);

function ulog($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

ulog("REQUEST_METHOD={$_SERVER['REQUEST_METHOD']}");
ulog("Content-Type header: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A'));

// Step 1: Auth
try {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
    ulog("Auth.php loaded OK");
} catch (Throwable $e) {
    ulog("FATAL: Auth.php load failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Auth load failed: ' . $e->getMessage()]);
    exit;
}

Auth::start_session();
ulog("Session started, status=" . session_status());
ulog("SESSION=" . json_encode($_SESSION));

try {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
    ulog("db.php loaded OK");
} catch (Throwable $e) {
    ulog("FATAL: db.php load failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'DB load failed: ' . $e->getMessage()]);
    exit;
}

try {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'AIService.php';
    ulog("AIService.php loaded OK");
} catch (Throwable $e) {
    ulog("FATAL: AIService.php load failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'AI service load failed: ' . $e->getMessage()]);
    exit;
}

try {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'PDFParser.php';
    ulog("PDFParser.php loaded OK");
} catch (Throwable $e) {
    ulog("FATAL: PDFParser.php load failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'PDF parser load failed: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');
ulog("JSON header sent");

$debugMode = true;

// Step 2: Auth check
$user_id = Auth::user_id();
ulog("user_id=$user_id is_student=" . (Auth::is_student() ? 'true' : 'false'));
if (!$user_id || !Auth::is_student()) {
    ulog("AUTH FAILED: user_id=$user_id");
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please sign in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ulog("NOT POST");
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Step 3: File check
ulog("FILES=" . json_encode($_FILES ? array_keys($_FILES) : 'empty'));
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['resume']['error'] ?? 'No file uploaded';
    ulog("UPLOAD FAILED: error=$error");
    echo json_encode(['success' => false, 'error' => 'Upload failed: ' . $error]);
    exit;
}

$file = $_FILES['resume'];
ulog("File: name={$file['name']} size={$file['size']} tmp={$file['tmp_name']}");

// Step 4: Validate
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
ulog("Extension: $ext");
if ($ext !== 'pdf') {
    echo json_encode(['success' => false, 'error' => 'Only PDF files are accepted']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 5MB']);
    exit;
}

$header = @file_get_contents($file['tmp_name'], false, null, 0, 5);
ulog("PDF header: " . bin2hex($header ?? ''));
if ($header !== '%PDF-') {
    echo json_encode(['success' => false, 'error' => 'File is not a valid PDF']);
    exit;
}

// Step 5: PDF extraction
try {
    ulog("Starting PDFParser::extractText...");
    $extractedText = PDFParser::extractText($file['tmp_name']);
    ulog("Extracted text length=" . strlen($extractedText));
    ulog("Extracted text preview: " . substr($extractedText, 0, 200));
} catch (Throwable $e) {
    ulog("PDFParser EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    echo json_encode(['success' => false, 'error' => 'PDF extraction failed: ' . $e->getMessage()]);
    exit;
}

if (empty(trim($extractedText))) {
    ulog("Extracted text is empty");
    echo json_encode(['success' => false, 'error' => 'No text could be extracted from PDF']);
    exit;
}

// Step 6: AI call
try {
    $rules = 'Return ONLY valid JSON, no markdown, no code blocks. Parse the resume text into this structure:
{
    "personal": {"full_name": "string", "email": "string", "phone": "string", "location": "string", "linkedin": "string or null", "github": "string or null", "portfolio": "string or null", "summary": "string"},
    "academics": [{"degree": "string", "institution": "string", "board_university": "string", "start_year": "string", "end_year": "string", "grade": "string", "description": "string"}],
    "experience": [{"job_title": "string", "company": "string", "location": "string", "start_date": "string", "end_date": "string", "is_current": boolean, "description": "string"}],
    "skills": [{"skill_name": "string", "proficiency": "Beginner or Intermediate or Advanced or Expert"}],
    "projects": [{"title": "string", "tech_stack": "string", "url": "string or null", "start_date": "string", "end_date": "string", "description": "string"}],
    "achievements": [{"title": "string", "issuer": "string", "date": "string", "description": "string"}]
}';

    $context = "Extracted resume text:\n$extractedText";
    $input = "Parse this resume text into the structured JSON format.";

    ulog("Calling AI...");
    $ai_response = ai($rules, $context, $input);
    ulog("AI response length=" . strlen($ai_response));
    ulog("AI response preview: " . substr($ai_response, 0, 300));

    $profile_data = json_decode($ai_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ulog("JSON decode error: " . json_last_error_msg());
        throw new Exception("AI returned invalid JSON: " . json_last_error_msg());
    }

    ulog("SUCCESS: returning profile data");
    echo json_encode([
        'success' => true,
        'profile_data' => $profile_data,
        'extracted_text' => substr($extractedText, 0, 500)
    ]);

} catch (Throwable $e) {
    ulog("EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    $response = ['success' => false, 'error' => 'Failed to process resume: ' . $e->getMessage()];
    $response['debug'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ];
    echo json_encode($response);
}
