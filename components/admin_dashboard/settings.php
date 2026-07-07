<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_currency') {
        $currency = $_POST['currency'] ?? 'USD';
        $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('platform_currency', ?)");
        $stmt->execute([$currency]);
        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&currency_saved=1";</script>';
        exit;
    }
    
    if ($action === 'save_groq') {
        $key = $_POST['groq_key'] ?? '';
        $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('groq_api_key', ?)");
        $stmt->execute([$key]);
        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&groq_saved=1";</script>';
        exit;
    }

    if ($action === 'save_oauth') {
        $client_id = trim($_POST['google_client_id'] ?? '');
        $client_secret = trim($_POST['google_client_secret'] ?? '');
        $redirect_uri = trim($_POST['google_redirect_uri'] ?? '');
        
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('oauth_google_client_id', ?)")->execute([$client_id]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('oauth_google_client_secret', ?)")->execute([$client_secret]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('oauth_google_redirect_uri', ?)")->execute([$redirect_uri]);
        
        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&oauth_saved=1";</script>';
        exit;
    }

    if ($action === 'save_smtp') {
        $host = trim($_POST['smtp_host'] ?? '');
        $port = trim($_POST['smtp_port'] ?? '');
        $username = trim($_POST['smtp_username'] ?? '');
        $password = trim($_POST['smtp_password'] ?? '');
        $encryption = $_POST['smtp_encryption'] ?? 'tls';
        
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('smtp_host', ?)")->execute([$host]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('smtp_port', ?)")->execute([$port]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('smtp_username', ?)")->execute([$username]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('smtp_password', ?)")->execute([$password]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('smtp_encryption', ?)")->execute([$encryption]);
        
        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&smtp_saved=1";</script>';
        exit;
    }

    if ($action === 'save_dev_settings') {
        require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
        Auth::start_session();
        $beta_mode = isset($_POST['beta_mode']) ? '1' : '0';
        $dev_login = isset($_POST['dev_login_enabled']) ? '1' : '0';
        $credit_override = (int)($_POST['credit_override'] ?? 20);
        $social_login = isset($_POST['social_login_enabled']) ? '1' : '0';
        $beta_global_credits = (int)($_POST['beta_global_credits'] ?? 50);

        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('beta_mode', ?)")->execute([$beta_mode]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('dev_login_enabled', ?)")->execute([$dev_login]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('credit_override', ?)")->execute([$credit_override]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('social_login_enabled', ?)")->execute([$social_login]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('beta_global_credits', ?)")->execute([$beta_global_credits]);

        // Save permission toggles
        $perms = ['perm_ai_modify', 'perm_web_speech', 'perm_custom_profiles', 'perm_pdf_print', 'perm_paid_templates'];
        foreach ($perms as $p) {
            $val = isset($_POST[$p]) ? '1' : '0';
            $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('beta_$p', ?)")->execute([$val]);
        }

        Auth::set_beta((bool)$beta_mode);

        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&dev_saved=1";</script>';
        exit;
    }

    if ($action === 'apply_credits_all') {
        $credits = (int)($_POST['credits_amount'] ?? 50);
        if ($credits < 0) $credits = 0;
        $db->prepare("UPDATE users SET ai_credits = ?")->execute([$credits]);
        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&credits_applied=1";</script>';
        exit;
    }

    if ($action === 'save_banner') {
        $banner_enabled = isset($_POST['banner_enabled']) ? '1' : '0';
        $banner_text = trim($_POST['banner_text'] ?? '');
        $banner_color = trim($_POST['banner_color'] ?? '#006c49');
        $banner_link = trim($_POST['banner_link'] ?? '');

        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('banner_enabled', ?)")->execute([$banner_enabled]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('banner_text', ?)")->execute([$banner_text]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('banner_color', ?)")->execute([$banner_color]);
        $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('banner_link', ?)")->execute([$banner_link]);

        echo '<script>window.location.href = "?page=admin_dashboard&tab=settings&banner_saved=1";</script>';
        exit;
    }

}

