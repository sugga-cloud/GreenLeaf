<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
Auth::start_session();
$user_id = Auth::user_id();
$stmt = $db->prepare("SELECT * FROM profile_hobbies WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();
?>
<div id="hobbies-list" class="flex flex-wrap gap-3 min-h-[48px] mb-6">
<?php if(empty($rows)): ?>
<p class="text-on-surface-variant font-body-md w-full text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No hobbies yet. Add some below.</p>
<?php else: foreach($rows as $r): ?>
<div class="flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-2 rounded-full font-label-md">
  <span><?= htmlspecialchars($r['hobby']) ?></span>
  <form method="POST" action="?page=profile&step=7" class="m-0 inline">
    <input type="hidden" name="action" value="delete_hobby">
    <input type="hidden" name="id" value="<?= $r['id'] ?>">
    <button type="submit" class="ml-1 hover:opacity-70 transition-opacity"><span class="material-symbols-outlined" style="font-size:14px">close</span></button>
  </form>
</div>
<?php endforeach; endif; ?>
</div>

<div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30">
  <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Hobby / Interest</h4>
  <form method="POST" action="?page=profile&step=7" class="flex gap-3">
    <input type="hidden" name="action" value="add_hobby">
    <input name="hobby" placeholder="Photography, Hiking, Chess..." class="flex-1 border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2 whitespace-nowrap">
      <span class="material-symbols-outlined text-sm">add</span> Add
    </button>
  </form>
</div>
