<?php 
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
include __DIR__ . '/../components/common/head.php'; 
?>
<title>GreenLeaf Resume Admin Dashboard</title>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<?php include __DIR__ . '/../components/admin_dashboard/sidebar.php'; ?>
<!-- Main Content Area -->
<main class="ml-64 p-margin-desktop min-h-screen">

<?php
if ($tab === 'payments') {
    include __DIR__ . '/../components/admin_dashboard/payments.php';
} elseif ($tab === 'plans') {
    include __DIR__ . '/../components/admin_dashboard/plans.php';
} elseif ($tab === 'templates') {
    include __DIR__ . '/../components/admin_dashboard/templates.php';
} elseif ($tab === 'tickets') {
    include __DIR__ . '/../components/admin_dashboard/tickets.php';
} elseif ($tab === 'notifications') {
    include __DIR__ . '/../components/admin_dashboard/notifications.php';
} elseif ($tab === 'settings') {
    include __DIR__ . '/../components/admin_dashboard/settings.php';
} else {
    include __DIR__ . '/../components/admin_dashboard/users.php';
}
?>

</main>
<!-- Footer Shell -->
<footer class="ml-64 bg-surface-container-lowest border-t border-outline-variant py-8 px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-6">
<div class="flex flex-col items-center md:items-start">
<span class="font-headline-md text-headline-md font-bold text-primary">GreenLeaf Resume</span>
<p class="font-body-md text-on-surface-variant mt-2">© 2026 GreenLeaf Resume. Growth through Structure. Built by Wribix.</p>
</div>
<div class="flex gap-8">
<a class="font-label-sm text-on-surface-variant hover:text-primary hover:underline transition-all" href="#">Privacy Policy</a>
<a class="font-label-sm text-on-surface-variant hover:text-primary hover:underline transition-all" href="#">Terms of Service</a>
<a class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary hover:underline transition-all" href="#">Support</a>
</div>
</footer>

<script src="/public/js/app.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if (typeof loadUsers === 'function') {
            loadUsers();
        }
    });
</script>
</body></html>
