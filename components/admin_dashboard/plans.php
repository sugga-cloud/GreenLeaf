<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

// Retrieve default platform currency settings
$curr_stmt = $db->prepare("SELECT value FROM settings WHERE key = 'platform_currency'");
$curr_stmt->execute();
$currency_code = $curr_stmt->fetchColumn() ?: 'USD';

$currencies = ['USD' => '$', 'EUR' => 'â‚¬', 'GBP' => 'Â£', 'INR' => 'â‚¹', 'CAD' => 'C$', 'AUD' => 'A$'];
$currency_symbol = $currencies[$currency_code] ?? '$';

// Helper function to check if a plan is actively bought / has subscribers
function isPlanBought($planName) {
    if ($planName === 'Pro Career Growth') {
        return true;
    }
    return false;
}

// Handle CRUD POST Requests
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_plan') {
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0.00);
        $duration = intval($_POST['duration_days'] ?? 30);
        $access_paid_templates = isset($_POST['access_paid_templates']) ? 1 : 0;
        $max_resumes = intval($_POST['max_resumes'] ?? 2);
        $ai_credits = intval($_POST['ai_credits'] ?? 10);
        $features = trim($_POST['features'] ?? '');
        $selected_templates = $_POST['templates'] ?? [];
        $perm_ai_modify = isset($_POST['perm_ai_modify']) ? 1 : 0;
        $perm_web_speech = isset($_POST['perm_web_speech']) ? 1 : 0;
        $perm_custom_profiles = isset($_POST['perm_custom_profiles']) ? 1 : 0;
        $perm_pdf_print = isset($_POST['perm_pdf_print']) ? 1 : 0;

        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT INTO plans (name, price, duration_days, access_paid_templates, max_resumes, ai_credits, features, perm_ai_modify, perm_web_speech, perm_custom_profiles, perm_pdf_print) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $price, $duration, $access_paid_templates, $max_resumes, $ai_credits, $features, $perm_ai_modify, $perm_web_speech, $perm_custom_profiles, $perm_pdf_print]);
                $plan_id = $db->lastInsertId();

                if ($access_paid_templates && !empty($selected_templates)) {
                    $ins_pt = $db->prepare("INSERT OR IGNORE INTO plan_templates (plan_id, template_id) VALUES (?, ?)");
                    foreach ($selected_templates as $tid) {
                        $ins_pt->execute([$plan_id, intval($tid)]);
                    }
                }

                echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&created=1";</script>';
                exit;
            } catch (Exception $e) {
                $error = "Plan name must be unique. Choose a different catalog name.";
            }
        }
    }

    if ($action === 'update_plan') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0.00);
        $duration = intval($_POST['duration_days'] ?? 30);
        $access_paid_templates = isset($_POST['access_paid_templates']) ? 1 : 0;
        $max_resumes = intval($_POST['max_resumes'] ?? 2);
        $ai_credits = intval($_POST['ai_credits'] ?? 10);
        $features = trim($_POST['features'] ?? '');
        $selected_templates = $_POST['templates'] ?? [];
        $perm_ai_modify = isset($_POST['perm_ai_modify']) ? 1 : 0;
        $perm_web_speech = isset($_POST['perm_web_speech']) ? 1 : 0;
        $perm_custom_profiles = isset($_POST['perm_custom_profiles']) ? 1 : 0;
        $perm_pdf_print = isset($_POST['perm_pdf_print']) ? 1 : 0;

        $orig_stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
        $orig_stmt->execute([$id]);
        $original = $orig_stmt->fetch();

        if ($original) {
            $is_bought = isPlanBought($original['name']);

            if ($is_bought) {
                if ($name !== $original['name']) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=name_update_blocked";</script>'; exit; }
                if ($duration < $original['duration_days']) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=duration_reduction_blocked";</script>'; exit; }
                if ($price < $original['price']) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=price_reduction_blocked";</script>'; exit; }
                if ($max_resumes < $original['max_resumes']) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=resumes_reduction_blocked";</script>'; exit; }
                if ($ai_credits < $original['ai_credits']) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=credits_reduction_blocked";</script>'; exit; }
                if ($original['access_paid_templates'] == 1 && $access_paid_templates == 0) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=templates_reduction_blocked";</script>'; exit; }
            }

            try {
                $stmt = $db->prepare("UPDATE plans SET name = ?, price = ?, duration_days = ?, access_paid_templates = ?, max_resumes = ?, ai_credits = ?, features = ?, perm_ai_modify = ?, perm_web_speech = ?, perm_custom_profiles = ?, perm_pdf_print = ? WHERE id = ?");
                $stmt->execute([$name, $price, $duration, $access_paid_templates, $max_resumes, $ai_credits, $features, $perm_ai_modify, $perm_web_speech, $perm_custom_profiles, $perm_pdf_print, $id]);

                // Update plan-templates associations
                $db->prepare("DELETE FROM plan_templates WHERE plan_id = ?")->execute([$id]);
                if ($access_paid_templates && !empty($selected_templates)) {
                    $ins_pt = $db->prepare("INSERT OR IGNORE INTO plan_templates (plan_id, template_id) VALUES (?, ?)");
                    foreach ($selected_templates as $tid) {
                        $ins_pt->execute([$id, intval($tid)]);
                    }
                }

                echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&updated=1";</script>';
                exit;
            } catch (Exception $e) {
                $error = "Failed to update plan configurations.";
            }
        }
    }

    if ($action === 'delete_plan') {
        $id = intval($_POST['id'] ?? 0);
        $orig_stmt = $db->prepare("SELECT name FROM plans WHERE id = ?");
        $orig_stmt->execute([$id]);
        $plan_name = $orig_stmt->fetchColumn();

        if ($plan_name) {
            if (isPlanBought($plan_name)) { echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&err=delete_blocked";</script>'; exit; }
            $db->prepare("DELETE FROM plan_templates WHERE plan_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM plans WHERE id = ?")->execute([$id]);
            echo '<script>window.location.href = "?page=admin_dashboard&tab=plans&deleted=1";</script>';
            exit;
        }
    }
}

