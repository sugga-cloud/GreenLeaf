<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
Auth::start_session();
$user_id = Auth::user_id();
$stmt = $db->prepare("SELECT * FROM profile_academics WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();
?>
<div id="academics-list" class="flex flex-col gap-4 mb-6">
<?php if(empty($rows)): ?>
<p class="text-on-surface-variant font-body-md text-center py-8 border-2 border-dashed border-outline-variant rounded-xl" id="empty-msg">No academic records yet. Add one below.</p>
<?php else: foreach($rows as $r): ?>
<div class="bg-surface border border-outline-variant/40 rounded-xl p-5 flex justify-between items-start gap-4">
  <div>
    <p class="font-label-md text-on-surface"><?= htmlspecialchars($r['degree']) ?> â€” <?= htmlspecialchars($r['institution']) ?></p>
    <p class="text-label-sm text-on-surface-variant mt-1"><?= htmlspecialchars($r['board_university']) ?> | <?= $r['start_year'] ?> â€“ <?= $r['end_year'] ?> | Grade: <?= htmlspecialchars($r['grade']) ?></p>
    <?php if($r['description']): ?><p class="text-body-md text-on-surface-variant mt-2 text-sm"><?= htmlspecialchars($r['description']) ?></p><?php endif; ?>
  </div>
  <form method="POST" action="?page=profile&step=2">
    <input type="hidden" name="action" value="delete_academic">
    <input type="hidden" name="id" value="<?= $r['id'] ?>">
    <button type="submit" class="text-error p-2 hover:bg-error-container rounded-lg transition-colors"><span class="material-symbols-outlined text-sm">delete</span></button>
  </form>
</div>
<?php endforeach; endif; ?>
</div>

<div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30">
  <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Academic Record</h4>
  <form method="POST" action="?page=profile&step=2">
  <input type="hidden" name="action" value="add_academic">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block font-label-md text-on-surface-variant mb-1">Degree / Certificate</label>
      <input name="degree" placeholder="B.Tech, 12th, Diploma..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    </div>
    <div>
      <label class="block font-label-md text-on-surface-variant mb-1">Institution</label>
      <input name="institution" placeholder="College / School name" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    </div>
    <div>
      <label class="block font-label-md text-on-surface-variant mb-1">Board / University</label>
      <input name="board_university" placeholder="CBSE, Mumbai University..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    </div>
    <div>
      <label class="block font-label-md text-on-surface-variant mb-1">Grade / CGPA / %</label>
      <input name="grade" placeholder="8.5 / 90%" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    </div>
    <div>
      <label class="block font-label-md text-on-surface-variant mb-1">Start Year</label>
      <input name="start_year" type="number" min="1990" max="2030" placeholder="2020" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    </div>
    <div>
      <label class="block font-label-md text-on-surface-variant mb-1">End Year</label>
      <input name="end_year" type="number" min="1990" max="2030" placeholder="2024" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
    </div>
    <div class="md:col-span-2">
      <label class="block font-label-md text-on-surface-variant mb-1">Description (optional)</label>
      <textarea name="description" rows="2" placeholder="Key courses, achievements..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none"></textarea>
    </div>
  </div>
  <div class="flex justify-end mt-4">
    <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
      <span class="material-symbols-outlined text-sm">add</span> Add Record
    </button>
  </div>
  </form>
</div>
