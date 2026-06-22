<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();

// Fetch student profile details
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$student = $user_stmt->fetch();

// Fetch matching plan details
$plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
$plan_stmt->execute([$student['current_plan'] ?? 'Starter Launch']);
$user_plan = $plan_stmt->fetch() ?: [
    'name' => 'Starter Launch',
    'max_resumes' => 2
];

// Count current resumes
$resumes_stmt = $db->prepare("SELECT COUNT(*) FROM resumes WHERE user_id = ?");
$resumes_stmt->execute([$user_id]);
$current_resumes = $resumes_stmt->fetchColumn();

if ($current_resumes >= intval($user_plan['max_resumes'])) {
    echo '<script>window.location.href = "?page=resumes&err=limit_reached";</script>';
    exit;
}

include __DIR__ . '/../components/common/head.php'; 
?>
<title>GreenLeaf Resume - Select Job Profile</title>
<style>
  .profile-card {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
  .profile-card:hover {
    transform: translateY(-4px);
  }
  .manage-mode .delete-btn {
    display: flex !important;
  }
  .asymmetric-leaf {
    border-radius: 24px 4px 24px 4px;
  }
  .toast {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
</style>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>

<!-- Main Content Canvas -->
<main class="md:ml-64 flex flex-col min-h-screen">
  
  <!-- Unified Dashboard Top Bar -->
  <header class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-surface/80 backdrop-blur-md shadow-sm flex justify-between items-center px-6 md:px-16 py-4">
    <div class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-primary">
      <span class="material-symbols-outlined">energy_savings_leaf</span>
      <span>GreenLeaf Resume</span>
    </div>
    <a href="?page=user_dashboard" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md">
      <span class="material-symbols-outlined text-sm">arrow_back</span> Dashboard
    </a>
  </header>

  <!-- Notification Toast -->
  <div id="toast" class="toast fixed bottom-6 right-6 z-50 bg-inverse-surface text-inverse-on-surface px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 translate-y-20 opacity-0 pointer-events-none">
    <span id="toast-icon" class="material-symbols-outlined text-primary">check_circle</span>
    <span id="toast-msg" class="font-label-md">Notification</span>
  </div>

  <div class="mt-24 px-6 md:px-16 pb-16 flex-1 flex flex-col">
    
    <!-- Progress Stepper -->
    <div class="mb-10 flex justify-center">
      <div class="flex items-center gap-4">
        <div class="flex flex-col items-center gap-2">
          <div class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md">
            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">eco</span>
          </div>
          <span class="font-label-sm text-label-sm text-primary">Role</span>
        </div>
        <div class="w-16 h-0.5 bg-primary rounded-full"></div>
        <div class="flex flex-col items-center gap-2 opacity-50">
          <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center">
            <span class="material-symbols-outlined text-sm">description</span>
          </div>
          <span class="font-label-sm text-label-sm">Details</span>
        </div>
        <div class="w-16 h-0.5 bg-secondary-container rounded-full"></div>
        <div class="flex flex-col items-center gap-2 opacity-50">
          <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center">
            <span class="material-symbols-outlined text-sm">palette</span>
          </div>
          <span class="font-label-sm text-label-sm">Design</span>
        </div>
      </div>
    </div>

    <!-- Header Section -->
    <div class="text-center mb-10">
      <h1 class="font-headline-xl text-headline-xl text-on-surface mb-3">Select Your Job Profile</h1>
      <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
        Our AI engine tailors your structure based on industry benchmarks. Choose the path that matches your expertise to begin your growth journey.
      </p>
    </div>

    <!-- Profiles Dynamic Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12" id="profileGrid">
      <!-- Populated dynamically via JS -->
    </div>

    <!-- Interactive Management Controls -->
    <div class="flex flex-wrap justify-center gap-4 mb-16">
      <button onclick="openAddProfileModal()" class="px-6 py-3 bg-primary/10 text-primary border border-primary/20 font-label-md rounded-xl flex items-center gap-2 hover:bg-primary/20 transition-all active:scale-95 shadow-sm">
        <span class="material-symbols-outlined">add</span> Add Custom Profile
      </button>
      <button id="manage-mode-btn" onclick="toggleManageMode()" class="px-6 py-3 border border-outline-variant text-on-surface-variant font-label-md rounded-xl flex items-center gap-2 hover:bg-surface-variant transition-all active:scale-95 shadow-sm">
        <span class="material-symbols-outlined">settings</span> Manage Profiles
      </button>
    </div>

    <!-- CTA Action Bar -->
    <div class="mt-auto flex flex-col items-center gap-6">
      <div class="flex items-center gap-3 p-4 bg-tertiary-fixed rounded-xl text-on-tertiary-fixed-variant shadow-sm border border-tertiary/10">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
        <span class="font-label-md text-label-md">Ready to generate a resume tailored for <strong id="selectedProfileName">Full Stack Developer</strong> positions.</span>
      </div>
      <button onclick="openTemplateModal()" class="group flex items-center gap-4 bg-primary text-on-primary px-12 py-5 rounded-full font-headline-md text-headline-md shadow-lg shadow-primary/20 hover:shadow-xl hover:opacity-90 active:scale-95 transition-all text-xl">
        Generate My Tailored Resume
        <span class="material-symbols-outlined text-2xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
      </button>
    </div>

  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>

<!-- ── MODAL: Select Template ────────────────────────────── -->
<div id="templateModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">
    <div class="p-6 border-b border-surface-variant flex justify-between items-center">
      <h2 class="font-headline-lg text-on-surface">Select a Resume Template</h2>
      <button onclick="closeTemplateModal()" class="p-2 hover:bg-surface-variant rounded-full transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div id="templateGrid" class="p-6 overflow-y-auto flex-1 bg-surface grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="col-span-full text-center py-10 text-on-surface-variant">
        <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
        <p class="mt-2 text-sm">Loading templates…</p>
      </div>
    </div>
  </div>
</div>

<!-- ── MODAL: Add Custom Profile ────────────────────────── -->
<div id="addProfileModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4 animate-fade-in">
  <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col">
    <div class="p-6 border-b border-surface-variant flex justify-between items-center">
      <h2 class="font-headline-md text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">add_circle</span> Add Custom Profile
      </h2>
      <button onclick="closeAddProfileModal()" class="p-2 hover:bg-surface-variant rounded-full transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <form onsubmit="saveCustomProfile(event)" class="p-6 flex flex-col gap-4">
      <div>
        <label class="block font-label-md text-on-surface-variant mb-1">Profile / Role Name</label>
        <input name="name" placeholder="e.g. UX Designer, DevOps Engineer" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
      </div>
      <div>
        <label class="block font-label-md text-on-surface-variant mb-1">Description</label>
        <textarea name="description" rows="3" placeholder="Brief details about backend pipeline, system architecture, or graphic layouts..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"></textarea>
      </div>
      <div>
        <label class="block font-label-md text-on-surface-variant mb-1">Select Card Icon</label>
        <select name="icon" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
          <option value="terminal">Terminal / Code (terminal)</option>
          <option value="layers">Layers (layers)</option>
          <option value="monitoring">Monitoring (monitoring)</option>
          <option value="assignment_ind">Management (assignment_ind)</option>
          <option value="brush">Design / Arts (brush)</option>
          <option value="database">Database (database)</option>
          <option value="cloud">Cloud / Network (cloud)</option>
          <option value="psychology">AI / Science (psychology)</option>
        </select>
      </div>
      <div class="flex justify-end gap-3 mt-2">
        <button type="button" onclick="closeAddProfileModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-on-surface active:scale-95">Cancel</button>
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary hover:opacity-90 font-label-md shadow transition-all active:scale-95">Add Profile</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Client JS App ─────────────────────────────────────── -->
<script>
  let profilesList = [];
  let templatesList = [];
  let currentSelectedProfile = 'Full Stack Developer';
  let isManageMode = false;

  document.addEventListener('DOMContentLoaded', () => {
    loadProfiles();
  });

  // Fetch job profiles dynamically via standard REST API
  async function loadProfiles() {
    try {
      const res = await fetch('api/jobs.php');
      const json = await res.json();
      if (json.success) {
        profilesList = json.data;
        renderProfilesGrid();
      } else {
        showToast('Error loading profiles: ' + json.error, 'error');
      }
    } catch (e) {
      showToast('Connection failed: ' + e.message, 'error');
    }
  }

  async function loadTemplates() {
    try {
      const res = await fetch('api/templates.php');
      const json = await res.json();
      if (json.success) {
        templatesList = json.data || [];
        renderTemplateGrid();
      } else {
        document.getElementById('templateGrid').innerHTML =
          '<p class="col-span-full text-center text-error py-10 text-sm">Failed to load templates: ' + escapeHtml(json.error || 'Unknown error') + '</p>';
      }
    } catch (e) {
      document.getElementById('templateGrid').innerHTML =
        '<p class="col-span-full text-center text-error py-10 text-sm">Connection failed: ' + escapeHtml(e.message) + '</p>';
    }
  }

  function renderTemplateGrid() {
    const grid = document.getElementById('templateGrid');
    if (!templatesList.length) {
      grid.innerHTML = '<p class="col-span-full text-center text-on-surface-variant py-10 text-sm">No templates available right now.</p>';
      return;
    }
    grid.innerHTML = templatesList.map(t => {
      const accent = t.accent_color || '#006C49';
      const isPaid = (t.type || '').toLowerCase() === 'paid';
      return `
        <button onclick='selectTemplateAndGenerate(${JSON.stringify(t.name)})'
                class="group text-left p-4 bg-surface-container-lowest border border-outline-variant rounded-xl hover:border-primary hover:shadow-lg transition-all focus:ring-2 focus:ring-primary outline-none relative">
          <div class="absolute top-3 right-3 z-10 flex gap-1">
            ${isPaid ? '<span class="bg-amber-100 text-amber-800 text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full flex items-center gap-0.5"><span class="material-symbols-outlined text-[10px]">workspace_premium</span>Paid</span>' : '<span class="bg-emerald-100 text-emerald-800 text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full">Free</span>'}
          </div>
          <div class="aspect-[1/1.4] bg-surface-variant rounded-lg mb-4 p-4 flex flex-col gap-2 relative overflow-hidden" style="border-top: 3px solid ${accent}">
            <div class="h-2 rounded-full" style="width:33%; background:${accent}"></div>
            <div class="h-1 bg-on-surface/10 rounded w-full"></div>
            <div class="h-1 bg-on-surface/10 rounded w-4/5"></div>
            <div class="flex gap-2 mt-2">
              <div class="flex-1 flex flex-col gap-1">
                <div class="h-1 bg-on-surface/10 rounded w-full"></div>
                <div class="h-1 bg-on-surface/10 rounded w-3/4"></div>
              </div>
              <div class="w-1/3 flex flex-col gap-1">
                <div class="h-1 rounded w-full" style="background:${accent}55"></div>
                <div class="h-1 rounded w-2/3" style="background:${accent}55"></div>
              </div>
            </div>
            <div class="absolute bottom-3 right-3 text-on-surface-variant/40">
              <span class="material-symbols-outlined text-3xl">${escapeHtml(t.icon || 'description')}</span>
            </div>
          </div>
          <h4 class="font-label-md text-on-surface">${escapeHtml(t.name)}</h4>
          <p class="text-[10px] text-on-surface-variant mt-1 line-clamp-2">${escapeHtml(t.description || '')}</p>
        </button>
      `;
    }).join('');
  }

  // Render profiles
  function renderProfilesGrid() {
    const grid = document.getElementById('profileGrid');
    if (profilesList.length === 0) {
      grid.innerHTML = '<p class="col-span-full text-center text-on-surface-variant/60 py-10 font-body-lg border-2 border-dashed border-outline-variant rounded-2xl">No profiles found.</p>';
      return;
    }

    grid.innerHTML = profilesList.map(p => {
      const isSelected = p.name === currentSelectedProfile;
      const isDefault = [1, 2, 3, 4].includes(p.id); // Base ID set seeded on-the-fly
      
      return `
        <div onclick="selectProfile('${escapeHtml(p.name)}', this)" 
             class="profile-card cursor-pointer group text-left p-8 bg-surface-container-lowest border ${isSelected ? 'border-2 border-primary shadow-xl shadow-primary/5 selected-profile' : 'border-outline-variant'} asymmetric-leaf transition-all active:scale-[0.98] flex flex-col gap-6 relative">
          
          ${p.name === 'Full Stack Developer' ? '<div class="absolute -top-3 -right-3 bg-primary text-on-primary px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase">Most Popular</div>' : ''}
          
          <!-- Delete button (Only displayed in Manage Mode and on Custom Profiles) -->
          ${(!isDefault) ? `
            <button onclick="event.stopPropagation(); deleteProfile(${p.id})" 
                    class="delete-btn absolute top-3 right-3 w-8 h-8 rounded-full bg-error/10 hover:bg-error hover:text-on-error text-error flex items-center justify-center transition-all z-20 ${isManageMode ? 'flex' : 'hidden'}">
              <span class="material-symbols-outlined text-sm">delete</span>
            </button>
          ` : ''}

          <div class="w-14 h-14 rounded-xl ${isSelected ? 'bg-primary text-on-primary' : 'bg-secondary-container text-primary group-hover:bg-primary group-hover:text-on-primary'} flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-3xl">${escapeHtml(p.icon)}</span>
          </div>
          <div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">${escapeHtml(p.name)}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">${escapeHtml(p.description)}</p>
          </div>
        </div>
      `;
    }).join('');
  }

  function selectProfile(profileName, element) {
    if (isManageMode) return; // Prevent selection while managing / deleting
    
    currentSelectedProfile = profileName;
    document.getElementById('selectedProfileName').innerText = profileName;
    
    // Rerender to apply thick border dynamic classes
    renderProfilesGrid();
  }

  // Manage Profiles Mode Toggle
  function toggleManageMode() {
    isManageMode = !isManageMode;
    const btn = document.getElementById('manage-mode-btn');
    if (isManageMode) {
      btn.innerHTML = `<span class="material-symbols-outlined">check</span> Done Managing`;
      btn.className = "px-6 py-3 bg-inverse-surface text-inverse-on-surface font-label-md rounded-xl flex items-center gap-2 hover:opacity-90 transition-all active:scale-95 shadow-sm";
    } else {
      btn.innerHTML = `<span class="material-symbols-outlined">settings</span> Manage Profiles`;
      btn.className = "px-6 py-3 border border-outline-variant text-on-surface-variant font-label-md rounded-xl flex items-center gap-2 hover:bg-surface-variant transition-all active:scale-95 shadow-sm";
    }
    renderProfilesGrid();
  }

  // API Call: Delete Profile
  async function deleteProfile(id) {
    if (!confirm('Are you sure you want to delete this custom job profile?')) return;
    try {
      const res = await fetch('api/jobs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_profile', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Profile deleted successfully');
        loadProfiles();
      } else {
        showToast(json.error, 'error');
      }
    } catch (e) {
      showToast(e.message, 'error');
    }
  }

  // Modal controls
  function openAddProfileModal() {
    document.getElementById('addProfileModal').classList.remove('hidden');
    document.getElementById('addProfileModal').classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }

  function closeAddProfileModal() {
    document.getElementById('addProfileModal').classList.add('hidden');
    document.getElementById('addProfileModal').classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  function openTemplateModal() {
    document.getElementById('templateModal').classList.remove('hidden');
    document.getElementById('templateModal').classList.add('flex');
    if (templatesList.length === 0) loadTemplates();
  }

  function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
    document.getElementById('templateModal').classList.remove('flex');
  }

  // API Call: Save custom profile
  async function saveCustomProfile(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_custom_profile';

    try {
      const res = await fetch('api/jobs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Custom job profile added!');
        e.target.reset();
        closeAddProfileModal();
        loadProfiles();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // AJAX Resume Generator
  function selectTemplateAndGenerate(templateName) {
    const formData = new FormData();
    formData.append('profile', currentSelectedProfile);
    formData.append('template', templateName);

    fetch('api/generate_resume.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        closeTemplateModal();
        window.location.href = '?page=resumes&generating=' + data.id;
      } else {
        if (data.limit_reached) {
          showToast(data.error, 'error');
          setTimeout(() => window.location.href = '?page=resumes&err=limit_reached', 2000);
        } else if (data.no_credits) {
          showToast(data.error, 'error');
          setTimeout(() => window.location.href = '?page=plan', 2000);
        } else {
          showToast(data.error || 'Failed to create resume', 'error');
        }
      }
    })
    .catch(err => {
      showToast('Connection error: ' + err.message, 'error');
    });
  }

  // Toast utility
  function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    const toastIcon = document.getElementById('toast-icon');

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
    }, 3500);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
</script>
</body>
</html>
