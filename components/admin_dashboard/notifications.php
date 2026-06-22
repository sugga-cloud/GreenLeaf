<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

// Handle notifications broadcast POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'broadcast_notification') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type = $_POST['type'] ?? 'Info';
    
    if (!empty($title) && !empty($message)) {
        // Insert dynamic notification to SQLite DB (assigned to demo user 1)
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([1, $title, $message, $type]);

        echo '<script>window.location.href = "?page=admin_dashboard&tab=notifications&sent=1&title=' . urlencode($title) . '";</script>';
        exit;
    }
}

// Query real broadcast list history from notifications table
$past_broadcasts = $db->query("SELECT * FROM notifications ORDER BY created_at DESC")->fetchAll();
?>
<div class="flex flex-col gap-8">
  
  <!-- Header -->
  <div>
    <h1 class="font-headline-lg text-headline-lg text-on-surface">Notification Broadcast Panel</h1>
    <p class="text-on-surface-variant font-body-md mt-1">Compose dynamic global system alerts, warnings, or campaign messages to send to all students.</p>
  </div>

  <!-- Notification Alerts -->
  <?php if (isset($_GET['sent'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm text-xs font-bold">
      <span class="material-symbols-outlined text-sm">campaign</span>
      <span>Successfully broadcasted "<strong><?= htmlspecialchars($_GET['title']) ?></strong>" to all active students!</span>
    </div>
  <?php endif; ?>

  <!-- Two Column Layout -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- Column 1: Notification Creator Form (1/3 Width) -->
    <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-6">
      <h3 class="font-headline-md text-base text-on-surface border-b border-outline-variant/15 pb-3 flex items-center gap-2 font-bold">
        <span class="material-symbols-outlined text-primary">add_alert</span> Broadcast Notification
      </h3>

      <form method="POST" class="flex flex-col gap-4 m-0">
        <input type="hidden" name="action" value="broadcast_notification">
        
        <div>
          <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Alert Title</label>
          <input type="text" name="title" placeholder="e.g. Dynamic resumes catalog update" required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 text-xs font-semibold">
        </div>

        <div>
          <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Alert Type / Severity</label>
          <select name="type" class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 text-xs font-semibold">
            <option value="Info">General Info (Primary blue)</option>
            <option value="Success">Success (Emerald green)</option>
            <option value="Warning">Warning / Critical (Amber/Red)</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-on-surface-variant mb-1 font-semibold text-xs">Message</label>
          <textarea name="message" rows="4" placeholder="Compose alert details..." required class="w-full border border-outline-variant rounded-xl px-4 py-3 bg-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none text-xs font-semibold"></textarea>
        </div>

        <button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-xl font-label-md font-bold shadow-md hover:opacity-90 active:scale-95 transition-all text-xs flex items-center justify-center gap-1.5 mt-2">
          <span class="material-symbols-outlined text-sm">send</span> Broadcast Now
        </button>
      </form>
    </div>

    <!-- Column 2: Broadcast History Timeline (2/3 Width) -->
    <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-6">
      <h3 class="font-headline-md text-base text-on-surface border-b border-outline-variant/15 pb-3 flex items-center gap-2 font-bold">
        <span class="material-symbols-outlined text-primary">history</span> Past Broadcast History
      </h3>

      <div class="flex flex-col gap-4">
        <?php if (empty($past_broadcasts)): ?>
          <div class="text-center py-8 flex flex-col items-center justify-center border border-dashed border-outline-variant/30 rounded-2xl bg-surface/50 p-6 text-on-surface-variant">
            <span class="material-symbols-outlined text-3xl mb-2">history</span>
            <p class="text-xs">No notifications have been broadcasted yet. Send your first alert!</p>
          </div>
        <?php else: ?>
          <?php foreach ($past_broadcasts as $b): ?>
            <?php
              $type = $b['type'];
              $type_cls = 'bg-primary/10 text-primary border border-primary/20';
              if ($type === 'Success') {
                  $type_cls = 'bg-emerald-100 text-emerald-800 border border-emerald-250';
              } elseif ($type === 'Warning') {
                  $type_cls = 'bg-amber-100 text-amber-800 border border-amber-250';
              }
            ?>
            <div class="p-5 border border-outline-variant/20 rounded-xl bg-surface/30 flex flex-col gap-3">
              <div class="flex justify-between items-start gap-2">
                <div>
                  <span class="text-[9px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full <?= $type_cls ?>">
                    <?= $type ?>
                  </span>
                  <h4 class="font-headline-md text-sm font-extrabold text-on-surface mt-2">
                    <?= htmlspecialchars($b['title']) ?>
                  </h4>
                </div>
                <span class="text-[9px] font-semibold text-on-surface-variant">
                  Sent: <?= date('M d, Y H:i', strtotime($b['created_at'])) ?>
                </span>
              </div>
              <p class="text-xs text-on-surface-variant leading-relaxed font-medium">
                <?= htmlspecialchars($b['message']) ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

</div>
