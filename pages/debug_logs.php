<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';
require_once __DIR__ . '/../services/Logger.php';

$action = $_GET['action'] ?? '';
if ($action === 'clear' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    clear_logs();
    echo '<script>window.location.href = "?page=debug_logs&cleared=1";</script>';
    exit;
}

$auto = isset($_GET['auto']) ? max(1, min(30, (int)$_GET['auto'])) : 0;
$logs = read_recent_logs(300);

include __DIR__ . '/../components/common/head.php';
?>
<title>AI Debug Logs — GreenLeaf</title>
<style>
  .log-line {
    font-family: 'JetBrains Mono', 'Consolas', 'Monaco', monospace;
    font-size: 11px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-all;
  }
  .log-INFO  { color: #065f46; }
  .log-WARN  { color: #92400e; background: #fffbeb; }
  .log-ERROR { color: #991b1b; background: #fef2f2; }
  .log-DEBUG { color: #1e40af; background: #eff6ff; }
  .log-ts    { color: #6b7280; }
  .log-tag   { color: #7c3aed; font-weight: bold; }
</style>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen">

<main class="min-h-screen pb-24 max-w-6xl mx-auto px-4 sm:px-6 py-6">
  <div class="flex items-center gap-2 mb-4 text-sm text-on-surface-variant">
    <a href="?page=user_dashboard" class="hover:text-primary transition-colors flex items-center gap-1">
      <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back
    </a>
  </div>

  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
    <div>
      <h1 class="font-headline-md text-2xl text-on-surface font-extrabold flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">terminal</span>
        AI Generation Logs
      </h1>
      <p class="text-on-surface-variant text-xs mt-1">
        Live tail of <code class="bg-surface-container px-1.5 py-0.5 rounded">logs/ai.log</code> — every AI call, prompt size, raw response, parse result, and DB update.
      </p>
    </div>
    <div class="flex gap-2">
      <select id="autoRefresh" onchange="changeAuto(this.value)" class="text-xs border border-outline-variant/40 rounded-lg px-2 py-1.5 bg-surface">
        <option value="0">No auto-refresh</option>
        <option value="2">Every 2s</option>
        <option value="5">Every 5s</option>
        <option value="10">Every 10s</option>
      </select>
      <button onclick="window.location.reload()" class="text-xs border border-outline-variant/40 rounded-lg px-3 py-1.5 hover:bg-surface-variant">Refresh</button>
      <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <button onclick="if(confirm('Clear all logs?')) window.location.href='?page=debug_logs&action=clear'" class="text-xs bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-100">Clear</button>
      <?php endif; ?>
    </div>
  </div>

  <?php if (isset($_GET['cleared'])): ?>
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">Logs cleared.</div>
  <?php endif; ?>

  <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/30 shadow-sm overflow-hidden">
    <div class="px-4 py-2.5 bg-surface-container border-b border-outline-variant/20 flex justify-between text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">
      <span><?= count($logs) ?> log lines (newest first)</span>
      <span class="text-on-surface-variant/60 font-mono text-[10px]"><?= htmlspecialchars(date('Y-m-d H:i:s')) ?></span>
    </div>
    <div id="logBox" class="p-4 max-h-[70vh] overflow-y-auto bg-white">
      <?php if (empty($logs)): ?>
        <p class="text-on-surface-variant text-sm italic py-8 text-center">No logs yet. Trigger a resume generation to see entries.</p>
      <?php else: ?>
        <?php foreach ($logs as $line):
          $cls = 'log-INFO';
          if (strpos($line, '[ERROR]') !== false) $cls = 'log-ERROR';
          elseif (strpos($line, '[WARN]') !== false) $cls = 'log-WARN';
          elseif (strpos($line, '[DEBUG]') !== false) $cls = 'log-DEBUG';
        ?>
        <div class="log-line <?= $cls ?> py-0.5 px-1 rounded">
          <?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
            $formatted = preg_replace_callback(
              '/\[([\d\-: ]+)\] \[(\w+)\] \[([^\]]+)\] (.*)/',
              function($m) {
                return '<span class="log-ts">[' . htmlspecialchars($m[1]) . ']</span> '
                     . '<span>[' . htmlspecialchars($m[2]) . ']</span> '
                     . '<span class="log-tag">[' . htmlspecialchars($m[3]) . ']</span> '
                     . '<span>' . htmlspecialchars($m[4]) . '</span>';
              },
              htmlspecialchars($line, ENT_QUOTES)
            );
            echo $formatted;
          ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30">
      <h3 class="font-bold text-on-surface text-sm mb-2 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[18px]">info</span>
        What gets logged
      </h3>
      <ul class="text-xs text-on-surface-variant space-y-1.5">
        <li>• Resume creation, status transitions, validation checks</li>
        <li>• Profile data summary (counts, name, email)</li>
        <li>• AI call: model, prompt sizes, raw response</li>
        <li>• JSON parse result, field counts, success/fail decisions</li>
        <li>• All errors with full stack trace</li>
        <li>• Credit refund events</li>
      </ul>
    </div>
    <div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant/30">
      <h3 class="font-bold text-on-surface text-sm mb-2 flex items-center gap-2">
        <span class="material-symbols-outlined text-amber-600 text-[18px]">build</span>
        Common failure causes
      </h3>
      <ul class="text-xs text-on-surface-variant space-y-1.5">
        <li>• <strong>Empty profile:</strong> AI returns empty JSON → fix by adding personal/exp/skills</li>
        <li>• <strong>No credits:</strong> 0 credits → upgrade plan</li>
        <li>• <strong>SSL error:</strong> cURL can't reach API → check internet</li>
        <li>• <strong>API key invalid:</strong> check <code>.env</code></li>
        <li>• <strong>Model overloaded:</strong> retry button available</li>
      </ul>
    </div>
  </div>
</main>

<script>
function changeAuto(v) {
  if (window._autoTimer) clearInterval(window._autoTimer);
  if (v > 0) {
    window._autoTimer = setInterval(() => window.location.reload(), v * 1000);
  }
}
<?php if ($auto > 0): ?>
changeAuto(<?= $auto ?>);
<?php endif; ?>
</script>
</body>
</html>
