<?php 
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();

// Action to mark all notifications as read
if (isset($_GET['action']) && $_GET['action'] === 'mark_notifications_read') {
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    echo '<script>window.location.href = "?page=user_dashboard";</script>';
    exit;
}

// Fetch student profile details
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$student = $user_stmt->fetch();

// Fetch matching plan details
$plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
$plan_stmt->execute([$student['current_plan'] ?? 'Starter Launch']);
$user_plan = $plan_stmt->fetch() ?: [
    'name' => 'Starter Launch',
    'price' => 0.00,
    'duration_days' => 30,
    'access_paid_templates' => 0,
    'max_resumes' => 2,
    'ai_credits' => 5
];

// Fetch resumes for user_id
$resumes_stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC");
$resumes_stmt->execute([$user_id]);
$resumes = $resumes_stmt->fetchAll(PDO::FETCH_ASSOC);
$feedback_enabled = $db->query("SELECT value FROM settings WHERE key = 'feedback_enabled'")->fetchColumn() ?: '0';
include __DIR__ . '/../components/common/head.php'; 
?>
<title>GreenLeaf Resume - User Dashboard</title>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>
<!-- Main Content Canvas -->
<main class="md:ml-64 flex flex-col min-h-screen">
<!-- TopAppBar Shell -->
<header class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-surface/80 backdrop-blur-md shadow-sm flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4">
<div class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-primary">
<span class="material-symbols-outlined" data-icon="energy_savings_leaf">energy_savings_leaf</span>
<span>GreenLeaf Resume</span>
</div>
<div class="flex items-center gap-4">
<div class="hidden sm:block">
<button onclick="window.location.href='?page=select_job_profile'" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-label-md shadow-sm active:scale-95 transition-all">Create Resume</button>
</div>
<div class="flex items-center gap-2">
  <div class="relative">
    <button onclick="toggleNotificationsDropdown()" class="relative flex items-center justify-center p-2 text-on-surface-variant hover:bg-surface-variant rounded-full transition-all">
      <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
      <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
        $stmt_unread = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt_unread->execute([$user_id]);
        $unread_count = $stmt_unread->fetchColumn();
        if ($unread_count > 0):
      ?>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-error rounded-full animate-pulse"></span>
      <?php endif; ?>
    </button>
    
    <!-- Notifications Dropdown Box -->
    <div id="notifications-dropdown" class="hidden absolute right-0 top-12 w-80 bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-xl z-50 p-4 flex flex-col gap-3">
      <div class="flex justify-between items-center border-b border-outline-variant/15 pb-2">
        <span class="font-label-md text-xs font-bold text-on-surface">Recent Alerts</span>
        <?php if ($unread_count > 0): ?>
          <a href="?action=mark_notifications_read" class="text-[10px] text-primary hover:underline font-bold">Mark all read</a>
        <?php endif; ?>
      </div>
      <div class="flex flex-col gap-2 max-h-60 overflow-y-auto">
        <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
          $notifs = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5")->fetchAll();
          if (empty($notifs)):
        ?>
          <p class="text-[11px] text-on-surface-variant text-center py-4">No recent alerts or notifications.</p>
        <?php else: ?>
          <?php foreach ($notifs as $n): ?>
            <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
              $icon = 'info';
              $icon_cls = 'text-primary bg-primary/10';
              if ($n['type'] === 'Success') { $icon = 'check_circle'; $icon_cls = 'text-emerald-600 bg-emerald-100'; }
              elseif ($n['type'] === 'Warning') { $icon = 'warning'; $icon_cls = 'text-amber-600 bg-amber-100'; }
            ?>
            <div class="p-2.5 rounded-xl bg-surface/40 hover:bg-surface border border-outline-variant/10 transition-colors flex gap-2.5 items-start text-left">
              <span class="material-symbols-outlined text-[16px] p-1 rounded-lg <?= $icon_cls ?> mt-0.5"><?= $icon ?></span>
              <div class="flex-1">
                <span class="block text-[11px] font-extrabold text-on-surface leading-tight"><?= htmlspecialchars($n['title']) ?></span>
                <span class="block text-[10px] text-on-surface-variant leading-relaxed mt-0.5"><?= htmlspecialchars($n['message']) ?></span>
                <span class="block text-[8px] text-outline mt-1 font-semibold"><?= date('M d, H:i', strtotime($n['created_at'])) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <span class="material-symbols-outlined text-on-surface-variant p-2 cursor-pointer hover:bg-surface-variant rounded-full" data-icon="account_circle">account_circle</span>
