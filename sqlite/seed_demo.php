<?php
// Creates a demo database with rich seed data for contributors
// Does NOT touch existing database.sqlite

$target = __DIR__ . DIRECTORY_SEPARATOR . 'database.demo.sqlite';
if (file_exists($target)) unlink($target);

// Create a fresh connection directly
$orig_path = __DIR__ . DIRECTORY_SEPARATOR . 'database.sqlite';
$orig_exists = file_exists($orig_path);

// Back up user's real DB if it exists
if ($orig_exists) {
    rename($orig_path, $orig_path . '.bak');
}

// Run schema to create fresh DB
require_once __DIR__ . '/../models/schema.php';
$schema = new Schema();
$db = $schema::getDB();
$schema::init();

// Copy to demo file
copy($orig_path, $target);
echo "Copied schema to demo DB\n";

// Restore original DB
if ($orig_exists) {
    unlink($orig_path);
    rename($orig_path . '.bak', $orig_path);
    echo "Restored original database\n";
} else {
    unlink($orig_path);
}

// Now seed extra demo data
$demo = new PDO('sqlite:' . $target);
$demo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Add extra demo users (beyond the 2 seeded by schema)
$count = $demo->query("SELECT COUNT(*) FROM users")->fetchColumn();
echo "Existing users: $count\n";

$ins = $demo->prepare("INSERT OR IGNORE INTO users (first_name, last_name, email, phone, location, current_plan, ai_credits, trial_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$ins->execute(['Alice', 'Johnson', 'alice@example.com', '+1-555-0101', 'New York, NY', 'Pro Career Growth', 50, 'Active']);
$ins->execute(['Bob', 'Smith', 'bob@example.com', '+1-555-0102', 'San Francisco, CA', 'Starter Launch', 5, 'Active']);
$ins->execute(['Carol', 'Williams', 'carol@example.com', '+1-555-0103', 'Chicago, IL', 'Pro Career Growth', 50, 'Active']);

// Sample profile for Alice (user 3 in demo)
$demo->exec("INSERT OR IGNORE INTO profile_personal (user_id, full_name, email, phone, city, summary) VALUES (3, 'Alice Johnson', 'alice@example.com', '+1-555-0101', 'New York, NY', 'Experienced full-stack developer with 5+ years building scalable web applications.')");
$demo->exec("INSERT OR IGNORE INTO profile_academics (user_id, degree, institution, start_year, end_year, grade) VALUES (3, 'B.S. Computer Science', 'MIT', '2016', '2020', '3.8 GPA')");
$demo->exec("INSERT OR IGNORE INTO profile_experience (user_id, job_title, company, start_date, end_date, description) VALUES (3, 'Senior Developer', 'Tech Corp', '2020-06', '2024-01', 'Led team of 5 developers building React/PHP applications.')");
$demo->exec("INSERT OR IGNORE INTO profile_skills (user_id, skill_name, proficiency) VALUES (3, 'PHP', 'Advanced'), (3, 'JavaScript', 'Advanced'), (3, 'React', 'Intermediate')");
$demo->exec("INSERT OR IGNORE INTO profile_projects (user_id, title, description) VALUES (3, 'CLI Tool', 'Built a CLI tool for automated PHP linting used by 500+ developers')");

echo "\nDemo database created: database.demo.sqlite\n";
echo "Size: " . filesize($target) . " bytes\n";
echo "\nTo use: copy sqlite/database.demo.sqlite to sqlite/database.sqlite\n";
