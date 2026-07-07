<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$autopay_stmt = $db->prepare("SELECT value FROM settings WHERE key = 'billing_autopay'");
$autopay_stmt->execute();
$autopay_status = $autopay_stmt->fetchColumn() === 'true';

if (isset($_POST['action']) && $_POST['action'] === 'toggle_autopay') {
    $new_autopay = $autopay_status ? 'false' : 'true';
    $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('billing_autopay', ?)")->execute([$new_autopay]);
    echo '<script>window.location.href = "?page=plan&autopay_updated=1";</script>';
    exit;
}

$curr_stmt = $db->prepare("SELECT value FROM settings WHERE key = 'platform_currency'");
$curr_stmt->execute();
$currency_code = $curr_stmt->fetchColumn() ?: 'USD';
$currencies = ['USD' => '&#36;', 'EUR' => '&euro;', 'GBP' => '&pound;', 'INR' => '&#8377;', 'CAD' => 'C&#36;', 'AUD' => 'A&#36;'];
$currency_symbol = $currencies[$currency_code] ?? '$';

// Fetch all plans with their templates
$plans = $db->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll();
$pt_rows = $db->query("SELECT plan_id, template_id FROM plan_templates")->fetchAll();
$plan_templates_map = [];
foreach ($pt_rows as $pt) {
    $plan_templates_map[$pt['plan_id']][] = $pt['template_id'];
}

$paid_tpl_names = $db->query("SELECT id, name FROM templates WHERE type = 'Paid'")->fetchAll(PDO::FETCH_ASSOC);
$tpl_name_map = [];
foreach ($paid_tpl_names as $t) $tpl_name_map[$t['id']] = $t['name'];

$user_plan = null;
$user_plan_index = -1;
foreach ($plans as $i => $p) {
    if ($p['name'] === ($user['current_plan'] ?? 'Starter Launch')) {
        $user_plan = $p;
        $user_plan_index = $i;
        break;
    }
}

