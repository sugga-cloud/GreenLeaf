<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';
require_once __DIR__ . '/../lib/pdf_generator.php';

$user_id = Auth::user_id();
$resume_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$resume_id) {
    http_response_code(400);
    die('Missing resume ID');
}

$stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->execute([$resume_id, $user_id]);
$resume = $stmt->fetch();

if (!$resume) {
    http_response_code(404);
    die('Resume not found');
}

// Same data fetching as preview_resume.php
$has_ai = !empty($resume['ai_content']);
$ai_data = $has_ai ? json_decode($resume['ai_content'], true) : null;

if ($ai_data) {
    if (isset($ai_data['skills'])) {
        $ai_data['skills'] = array_map(function($s) {
            return ['skill_name' => $s['skill_name'] ?? $s['name'] ?? '', 'proficiency' => $s['proficiency'] ?? $s['level'] ?? ''];
        }, $ai_data['skills']);
    }
    if (isset($ai_data['experience'])) {
        $ai_data['experience'] = array_map(function($e) {
            return ['job_title' => $e['job_title'] ?? $e['position'] ?? '', 'company' => $e['company'] ?? '', 'location' => $e['location'] ?? '', 'start_date' => $e['start_date'] ?? '', 'end_date' => $e['end_date'] ?? '', 'is_current' => $e['is_current'] ?? false, 'description' => is_array($e['description'] ?? '') ? implode("\n", $e['description']) : ($e['description'] ?? '')];
        }, $ai_data['experience']);
    }
    if (isset($ai_data['academics'])) {
        $ai_data['academics'] = array_map(function($a) {
            return ['degree' => $a['degree'] ?? '', 'institution' => $a['institution'] ?? '', 'start_year' => $a['start_year'] ?? $a['graduation_year'] ?? '', 'end_year' => $a['end_year'] ?? $a['graduation_year'] ?? '', 'grade' => $a['grade'] ?? ''];
        }, $ai_data['academics']);
    }
    if (isset($ai_data['projects'])) {
        $ai_data['projects'] = array_map(function($p) {
            return ['title' => $p['title'] ?? $p['name'] ?? '', 'tech_stack' => $p['tech_stack'] ?? (is_array($p['technologies'] ?? null) ? implode(', ', $p['technologies']) : ($p['technologies'] ?? '')), 'url' => $p['url'] ?? '', 'description' => $p['description'] ?? ''];
        }, $ai_data['projects']);
    }
    if (isset($ai_data['achievements'])) {
        $ai_data['achievements'] = array_map(function($a) {
            if (is_string($a)) return ['title' => $a, 'issuer' => '', 'date' => ''];
            return ['title' => $a['title'] ?? '', 'issuer' => $a['issuer'] ?? '', 'date' => $a['date'] ?? ''];
        }, $ai_data['achievements']);
    }
}

$stmt_rp = $db->prepare("SELECT * FROM profile_personal WHERE user_id = ? LIMIT 1");
$stmt_rp->execute([$user_id]);
$raw_personal = $stmt_rp->fetch() ?: null;

$stmt_ra = $db->prepare("SELECT * FROM profile_academics WHERE user_id = ? ORDER BY start_year DESC");
$stmt_ra->execute([$user_id]);
$raw_academics = $stmt_ra->fetchAll();

$stmt_re = $db->prepare("SELECT * FROM profile_experience WHERE user_id = ? ORDER BY start_date DESC");
$stmt_re->execute([$user_id]);
$raw_experience = $stmt_re->fetchAll();

$stmt_rs = $db->prepare("SELECT * FROM profile_skills WHERE user_id = ? ORDER BY id DESC");
$stmt_rs->execute([$user_id]);
$raw_skills = $stmt_rs->fetchAll();

$stmt_rpj = $db->prepare("SELECT * FROM profile_projects WHERE user_id = ? ORDER BY start_date DESC");
$stmt_rpj->execute([$user_id]);
$raw_projects = $stmt_rpj->fetchAll();

$stmt_rach = $db->prepare("SELECT * FROM profile_achievements WHERE user_id = ? ORDER BY date DESC");
$stmt_rach->execute([$user_id]);
$raw_achievements = $stmt_rach->fetchAll();

