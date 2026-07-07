<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

// Fetch student & active plan details
$user_id = Auth::user_id();
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$student = $user_stmt->fetch();

$plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
$plan_stmt->execute([$student['current_plan'] ?? 'Starter Launch']);
$user_plan = $plan_stmt->fetch() ?: [
  'name' => 'Starter Launch',
  'access_paid_templates' => 0,
  'perm_ai_modify' => 0
];
$can_ai_modify = !empty($user_plan['perm_ai_modify']);
$beta_ai = Auth::beta_perm('perm_ai_modify');
if ($beta_ai !== null) $can_ai_modify = $beta_ai;

$resume_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$resume = null;

if ($resume_id) {
  // Intercept template switch POST
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'switch_template') {
    $new_template = trim($_POST['template'] ?? '');
    if (!empty($new_template)) {
      $upd = $db->prepare("UPDATE resumes SET template = ? WHERE id = ?");
      $upd->execute([$new_template, $resume_id]);
      echo '<script>window.location.href = "?page=preview_resume&id=' . $resume_id . '&template_switched=1";</script>';
      exit;
    }
  }

  $stmt = $db->prepare("SELECT * FROM resumes WHERE id = :id");
  $stmt->execute([':id' => $resume_id]);
  $resume = $stmt->fetch();
}

if (!$resume) {
  echo "Resume not found.";
  exit;
}

// Handle pending/generating status
$resume_status = $resume['status'] ?? 'completed';
if ($resume_status === 'pending' || $resume_status === 'generating') {
  echo '<!DOCTYPE html><html><head>';
  include __DIR__ . '/../components/common/head.php';
  echo '<title>Generating Resume — GreenLeaf</title></head><body class="bg-background font-body-md text-on-background min-h-screen flex items-center justify-center">';
  echo '<div class="text-center p-8">';
  echo '<div class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>';
  echo '<h2 class="font-headline-lg text-on-surface mb-2">Generating Your AI Resume</h2>';
  echo '<p class="text-on-surface-variant mb-4">This usually takes 10-20 seconds...</p>';
  echo '<p class="text-on-surface-variant/60 text-sm">You can safely leave this page. Your resume will appear in <a href="?page=resumes" class="text-primary hover:underline font-bold">My Resumes</a> when ready.</p>';
  echo '<script>setTimeout(() => window.location.reload(), 5000);</script>';
  echo '</div></body></html>';
  exit;
}

// Check if AI content exists and normalize field names
$has_ai = !empty($resume['ai_content']);
$ai_data = $has_ai ? json_decode($resume['ai_content'], true) : null;

if ($ai_data) {
  // Normalize skills: AI may return "name"/"level" or "skill_name"/"proficiency"
  if (isset($ai_data['skills'])) {
    $ai_data['skills'] = array_map(function($s) {
      return [
        'skill_name' => $s['skill_name'] ?? $s['name'] ?? '',
        'proficiency' => $s['proficiency'] ?? $s['level'] ?? '',
      ];
    }, $ai_data['skills']);
  }
  // Normalize experience: AI may return "position" or "job_title"
  if (isset($ai_data['experience'])) {
    $ai_data['experience'] = array_map(function($e) {
      return [
        'job_title' => $e['job_title'] ?? $e['position'] ?? '',
        'company' => $e['company'] ?? '',
        'location' => $e['location'] ?? '',
        'start_date' => $e['start_date'] ?? '',
        'end_date' => $e['end_date'] ?? '',
        'is_current' => $e['is_current'] ?? false,
        'description' => is_array($e['description'] ?? '') ? implode("\n", $e['description']) : ($e['description'] ?? ''),
      ];
    }, $ai_data['experience']);
  }
  // Normalize academics
  if (isset($ai_data['academics'])) {
    $ai_data['academics'] = array_map(function($a) {
      return [
        'degree' => $a['degree'] ?? '',
        'institution' => $a['institution'] ?? '',
        'board_university' => $a['board_university'] ?? '',
        'start_year' => $a['start_year'] ?? $a['graduation_year'] ?? '',
        'end_year' => $a['end_year'] ?? $a['graduation_year'] ?? '',
        'grade' => $a['grade'] ?? '',
        'description' => $a['description'] ?? '',
      ];
    }, $ai_data['academics']);
  }
  // Normalize projects
  if (isset($ai_data['projects'])) {
    $ai_data['projects'] = array_map(function($p) {
      return [
        'title' => $p['title'] ?? $p['name'] ?? '',
        'tech_stack' => $p['tech_stack'] ?? (is_array($p['technologies'] ?? null) ? implode(', ', $p['technologies']) : ($p['technologies'] ?? '')),
        'url' => $p['url'] ?? '',
        'start_date' => $p['start_date'] ?? '',
        'end_date' => $p['end_date'] ?? '',
        'description' => $p['description'] ?? '',
      ];
    }, $ai_data['projects']);
  }
  // Normalize achievements: may be strings or objects
  if (isset($ai_data['achievements'])) {
    $ai_data['achievements'] = array_map(function($a) {
      if (is_string($a)) {
        return ['title' => $a, 'issuer' => '', 'date' => '', 'description' => ''];
      }
      return [
        'title' => $a['title'] ?? '',
        'issuer' => $a['issuer'] ?? '',
        'date' => $a['date'] ?? '',
        'description' => $a['description'] ?? '',
      ];
    }, $ai_data['achievements']);
  }
}

// Fetch raw profile data for fallback
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

// Build unified data from AI or fallback
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
  $r_generated_at = $ai_data['generated_at'] ?? '';
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
    return [
      'job_title' => $e['job_title'] ?? '', 'company' => $e['company'] ?? '',
      'location' => $e['location'] ?? '', 'start_date' => $e['start_date'] ?? '',
      'end_date' => $e['end_date'] ?? '', 'is_current' => $e['is_current'] ?? 0,
      'description' => $e['description'] ?? '',
    ];
  }, $raw_experience);
  $r_academics = array_map(function($a) {
    return [
      'degree' => $a['degree'] ?? '', 'institution' => $a['institution'] ?? '',
      'board_university' => $a['board_university'] ?? '', 'start_year' => $a['start_year'] ?? '',
      'end_year' => $a['end_year'] ?? '', 'grade' => $a['grade'] ?? '',
      'description' => $a['description'] ?? '',
    ];
  }, $raw_academics);
  $r_skills = array_map(function($s) {
    return ['skill_name' => $s['skill_name'] ?? '', 'proficiency' => $s['proficiency'] ?? ''];
  }, $raw_skills);
  $r_projects = array_map(function($p) {
    return [
      'title' => $p['title'] ?? '', 'tech_stack' => $p['tech_stack'] ?? '',
      'url' => $p['url'] ?? '', 'start_date' => $p['start_date'] ?? '',
      'end_date' => $p['end_date'] ?? '', 'description' => $p['description'] ?? '',
    ];
  }, $raw_projects);
  $r_achievements = array_map(function($a) {
    return ['title' => $a['title'] ?? '', 'issuer' => $a['issuer'] ?? '', 'date' => $a['date'] ?? '', 'description' => $a['description'] ?? ''];
  }, $raw_achievements);
  $r_generated_at = '';
}