</div>
</div>
</header>
<!-- Dashboard Content -->
<div class="mt-24 px-margin-mobile md:px-margin-desktop pb-12 flex flex-col gap-8">
<!-- Hero / Welcome Section -->
<section class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
<div>
<h2 class="font-headline-xl text-headline-xl text-on-surface">Hello, Professional</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl">Your career growth is in full bloom. Ready to update your professional story today?</p>
</div>
<button onclick="window.location.href='?page=select_job_profile'" class="w-full md:w-auto flex items-center justify-center gap-2 bg-primary text-on-primary px-8 py-4 rounded-xl font-label-md shadow-lg active:scale-95 transition-all text-lg">
<span class="material-symbols-outlined" data-icon="add">add</span>
                    Create Resume
                </button>
</section>
<!-- Bento Grid Layout -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
<!-- Subscription Status Card -->
<div class="lg:col-span-4 bg-surface-container-lowest rounded-xl p-8 shadow-sm border border-outline-variant/30 flex flex-col items-center justify-center relative overflow-hidden">
<div class="absolute -top-6 -right-6 w-24 h-24 bg-secondary-container/20 rounded-full blur-2xl"></div>
<h3 class="font-label-md text-label-md text-primary mb-6 uppercase tracking-wider">Active Plan</h3>

<!-- Progress / Status Ring -->
<div class="relative w-48 h-48 flex items-center justify-center mb-6">
<svg class="w-full h-full transform -rotate-90">
<circle class="text-surface-container" cx="96" cy="96" fill="transparent" r="80" stroke="currentColor" stroke-width="12"></circle>
<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
  // Compute resume usage percentage for the ring stroke
  $resumes_count = count($resumes);
  $max_resumes = intval($user_plan['max_resumes'] ?? 2);
  $usage_pct = $max_resumes > 0 && $max_resumes < 9000 ? ($resumes_count / $max_resumes) * 100 : 0;
  if ($max_resumes >= 9000) { $usage_pct = 100; }
  $stroke_offset = 502 - (502 * (min($usage_pct, 100) / 100));
?>
<circle class="text-primary transition-all duration-1000" cx="96" cy="96" fill="transparent" r="80" stroke="currentColor" stroke-dasharray="502" stroke-dashoffset="<?= $stroke_offset ?>" stroke-linecap="round" stroke-width="12"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
<span class="text-headline-md font-bold text-on-surface leading-tight"><?= htmlspecialchars($user_plan['name']) ?></span>
<span class="text-[10px] text-on-surface-variant uppercase font-extrabold mt-1"><?= $user_plan['duration_days'] ?> Days Term</span>
</div>
</div>

<div class="text-center w-full flex flex-col gap-2.5">
<div class="flex justify-between items-center bg-surface p-2.5 rounded-xl border border-outline-variant/10 text-xs">
  <span class="font-semibold text-on-surface-variant">Resumes Generated</span>
  <span class="font-bold text-primary"><?= $resumes_count ?> / <?= $max_resumes > 5000 ? 'Unlimited' : $max_resumes ?></span>
</div>
<div class="flex justify-between items-center bg-surface p-2.5 rounded-xl border border-outline-variant/10 text-xs">
  <span class="font-semibold text-on-surface-variant">AI Bullet Credits</span>
  <span class="font-bold text-primary"><?= htmlspecialchars($student['ai_credits'] ?? 5) ?> Left</span>