if ($has_ai && $ai_data) {
    $r_name = $ai_data['header']['full_name'] ?? ($raw_personal['full_name'] ?? 'Your Name');
    $r_jobtitle = $ai_data['header']['job_title'] ?? $resume['job_profile'];
    $r_email = $ai_data['header']['email'] ?? ($raw_personal['email'] ?? '');
    $r_phone = $ai_data['header']['phone'] ?? ($raw_personal['phone'] ?? '');
    $r_location = $ai_data['header']['location'] ?? ($raw_personal['city'] ?? '');
    $r_linkedin = $ai_data['header']['linkedin'] ?? ($raw_personal['linkedin'] ?? '');
    $r_github = $ai_data['header']['github'] ?? ($raw_personal['github'] ?? '');
    $r_portfolio = $ai_data['header']['portfolio'] ?? ($raw_personal['portfolio'] ?? '');
    $r_summary = $ai_data['summary'] ?? ($raw_personal['summary'] ?? '');
    $r_experience = $ai_data['experience'] ?? [];
    $r_academics = $ai_data['academics'] ?? [];
    $r_skills = $ai_data['skills'] ?? [];
    $r_projects = $ai_data['projects'] ?? [];
    $r_achievements = $ai_data['achievements'] ?? [];
} else {
    $r_name = $raw_personal['full_name'] ?? 'Your Name';
    $r_jobtitle = $resume['job_profile'];
    $r_email = $raw_personal['email'] ?? '';
    $r_phone = $raw_personal['phone'] ?? '';
    $r_location = $raw_personal['city'] ?? '';
    $r_linkedin = $raw_personal['linkedin'] ?? '';
    $r_github = $raw_personal['github'] ?? '';
    $r_portfolio = $raw_personal['portfolio'] ?? '';
    $r_summary = $raw_personal['summary'] ?? '';
    $r_experience = array_map(function($e) {
        return ['job_title' => $e['job_title'] ?? '', 'company' => $e['company'] ?? '', 'location' => $e['location'] ?? '', 'start_date' => $e['start_date'] ?? '', 'end_date' => $e['end_date'] ?? '', 'is_current' => $e['is_current'] ?? 0, 'description' => $e['description'] ?? ''];
    }, $raw_experience);
    $r_academics = array_map(function($a) {
        return ['degree' => $a['degree'] ?? '', 'institution' => $a['institution'] ?? '', 'start_year' => $a['start_year'] ?? '', 'end_year' => $a['end_year'] ?? '', 'grade' => $a['grade'] ?? ''];
    }, $raw_academics);
    $r_skills = array_map(function($s) { return ['skill_name' => $s['skill_name'] ?? '', 'proficiency' => $s['proficiency'] ?? '']; }, $raw_skills);
    $r_projects = array_map(function($p) {
        return ['title' => $p['title'] ?? '', 'tech_stack' => $p['tech_stack'] ?? '', 'url' => $p['url'] ?? '', 'description' => $p['description'] ?? ''];
    }, $raw_projects);
    $r_achievements = array_map(function($a) {
        return ['title' => $a['title'] ?? '', 'issuer' => $a['issuer'] ?? '', 'date' => $a['date'] ?? ''];
    }, $raw_achievements);
}

$template = $resume['template'] ?? 'Minimalist';

// Generate PDF
$pdf = new SimplePDF('letter');
$ml = 20; $mr = 20; $mt = 20; $cw = $pdf->pageWidth - $ml - $mr;

// Header
$pdf->setFont('Helvetica', 'B', 22);
$pdf->x = $ml; $pdf->writeLine($cw, $r_name);

$pdf->setFont('Helvetica', '', 10);
$pdf->x = $ml; $pdf->writeLine($cw, $r_jobtitle);

$pdf->setFont('Helvetica', '', 8);
$contact = trim(implode(' | ', array_filter([$r_email, $r_phone, $r_location])));
if ($contact) { $pdf->x = $ml; $pdf->writeLine($cw, $contact); }

$socials = [];
if ($r_linkedin) $socials[] = 'LinkedIn';
if ($r_github) $socials[] = 'GitHub';
if ($r_portfolio) $socials[] = 'Portfolio';
if ($socials) {
    $pdf->x = $ml;
    $socialText = 'Social: ' . implode(', ', $socials);
    $pdf->writeLine($cw, $socialText);
}

