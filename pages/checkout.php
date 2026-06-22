<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

$user_id = Auth::user_id();
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch();

$plan_id = intval($_POST['plan_id'] ?? $_GET['plan_id'] ?? 0);
$plan = null;
$plan_templates = [];
$plan_perms = [];

if ($plan_id) {
    $stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    if ($plan) {
        $pt_stmt = $db->prepare("
            SELECT t.* FROM templates t
            INNER JOIN plan_templates pt ON pt.template_id = t.id
            WHERE pt.plan_id = ? AND LOWER(t.status) = 'active'
            ORDER BY t.id ASC
        ");
        $pt_stmt->execute([$plan_id]);
        $plan_templates = $pt_stmt->fetchAll();
        $plan_perms = [
            'AI Modify widget'    => !empty($plan['perm_ai_modify']),
            'Web Speech / Voice'  => !empty($plan['perm_web_speech']),
            'Custom job profiles' => !empty($plan['perm_custom_profiles']),
            'PDF print'           => !empty($plan['perm_pdf_print']),
        ];
    }
}

$curr_stmt = $db->prepare("SELECT value FROM settings WHERE key = 'platform_currency'");
$curr_stmt->execute();
$currency_code = $curr_stmt->fetchColumn() ?: 'USD';
$currencies = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹', 'CAD' => 'C$', 'AUD' => 'A$'];
$currency_symbol = $currencies[$currency_code] ?? '$';

$purchase_success = false;
$old_plan_name = $user['current_plan'] ?? 'Starter Launch';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_purchase']) && $plan) {
    $card_name  = trim($_POST['card_name'] ?? '');
    $card_num   = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_exp   = trim($_POST['card_expiry'] ?? '');
    $card_cvv   = trim($_POST['card_cvv'] ?? '');

    $errors = [];
    if (strlen($card_num) < 12) $errors[] = 'Invalid card number';
    if (strlen($card_cvv) < 3) $errors[] = 'Invalid CVV';
    if (empty($card_name)) $errors[] = 'Cardholder name required';
    if (!preg_match('/^\d{2}\/\d{2}$/', $card_exp)) $errors[] = 'Invalid expiry (MM/YY)';

    if (empty($errors)) {
        $old_plan_name = $user['current_plan'] ?? 'Starter Launch';
        $old_credits   = (int)($user['ai_credits'] ?? 0);

        $db->prepare("
            UPDATE users
            SET current_plan = ?,
                ai_credits = ?,
                plan_expiry = date('now', '+' || ? || ' days'),
                plan_subscribed_at = datetime('now')
            WHERE id = ?
        ")->execute([$plan['name'], $plan['ai_credits'], $plan['duration_days'], $user_id]);

        try {
            $db->prepare("
                INSERT INTO notifications (user_id, type, title, body, created_at)
                VALUES (?, 'plan', 'Plan Activated', ?, datetime('now'))
            ")->execute([
                $user_id,
                "Your {$plan['name']} plan is now active. {$plan['ai_credits']} AI credits added."
            ]);
        } catch (Exception $e) {}

        $purchase_success = true;
        $transaction_id = 'TXN-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));
        $user = $db->prepare("SELECT * FROM users WHERE id = ?");
        $user->execute([$user_id]);
        $user = $user->fetch();
    } else {
        $error_msg = implode(' • ', $errors);
    }
}

if (!$plan) {
    echo '<script>window.location.href = "?page=plan";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Checkout - <?= htmlspecialchars($plan['name']) ?></title>
<?php include __DIR__ . '/../components/common/head.php'; ?>
<style>
  .checkout-bg {
    background: linear-gradient(135deg, #f8faf5 0%, #e8f5e9 50%, #f1f8e9 100%);
  }
  .glass-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 20px 60px -10px rgba(0, 100, 0, 0.08), 0 8px 20px -5px rgba(0, 0, 0, 0.04);
  }
  .input-field {
    background: rgba(255, 255, 255, 0.9);
    border: 1.5px solid #e5e7eb;
    transition: all 0.2s ease;
  }
  .input-field:focus {
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
    outline: none;
  }
  .pay-btn {
    background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
    box-shadow: 0 8px 20px -4px rgba(46, 125, 50, 0.4);
    transition: all 0.25s ease;
  }
  .pay-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px -4px rgba(46, 125, 50, 0.5);
  }
  .pay-btn:active:not(:disabled) {
    transform: translateY(0);
  }
  .pay-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  .success-circle {
    animation: pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }
  .success-check {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    animation: draw 0.6s ease-out 0.3s forwards;
  }
  @keyframes pop {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }
  @keyframes draw {
    to { stroke-dashoffset: 0; }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .fade-up {
    animation: fadeUp 0.6s ease-out forwards;
  }
  .confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    pointer-events: none;
    z-index: 9999;
  }
  @keyframes fall {
    0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
  }
  .summary-row {
    transition: all 0.2s ease;
  }
  .summary-row:hover {
    background: rgba(46, 125, 50, 0.04);
  }
