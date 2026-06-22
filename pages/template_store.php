<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();

// Fetch user's plan
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$plan_name = $user['current_plan'] ?? 'Starter Launch';
$plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
$plan_stmt->execute([$plan_name]);
$user_plan = $plan_stmt->fetch();

// Get user's accessible paid template IDs (with beta override)
$user_paid_tpl_ids = [];
$beta_paid = Auth::beta_perm('perm_paid_templates');
if ($beta_paid === true) {
    // Beta override: all templates accessible - fetch all paid template IDs
    $pt_stmt = $db->query("SELECT id FROM templates WHERE LOWER(type) = 'paid'");
    $user_paid_tpl_ids = $pt_stmt->fetchAll(PDO::FETCH_COLUMN);
} elseif ($beta_paid === false) {
    // Beta override: no paid templates accessible
    $user_paid_tpl_ids = [];
} elseif ($user_plan && !empty($user_plan['access_paid_templates'])) {
    $pt_stmt = $db->prepare("SELECT template_id FROM plan_templates WHERE plan_id = ?");
    $pt_stmt->execute([$user_plan['id']]);
    $user_paid_tpl_ids = $pt_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch all templates
$all_templates = $db->query("SELECT * FROM templates WHERE status = 'Active' ORDER BY type ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Build accessible and locked lists
$accessible = [];
$locked = [];
foreach ($all_templates as $t) {
    if ($t['type'] === 'Free') {
        $accessible[] = $t;
    } else {
        // Paid: accessible only if user's plan has it
        if (in_array($t['id'], $user_paid_tpl_ids)) {
            $accessible[] = $t;
        } else {
            $locked[] = $t;
        }
    }
}

$profiles = $db->query("SELECT * FROM job_profiles ORDER BY id ASC")->fetchAll();

include __DIR__ . '/../components/common/head.php';
?>
<title>Template Store — GreenLeaf Resume</title>
<style>
  .store-card {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
  .store-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.06);
  }
  .image-zoom {
    transition: transform 0.5s ease;
  }
  .store-card:hover .image-zoom {
    transform: scale(1.05);
  }
  .locked-card {
    opacity: 0.65;
    filter: grayscale(0.4);
  }
  .locked-card:hover {
    transform: translateY(-2px);
    opacity: 0.85;
    filter: grayscale(0.2);
  }
</style>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>

<main class="md:ml-64 flex flex-col min-h-screen">

  <header class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-surface/80 backdrop-blur-md shadow-sm flex justify-between items-center px-6 md:px-16 py-4">
    <div class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-primary">
      <span class="material-symbols-outlined">energy_savings_leaf</span>
      <span>GreenLeaf Resume</span>
    </div>
    <a href="?page=user_dashboard" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md">
      <span class="material-symbols-outlined text-sm">arrow_back</span> Dashboard
    </a>
  </header>

  <div id="store-toast" class="toast fixed bottom-6 right-6 z-[120] bg-inverse-surface text-inverse-on-surface px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 translate-y-20 opacity-0 pointer-events-none transition-all duration-300">
    <span id="store-toast-icon" class="material-symbols-outlined text-primary">check_circle</span>
    <span id="store-toast-msg" class="font-label-md">Template Notification</span>
  </div>

  <div class="mt-24 px-6 md:px-16 pb-16 flex-1 flex flex-col">

    <!-- Title Section -->
    <div class="mb-10 text-center md:text-left flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Template Store</h1>
        <p class="text-on-surface-variant font-body-md mt-1">
          Your plan: <strong class="text-primary"><?= htmlspecialchars($plan_name) ?></strong> ·
          <?= count($accessible) ?> layouts available · <?= count($locked) ?> locked
        </p>
      </div>
      <a href="?page=plan" class="flex items-center gap-2 border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary px-5 py-2.5 rounded-xl font-label-md transition-all active:scale-95">
        <span class="material-symbols-outlined text-sm">workspace_premium</span> Manage Plan
      </a>
    </div>

    <?php if (empty($accessible) && empty($locked)): ?>
      <div class="flex-1 flex flex-col items-center justify-center text-center py-16 bg-surface-container-lowest rounded-2xl border border-outline-variant/30 shadow-sm">
        <span class="material-symbols-outlined text-primary text-6xl mb-4">grid_off</span>
        <h3 class="font-headline-md text-xl text-on-surface mb-2">No Templates Available</h3>
        <p class="text-on-surface-variant font-body-md max-w-md">The template catalog is currently empty. Please contact support.</p>
      </div>
    <?php else: ?>

    <!-- Accessible Templates Section -->
    <?php if (!empty($accessible)): ?>
    <div class="mb-10">
      <h2 class="font-headline-md text-lg text-on-surface font-bold flex items-center gap-2 mb-5">
        <span class="material-symbols-outlined text-primary">check_circle</span> Your Available Layouts
        <span class="text-[10px] font-bold text-on-surface-variant bg-surface px-2 py-0.5 rounded-full"><?= count($accessible) ?></span>
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($accessible as $t):
          $is_paid = $t['type'] === 'Paid';
        ?>
          <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden flex flex-col store-card relative">
            <div class="aspect-[4/3] bg-surface-variant relative overflow-hidden group">
              <?php if (!empty($t['image_url'])): ?>
                <img src="<?= htmlspecialchars($t['image_url']) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 image-zoom">
              <?php else: ?>
                <div class="w-full h-full flex flex-col items-center justify-center p-4" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>15; color: <?= htmlspecialchars($t['accent_color']) ?>;">
                  <span class="material-symbols-outlined text-5xl mb-2"><?= htmlspecialchars($t['icon']) ?></span>
                  <div class="w-16 h-1 rounded-full mb-2" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>;"></div>
                  <div class="space-y-1 w-full max-w-[140px]">
                    <div class="h-1 bg-on-surface/10 rounded w-full"></div>
                    <div class="h-1 bg-on-surface/10 rounded w-3/4"></div>
                    <div class="h-1 bg-on-surface/10 rounded w-1/2"></div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
              <span class="absolute top-4 right-4 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full shadow-sm <?= $is_paid ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                <?= htmlspecialchars($t['type']) ?>
              </span>
            </div>
            <div class="p-6 flex flex-col flex-1 gap-4">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg text-white flex items-center justify-center flex-shrink-0" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>;">
                  <span class="material-symbols-outlined text-xl"><?= htmlspecialchars($t['icon']) ?></span>
                </div>
                <div>
                  <h3 class="font-headline-md text-base font-extrabold text-on-surface"><?= htmlspecialchars($t['name']) ?></h3>
                  <p class="text-xs text-on-surface-variant leading-relaxed mt-1"><?= htmlspecialchars($t['description']) ?></p>
                </div>
              </div>
              <div class="mt-auto pt-4 border-t border-outline-variant/15">
                <button onclick="openSelectRoleModal('<?= htmlspecialchars($t['name']) ?>')" class="w-full bg-primary text-on-primary py-2.5 rounded-xl font-label-md text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-1.5">
                  <span class="material-symbols-outlined text-sm">auto_awesome</span> Use This Template
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Locked Templates Section -->
    <?php if (!empty($locked)): ?>
    <div class="mb-10">
      <h2 class="font-headline-md text-lg text-on-surface font-bold flex items-center gap-2 mb-5">
        <span class="material-symbols-outlined text-amber-600">lock</span> Premium Layouts
        <span class="text-[10px] font-bold text-on-surface-variant bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full"><?= count($locked) ?> locked</span>
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($locked as $t): ?>
          <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden flex flex-col store-card locked-card relative">
            <div class="aspect-[4/3] bg-surface-variant relative overflow-hidden group">
              <?php if (!empty($t['image_url'])): ?>
                <img src="<?= htmlspecialchars($t['image_url']) ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <div class="w-full h-full flex flex-col items-center justify-center p-4" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>20; color: <?= htmlspecialchars($t['accent_color']) ?>;">
                  <span class="material-symbols-outlined text-5xl mb-2"><?= htmlspecialchars($t['icon']) ?></span>
                  <div class="w-16 h-1 rounded-full mb-2" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>;"></div>
                  <div class="space-y-1 w-full max-w-[140px]">
                    <div class="h-1 bg-on-surface/10 rounded w-full"></div>
                    <div class="h-1 bg-on-surface/10 rounded w-3/4"></div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
              <span class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-extrabold uppercase px-3 py-1 rounded-full shadow-sm border border-amber-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-[12px]">lock</span> Locked
              </span>
            </div>
            <div class="p-6 flex flex-col flex-1 gap-4">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                  <span class="material-symbols-outlined text-xl"><?= htmlspecialchars($t['icon']) ?></span>
                </div>
                <div>
                  <h3 class="font-headline-md text-base font-extrabold text-on-surface"><?= htmlspecialchars($t['name']) ?></h3>
                  <p class="text-xs text-on-surface-variant leading-relaxed mt-1"><?= htmlspecialchars($t['description']) ?></p>
                </div>
              </div>
              <div class="mt-auto pt-4 border-t border-outline-variant/15">
                <a href="?page=plan" class="w-full bg-amber-500 text-white py-2.5 rounded-xl font-label-md text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-1.5">
                  <span class="material-symbols-outlined text-sm">workspace_premium</span> Upgrade to Unlock
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>

  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>

<!-- Role selection modal -->
<div id="roleModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col p-6 gap-4">
    <div class="flex items-center justify-between border-b border-outline-variant/15 pb-3">
      <h3 class="font-headline-md text-lg font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">work</span> Select Job Profile
      </h3>
      <button onclick="closeRoleModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-variant/40 transition-colors">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>
    <form onsubmit="generateStoreResume(event)" class="flex flex-col gap-4">
      <input type="hidden" id="selectedTemplateId" name="template">
      <div>
        <label class="block font-label-md text-on-surface-variant mb-1.5 font-semibold text-xs uppercase tracking-wider">Choose a Target Job Role</label>
        <select name="profile" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all text-sm">
          <?php foreach ($profiles as $p): ?>
            <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <span class="text-[10px] text-on-surface-variant/70 mt-1 block">Your pre-saved resume details will map to this template.</span>
      </div>
      <div class="flex justify-end gap-3 mt-2 border-t border-outline-variant/15 pt-4">
        <button type="button" onclick="closeRoleModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-on-surface text-xs font-semibold">Cancel</button>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary hover:opacity-90 font-label-md shadow-md active:scale-95 transition-all text-xs font-bold flex items-center gap-1.5">
          <span class="material-symbols-outlined text-xs">auto_awesome</span> Generate Resume
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openSelectRoleModal(templateId) {
  document.getElementById('selectedTemplateId').value = templateId;
  document.getElementById('roleModal').classList.remove('hidden');
  document.getElementById('roleModal').classList.add('flex');
  document.body.classList.add('overflow-hidden');
}
function closeRoleModal() {
  document.getElementById('roleModal').classList.add('hidden');
  document.getElementById('roleModal').classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
}
async function generateStoreResume(e) {
  e.preventDefault();
  const formData = new FormData(e.target);
  try {
    const res = await fetch('api/generate_resume.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      closeRoleModal();
      showStoreToast('Resume queued for AI generation! Redirecting...');
      setTimeout(() => window.location.href = '?page=resumes&generating=' + data.id, 1200);
    } else {
      showStoreToast('Failed: ' + data.error, 'error');
    }
  } catch (err) {
    showStoreToast(err.message, 'error');
  }
}
function showStoreToast(msg, type = 'success') {
  const toast = document.getElementById('store-toast');
  const toastMsg = document.getElementById('store-toast-msg');
  const toastIcon = document.getElementById('store-toast-icon');
  toastMsg.innerText = msg;
  if (type === 'success') {
    toastIcon.innerText = 'check_circle';
    toastIcon.className = 'material-symbols-outlined text-primary';
  } else {
    toastIcon.innerText = 'error';
    toastIcon.className = 'material-symbols-outlined text-error';
  }
  toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
  toast.classList.add('translate-y-0', 'opacity-100');
  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
  }, 3000);
}
</script>
</body>
</html>