$pdf->y += 5;

// Summary
if ($r_summary) {
    $pdf->setFont('Helvetica', 'B', 12);
    $pdf->x = $ml; $pdf->writeLine($cw, 'Professional Summary');
    $pdf->setFont('Helvetica', '', 9);
    $pdf->x = $ml; $pdf->multiCell($cw, $r_summary);
    $pdf->y += 3;
}

// Experience
if ($r_experience) {
    $pdf->setFont('Helvetica', 'B', 12);
    $pdf->x = $ml; $pdf->writeLine($cw, 'Experience');
    foreach ($r_experience as $exp) {
        $pdf->setFont('Helvetica', 'B', 10);
        $title = $exp['job_title'] . ' at ' . $exp['company'];
        $pdf->x = $ml; $pdf->writeLine($cw, $title);
        $dates = ($exp['start_date'] ?? '') . ' - ' . (($exp['is_current'] ?? false) ? 'Present' : ($exp['end_date'] ?? ''));
        $pdf->setFont('Helvetica', 'I', 8);
        $pdf->x = $ml; $pdf->writeLine($cw, $dates);
        if (!empty($exp['description'])) {
            $pdf->setFont('Helvetica', '', 9);
            $pdf->x = $ml; $pdf->multiCell($cw - 10, $exp['description']);
        }
        $pdf->y += 2;
    }
    $pdf->y += 2;
}

// Education
if ($r_academics) {
    $pdf->setFont('Helvetica', 'B', 12);
    $pdf->x = $ml; $pdf->writeLine($cw, 'Education');
    foreach ($r_academics as $acad) {
        $pdf->setFont('Helvetica', 'B', 10);
        $pdf->x = $ml; $pdf->writeLine($cw, $acad['degree'] . ' - ' . $acad['institution']);
        $pdf->setFont('Helvetica', '', 8);
        $eduLine = ($acad['start_year'] ?? '') . ' - ' . ($acad['end_year'] ?? '');
        if (!empty($acad['grade'])) $eduLine .= ' | Grade: ' . $acad['grade'];
        $pdf->x = $ml; $pdf->writeLine($cw, $eduLine);
        $pdf->y += 1;
    }
    $pdf->y += 2;
}

// Skills
if ($r_skills) {
    $pdf->setFont('Helvetica', 'B', 12);
    $pdf->x = $ml; $pdf->writeLine($cw, 'Skills');
    $pdf->setFont('Helvetica', '', 9);
    $skillNames = array_map(function($s) { return $s['skill_name']; }, $r_skills);
    $pdf->x = $ml; $pdf->multiCell($cw, implode(', ', $skillNames));
    $pdf->y += 2;
}

// Projects
if ($r_projects) {
    $pdf->setFont('Helvetica', 'B', 12);
    $pdf->x = $ml; $pdf->writeLine($cw, 'Projects');
    foreach ($r_projects as $proj) {
        $pdf->setFont('Helvetica', 'B', 10);
        $projTitle = $proj['title'];
        if (!empty($proj['tech_stack'])) $projTitle .= ' (' . $proj['tech_stack'] . ')';
        $pdf->x = $ml; $pdf->writeLine($cw, $projTitle);
        if (!empty($proj['description'])) {
            $pdf->setFont('Helvetica', '', 9);
            $pdf->x = $ml; $pdf->multiCell($cw - 10, $proj['description']);
        }
        $pdf->y += 1;
    }
    $pdf->y += 2;
}

// Achievements
if ($r_achievements) {
    $pdf->setFont('Helvetica', 'B', 12);
    $pdf->x = $ml; $pdf->writeLine($cw, 'Achievements');
    $pdf->setFont('Helvetica', '', 9);
    foreach ($r_achievements as $ach) {
        $achLine = $ach['title'];
        if (!empty($ach['issuer'])) $achLine .= ' — ' . $ach['issuer'];
        if (!empty($ach['date'])) $achLine .= ' (' . $ach['date'] . ')';
        $pdf->x = $ml + 5; $pdf->writeLine($cw - 5, '- ' . $achLine);
    }
    $pdf->y += 2;
}

$pdf->output($r_name . ' - Resume.pdf');
