<!-- Users Tab -->
<!-- Header Section -->
<header class="flex justify-between items-end mb-10">
<div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">User Management</h2>
<p class="font-body-md text-on-surface-variant">Monitor, manage, and extend user trial experiences.</p>
        </div>
        <div class="flex gap-4">
          <button onclick="cleanupDuplicates()" class="px-4 py-2 bg-error-container text-on-error-container rounded-lg font-label-md text-xs font-bold hover:opacity-80 transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">clean_hands</span> Clean Duplicates
          </button>
          <div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
<input id="userSearch" class="pl-10 pr-4 py-2 bg-surface border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-body-md w-64 transition-all" placeholder="Search professionals..." type="text"/>
</div>
</div>
</header>
<!-- Stats Bento Grid -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-10">
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant flex items-center gap-6">
<div class="w-14 h-14 rounded-full bg-primary-fixed-dim/20 flex items-center justify-center text-primary">
<span class="material-symbols-outlined" style="font-size: 32px;">group</span>
</div>
<div>
<p class="font-label-sm text-on-surface-variant uppercase tracking-wider">Total Users</p>
<h3 class="font-headline-md text-headline-md text-on-surface" id="totalUsersStat">0</h3>
<p class="text-primary font-label-sm flex items-center gap-1 mt-1">
<span class="material-symbols-outlined" style="font-size: 16px;">trending_up</span>
                        +14% this month
                    </p>
</div>
</div>
</section>
<!-- User Management Table Container -->
<section class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden">
<div class="p-6 border-b border-surface-variant flex justify-between items-center">
<h3 class="font-headline-md text-headline-md text-on-surface">Professional Network</h3>
<div class="flex items-center gap-2">
<span class="text-label-sm text-on-surface-variant">Show:</span>
<select class="bg-transparent border-none font-label-md text-primary focus:ring-0 cursor-pointer">
<option>All Users</option>
<option>Active Trials</option>
<option>Expired</option>
</select>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" id="usersTable">
<thead>
<tr class="bg-secondary-container/30">
<th class="px-6 py-4 font-label-md text-on-secondary-container">Name</th>
<th class="px-6 py-4 font-label-md text-on-secondary-container">Trial Status</th>
<th class="px-6 py-4 font-label-md text-on-secondary-container">Usage</th>
<th class="px-6 py-4 font-label-md text-on-secondary-container">Joined</th>
<th class="px-6 py-4 font-label-md text-on-secondary-container text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-surface-variant" id="usersTableBody">
    <!-- Rows will be injected by JS -->
</tbody>
</table>
</div>
</div>
</section>

<!-- User Details Modal -->
<div id="userDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center opacity-0 transition-opacity">
    <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-xl transform scale-95 transition-transform p-6 relative">
        <button onclick="closeUserDetailsModal()" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        <h3 class="font-headline-md text-headline-md text-on-surface mb-6">User Details</h3>
        
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <div id="modalUserInitials" class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-white font-headline-md font-bold">
                    U
                </div>
                <div>
                    <h4 id="modalUserName" class="font-headline-sm text-headline-sm text-on-surface">User Name</h4>
                    <p id="modalUserEmail" class="font-body-md text-on-surface-variant">user@example.com</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="p-3 bg-surface-container rounded-xl">
                    <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[10px]">Status</p>
                    <p id="modalUserStatus" class="font-body-md text-on-surface font-semibold mt-1">Active</p>
                </div>
                <div class="p-3 bg-surface-container rounded-xl">
                    <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[10px]">Joined</p>
                    <p id="modalUserJoined" class="font-body-md text-on-surface font-semibold mt-1">Date</p>
                </div>
                <div class="p-3 bg-surface-container rounded-xl">
                    <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[10px]">Plan</p>
                    <p id="modalUserPlan" class="font-body-md text-on-surface font-semibold mt-1">Free</p>
                </div>
                <div class="p-3 bg-surface-container rounded-xl">
                    <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[10px]">Resumes Used</p>
                    <p id="modalUserUsage" class="font-body-md text-on-surface font-semibold mt-1">0</p>
                </div>
                <div class="col-span-2 p-3 bg-surface-container rounded-xl">
                    <p class="font-label-sm text-on-surface-variant uppercase tracking-wider text-[10px]">Location</p>
                    <p id="modalUserLocation" class="font-body-md text-on-surface font-semibold mt-1">Unknown</p>
                </div>
            </div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button onclick="closeUserDetailsModal()" class="px-4 py-2 border border-outline-variant rounded-xl font-label-md text-on-surface hover:bg-surface-variant transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Feedbacks Section -->
<section class="mt-8 bg-surface-container-lowest rounded-xl shadow-sm border border-surface-variant overflow-hidden">
<div class="p-6 border-b border-surface-variant">
    <h3 class="font-headline-md text-headline-md text-on-surface">User Feedbacks</h3>
</div>
<div class="p-4 max-h-80 overflow-y-auto" id="feedbacksList">
    <p class="text-on-surface-variant text-xs font-medium text-center py-8">Loading feedbacks...</p>
</div>
</section>

<script>
function loadFeedbacks() {
    fetch('api/feedback.php?action=list')
        .then(r => r.json())
        .then(data => {
            const el = document.getElementById('feedbacksList');
            if (!data.success || !data.data.length) {
                el.innerHTML = '<p class="text-on-surface-variant text-xs font-medium text-center py-8">No feedbacks yet.</p>';
                return;
            }
            el.innerHTML = data.data.map(f => `
                <div class="p-3 bg-surface-container rounded-xl mb-2 flex justify-between items-start">
                    <div>
                        <p class="text-[10px] text-on-surface-variant font-medium">${f.email || 'Unknown'} &middot; ${new Date(f.created_at).toLocaleDateString()}</p>
                        <p class="text-xs text-on-surface font-semibold mt-1">${f.message}</p>
                    </div>
                    <button onclick="deleteFeedback(${f.id})" class="text-error hover:opacity-70 text-xs font-bold shrink-0 ml-2">Delete</button>
                </div>
            `).join('');
        });
}
function deleteFeedback(id) {
    if (!confirm('Delete this feedback?')) return;
    fetch('api/feedback.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete&id=' + id
    }).then(r => r.json()).then(d => { if (d.success) loadFeedbacks(); });
}
document.addEventListener("DOMContentLoaded", loadFeedbacks);
</script>

</div>
