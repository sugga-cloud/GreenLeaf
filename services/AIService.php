<?php

require_once __DIR__ . '/../config.php';

function stripThinkTags($content) {
    $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
    $content = preg_replace('/<reasoning>.*?<\/reasoning>/s', '', $content);
    $content = trim($content);
    if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $m)) {
        $content = $m[1];
    }
    return trim($content);
}

function ai($rules, $context, $input, $retries = 2) {
    $apiKey = env('AI_API_KEY');
    $model = env('AI_MODEL', 'llama-3.3-70b-versatile');
    $apiUrl = env('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

    // Override with DB setting if admin saved a Groq key
    try {
        if (!isset($db)) {
            $db = new PDO('sqlite:' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'database.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $dbKey = $db->query("SELECT value FROM settings WHERE key = 'groq_api_key'")->fetchColumn();
        if (!empty($dbKey)) {
            $apiKey = $dbKey;
            $model = 'llama-3.3-70b-versatile';
            $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        }
    } catch (Exception $e) {
        // DB not available, use .env values
    }

    $systemPrompt = "Rules:\n$rules\n\nContext:\n$context";

    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $input],
        ],
        'max_tokens' => 3000,
    ];

    $lastError = null;
    for ($attempt = 0; $attempt <= $retries; $attempt++) {
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
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $lastError = "cURL Error: $error";
        } elseif ($httpCode === 200) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            $content = stripThinkTags($content);
            if (!empty(trim($content))) {
                return $content;
            }
            $lastError = "AI returned empty content (attempt " . ($attempt + 1) . ")";
        } else {
            $data = json_decode($response, true);
            $lastError = "API Error ($httpCode): " . ($data['error']['message'] ?? 'Unknown');
        }

        if ($attempt < $retries) {
            sleep(2 * ($attempt + 1));
        }
    }

    throw new Exception($lastError);
}
