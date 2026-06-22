<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'AIService.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_template') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'Free';
        $accent = trim($_POST['accent'] ?? '#006C49');
        $icon = trim($_POST['icon'] ?? 'description');
        $image = trim($_POST['image'] ?? '');

        if (!empty($name) && !empty($description)) {
            try {
                $stmt = $db->prepare("INSERT INTO templates (name, description, type, accent_color, icon, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $type, $accent, $icon, $image]);
                echo '<script>window.location.href = "?page=admin_dashboard&tab=templates&created=1";</script>';
                exit;
            } catch (Exception $e) {
                $error = "Template name must be unique. Choose a different catalog name.";
            }
        } else {
            $error = "Name and description are required.";
        }
    }

    if ($action === 'update_template') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'Free';
        $accent = trim($_POST['accent'] ?? '#006C49');
        $icon = trim($_POST['icon'] ?? 'description');
        $image = trim($_POST['image'] ?? '');

        if ($id && !empty($name)) {
            $stmt = $db->prepare("UPDATE templates SET name = ?, description = ?, type = ?, accent_color = ?, icon = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$name, $description, $type, $accent, $icon, $image, $id]);
            echo '<script>window.location.href = "?page=admin_dashboard&tab=templates&updated=1";</script>';
            exit;
        }
    }

    if ($action === 'delete_template') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM plan_templates WHERE template_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM templates WHERE id = ?")->execute([$id]);
            echo '<script>window.location.href = "?page=admin_dashboard&tab=templates&deleted=1";</script>';
            exit;
        }
    }
}

