<!-- Form Canvas -->
<section class="w-full bg-surface-container-lowest p-8 md:p-12 shadow-sm rounded-xl border border-surface-variant/50 relative overflow-hidden">
<!-- Decorative Background Leaf Trace -->
<div class="absolute -top-12 -right-12 w-48 h-48 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
<form action="api/register.php" method="POST" id="registrationForm" class="flex flex-col md:flex-row gap-gutter" onsubmit="event.preventDefault(); submitRegistration();">
<!-- Instruction Panel -->
<div class="md:w-1/3 flex flex-col gap-4">
<h2 class="font-headline-md text-headline-md text-on-surface">Personal Information</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Your foundation starts here. Providing your basic contact details ensures employers can reach out to you seamlessly.</p>
<div class="mt-4 p-4 bg-secondary-container/30 rounded-lg border border-secondary-container asymmetric-leaf">
<div class="flex items-center gap-2 text-primary font-label-md">
<span class="material-symbols-outlined text-sm" data-icon="tips_and_updates">tips_and_updates</span>
<span>Pro Tip</span>
</div>
<p class="font-label-sm text-label-sm text-on-secondary-container mt-1">Use a professional email address for better response rates from recruiters.</p>
</div>
</div>
<!-- Form Fields -->
<div class="md:w-2/3 space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="flex flex-col gap-1.5">
<label class="font-label-md text-label-md text-on-surface-variant" for="first_name">First Name</label>
<input name="first_name" required class="w-full p-3 bg-surface rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline" id="first_name" placeholder="Enter first name" type="text"/>
</div>
<div class="flex flex-col gap-1.5">
<label class="font-label-md text-label-md text-on-surface-variant" for="last_name">Last Name</label>
<input name="last_name" required class="w-full p-3 bg-surface rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline" id="last_name" placeholder="Enter last name" type="text"/>
</div>
</div>
<div class="flex flex-col gap-1.5">
<label class="font-label-md text-label-md text-on-surface-variant" for="email">Email Address</label>
<input name="email" required class="w-full p-3 bg-surface rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline" id="email" placeholder="you@university.edu" type="email"/>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="flex flex-col gap-1.5">
<label class="font-label-md text-label-md text-on-surface-variant" for="phone">Phone Number</label>
<input name="phone" class="w-full p-3 bg-surface rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline" id="phone" placeholder="+1 (555) 000-0000" type="tel"/>
</div>
<div class="flex flex-col gap-1.5">
<label class="font-label-md text-label-md text-on-surface-variant" for="location">Location</label>
<input name="location" class="w-full p-3 bg-surface rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-outline" id="location" placeholder="City, Country" type="text"/>
</div>
</div>
<div class="pt-8 flex flex-col-reverse md:flex-row justify-between items-center gap-4">
<button type="button" onclick="window.location.href='?page=select_job_profile';" class="w-full md:w-auto px-8 py-3 rounded-lg font-label-md text-label-md text-on-surface-variant bg-surface-container-high hover:bg-surface-variant active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            Skip for Now
                        </button>
<button type="submit" class="w-full md:w-auto px-10 py-3 rounded-lg font-label-md text-label-md text-white bg-primary hover:opacity-90 active:scale-[0.95] transition-all flex items-center justify-center gap-2 shadow-sm">
                            Next: Education
                            <span class="material-symbols-outlined text-sm" data-icon="arrow_forward">arrow_forward</span>
</button>
</div>
</div>
</form>
</section>

<script>
function submitRegistration() {
    const formData = new FormData(document.getElementById('registrationForm'));
    fetch('api/register.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              window.location.href = '?page=select_job_profile';
          } else {
              alert('Error saving data');
          }
      });
}
</script>
