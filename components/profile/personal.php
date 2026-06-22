<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'Auth.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
Auth::start_session();
$user_id = Auth::user_id();
$stmt = $db->prepare("SELECT * FROM profile_personal WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$p = $stmt->fetch() ?: [];
?>
<form method="POST" action="?page=profile&step=1">
<input type="hidden" name="action" value="save_personal">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="md:col-span-2">
    <label class="block font-label-md text-on-surface-variant mb-1">Full Name</label>
    <input name="full_name" value="<?= htmlspecialchars($p['full_name']??'') ?>" placeholder="John Doe" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">Email</label>
    <input name="email" type="email" value="<?= htmlspecialchars($p['email']??'') ?>" placeholder="you@email.com" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">Phone</label>
    <input name="phone" value="<?= htmlspecialchars($p['phone']??'') ?>" placeholder="+91 9876543210" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">Date of Birth</label>
    <input name="dob" type="date" value="<?= htmlspecialchars($p['dob']??'') ?>" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">Gender</label>
    <select name="gender" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
      <option value="">Select gender</option>
      <?php foreach(['Male','Female','Non-binary','Prefer not to say'] as $g): ?>
      <option value="<?=$g?>" <?= ($p['gender']??'')===$g?'selected':'' ?>><?=$g?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">Nationality</label>
    <input name="nationality" value="<?= htmlspecialchars($p['nationality']??'') ?>" placeholder="Indian" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">City</label>
    <input name="city" value="<?= htmlspecialchars($p['city']??'') ?>" placeholder="Mumbai" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div class="md:col-span-2">
    <label class="block font-label-md text-on-surface-variant mb-1">Address</label>
    <input name="address" value="<?= htmlspecialchars($p['address']??'') ?>" placeholder="Street, Area" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">LinkedIn URL</label>
    <input name="linkedin" value="<?= htmlspecialchars($p['linkedin']??'') ?>" placeholder="https://linkedin.com/in/..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div>
    <label class="block font-label-md text-on-surface-variant mb-1">GitHub URL</label>
    <input name="github" value="<?= htmlspecialchars($p['github']??'') ?>" placeholder="https://github.com/..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div class="md:col-span-2">
    <label class="block font-label-md text-on-surface-variant mb-1">Portfolio URL</label>
    <input name="portfolio" value="<?= htmlspecialchars($p['portfolio']??'') ?>" placeholder="https://yourportfolio.com" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40">
  </div>
  <div class="md:col-span-2">
    <label class="block font-label-md text-on-surface-variant mb-1">Professional Summary</label>
    <textarea name="summary" rows="4" placeholder="A brief about yourself..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none"><?= htmlspecialchars($p['summary']??'') ?></textarea>
  </div>
</div>
<div class="flex justify-end mt-8">
  <button type="submit" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
    <span class="material-symbols-outlined text-sm">save</span> Save & Continue
  </button>
</div>
</form>
