<?php
// Mock transactions dataset to make the system feel alive and beautiful immediately
$payments = [
    ['id' => 'TXN-90210', 'name' => 'Sazid Ahmed', 'email' => 'sazid@greenleaf.com', 'plan' => 'Pro Career Growth', 'amount' => '$19.99', 'status' => 'Succeeded', 'date' => '2026-05-18 14:32'],
    ['id' => 'TXN-88219', 'name' => 'Sarah Connor', 'email' => 'sarah@resistance.org', 'plan' => 'Pro Career Growth', 'amount' => '$19.99', 'status' => 'Succeeded', 'date' => '2026-05-16 09:12'],
    ['id' => 'TXN-74620', 'name' => 'John Doe', 'email' => 'john@doe.net', 'plan' => 'Elite Career Advanced', 'amount' => '$49.99', 'status' => 'Succeeded', 'date' => '2026-05-15 18:24'],
    ['id' => 'TXN-65239', 'name' => 'Bruce Wayne', 'email' => 'bruce@waynecorp.com', 'plan' => 'Pro Career Growth', 'amount' => '$19.99', 'status' => 'Failed', 'date' => '2026-05-12 11:05'],
    ['id' => 'TXN-54123', 'name' => 'Diana Prince', 'email' => 'diana@themyscira.gov', 'plan' => 'Elite Career Advanced', 'amount' => '$49.99', 'status' => 'Succeeded', 'date' => '2026-05-09 23:41']
];
?>
<div class="flex flex-col gap-8">
  
  <!-- Header -->
  <div>
    <h1 class="font-headline-lg text-headline-lg text-on-surface">Payment Analytics</h1>
    <p class="text-on-surface-variant font-body-md mt-1">Track dynamic customer billing logs, revenue streams, and transaction logs.</p>
  </div>

  <!-- Bento Stats metrics -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-1 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-[60px] pointer-events-none"></div>
      <span class="text-xs uppercase font-extrabold tracking-wider text-primary">Total Gross Revenue</span>
      <h2 class="font-headline-lg text-3xl font-extrabold text-on-surface mt-2">$1,294.50</h2>
      <p class="text-[10px] text-primary font-bold mt-1">+14.2% increase this month</p>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-1 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-[60px] pointer-events-none"></div>
      <span class="text-xs uppercase font-extrabold tracking-wider text-primary">Active Billing Subscriptions</span>
      <h2 class="font-headline-lg text-3xl font-extrabold text-on-surface mt-2">62 Customers</h2>
      <p class="text-[10px] text-on-surface-variant font-medium mt-1">Autopay enabled on 84%</p>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-2xl shadow-sm flex flex-col gap-1 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-[60px] pointer-events-none"></div>
      <span class="text-xs uppercase font-extrabold tracking-wider text-error">Failed Transactions Rate</span>
      <h2 class="font-headline-lg text-3xl font-extrabold text-on-surface mt-2">1.6%</h2>
      <p class="text-[10px] text-error font-bold mt-1">Auto-retry pipeline active</p>
    </div>
  </div>

  <!-- Transactions Table -->
  <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="font-headline-md text-lg text-on-surface font-extrabold flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">history</span> Transaction Logs
      </h3>
      <button onclick="alert('Exporting transactions to CSV...')" class="flex items-center gap-1.5 border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary px-4 py-2 rounded-xl text-xs font-semibold transition-all">
        <span class="material-symbols-outlined text-sm">download</span> Export CSV
      </button>
    </div>

    <div class="overflow-x-auto w-full">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-outline-variant/20 text-xs font-bold text-on-surface-variant uppercase bg-surface-container/50">
            <th class="py-4 px-4 rounded-l-xl">TXN ID</th>
            <th class="py-4 px-4">User</th>
            <th class="py-4 px-4">Active Plan</th>
            <th class="py-4 px-4">Amount</th>
            <th class="py-4 px-4">Status</th>
            <th class="py-4 px-4 rounded-r-xl">Date & Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/10 text-xs">
          <?php foreach ($payments as $p): ?>
            <?php
              $st = $p['status'];
              $st_cls = $st === 'Succeeded' ? 'bg-emerald-100 text-emerald-800' : 'bg-error-container text-on-error-container';
            ?>
            <tr class="hover:bg-surface-variant/10 transition-colors">
              <td class="py-4 px-4 font-mono font-bold text-primary"><?= $p['id'] ?></td>
              <td class="py-4 px-4">
                <p class="font-bold text-on-surface"><?= htmlspecialchars($p['name']) ?></p>
                <p class="text-[10px] text-on-surface-variant mt-0.5"><?= htmlspecialchars($p['email']) ?></p>
              </td>
              <td class="py-4 px-4 font-semibold text-on-surface-variant"><?= htmlspecialchars($p['plan']) ?></td>
              <td class="py-4 px-4 font-bold text-on-surface"><?= $p['amount'] ?></td>
              <td class="py-4 px-4">
                <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[9px] <?= $st_cls ?>">
                  <?= $st ?>
                </span>
              </td>
              <td class="py-4 px-4 text-on-surface-variant font-medium"><?= $p['date'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