// Fetch all plans and their template associations
$plans = $db->query("SELECT * FROM plans ORDER BY price ASC")->fetchAll();
$all_paid_templates = $db->query("SELECT * FROM templates WHERE type = 'Paid' ORDER BY name ASC")->fetchAll();
$all_free_templates = $db->query("SELECT * FROM templates WHERE type = 'Free' ORDER BY name ASC")->fetchAll();

$plan_templates_map = [];
$pt_rows = $db->query("SELECT plan_id, template_id FROM plan_templates")->fetchAll();
foreach ($pt_rows as $pt) {
    $plan_templates_map[$pt['plan_id']][] = $pt['template_id'];
}
?>
<div class="flex flex-col gap-8">

  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="font-headline-lg text-headline-lg text-on-surface">Subscription Plans Manager</h1>
      <p class="text-on-surface-variant font-body-md mt-1">Create tiers, check which permissions and paid templates users get, and protect active subscriptions.</p>
    </div>
    <button onclick="openCreatePlanModal()" class="bg-primary text-on-primary hover:opacity-90 px-5 py-2.5 rounded-xl font-label-md text-xs font-bold transition-all active:scale-95 shadow flex items-center gap-1.5 border border-primary/10">
      <span class="material-symbols-outlined text-xs">add</span> Create New Plan
    </button>
  </div>

  <!-- Business Rules Warning Banner -->
  <div class="p-4 bg-surface-container-low border border-outline-variant/30 rounded-2xl text-xs flex gap-3 items-start text-on-surface-variant">
    <span class="material-symbols-outlined text-primary text-base">info</span>
    <div>
      <span class="font-bold text-on-surface">SAAS Business Protection Rules Enabled:</span>
      <p class="mt-0.5 leading-relaxed">If a subscription tier has active buyers (e.g. <strong>Pro Career Growth</strong>), platform deletion, tier name changes, price drops, shortening of validity duration, lowering of resume limits, or reducing AI credits are strictly blocked to protect continuous service integrity.</p>
    </div>
  </div>

  <!-- Notification & Error Alerts -->
  <?php if (isset($_GET['created'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm text-xs font-bold">
      <span class="material-symbols-outlined text-sm">check_circle</span> New billing plan created successfully!
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['updated'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm text-xs font-bold">
      <span class="material-symbols-outlined text-sm">check_circle</span> Plan details adjusted and saved.
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm text-xs font-bold">
      <span class="material-symbols-outlined text-sm">delete</span> Subscription plan deleted.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['err'])): ?>
    <?php
      $err = $_GET['err'];
      $err_msg = "A business rule validation occurred.";
      $map = [
          'name_update_blocked' => '<strong>Name Update Blocked:</strong> Tier name changes are restricted for active subscription plans to protect invoicing consistency.',
          'duration_reduction_blocked' => '<strong>Duration Reduction Blocked:</strong> You cannot shorten the duration days of an active plan. Existing subscribers are entitled to their full term.',
          'price_reduction_blocked' => '<strong>Price Reduction Blocked:</strong> Downgrading prices on active plans with active recurring customers is restricted. Please design a new plan instead.',
          'delete_blocked' => '<strong>Deletion Blocked:</strong> Deleting plans that have active subscribers is strictly prohibited to prevent immediate service disruption.',
          'resumes_reduction_blocked' => '<strong>Max Resumes Reduction Blocked:</strong> Lowering the allowed resume count on active bought plans is blocked to prevent breaking user limits.',
          'credits_reduction_blocked' => '<strong>AI Credits Reduction Blocked:</strong> Shortening or dropping monthly AI optimization tokens is blocked for plans with active buyers.',
          'templates_reduction_blocked' => '<strong>Revoke Premium Templates Blocked:</strong> Restricting access to paid layouts on a tier that originally included them is protected.',
      ];
      $err_msg = $map[$err] ?? $err_msg;
    ?>
    <div class="p-4 bg-error-container text-on-error-container rounded-xl flex items-start gap-2.5 animate-fade-in border border-error/15 shadow-sm text-xs">
      <span class="material-symbols-outlined text-base mt-0.5">warning</span>
      <p class="leading-relaxed"><?= $err_msg ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="p-4 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 animate-fade-in border border-error/15 text-xs font-bold">
      <span class="material-symbols-outlined text-sm">warning</span> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- Plan Cards Grid -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ($plans as $p):
      $is_bought = isPlanBought($p['name']);
      $accent_cls = $is_bought ? 'border-2 border-primary shadow-lg scale-[1.02]' : 'border border-outline-variant/30 shadow-sm';
      $plan_tpl_ids = $plan_templates_map[$p['id']] ?? [];
      $plan_paid_templates = array_filter($all_paid_templates, fn($t) => in_array($t['id'], $plan_tpl_ids));
    ?>
      <div class="bg-surface-container-lowest p-6 rounded-2xl flex flex-col gap-5 relative overflow-hidden <?= $accent_cls ?>">
        <?php if ($is_bought): ?>
          <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full text-[9px] font-extrabold uppercase tracking-wider shadow">
            Active Subscribers Locked
          </div>
        <?php endif; ?>

        <div>
          <div class="flex items-center justify-between gap-2">
            <h3 class="font-headline-md text-base text-on-surface font-extrabold truncate"><?= htmlspecialchars($p['name']) ?></h3>
            <?php if ($is_bought): ?>
              <span class="material-symbols-outlined text-primary text-sm" title="Active subscribers inside. Rules locked.">lock</span>
            <?php endif; ?>
          </div>
          <p class="text-2xl font-extrabold text-primary mt-2">
            <?= $currency_symbol ?><?= number_format($p['price'], 2) ?>
            <span class="text-xs text-on-surface-variant font-medium">/ <?= $p['duration_days'] ?> days</span>
          </p>
        </div>

        <!-- Dynamic Structural Attributes Metrics -->
        <div class="grid grid-cols-2 gap-2 bg-surface/40 p-3 rounded-xl border border-outline-variant/10 text-[10px] font-bold text-on-surface-variant">
          <div class="flex items-center gap-1">
            <span class="material-symbols-outlined text-primary text-sm">event_note</span>
            <span><?= $p['duration_days'] ?> Days</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="material-symbols-outlined text-primary text-sm">article</span>
            <span><?= $p['max_resumes'] > 5000 ? 'Unlimited' : $p['max_resumes'] . ' Resumes' ?></span>
          </div>
          <div class="flex items-center gap-1 col-span-2 border-t border-outline-variant/10 pt-1.5 mt-0.5">
            <span class="material-symbols-outlined text-primary text-sm">auto_awesome</span>
            <span><?= $p['ai_credits'] ?> AI Credits</span>
          </div>
        </div>

        <!-- Permission Checkboxes Summary -->
        <div class="flex flex-col gap-1.5 text-[10px] text-on-surface-variant">
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[14px] <?= !empty($p['perm_ai_modify']) ? 'text-emerald-600' : 'text-outline' ?>"><?= !empty($p['perm_ai_modify']) ? 'check_box' : 'check_box_outline_blank' ?></span>
            <span>AI Modify Widget</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[14px] <?= !empty($p['perm_web_speech']) ? 'text-emerald-600' : 'text-outline' ?>"><?= !empty($p['perm_web_speech']) ? 'check_box' : 'check_box_outline_blank' ?></span>
            <span>Web Speech / Voice Modify</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[14px] <?= !empty($p['perm_custom_profiles']) ? 'text-emerald-600' : 'text-outline' ?>"><?= !empty($p['perm_custom_profiles']) ? 'check_box' : 'check_box_outline_blank' ?></span>
            <span>Custom Job Profiles</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[14px] <?= !empty($p['perm_pdf_print']) ? 'text-emerald-600' : 'text-outline' ?>"><?= !empty($p['perm_pdf_print']) ? 'check_box' : 'check_box_outline_blank' ?></span>
            <span>PDF Print / Download</span>
          </div>
        </div>

        <!-- Paid Templates Included -->
        <?php if (!empty($p['access_paid_templates'])): ?>
        <div class="bg-amber-50/50 border border-amber-200/50 rounded-xl p-3">
          <p class="text-[9px] font-extrabold text-amber-800 uppercase tracking-wider mb-2 flex items-center gap-1">
            <span class="material-symbols-outlined text-[12px]">workspace_premium</span> Paid Templates Included
          </p>
          <?php if (empty($plan_paid_templates)): ?>
            <p class="text-[10px] text-amber-700 italic">No specific templates assigned (all paid allowed)</p>
          <?php else: ?>
            <div class="flex flex-wrap gap-1.5">
              <?php foreach ($plan_paid_templates as $pt): ?>
                <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[9px] font-bold border border-amber-200 flex items-center gap-1">
                  <span class="material-symbols-outlined text-[10px]"><?= htmlspecialchars($pt['icon']) ?></span>
                  <?= htmlspecialchars($pt['name']) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Action grid -->
        <div class="flex gap-2 border-t border-outline-variant/10 pt-4 mt-2">
          <button onclick='openEditPlanModal(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($plan_tpl_ids) ?>, <?= $is_bought ? "true" : "false" ?>)' class="flex-1 bg-secondary-container text-on-secondary-container hover:opacity-90 py-2.5 rounded-xl font-label-md text-xs font-bold transition-all active:scale-95 text-center flex items-center justify-center gap-1">
            <span class="material-symbols-outlined text-xs">edit</span> Edit Plan
          </button>

          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?')" class="m-0">
            <input type="hidden" name="action" value="delete_plan">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">

            <?php if ($is_bought): ?>
              <button type="button" onclick="alert('Deletion Blocked: You cannot delete a plan that has active recurring subscribers.')" class="p-2.5 rounded-xl bg-outline-variant/30 text-on-surface-variant/40 cursor-not-allowed" title="Deletion locked by business protection rules">
                <span class="material-symbols-outlined text-xs">block</span>
              </button>
            <?php else: ?>
              <button type="submit" class="p-2.5 rounded-xl bg-error/10 hover:bg-error hover:text-on-error text-error transition-all active:scale-95" title="Delete plan layout">
                <span class="material-symbols-outlined text-xs">delete</span>
              </button>
            <?php endif; ?>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- â”€â”€ MODAL: Create Plan â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
<div id="createPlanModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-2xl rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col max-h-[92vh]">
    <div class="flex items-center justify-between border-b border-outline-variant/15 p-6 pb-3 flex-shrink-0">
      <h3 class="font-headline-md text-base font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">add_circle</span> Create Subscription Plan
      </h3>
      <button onclick="closeCreatePlanModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-variant/40 transition-all">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <form method="POST" class="flex flex-col flex-1 overflow-hidden m-0">
      <input type="hidden" name="action" value="create_plan">
      <div class="p-6 overflow-y-auto flex-1 flex flex-col gap-5">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-3">
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Plan Name <span class="text-error">*</span></label>
            <input type="text" name="name" placeholder="e.g. Ultra Job Pro" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold">
          </div>
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Price (<?= $currency_symbol ?>)</label>
            <input type="number" step="0.01" name="price" value="0.00" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold">
          </div>
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Duration (Days)</label>
            <input type="number" name="duration_days" value="30" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold">
          </div>
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">AI Credits</label>
            <input type="number" name="ai_credits" value="10" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold">
          </div>
        </div>

        <!-- Permissions & Features Checkboxes -->
        <div class="bg-surface/30 p-4 rounded-xl border border-outline-variant/15 flex flex-col gap-3">
          <span class="block font-label-md text-primary font-extrabold text-[10px] uppercase tracking-wider">Plan Permissions & Facilities</span>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="access_paid_templates" id="create-access-templates" onchange="toggleTemplateSection('create')" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">Paid Template Access</span>
                <span class="block text-[9px] text-on-surface-variant">Allow premium layouts below</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="perm_ai_modify" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">AI Modify Widget</span>
                <span class="block text-[9px] text-on-surface-variant">Refine resumes via AI text/voice</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="perm_web_speech" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">Web Speech / Voice</span>
                <span class="block text-[9px] text-on-surface-variant">Browser voice recognition</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="perm_custom_profiles" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">Custom Job Profiles</span>
                <span class="block text-[9px] text-on-surface-variant">User can add custom roles</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg sm:col-span-2">
              <input type="checkbox" name="perm_pdf_print" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">PDF Print / Download</span>
                <span class="block text-[9px] text-on-surface-variant">Browser-native print to PDF</span>
              </div>
            </label>
          </div>

          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Max Resumes Limit</label>
            <input type="number" name="max_resumes" value="2" required class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold">
          </div>
        </div>

        <!-- Template Selection Checkboxes (toggled by access_paid_templates) -->
        <div id="create-template-section" class="bg-amber-50/40 p-4 rounded-xl border border-amber-200/50 flex-col gap-2 hidden">
          <span class="block font-label-md text-amber-800 font-extrabold text-[10px] uppercase tracking-wider flex items-center gap-1">
            <span class="material-symbols-outlined text-[12px]">workspace_premium</span> Select which paid templates this plan includes
          </span>
          <p class="text-[10px] text-amber-700/80 mb-1">Check the templates users on this plan can access. Unchecked = not available.</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
            <?php foreach ($all_paid_templates as $pt): ?>
            <label class="flex items-start gap-2.5 cursor-pointer py-2 px-2.5 hover:bg-amber-100/50 rounded-lg border border-amber-200/40 bg-surface">
              <input type="checkbox" name="templates[]" value="<?= $pt['id'] ?>" class="w-4 h-4 mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-outline-variant">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[14px]" style="color: <?= htmlspecialchars($pt['accent_color']) ?>;"><?= htmlspecialchars($pt['icon']) ?></span>
                  <span class="text-xs font-bold text-on-surface truncate"><?= htmlspecialchars($pt['name']) ?></span>
                </div>
                <span class="block text-[9px] text-on-surface-variant line-clamp-2 mt-0.5"><?= htmlspecialchars($pt['description']) ?></span>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Features list (comma separated)</label>
          <textarea name="features" rows="2" placeholder="Unlimited Resumes, Premium styling highlights, Remove watermark..." required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none font-semibold"></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-3 p-6 pt-4 border-t border-outline-variant/15 flex-shrink-0">
        <button type="button" onclick="closeCreatePlanModal()" class="px-4 py-2 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-xs font-semibold text-on-surface">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-on-primary hover:opacity-90 font-label-md text-xs font-bold shadow-md active:scale-95 transition-all">
          Save Plan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- â”€â”€ MODAL: Edit Plan â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
<div id="editPlanModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-2xl rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col max-h-[92vh]">
    <div class="flex items-center justify-between border-b border-outline-variant/15 p-6 pb-3 flex-shrink-0">
      <h3 class="font-headline-md text-base font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">edit</span> Adjust Subscription Plan
      </h3>
      <button onclick="closeEditPlanModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-variant/40 transition-all">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <div id="edit-protection-banner" class="hidden mx-6 mt-4 p-3 bg-amber-50 text-amber-800 rounded-xl items-start gap-2 border border-amber-200 text-[10px] leading-relaxed">
      <span class="material-symbols-outlined text-xs mt-0.5">lock</span>
      <p>This plan has active subscribers. Business rules are active: Plan name, pricing drops, validity, credit count reductions, and layout permissions are locked.</p>
    </div>

    <form method="POST" class="flex flex-col flex-1 overflow-hidden m-0">
      <input type="hidden" name="action" value="update_plan">
      <input type="hidden" name="id" id="edit-id">
      <div class="p-6 overflow-y-auto flex-1 flex flex-col gap-5">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-3">
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Plan Name</label>
            <input type="text" name="name" id="edit-name" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold transition-all">
          </div>
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Price (<?= $currency_symbol ?>)</label>
            <input type="number" step="0.01" name="price" id="edit-price" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold transition-all">
          </div>
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Duration (Days)</label>
            <input type="number" name="duration_days" id="edit-duration" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold transition-all">
          </div>
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">AI Credits</label>
            <input type="number" name="ai_credits" id="edit-ai-credits" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold transition-all">
          </div>
        </div>

        <!-- Permissions & Features Checkboxes -->
        <div class="bg-surface/30 p-4 rounded-xl border border-outline-variant/15 flex flex-col gap-3">
          <span class="block font-label-md text-primary font-extrabold text-[10px] uppercase tracking-wider">Plan Permissions & Facilities</span>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="access_paid_templates" id="edit-access-templates" onchange="toggleTemplateSection('edit')" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">Paid Template Access</span>
                <span class="block text-[9px] text-on-surface-variant">Allow premium layouts below</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="perm_ai_modify" id="edit-perm-ai" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">AI Modify Widget</span>
                <span class="block text-[9px] text-on-surface-variant">Refine resumes via AI text/voice</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="perm_web_speech" id="edit-perm-speech" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">Web Speech / Voice</span>
                <span class="block text-[9px] text-on-surface-variant">Browser voice recognition</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg">
              <input type="checkbox" name="perm_custom_profiles" id="edit-perm-profiles" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">Custom Job Profiles</span>
                <span class="block text-[9px] text-on-surface-variant">User can add custom roles</span>
              </div>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 hover:bg-surface/50 rounded-lg sm:col-span-2">
              <input type="checkbox" name="perm_pdf_print" id="edit-perm-pdf" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant">
              <div>
                <span class="block text-xs font-bold text-on-surface">PDF Print / Download</span>
                <span class="block text-[9px] text-on-surface-variant">Browser-native print to PDF</span>
              </div>
            </label>
          </div>

          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Max Resumes Limit</label>
            <input type="number" name="max_resumes" id="edit-max-resumes" required class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold">
          </div>
        </div>

        <!-- Template Selection Checkboxes -->
        <div id="edit-template-section" class="bg-amber-50/40 p-4 rounded-xl border border-amber-200/50 flex-col gap-2 hidden">
          <span class="block font-label-md text-amber-800 font-extrabold text-[10px] uppercase tracking-wider flex items-center gap-1">
            <span class="material-symbols-outlined text-[12px]">workspace_premium</span> Select which paid templates this plan includes
          </span>
          <p class="text-[10px] text-amber-700/80 mb-1">Check the templates users on this plan can access.</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
            <?php foreach ($all_paid_templates as $pt): ?>
            <label class="flex items-start gap-2.5 cursor-pointer py-2 px-2.5 hover:bg-amber-100/50 rounded-lg border border-amber-200/40 bg-surface">
              <input type="checkbox" name="templates[]" value="<?= $pt['id'] ?>" class="edit-tpl-cb w-4 h-4 mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-outline-variant" data-tpl-id="<?= $pt['id'] ?>">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[14px]" style="color: <?= htmlspecialchars($pt['accent_color']) ?>;"><?= htmlspecialchars($pt['icon']) ?></span>
                  <span class="text-xs font-bold text-on-surface truncate"><?= htmlspecialchars($pt['name']) ?></span>
                </div>
                <span class="block text-[9px] text-on-surface-variant line-clamp-2 mt-0.5"><?= htmlspecialchars($pt['description']) ?></span>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Features list (comma separated)</label>
          <textarea name="features" id="edit-features" rows="2" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none font-semibold"></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-3 p-6 pt-4 border-t border-outline-variant/15 flex-shrink-0">
        <button type="button" onclick="closeEditPlanModal()" class="px-4 py-2 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-xs font-semibold text-on-surface">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-on-primary hover:opacity-90 font-label-md text-xs font-bold shadow-md active:scale-95 transition-all">
          Apply Updates
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openCreatePlanModal() {
  document.getElementById('createPlanModal').classList.remove('hidden');
  document.getElementById('createPlanModal').classList.add('flex');
  document.body.classList.add('overflow-hidden');
}
function closeCreatePlanModal() {
  document.getElementById('createPlanModal').classList.add('hidden');
  document.getElementById('createPlanModal').classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
}
function openEditPlanModal(plan, tplIds, isBought) {
  document.getElementById('edit-id').value = plan.id;
  document.getElementById('edit-name').value = plan.name;
  document.getElementById('edit-price').value = parseFloat(plan.price).toFixed(2);
  document.getElementById('edit-duration').value = plan.duration_days;
  document.getElementById('edit-ai-credits').value = plan.ai_credits;
  document.getElementById('edit-features').value = plan.features;
  document.getElementById('edit-max-resumes').value = plan.max_resumes;
  document.getElementById('edit-access-templates').checked = parseInt(plan.access_paid_templates) === 1;
  document.getElementById('edit-perm-ai').checked = parseInt(plan.perm_ai_modify) === 1;
  document.getElementById('edit-perm-speech').checked = parseInt(plan.perm_web_speech) === 1;
  document.getElementById('edit-perm-profiles').checked = parseInt(plan.perm_custom_profiles) === 1;
  document.getElementById('edit-perm-pdf').checked = parseInt(plan.perm_pdf_print) === 1;

  // Pre-check associated templates
  document.querySelectorAll('.edit-tpl-cb').forEach(cb => cb.checked = false);
  (tplIds || []).forEach(id => {
    const cb = document.querySelector(`.edit-tpl-cb[data-tpl-id="${id}"]`);
    if (cb) cb.checked = true;
  });

  toggleTemplateSection('edit');

  const banner = document.getElementById('edit-protection-banner');
  const nameInput = document.getElementById('edit-name');
  const priceInput = document.getElementById('edit-price');
  const durationInput = document.getElementById('edit-duration');
  const accessCheckbox = document.getElementById('edit-access-templates');
  const maxResumesInput = document.getElementById('edit-max-resumes');
  const aiCreditsInput = document.getElementById('edit-ai-credits');

  if (isBought) {
    banner.classList.remove('hidden');
    banner.classList.add('flex');
    nameInput.setAttribute('readonly', 'readonly');
    nameInput.className = 'w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface-variant/45 text-xs text-on-surface-variant/70 cursor-not-allowed font-semibold';
    if (parseInt(plan.access_paid_templates) === 1) {
      accessCheckbox.setAttribute('onclick', 'return false;');
    } else {
      accessCheckbox.removeAttribute('onclick');
    }
    priceInput.setAttribute('min', plan.price);
    durationInput.setAttribute('min', plan.duration_days);
    maxResumesInput.setAttribute('min', plan.max_resumes);
    aiCreditsInput.setAttribute('min', plan.ai_ai_credits || plan.ai_credits);
  } else {
    banner.classList.add('hidden');
    banner.classList.remove('flex');
    nameInput.removeAttribute('readonly');
    nameInput.className = 'w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold';
    accessCheckbox.removeAttribute('onclick');
    priceInput.removeAttribute('min');
    durationInput.removeAttribute('min');
    maxResumesInput.removeAttribute('min');
    aiCreditsInput.removeAttribute('min');
  }

  document.getElementById('editPlanModal').classList.remove('hidden');
  document.getElementById('editPlanModal').classList.add('flex');
  document.body.classList.add('overflow-hidden');
}
function closeEditPlanModal() {
  document.getElementById('editPlanModal').classList.add('hidden');
  document.getElementById('editPlanModal').classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
}

function toggleTemplateSection(mode) {
  const cb = document.getElementById(mode + '-access-templates');
  const section = document.getElementById(mode + '-template-section');
  if (cb && section) {
    if (cb.checked) {
      section.classList.remove('hidden');
      section.classList.add('flex');
    } else {
      section.classList.add('hidden');
      section.classList.remove('flex');
    }
  }
}
</script>
