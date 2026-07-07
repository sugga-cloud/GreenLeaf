<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();

// Handle quick inline delete action
if (isset($_POST['action']) && $_POST['action'] === 'delete_resume') {
    $resume_id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $user_id]);
    echo '<script>window.location.href = "?page=resumes&deleted=1";</script>';
    exit;
}

// Fetch student plan limits
$user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$student = $user_stmt->fetch();

$plan_stmt = $db->prepare("SELECT * FROM plans WHERE name = ?");
$plan_stmt->execute([$student['current_plan'] ?? 'Starter Launch']);
$user_plan = $plan_stmt->fetch() ?: [
    'name' => 'Starter Launch',
    'max_resumes' => 2,
    'ai_credits' => 5
];

// Count current resumes
$count_stmt = $db->prepare("SELECT COUNT(*) FROM resumes WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$current_resumes = $count_stmt->fetchColumn();

// Handle quick duplicate action
if (isset($_POST['action']) && $_POST['action'] === 'duplicate_resume') {
    if ($current_resumes >= intval($user_plan['max_resumes'])) {
        echo '<script>window.location.href = "?page=resumes&err=limit_reached";</script>';
        exit;
    }

    $resume_id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $user_id]);
    $source = $stmt->fetch();
    if ($source) {
        $ins = $db->prepare("INSERT INTO resumes (user_id, job_profile, template, ai_content, status) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$user_id, $source['job_profile'] . ' (Copy)', $source['template'], $source['ai_content'] ?? null, $source['status'] ?? 'completed']);
        echo '<script>window.location.href = "?page=resumes&duplicated=1";</script>';
        exit;
    }
}

// Fetch all resumes for user_id
$resumes_stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC");
$resumes_stmt->execute([$user_id]);
$resumes = $resumes_stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../components/common/head.php';
?>
<title>My Resumes — GreenLeaf Resume</title>
<style>
  .resume-card {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
  .resume-card:hover {
    transform: translateY(-4px);
  }
  @keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }
  .pulse-dot { animation: pulse-dot 1.5s ease-in-out infinite; }
</style>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/user_dashboard/sidebar.php'; ?>

