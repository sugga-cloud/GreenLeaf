<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';

// Handle support ticket updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_ticket_status') {
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    $new_status = $_POST['status'] ?? 'Open';
    $reply_message = trim($_POST['reply_message'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 1);

    if ($ticket_id > 0) {
        // Update ticket status
        $stmt = $db->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $ticket_id]);

        // Send a dynamic notification to the student user
        $notif_title = "Support Ticket #$ticket_id Updated to $new_status";
        $type = 'Info';
        if ($new_status === 'Resolved') {
            $type = 'Success';
        } elseif ($new_status === 'Open') {
            $type = 'Warning';
        }

        // Use custom message if provided, otherwise compile a default one
        $final_message = $reply_message;
        if (empty($final_message)) {
            $final_message = "Your support ticket #$ticket_id has been changed to status '$new_status'. Please check the resolution console.";
        }

        $notif_stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $notif_stmt->execute([$user_id, $notif_title, $final_message, $type]);

        echo '<script>window.location.href = "?page=admin_dashboard&tab=tickets&updated_status=1";</script>';
        exit;
    }
}

// Fetch all support tickets raised by students
$tickets = $db->query("SELECT * FROM tickets ORDER BY created_at DESC")->fetchAll();
?>
<div class="flex flex-col gap-8">
  
  <!-- Header -->
  <div>
    <h1 class="font-headline-lg text-headline-lg text-on-surface">Support Ticket Resolutions</h1>
    <p class="text-on-surface-variant font-body-md mt-1">Review dynamic tickets raised by students, compose resolution notices, and dispatch system alerts.</p>
  </div>

  <!-- Notification Alerts -->
  <?php if (isset($_GET['updated_status'])): ?>
    <div class="p-4 bg-primary-container text-on-primary-container rounded-xl flex items-center gap-2 animate-fade-in border border-primary/10 shadow-sm text-xs font-bold">
      <span class="material-symbols-outlined text-sm">check_circle</span>
      <span>Support ticket updated and resolution notice dispatched to the student's notification center!</span>
    </div>
  <?php endif; ?>

  <!-- Ticket Dashboard Cards -->
  <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6">
    <h3 class="font-headline-md text-base text-on-surface border-b border-outline-variant/15 pb-3 flex items-center gap-2 font-bold mb-6">
      <span class="material-symbols-outlined text-primary">support_agent</span> Raised Student Tickets
    </h3>

    <?php if (empty($tickets)): ?>
      <div class="text-center py-12 flex flex-col items-center justify-center border border-dashed border-outline-variant/30 rounded-2xl bg-surface/50 p-6">
        <span class="material-symbols-outlined text-on-surface-variant/40 text-5xl mb-3">help_outline</span>
        <h4 class="font-label-md text-on-surface font-bold">All caught up!</h4>
        <p class="text-xs text-on-surface-variant max-w-sm mt-1">No student support tickets are currently pending. High five! ðŸŒŸ</p>
      </div>
    <?php else: ?>
      <div class="flex flex-col gap-6">
        <?php foreach ($tickets as $t): ?>
          <?php
            $status = $t['status'];
            $badge_class = 'bg-secondary-container text-on-secondary-container';
            if ($status === 'Open') {
                $badge_class = 'bg-amber-100 text-amber-800 border border-amber-200';
            } elseif ($status === 'In Progress') {
                $badge_class = 'bg-blue-100 text-blue-800 border border-blue-200';
            } elseif ($status === 'Resolved') {
                $badge_class = 'bg-emerald-100 text-emerald-800 border border-emerald-250';
            }
          ?>
          <div class="p-6 border border-outline-variant/20 rounded-xl bg-surface/30 flex flex-col lg:flex-row gap-6 justify-between">
            
            <!-- Left Info Block (2/3 Width) -->
            <div class="flex-1 flex flex-col gap-3">
              <div class="flex flex-wrap gap-2 items-center">
                <span class="text-[9px] uppercase font-extrabold tracking-wider bg-primary/10 text-primary px-2.5 py-0.5 rounded-full">
                  <?= htmlspecialchars($t['category']) ?>
                </span>
                <span class="text-[9px] font-bold uppercase px-2.5 py-0.5 rounded-full <?= $badge_class ?>">
                  <?= htmlspecialchars($status) ?>
                </span>
              </div>
              
              <h4 class="font-headline-md text-sm font-extrabold text-on-surface">
                Ticket #<?= $t['id'] ?>: <?= htmlspecialchars($t['subject']) ?>
              </h4>
              
              <p class="text-xs text-on-surface-variant leading-relaxed bg-surface p-3.5 rounded-lg border border-outline-variant/10 font-semibold">
                <?= nl2br(htmlspecialchars($t['description'])) ?>
              </p>
              
              <div class="text-[10px] text-on-surface-variant font-bold mt-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-xs">schedule</span>
                <span>Submitted: <?= date('M d, Y H:i', strtotime($t['created_at'])) ?></span>
              </div>
            </div>

            <!-- Right Notice Response & Action Block (1/3 Width) -->
            <div class="lg:w-80 border-t lg:border-t-0 lg:border-l border-outline-variant/15 pt-5 lg:pt-0 lg:pl-6 flex flex-col gap-4">
              <span class="text-[10px] font-extrabold text-primary uppercase tracking-wider">Resolution Notice / Reply</span>
              
              <form method="POST" class="m-0 flex flex-col gap-3">
                <input type="hidden" name="action" value="update_ticket_status">
                <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($t['user_id'] ?? 1) ?>">
                
                <!-- Notice Text Input -->
                <div>
                  <textarea name="reply_message" rows="3" placeholder="Write notice/response here... (e.g. margin issue resolved, try PDF preview now)" class="w-full border border-outline-variant rounded-xl p-3 bg-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40 font-semibold resize-none placeholder:text-[10px]"></textarea>
                  <span class="block text-[8px] text-on-surface-variant mt-1">This text will be sent to the student's personal notification center instantly.</span>
                </div>

                <!-- Interactive Buttons based on ticket state -->
                <div class="flex flex-col gap-2">
                  <?php if ($status !== 'In Progress' && $status !== 'Resolved'): ?>
                    <button type="submit" name="status" value="In Progress" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-label-md py-2.5 rounded-xl text-xs font-bold active:scale-95 transition-all shadow flex items-center justify-center gap-1.5">
                      <span class="material-symbols-outlined text-xs">pending</span> Mark In Progress & Notify
                    </button>
                  <?php endif; ?>

                  <?php if ($status !== 'Resolved'): ?>
                    <button type="submit" name="status" value="Resolved" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-label-md py-2.5 rounded-xl text-xs font-bold active:scale-95 transition-all shadow flex items-center justify-center gap-1.5">
                      <span class="material-symbols-outlined text-xs">check_circle</span> Mark Resolved & Notify
                    </button>
                  <?php else: ?>
                    <button type="submit" name="status" value="Open" class="w-full border border-outline-variant/60 hover:bg-surface-variant text-on-surface-variant font-label-md py-2.5 rounded-xl text-xs font-bold active:scale-95 transition-all flex items-center justify-center gap-1.5">
                      <span class="material-symbols-outlined text-xs">restore</span> Reopen Ticket & Notify
                    </button>
                  <?php endif; ?>
                </div>
              </form>
            </div>
            
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