</div>
<p class="text-on-surface-variant text-[11px] mt-2">Limits are automatically configured by global admin rules.</p>
<button onclick="window.location.href='?page=plan'" class="text-primary font-label-md hover:underline active:scale-95 transition-all text-xs font-bold mt-2">Manage Subscription</button>
</div>
</div>
<!-- Quick Actions & Stats -->
<div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-gutter">
<!-- Profile Update Card -->
<div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm border border-outline-variant/30 flex flex-col justify-between">
<div class="flex items-start justify-between mb-4">
<div class="p-3 bg-secondary-fixed text-on-secondary-fixed rounded-lg">
<span class="material-symbols-outlined" data-icon="person_edit">person_edit</span>
</div>
<span class="text-label-sm text-on-surface-variant">Last updated 2d ago</span>
</div>
<div>
<h4 class="font-headline-md text-headline-md text-on-surface mb-2">Personal Details</h4>
<p class="text-on-surface-variant font-body-md mb-6">Keep your contact information and social links up to date for recruiters.</p>
<button onclick="window.location.href='?page=profile'" class="w-full bg-secondary-container text-on-secondary-container py-3 rounded-lg font-label-md hover:opacity-90 active:scale-95 transition-all">Update Profile</button>
</div>
</div>
<!-- Growth Progress -->
<div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm border border-outline-variant/30 flex flex-col justify-between">
<div class="flex items-start justify-between mb-4">
<div class="p-3 bg-tertiary-fixed text-on-tertiary-fixed rounded-lg">
<span class="material-symbols-outlined" data-icon="trending_up">trending_up</span>
</div>
<span class="text-label-sm text-primary font-bold">85% Profile Strength</span>
</div>
<div>
<h4 class="font-headline-md text-headline-md text-on-surface mb-2">Quick Actions</h4>
<div class="flex flex-col gap-2">
<a class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors border border-transparent hover:border-outline-variant/30" href="?page=profile&step=3">
<span class="font-label-md">Add Work Experience</span>
<span class="material-symbols-outlined text-primary" data-icon="chevron_right">chevron_right</span>
</a>
<a class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors border border-transparent hover:border-outline-variant/30" href="?page=profile&step=6">
<span class="font-label-md">Update Certifications</span>
<span class="material-symbols-outlined text-primary" data-icon="chevron_right">chevron_right</span>
</a>
<a class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors border border-transparent hover:border-outline-variant/30" href="?page=profile&step=4">
<span class="font-label-md">Add Skills</span>
<span class="material-symbols-outlined text-primary" data-icon="chevron_right">chevron_right</span>
</a>
</div>
</div>
</div>
<!-- Template Browser Banner -->
<div class="md:col-span-2 bg-inverse-surface rounded-xl p-8 relative overflow-hidden flex flex-col md:flex-row items-center gap-8 shadow-lg">
<div class="absolute right-0 top-0 w-1/3 h-full bg-primary/10 -skew-x-12 transform translate-x-1/2"></div>
<div class="flex-1 z-10">
<h4 class="text-surface-container-lowest font-headline-md mb-2">Discover New Templates</h4>
<p class="text-surface-variant font-body-md mb-4">Our designers just added 5 new professional layouts optimized for tech industries.</p>
<button onclick="window.location.href='?page=templates'" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-label-md active:scale-95 transition-all">Browse Library</button>
</div>
<div class="w-full md:w-48 h-32 rounded-lg bg-surface-container-high/20 flex items-center justify-center backdrop-blur-sm z-10 border border-white/10">
<span class="material-symbols-outlined text-surface-container-lowest text-4xl" data-icon="auto_awesome_motion">auto_awesome_motion</span>
</div>
</div>
</div>
</div>
<!-- Previously Generated Resumes -->
<section class="mt-8">
<div class="flex justify-between items-center mb-6">
<h3 class="font-headline-md text-headline-md text-on-surface">Previously Generated Resumes</h3>
<button onclick="window.location.href='?page=select_job_profile'" class="text-primary font-label-md flex items-center gap-1 hover:underline">
    Create New <span class="material-symbols-outlined text-sm">add</span>
</button>
</div>

<?php if (empty($resumes)): ?>
<div class="flex flex-col items-center justify-center py-24 border-2 border-dashed border-outline-variant rounded-2xl text-center">
    <span class="material-symbols-outlined text-6xl text-outline mb-4">description</span>
    <h4 class="font-headline-md text-on-surface mb-2">No resumes yet</h4>
    <p class="font-body-md text-on-surface-variant mb-6">Select a job profile and generate your first tailored resume.</p>
    <button onclick="window.location.href='?page=select_job_profile'" class="bg-primary text-on-primary px-8 py-3 rounded-lg font-label-md hover:opacity-90 active:scale-95 transition-all flex items-center gap-2">
        <span class="material-symbols-outlined">add</span> Create My First Resume
    </button>
