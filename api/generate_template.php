<?php
set_time_limit(0);
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
if (!Auth::is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../sqlite/db.php';
require_once __DIR__ . '/../services/AIService.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$prompt = trim($input['prompt'] ?? '');

if (empty($prompt)) {
    echo json_encode(['success' => false, 'error' => 'Prompt is required']);
    exit;
}

$rules = 'Return ONLY valid JSON, no markdown, no code blocks.

You are a resume template catalog designer. Given a style concept, generate a complete template configuration.

JSON Structure:
{
    "name": "Creative Template Name (max 35 chars)",
    "description": "One professional sentence describing the layout and target audience (max 200 chars)",
    "accent_color": "#hexcode",
    "icon": "material_symbols_icon_name",
    "type": "Free or Paid (use Paid for premium/niche styles, Free for universal styles)"
}

Rules:
- name: should be catchy and professional
- description: professional, 1 sentence
- accent_color: valid hex code matching the vibe
- icon: use common material symbol names like: terminal, spa, grid_view, military_tech, code, auto_awesome, palette, brush, layers, analytics, psychology, bolt, diamond, eco, waves
- type: "Paid" for executive, tech pro, luxury, cyberpunk, premium, or niche styles. "Free" for minimal, standard, clean, modern, universal styles.';

$context = "Style vibe: " . $prompt;

try {
    $ai_response = ai($rules, $context, "Generate a resume template configuration for the given style vibe. Return ONLY the JSON.");

    $decoded = json_decode($ai_response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        throw new Exception("AI returned invalid JSON");
    }

    $template = [
        'name' => substr(trim($decoded['name'] ?? 'Custom Template'), 0, 35),
        'description' => substr(trim($decoded['description'] ?? 'Custom resume template.'), 0, 200),
        'accent_color' => preg_match('/^#[0-9A-Fa-f]{6}$/', $decoded['accent_color'] ?? '') ? $decoded['accent_color'] : '#006C49',
        'icon' => preg_replace('/[^a-z_]/', '', strtolower($decoded['icon'] ?? 'description')),
        'type' => in_array($decoded['type'] ?? '', ['Free', 'Paid']) ? $decoded['type'] : 'Free'
    ];

    echo json_encode(['success' => true, 'template' => $template]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