</style>
</head>
<body class="bg-surface font-body text-on-surface">
<main class="min-h-screen checkout-bg pb-24">
  <?php if (!$purchase_success): ?>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center gap-2 mb-6 text-sm text-on-surface-variant">
      <a href="?page=plan" class="hover:text-primary transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Back to Plans
      </a>
      <span class="material-symbols-outlined text-[16px]">chevron_right</span>
      <span class="text-on-surface font-medium">Checkout</span>
    </div>

    <div class="mb-8 fade-up">
      <h1 class="font-headline-md text-3xl text-on-surface font-extrabold">Complete your purchase</h1>
      <p class="text-on-surface-variant mt-2">Review your plan and enter payment details to activate.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
      <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2 fade-up">
        <span class="material-symbols-outlined text-[20px]">error</span>
        <?= htmlspecialchars($error_msg) ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <div class="lg:col-span-3 fade-up" style="animation-delay: 0.1s">
        <div class="glass-card rounded-2xl p-6 sm:p-8">
          <h2 class="font-headline-md text-xl text-on-surface font-bold mb-1">Payment Details</h2>
          <p class="text-xs text-on-surface-variant mb-6">All transactions are secured and encrypted.</p>

          <form method="POST" id="checkoutForm" class="flex flex-col gap-5">
            <input type="hidden" name="confirm_purchase" value="1">
            <input type="hidden" name="plan_id" value="<?= $plan_id ?>">

            <div>
              <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Cardholder Name</label>
              <input type="text" name="card_name" required value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>"
                     class="input-field w-full px-4 py-3 rounded-xl text-sm"
                     placeholder="John Doe">
            </div>

            <div>
              <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Card Number</label>
              <div class="relative">
                <input type="text" name="card_number" id="cardNumber" required maxlength="19"
                       value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"
                       class="input-field w-full px-4 py-3 pr-14 rounded-xl text-sm tracking-wider"
                       placeholder="4242 4242 4242 4242">
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[22px]">credit_card</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Expiry</label>
                <input type="text" name="card_expiry" id="cardExpiry" required maxlength="5"
                       value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>"
                       class="input-field w-full px-4 py-3 rounded-xl text-sm"
                       placeholder="MM/YY">
              </div>
              <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">CVV</label>
                <input type="text" name="card_cvv" id="cardCvv" required maxlength="4"
                       value="<?= htmlspecialchars($_POST['card_cvv'] ?? '') ?>"
                       class="input-field w-full px-4 py-3 rounded-xl text-sm"
                       placeholder="123">
              </div>
            </div>

            <div class="bg-primary/5 border border-primary/15 rounded-xl p-4 flex items-start gap-3">
              <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">lock</span>
              <div class="flex-1">
                <p class="text-xs font-bold text-on-surface">Secure Checkout</p>
                <p class="text-[11px] text-on-surface-variant mt-0.5">Your card is charged once. Subscription auto-renews in <?= $plan['duration_days'] ?> days unless canceled.</p>
              </div>
            </div>

            <button type="submit" id="payBtn" class="pay-btn w-full text-white py-4 rounded-xl font-label-md font-bold uppercase tracking-wider text-sm flex items-center justify-center gap-2">
              <span class="material-symbols-outlined text-[20px]">lock</span>
              <span id="payBtnText">Pay <?= $currency_symbol ?><?= number_format($plan['price'], 2) ?></span>
              <span id="paySpinner" class="hidden material-symbols-outlined text-[20px] animate-spin">progress_activity</span>
            </button>
          </form>
        </div>
      </div>

      <div class="lg:col-span-2 fade-up" style="animation-delay: 0.2s">
        <div class="glass-card rounded-2xl p-6 sticky top-6">
          <h2 class="font-headline-md text-lg text-on-surface font-bold mb-1">Order Summary</h2>
          <p class="text-xs text-on-surface-variant mb-4">Plan: <span class="font-bold text-primary"><?= htmlspecialchars($plan['name']) ?></span></p>

          <div class="bg-gradient-to-br from-primary/8 to-primary/3 border border-primary/15 rounded-xl p-4 mb-4">
            <p class="text-[10px] text-on-surface-variant uppercase tracking-widest font-bold">Plan</p>
            <p class="text-xl font-extrabold text-on-surface mt-0.5"><?= htmlspecialchars($plan['name']) ?></p>
            <div class="mt-3 flex items-baseline gap-1">
              <span class="text-3xl font-extrabold text-on-surface"><?= $currency_symbol ?><?= number_format($plan['price'], 2) ?></span>
              <span class="text-xs text-on-surface-variant">/ <?= $plan['duration_days'] ?> days</span>
            </div>
          </div>

          <div class="flex flex-col gap-2.5 mb-4">
            <div class="summary-row flex items-center gap-2 p-2 rounded-lg">
              <span class="material-symbols-outlined text-emerald-600 text-[18px]">description</span>
              <span class="text-xs text-on-surface-variant"><?= $plan['max_resumes'] > 5000 ? 'Unlimited' : $plan['max_resumes'] ?> resume slots</span>
            </div>
            <div class="summary-row flex items-center gap-2 p-2 rounded-lg">
              <span class="material-symbols-outlined text-amber-600 text-[18px]">auto_awesome</span>
              <span class="text-xs text-on-surface-variant"><?= $plan['ai_credits'] ?> AI credits</span>
            </div>
            <?php if (!empty($plan_templates)): ?>
              <div class="summary-row p-2 rounded-lg">
                <div class="flex items-center gap-2 mb-1.5">
                  <span class="material-symbols-outlined text-purple-600 text-[18px]">workspace_premium</span>
                  <span class="text-xs font-bold text-on-surface">Premium Templates</span>
                </div>
                <div class="flex flex-wrap gap-1.5 ml-6">
                  <?php foreach ($plan_templates as $t): ?>
                    <span class="text-[10px] bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded-full font-medium">
                      <?= htmlspecialchars($t['name']) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
            <?php if (array_filter($plan_perms)): ?>
              <div class="summary-row p-2 rounded-lg">
                <div class="flex items-center gap-2 mb-1.5">
                  <span class="material-symbols-outlined text-blue-600 text-[18px]">verified</span>
                  <span class="text-xs font-bold text-on-surface">Permissions</span>
                </div>
                <div class="flex flex-col gap-1 ml-6">
                  <?php foreach ($plan_perms as $label => $enabled): ?>
                    <?php if ($enabled): ?>
                      <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-blue-500 text-[12px]">check_circle</span>
                        <span class="text-[10px] text-on-surface-variant"><?= htmlspecialchars($label) ?></span>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="border-t border-outline-variant/20 pt-4 space-y-2">
            <div class="flex justify-between text-xs text-on-surface-variant">
              <span>Subtotal</span>
              <span><?= $currency_symbol ?><?= number_format($plan['price'], 2) ?></span>
            </div>
            <div class="flex justify-between text-xs text-on-surface-variant">
              <span>Tax</span>
              <span><?= $currency_symbol ?>0.00</span>
            </div>
            <div class="flex justify-between text-base font-extrabold text-on-surface pt-2 border-t border-outline-variant/15">
              <span>Total</span>
              <span><?= $currency_symbol ?><?= number_format($plan['price'], 2) ?></span>
            </div>
          </div>

          <div class="mt-4 flex items-center justify-center gap-3 text-[10px] text-on-surface-variant">
            <span class="material-symbols-outlined text-[14px]">shield</span>
            <span>256-bit SSL Encrypted</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
    <div class="glass-card rounded-3xl p-8 sm:p-12 text-center">
      <div class="success-circle mx-auto w-24 h-24 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center shadow-lg mb-6">
        <svg class="w-14 h-14" viewBox="0 0 52 52" fill="none">
          <circle class="opacity-25" cx="26" cy="26" r="24" stroke="white" stroke-width="2" fill="none"/>
          <path class="success-check" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" d="M14 27l8 8 16-18"/>
        </svg>
      </div>

      <h1 class="font-headline-md text-3xl text-on-surface font-extrabold mb-2 fade-up" style="animation-delay: 0.4s">Payment Successful!</h1>
      <p class="text-on-surface-variant mb-6 fade-up" style="animation-delay: 0.5s">
        Your <span class="font-bold text-primary"><?= htmlspecialchars($plan['name']) ?></span> plan is now active.
      </p>

      <div class="bg-gradient-to-br from-emerald-50 to-primary/5 border border-emerald-200 rounded-2xl p-5 mb-6 text-left fade-up" style="animation-delay: 0.6s">
        <div class="flex items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-emerald-600">receipt_long</span>
          <h3 class="font-bold text-on-surface text-sm">Transaction Receipt</h3>
        </div>
        <div class="space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Transaction ID</span>
            <span class="font-mono font-bold text-on-surface"><?= $transaction_id ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Date</span>
            <span class="font-medium text-on-surface"><?= date('M d, Y H:i') ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Previous Plan</span>
            <span class="font-medium text-on-surface"><?= htmlspecialchars($old_plan_name) ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">New Plan</span>
            <span class="font-bold text-primary"><?= htmlspecialchars($plan['name']) ?></span>
          </div>
          <div class="flex justify-between border-t border-emerald-200 pt-2 mt-2">
            <span class="text-on-surface-variant font-bold">Amount Charged</span>
            <span class="font-extrabold text-on-surface text-base"><?= $currency_symbol ?><?= number_format($plan['price'], 2) ?></span>
          </div>
        </div>
      </div>

      <div class="bg-surface-container-low rounded-2xl p-5 mb-6 text-left fade-up" style="animation-delay: 0.7s">
        <div class="flex items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-primary">workspace_premium</span>
          <h3 class="font-bold text-on-surface text-sm">What's Active Now</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
            <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
            <span class="text-xs text-on-surface"><?= $plan['ai_credits'] ?> AI credits loaded</span>
          </div>
          <?php if ($plan['max_resumes'] > 5000): ?>
            <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
              <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
              <span class="text-xs text-on-surface">Unlimited resumes</span>
            </div>
          <?php else: ?>
            <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
              <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
              <span class="text-xs text-on-surface"><?= $plan['max_resumes'] ?> resume slots</span>
            </div>
          <?php endif; ?>
          <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
            <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
            <span class="text-xs text-on-surface">Valid until <?= date('M d, Y', strtotime($user['plan_expiry'])) ?></span>
          </div>
          <?php foreach ($plan_perms as $label => $enabled): ?>
            <?php if ($enabled): ?>
              <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
                <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
                <span class="text-xs text-on-surface"><?= htmlspecialchars($label) ?></span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php foreach ($plan_templates as $t): ?>
            <div class="flex items-center gap-2 p-2 bg-purple-50 rounded-lg border border-purple-200">
              <span class="material-symbols-outlined text-purple-600 text-[16px]">workspace_premium</span>
              <span class="text-xs text-on-surface font-medium"><?= htmlspecialchars($t['name']) ?> template</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 fade-up" style="animation-delay: 0.8s">
        <a href="?page=user_dashboard" class="flex-1 bg-primary text-on-primary py-3.5 rounded-xl font-label-md font-bold uppercase tracking-wider text-xs flex items-center justify-center gap-2 hover:opacity-90 active:scale-95 transition-all shadow-md">
          <span class="material-symbols-outlined text-[18px]">dashboard</span>
          Go to Dashboard
        </a>
        <a href="?page=template_store" class="flex-1 bg-surface-container-low text-on-surface py-3.5 rounded-xl font-label-md font-bold uppercase tracking-wider text-xs flex items-center justify-center gap-2 hover:bg-surface-container transition-colors border border-outline-variant/30">
          <span class="material-symbols-outlined text-[18px]">style</span>
          Browse Templates
        </a>
      </div>

      <p class="text-[10px] text-on-surface-variant mt-6 fade-up" style="animation-delay: 0.9s">
        A receipt has been sent to your registered email. Need help? <a href="?page=support" class="text-primary font-bold hover:underline">Contact support</a>
      </p>
    </div>
  </div>
  <script>
    (function() {
      const colors = ['#2e7d32', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6', '#10b981', '#f43f5e', '#06b6d4'];
      const shapes = ['■', '●', '▲', '◆', '★', '✦'];
      for (let i = 0; i < 60; i++) {
        const c = document.createElement('div');
        c.className = 'confetti';
        c.style.left = Math.random() * 100 + 'vw';
        c.style.top = '-20px';
        c.style.background = colors[Math.floor(Math.random() * colors.length)];
        c.style.color = colors[Math.floor(Math.random() * colors.length)];
        c.style.fontSize = (Math.random() * 12 + 8) + 'px';
        c.style.animation = `fall ${Math.random() * 2 + 2}s linear ${Math.random() * 1.5}s forwards`;
        c.textContent = shapes[Math.floor(Math.random() * shapes.length)];
        c.style.transform = 'translateY(0)';
        document.body.appendChild(c);
      }
    })();
  </script>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../components/common/app_footer.php'; ?>
<?php include __DIR__ . '/../components/common/bottom_nav.php'; ?>
<script>
  document.getElementById('cardNumber')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 16);
    e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
  });
  document.getElementById('cardExpiry')?.addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
    e.target.value = v;
  });
  document.getElementById('cardCvv')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
  });
  const form = document.getElementById('checkoutForm');
  if (form) {
    form.addEventListener('submit', function() {
      const btn = document.getElementById('payBtn');
      const txt = document.getElementById('payBtnText');
      const sp  = document.getElementById('paySpinner');
      btn.disabled = true;
      txt.textContent = 'Processing...';
      sp.classList.remove('hidden');
    });
  }
</script>
</body>
</html>