<!-- Main Content Canvas -->
<main class="md:ml-64 flex flex-col min-h-screen">
  
  <!-- Top bar -->
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
    <!-- Header title -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">My Resumes</h1>
        <p class="text-on-surface-variant font-body-md mt-1">Manage and view all your generated career resumes here.</p>
      </div>
      <a href="?page=select_job_profile" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-xl font-label-md shadow hover:opacity-90 active:scale-95 transition-all">
        <span class="material-symbols-outlined text-sm">add</span> Create New Resume
      </a>
      <a href="?page=debug_logs&auto=5" target="_blank" class="flex items-center gap-2 bg-surface-container text-on-surface px-4 py-3 rounded-xl font-label-md border border-outline-variant/30 hover:bg-surface-variant transition-all text-xs font-bold" title="View AI generation logs">
        <span class="material-symbols-outlined text-sm">terminal</span> View Logs
      </a>
    </div>

    <!-- Credits & Plan Info -->
    <div class="mb-6 p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30 flex items-center gap-4">
      <span class="material-symbols-outlined text-primary">token</span>
      <div class="flex-1">
        <span class="font-label-md text-on-surface">AI Credits: <strong><?= $student['ai_credits'] ?></strong> / <?= $user_plan['ai_credits'] ?></span>
        <span class="text-on-surface-variant text-xs ml-3">Plan: <?= htmlspecialchars($user_plan['name']) ?></span>
      </div>
      <span class="text-on-surface-variant text-xs"><?= $current_resumes ?> / <?= $user_plan['max_resumes'] ?> resumes</span>
    </div>

    <!-- Notification Alerts -->
    <?php if (isset($_GET['err']) && $_GET['err'] === 'limit_reached'): ?>
      <div class="mb-6 p-4 bg-error-container text-on-error-container rounded-xl flex items-center justify-between gap-4 animate-fade-in border border-error/20">
        <div class="flex items-center gap-2.5">
          <span class="material-symbols-outlined text-sm">lock</span>
          <span class="font-label-md text-xs font-semibold"><strong>Max Resumes Limit Reached!</strong> Your current plan (<strong><?= htmlspecialchars($user_plan['name']) ?></strong>) allows up to <strong><?= $user_plan['max_resumes'] ?></strong> resumes. Upgrade to generate more.</span>
        </div>
        <a href="?page=plan" class="bg-error text-on-error text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-sm hover:opacity-90 active:scale-95 transition-all">Upgrade Plan</a>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <div class="mb-6 p-4 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 animate-fade-in">
        <span class="material-symbols-outlined text-sm">delete</span>
        <span class="font-label-md">Resume deleted successfully.</span>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['duplicated'])): ?>
      <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in">
        <span class="material-symbols-outlined text-sm">content_copy</span>
        <span class="font-label-md">Resume duplicated successfully!</span>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['generating'])): ?>
      <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in">
        <span class="material-symbols-outlined text-sm ai-loading">auto_awesome</span>
        <span class="font-label-md">Resume queued for AI generation. Processing now...</span>
      </div>
    <?php endif; ?>

    <!-- Resumes Grid -->
    <?php if (empty($resumes)): ?>
      <div class="flex-1 flex flex-col items-center justify-center text-center py-16 bg-surface-container-lowest rounded-2xl border border-outline-variant/30 shadow-sm p-8">
        <span class="material-symbols-outlined text-primary text-6xl mb-4" style="font-variation-settings:'FILL' 1">description</span>
        <h3 class="font-headline-md text-xl text-on-surface mb-2">No Resumes Found</h3>
        <p class="text-on-surface-variant font-body-md max-w-md mb-6">You haven't generated any resume templates yet. Get started by selecting a target job profile!</p>
        <a href="?page=select_job_profile" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label-md shadow hover:opacity-90 active:scale-95 transition-all">
          Select Job Profile
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($resumes as $r): ?>
          <?php
            $status = $r['status'] ?? 'completed';
            $is_pending = ($status === 'pending' || $status === 'generating');
            $is_failed = ($status === 'failed');
            $is_empty_completed = ($status === 'completed' && empty($r['ai_content']));
          ?>
          <?php if ($is_failed && !empty($r['last_error'])): ?>
            <div class="col-span-full -mb-3 mt-2 px-4 py-2.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[11px] font-mono flex items-start gap-2">
              <span class="material-symbols-outlined text-[16px] mt-0.5">error</span>
              <div class="flex-1">
                <span class="font-bold not-italic text-[10px] uppercase tracking-wider text-red-800">Resume #<?= $r['id'] ?> failed:</span>
                <span class="ml-1"><?= htmlspecialchars($r['last_error']) ?></span>
              </div>
            </div>
          <?php endif; ?>
          <div class="bg-surface-container-lowest border <?= $is_pending ? 'border-primary/40' : ($is_failed ? 'border-error/40' : 'border-outline-variant/30') ?> rounded-2xl p-6 shadow-sm flex flex-col gap-6 resume-card relative overflow-hidden" <?= $is_pending ? 'id="resume-card-' . $r['id'] . '"' : '' ?>>
            <!-- Asymmetric Accent -->
            <div class="absolute top-0 right-0 w-24 h-24 <?= $is_pending ? 'bg-primary/10' : 'bg-primary/5' ?> rounded-bl-[100px] pointer-events-none"></div>

            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl <?= $is_pending ? 'bg-primary/20' : ($is_failed ? 'bg-error/10' : 'bg-primary/10') ?> text-primary flex items-center justify-center relative">
                <span class="material-symbols-outlined text-2xl"><?= $is_failed ? 'error' : 'description' ?></span>
                <!-- Status Dot -->
                <?php if ($status === 'completed'): ?>
                  <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-surface-container-lowest"></span>
                <?php elseif ($is_pending): ?>
                  <span class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full border-2 border-surface-container-lowest pulse-dot"></span>
                <?php elseif ($is_failed): ?>
                  <span class="absolute -top-1 -right-1 w-3 h-3 bg-error rounded-full border-2 border-surface-container-lowest"></span>
                <?php endif; ?>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-headline-md text-headline-md text-on-surface truncate"><?= htmlspecialchars($r['job_profile']) ?></h3>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                  <span class="bg-secondary-container text-on-secondary-container text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 rounded">
                    <?= htmlspecialchars($r['template']) ?>
                  </span>
                  <?php if (!empty($r['ai_content']) && !$is_pending): ?>
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 rounded flex items-center gap-0.5">
                      <span class="material-symbols-outlined text-[10px]">auto_awesome</span> AI
                    </span>
                  <?php endif; ?>
                  <?php if ($is_pending): ?>
                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 rounded flex items-center gap-0.5">
                      <span class="material-symbols-outlined text-[10px] ai-loading">autorenew</span> Generating
                    </span>
                  <?php endif; ?>
                  <?php if ($is_failed): ?>
                    <span class="bg-red-100 text-red-800 text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 rounded flex items-center gap-0.5">
                      <span class="material-symbols-outlined text-[10px]">error</span> Failed
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Date Metadata -->
            <div class="border-t border-outline-variant/20 pt-4 flex items-center justify-between text-xs text-on-surface-variant font-medium">
              <div class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                <span>Created: <?= date('M d, Y', strtotime($r['created_at'])) ?></span>
              </div>
              <?php if ($is_pending): ?>
                <span class="text-amber-600 font-bold flex items-center gap-1">
                  <span class="w-2 h-2 bg-amber-500 rounded-full pulse-dot"></span> Processing
                </span>
              <?php elseif ($is_failed): ?>
                <span class="text-error font-bold flex items-center gap-1">
                  <span class="w-2 h-2 bg-error rounded-full"></span> Failed
                </span>
              <?php else: ?>
                <span class="text-emerald-600 font-bold flex items-center gap-1">
                  <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Ready
                </span>
              <?php endif; ?>
            </div>

            <!-- Actions buttons -->
            <div class="flex flex-col gap-2 mt-2">
              <!-- Row 1: View & AI Modify -->
              <div class="flex gap-2 w-full">
                <?php if ($is_pending): ?>
                  <button disabled class="flex-1 flex items-center justify-center gap-1.5 bg-surface-variant text-on-surface-variant/50 py-2.5 rounded-xl font-label-md text-center text-xs font-bold cursor-not-allowed">
                    <span class="material-symbols-outlined text-xs ai-loading">autorenew</span> Generating...
                  </button>
                <?php elseif ($is_failed): ?>
                  <button onclick="retryGenerate(<?= $r['id'] ?>)" class="flex-1 flex items-center justify-center gap-1.5 bg-error/10 text-error py-2.5 rounded-xl font-label-md text-center shadow-sm hover:bg-error/20 active:scale-95 transition-all text-xs font-bold">
                    <span class="material-symbols-outlined text-xs">refresh</span> Retry
                  </button>
                <?php elseif ($is_empty_completed): ?>
                  <button onclick="retryGenerate(<?= $r['id'] ?>)" class="flex-1 flex items-center justify-center gap-1.5 bg-amber-100 text-amber-800 py-2.5 rounded-xl font-label-md text-center shadow-sm hover:bg-amber-200 active:scale-95 transition-all text-xs font-bold">
                    <span class="material-symbols-outlined text-xs">refresh</span> Regenerate
                  </button>
                <?php else: ?>
                  <a href="?page=preview_resume&id=<?= $r['id'] ?>" class="flex-1 flex items-center justify-center gap-1.5 bg-primary text-on-primary py-2.5 rounded-xl font-label-md text-center shadow-sm hover:opacity-90 active:scale-95 transition-all text-xs font-bold">
                    <span class="material-symbols-outlined text-xs">visibility</span> View
                  </a>
                  <a href="?page=preview_resume&id=<?= $r['id'] ?>&ai=1" class="flex-1 flex items-center justify-center gap-1.5 bg-secondary-container text-on-secondary-container py-2.5 rounded-xl font-label-md text-center shadow-sm hover:opacity-90 active:scale-95 transition-all text-xs font-bold border border-primary/10">
                    <span class="material-symbols-outlined text-xs">auto_awesome</span> Modify
                  </a>
                <?php endif; ?>
              </div>

              <!-- Row 2: Duplicate, Download & Delete -->
              <div class="flex gap-2 w-full items-center">
                <?php if (!$is_pending): ?>
                  <form method="POST" class="m-0 flex-1">
                    <input type="hidden" name="action" value="duplicate_resume">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button type="submit" class="w-full flex items-center justify-center gap-1 border border-outline-variant/60 text-on-surface-variant hover:text-primary py-2 rounded-xl font-label-md text-center hover:bg-surface-variant transition-all active:scale-95 text-xs font-semibold">
                      <span class="material-symbols-outlined text-xs">content_copy</span> Duplicate
                    </button>
                  </form>
                  <a href="?page=preview_resume&id=<?= $r['id'] ?>&standalone=1" target="_blank" class="flex-1 flex items-center justify-center gap-1 border border-outline-variant/60 text-on-surface-variant hover:text-primary py-2 rounded-xl font-label-md text-center hover:bg-surface-variant transition-all active:scale-95 text-xs font-semibold bg-surface-variant/30">
                    <span class="material-symbols-outlined text-xs">download</span> PDF
                  </a>
                <?php endif; ?>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this resume?')" class="m-0">
                  <input type="hidden" name="action" value="delete_resume">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="p-2 rounded-xl bg-error/10 hover:bg-error hover:text-on-error text-error transition-all active:scale-95">
                    <span class="material-symbols-outlined text-xs">delete</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>

