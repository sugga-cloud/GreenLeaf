<?php include __DIR__ . '/../components/common/head.php'; ?>
<title>GreenLeaf Resume - Student Registration</title>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen">
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>
<main class="max-w-4xl mx-auto px-margin-mobile md:px-margin-desktop py-12 flex flex-col items-center">
<header class="text-center mb-12">
<div class="flex items-center justify-center gap-2 mb-4">
<span class="material-symbols-outlined text-primary text-4xl" data-icon="eco" style="font-variation-settings: 'FILL' 1;">eco</span>
<h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">GreenLeaf Resume</h1>
</div>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-md">Let's grow your career. Tell us about your journey to build your professional profile.</p>
</header>

<?php include __DIR__ . '/../components/student_registration/progress.php'; ?>

<?php include __DIR__ . '/../components/student_registration/form.php'; ?>

<!-- Aesthetic Card Row (Contextual Details) -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 w-full">
<div class="p-6 bg-surface-container-low rounded-xl border border-surface-variant/30 flex flex-col gap-3">
<span class="material-symbols-outlined text-primary text-2xl" data-icon="verified_user">verified_user</span>
<h3 class="font-label-md text-label-md text-on-surface">Data Privacy</h3>
<p class="font-label-sm text-label-sm text-on-surface-variant">Your data is encrypted and only shared with verified employers you apply to.</p>
</div>
<div class="p-6 bg-surface-container-low rounded-xl border border-surface-variant/30 flex flex-col gap-3">
<span class="material-symbols-outlined text-primary text-2xl" data-icon="auto_awesome">auto_awesome</span>
<h3 class="font-label-md text-label-md text-on-surface">Smart Suggestions</h3>
<p class="font-label-sm text-label-sm text-on-surface-variant">We'll suggest formatting based on top industry standard resume layouts.</p>
</div>
<div class="p-6 bg-surface-container-low rounded-xl border border-surface-variant/30 flex flex-col gap-3">
<span class="material-symbols-outlined text-primary text-2xl" data-icon="sync">sync</span>
<h3 class="font-label-md text-label-md text-on-surface">Auto-Save</h3>
<p class="font-label-sm text-label-sm text-on-surface-variant">Progress is saved automatically so you can continue your setup later.</p>
</div>
</section>

<?php include __DIR__ . '/../components/common/footer.php'; ?>

</main>
<!-- Side Decoration (Visual Anchor) -->
<div class="fixed left-0 top-0 h-full w-1.5 bg-gradient-to-b from-primary via-secondary to-primary/20 hidden lg:block"></div>
</body></html>
