<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();

// Fetch current user details
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Read checkbox setting value
$auto_update_stmt = $db->prepare("SELECT value FROM settings WHERE key = 'auto_update_resumes'");
$auto_update_stmt->execute();
$auto_update = $auto_update_stmt->fetchColumn() === 'true';

$theme_stmt = $db->prepare("SELECT value FROM settings WHERE key = 'app_theme'");
$theme_stmt->execute();
$app_theme = $theme_stmt->fetchColumn() ?: 'light';

// Handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $auto_val = isset($_POST['auto_update_resumes']) ? 'true' : 'false';
        $theme_val = $_POST['app_theme'] ?? 'light';

        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('auto_update_resumes', ?)")->execute([$auto_val]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('app_theme', ?)")->execute([$theme_val]);

        echo '<script>window.location.href = "?page=settings&saved=1";</script>';
        exit;
    }

    if ($action === 'delete_account') {
        $tables = ['users', 'profile_personal', 'profile_academics', 'profile_experience', 'profile_skills', 'profile_projects', 'profile_achievements', 'profile_hobbies', 'resumes', 'tickets', 'notifications'];
        foreach ($tables as $t) {
            $db->prepare("DELETE FROM $t WHERE user_id = ?")->execute([$user_id]);
        }

        Auth::clear_session();
        echo '<script>window.location.href = "?page=landing&deleted_account=1";</script>';
        exit;
    }
}

include __DIR__ . '/../components/common/head.php';
?>
<title>Settings — GreenLeaf Resume</title>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>

<!-- Main Content Canvas -->
<main class="md:ml-64 flex flex-col min-h-screen">
  
  <!-- Top bar -->
  <header class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-surface/80 backdrop-blur-md shadow-sm flex justify-between items-center px-6 md:px-16 py-4">
    <div class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-primary">
      <span class="material-symbols-outlined">energy_savings_leaf</span>
      <span>GreenLeaf Resume</span>
    </div>
    <a href="?page=user_dashboard" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md">
      <span class="material-symbols-outlined text-sm">arrow_back</span> Dashboard
    </a>
  </header>

  <div class="mt-24 px-6 md:px-16 pb-16 flex-1 flex flex-col">
    
    <!-- Title -->
    <div class="mb-8">
      <h1 class="font-headline-lg text-headline-lg text-on-surface">Account Settings</h1>
      <p class="text-on-surface-variant font-body-md mt-1">Configure your personal experience, automation details, and active preferences.</p>
    </div>

    <!-- Alert notifications -->
    <?php if (isset($_GET['saved'])): ?>
      <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in shadow-sm border border-primary/10">
        <span class="material-symbols-outlined text-sm">check_circle</span>
        <span class="font-label-md">Settings updated successfully!</span>
      </div>
    <?php endif; ?>

    <!-- Form Section -->
    <form method="POST" class="flex flex-col gap-6 max-w-2xl bg-surface-container-lowest p-8 border border-outline-variant/30 rounded-2xl shadow-sm mb-8">
      <input type="hidden" name="action" value="save_settings">
      
      <h3 class="font-headline-md text-lg text-on-surface border-b border-outline-variant/30 pb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">tune</span> Preferences
      </h3>

      <!-- Checkbox Update -->
      <div class="flex items-start gap-4 py-2 hover:bg-surface-variant/20 p-2.5 rounded-xl transition-all">
        <div class="flex items-center h-5">
          <input id="auto_update_resumes" name="auto_update_resumes" type="checkbox" value="true" <?= $auto_update ? 'checked' : '' ?> class="w-5 h-5 text-primary border-outline rounded focus:ring-primary focus:ring-offset-background transition-all">
        </div>
        <div class="flex flex-col gap-0.5">
          <label for="auto_update_resumes" class="font-label-md text-on-surface font-semibold cursor-pointer">Automatically update resumes on profile change</label>
          <span class="text-xs text-on-surface-variant">When enabled, any changes you make to your profile fields will sync across all generated resumes instantly.</span>
        </div>
      </div>

      <!-- Theme selection dropdown -->
      <div class="flex flex-col gap-2 mt-2">
        <label class="font-label-md text-on-surface font-semibold">Dashboard Theme Appearance</label>
        <select name="app_theme" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
          <option value="light" <?= $app_theme === 'light' ? 'selected' : '' ?>>Standard Light theme (Vibrant Leaves)</option>
          <option value="dark" <?= $app_theme === 'dark' ? 'selected' : '' ?>>Premium Slate Dark mode</option>
        </select>
        <span class="text-xs text-on-surface-variant">Tailor the visual environment of your personal GreenLeaf career ecosystem.</span>
      </div>

      <!-- Action Footer -->
      <div class="flex justify-end pt-4 border-t border-outline-variant/20">
        <button type="submit" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label-md shadow hover:opacity-90 active:scale-95 transition-all">
          Save Settings
        </button>
      </div>
    </form>

    <!-- Danger Zone Area -->
    <div class="max-w-2xl bg-error/5 border border-error/20 p-8 rounded-2xl shadow-sm flex flex-col gap-6">
      <div class="flex items-center gap-3 text-error border-b border-error/10 pb-3">
        <span class="material-symbols-outlined">report</span>
        <h3 class="font-headline-md text-lg font-bold">Danger Zone</h3>
      </div>
      <div>
        <h4 class="font-label-md text-on-surface font-semibold mb-1">Delete Account and Profile Data</h4>
        <p class="text-xs text-on-surface-variant">Once you delete your account, all generated resumes, active job configurations, and academic metrics will be permanently erased. This operation cannot be undone.</p>
      </div>
      <div>
        <button onclick="openDeleteModal()" class="bg-error text-on-error px-6 py-3 rounded-xl font-label-md shadow-md hover:bg-error-container hover:text-on-error-container transition-all active:scale-95">
          Delete My Account
        </button>
      </div>
    </div>

  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>

<!-- ── MODAL: Delete Confirmation ────────────────────────── -->
<div id="deleteModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col p-6 gap-4">
    <div class="flex items-center gap-3 text-error">
      <span class="material-symbols-outlined text-3xl">warning</span>
      <h3 class="font-headline-md text-xl font-bold text-on-surface">Are you absolutely sure?</h3>
    </div>
    <p class="text-on-surface-variant font-body-md">
      This action will permanently delete your account, your profile records, and all generated templates from GreenLeaf. You will be logged out immediately.
    </p>
    <form method="POST" class="m-0 flex justify-end gap-3 mt-4">
      <input type="hidden" name="action" value="delete_account">
      <button type="button" onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-on-surface">Cancel</button>
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-error text-on-error hover:opacity-90 font-label-md shadow-md active:scale-95 transition-all">Yes, Delete Account</button>
    </form>
  </div>
</div>

<script>
  function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
  }
  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
  }
</script>
</body>
</html>
