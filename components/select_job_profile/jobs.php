<!-- Profile Grid (Bento Style Layout) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-16" id="profileGrid">
<!-- Profiles will be handled via JS for selection -->
<button type="button" onclick="selectProfile('Python Developer', this)" class="profile-card group text-left p-8 bg-surface-container-lowest border border-outline-variant asymmetric-leaf hover:border-primary hover:shadow-xl hover:shadow-primary/5 transition-all active:scale-[0.98] flex flex-col gap-6">
<div class="w-14 h-14 rounded-xl bg-secondary-container flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined text-3xl">terminal</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Python Developer</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Backend logic, data pipelines, and automation expertise.</p>
</div>
</button>

<button type="button" onclick="selectProfile('Full Stack Developer', this)" class="profile-card group text-left p-8 bg-surface-container-lowest border-2 border-primary asymmetric-leaf shadow-xl shadow-primary/5 transition-all active:scale-[0.98] flex flex-col gap-6 relative selected-profile">
<div class="absolute -top-3 -right-3 bg-primary text-on-primary px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase">Most Popular</div>
<div class="w-14 h-14 rounded-xl bg-primary flex items-center justify-center text-on-primary">
<span class="material-symbols-outlined text-3xl">layers</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Full Stack Developer</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Bridging the gap between frontend beauty and backend power.</p>
</div>
</button>

<button type="button" onclick="selectProfile('Data Scientist', this)" class="profile-card group text-left p-8 bg-surface-container-lowest border border-outline-variant asymmetric-leaf hover:border-primary hover:shadow-xl hover:shadow-primary/5 transition-all active:scale-[0.98] flex flex-col gap-6">
<div class="w-14 h-14 rounded-xl bg-secondary-container flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined text-3xl">monitoring</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Data Scientist</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Statistical modeling, predictive insights, and storytelling with data.</p>
</div>
</button>

<button type="button" onclick="selectProfile('Project Manager', this)" class="profile-card group text-left p-8 bg-surface-container-lowest border border-outline-variant asymmetric-leaf hover:border-primary hover:shadow-xl hover:shadow-primary/5 transition-all active:scale-[0.98] flex flex-col gap-6">
<div class="w-14 h-14 rounded-xl bg-secondary-container flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-on-primary transition-colors">
<span class="material-symbols-outlined text-3xl">assignment_ind</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Project Manager</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Strategic planning, team leadership, and delivery excellence.</p>
</div>
</button>
</div>

<!-- Secondary Grid (More Options) -->
<div class="flex justify-center gap-4 mb-16">
    <button class="px-6 py-3 border border-primary text-primary font-label-md rounded-lg flex items-center gap-2 hover:bg-primary/5 transition-colors">
        <span class="material-symbols-outlined">add</span> Add Custom Profile
    </button>
    <button class="px-6 py-3 border border-outline-variant text-on-surface-variant font-label-md rounded-lg flex items-center gap-2 hover:bg-surface-variant transition-colors" onclick="alert('Manage Profiles')">
        <span class="material-symbols-outlined">settings</span> Manage Profiles
    </button>
</div>

<!-- CTA Action Bar -->
<div class="mt-10 flex flex-col items-center gap-6">
<div class="flex items-center gap-3 p-4 bg-tertiary-fixed rounded-xl text-on-tertiary-fixed-variant">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
<span class="font-label-md text-label-md">Ready to generate a resume tailored for <strong id="selectedProfileName">Full Stack Developer</strong> positions.</span>
</div>
<button onclick="openTemplateModal()" class="group flex items-center gap-4 bg-primary text-on-primary px-12 py-5 rounded-full font-headline-md text-headline-md shadow-lg shadow-primary/20 hover:shadow-xl hover:opacity-90 active:scale-95 transition-all">
                Generate My Tailored Resume
                <span class="material-symbols-outlined text-2xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
</button>
</div>

