<?php
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
?>
<aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container-low dark:bg-inverse-surface shadow-md flex flex-col p-4 gap-2 z-50 overflow-y-auto">
<div class="mb-4 px-2 shrink-0">
<h1 class="font-headline-md text-headline-md font-bold text-primary">Admin Panel</h1>
<p class="font-label-sm text-label-sm text-on-surface-variant">Management Console</p>
</div>
<nav class="flex flex-col gap-1 flex-1 overflow-y-auto min-h-0">
<a class="<?php echo $tab === 'payments' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=payments">
<span class="material-symbols-outlined">receipt_long</span>
<span class="font-label-md">Payments</span>
</a>
<a class="<?php echo $tab === 'users' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=users">
<span class="material-symbols-outlined">group</span>
<span class="font-label-md">Users</span>
</a>
<a class="<?php echo $tab === 'plans' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=plans">
<span class="material-symbols-outlined">card_membership</span>
<span class="font-label-md">Plans</span>
</a>
<a class="<?php echo $tab === 'templates' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=templates">
<span class="material-symbols-outlined">storefront</span>
<span class="font-label-md">Template Store</span>
</a>
<a class="<?php echo $tab === 'tickets' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=tickets">
<span class="material-symbols-outlined">support_agent</span>
<span class="font-label-md">Support Tickets</span>
</a>
<a class="<?php echo $tab === 'notifications' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=notifications">
<span class="material-symbols-outlined">campaign</span>
<span class="font-label-md">Notifications</span>
</a>
<a class="<?php echo $tab === 'settings' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant'; ?> flex items-center gap-3 p-3 rounded-lg transition-all" href="?page=admin_dashboard&tab=settings">
<span class="material-symbols-outlined">settings</span>
<span class="font-label-md">Settings</span>
</a>
</nav>
<div class="mt-auto border-t border-outline-variant pt-4 flex flex-col gap-1 shrink-0">
<?php
$dev_enabled = $db->query("SELECT value FROM settings WHERE key = 'dev_login_enabled'")->fetchColumn();
if ($dev_enabled): ?>
<div class="relative">
  <button id="admin-dev-toggle" onclick="document.getElementById('admin-dev-dropdown').classList.toggle('hidden')" class="w-full flex items-center gap-3 p-3 text-on-surface-variant hover:bg-surface-variant rounded-lg transition-colors">
    <span class="material-symbols-outlined">terminal</span>
    <span class="font-label-md">Dev Login</span>
    <span class="material-symbols-outlined text-sm ml-auto">expand_more</span>
  </button>
  <div id="admin-dev-dropdown" class="hidden absolute bottom-full left-0 right-0 mb-1 bg-surface-container-lowest border border-outline-variant/50 rounded-xl shadow-lg overflow-hidden max-h-64 overflow-y-auto z-50">
    <?php
    require_once __DIR__ . '/../../sqlite/db.php';
    $allUsers = $db->query("SELECT id, email, first_name, last_name FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allUsers as $u):
    ?>
    <form method="POST" action="?page=auth" class="border-b border-outline-variant/20 last:border-b-0">
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="email" value="<?= htmlspecialchars($u['email']) ?>">
      <input type="hidden" name="password" value="devmode_bypass">
      <button type="submit" class="w-full px-3 py-2.5 text-left hover:bg-primary-container/30 transition-colors flex items-center gap-2">
        <div class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center text-[10px] font-bold flex-shrink-0">
          <?= strtoupper(substr($u['first_name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="flex flex-col min-w-0">
          <span class="font-label-md text-on-surface text-[11px] font-bold truncate"><?= htmlspecialchars($u['email']) ?></span>
          <span class="text-[9px] text-on-surface-variant font-medium truncate"><?= htmlspecialchars(($u['first_name'] ?? '').' '.($u['last_name'] ?? '')) ?></span>
        </div>
      </button>
    </form>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<a class="text-on-surface-variant flex items-center gap-3 p-3 hover:bg-surface-variant rounded-lg transition-colors" href="?page=auth">
<span class="material-symbols-outlined">logout</span>
<span class="font-label-md">Logout</span>
</a>
</div>
</aside>