include __DIR__ . '/../components/common/head.php';
?>
<title>Billing Plan — GreenLeaf Resume</title>
<style>
  .billing-card { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
  .billing-card:hover { transform: translateY(-4px); }
  .switch-bg { transition: background-color 0.2s ease; }
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

  <div class="mt-24 px-6 md:px-16 pb-16 flex-1 flex flex-col">

    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">My Plan & Subscription</h1>
        <p class="text-on-surface-variant font-body-md mt-1">Review your active tier, see what templates are unlocked, and upgrade anytime.</p>
      </div>
      <span class="bg-primary/10 text-primary border border-primary/25 font-label-md text-xs font-bold px-4 py-2 rounded-full uppercase">
        <?= htmlspecialchars($user['current_plan'] ?? 'Starter Launch') ?> Active
      </span>
    </div>

    <?php if (isset($_GET['autopay_updated'])): ?>
      <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10">
        <span class="material-symbols-outlined text-sm">check_circle</span>
        <span class="font-label-md">Autopay settings saved successfully.</span>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['upgraded'])): ?>
      <div class="mb-6 p-4 bg-tertiary-fixed text-on-tertiary-fixed-variant rounded-xl flex items-center gap-2 animate-fade-in border border-tertiary/10">
        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
        <span class="font-label-md">Congratulations! You have successfully switched to the <strong><?= htmlspecialchars($_GET['plan']) ?></strong> plan.</span>
      </div>
    <?php endif; ?>

    <!-- Current Plan Details -->
    <?php if ($user_plan): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
      <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-4 lg:col-span-2">
        <h3 class="font-label-md text-xs uppercase tracking-wider text-primary font-bold">Active Tier Plan</h3>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mt-2">
          <div>
            <h2 class="font-headline-md text-2xl text-on-surface font-extrabold"><?= htmlspecialchars($user_plan['name']) ?></h2>
            <p class="text-on-surface-variant text-sm mt-1">
              <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
                $perms = [];
                if (!empty($user_plan['perm_ai_modify'])) $perms[] = 'AI Modify';
                if (!empty($user_plan['perm_web_speech'])) $perms[] = 'Voice';
                if (!empty($user_plan['perm_custom_profiles'])) $perms[] = 'Custom Profiles';
                if (!empty($user_plan['perm_pdf_print'])) $perms[] = 'PDF Print';
                echo !empty($perms) ? 'Includes: ' . implode(' · ', $perms) : 'Standard tier features.';
              ?>
            </p>
          </div>
          <div class="text-right">
            <span class="text-3xl font-extrabold text-on-surface"><?= $currency_symbol ?><?= number_format($user_plan['price'], 2) ?></span>
            <span class="text-xs text-on-surface-variant">/ <?= $user_plan['duration_days'] ?> days</span>
          </div>
        </div>

        <!-- Quota Progress -->
        <div class="mt-6 flex flex-col gap-2">
          <div class="flex justify-between items-center text-xs font-semibold text-on-surface-variant">
            <span>Monthly AI Optimizations Quota</span>
            <span><?= $user['ai_credits'] ?> / <?= $user_plan['ai_credits'] ?> remaining</span>
          </div>
          <?php $pct = $user_plan['ai_credits'] > 0 ? min(100, ($user['ai_credits'] / $user_plan['ai_credits']) * 100) : 0; ?>
          <div class="w-full h-2.5 bg-secondary-container rounded-full overflow-hidden">
            <div class="h-full bg-primary rounded-full transition-all duration-1000" style="width: <?= $pct ?>%;"></div>
          </div>
        </div>

        <!-- Included Templates -->
        <?php if (!empty($user_plan['access_paid_templates'])): ?>
        <?php $included_ids = $plan_templates_map[$user_plan['id']] ?? []; ?>
        <div class="border-t border-outline-variant/15 pt-4 mt-2">
          <p class="text-[10px] font-extrabold text-amber-800 uppercase tracking-wider mb-2 flex items-center gap-1">
            <span class="material-symbols-outlined text-[12px]">workspace_premium</span>
            <?= count($included_ids) ?> Premium Templates Unlocked
          </p>
          <?php if (empty($included_ids)): ?>
            <p class="text-xs text-on-surface-variant italic">Your plan has premium access but no specific templates are assigned yet.</p>
          <?php else: ?>
            <div class="flex flex-wrap gap-1.5">
              <?php foreach ($included_ids as $tid): ?>
                <?php if (isset($tpl_name_map[$tid])): ?>
                <span class="bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full text-[10px] font-bold border border-amber-200 flex items-center gap-1">
                  <span class="material-symbols-outlined text-[10px]">check_circle</span>
                  <?= htmlspecialchars($tpl_name_map[$tid]) ?>
                </span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-3 items-center mt-2 pt-2 border-t border-outline-variant/10 text-xs font-semibold text-on-surface-variant">
          <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-primary">check_circle</span> <?= $user_plan['max_resumes'] > 5000 ? 'Unlimited' : $user_plan['max_resumes'] ?> Resumes</span>
          <?php if (!empty($user_plan['perm_ai_modify'])): ?><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-primary">check_circle</span> AI Modify</span><?php endif; ?>
          <?php if (!empty($user_plan['perm_web_speech'])): ?><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-primary">check_circle</span> Voice</span><?php endif; ?>
          <?php if (!empty($user_plan['perm_pdf_print'])): ?><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-primary">check_circle</span> PDF Print</span><?php endif; ?>
        </div>
      </div>

      <!-- Expiry & Autopay -->
      <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-6 justify-between">
        <div class="flex flex-col gap-2">
          <h3 class="font-label-md text-xs uppercase tracking-wider text-error font-bold flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">event</span> Subscription Expiry
          </h3>
          <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
            $expiry = $user['plan_expiry'] ?? null;
            $days_left = 0;
            if ($expiry) {
              $diff = (strtotime($expiry) - time()) / 86400;
              $days_left = max(0, (int)$diff);
            }
          ?>
          <p class="font-headline-md text-lg text-on-surface font-extrabold mt-1"><?= $expiry ? date('F d, Y', strtotime($expiry)) : 'N/A' ?></p>
          <span class="text-xs text-error font-bold bg-error/10 border border-error/20 px-3 py-1 rounded-lg w-max mt-1">
            <?= $days_left ?> days remaining
          </span>
        </div>
        <div class="border-t border-outline-variant/15 pt-4 flex flex-col gap-3">
          <div class="flex justify-between items-center">
            <div>
              <h4 class="font-label-md text-sm text-on-surface font-bold">Credit Card Autopay</h4>
              <p class="text-[10px] text-on-surface-variant">Auto-charge at next cycle.</p>
            </div>
            <form method="POST" class="m-0">
              <input type="hidden" name="action" value="toggle_autopay">
              <button type="submit" class="flex items-center cursor-pointer">
                <div class="w-12 h-6 rounded-full p-0.5 switch-bg <?= $autopay_status ? 'bg-primary' : 'bg-outline-variant' ?> flex items-center transition-colors">
                  <div class="w-5 h-5 bg-surface-container-lowest rounded-full shadow-md transform <?= $autopay_status ? 'translate-x-6' : 'translate-x-0' ?> transition-transform duration-200"></div>
                </div>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Available Plans -->
    <div class="mb-8">
      <h3 class="font-headline-md text-xl text-on-surface mb-2 font-bold">Available Plans</h3>
      <p class="text-on-surface-variant font-body-md">Switch to a different tier anytime. Your credits and limits update immediately.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($plans as $i => $p):
        $is_current = ($user['current_plan'] ?? '') === $p['name'];
        $perms_list = [];
        if (!empty($p['perm_ai_modify'])) $perms_list[] = 'AI Modify widget';
        if (!empty($p['perm_web_speech'])) $perms_list[] = 'Web Speech / Voice';
        if (!empty($p['perm_custom_profiles'])) $perms_list[] = 'Custom job profiles';
        if (!empty($p['perm_pdf_print'])) $perms_list[] = 'PDF print';
        $feat_list = array_filter(array_map('trim', explode(',', $p['features'])));
        $all_features = array_unique(array_merge($feat_list, $perms_list));
        $border_cls = $is_current ? 'border-2 border-primary shadow-lg' : 'border border-outline-variant/30 shadow-sm';
      ?>
        <div class="bg-surface-container-lowest p-6 rounded-2xl flex flex-col gap-6 billing-card relative <?= $border_cls ?> <?= !$is_current && $p['price'] == 0 ? 'opacity-80' : '' ?>">
          <?php if ($is_current): ?>
            <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
              Your Current Plan
            </div>
            <div class="mt-2">
          <?php else: ?>
            <div>
          <?php endif; ?>
            <h4 class="font-headline-md text-lg text-on-surface font-bold"><?= htmlspecialchars($p['name']) ?></h4>
            <p class="text-xs text-on-surface-variant mt-1">
              <?= $p['max_resumes'] > 5000 ? 'Unlimited resumes' : $p['max_resumes'] . ' resumes' ?> ·
              <?= $p['ai_credits'] ?> AI credits
            </p>
            <div class="mt-4">
              <span class="text-3xl font-extrabold text-on-surface"><?= $currency_symbol ?><?= number_format($p['price'], 2) ?></span>
              <span class="text-xs text-on-surface-variant">/ <?= $p['duration_days'] ?> days</span>
            </div>
          </div>

          <div class="flex-1 border-t border-outline-variant/15 pt-4">
            <ul class="flex flex-col gap-2.5 text-xs text-on-surface-variant font-medium">
              <?php foreach ($all_features as $f): ?>
                <li class="flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[14px] <?= $is_current ? 'text-primary' : 'text-emerald-600' ?>">check</span>
                  <span><?= htmlspecialchars($f) ?></span>
                </li>
              <?php endforeach; ?>
              <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
                if (!empty($p['access_paid_templates'])) {
                  $pt_ids = $plan_templates_map[$p['id']] ?? [];
                  foreach ($pt_ids as $tid) {
                    if (isset($tpl_name_map[$tid])) {
                      echo '<li class="flex items-center gap-1.5">';
                      echo '<span class="material-symbols-outlined text-[14px] text-amber-600">workspace_premium</span>';
                      echo '<span>' . htmlspecialchars($tpl_name_map[$tid]) . ' template</span>';
                      echo '</li>';
                    }
                  }
                }
              ?>
            </ul>
          </div>

          <form method="POST" action="?page=checkout" class="m-0">
            <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
            <?php if ($is_current): ?>
              <button type="button" disabled class="w-full bg-primary/10 text-primary py-3 rounded-xl font-label-md font-bold uppercase tracking-wider text-xs">
                Currently Active
              </button>
            <?php else: ?>
              <button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-xl font-label-md font-bold hover:opacity-90 active:scale-95 transition-all shadow-md text-xs">
                <?= $p['price'] > 0 ? 'Switch to ' . htmlspecialchars($p['name']) : 'Switch to Free' ?>
              </button>
            <?php endif; ?>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>
</body>
</html>