$template = $resume['template'] ?? 'Minimalist';

if (isset($_GET['standalone'])): ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($r_name) ?> — Resume</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,200&display=swap" rel="stylesheet">
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                "primary-fixed-dim": "#4edea3",
                "on-secondary": "#ffffff",
                "on-surface": "#151c27",
                "inverse-surface": "#2a313d",
                "on-tertiary-container": "#1b3f31",
                "secondary-fixed": "#b0f0d6",
                "surface": "#f9f9ff",
                "on-secondary-fixed": "#002117",
                "outline": "#6c7a71",
                "on-tertiary-fixed-variant": "#294e3f",
                "surface-container-high": "#e2e8f8",
                "on-surface-variant": "#3c4a42",
                "surface-bright": "#f9f9ff",
                "surface-tint": "#006c49",
                "on-primary": "#ffffff",
                "primary-fixed": "#6ffbbe",
                "tertiary": "#416656",
                "secondary-fixed-dim": "#95d3ba",
                "secondary-container": "#adedd3",
                "on-secondary": "#002117",
                "on-error": "#ffffff",
                "on-primary-fixed": "#002114",
                "on-secondary-fixed-variant": "#1b3c2e",
                "surface-container": "#edf3fe",
                "on-tertiary-fixed": "#001f16",
                "on-error-container": "#601410",
                "tertiary-fixed": "#bef1dd",
                "primary": "#006c49",
                "secondary": "#3c5b4c",
                "on-primary-container": "#002114",
                "on-inverse-surface": "#edf3fe",
                "error-container": "#ffdad6",
                "surface-container-lowest": "#ffffff",
                "on-background": "#191c20",
                "background": "#f9f9ff",
                "tertiary-fixed-dim": "#a3d4c1",
                "surface-container-low": "#f3f3fb",
                "primary-container": "#6ffbbe",
                "on-surface-fixed": "#151c27",
                "outline-variant": "#bfcec4",
                "on-primary-fixed-variant": "#003824",
                "surface-variant": "#dae5da"
            }
        }
    }
};
</script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { background: white; font-family: 'Inter', 'Segoe UI', sans-serif; padding: 0.5in; }
@media print { @page { margin: 0; } body { padding: 0; } }
</style>
</head>
<body>
<?php else:
include __DIR__ . '/../components/common/head.php';
?>
<title>Preview Resume — GreenLeaf Resume</title>
<style>
  .resume-paper { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
  @media print {
    .no-print { display: none !important; }
    .resume-paper { box-shadow: none; border: none; }
  }
  @keyframes spin-ai { to { transform: rotate(360deg); } }
  .ai-loading { animation: spin-ai 1s linear infinite; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>
<main class="md:ml-64 flex flex-col min-h-screen">
  <header class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-surface/80 backdrop-blur-md shadow-sm flex justify-between items-center px-6 md:px-16 py-4 no-print">
    <div class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-primary">
      <span class="material-symbols-outlined">energy_savings_leaf</span>
      <span>GreenLeaf Resume</span>
    </div>
    <div class="flex items-center gap-4">
      <button onclick="saveResume()" class="flex items-center gap-2 bg-primary text-on-primary font-label-md px-4 py-2 rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-sm">
        <span class="material-symbols-outlined text-sm">save</span> Save Resume
      </button>
      <button onclick="downloadResume()" class="flex items-center gap-2 bg-secondary-container text-on-secondary-container font-label-md px-4 py-2 rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-sm">
        <span class="material-symbols-outlined text-sm">download</span> Download PDF
      </button>
      <a href="?page=resumes" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md">
        <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Resumes
      </a>
    </div>
  </header>
  <div class="mt-24 px-6 md:px-16 pb-16 flex-1 flex flex-col items-center">
    <?php if (isset($_GET['template_switched'])): ?>
        <div class="w-full max-w-4xl mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in shadow border border-primary/10 no-print">
          <span class="material-symbols-outlined text-sm">palette</span>
          <span class="font-label-md text-xs font-bold">Resume template updated successfully!</span>
        </div>
    <?php endif; ?>
    <div class="w-full max-w-4xl bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/30 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 no-print">
      <div>
        <h2 class="font-headline-md text-lg text-on-surface font-bold">Resume Preview: <?= htmlspecialchars($resume['job_profile']) ?></h2>
        <p class="text-on-surface-variant text-sm mt-1">
          Template: <span class="bg-primary-container text-on-primary-container font-bold text-xs px-2.5 py-0.5 rounded-full uppercase ml-1"><?= htmlspecialchars($template) ?></span>
          <?php if ($has_ai): ?>
            <span class="bg-emerald-100 text-emerald-800 font-bold text-xs px-2.5 py-0.5 rounded-full uppercase ml-1">AI Tailored</span>
          <?php endif; ?>
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button onclick="openSwitchTemplateModal()" class="flex items-center gap-2 bg-secondary-container text-on-secondary-container hover:opacity-90 px-4 py-2.5 rounded-xl font-label-md text-xs font-bold transition-all active:scale-95 border border-primary/10">
          <span class="material-symbols-outlined text-xs">palette</span> Switch Template
        </button>
        <a href="?page=profile" class="flex items-center gap-2 border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary px-4 py-2.5 rounded-xl font-label-md text-xs font-semibold transition-all active:scale-95">
          <span class="material-symbols-outlined text-xs">edit</span> Edit Profile Data
        </a>
      </div>
    </div>
<?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- TEMPLATE: MINIMALIST                                   -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <?php if ($template === 'Minimalist'): ?>
    <div id="resume-paper" class="w-full max-w-4xl bg-white text-gray-900 px-16 py-14 rounded-sm resume-paper flex flex-col gap-7 min-h-[297mm]" style="font-family: 'Inter', 'Segoe UI', sans-serif;">
      
      <header class="text-center border-b border-gray-300 pb-6">
        <h1 id="r-name" class="text-3xl font-light tracking-[0.15em] uppercase text-gray-900"><?= htmlspecialchars($r_name) ?></h1>
        <p id="r-jobtitle" class="text-sm tracking-[0.3em] uppercase text-emerald-700 mt-2 font-medium"><?= htmlspecialchars($r_jobtitle) ?></p>
        <div id="r-contact" class="flex flex-wrap justify-center gap-x-5 gap-y-1 mt-3 text-xs text-gray-500">
          <?php if (!empty($r_email)): ?><span><?= htmlspecialchars($r_email) ?></span><?php endif; ?>
          <?php if (!empty($r_phone)): ?><span><?= htmlspecialchars($r_phone) ?></span><?php endif; ?>
          <?php if (!empty($r_location)): ?><span><?= htmlspecialchars($r_location) ?></span><?php endif; ?>
        </div>
        <div id="r-socials" class="flex flex-wrap justify-center gap-4 text-xs mt-2">
          <?php if (!empty($r_linkedin)): ?><a href="<?= htmlspecialchars($r_linkedin) ?>" target="_blank" class="text-emerald-700 hover:underline">LinkedIn</a><?php endif; ?>
          <?php if (!empty($r_github)): ?><a href="<?= htmlspecialchars($r_github) ?>" target="_blank" class="text-emerald-700 hover:underline">GitHub</a><?php endif; ?>
          <?php if (!empty($r_portfolio)): ?><a href="<?= htmlspecialchars($r_portfolio) ?>" target="_blank" class="text-emerald-700 hover:underline">Portfolio</a><?php endif; ?>
        </div>
      </header>

      <?php if (!empty($r_summary)): ?>
      <section>
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-2 border-b border-gray-100 pb-1">Summary</h3>
        <p id="r-summary" class="text-xs leading-relaxed text-gray-600"><?= htmlspecialchars($r_summary) ?></p>
      </section>
      <?php endif; ?>

      <?php if (!empty($r_experience)): ?>
      <section>
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-3 border-b border-gray-100 pb-1">Experience</h3>
        <div id="r-experience" class="flex flex-col gap-4">
          <?php foreach ($r_experience as $exp): ?>
          <div>
            <div class="flex justify-between items-baseline">
              <h4 class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($exp['job_title']) ?></h4>
              <span class="text-[10px] text-gray-400"><?= htmlspecialchars($exp['start_date']) ?> – <?= ($exp['is_current'] ?? false) ? 'Present' : htmlspecialchars($exp['end_date'] ?? '') ?></span>
            </div>
            <p class="text-xs text-emerald-700 font-medium"><?= htmlspecialchars($exp['company']) ?><?= !empty($exp['location']) ? ' · ' . htmlspecialchars($exp['location']) : '' ?></p>
            <?php if (!empty($exp['description'])): ?>
            <p class="text-[11px] text-gray-500 leading-relaxed mt-1"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($r_academics)): ?>
      <section>
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-3 border-b border-gray-100 pb-1">Education</h3>
        <div id="r-academics" class="flex flex-col gap-3">
          <?php foreach ($r_academics as $acad): ?>
          <div>
            <div class="flex justify-between items-baseline">
              <h4 class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($acad['degree']) ?></h4>
              <span class="text-[10px] text-gray-400"><?= htmlspecialchars($acad['start_year']) ?> – <?= htmlspecialchars($acad['end_year']) ?></span>
            </div>
            <p class="text-xs text-gray-600"><?= htmlspecialchars($acad['institution']) ?></p>
            <?php if (!empty($acad['grade'])): ?><p class="text-[10px] text-gray-400 mt-0.5">Grade: <?= htmlspecialchars($acad['grade']) ?></p><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($r_skills)): ?>
      <section>
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-2 border-b border-gray-100 pb-1">Skills</h3>
        <div id="r-skills" class="flex flex-wrap gap-1.5">
          <?php foreach ($r_skills as $s): ?>
          <span class="px-2 py-0.5 bg-gray-50 border border-gray-200 rounded text-[10px] text-gray-700"><?= htmlspecialchars($s['skill_name']) ?></span>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($r_projects)): ?>
      <section>
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-3 border-b border-gray-100 pb-1">Projects</h3>
        <div id="r-projects" class="flex flex-col gap-3">
          <?php foreach ($r_projects as $p): ?>
          <div>
            <h4 class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($p['title']) ?></h4>
            <p class="text-[10px] text-emerald-700 font-medium"><?= htmlspecialchars($p['tech_stack']) ?></p>
            <?php if (!empty($p['description'])): ?>
            <p class="text-[11px] text-gray-500 leading-relaxed mt-1"><?= htmlspecialchars($p['description']) ?></p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($r_achievements)): ?>
      <section>
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-3 border-b border-gray-100 pb-1">Achievements</h3>
        <div id="r-achievements" class="flex flex-col gap-2">
          <?php foreach ($r_achievements as $a): ?>
          <div class="flex items-start gap-2">
            <span class="text-emerald-600 mt-0.5">&#9670;</span>
            <div>
              <h4 class="text-xs font-semibold text-gray-900"><?= htmlspecialchars($a['title']) ?><?php if (!empty($a['issuer'])): ?> <span class="font-normal text-gray-400">— <?= htmlspecialchars($a['issuer']) ?></span><?php endif; ?></h4>
              <?php if (!empty($a['date'])): ?><p class="text-[10px] text-gray-400"><?= htmlspecialchars($a['date']) ?></p><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- TEMPLATE: STANDARD MODERN                              -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <?php elseif ($template === 'Standard Modern'): ?>
    <div id="resume-paper" class="w-full max-w-4xl bg-white text-gray-900 rounded-sm resume-paper flex min-h-[297mm] overflow-hidden" style="font-family: 'Inter', 'Segoe UI', sans-serif;">
      
      <!-- Left Sidebar -->
      <aside class="w-[35%] bg-emerald-800 text-white p-8 flex flex-col gap-6">
        <div>
          <h1 id="r-name" class="text-xl font-bold leading-tight"><?= htmlspecialchars($r_name) ?></h1>
          <p class="text-[10px] uppercase tracking-[0.3em] text-emerald-200 mt-2"><?= htmlspecialchars($r_jobtitle) ?></p>
        </div>

        <div class="flex flex-col gap-1 text-[11px] text-emerald-100">
          <?php if (!empty($r_phone)): ?><div class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">call</span> <?= htmlspecialchars($r_phone) ?></div><?php endif; ?>
          <?php if (!empty($r_email)): ?><div class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">mail</span> <?= htmlspecialchars($r_email) ?></div><?php endif; ?>
          <?php if (!empty($r_location)): ?><div class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">location_on</span> <?= htmlspecialchars($r_location) ?></div><?php endif; ?>
          <?php if (!empty($r_linkedin)): ?><div class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">link</span> <a href="<?= htmlspecialchars($r_linkedin) ?>" target="_blank" class="hover:underline">LinkedIn</a></div><?php endif; ?>
          <?php if (!empty($r_github)): ?><div class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">code</span> <a href="<?= htmlspecialchars($r_github) ?>" target="_blank" class="hover:underline">GitHub</a></div><?php endif; ?>
          <?php if (!empty($r_portfolio)): ?><div class="flex items-center gap-2"><span class="material-symbols-outlined text-xs">language</span> <a href="<?= htmlspecialchars($r_portfolio) ?>" target="_blank" class="hover:underline">Portfolio</a></div><?php endif; ?>
        </div>

        <?php if (!empty($r_skills)): ?>
        <div>
          <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300 mb-2 border-b border-emerald-600 pb-1">Skills</h3>
          <div id="r-skills" class="flex flex-wrap gap-1">
            <?php foreach ($r_skills as $s): ?>
            <span class="px-2 py-0.5 bg-emerald-700/50 rounded text-[10px] text-white"><?= htmlspecialchars($s['skill_name']) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($r_achievements)): ?>
        <div>
          <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300 mb-2 border-b border-emerald-600 pb-1">Achievements</h3>
          <div id="r-achievements" class="flex flex-col gap-2">
            <?php foreach ($r_achievements as $a): ?>
            <div>
              <h4 class="text-[11px] font-semibold text-white"><?= htmlspecialchars($a['title']) ?></h4>
              <?php if (!empty($a['issuer'])): ?><p class="text-[9px] text-emerald-200"><?= htmlspecialchars($a['issuer']) ?></p><?php endif; ?>
              <?php if (!empty($a['date'])): ?><p class="text-[9px] text-emerald-300"><?= htmlspecialchars($a['date']) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </aside>

      <!-- Right Main Content -->
      <div class="flex-1 p-8 flex flex-col gap-6">

        <?php if (!empty($r_summary)): ?>
        <section>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-800 mb-2 border-b-2 border-emerald-200 pb-1">Professional Summary</h3>
          <p id="r-summary" class="text-[11px] leading-relaxed text-gray-600"><?= htmlspecialchars($r_summary) ?></p>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_experience)): ?>
        <section>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-800 mb-3 border-b-2 border-emerald-200 pb-1">Work Experience</h3>
          <div id="r-experience" class="flex flex-col gap-4">
            <?php foreach ($r_experience as $exp): ?>
            <div>
              <div class="flex justify-between items-baseline">
                <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($exp['job_title']) ?></h4>
                <span class="text-[10px] text-gray-400 font-medium"><?= htmlspecialchars($exp['start_date']) ?> – <?= ($exp['is_current'] ?? false) ? 'Present' : htmlspecialchars($exp['end_date'] ?? '') ?></span>
              </div>
              <p class="text-xs text-emerald-700 font-semibold"><?= htmlspecialchars($exp['company']) ?><?= !empty($exp['location']) ? ' | ' . htmlspecialchars($exp['location']) : '' ?></p>
              <?php if (!empty($exp['description'])): ?>
              <p class="text-[11px] text-gray-500 leading-relaxed mt-1"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_academics)): ?>
        <section>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-800 mb-3 border-b-2 border-emerald-200 pb-1">Education</h3>
          <div id="r-academics" class="flex flex-col gap-3">
            <?php foreach ($r_academics as $acad): ?>
            <div>
              <div class="flex justify-between items-baseline">
                <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($acad['degree']) ?></h4>
                <span class="text-[10px] text-gray-400"><?= htmlspecialchars($acad['start_year']) ?> – <?= htmlspecialchars($acad['end_year']) ?></span>
              </div>
              <p class="text-xs text-gray-600"><?= htmlspecialchars($acad['institution']) ?></p>
              <?php if (!empty($acad['grade'])): ?><p class="text-[10px] text-gray-400">Grade: <?= htmlspecialchars($acad['grade']) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_projects)): ?>
        <section>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-800 mb-3 border-b-2 border-emerald-200 pb-1">Projects</h3>
          <div id="r-projects" class="flex flex-col gap-3">
            <?php foreach ($r_projects as $p): ?>
            <div>
              <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['title']) ?></h4>
              <p class="text-[10px] text-emerald-700 font-semibold"><?= htmlspecialchars($p['tech_stack']) ?></p>
              <?php if (!empty($p['description'])): ?>
              <p class="text-[11px] text-gray-500 leading-relaxed mt-1"><?= htmlspecialchars($p['description']) ?></p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- TEMPLATE: CREATIVE LEAF                               -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <?php else: ?>
    <div id="resume-paper" class="w-full max-w-4xl bg-white text-gray-900 rounded-sm resume-paper flex flex-col min-h-[297mm] overflow-hidden" style="font-family: 'Inter', 'Segoe UI', sans-serif;">
      
      <!-- Header Banner -->
      <header class="bg-gradient-to-r from-emerald-700 to-emerald-500 text-white px-12 py-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-bl-full"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-tr-full"></div>
        <div class="relative">
          <h1 id="r-name" class="text-3xl font-bold"><?= htmlspecialchars($r_name) ?></h1>
          <p id="r-jobtitle" class="text-sm uppercase tracking-[0.3em] text-emerald-100 mt-1"><?= htmlspecialchars($r_jobtitle) ?></p>
          <div id="r-contact" class="flex flex-wrap gap-4 mt-3 text-[11px] text-emerald-100">
            <?php if (!empty($r_email)): ?><div class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">mail</span> <?= htmlspecialchars($r_email) ?></div><?php endif; ?>
            <?php if (!empty($r_phone)): ?><div class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">call</span> <?= htmlspecialchars($r_phone) ?></div><?php endif; ?>
            <?php if (!empty($r_location)): ?><div class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">location_on</span> <?= htmlspecialchars($r_location) ?></div><?php endif; ?>
          </div>
          <div id="r-socials" class="flex gap-3 mt-2 text-[10px]">
            <?php if (!empty($r_linkedin)): ?><a href="<?= htmlspecialchars($r_linkedin) ?>" target="_blank" class="text-white/80 hover:text-white underline">LinkedIn</a><?php endif; ?>
            <?php if (!empty($r_github)): ?><a href="<?= htmlspecialchars($r_github) ?>" target="_blank" class="text-white/80 hover:text-white underline">GitHub</a><?php endif; ?>
            <?php if (!empty($r_portfolio)): ?><a href="<?= htmlspecialchars($r_portfolio) ?>" target="_blank" class="text-white/80 hover:text-white underline">Portfolio</a><?php endif; ?>
          </div>
        </div>
      </header>

      <div class="px-12 py-8 flex flex-col gap-6">

        <?php if (!empty($r_summary)): ?>
        <section>
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-0.5 bg-emerald-500 rounded-full"></div>
            <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Professional Summary</h3>
          </div>
          <p id="r-summary" class="text-[11px] leading-relaxed text-gray-600 pl-10"><?= htmlspecialchars($r_summary) ?></p>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_experience)): ?>
        <section>
          <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-0.5 bg-emerald-500 rounded-full"></div>
            <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Work Experience</h3>
          </div>
          <div id="r-experience" class="flex flex-col gap-4 pl-10">
            <?php foreach ($r_experience as $exp): ?>
            <div class="relative pl-4 border-l-2 border-emerald-200">
              <div class="absolute left-[-5px] top-1 w-2 h-2 bg-emerald-500 rounded-full"></div>
              <div class="flex justify-between items-baseline">
                <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($exp['job_title']) ?></h4>
                <span class="text-[10px] text-gray-400"><?= htmlspecialchars($exp['start_date']) ?> – <?= ($exp['is_current'] ?? false) ? 'Present' : htmlspecialchars($exp['end_date'] ?? '') ?></span>
              </div>
              <p class="text-xs text-emerald-700 font-semibold"><?= htmlspecialchars($exp['company']) ?><?= !empty($exp['location']) ? ' · ' . htmlspecialchars($exp['location']) : '' ?></p>
              <?php if (!empty($exp['description'])): ?>
              <p class="text-[11px] text-gray-500 leading-relaxed mt-1"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_academics)): ?>
        <section>
          <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-0.5 bg-emerald-500 rounded-full"></div>
            <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Education</h3>
          </div>
          <div id="r-academics" class="flex flex-col gap-3 pl-10">
            <?php foreach ($r_academics as $acad): ?>
            <div>
              <div class="flex justify-between items-baseline">
                <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($acad['degree']) ?></h4>
                <span class="text-[10px] text-gray-400"><?= htmlspecialchars($acad['start_year']) ?> – <?= htmlspecialchars($acad['end_year']) ?></span>
              </div>
              <p class="text-xs text-gray-600"><?= htmlspecialchars($acad['institution']) ?></p>
              <?php if (!empty($acad['grade'])): ?><p class="text-[10px] text-gray-400">Grade: <?= htmlspecialchars($acad['grade']) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <!-- Skills as pills row -->
        <?php if (!empty($r_skills)): ?>
        <section>
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-0.5 bg-emerald-500 rounded-full"></div>
            <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Skills</h3>
          </div>
          <div id="r-skills" class="flex flex-wrap gap-1.5 pl-10">
            <?php foreach ($r_skills as $s): ?>
            <span class="px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-full text-[10px] font-semibold text-emerald-800"><?= htmlspecialchars($s['skill_name']) ?></span>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_projects)): ?>
        <section>
          <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-0.5 bg-emerald-500 rounded-full"></div>
            <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Projects</h3>
          </div>
          <div id="r-projects" class="grid grid-cols-1 gap-3 pl-10">
            <?php foreach ($r_projects as $p): ?>
            <div class="bg-emerald-50/50 rounded-lg p-3 border border-emerald-100">
              <h4 class="text-sm font-bold text-gray-900"><?= htmlspecialchars($p['title']) ?></h4>
              <p class="text-[10px] text-emerald-700 font-semibold mt-0.5"><?= htmlspecialchars($p['tech_stack']) ?></p>
              <?php if (!empty($p['description'])): ?>
              <p class="text-[11px] text-gray-500 leading-relaxed mt-1"><?= htmlspecialchars($p['description']) ?></p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($r_achievements)): ?>
        <section>
          <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-0.5 bg-emerald-500 rounded-full"></div>
            <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Achievements</h3>
          </div>
          <div id="r-achievements" class="flex flex-col gap-2 pl-10">
            <?php foreach ($r_achievements as $a): ?>
            <div class="flex items-start gap-2">
              <span class="material-symbols-outlined text-emerald-500 text-[14px] mt-0.5">emoji_events</span>
              <div>
                <h4 class="text-xs font-semibold text-gray-900"><?= htmlspecialchars($a['title']) ?><?php if (!empty($a['issuer'])): ?> <span class="font-normal text-gray-400">— <?= htmlspecialchars($a['issuer']) ?></span><?php endif; ?></h4>
                <?php if (!empty($a['date'])): ?><p class="text-[10px] text-gray-400"><?= htmlspecialchars($a['date']) ?></p><?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

      </div>
    </div>
    <?php endif; ?>

