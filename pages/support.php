<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_ticket') {
    $subject = trim($_POST['subject'] ?? '');
    $category = trim($_POST['category'] ?? 'General Question');
    $description = trim($_POST['description'] ?? '');

    if (!empty($subject) && !empty($description)) {
        $stmt = $db->prepare("INSERT INTO tickets (user_id, subject, category, description, status) VALUES (?, ?, ?, ?, 'Open')");
        $stmt->execute([$user_id, $subject, $category, $description]);
        echo '<script>window.location.href = "?page=support&created=1";</script>';
        exit;
    }
}

$tickets_stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
$tickets_stmt->execute([$user_id]);
$tickets = $tickets_stmt->fetchAll();

include __DIR__ . '/../components/common/head.php';
?>
<title>Support Ticket Hub — GreenLeaf Resume</title>
<style>
  .ticket-item {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
  .ticket-item:hover {
    transform: translateX(4px);
  }
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
    
    <!-- Title Section -->
    <div class="mb-8">
      <h1 class="font-headline-lg text-headline-lg text-on-surface">Support Ticket Hub</h1>
      <p class="text-on-surface-variant font-body-md mt-1">Submit support tickets, report system issues, or check status on dynamic queries.</p>
    </div>

    <!-- Alert notifications -->
    <?php if (isset($_GET['created'])): ?>
      <div class="mb-6 p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10">
        <span class="material-symbols-outlined text-sm">confirmation_number</span>
        <span class="font-label-md">Support ticket created successfully! Our agents will respond shortly.</span>
      </div>
    <?php endif; ?>

    <!-- Two Column Desktop Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
      
      <!-- Column 1: Ticket Creator Form (1/3 Width) -->
      <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-6">
        <h3 class="font-headline-md text-lg text-on-surface border-b border-outline-variant/15 pb-3 flex items-center gap-2 font-bold">
          <span class="material-symbols-outlined text-primary">add_box</span> Raise Support Ticket
        </h3>
        
        <form method="POST" class="flex flex-col gap-4">
          <input type="hidden" name="action" value="create_ticket">
          
          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold">Subject / Title</label>
            <input type="text" name="subject" placeholder="e.g. Payment deduction failure" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all text-sm">
          </div>

          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold">Category</label>
            <select name="category" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 transition-all text-sm">
              <option value="Resume Generation">Resume Builder & AI Tool</option>
              <option value="Plan Upgrade & Billing">Billing & Subscriptions</option>
              <option value="Bug / System Issue">Technical Bug / Error</option>
              <option value="General Question">General Query</option>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-on-surface-variant mb-1 font-semibold">Detailed Description</label>
            <textarea name="description" rows="5" placeholder="Please describe your issue, steps to reproduce, or dynamic queries in detail..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all text-sm"></textarea>
          </div>

          <button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-xl font-label-md font-bold shadow-md hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 mt-2">
            <span class="material-symbols-outlined text-sm">send</span> Submit Ticket
          </button>
        </form>
      </div>

      <!-- Column 2: Tickets Timeline List (2/3 Width) -->
      <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-6">
        <h3 class="font-headline-md text-lg text-on-surface border-b border-outline-variant/15 pb-3 flex items-center gap-2 font-bold">
          <span class="material-symbols-outlined text-primary">history</span> Ticket History
        </h3>

        <?php if (empty($tickets)): ?>
          <div class="text-center py-12 flex flex-col items-center justify-center border border-dashed border-outline-variant/30 rounded-2xl bg-surface/50 p-6">
            <span class="material-symbols-outlined text-on-surface-variant/40 text-5xl mb-3">help_outline</span>
            <h4 class="font-label-md text-on-surface font-bold">No Support Tickets</h4>
            <p class="text-xs text-on-surface-variant max-w-sm mt-1">You haven't opened any support requests yet. Raise a ticket on the left side to get premium technical help.</p>
          </div>
        <?php else: ?>
          <div class="flex flex-col gap-4">
            <?php foreach ($tickets as $t): ?>
              <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
                // Map status badges
                $status = $t['status'];
                $badge_class = 'bg-secondary-container text-on-secondary-container';
                if ($status === 'Open') {
                    $badge_class = 'bg-amber-100 text-amber-800 border border-amber-250';
                } elseif ($status === 'In Progress') {
                    $badge_class = 'bg-blue-100 text-blue-800 border border-blue-250';
                } elseif ($status === 'Resolved') {
                    $badge_class = 'bg-emerald-100 text-emerald-800 border border-emerald-250';
                }
              ?>
              <div class="p-5 border border-outline-variant/20 rounded-xl bg-surface/30 flex flex-col gap-3 ticket-item">
                <div class="flex flex-wrap justify-between items-start gap-2">
                  <div>
                    <span class="text-[10px] uppercase font-extrabold tracking-wider bg-primary/10 text-primary px-2.5 py-0.5 rounded-full">
                      <?= htmlspecialchars($t['category']) ?>
                    </span>
                    <h4 class="font-headline-md text-sm font-extrabold text-on-surface mt-2">#<?= $t['id'] ?>: <?= htmlspecialchars($t['subject']) ?></h4>
                  </div>
                  <span class="text-[10px] font-bold uppercase px-3 py-1 rounded-full <?= $badge_class ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </div>
                
                <p class="text-xs text-on-surface-variant leading-relaxed">
                  <?= nl2br(htmlspecialchars($t['description'])) ?>
                </p>

                <div class="border-t border-outline-variant/10 pt-2 flex justify-between items-center text-[10px] text-on-surface-variant font-semibold">
                  <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">schedule</span>
                    <span>Submitted: <?= date('M d, Y H:i', strtotime($t['created_at'])) ?></span>
                  </span>
                  <span>Agent assigned: GreenLeaf AI Agent</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>

  <?php include __DIR__ . '/../components/common/app_footer.php'; ?>
</main>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>
</body>
</html>