</div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
<?php 
$thumb_images = [
    'https://lh3.googleusercontent.com/aida-public/AB6AXuAJhse6ro0YjBWWSXqy5pZ6Dc0UbgnRV5MIMpMLY3FJjjiCadbN7VQb4RaUmX4Pu9Pk_hlgCv1XdY5atwNpusynXoEd6iaNOww-wsNosMDZBooK90Tb6-aRCI9vFgcqUT_S7Xse0JHdP_NDUuKWgqPVhw0Jt_35vQdM0rLA-GY2BraHFKU9drMN7HrcpDn4HMUt7ATfH6hJVOEyP4Za6_07qgRFDjgGVhfDIvDGHX-fC3mRlwefMDn0SzldXyP4hSHK59HF848E9gA',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBZA7r6UjZ0X_2M3uoO5v6fRfsgO3993aGR8wE6uIWikqbD3si8T8o6Tl_wXPEAAoz4HPSm6H98ClUtPLPEvWaUNKE4K4_1BHzfJf645Ub8siv0woX0wjvjy4vznle-xAS7n334tvmeS8fd_QERiniE1hgtG4MDVL_UEXamRrAxLr9BpksmuW3-fZE-a33wnL4TV5oe5YQdK_zOAxYPKuMCMnUdE5iFG0M0UglxcGvLeKqRmBZQYBGsbCduXQVs6a5rpYc5Brq_1to',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuD5XlefmKgq0tgllhUxEpsF3dhjNULfCyRXkfmycy098xnbFPGk4d2WMX08v51WcznZlP1oOePpc1svDczUk5xqjR4BPQiKNAPHP0m7c58UtUDQqzRT0caC_2HhDHHxNIEN8Ap1PJBLXA4DBF1CjdmyrQZNN9QQys6a1yePl3CCL18FIRUp7dzDA45i4K0xvCgzkynJ1JrKjJ8SJQ8024BBH8RBoCjSyHBk7bVASzGsgpfW0C5wN-BBN2lD0EWB9v7sVnnY71zmDrc'
];
foreach ($resumes as $i => $resume): 
    $thumb = $thumb_images[$i % count($thumb_images)];
    $date = date('M d, Y', strtotime($resume['created_at']));
?>
<div class="group cursor-pointer" onclick="window.open('?page=preview_resume&id=<?php echo $resume['id']; ?>', '_blank')">
    <div class="asymmetric-leaf bg-surface-container shadow-sm p-4 mb-4 aspect-[3/4] relative overflow-hidden group-hover:shadow-xl transition-all duration-300 border border-outline-variant/20 group-hover:border-primary/40">
        <img src="<?php echo $thumb; ?>" alt="<?php echo htmlspecialchars($resume['job_profile']); ?> resume" class="w-full h-full object-cover rounded opacity-80 group-hover:opacity-100 transition-opacity">
        <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
            <span class="bg-surface-container-lowest text-primary px-4 py-2 rounded-full shadow-lg flex items-center gap-2 font-label-md">
                <span class="material-symbols-outlined text-sm">open_in_new</span> Open Preview
            </span>
        </div>
        <!-- Template Badge -->
        <div class="absolute top-3 left-3 bg-surface/80 backdrop-blur-sm px-2 py-1 rounded-full">
            <span class="font-label-sm text-primary text-xs"><?php echo htmlspecialchars($resume['template']); ?></span>
        </div>
    </div>
    <h5 class="font-label-md text-on-surface"><?php echo htmlspecialchars($resume['job_profile']); ?></h5>
    <p class="text-label-sm text-on-surface-variant">Generated <?php echo $date; ?></p>
</div>
<?php endforeach; ?>

<!-- New Resume CTA Card -->
<div class="group cursor-pointer" onclick="window.location.href='?page=select_job_profile'">
    <div class="asymmetric-leaf bg-surface-container shadow-sm p-4 mb-4 aspect-[3/4] relative overflow-hidden group-hover:shadow-lg transition-all duration-300 border-2 border-dashed border-outline-variant group-hover:border-primary flex items-center justify-center">
        <div class="flex flex-col items-center gap-3 text-outline group-hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-5xl">add_circle</span>
            <span class="font-label-md text-center">New Resume</span>
        </div>
    </div>
    <h5 class="font-label-md text-on-surface">Start a New Resume</h5>
    <p class="text-label-sm text-on-surface-variant">Choose a job profile to begin</p>
</div>

</div>
<?php endif; ?>
</section>
</div>
<?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<!-- Mobile Navigation Shell -->
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>
<!-- JavaScript Dropdown Controller -->
<script>
function toggleNotificationsDropdown() {
  const dd = document.getElementById('notifications-dropdown');
  if (dd) {
    dd.classList.toggle('hidden');
  }
}
// Close dropdown if clicking outside
window.addEventListener('click', function(e) {
  const dd = document.getElementById('notifications-dropdown');
  if (dd && !dd.classList.contains('hidden')) {
    if (!dd.contains(e.target) && !e.target.closest('[onclick="toggleNotificationsDropdown()"]')) {
      dd.classList.add('hidden');
    }
  }
});
</script>
<?php include __DIR__ . '/../components/common/feedback_popup.php'; ?>
</body></html>
