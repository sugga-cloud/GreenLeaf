<?php
require_once __DIR__ . '/services/Auth.php';
Auth::start_session();

// Simple router
$page = isset($_GET['page']) ? $_GET['page'] : 'landing';

// Handle universal logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    Auth::clear_session();
    echo '<script>window.location.href = "?page=landing";</script>';
    exit;
}

$allowed_pages = [
    'landing',
    'auth',
    'student_registration',
    'select_job_profile',
    'admin_dashboard',
    'user_dashboard',
    'preview_resume',
    'profile',
    'resumes',
    'settings',
    'plan',
    'support',
    'template_store',
    'checkout',
    'debug_logs',
];

if (!in_array($page, $allowed_pages)) {
    $page = 'landing';
}

if ($page === 'admin_dashboard') {
    Auth::require_admin();
}

$student_protected = [
    'user_dashboard',
    'select_job_profile',
    'preview_resume',
    'profile',
    'resumes',
    'settings',
    'plan',
    'support',
    'template_store',
    'checkout',
    'debug_logs'
];

if (in_array($page, $student_protected)) {
    Auth::require_student();
}

require_once __DIR__ . '/pages/' . $page . '.php';
?>