// Retrieve current settings
$current_currency = $db->query("SELECT value FROM settings WHERE key = 'platform_currency'")->fetchColumn() ?: 'USD';
$current_groq = $db->query("SELECT value FROM settings WHERE key = 'groq_api_key'")->fetchColumn() ?: '';
$beta_mode = $db->query("SELECT value FROM settings WHERE key = 'beta_mode'")->fetchColumn() ?: '0';
$dev_login = $db->query("SELECT value FROM settings WHERE key = 'dev_login_enabled'")->fetchColumn() ?: '0';
$credit_override = $db->query("SELECT value FROM settings WHERE key = 'credit_override'")->fetchColumn() ?: '20';
$social_login = $db->query("SELECT value FROM settings WHERE key = 'social_login_enabled'")->fetchColumn() ?: '0';
$banner_enabled = $db->query("SELECT value FROM settings WHERE key = 'banner_enabled'")->fetchColumn() ?: '0';
$banner_text = $db->query("SELECT value FROM settings WHERE key = 'banner_text'")->fetchColumn() ?: '';
$banner_color = $db->query("SELECT value FROM settings WHERE key = 'banner_color'")->fetchColumn() ?: '#006c49';
$banner_link = $db->query("SELECT value FROM settings WHERE key = 'banner_link'")->fetchColumn() ?: '';
$beta_global_credits = $db->query("SELECT value FROM settings WHERE key = 'beta_global_credits'")->fetchColumn() ?: '50';
$beta_perm_ai_modify = $db->query("SELECT value FROM settings WHERE key = 'beta_perm_ai_modify'")->fetchColumn() ?: '1';
$beta_perm_web_speech = $db->query("SELECT value FROM settings WHERE key = 'beta_perm_web_speech'")->fetchColumn() ?: '1';
$beta_perm_custom_profiles = $db->query("SELECT value FROM settings WHERE key = 'beta_perm_custom_profiles'")->fetchColumn() ?: '1';
$beta_perm_pdf_print = $db->query("SELECT value FROM settings WHERE key = 'beta_perm_pdf_print'")->fetchColumn() ?: '1';
$beta_perm_paid_templates = $db->query("SELECT value FROM settings WHERE key = 'beta_perm_paid_templates'")->fetchColumn() ?: '1';

// OAuth fields
$oauth_client_id = $db->query("SELECT value FROM settings WHERE key = 'oauth_google_client_id'")->fetchColumn() ?: '';
$oauth_client_secret = $db->query("SELECT value FROM settings WHERE key = 'oauth_google_client_secret'")->fetchColumn() ?: '';
$oauth_redirect_uri = $db->query("SELECT value FROM settings WHERE key = 'oauth_google_redirect_uri'")->fetchColumn() ?: '';

// SMTP fields
$smtp_host = $db->query("SELECT value FROM settings WHERE key = 'smtp_host'")->fetchColumn() ?: 'smtp.mailtrap.io';
$smtp_port = $db->query("SELECT value FROM settings WHERE key = 'smtp_port'")->fetchColumn() ?: '2525';
$smtp_username = $db->query("SELECT value FROM settings WHERE key = 'smtp_username'")->fetchColumn() ?: '';
$smtp_password = $db->query("SELECT value FROM settings WHERE key = 'smtp_password'")->fetchColumn() ?: '';
$smtp_encryption = $db->query("SELECT value FROM settings WHERE key = 'smtp_encryption'")->fetchColumn() ?: 'tls';
?>

<!-- Settings Tab -->
<header class="mb-10">
  <h2 class="font-headline-lg text-headline-lg text-on-surface">Platform Settings</h2>
  <p class="font-body-md text-on-surface-variant">Configure oauth integrations, system email delivery engines, global currencies, and processing credentials.</p>
</header>

<!-- Success Notifications -->
<?php if (isset($_GET['currency_saved'])): ?>
  <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">payments</span>
    <span class="font-label-md text-xs font-bold">Platform default currency updated successfully!</span>
  </div>
<?php endif; ?>
<?php if (isset($_GET['groq_saved'])): ?>
  <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">memory</span>
    <span class="font-label-md text-xs font-bold">Groq AI API settings updated successfully!</span>
  </div>
<?php endif; ?>
<?php if (isset($_GET['oauth_saved'])): ?>
  <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">key</span>
    <span class="font-label-md text-xs font-bold">Google Client OAuth Credentials saved successfully!</span>
  </div>
<?php endif; ?>
<?php if (isset($_GET['smtp_saved'])): ?>
  <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">mail</span>
    <span class="font-label-md text-xs font-bold">System SMTP configuration saved successfully!</span>
  </div>
