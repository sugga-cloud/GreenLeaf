<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
include __DIR__ . '/../components/common/head.php';
?>
<title>My Profile — GreenLeaf Resume</title>
<style>
  /* Premium animations and styling */
  .step-transition {
    transition: all 0.3s ease-in-out;
  }
  .progress-bar-fill {
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .toast {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
</style>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>

<main class="md:ml-64 flex flex-col min-h-screen">
  <!-- Top bar -->
  <header class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-surface/80 backdrop-blur-md shadow-sm flex justify-between items-center px-6 md:px-16 py-4">
    <div class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-primary">
      <span class="material-symbols-outlined">energy_savings_leaf</span>
      <span>GreenLeaf Resume</span>
    </div>
    <div class="flex items-center gap-4">
      <button onclick="openPreviewModal()" class="flex items-center gap-2 bg-primary text-on-primary font-label-md px-4 py-2.5 rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-sm">
        <span class="material-symbols-outlined text-sm">visibility</span> Preview Profile
      </button>
      <a href="?page=user_dashboard" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md">
        <span class="material-symbols-outlined text-sm">arrow_back</span> Dashboard
      </a>
    </div>
  </header>

  <!-- Notification Toast -->
  <div id="toast" class="toast fixed bottom-6 right-6 z-50 bg-inverse-surface text-inverse-on-surface px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 translate-y-20 opacity-0 pointer-events-none">
    <span id="toast-icon" class="material-symbols-outlined text-primary">check_circle</span>
    <span id="toast-msg" class="font-label-md">Profile saved successfully</span>
  </div>

  <div class="mt-24 px-6 md:px-16 pb-16 flex-1 flex flex-col">
    <!-- Page title -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">My Profile</h1>
        <p class="text-on-surface-variant font-body-md mt-1">Complete your profile to generate better, tailored resumes instantly.</p>
      </div>
      <button onclick="openUploadModal()" class="flex items-center gap-2 bg-primary/10 text-primary border border-primary/20 font-label-md px-5 py-2.5 rounded-xl hover:bg-primary/20 transition-all active:scale-95 shadow-sm whitespace-nowrap">
        <span class="material-symbols-outlined text-sm">upload_file</span> Import from Resume PDF
      </button>
    </div>

    <!-- ── Step Wizard Bar ──────────────────────────────────── -->
    <div class="mb-10 bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/30 shadow-sm">
      <div class="flex items-center justify-between overflow-x-auto pb-4 gap-4">
        <!-- Step 1 -->
        <button onclick="goToStep(1)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="1">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">person</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Personal</span>
        </button>

        <div class="flex-1 h-0.5 bg-outline-variant min-w-[20px]" data-connector="1"></div>

        <!-- Step 2 -->
        <button onclick="goToStep(2)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="2">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">school</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Academics</span>
        </button>

        <div class="flex-1 h-0.5 bg-outline-variant min-w-[20px]" data-connector="2"></div>

        <!-- Step 3 -->
        <button onclick="goToStep(3)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="3">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">work</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Experience</span>
        </button>

        <div class="flex-1 h-0.5 bg-outline-variant min-w-[20px]" data-connector="3"></div>

        <!-- Step 4 -->
        <button onclick="goToStep(4)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="4">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">psychology</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Skills</span>
        </button>

        <div class="flex-1 h-0.5 bg-outline-variant min-w-[20px]" data-connector="4"></div>

        <!-- Step 5 -->
        <button onclick="goToStep(5)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="5">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">code</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Projects</span>
        </button>

        <div class="flex-1 h-0.5 bg-outline-variant min-w-[20px]" data-connector="5"></div>

        <!-- Step 6 -->
        <button onclick="goToStep(6)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="6">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">emoji_events</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Achievements</span>
        </button>

        <div class="flex-1 h-0.5 bg-outline-variant min-w-[20px]" data-connector="6"></div>

        <!-- Step 7 -->
        <button onclick="goToStep(7)" class="step-btn flex flex-col items-center gap-1 group transition-all" data-step="7">
          <div class="step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant">
            <span class="material-symbols-outlined text-base">favorite</span>
          </div>
          <span class="text-xs font-label-md text-on-surface-variant whitespace-nowrap">Hobbies</span>
        </button>
      </div>

      <!-- Smooth Progress Bar -->
      <div class="mt-6">
        <div class="h-2 bg-surface-container rounded-full overflow-hidden">
          <div id="progress-bar-fill" class="progress-bar-fill h-full bg-primary rounded-full" style="width: 0%"></div>
        </div>
        <div class="flex justify-between items-center mt-2">
          <p id="progress-text" class="text-label-sm text-on-surface-variant">Step 1 of 7 — Personal Details</p>
          <span id="completion-percentage" class="text-label-sm font-bold text-primary">0% Complete</span>
        </div>
      </div>
    </div>

    <!-- ── Content Panels (SPA) ──────────────────────────────── -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-8 flex-1 flex flex-col">
      
      <!-- STEP 1: PERSONAL DETAILS -->
      <div id="step-panel-1" class="step-panel hidden flex-col gap-6">
        <form id="form-personal" onsubmit="savePersonal(event)" class="flex flex-col gap-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block font-label-md text-on-surface-variant mb-1">Full Name</label>
              <input name="full_name" placeholder="John Doe" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">Email</label>
              <input name="email" type="email" placeholder="you@email.com" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">Phone</label>
              <input name="phone" placeholder="+91 9876543210" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">Date of Birth</label>
              <input name="dob" type="date" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">Gender</label>
              <select name="gender" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
                <option value="">Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Non-binary">Non-binary</option>
                <option value="Prefer not to say">Prefer not to say</option>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">Nationality</label>
              <input name="nationality" placeholder="Indian" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">City / Location</label>
              <input name="city" placeholder="Mumbai" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div class="md:col-span-2">
              <label class="block font-label-md text-on-surface-variant mb-1">Address</label>
              <input name="address" placeholder="Street, Area" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">LinkedIn URL</label>
              <input name="linkedin" placeholder="https://linkedin.com/in/..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div>
              <label class="block font-label-md text-on-surface-variant mb-1">GitHub URL</label>
              <input name="github" placeholder="https://github.com/..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div class="md:col-span-2">
              <label class="block font-label-md text-on-surface-variant mb-1">Portfolio URL</label>
              <input name="portfolio" placeholder="https://yourportfolio.com" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
            </div>
            <div class="md:col-span-2">
              <label class="block font-label-md text-on-surface-variant mb-1">Professional Summary</label>
              <textarea name="summary" rows="4" placeholder="A brief about yourself..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"></textarea>
            </div>
          </div>
          <div class="flex justify-end mt-4">
            <button type="submit" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">save</span> Save & Continue
            </button>
          </div>
        </form>
      </div>

      <!-- STEP 2: ACADEMICS -->
      <div id="step-panel-2" class="step-panel hidden flex-col gap-6">
        <div id="academics-list" class="flex flex-col gap-4">
          <!-- Populated by JS -->
        </div>

        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 mt-4">
          <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Academic Record</h4>
          <form id="form-academic" onsubmit="addAcademic(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Degree / Certificate</label>
                <input name="degree" placeholder="B.Tech, 12th, Diploma..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Institution</label>
                <input name="institution" placeholder="College / School name" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Board / University</label>
                <input name="board_university" placeholder="CBSE, Mumbai University..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Grade / CGPA / %</label>
                <input name="grade" placeholder="8.5 / 90%" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Start Year</label>
                <input name="start_year" type="number" min="1990" max="2030" placeholder="2020" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">End Year</label>
                <input name="end_year" type="number" min="1990" max="2030" placeholder="2024" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div class="md:col-span-2">
                <label class="block font-label-md text-on-surface-variant mb-1">Description (optional)</label>
                <textarea name="description" rows="2" placeholder="Key courses, achievements..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"></textarea>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Record
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- STEP 3: EXPERIENCE -->
      <div id="step-panel-3" class="step-panel hidden flex-col gap-6">
        <div id="experience-list" class="flex flex-col gap-4">
          <!-- Populated by JS -->
        </div>

        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 mt-4">
          <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Experience</h4>
          <form id="form-experience" onsubmit="addExperience(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Job Title</label>
                <input name="job_title" placeholder="Software Engineer" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Company</label>
                <input name="company" placeholder="Google, Startup..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Location</label>
                <input name="location" placeholder="Bangalore / Remote" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div class="flex items-end gap-2">
                <label class="flex items-center gap-2 cursor-pointer pb-3">
                  <input type="checkbox" name="is_current" id="is_current_cb" class="w-4 h-4 accent-primary" onchange="toggleEndDateField(this.checked)">
                  <span class="font-label-md text-on-surface-variant">Currently working here</span>
                </label>
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Start Date</label>
                <input name="start_date" type="month" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div id="end_date_field_container">
                <label class="block font-label-md text-on-surface-variant mb-1">End Date</label>
                <input name="end_date" type="month" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div class="md:col-span-2">
                <label class="block font-label-md text-on-surface-variant mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Key responsibilities and achievements..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"></textarea>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Experience
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- STEP 4: SKILLS -->
      <div id="step-panel-4" class="step-panel hidden flex-col gap-6">
        <div id="skills-list" class="flex flex-wrap gap-3 min-h-[48px]">
          <!-- Populated by JS -->
        </div>

        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 mt-4">
          <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Skill</h4>
          <form id="form-skill" onsubmit="addSkill(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Skill Name</label>
                <input name="skill_name" placeholder="Python, React, Photoshop..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Proficiency Level</label>
                <select name="proficiency" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
                  <option value="Beginner">Beginner</option>
                  <option value="Intermediate" selected>Intermediate</option>
                  <option value="Advanced">Advanced</option>
                  <option value="Expert">Expert</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Skill
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- STEP 5: PROJECTS -->
      <div id="step-panel-5" class="step-panel hidden flex-col gap-6">
        <div id="projects-list" class="flex flex-col gap-4">
          <!-- Populated by JS -->
        </div>

        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 mt-4">
          <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Project</h4>
          <form id="form-project" onsubmit="addProject(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Project Title</label>
                <input name="title" placeholder="Portfolio Website" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Tech Stack</label>
                <input name="tech_stack" placeholder="React, Node.js, MongoDB" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Project URL (optional)</label>
                <input name="url" type="url" placeholder="https://github.com/..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block font-label-md text-on-surface-variant mb-1">Start</label>
                  <input name="start_date" type="month" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
                </div>
                <div>
                  <label class="block font-label-md text-on-surface-variant mb-1">End</label>
                  <input name="end_date" type="month" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
                </div>
              </div>
              <div class="md:col-span-2">
                <label class="block font-label-md text-on-surface-variant mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="What it does, your role, impact..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"></textarea>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Project
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- STEP 6: ACHIEVEMENTS -->
      <div id="step-panel-6" class="step-panel hidden flex-col gap-6">
        <div id="achievements-list" class="flex flex-col gap-4">
          <!-- Populated by JS -->
        </div>

        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 mt-4">
          <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Achievement / Certification</h4>
          <form id="form-achievement" onsubmit="addAchievement(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Title</label>
                <input name="title" placeholder="Best Innovator Award" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Issuer / Organisation</label>
                <input name="issuer" placeholder="Google, College..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div>
                <label class="block font-label-md text-on-surface-variant mb-1">Date</label>
                <input name="date" type="month" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              </div>
              <div class="md:col-span-2">
                <label class="block font-label-md text-on-surface-variant mb-1">Description (optional)</label>
                <textarea name="description" rows="2" placeholder="Brief about this achievement..." class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"></textarea>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Achievement
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- STEP 7: HOBBIES -->
      <div id="step-panel-7" class="step-panel hidden flex-col gap-6">
        <div id="hobbies-list" class="flex flex-wrap gap-3 min-h-[48px]">
          <!-- Populated by JS -->
        </div>

        <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/30 mt-4">
          <h4 class="font-label-md text-primary mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-sm">add_circle</span> Add Hobby / Interest</h4>
          <form id="form-hobby" onsubmit="addHobby(event)">
            <div class="flex gap-3">
              <input name="hobby" placeholder="Photography, Hiking, Chess..." required class="flex-1 border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all">
              <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">add</span> Add
              </button>
            </div>
          </form>
        </div>

        <div class="mt-8 p-6 bg-primary/5 border border-primary/20 rounded-2xl text-center">
          <span class="material-symbols-outlined text-primary text-4xl mb-3 block" style="font-variation-settings:'FILL' 1">check_circle</span>
          <h4 class="font-label-md text-on-surface mb-2">Profile Fully Complete!</h4>
          <p class="text-on-surface-variant font-body-md mb-4">Your dynamic profile records are locked and ready for custom resume tailoring.</p>
          <a href="?page=user_dashboard" class="inline-flex items-center gap-2 bg-primary text-on-primary px-8 py-3 rounded-xl font-label-md shadow active:scale-95 transition-all">
            <span class="material-symbols-outlined text-sm">dashboard</span> Go to Dashboard
          </a>
        </div>
      </div>

      <!-- Navigation buttons at the bottom of the card -->
      <div id="panel-navigation" class="flex justify-between mt-8 pt-6 border-t border-outline-variant/30">
        <button id="prev-btn" onclick="prevStep()" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-md focus:outline-none">
          <span class="material-symbols-outlined text-sm">arrow_back</span> Previous
        </button>
        <button id="next-btn" onclick="nextStep()" class="flex items-center gap-2 bg-surface-container text-on-surface px-6 py-2.5 rounded-xl font-label-md hover:bg-surface-container-high transition-colors focus:outline-none">
          Next <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </button>
      </div>

    </div>
  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>

<!-- ── JavaScript Client Side App ──────────────────────────── -->
<script>
  let currentStep = 1;
  let profileData = {
    personal: null,
    academics: [],
    experience: [],
    skills: [],
    projects: [],
    achievements: [],
    hobbies: []
  };

  const stepsConfig = [
    { label: 'Personal Details', icon: 'person' },
    { label: 'Academic Qualifications', icon: 'school' },
    { label: 'Work Experience', icon: 'work' },
    { label: 'Technical & Soft Skills', icon: 'psychology' },
    { label: 'Projects & Implementations', icon: 'code' },
    { label: 'Achievements & Awards', icon: 'emoji_events' },
    { label: 'Interests & Hobbies', icon: 'favorite' }
  ];

  document.addEventListener('DOMContentLoaded', () => {
    // Check url step query
    const urlParams = new URLSearchParams(window.location.search);
    const stepParam = parseInt(urlParams.get('step'));
    if (stepParam >= 1 && stepParam <= 7) {
      currentStep = stepParam;
    }

    loadProfileData();
  });

  // Fetch all profile records via the centralized API
  async function loadProfileData() {
    try {
      const res = await fetch('/api/profile.php');
      const json = await res.json();
      if (json.success) {
        profileData = json.data;
        renderAllPanels();
        goToStep(currentStep, false);
      } else {
        showToast('Error loading profile data: ' + json.error, 'error');
      }
    } catch (e) {
      showToast('Connection failed: ' + e.message, 'error');
    }
  }

  // Render the data inside forms and lists dynamically
  function renderAllPanels() {
    // 1. Personal details
    if (profileData.personal) {
      const f = document.getElementById('form-personal');
      for (const key in profileData.personal) {
        if (f.elements[key]) {
          f.elements[key].value = profileData.personal[key] || '';
        }
      }
    }

    // 2. Academics
    const acadsList = document.getElementById('academics-list');
    if (profileData.academics.length === 0) {
      acadsList.innerHTML = `<p class="text-on-surface-variant font-body-md text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No academic records yet. Add one below.</p>`;
    } else {
      acadsList.innerHTML = profileData.academics.map(r => `
        <div class="bg-surface border border-outline-variant/40 rounded-xl p-5 flex justify-between items-start gap-4">
          <div>
            <p class="font-label-md text-on-surface">${escapeHtml(r.degree)} — ${escapeHtml(r.institution)}</p>
            <p class="text-label-sm text-on-surface-variant mt-1">${escapeHtml(r.board_university)} | ${r.start_year} – ${r.end_year} | Grade: ${escapeHtml(r.grade)}</p>
            ${r.description ? `<p class="text-body-md text-on-surface-variant mt-2 text-sm">${escapeHtml(r.description)}</p>` : ''}
          </div>
          <button onclick="deleteAcademic(${r.id})" class="text-error p-2 hover:bg-error-container rounded-lg transition-colors"><span class="material-symbols-outlined text-sm">delete</span></button>
        </div>
      `).join('');
    }

    // 3. Experience
    const expList = document.getElementById('experience-list');
    if (profileData.experience.length === 0) {
      expList.innerHTML = `<p class="text-on-surface-variant font-body-md text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No experience records yet. Add one below.</p>`;
    } else {
      expList.innerHTML = profileData.experience.map(r => `
        <div class="bg-surface border border-outline-variant/40 rounded-xl p-5 flex justify-between items-start gap-4">
          <div>
            <p class="font-label-md text-on-surface">${escapeHtml(r.job_title)} @ ${escapeHtml(r.company)}</p>
            <p class="text-label-sm text-on-surface-variant mt-1">${escapeHtml(r.location)} | ${r.start_date} – ${r.is_current ? 'Present' : r.end_date}</p>
            ${r.description ? `<p class="text-sm text-on-surface-variant mt-2">${escapeHtml(r.description)}</p>` : ''}
          </div>
          <button onclick="deleteExperience(${r.id})" class="text-error p-2 hover:bg-error-container rounded-lg transition-colors"><span class="material-symbols-outlined text-sm">delete</span></button>
        </div>
      `).join('');
    }

    // 4. Skills
    const skillsList = document.getElementById('skills-list');
    if (profileData.skills.length === 0) {
      skillsList.innerHTML = `<p class="text-on-surface-variant font-body-md w-full text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No skills yet. Add some below.</p>`;
    } else {
      skillsList.innerHTML = profileData.skills.map(r => `
        <div class="flex items-center gap-2 bg-primary-container text-on-primary-container px-4 py-2 rounded-full font-label-md animate-fade-in">
          <span>${escapeHtml(r.skill_name)}</span>
          <span class="text-xs opacity-70">· ${escapeHtml(r.proficiency)}</span>
          <button onclick="deleteSkill(${r.id})" class="ml-1 hover:opacity-70 transition-opacity"><span class="material-symbols-outlined" style="font-size:14px">close</span></button>
        </div>
      `).join('');
    }

    // 5. Projects
    const projectsList = document.getElementById('projects-list');
    if (profileData.projects.length === 0) {
      projectsList.innerHTML = `<p class="text-on-surface-variant font-body-md text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No projects yet. Add one below.</p>`;
    } else {
      projectsList.innerHTML = profileData.projects.map(r => `
        <div class="bg-surface border border-outline-variant/40 rounded-xl p-5 flex justify-between items-start gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
              <p class="font-label-md text-on-surface">${escapeHtml(r.title)}</p>
              ${r.url ? `<a href="${escapeHtml(r.url)}" target="_blank" class="text-primary hover:underline text-xs flex items-center gap-1"><span class="material-symbols-outlined" style="font-size:14px">open_in_new</span> Link</a>` : ''}
            </div>
            <p class="text-label-sm text-on-surface-variant">${escapeHtml(r.tech_stack)} | ${r.start_date} – ${r.end_date}</p>
            ${r.description ? `<p class="text-sm text-on-surface-variant mt-2">${escapeHtml(r.description)}</p>` : ''}
          </div>
          <button onclick="deleteProject(${r.id})" class="text-error p-2 hover:bg-error-container rounded-lg transition-colors"><span class="material-symbols-outlined text-sm">delete</span></button>
        </div>
      `).join('');
    }

    // 6. Achievements
    const achievementsList = document.getElementById('achievements-list');
    if (profileData.achievements.length === 0) {
      achievementsList.innerHTML = `<p class="text-on-surface-variant font-body-md text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No achievements yet. Add one below.</p>`;
    } else {
      achievementsList.innerHTML = profileData.achievements.map(r => `
        <div class="bg-surface border border-outline-variant/40 rounded-xl p-5 flex justify-between items-start gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="material-symbols-outlined text-primary" style="font-size:18px">emoji_events</span>
              <p class="font-label-md text-on-surface">${escapeHtml(r.title)}</p>
            </div>
            <p class="text-label-sm text-on-surface-variant">${escapeHtml(r.issuer)} | ${escapeHtml(r.date)}</p>
            ${r.description ? `<p class="text-sm text-on-surface-variant mt-2">${escapeHtml(r.description)}</p>` : ''}
          </div>
          <button onclick="deleteAchievement(${r.id})" class="text-error p-2 hover:bg-error-container rounded-lg transition-colors"><span class="material-symbols-outlined text-sm">delete</span></button>
        </div>
      `).join('');
    }

    // 7. Hobbies
    const hobbiesList = document.getElementById('hobbies-list');
    if (profileData.hobbies.length === 0) {
      hobbiesList.innerHTML = `<p class="text-on-surface-variant font-body-md w-full text-center py-8 border-2 border-dashed border-outline-variant rounded-xl">No hobbies yet. Add some below.</p>`;
    } else {
      hobbiesList.innerHTML = profileData.hobbies.map(r => `
        <div class="flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-2 rounded-full font-label-md animate-fade-in">
          <span>${escapeHtml(r.hobby)}</span>
          <button onclick="deleteHobby(${r.id})" class="ml-1 hover:opacity-70 transition-opacity"><span class="material-symbols-outlined" style="font-size:14px">close</span></button>
        </div>
      `).join('');
    }
  }

  // Handle Dynamic Step Switching SPA Navigation
  function goToStep(step, updateUrl = true) {
    if (step < 1 || step > 7) return;
    currentStep = step;

    if (updateUrl) {
      const url = new URL(window.location);
      url.searchParams.set('step', currentStep);
      window.history.pushState({}, '', url);
    }

    // Toggle panel visibility
    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('flex'));
    const activePanel = document.getElementById(`step-panel-${currentStep}`);
    if (activePanel) {
      activePanel.classList.remove('hidden');
      activePanel.classList.add('flex');
    }

    // Update wizard icons & indicators
    document.querySelectorAll('.step-btn').forEach(btn => {
      const btnStep = parseInt(btn.getAttribute('data-step'));
      const container = btn.querySelector('.step-icon-container');
      const label = btn.querySelector('span:last-child');
      
      if (btnStep < currentStep) {
        // Step Done
        container.className = "step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-primary text-on-primary shadow-md scale-100";
        container.innerHTML = `<span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1">check</span>`;
        label.className = "text-xs font-label-md text-primary font-bold whitespace-nowrap";
      } else if (btnStep === currentStep) {
        // Step Active
        container.className = "step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-primary-container text-primary ring-2 ring-primary shadow-lg scale-110";
        container.innerHTML = `<span class="material-symbols-outlined text-base">${stepsConfig[btnStep-1].icon}</span>`;
        label.className = "text-xs font-label-md text-primary font-bold whitespace-nowrap";
      } else {
        // Step Future
        container.className = "step-icon-container w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 bg-surface-container text-on-surface-variant scale-100";
        container.innerHTML = `<span class="material-symbols-outlined text-base">${stepsConfig[btnStep-1].icon}</span>`;
        label.className = "text-xs font-label-md text-on-surface-variant whitespace-nowrap";
      }
    });

    // Update connectors
    for (let i = 1; i <= 6; i++) {
      const conn = document.querySelector(`[data-connector="${i}"]`);
      if (conn) {
        if (i < currentStep) {
          conn.className = "flex-1 h-0.5 bg-primary min-w-[20px] transition-all duration-300";
        } else {
          conn.className = "flex-1 h-0.5 bg-outline-variant min-w-[20px] transition-all duration-300";
        }
      }
    }

    // Update progress bar width & label text
    const percentage = Math.round(((currentStep - 1) / 6) * 100);
    document.getElementById('progress-bar-fill').style.width = `${percentage}%`;
    document.getElementById('progress-text').innerText = `Step ${currentStep} of 7 — ${stepsConfig[currentStep-1].label}`;
    document.getElementById('completion-percentage').innerText = `${percentage}% Complete`;

    // Bottom Navigation show/hide
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    prevBtn.classList.toggle('invisible', currentStep === 1);
    
    if (currentStep === 7) {
      nextBtn.classList.add('hidden');
    } else {
      nextBtn.classList.remove('hidden');
    }
  }

  function prevStep() {
    goToStep(currentStep - 1);
  }

  function nextStep() {
    goToStep(currentStep + 1);
  }

  // Toggle end date field in experience form based on checkbox
  function toggleEndDateField(isCurrent) {
    const endContainer = document.getElementById('end_date_field_container');
    const endInput = endContainer.querySelector('input');
    if (isCurrent) {
      endContainer.classList.add('opacity-40', 'pointer-events-none');
      endInput.removeAttribute('required');
      endInput.value = '';
    } else {
      endContainer.classList.remove('opacity-40', 'pointer-events-none');
    }
  }

  // API Call: Save Personal details
  async function savePersonal(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'save_personal';

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Personal details updated successfully');
        loadProfileData().then(() => nextStep());
      } else {
        showToast(json.error || 'Failed to update personal details', 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Add Academic Record
  async function addAcademic(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_academic';

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Academic record added successfully');
        e.target.reset();
        loadProfileData();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Delete Academic Record
  async function deleteAcademic(id) {
    if (!confirm('Are you sure you want to delete this academic record?')) return;
    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_academic', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Academic record deleted');
        loadProfileData();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Add Experience Record
  async function addExperience(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_experience';
    data.is_current = document.getElementById('is_current_cb').checked ? 1 : 0;

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Experience record added successfully');
        e.target.reset();
        toggleEndDateField(false);
        loadProfileData();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Delete Experience Record
  async function deleteExperience(id) {
    if (!confirm('Are you sure you want to delete this experience?')) return;
    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_experience', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Experience record deleted');
        loadProfileData();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Add Skill
  async function addSkill(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_skill';

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Skill added');
        e.target.reset();
        loadProfileData();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Delete Skill
  async function deleteSkill(id) {
    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_skill', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Skill deleted');
        loadProfileData();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Add Project
  async function addProject(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_project';

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Project added successfully');
        e.target.reset();
        loadProfileData();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Delete Project
  async function deleteProject(id) {
    if (!confirm('Are you sure you want to delete this project?')) return;
    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_project', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Project deleted');
        loadProfileData();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Add Achievement
  async function addAchievement(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_achievement';

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Achievement / Certificate added');
        e.target.reset();
        loadProfileData();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Delete Achievement
  async function deleteAchievement(id) {
    if (!confirm('Are you sure you want to delete this achievement?')) return;
    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_achievement', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Achievement record deleted');
        loadProfileData();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Add Hobby
  async function addHobby(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'add_hobby';

    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.success) {
        showToast('Hobby added');
        e.target.reset();
        loadProfileData();
      } else {
        showToast(json.error, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // API Call: Delete Hobby
  async function deleteHobby(id) {
    try {
      const res = await fetch('/api/profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_hobby', id })
      });
      const json = await res.json();
      if (json.success) {
        showToast('Hobby removed');
        loadProfileData();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // Alert/Toast Utility
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

  // Utility: HTML escaping
  function escapeHtml(str) {
    if (!str) return '';
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // ── PREVIEW MODAL LOGIC ────────────────────────────────────
  function openPreviewModal() {
    renderPreviewModal();
    const modal = document.getElementById('preview-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }

  function closePreviewModal() {
    const modal = document.getElementById('preview-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  function renderPreviewModal() {
    const p = profileData.personal || {};
    
    // Header & Personal Bio
    document.getElementById('preview-name').innerText = p.full_name || 'Your Full Name';
    document.getElementById('preview-summary').innerText = p.summary || 'Your professional summary will appear here once saved.';
    
    // Contact Info Grid
    const contacts = [];
    if (p.email) contacts.push(`<div class="flex items-center gap-2 text-on-surface-variant"><span class="material-symbols-outlined text-primary text-sm">mail</span><span>${escapeHtml(p.email)}</span></div>`);
    if (p.phone) contacts.push(`<div class="flex items-center gap-2 text-on-surface-variant"><span class="material-symbols-outlined text-primary text-sm">call</span><span>${escapeHtml(p.phone)}</span></div>`);
    if (p.city || p.nationality) contacts.push(`<div class="flex items-center gap-2 text-on-surface-variant"><span class="material-symbols-outlined text-primary text-sm">location_on</span><span>${escapeHtml(p.city)}${p.nationality ? ', ' + escapeHtml(p.nationality) : ''}</span></div>`);
    if (p.dob) contacts.push(`<div class="flex items-center gap-2 text-on-surface-variant"><span class="material-symbols-outlined text-primary text-sm">cake</span><span>${escapeHtml(p.dob)}</span></div>`);
    document.getElementById('preview-contacts-grid').innerHTML = contacts.length > 0 ? contacts.join('') : '<p class="text-on-surface-variant/60 text-sm">No contact details added yet.</p>';

    // Social Links
    const links = [];
    if (p.linkedin) links.push(`<a href="${escapeHtml(p.linkedin)}" target="_blank" class="flex items-center gap-1.5 text-primary hover:underline text-sm"><span class="material-symbols-outlined text-sm">link</span>LinkedIn</a>`);
    if (p.github) links.push(`<a href="${escapeHtml(p.github)}" target="_blank" class="flex items-center gap-1.5 text-primary hover:underline text-sm"><span class="material-symbols-outlined text-sm">link</span>GitHub</a>`);
    if (p.portfolio) links.push(`<a href="${escapeHtml(p.portfolio)}" target="_blank" class="flex items-center gap-1.5 text-primary hover:underline text-sm"><span class="material-symbols-outlined text-sm">language</span>Portfolio</a>`);
    document.getElementById('preview-links-container').innerHTML = links.length > 0 ? links.join('<span class="text-outline-variant/40">|</span>') : '';

    // Academics Timeline
    const acads = profileData.academics;
    const acadsSection = document.getElementById('preview-academics');
    if (acads.length === 0) {
      acadsSection.innerHTML = '<p class="text-on-surface-variant/60 text-sm italic">No academic history added yet.</p>';
    } else {
      acadsSection.innerHTML = acads.map(r => `
        <div class="border-l-2 border-primary/30 pl-4 py-1 relative">
          <div class="absolute w-3 h-3 bg-primary rounded-full -left-[7px] top-2"></div>
          <h4 class="font-label-md text-on-surface">${escapeHtml(r.degree)}</h4>
          <p class="text-sm font-semibold text-primary/80 mt-0.5">${escapeHtml(r.institution)} <span class="text-on-surface-variant font-normal">(${escapeHtml(r.board_university)})</span></p>
          <p class="text-xs text-on-surface-variant mt-0.5">${r.start_year} – ${r.end_year} | Grade: ${escapeHtml(r.grade)}</p>
          ${r.description ? `<p class="text-xs text-on-surface-variant mt-1.5 bg-surface-container-low p-2 rounded-lg">${escapeHtml(r.description)}</p>` : ''}
        </div>
      `).join('');
    }

    // Experience Timeline
    const exps = profileData.experience;
    const expsSection = document.getElementById('preview-experience');
    if (exps.length === 0) {
      expsSection.innerHTML = '<p class="text-on-surface-variant/60 text-sm italic">No work history added yet.</p>';
    } else {
      expsSection.innerHTML = exps.map(r => `
        <div class="border-l-2 border-secondary/30 pl-4 py-1 relative">
          <div class="absolute w-3 h-3 bg-secondary rounded-full -left-[7px] top-2"></div>
          <h4 class="font-label-md text-on-surface">${escapeHtml(r.job_title)}</h4>
          <p class="text-sm font-semibold text-secondary/80 mt-0.5">${escapeHtml(r.company)} <span class="text-on-surface-variant font-normal">(${escapeHtml(r.location)})</span></p>
          <p class="text-xs text-on-surface-variant mt-0.5">${r.start_date} – ${r.is_current ? 'Present' : r.end_date}</p>
          ${r.description ? `<p class="text-xs text-on-surface-variant mt-1.5 bg-surface-container-low p-2 rounded-lg">${escapeHtml(r.description)}</p>` : ''}
        </div>
      `).join('');
    }

    // Skills Badges
    const skills = profileData.skills;
    const skillsSection = document.getElementById('preview-skills');
    if (skills.length === 0) {
      skillsSection.innerHTML = '<p class="text-on-surface-variant/60 text-sm italic w-full">No skills added yet.</p>';
    } else {
      skillsSection.innerHTML = skills.map(r => `
        <span class="bg-primary-container text-on-primary-container px-3 py-1.5 rounded-full text-xs font-semibold">
          ${escapeHtml(r.skill_name)} <span class="opacity-60 text-[10px]">(${escapeHtml(r.proficiency)})</span>
        </span>
      `).join('');
    }

    // Projects Grid
    const projs = profileData.projects;
    const projsSection = document.getElementById('preview-projects');
    if (projs.length === 0) {
      projsSection.innerHTML = '<p class="text-on-surface-variant/60 text-sm italic">No projects added yet.</p>';
    } else {
      projsSection.innerHTML = projs.map(r => `
        <div class="bg-surface-container-low border border-outline-variant/30 rounded-xl p-4 flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <h4 class="font-label-md text-on-surface">${escapeHtml(r.title)}</h4>
            ${r.url ? `<a href="${escapeHtml(r.url)}" target="_blank" class="text-primary text-xs hover:underline flex items-center gap-0.5"><span class="material-symbols-outlined text-[14px]">open_in_new</span>Link</a>` : ''}
          </div>
          <p class="text-[11px] text-on-surface-variant font-semibold bg-surface px-2 py-0.5 rounded self-start">${escapeHtml(r.tech_stack)}</p>
          <p class="text-xs text-on-surface-variant font-medium">${r.start_date} – ${r.end_date}</p>
          ${r.description ? `<p class="text-xs text-on-surface-variant mt-1 border-t border-outline-variant/20 pt-2">${escapeHtml(r.description)}</p>` : ''}
        </div>
      `).join('');
    }

    // Achievements
    const achs = profileData.achievements;
    const achsSection = document.getElementById('preview-achievements');
    if (achs.length === 0) {
      achsSection.innerHTML = '<p class="text-on-surface-variant/60 text-sm italic">No achievements added yet.</p>';
    } else {
      achsSection.innerHTML = achs.map(r => `
        <div class="flex items-start gap-3 bg-surface p-3 rounded-xl border border-outline-variant/20">
          <span class="material-symbols-outlined text-primary" style="font-size:20px; font-variation-settings: 'FILL' 1;">emoji_events</span>
          <div>
            <h4 class="font-label-md text-on-surface text-sm">${escapeHtml(r.title)}</h4>
            <p class="text-xs text-on-surface-variant font-semibold mt-0.5">${escapeHtml(r.issuer)} <span class="font-normal">(${escapeHtml(r.date)})</span></p>
            ${r.description ? `<p class="text-xs text-on-surface-variant mt-1">${escapeHtml(r.description)}</p>` : ''}
          </div>
        </div>
      `).join('');
    }

    // Hobbies
    const hobs = profileData.hobbies;
    const hobsSection = document.getElementById('preview-hobbies');
    if (hobs.length === 0) {
      hobsSection.innerHTML = '<p class="text-on-surface-variant/60 text-sm italic w-full">No hobbies added yet.</p>';
    } else {
      hobsSection.innerHTML = hobs.map(r => `
        <span class="bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full text-xs font-semibold">
          ${escapeHtml(r.hobby)}
        </span>
      `).join('');
    }
  }
</script>

<!-- ── Preview Profile Modal overlay structure ──────────────── -->
<div id="preview-modal" class="fixed inset-0 z-50 bg-on-background/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant/30 transform scale-100 transition-all">
    <!-- Header -->
    <div class="px-6 py-4 bg-primary text-on-primary flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">account_circle</span>
        <h3 class="font-headline-md text-lg font-bold">Profile Summary View</h3>
      </div>
      <button onclick="closePreviewModal()" class="text-on-primary/80 hover:text-on-primary p-1 rounded-full hover:bg-white/10 transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <!-- Scrollable content -->
    <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-8 bg-background">
      <!-- Row 1: Header Info & Bio -->
      <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
        <div>
          <h2 id="preview-name" class="font-headline-lg text-2xl text-on-surface font-bold">Your Full Name</h2>
          <div id="preview-links-container" class="flex flex-wrap items-center gap-2 mt-2">
            <!-- Populated dynamically -->
          </div>
        </div>
        <p id="preview-summary" class="text-on-surface-variant font-body-md bg-surface p-4 rounded-xl italic border-l-4 border-primary">
          Your summary...
        </p>
        <div id="preview-contacts-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-2">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- Row 2: Timelines (Academics & Experience) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Academics -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
          <h3 class="font-label-md text-primary flex items-center gap-2 border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-sm">school</span> Academic Qualifications
          </h3>
          <div id="preview-academics" class="space-y-6">
            <!-- Populated dynamically -->
          </div>
        </div>

        <!-- Experience -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
          <h3 class="font-label-md text-secondary flex items-center gap-2 border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-sm">work</span> Work Experience
          </h3>
          <div id="preview-experience" class="space-y-6">
            <!-- Populated dynamically -->
          </div>
        </div>
      </div>

      <!-- Row 3: Skills & Projects -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Skills -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
          <h3 class="font-label-md text-primary flex items-center gap-2 border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-sm">psychology</span> Technical & Soft Skills
          </h3>
          <div id="preview-skills" class="flex flex-wrap gap-2">
            <!-- Populated dynamically -->
          </div>
        </div>

        <!-- Projects -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
          <h3 class="font-label-md text-primary flex items-center gap-2 border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-sm">code</span> Projects
          </h3>
          <div id="preview-projects" class="grid grid-cols-1 gap-4">
            <!-- Populated dynamically -->
          </div>
        </div>
      </div>

      <!-- Row 4: Achievements & Hobbies -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Achievements -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
          <h3 class="font-label-md text-primary flex items-center gap-2 border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-sm">emoji_events</span> Awards & Certifications
          </h3>
          <div id="preview-achievements" class="space-y-4">
            <!-- Populated dynamically -->
          </div>
        </div>

        <!-- Hobbies -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/20 shadow-sm flex flex-col gap-4">
          <h3 class="font-label-md text-secondary flex items-center gap-2 border-b border-outline-variant/20 pb-3">
            <span class="material-symbols-outlined text-sm">favorite</span> Hobbies & Interests
          </h3>
          <div id="preview-hobbies" class="flex flex-wrap gap-2">
            <!-- Populated dynamically -->
          </div>
        </div>
      </div>

    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant/30 flex justify-end gap-3">
      <button onclick="closePreviewModal()" class="px-5 py-2 rounded-xl text-on-surface font-label-md bg-surface border border-outline-variant/40 hover:bg-surface-container-high transition-all active:scale-95">Close View</button>
      <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-primary text-on-primary font-label-md hover:opacity-90 transition-all active:scale-95 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">print</span> Print Profile
      </button>
    </div>
  </div>
</div>

<!-- UPLOAD RESUME MODAL -->
<div id="uploadModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-surface-container-lowest w-full max-w-lg rounded-2xl shadow-2xl border border-outline-variant/30 flex flex-col max-h-[90vh]">
    <div class="p-6 border-b border-surface-variant flex justify-between items-center">
      <h2 class="font-headline-md text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">upload_file</span> Import from Resume PDF
      </h2>
      <button onclick="closeUploadModal()" class="p-2 hover:bg-surface-variant rounded-full transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    
    <div class="p-6 flex-1 overflow-y-auto">
      <!-- Upload Area -->
      <div id="upload-area" class="border-2 border-dashed border-outline-variant rounded-2xl p-8 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all" onclick="document.getElementById('file-input').click()">
        <input type="file" id="file-input" accept=".pdf" class="hidden" onchange="handleFileSelect(event)">
        <span class="material-symbols-outlined text-primary text-5xl mb-4 block">description</span>
        <h3 class="font-label-lg text-on-surface mb-2">Drop your PDF here or click to browse</h3>
        <p class="text-on-surface-variant text-xs">Only PDF files accepted. Max 5MB.</p>
        <p id="selected-file-name" class="text-primary font-label-md mt-3 hidden"></p>
      </div>

      <!-- Loading State -->
      <div id="upload-loading" class="hidden flex-col items-center gap-4 py-8">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-on-surface-variant font-label-md">Extracting data from your resume...</p>
        <p class="text-on-surface-variant/60 text-xs">AI is parsing your information</p>
      </div>

      <!-- Preview of extracted data -->
      <div id="extracted-preview" class="hidden mt-4">
        <div class="flex items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-primary text-sm">check_circle</span>
          <h4 class="font-label-md text-on-surface font-bold">Data Extracted Successfully</h4>
        </div>
        <div id="preview-content" class="bg-surface rounded-xl p-4 text-xs text-on-surface-variant max-h-60 overflow-y-auto border border-outline-variant/20"></div>
        <p class="text-xs text-on-surface-variant/60 mt-3">This will replace your current profile data. You can edit it afterwards.</p>
      </div>

      <!-- Error State -->
      <div id="upload-error" class="hidden mt-4 p-4 bg-error/10 border border-error/20 rounded-xl">
        <div class="flex items-start gap-2">
          <span class="material-symbols-outlined text-error text-sm mt-0.5">error</span>
          <p id="error-message" class="text-error text-xs font-label-md whitespace-pre-wrap break-words"></p>
        </div>
      </div>
    </div>

    <div class="p-6 border-t border-surface-variant flex justify-end gap-3">
      <button onclick="closeUploadModal()" class="px-5 py-2.5 rounded-xl border border-outline-variant/40 hover:bg-surface-variant transition-all font-label-md text-on-surface text-xs font-semibold">Cancel</button>
      <button id="import-btn" onclick="importProfileData()" disabled class="px-6 py-2.5 rounded-xl bg-primary text-on-primary hover:opacity-90 font-label-md shadow active:scale-95 transition-all text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
        <span class="material-symbols-outlined text-xs">download</span> Import Data
      </button>
    </div>
  </div>
</div>

<script>
  let extractedProfileData = null;

  function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
    document.body.classList.add('overflow-hidden');
    resetUploadState();
  }

  function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    resetUploadState();
  }

  function resetUploadState() {
    document.getElementById('upload-area').classList.remove('hidden');
    document.getElementById('upload-loading').classList.add('hidden');
    document.getElementById('upload-loading').classList.remove('flex');
    document.getElementById('extracted-preview').classList.add('hidden');
    document.getElementById('upload-error').classList.add('hidden');
    document.getElementById('selected-file-name').classList.add('hidden');
    document.getElementById('import-btn').disabled = true;
    document.getElementById('file-input').value = '';
    extractedProfileData = null;
  }

  function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate PDF
    if (file.type !== 'application/pdf') {
      showError('Only PDF files are accepted');
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      showError('File too large. Maximum size is 5MB');
      return;
    }

    document.getElementById('selected-file-name').textContent = file.name;
    document.getElementById('selected-file-name').classList.remove('hidden');
    document.getElementById('upload-area').classList.add('hidden');
    document.getElementById('upload-loading').classList.remove('hidden');
    document.getElementById('upload-loading').classList.add('flex');

    uploadAndExtract(file);
  }

  async function uploadAndExtract(file) {
    const formData = new FormData();
    formData.append('resume', file);

    try {
      const res = await fetch('/api/upload_resume.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        extractedProfileData = data.profile_data;
        showPreview(data.profile_data);
      } else {
        showError(data.error || 'Failed to extract data', data.debug || null);
      }
    } catch (e) {
      showError('Connection error: ' + e.message);
    }
  }

  function showPreview(data) {
    document.getElementById('upload-loading').classList.add('hidden');
    document.getElementById('upload-loading').classList.remove('flex');
    document.getElementById('extracted-preview').classList.remove('hidden');

    let html = '';
    if (data.personal) {
      html += '<div class="mb-3"><strong class="text-on-surface">Personal:</strong> ' + escapeHtml(data.personal.full_name || '') + ' | ' + escapeHtml(data.personal.email || '') + ' | ' + escapeHtml(data.personal.phone || '') + '</div>';
    }
    if (data.academics && data.academics.length > 0) {
      html += '<div class="mb-3"><strong class="text-on-surface">Education:</strong> ' + data.academics.map(a => escapeHtml(a.degree || '')).join(', ') + '</div>';
    }
    if (data.experience && data.experience.length > 0) {
      html += '<div class="mb-3"><strong class="text-on-surface">Experience:</strong> ' + data.experience.map(e => escapeHtml(e.job_title || '') + ' at ' + escapeHtml(e.company || '')).join('; ') + '</div>';
    }
    if (data.skills && data.skills.length > 0) {
      html += '<div class="mb-3"><strong class="text-on-surface">Skills:</strong> ' + data.skills.map(s => escapeHtml(s.skill_name || '')).join(', ') + '</div>';
    }
    if (data.projects && data.projects.length > 0) {
      html += '<div class="mb-3"><strong class="text-on-surface">Projects:</strong> ' + data.projects.map(p => escapeHtml(p.title || '')).join(', ') + '</div>';
    }
    if (data.achievements && data.achievements.length > 0) {
      html += '<div><strong class="text-on-surface">Achievements:</strong> ' + data.achievements.map(a => escapeHtml(a.title || '')).join(', ') + '</div>';
    }

    document.getElementById('preview-content').innerHTML = html || '<p class="text-on-surface-variant">Limited data extracted. You may need to fill in details manually.</p>';
    document.getElementById('import-btn').disabled = false;
  }

  function showError(msg, debug) {
    document.getElementById('upload-loading').classList.add('hidden');
    document.getElementById('upload-loading').classList.remove('flex');
    document.getElementById('upload-error').classList.remove('hidden');
    let fullMsg = msg;
    if (debug) {
      fullMsg += '\n\n[DEBUG]\n' + JSON.stringify(debug, null, 2);
    }
    document.getElementById('error-message').textContent = fullMsg;
  }

  async function importProfileData() {
    if (!extractedProfileData) return;

    const btn = document.getElementById('import-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-xs ai-loading">autorenew</span> Importing...';

    try {
      const res = await fetch('/api/save_extracted_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ profile_data: extractedProfileData })
      });
      const data = await res.json();

      if (data.success) {
        closeUploadModal();
        showToast('Profile imported successfully!', 'success');
        // Reload page to show new data
        setTimeout(() => window.location.reload(), 1000);
      } else {
        showToast('Error: ' + data.error, 'error');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-xs">download</span> Import Data';
      }
    } catch (e) {
      showToast('Connection error: ' + e.message, 'error');
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-xs">download</span> Import Data';
    }
  }
</script>
</body>
</html>