// Fetch all templates
$templates = $db->query("SELECT * FROM templates ORDER BY type DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
$free_count = count(array_filter($templates, fn($t) => $t['type'] === 'Free'));
$paid_count = count(array_filter($templates, fn($t) => $t['type'] === 'Paid'));
?>
<div class="flex flex-col gap-8">

  <!-- Header -->
  <div>
    <h1 class="font-headline-lg text-headline-lg text-on-surface">Template Manager & Catalog</h1>
    <p class="text-on-surface-variant font-body-md mt-1">Create AI-powered resume layouts, label them Free or Paid, and assign them to subscription plans.</p>
  </div>

  <!-- Notification Alerts -->
  <?php if (isset($_GET['created'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
      <span class="material-symbols-outlined text-sm">add_circle</span>
      <span class="font-label-md text-xs font-bold">New template layout registered successfully!</span>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['updated'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm">
      <span class="material-symbols-outlined text-sm">edit</span>
      <span class="font-label-md text-xs font-bold">Template updated successfully!</span>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?>
    <div class="p-4 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 animate-fade-in border border-error/15 shadow-sm">
      <span class="material-symbols-outlined text-sm">delete</span>
      <span class="font-label-md text-xs font-bold">Template removed from catalog.</span>
    </div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="p-4 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 animate-fade-in border border-error/15 text-xs font-bold">
      <span class="material-symbols-outlined text-sm">warning</span> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- Stats Row -->
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
    <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
        <span class="material-symbols-outlined">grid_view</span>
      </div>
      <div>
        <p class="text-[10px] text-on-surface-variant uppercase tracking-wider font-bold">Total Templates</p>
        <p class="font-headline-md text-xl text-on-surface font-extrabold"><?= count($templates) ?></p>
      </div>
    </div>
    <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
        <span class="material-symbols-outlined">verified</span>
      </div>
      <div>
        <p class="text-[10px] text-on-surface-variant uppercase tracking-wider font-bold">Free Layouts</p>
        <p class="font-headline-md text-xl text-on-surface font-extrabold"><?= $free_count ?></p>
      </div>
    </div>
    <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
        <span class="material-symbols-outlined">workspace_premium</span>
      </div>
      <div>
        <p class="text-[10px] text-on-surface-variant uppercase tracking-wider font-bold">Premium Layouts</p>
        <p class="font-headline-md text-xl text-on-surface font-extrabold"><?= $paid_count ?></p>
      </div>
    </div>
  </div>

  <!-- AI Template Creator Section -->
  <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-6">
    <div class="flex items-center justify-between border-b border-outline-variant/15 pb-3">
      <h3 class="font-headline-md text-base text-on-surface font-bold flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">auto_awesome</span> AI Template Creator
      </h3>
      <span class="text-[10px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded-full">AI Powered</span>
    </div>

    <!-- AI Prompt Box -->
    <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex flex-col gap-3">
      <label class="block font-label-md text-primary font-extrabold text-xs flex items-center gap-1">
        <span class="material-symbols-outlined text-xs animate-pulse">auto_awesome</span> Describe your template vibe
      </label>
      <div class="flex gap-2">
        <input type="text" id="ai-template-prompt" placeholder="e.g. Futuristic Cyberpunk Developer, Midnight Royal Executive, Rose Gold Designer..." class="flex-1 border border-primary/30 rounded-xl px-4 py-2.5 bg-surface text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-primary/40">
        <button type="button" id="ai-generate-btn" onclick="generateTemplateWithAI()" class="bg-primary text-on-primary hover:opacity-90 px-5 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95 flex items-center gap-1.5 shadow-md">
          <span class="material-symbols-outlined text-xs" id="ai-gen-icon">auto_awesome</span> <span id="ai-gen-text">Generate</span>
        </button>
      </div>
      <p class="text-[10px] text-on-surface-variant leading-normal">The AI will create a complete template: name, description, accent color, icon, and Free/Paid label. You can review before saving.</p>
    </div>

    <!-- Manual / AI-Pre-filled Create Form -->
    <form method="POST" id="createTemplateForm" class="grid grid-cols-1 md:grid-cols-2 gap-4 m-0">
      <input type="hidden" name="action" value="create_template">

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Template Name <span class="text-error">*</span></label>
        <input type="text" name="name" id="tpl-name" placeholder="e.g. Cyberpunk Obsidian Neon" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Billing Access Label <span class="text-error">*</span></label>
        <select name="type" id="tpl-type" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
          <option value="Free">Free (available to all users)</option>
          <option value="Paid">Premium (assigned to specific plans)</option>
        </select>
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Accent Color (Hex)</label>
        <div class="flex gap-2">
          <input type="color" name="accent_picker" id="tpl-accent-picker" value="#006C49" class="w-12 h-11 border border-outline-variant rounded-lg cursor-pointer bg-surface">
          <input type="text" name="accent" id="tpl-accent" value="#006C49" required pattern="^#[0-9A-Fa-f]{6}$" class="flex-1 border border-outline-variant rounded-xl px-4 py-3 bg-surface text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Material Symbol Icon</label>
        <input type="text" name="icon" id="tpl-icon" value="description" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        <p class="text-[9px] text-on-surface-variant/70 mt-1">e.g. terminal, spa, grid_view, military_tech, code, auto_awesome</p>
      </div>

      <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Description <span class="text-error">*</span></label>
        <textarea name="description" id="tpl-description" rows="2" required placeholder="Brief catalog description shown to users..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none"></textarea>
      </div>

      <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Thumbnail Image URL (optional)</label>
        <input type="url" name="image" id="tpl-image" placeholder="https://example.com/preview.png" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
      </div>

      <div class="md:col-span-2 flex justify-end gap-2">
        <button type="reset" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 hover:bg-surface-variant text-xs font-semibold">Clear</button>
        <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-xl font-bold shadow-md hover:opacity-90 active:scale-95 transition-all text-xs flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm">save</span> Save Template
        </button>
      </div>
    </form>
  </div>

  <!-- Active Catalog List -->
  <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6">
    <h3 class="font-headline-md text-base text-on-surface border-b border-outline-variant/15 pb-3 flex items-center gap-2 font-bold mb-6">
      <span class="material-symbols-outlined text-primary">grid_on</span> Active Catalog
      <span class="ml-auto text-[10px] font-bold text-on-surface-variant bg-surface px-2 py-0.5 rounded-full"><?= count($templates) ?> items</span>
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <?php foreach ($templates as $t):
        $is_paid = $t['type'] === 'Paid';
      ?>
        <div class="border border-outline-variant/30 rounded-xl bg-surface/20 flex flex-col gap-3 overflow-hidden">
          <div class="aspect-[4/3] bg-surface-variant relative overflow-hidden" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>15;">
            <?php if (!empty($t['image_url'])): ?>
              <img src="<?= htmlspecialchars($t['image_url']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <div class="w-full h-full flex flex-col items-center justify-center p-4" style="color: <?= htmlspecialchars($t['accent_color']) ?>;">
                <span class="material-symbols-outlined text-4xl mb-2"><?= htmlspecialchars($t['icon']) ?></span>
                <div class="w-16 h-1 rounded-full mb-2" style="background-color: <?= htmlspecialchars($t['accent_color']) ?>;"></div>
                <div class="space-y-1 w-full max-w-[120px]">
                  <div class="h-1 bg-on-surface/10 rounded w-full"></div>
                  <div class="h-1 bg-on-surface/10 rounded w-3/4"></div>
                  <div class="h-1 bg-on-surface/10 rounded w-1/2"></div>
                </div>
              </div>
            <?php endif; ?>
            <span class="absolute top-3 right-3 text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full border <?= $is_paid ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200' ?>">
              <?= htmlspecialchars($t['type']) ?>
            </span>
          </div>

          <div class="p-4 flex flex-col gap-2 flex-1">
            <h4 class="font-headline-md text-sm font-extrabold text-on-surface flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[14px]" style="color: <?= htmlspecialchars($t['accent_color']) ?>;"><?= htmlspecialchars($t['icon']) ?></span>
              <?= htmlspecialchars($t['name']) ?>
            </h4>
            <p class="text-[11px] text-on-surface-variant leading-relaxed line-clamp-3"><?= htmlspecialchars($t['description']) ?></p>

            <div class="flex items-center gap-1 mt-2 pt-2 border-t border-outline-variant/10">
              <button onclick='openEditTemplateModal(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="flex-1 flex items-center justify-center gap-1 bg-secondary-container text-on-secondary-container hover:opacity-90 py-2 rounded-lg text-xs font-bold transition-all active:scale-95">
                <span class="material-symbols-outlined text-xs">edit</span> Edit
              </button>
              <form method="POST" onsubmit="return confirm('Delete this template? Users with resumes using it will see the default layout.')" class="m-0">
                <input type="hidden" name="action" value="delete_template">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button type="submit" class="p-2 rounded-lg bg-error/10 hover:bg-error hover:text-on-error text-error transition-all active:scale-95" title="Delete">
                  <span class="material-symbols-outlined text-xs">delete</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Edit Template Modal -->
<div id="editTemplateModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col p-6 gap-4 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between border-b border-outline-variant/15 pb-3">
      <h3 class="font-headline-md text-base font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">edit</span> Edit Template
      </h3>
      <button onclick="closeEditTemplateModal()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-variant/40 transition-all">
        <span class="material-symbols-outlined text-sm">close</span>
      </button>
    </div>

    <form method="POST" class="flex flex-col gap-3 m-0">
      <input type="hidden" name="action" value="update_template">
      <input type="hidden" name="id" id="edit-tpl-id">

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Template Name</label>
        <input type="text" name="name" id="edit-tpl-name" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Billing Label</label>
        <select name="type" id="edit-tpl-type" class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
          <option value="Free">Free</option>
          <option value="Paid">Premium</option>
        </select>
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Accent Color</label>
        <div class="flex gap-2">
          <input type="color" id="edit-tpl-accent-picker" class="w-10 h-10 border border-outline-variant rounded-lg cursor-pointer bg-surface">
          <input type="text" name="accent" id="edit-tpl-accent" required pattern="^#[0-9A-Fa-f]{6}$" class="flex-1 border border-outline-variant rounded-xl px-3 py-2 bg-surface text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Icon</label>
        <input type="text" name="icon" id="edit-tpl-icon" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Description</label>
        <textarea name="description" id="edit-tpl-description" rows="3" required class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none"></textarea>
      </div>

      <div>
        <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Image URL</label>
        <input type="url" name="image" id="edit-tpl-image" class="w-full border border-outline-variant rounded-xl px-4 py-2.5 bg-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
      </div>

      <div class="flex justify-end gap-2 mt-2 border-t border-outline-variant/15 pt-4">
        <button type="button" onclick="closeEditTemplateModal()" class="px-4 py-2 rounded-xl border border-outline-variant/40 text-xs font-semibold">Cancel</button>
        <button type="submit" class="bg-primary text-on-primary px-5 py-2 rounded-xl font-bold shadow-md text-xs">Apply Updates</button>
      </div>
    </form>
  </div>
</div>

<script>
// Sync color picker and hex input
document.getElementById('tpl-accent-picker').addEventListener('input', e => {
  document.getElementById('tpl-accent').value = e.target.value;
});
document.getElementById('tpl-accent').addEventListener('input', e => {
  if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
    document.getElementById('tpl-accent-picker').value = e.target.value;
  }
});
document.getElementById('edit-tpl-accent-picker').addEventListener('input', e => {
  document.getElementById('edit-tpl-accent').value = e.target.value;
});
document.getElementById('edit-tpl-accent').addEventListener('input', e => {
  if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
    document.getElementById('edit-tpl-accent-picker').value = e.target.value;
  }
});

function openEditTemplateModal(t) {
  document.getElementById('edit-tpl-id').value = t.id;
  document.getElementById('edit-tpl-name').value = t.name;
  document.getElementById('edit-tpl-type').value = t.type;
  document.getElementById('edit-tpl-accent').value = t.accent_color || '#006C49';
  document.getElementById('edit-tpl-accent-picker').value = t.accent_color || '#006C49';
  document.getElementById('edit-tpl-icon').value = t.icon;
  document.getElementById('edit-tpl-description').value = t.description;
  document.getElementById('edit-tpl-image').value = t.image_url || '';
  document.getElementById('editTemplateModal').classList.remove('hidden');
  document.getElementById('editTemplateModal').classList.add('flex');
}
function closeEditTemplateModal() {
  document.getElementById('editTemplateModal').classList.add('hidden');
  document.getElementById('editTemplateModal').classList.remove('flex');
}

async function generateTemplateWithAI() {
  const prompt = document.getElementById('ai-template-prompt').value.trim();
  if (!prompt) {
    alert("Please enter a style concept first.");
    return;
  }
  const btn = document.getElementById('ai-generate-btn');
  const icon = document.getElementById('ai-gen-icon');
  const text = document.getElementById('ai-gen-text');
  btn.disabled = true;
  icon.classList.add('animate-spin');
  icon.textContent = 'autorenew';
  text.textContent = 'Generating...';

  try {
    const res = await fetch('api/generate_template.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ prompt })
    });
    const data = await res.json();

    if (data.success && data.template) {
      const t = data.template;
      const fields = [
        { id: 'tpl-name', val: t.name },
        { id: 'tpl-type', val: t.type || 'Free' },
        { id: 'tpl-accent', val: t.accent_color || '#006C49' },
        { id: 'tpl-accent-picker', val: t.accent_color || '#006C49' },
        { id: 'tpl-icon', val: t.icon || 'description' },
        { id: 'tpl-description', val: t.description }
      ];
      fields.forEach(f => {
        const el = document.getElementById(f.id);
        if (el) {
          el.value = f.val;
          el.classList.add('ring-4', 'ring-primary/20', 'border-primary');
          setTimeout(() => el.classList.remove('ring-4', 'ring-primary/20', 'border-primary'), 1500);
        }
      });
    } else {
      alert('AI generation failed: ' + (data.error || 'Unknown error'));
    }
  } catch (err) {
    alert('Connection error: ' + err.message);
  } finally {
    btn.disabled = false;
    icon.classList.remove('animate-spin');
    icon.textContent = 'auto_awesome';
    text.textContent = 'Generate';
  }
}
</script>