<!-- Template Selection Modal -->
<div id="templateModal" class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-surface-container-lowest w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-surface-variant flex justify-between items-center">
            <h2 class="font-headline-lg text-on-surface">Select a Resume Template</h2>
            <button onclick="closeTemplateModal()" class="p-2 hover:bg-surface-variant rounded-full transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-surface grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Template Options -->
            <button onclick="selectTemplateAndGenerate('Minimalist')" class="group text-left p-4 bg-surface-container-lowest border border-outline-variant rounded-xl hover:border-primary hover:shadow-lg transition-all focus:ring-2 focus:ring-primary outline-none">
                <div class="aspect-[1/1.4] bg-surface-variant rounded-lg mb-4 flex items-center justify-center relative overflow-hidden">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBZA7r6UjZ0X_2M3uoO5v6fRfsgO3993aGR8wE6uIWikqbD3si8T8o6Tl_wXPEAAoz4HPSm6H98ClUtPLPEvWaUNKE4K4_1BHzfJf645Ub8siv0woX0wjvjy4vznle-xAS7n334tvmeS8fd_QERiniE1hgtG4MDVL_UEXamRrAxLr9BpksmuW3-fZE-a33wnL4TV5oe5YQdK_zOAxYPKuMCMnUdE5iFG0M0UglxcGvLeKqRmBZQYBGsbCduXQVs6a5rpYc5Brq_1to" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                </div>
                <h4 class="font-label-md text-on-surface">Minimalist</h4>
            </button>
            <button onclick="selectTemplateAndGenerate('Modern Tech')" class="group text-left p-4 bg-surface-container-lowest border border-outline-variant rounded-xl hover:border-primary hover:shadow-lg transition-all focus:ring-2 focus:ring-primary outline-none">
                <div class="aspect-[1/1.4] bg-surface-variant rounded-lg mb-4 flex items-center justify-center relative overflow-hidden">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJhse6ro0YjBWWSXqy5pZ6Dc0UbgnRV5MIMpMLY3FJjjiCadbN7VQb4RaUmX4Pu9Pk_hlgCv1XdY5atwNpusynXoEd6iaNOww-wsNosMDZBooK90Tb6-aRCI9vFgcqUT_S7Xse0JHdP_NDUuKWgqPVhw0Jt_35vQdM0rLA-GY2BraHFKU9drMN7HrcpDn4HMUt7ATfH6hJVOEyP4Za6_07qgRFDjgGVhfDIvDGHX-fC3mRlwefMDn0SzldXyP4hSHK59HF848E9gA" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                </div>
                <h4 class="font-label-md text-on-surface">Modern Tech</h4>
            </button>
            <button onclick="selectTemplateAndGenerate('Executive')" class="group text-left p-4 bg-surface-container-lowest border border-outline-variant rounded-xl hover:border-primary hover:shadow-lg transition-all focus:ring-2 focus:ring-primary outline-none">
                <div class="aspect-[1/1.4] bg-surface-variant rounded-lg mb-4 flex items-center justify-center relative overflow-hidden">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5XlefmKgq0tgllhUxEpsF3dhjNULfCyRXkfmycy098xnbFPGk4d2WMX08v51WcznZlP1oOePpc1svDczUk5xqjR4BPQiKNAPHP0m7c58UtUDQqzRT0caC_2HhDHHxNIEN8Ap1PJBLXA4DBF1CjdmyrQZNN9QQys6a1yePl3CCL18FIRUp7dzDA45i4K0xvCgzkynJ1JrKjJ8SJQ8024BBH8RBoCjSyHBk7bVASzGsgpfW0C5wN-BBN2lD0EWB9v7sVnnY71zmDrc" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                </div>
                <h4 class="font-label-md text-on-surface">Executive</h4>
            </button>
        </div>
        <div class="p-6 border-t border-surface-variant bg-surface-container-lowest flex justify-end">
            <button onclick="window.location.href='?page=templates'" class="font-label-md text-primary flex items-center gap-2 hover:underline">
                Get more templates
                <span class="material-symbols-outlined text-sm">open_in_new</span>
            </button>
        </div>
    </div>
</div>

<script>
let currentSelectedProfile = 'Full Stack Developer';

function selectProfile(profileName, element) {
    currentSelectedProfile = profileName;
    document.getElementById('selectedProfileName').innerText = profileName;
    
    // Remove styling from all
    const cards = document.querySelectorAll('.profile-card');
    cards.forEach(card => {
        card.classList.remove('border-2', 'border-primary');
        card.classList.add('border', 'border-outline-variant');
    });

    // Add styling to selected
    element.classList.remove('border', 'border-outline-variant');
    element.classList.add('border-2', 'border-primary');
}

function openTemplateModal() {
    document.getElementById('templateModal').classList.remove('hidden');
    document.getElementById('templateModal').classList.add('flex');
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
    document.getElementById('templateModal').classList.remove('flex');
}

function selectTemplateAndGenerate(templateName) {
    const formData = new FormData();
    formData.append('profile', currentSelectedProfile);
    formData.append('template', templateName);

    fetch('api/generate_resume.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeTemplateModal();
            // Open in new tab
            window.open('?page=preview_resume&id=' + data.id, '_blank');
        } else {
            alert('Failed to generate resume');
        }
    });
}
</script>