<?php endif; ?>
<?php if (isset($_GET['dev_saved'])): ?>
  <div class="mb-6 p-4 bg-tertiary-container text-on-tertiary-container rounded-xl flex items-center gap-2 animate-fade-in border border-tertiary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">terminal</span>
    <span class="font-label-md text-xs font-bold">Developer settings updated successfully!</span>
  </div>
<?php endif; ?>
<?php if (isset($_GET['banner_saved'])): ?>
  <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">campaign</span>
    <span class="font-label-md text-xs font-bold">Announcement banner updated successfully!</span>
  </div>
<?php endif; ?>
<?php if (isset($_GET['credits_applied'])): ?>
  <div class="mb-6 p-4 bg-tertiary-container text-on-tertiary-container rounded-xl flex items-center gap-2 animate-fade-in border border-tertiary/10 shadow-sm">
    <span class="material-symbols-outlined text-sm">token</span>
    <span class="font-label-md text-xs font-bold">Credits applied to all users successfully!</span>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter items-start">
    
    <!-- Currency Settings -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-surface-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined">payments</span>
              </div>
              <h3 class="font-headline-md text-on-surface">Platform Currency Configuration</h3>
          </div>
          <form method="POST" class="flex flex-col gap-4 m-0">
              <input type="hidden" name="action" value="save_currency">
              
              <div class="flex flex-col gap-1.5">
                  <label class="font-label-md text-on-surface-variant font-bold text-xs">Primary Currency Tag</label>
                  <select name="currency" class="w-full px-4 py-3 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm font-semibold">
                      <option value="USD" <?= $current_currency === 'USD' ? 'selected' : '' ?>>USD ($)</option>
