<!-- SideNavBar Shell -->
<aside class="h-screen w-64 fixed left-0 top-0 bg-surface-container-low flex flex-col p-4 gap-2 shadow-md z-40 hidden md:flex overflow-y-auto no-print">
<div class="mb-4 px-2 shrink-0">
<h1 class="font-headline-md text-headline-md font-bold text-primary">User Dashboard</h1>
<p class="text-on-surface-variant font-label-sm">Career Hub</p>
</div>
<nav class="flex-1 flex flex-col gap-2 overflow-y-auto min-h-0">
<?php
$cur_page = $_GET['page'] ?? 'user_dashboard';
$nav_items = [
    ['href'=>'?page=user_dashboard', 'icon'=>'dashboard',    'label'=>'Overview',    'page'=>'user_dashboard'],
    ['href'=>'?page=profile',        'icon'=>'person',       'label'=>'My Profile',  'page'=>'profile'],
    ['href'=>'?page=select_job_profile', 'icon'=>'work',     'label'=>'Jobs',        'page'=>'select_job_profile'],
    ['href'=>'?page=resumes',        'icon'=>'description',  'label'=>'My Resumes',  'page'=>'resumes'],
    ['href'=>'?page=template_store', 'icon'=>'storefront',   'label'=>'Template Store', 'page'=>'template_store'],
];
foreach($nav_items as $item):
  $active = $cur_page === $item['page'];
?>
<a class="<?= $active ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant' ?> rounded-lg flex items-center gap-3 p-3 active:scale-[0.98] transition-all" href="<?= $item['href'] ?>">
<span class="material-symbols-outlined" data-icon="<?= $item['icon'] ?>"><?= $item['icon'] ?></span>
<span><?= $item['label'] ?></span>
</a>
<?php endforeach; ?>
<a class="<?= $cur_page === 'settings' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant' ?> rounded-lg flex items-center gap-3 p-3 active:scale-[0.98] transition-all" href="?page=settings">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
<span>Settings</span>
</a>
</nav>
<div class="mt-auto border-t border-outline-variant pt-4 flex flex-col gap-2 shrink-0">
<a href="?page=plan" class="w-full text-center <?= $cur_page === 'plan' ? 'bg-primary-container text-on-primary-container font-bold border border-primary/20' : 'bg-primary text-on-primary' ?> font-label-md py-3 rounded-lg shadow-sm active:scale-95 transition-all block hover:opacity-90">Plan & Billing</a>
<a class="<?= $cur_page === 'support' ? 'bg-primary-container text-on-primary-container font-bold' : 'text-on-surface-variant hover:bg-surface-variant' ?> rounded-lg flex items-center gap-3 p-3 active:scale-[0.98] transition-all" href="?page=support">
<span class="material-symbols-outlined" data-icon="help">help</span>
<span>Support</span>
</a>
<a class="text-on-surface-variant flex items-center gap-3 p-3 hover:bg-error-container hover:text-on-error-container rounded-lg transition-colors font-semibold text-xs" href="?action=logout">
<span class="material-symbols-outlined" data-icon="logout">logout</span>
<span>Logout</span>
</a>
</div>
</aside>