<?php if (!isset($_GET['standalone'])): ?>
  </div>
  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>
<?php endif; ?><!-- end dashboard wrap -->

<?php if (!isset($_GET['standalone'])): ?>
<script>
function saveResume() {
  fetch("/api/save_resume.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ job_profile: "<?= htmlspecialchars($resume['job_profile']) ?>", template: "<?= htmlspecialchars($resume['template']) ?>" })
  })
  .then(r => r.json())
  .then(data => {
    let toast = document.createElement('div');
    toast.className = 'fixed bottom-6 left-1/2 transform -translate-x-1/2 z-[200] bg-inverse-surface text-inverse-on-surface px-6 py-3.5 rounded-xl shadow-2xl flex items-center gap-2 text-sm font-semibold transition-all duration-300';
    toast.innerHTML = '<span class="material-symbols-outlined text-primary text-base">check_circle</span> <span>' + (data.message || 'Saved') + '</span>';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  })
  .catch(() => alert("An error occurred while saving."));
}
</script>

<!-- AI MODIFY CHAT PANEL -->
<?php if ($can_ai_modify): ?>
<div id="ai-chat-overlay" class="fixed inset-0 bg-on-surface/30 backdrop-blur-sm z-[90] hidden no-print transition-opacity" onclick="closeAIChat()"></div>
<div id="ai-chat-panel" class="fixed top-0 right-0 h-full w-full max-w-md bg-surface-container-lowest z-[95] hidden no-print flex flex-col shadow-2xl border-l border-outline-variant/30 transition-transform transform translate-x-full">
  
  <!-- Chat Header -->
  <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/20 bg-gradient-to-r from-primary/5 to-transparent">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-primary/15 flex items-center justify-center">
        <span class="material-symbols-outlined text-primary text-xl">psychology</span>
      </div>
      <div>
        <h3 class="font-headline-sm text-sm font-bold text-on-surface">Resume AI Agent</h3>
        <p class="text-[10px] text-on-surface-variant font-semibold" id="ai-chat-credits"><?= $student['ai_credits'] ?> credits remaining</p>
      </div>
    </div>
    <button onclick="closeAIChat()" class="w-9 h-9 rounded-full hover:bg-surface-variant/40 flex items-center justify-center transition-colors">
      <span class="material-symbols-outlined text-lg">close</span>
    </button>
  </div>

  <!-- Chat Messages -->
  <div id="ai-chat-messages" class="flex-1 overflow-y-auto px-5 py-4 flex flex-col gap-4 scroll-smooth">
    <!-- Welcome message -->
    <div class="flex items-start gap-3 animate-fade-in">
      <div class="w-8 h-8 rounded-lg bg-primary/15 flex items-center justify-center flex-shrink-0 mt-1">
        <span class="material-symbols-outlined text-primary text-sm">smart_toy</span>
      </div>
      <div class="bg-surface-container rounded-2xl rounded-tl-md px-4 py-3 max-w-[85%] shadow-sm">
        <p class="text-xs text-on-surface leading-relaxed">Hi! I'm your resume AI agent. I can help you modify your resume content. Just tell me what you'd like to change.</p>
      </div>
    </div>

    <!-- Suggested prompts -->
    <div class="flex flex-wrap gap-2 pl-11 animate-fade-in" id="ai-suggested-prompts">
      <button onclick="sendSuggestedPrompt('Make my summary more impactful and concise')" class="text-[10px] font-semibold px-3 py-1.5 rounded-full border border-primary/30 text-primary hover:bg-primary/10 transition-all active:scale-95">Improve summary</button>
      <button onclick="sendSuggestedPrompt('Reorder my skills to highlight frontend technologies first')" class="text-[10px] font-semibold px-3 py-1.5 rounded-full border border-primary/30 text-primary hover:bg-primary/10 transition-all active:scale-95">Prioritize skills</button>
      <button onclick="sendSuggestedPrompt('Make my experience descriptions more action-oriented with strong verbs')" class="text-[10px] font-semibold px-3 py-1.5 rounded-full border border-primary/30 text-primary hover:bg-primary/10 transition-all active:scale-95">Strengthen bullets</button>
      <button onclick="sendSuggestedPrompt('Add quantified achievements and metrics where possible')" class="text-[10px] font-semibold px-3 py-1.5 rounded-full border border-primary/30 text-primary hover:bg-primary/10 transition-all active:scale-95">Add metrics</button>
      <button onclick="sendSuggestedPrompt('Make the tone more professional and authoritative')" class="text-[10px] font-semibold px-3 py-1.5 rounded-full border border-primary/30 text-primary hover:bg-primary/10 transition-all active:scale-95">Professional tone</button>
      <button onclick="sendSuggestedPrompt('Tailor my resume for a senior full stack developer role')" class="text-[10px] font-semibold px-3 py-1.5 rounded-full border border-primary/30 text-primary hover:bg-primary/10 transition-all active:scale-95">Tailor for role</button>
    </div>
  </div>

  <!-- Chat Input -->
  <div class="border-t border-outline-variant/20 px-5 py-4 bg-surface/50">
    <div class="flex items-end gap-3">
      <div class="flex-1 relative">
        <textarea id="ai-chat-input" rows="2" placeholder="e.g. Highlight my cloud architecture experience..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all pr-10" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendAIChatMessage()}"></textarea>
        <button onclick="startVoiceInput()" id="ai-voice-btn" class="absolute right-3 bottom-3 text-on-surface-variant hover:text-error transition-colors" title="Voice input">
          <span class="material-symbols-outlined text-lg">mic</span>
        </button>
      </div>
      <button onclick="sendAIChatMessage()" id="ai-send-btn" class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-md flex-shrink-0 disabled:opacity-50">
        <span class="material-symbols-outlined text-lg">send</span>
      </button>
    </div>
    <div id="ai-voice-status" class="hidden mt-2 flex items-center gap-2 text-[10px] text-error font-semibold animate-pulse">
      <div class="w-2 h-2 bg-error rounded-full animate-ping"></div>
      <span>Listening... speak now</span>
    </div>
  </div>