<option value="EUR" <?= $current_currency === 'EUR' ? 'selected' : '' ?>>EUR (&euro;)</option>
<option value="GBP" <?= $current_currency === 'GBP' ? 'selected' : '' ?>>GBP (&pound;)</option>
<option value="INR" <?= $current_currency === 'INR' ? 'selected' : '' ?>>INR (&#8377;)</option>
                      <option value="CAD" <?= $current_currency === 'CAD' ? 'selected' : '' ?>>CAD (C$)</option>
                      <option value="AUD" <?= $current_currency === 'AUD' ? 'selected' : '' ?>>AUD (A$)</option>
                  </select>
                  <p class="font-label-sm text-outline mt-1 text-[10px]">This currency code will adjust layout tables across plans, template store, and checkout gateways.</p>
              </div>
              <button type="submit" class="mt-2 w-max bg-primary text-on-primary px-6 py-2.5 rounded-xl font-label-md font-bold shadow active:scale-95 hover:opacity-90 transition-all text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">save</span> Save Currency
              </button>
          </form>
        </div>
    </div>

    <!-- Groq API Settings -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-surface-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined">memory</span>
              </div>
              <h3 class="font-headline-md text-on-surface">AI Processing (Groq API)</h3>
          </div>
          <form method="POST" class="flex flex-col gap-4 m-0">
              <input type="hidden" name="action" value="save_groq">
              
              <div class="flex flex-col gap-1.5">
                  <label class="font-label-md text-on-surface-variant font-bold text-xs">Groq API Key</label>
                  <div class="relative">
                      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">key</span>
                      <input type="password" name="groq_key" placeholder="Enter your API key" value="<?= htmlspecialchars($current_groq) ?>" class="w-full pl-10 pr-4 py-3 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-sm">
                  </div>
                  <p class="font-label-sm text-outline mt-1 text-[10px] font-semibold">Used for dynamic resume generations, custom AI optimizations, and phrasings.</p>
              </div>
              <button type="submit" class="mt-2 w-max bg-primary text-on-primary px-6 py-2.5 rounded-xl font-label-md font-bold shadow active:scale-95 hover:opacity-90 transition-all text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">save</span> Save API Key
              </button>
          </form>
        </div>
    </div>

    <!-- Google OAuth Settings -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-surface-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined">login</span>
              </div>
              <h3 class="font-headline-md text-on-surface">Google Client OAuth Details</h3>
          </div>
          <form method="POST" class="flex flex-col gap-4 m-0">
              <input type="hidden" name="action" value="save_oauth">
              
              <div class="flex flex-col gap-3">
                  <div>
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">Client ID</label>
                      <input type="text" name="google_client_id" value="<?= htmlspecialchars($oauth_client_id) ?>" placeholder="e.g. 102934-8asdf.apps.googleusercontent.com" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  </div>
                  <div>
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">Client Secret</label>
                      <input type="password" name="google_client_secret" value="<?= htmlspecialchars($oauth_client_secret) ?>" placeholder="e.g. GOCSPX-3nK8..." class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs">
                  </div>
                  <div>
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">Redirect Callback URI</label>
                      <input type="text" name="google_redirect_uri" value="<?= htmlspecialchars($oauth_redirect_uri) ?>" placeholder="e.g. http://localhost:8000/?page=auth&provider=google" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  </div>
              </div>
              
              <button type="submit" class="mt-4 w-max bg-primary text-on-primary px-6 py-2.5 rounded-xl font-label-md font-bold shadow active:scale-95 hover:opacity-90 transition-all text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">save</span> Save OAuth details
              </button>
          </form>
        </div>
    </div>

    <!-- SMTP Delivery Server Details -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-surface-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined">mail</span>
              </div>
              <h3 class="font-headline-md text-on-surface">SMTP Transactional Email Details</h3>
          </div>
          <form method="POST" class="flex flex-col gap-4 m-0">
              <input type="hidden" name="action" value="save_smtp">
              
              <div class="grid grid-cols-2 gap-3 mb-1">
                  <div class="col-span-2 md:col-span-1">
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">SMTP Server Host</label>
                      <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtp_host) ?>" required placeholder="e.g. smtp.gmail.com" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  </div>
                  <div class="col-span-2 md:col-span-1">
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">SMTP Port</label>
                      <input type="text" name="smtp_port" value="<?= htmlspecialchars($smtp_port) ?>" required placeholder="e.g. 587 or 465" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  </div>
                  <div class="col-span-2 md:col-span-1">
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">Username ID</label>
                      <input type="text" name="smtp_username" value="<?= htmlspecialchars($smtp_username) ?>" placeholder="e.g. key-or-email" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  </div>
                  <div class="col-span-2 md:col-span-1">
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">Password Secret</label>
                      <input type="password" name="smtp_password" value="<?= htmlspecialchars($smtp_password) ?>" placeholder="SMTP Password" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs">
                  </div>
                  <div class="col-span-2">
                      <label class="block font-label-md text-on-surface-variant mb-1 font-bold text-xs">Secure Encryption Type</label>
                      <select name="smtp_encryption" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                          <option value="tls" <?= $smtp_encryption === 'tls' ? 'selected' : '' ?>>TLS (Recommended - 587)</option>
                          <option value="ssl" <?= $smtp_encryption === 'ssl' ? 'selected' : '' ?>>SSL (Implicit - 465)</option>
                          <option value="none" <?= $smtp_encryption === 'none' ? 'selected' : '' ?>>None (Plaintext - 25)</option>
                      </select>
                  </div>
              </div>
              
              <button type="submit" class="mt-4 w-max bg-primary text-on-primary px-6 py-2.5 rounded-xl font-label-md font-bold shadow active:scale-95 hover:opacity-90 transition-all text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">save</span> Save SMTP configs
              </button>
          </form>
        </div>
    </div>

</div>