<style>
  @keyframes spin-ai { to { transform: rotate(360deg); } }
  .ai-loading { animation: spin-ai 1s linear infinite; }
</style>

<script>
  // Auto-process pending resumes on page load
  document.addEventListener('DOMContentLoaded', () => {
    const pendingCards = document.querySelectorAll('[id^="resume-card-"]');
    pendingCards.forEach(card => {
      const id = card.id.replace('resume-card-', '');
      processResume(id);
    });
  });

  function processResume(id) {
    console.log('[resume #' + id + '] Polling AI generation...');
    fetch('api/process_resume.php?id=' + id)
      .then(res => res.json())
      .then(data => {
        console.log('[resume #' + id + '] Response:', data);
        if (data.status === 'completed' || data.status === 'failed') {
          window.location.reload();
        } else if (data.success === false) {
          console.error('[resume #' + id + '] Generation failed:', data.error);
          window.location.reload();
        } else {
          setTimeout(() => processResume(id), 3000);
        }
      })
      .catch(err => {
        console.error('[resume #' + id + '] Connection error:', err);
        setTimeout(() => processResume(id), 5000);
      });
  }

  function retryGenerate(id) {
    console.log('[resume #' + id + '] Manual retry triggered');
    fetch('api/process_resume.php?id=' + id, { method: 'POST' })
      .then(res => res.json())
      .then(data => {
        console.log('[resume #' + id + '] Retry response:', data);
        if (data.status === 'completed' || data.status === 'failed') {
          window.location.reload();
        } else if (data.success === false) {
          if (data.empty_profile && data.redirect) {
            if (confirm(data.error + '\n\nGo to your profile now?')) {
              window.location.href = data.redirect;
            }
          } else {
            const msg = data.error + (data.log ? '\n\nCheck logs: ' + data.log : '');
            alert(msg);
          }
        } else {
          window.location.reload();
        }
      })
      .catch(err => {
        console.error('[resume #' + id + '] Retry connection error:', err);
        alert('Connection error: ' + err.message);
      });
  }
</script>
</body>
</html>