</div>

<!-- AI Chat FAB -->
<button onclick="openAIChat()" id="ai-chat-fab" class="fixed bottom-6 right-6 z-[85] no-print bg-primary text-on-primary px-6 py-4 rounded-full shadow-[0_10px_25px_rgba(0,108,73,0.4)] hover:scale-105 active:scale-95 transition-all flex items-center gap-3 group">
  <span class="material-symbols-outlined animate-pulse">auto_awesome</span>
  <span class="font-headline-md text-lg font-bold">Modify with AI</span>
</button>

<script>
  const resumeId = <?= $resume_id ?>;
  let creditsRemaining = <?= $student['ai_credits'] ?>;
  let chatHistory = [];
  let isProcessing = false;
  let recognition = null;

  // Speech Recognition
  if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SR();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    recognition.onresult = (e) => {
      document.getElementById('ai-chat-input').value = e.results[0][0].transcript;
      document.getElementById('ai-voice-status').classList.add('hidden');
      sendAIChatMessage();
    };
    recognition.onerror = () => document.getElementById('ai-voice-status').classList.add('hidden');
    recognition.onend = () => document.getElementById('ai-voice-status').classList.add('hidden');
  }

  function startVoiceInput() {
    if (!recognition) { alert('Voice input is not supported in this browser.'); return; }
    document.getElementById('ai-voice-status').classList.remove('hidden');
    recognition.start();
  }

  function downloadResume() {
    var opt = { margin: 0, filename: 'resume.pdf', html2canvas: { scale: 2, useCORS: true, logging: false }, jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' } };
    html2pdf().set(opt).from(document.querySelector('.resume-paper')).save();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('ai') === '1') setTimeout(() => openAIChat(), 300);
  });

  function openAIChat() {
    const panel = document.getElementById('ai-chat-panel');
    const overlay = document.getElementById('ai-chat-overlay');
    const fab = document.getElementById('ai-chat-fab');
    panel.classList.remove('hidden');
    overlay.classList.remove('hidden');
    fab.classList.add('hidden');
    setTimeout(() => {
      panel.classList.remove('translate-x-full');
      panel.classList.add('translate-x-0');
    }, 10);
    document.getElementById('ai-chat-input').focus();
  }

  function closeAIChat() {
    const panel = document.getElementById('ai-chat-panel');
    const overlay = document.getElementById('ai-chat-overlay');
    const fab = document.getElementById('ai-chat-fab');
    panel.classList.remove('translate-x-0');
    panel.classList.add('translate-x-full');
    overlay.classList.add('hidden');
    setTimeout(() => { panel.classList.add('hidden'); fab.classList.remove('hidden'); }, 300);
  }

  function sendSuggestedPrompt(text) {
    document.getElementById('ai-chat-input').value = text;
    sendAIChatMessage();
  }

  function addChatBubble(text, role) {
    const container = document.getElementById('ai-chat-messages');
    const isUser = role === 'user';
    const bubble = document.createElement('div');
    bubble.className = `flex items-start gap-3 animate-fade-in ${isUser ? 'flex-row-reverse' : ''}`;
    
    const avatarClass = isUser ? 'bg-secondary/15' : 'bg-primary/15';
    const avatarIcon = isUser ? 'person' : 'smart_toy';
    const avatarColor = isUser ? 'text-secondary' : 'text-primary';
    const bubbleClass = isUser 
      ? 'bg-primary text-on-primary rounded-2xl rounded-tr-md' 
      : 'bg-surface-container text-on-surface rounded-2xl rounded-tl-md shadow-sm';
    
    bubble.innerHTML = `
      <div class="w-8 h-8 rounded-lg ${avatarClass} flex items-center justify-center flex-shrink-0 mt-1">
        <span class="material-symbols-outlined ${avatarColor} text-sm">${avatarIcon}</span>
      </div>
      <div class="${bubbleClass} px-4 py-3 max-w-[80%]">
        <p class="text-xs leading-relaxed whitespace-pre-wrap">${escapeHtml(text)}</p>
      </div>
    `;
    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
    return bubble;
  }

  function addLoadingBubble() {
    const container = document.getElementById('ai-chat-messages');
    const loading = document.createElement('div');
    loading.id = 'ai-loading-bubble';
    loading.className = 'flex items-start gap-3 animate-fade-in';
    loading.innerHTML = `
      <div class="w-8 h-8 rounded-lg bg-primary/15 flex items-center justify-center flex-shrink-0 mt-1">
        <span class="material-symbols-outlined text-primary text-sm">smart_toy</span>
      </div>
      <div class="bg-surface-container rounded-2xl rounded-tl-md px-4 py-3 shadow-sm">
        <div class="flex items-center gap-1.5">
          <div class="w-2 h-2 bg-primary/60 rounded-full animate-bounce" style="animation-delay:0ms"></div>
          <div class="w-2 h-2 bg-primary/60 rounded-full animate-bounce" style="animation-delay:150ms"></div>
          <div class="w-2 h-2 bg-primary/60 rounded-full animate-bounce" style="animation-delay:300ms"></div>
        </div>
      </div>
    `;
    container.appendChild(loading);
    container.scrollTop = container.scrollHeight;
  }

  function removeLoadingBubble() {
    const el = document.getElementById('ai-loading-bubble');
    if (el) el.remove();
  }

  async function sendAIChatMessage() {
    const input = document.getElementById('ai-chat-input');
    const text = input.value.trim();
    if (!text || isProcessing) return;
    if (creditsRemaining <= 0) { alert('No AI credits remaining. Please upgrade your plan.'); return; }

    isProcessing = true;
    input.value = '';
    document.getElementById('ai-send-btn').disabled = true;

    // Hide suggested prompts after first message
    const prompts = document.getElementById('ai-suggested-prompts');
    if (prompts) prompts.style.display = 'none';

    addChatBubble(text, 'user');
    chatHistory.push({ role: 'user', content: text });

    addLoadingBubble();

    try {
      const res = await fetch('/api/ai_modify.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          resume_id: resumeId,
          instruction: text,
          history: chatHistory
        })
      });
      const data = await res.json();
      removeLoadingBubble();

      if (data.success) {
        creditsRemaining = data.credits_remaining;
        document.getElementById('ai-chat-credits').textContent = creditsRemaining + ' credits remaining';
        
        addChatBubble('Done! I\'ve updated your resume with the requested changes.', 'assistant');
        chatHistory.push({ role: 'assistant', content: 'Resume updated successfully.' });

        updateResumeDisplay(data.ai_content);
      } else {
        const errMsg = data.error || 'Something went wrong';
        addChatBubble(errMsg, 'assistant');
        chatHistory.push({ role: 'assistant', content: errMsg });
        if (data.upgrade_required) {
          setTimeout(() => { if (confirm(errMsg + '\n\nGo to plan page?')) window.location.href = '?page=plan'; }, 500);
        }
      }
    } catch (e) {
      removeLoadingBubble();
      addChatBubble('Connection error: ' + e.message, 'assistant');
    } finally {
      isProcessing = false;
      document.getElementById('ai-send-btn').disabled = false;
      input.focus();
    }
  }

  function updateResumeDisplay(data) {
    if (data.header) {
      if (data.header.full_name) { const el = document.getElementById('r-name'); if (el) el.textContent = data.header.full_name; }
      if (data.header.job_title) { const el = document.getElementById('r-jobtitle'); if (el) el.textContent = data.header.job_title; }
      // Update contact info
      const contactEl = document.getElementById('r-contact');
      if (contactEl && data.header) {
        let contactHtml = '';
        if (data.header.email) contactHtml += '<span>' + escapeHtml(data.header.email) + '</span>';
        if (data.header.phone) contactHtml += '<span>' + escapeHtml(data.header.phone) + '</span>';
        if (data.header.location) contactHtml += '<span>' + escapeHtml(data.header.location) + '</span>';
        contactEl.innerHTML = contactHtml;
      }
      // Update socials
      const socialsEl = document.getElementById('r-socials');
      if (socialsEl && data.header) {
        let socialsHtml = '';
        if (data.header.linkedin) socialsHtml += '<a href="' + escapeHtml(data.header.linkedin) + '" target="_blank" class="text-emerald-700 hover:underline">LinkedIn</a>';
        if (data.header.github) socialsHtml += '<a href="' + escapeHtml(data.header.github) + '" target="_blank" class="text-emerald-700 hover:underline">GitHub</a>';
        if (data.header.portfolio) socialsHtml += '<a href="' + escapeHtml(data.header.portfolio) + '" target="_blank" class="text-emerald-700 hover:underline">Portfolio</a>';
        socialsEl.innerHTML = socialsHtml;
      }
    }
    if (data.summary) { const el = document.getElementById('r-summary'); if (el) el.textContent = data.summary; }
    if (data.skills) {
      const el = document.getElementById('r-skills');
      if (el) el.innerHTML = data.skills.map(s =>
        '<span class="px-2 py-0.5 bg-gray-50 border border-gray-200 rounded text-[10px] text-gray-700">' + escapeHtml(s.skill_name) + '</span>'
      ).join('');
    }
    if (data.experience) {
      const el = document.getElementById('r-experience');
      if (el) el.innerHTML = data.experience.map(exp => {
        const end = exp.is_current ? 'Present' : escapeHtml(exp.end_date || '');
        const loc = exp.location ? ' · ' + escapeHtml(exp.location) : '';
        const desc = exp.description ? '<p class="text-[11px] text-gray-500 leading-relaxed mt-1">' + escapeHtml(exp.description).replace(/\n/g, '<br>') + '</p>' : '';
        return '<div><div class="flex justify-between items-baseline"><h4 class="text-sm font-semibold text-gray-900">' + escapeHtml(exp.job_title) + '</h4><span class="text-[10px] text-gray-400">' + escapeHtml(exp.start_date) + ' – ' + end + '</span></div><p class="text-xs text-emerald-700 font-medium">' + escapeHtml(exp.company) + loc + '</p>' + desc + '</div>';
      }).join('');
    }
    if (data.academics) {
      const el = document.getElementById('r-academics');
      if (el) el.innerHTML = data.academics.map(a => {
        const grade = a.grade ? '<p class="text-[10px] text-gray-400 mt-0.5">Grade: ' + escapeHtml(a.grade) + '</p>' : '';
        return '<div><div class="flex justify-between items-baseline"><h4 class="text-sm font-semibold text-gray-900">' + escapeHtml(a.degree) + '</h4><span class="text-[10px] text-gray-400">' + escapeHtml(a.start_year) + ' – ' + escapeHtml(a.end_year) + '</span></div><p class="text-xs text-gray-600">' + escapeHtml(a.institution) + '</p>' + grade + '</div>';
      }).join('');
    }
    if (data.projects) {
      const el = document.getElementById('r-projects');
      if (el) el.innerHTML = data.projects.map(p => {
        const desc = p.description ? '<p class="text-[11px] text-gray-500 leading-relaxed mt-1">' + escapeHtml(p.description).replace(/\n/g, '<br>') + '</p>' : '';
        return '<div><h4 class="text-sm font-semibold text-gray-900">' + escapeHtml(p.title) + '</h4><p class="text-[10px] text-emerald-700 font-medium">' + escapeHtml(p.tech_stack || '') + '</p>' + desc + '</div>';
      }).join('');
    }
    if (data.achievements) {
      const el = document.getElementById('r-achievements');
      if (el) el.innerHTML = data.achievements.map(a => {
        const issuer = a.issuer ? ' — ' + escapeHtml(a.issuer) : '';
        const date = a.date ? ' (' + escapeHtml(a.date) + ')' : '';
        return '<div class="flex items-start gap-2"><span class="text-emerald-600 mt-0.5">•</span><span class="text-xs text-gray-700">' + escapeHtml(a.title) + issuer + date + '</span></div>';
      }).join('');
    }
    // Flash effect to indicate changes
    const paper = document.getElementById('resume-paper');
    if (paper) {
      paper.style.transition = 'box-shadow 0.3s';
      paper.style.boxShadow = '0 0 0 3px rgba(0,108,73,0.3)';
      setTimeout(() => { paper.style.boxShadow = ''; }, 1500);
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
</script>
<?php else: ?>
<!-- AI Modify not available on this plan -->
<button onclick="if(confirm('AI Modify requires Pro or Elite plan. Upgrade now?'))window.location.href='?page=plan'" class="fixed bottom-6 right-6 z-[85] no-print bg-surface-container text-on-surface-variant px-6 py-4 rounded-full shadow-lg border border-outline-variant/40 hover:border-primary/40 transition-all flex items-center gap-3 group opacity-70 hover:opacity-100">
  <span class="material-symbols-outlined">lock</span>
  <span class="font-headline-md text-lg font-bold">Modify with AI</span>
</button>
<?php endif; ?>

<!-- SWITCH TEMPLATE MODAL -->
<div id="switchTemplateModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4 no-print">
  <div class="bg-surface-container-lowest w-full max-w-lg rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col p-6 gap-4">
    <div class="flex items-center justify-between border-b border-outline-variant/15 pb-3">
      <h3 class="font-headline-md text-lg font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">palette</span> Switch Resume Template
      </h3>
      <button onclick="closeSwitchTemplateModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-variant/40 transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>
    <p class="text-xs text-on-surface-variant mb-2">Switch the design layout dynamically. All profile data stays the same.</p>
    <form method="POST" id="switchTemplateForm" class="flex flex-col gap-3 m-0">
      <input type="hidden" name="action" value="switch_template">
      <input type="hidden" name="template" id="selectedTemplateInput">
      <div class="flex flex-col gap-2 max-h-[300px] overflow-y-auto pr-1">
        <button type="button" onclick="selectTemplate('Minimalist')" class="flex items-center justify-between p-3.5 rounded-xl border border-outline-variant/40 hover:border-primary/50 text-left hover:bg-primary/5 transition-all w-full">
          <div><div class="flex items-center gap-2"><span class="font-label-md text-sm font-bold text-on-surface">Minimalist</span><span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Free</span></div><p class="text-[11px] text-on-surface-variant mt-1">Clean, simple, high-readability layout with centered header.</p></div>
          <span class="material-symbols-outlined text-primary invisible" id="check-Minimalist">check_circle</span>
        </button>
        <button type="button" onclick="selectTemplate('Standard Modern')" class="flex items-center justify-between p-3.5 rounded-xl border border-outline-variant/40 hover:border-primary/50 text-left hover:bg-primary/5 transition-all w-full">
          <div><div class="flex items-center gap-2"><span class="font-label-md text-sm font-bold text-on-surface">Standard Modern</span><span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Free</span></div><p class="text-[11px] text-on-surface-variant mt-1">Two-column layout with green sidebar and structured sections.</p></div>
          <span class="material-symbols-outlined text-primary invisible" id="check-StandardModern">check_circle</span>
        </button>
        <button type="button" onclick="selectTemplate('Creative Leaf')" class="flex items-center justify-between p-3.5 rounded-xl border border-outline-variant/40 hover:border-primary/50 text-left hover:bg-primary/5 transition-all w-full">
          <div><div class="flex items-center gap-2"><span class="font-label-md text-sm font-bold text-on-surface">Creative Leaf</span><span class="text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Free</span></div><p class="text-[11px] text-on-surface-variant mt-1">Gradient header, timeline experience, card-style projects.</p></div>
          <span class="material-symbols-outlined text-primary invisible" id="check-CreativeLeaf">check_circle</span>
        </button>
      </div>
      <div class="flex justify-end gap-3 mt-4 border-t border-outline-variant/15 pt-4">
        <button type="button" onclick="closeSwitchTemplateModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-on-surface text-xs font-semibold">Cancel</button>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary hover:opacity-90 font-label-md shadow-md active:scale-95 transition-all text-xs font-bold">Apply Template</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openSwitchTemplateModal() {
    document.getElementById('switchTemplateModal').classList.remove('hidden');
    document.getElementById('switchTemplateModal').classList.add('flex');
    const currentTemplate = "<?= htmlspecialchars($template) ?>";
    const checkIcon = document.getElementById('check-' + currentTemplate.replace(/\s+/g, ''));
    if (checkIcon) { checkIcon.classList.remove('invisible'); checkIcon.classList.add('visible'); }
  }
  function closeSwitchTemplateModal() {
    document.getElementById('switchTemplateModal').classList.add('hidden');
    document.getElementById('switchTemplateModal').classList.remove('flex');
  }
  function selectTemplate(templateName) {
    document.getElementById('selectedTemplateInput').value = templateName;
    ['Minimalist', 'StandardModern', 'CreativeLeaf'].forEach(id => {
      const el = document.getElementById('check-' + id);
      if (el) { el.classList.add('invisible'); el.classList.remove('visible'); }
    });
    const target = document.getElementById('check-' + templateName.replace(/\s+/g, ''));
    if (target) { target.classList.remove('invisible'); target.classList.add('visible'); }
  }
</script>
<?php endif; ?><!-- end dashboard JS/modals -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<?php if (isset($_GET['standalone'])): ?>
<script>(function(){ window.onload = function() { setTimeout(function() { var opt = { margin: 0, filename: 'resume.pdf', html2canvas: { scale: 2, useCORS: true, logging: false }, jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' } }; html2pdf().set(opt).from(document.querySelector('.resume-paper')).save().then(function(){ window.location.href = '?page=resumes'; }); }, 500); }; })();</script>
<?php else: ?>
</main>
<?php endif; ?>
</body>
</html>