<!-- Developer Settings Section -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-gutter items-start">

    <!-- Developer Mode Controls -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
                  <span class="material-symbols-outlined">terminal</span>
              </div>
              <h3 class="font-headline-md text-on-surface">Developer Settings</h3>
          </div>
          <form method="POST" class="flex flex-col gap-4 m-0">
              <input type="hidden" name="action" value="save_dev_settings">

              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="beta_mode" value="1" <?= $beta_mode ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">Beta Mode</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Disables payments, sets credits to 20, enables dev login</p>
                  </div>
              </label>

              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="dev_login_enabled" value="1" <?= $dev_login ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">Dev Login Panel</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Show quick-login dropdown on student login page and admin sidebar</p>
                  </div>
              </label>

              <div class="flex flex-col gap-1.5">
                  <label class="font-label-md text-on-surface-variant font-bold text-xs">Credit Override (Beta Mode)</label>
                  <input type="number" name="credit_override" value="<?= (int)$credit_override ?>" min="1" max="999" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  <p class="font-label-sm text-outline mt-1 text-[10px]">Credits given to new users when beta mode is active.</p>
              </div>

              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="social_login_enabled" value="1" <?= $social_login ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">Social Login (Google)</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Enable Google OAuth sign-in on the login page</p>
                  </div>
              </label>

              <hr class="border-outline-variant/40 my-2">

              <div class="flex items-center gap-2 mb-1">
                  <span class="material-symbols-outlined text-tertiary text-lg">tune</span>
                  <span class="font-label-md text-on-surface text-xs font-bold">Beta Permissions Override</span>
                  <span class="text-[9px] bg-tertiary/10 text-tertiary px-2 py-0.5 rounded-full font-bold">Active when beta ON</span>
              </div>

              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="perm_ai_modify" value="1" <?= $beta_perm_ai_modify ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">AI Modify Chat</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Allow all users to use the AI Chat Agent for resume editing</p>
                  </div>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="perm_web_speech" value="1" <?= $beta_perm_web_speech ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">Voice Input (Web Speech)</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Enable speech-to-text voice input across the platform</p>
                  </div>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="perm_custom_profiles" value="1" <?= $beta_perm_custom_profiles ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">Custom Job Profiles</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Allow users to create custom job profile entries</p>
                  </div>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="perm_pdf_print" value="1" <?= $beta_perm_pdf_print ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">PDF Print / Download</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Allow PDF export and printing for all users</p>
                  </div>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="perm_paid_templates" value="1" <?= $beta_perm_paid_templates ? 'checked' : '' ?> class="w-5 h-5 rounded accent-tertiary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">All Templates Access</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Unlock all premium templates for every user</p>
                  </div>
              </label>

              <hr class="border-outline-variant/40 my-2">

              <div class="flex items-center gap-2 mb-1">
                  <span class="material-symbols-outlined text-tertiary text-lg">token</span>
                  <span class="font-label-md text-on-surface text-xs font-bold">Global Credits</span>
                  <span class="text-[9px] bg-tertiary/10 text-tertiary px-2 py-0.5 rounded-full font-bold">Active when beta ON</span>
              </div>

              <div class="flex flex-col gap-1.5">
                  <label class="font-label-md text-on-surface-variant font-bold text-xs">Credits for All Users</label>
                  <input type="number" name="beta_global_credits" value="<?= (int)$beta_global_credits ?>" min="1" max="9999" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  <p class="font-label-sm text-outline mt-1 text-[10px]">When beta is ON, all users get this many credits (overrides individual balances) on next save.</p>
              </div>

              <button type="submit" class="mt-2 w-max bg-tertiary text-on-tertiary px-6 py-2.5 rounded-xl font-label-md font-bold shadow active:scale-95 hover:opacity-90 transition-all text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">save</span> Save Developer Settings
              </button>

              <!-- Apply Credits To All form -->
              <form method="POST" class="mt-4 p-4 bg-tertiary/5 border border-tertiary/20 rounded-xl">
                  <input type="hidden" name="action" value="apply_credits_all">
                  <div class="flex items-center gap-3 mb-3">
                      <span class="material-symbols-outlined text-tertiary text-lg">groups</span>
                      <span class="font-label-md text-on-surface text-xs font-bold">Apply Credits to All Users</span>
                  </div>
                  <div class="flex items-center gap-2">
                      <input type="number" name="credits_amount" value="50" min="1" max="9999" class="flex-1 px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-tertiary outline-none transition-all text-xs font-semibold" placeholder="Credit amount">
                      <button type="submit" class="bg-tertiary text-on-tertiary px-4 py-2.5 rounded-xl font-label-md font-bold hover:opacity-90 active:scale-95 transition-all text-xs whitespace-nowrap">Apply to All</button>
                  </div>
                  <p class="text-[10px] text-on-surface-variant mt-2">Instantly sets the given amount of AI credits for every registered user.</p>
              </form>

          </form>
        </div>
    </div>

    <!-- Announcement Banner -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined">campaign</span>
              </div>
              <h3 class="font-headline-md text-on-surface">Announcement Banner</h3>
          </div>
          <form method="POST" class="flex flex-col gap-4 m-0">
              <input type="hidden" name="action" value="save_banner">

              <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="banner_enabled" value="1" <?= $banner_enabled ? 'checked' : '' ?> class="w-5 h-5 rounded accent-primary">
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold">Enable Banner</span>
                      <p class="text-[10px] text-on-surface-variant font-medium">Show announcement strip below navigation</p>
                  </div>
              </label>

              <div class="flex flex-col gap-1.5">
                  <label class="font-label-md text-on-surface-variant font-bold text-xs">Banner Message</label>
                  <textarea name="banner_text" rows="2" placeholder="e.g. New feature launched! Check out AI Resume Modify..." class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold resize-none"><?= htmlspecialchars($banner_text) ?></textarea>
              </div>

              <div class="grid grid-cols-2 gap-3">
                  <div class="flex flex-col gap-1.5">
                      <label class="font-label-md text-on-surface-variant font-bold text-xs">Banner Color</label>
                      <div class="flex items-center gap-2">
                          <input type="color" name="banner_color" value="<?= htmlspecialchars($banner_color) ?>" class="w-10 h-10 rounded-lg border border-outline-variant cursor-pointer">
                          <input type="text" value="<?= htmlspecialchars($banner_color) ?>" readonly class="flex-1 px-3 py-2 bg-surface border border-outline-variant rounded-lg text-xs font-mono">
                      </div>
                  </div>
                  <div class="flex flex-col gap-1.5">
                      <label class="font-label-md text-on-surface-variant font-bold text-xs">Link URL (optional)</label>
                      <input type="text" name="banner_link" value="<?= htmlspecialchars($banner_link) ?>" placeholder="e.g. ?page=plan" class="w-full px-4 py-2.5 bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all text-xs font-semibold">
                  </div>
              </div>

              <button type="submit" class="mt-2 w-max bg-primary text-on-primary px-6 py-2.5 rounded-xl font-label-md font-bold shadow active:scale-95 hover:opacity-90 transition-all text-xs flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">save</span> Save Banner
              </button>
          </form>
        </div>
    </div>

    <!-- Backup & Logs -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
                  <span class="material-symbols-outlined">folder_zip</span>
              </div>
              <h3 class="font-headline-md text-on-surface">Backup & Logs</h3>
          </div>

          <div class="flex flex-col gap-4">
              <a href="/api/download_backup.php" class="w-full flex items-center gap-3 p-4 bg-surface-container border border-outline-variant rounded-xl hover:bg-primary-container/30 transition-colors text-left">
                  <span class="material-symbols-outlined text-primary">download</span>
                  <div>
                      <span class="font-label-md text-on-surface text-xs font-bold block">Download ZIP Backup</span>
                      <span class="text-[10px] text-on-surface-variant font-medium">Database + .env + schema</span>
                  </div>
              </a>

              <div class="p-4 bg-surface-container border border-outline-variant rounded-xl">
                  <div class="flex items-center gap-2 mb-3">
                      <span class="material-symbols-outlined text-primary">receipt_long</span>
                      <span class="font-label-md text-on-surface text-xs font-bold">Recent Logs</span>
                  </div>
                  <div id="log-viewer" class="max-h-48 overflow-y-auto bg-surface rounded-lg p-3 font-mono text-[10px] text-on-surface-variant leading-relaxed">
                      Loading logs...
                  </div>
              </div>
          </div>
        </div>
    </div>

</div>

<script>
(async function() {
    const viewer = document.getElementById('log-viewer');
    try {
        const [aiResp, uploadResp] = await Promise.all([
            fetch('/logs/ai.log').catch(() => null),
            fetch('/logs/upload_debug.log').catch(() => null)
        ]);
        let content = '';
        if (aiResp && aiResp.ok) {
            const aiText = await aiResp.text();
            const aiLines = aiText.trim().split('\n').slice(-30);
            content += '<div class="mb-2 font-bold text-primary">=== AI Log ===</div>' + aiLines.join('\n');
        }
        if (uploadResp && uploadResp.ok) {
            const upText = await uploadResp.text();
            const upLines = upText.trim().split('\n').slice(-20);
            content += '<div class="mt-3 mb-2 font-bold text-primary">=== Upload Log ===</div>' + upLines.join('\n');
        }
        if (!content) content = 'No log files found.';
        viewer.textContent = '';
        const pre = document.createElement('pre');
        pre.className = 'whitespace-pre-wrap';
        pre.textContent = content;
        viewer.appendChild(pre);
    } catch (e) {
        viewer.textContent = 'Unable to load logs.';
    }
})();
</script>
