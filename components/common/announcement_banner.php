<?php
if (!isset($db)) {
    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
}
$banner_enabled = $db->query("SELECT value FROM settings WHERE key = 'banner_enabled'")->fetchColumn();
if (!$banner_enabled) return;
$banner_text = $db->query("SELECT value FROM settings WHERE key = 'banner_text'")->fetchColumn();
$banner_color = $db->query("SELECT value FROM settings WHERE key = 'banner_color'")->fetchColumn() ?: '#006c49';
$banner_link = $db->query("SELECT value FROM settings WHERE key = 'banner_link'")->fetchColumn();
if (empty($banner_text)) return;
?>
<div id="announcement-banner" class="hidden w-full py-2.5 px-4 text-center relative text-white text-sm font-medium" style="background-color: <?= htmlspecialchars($banner_color) ?>;">
    <?php if ($banner_link): ?>
        <a href="<?= htmlspecialchars($banner_link) ?>" class="hover:underline"><?= htmlspecialchars($banner_text) ?></a>
    <?php else: ?>
        <span><?= htmlspecialchars($banner_text) ?></span>
    <?php endif; ?>
    <button onclick="dismissBanner()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white transition-colors" aria-label="Close banner">
        <span class="material-symbols-outlined" style="font-size:18px;">close</span>
    </button>
</div>
<script>
(function() {
    var bannerHash = '<?= md5($banner_text . $banner_color) ?>';
    var closed = localStorage.getItem('gl_banner_closed');
    if (closed !== bannerHash) {
        var el = document.getElementById('announcement-banner');
        if (el) el.classList.remove('hidden');
    }
})();
function dismissBanner() {
    var el = document.getElementById('announcement-banner');
    if (el) el.classList.add('hidden');
    localStorage.setItem('gl_banner_closed', '<?= md5($banner_text . $banner_color) ?>');
}
</script>
