function loadUsers() {
    fetch('api/get_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('usersTableBody');
                const totalStats = document.getElementById('totalUsersStat');
                tbody.innerHTML = '';
                
                if (totalStats) {
                    totalStats.textContent = data.data.length;
                }

                window.loadedUsers = data.data;

                data.data.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-surface-container-low transition-colors';
                    tr.innerHTML = `
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                                    ${user.first_name ? user.first_name[0] : 'U'}
                                </div>
                                <div>
                                    <p class="font-label-md text-on-surface">${user.first_name} ${user.last_name}</p>
                                    <p class="text-label-sm text-on-surface-variant">${user.email}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 bg-primary-container/20 text-on-primary-container rounded-full font-label-sm inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                ${user.trial_status}
                            </span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col gap-1">
                                <span class="font-label-md text-on-surface">${user.resume_count} Resumes</span>
                                <div class="w-24 bg-surface-variant h-1 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full" style="width: ${Math.min(user.resume_count * 10, 100)}%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <p class="font-label-md text-on-surface">${new Date(user.created_at).toLocaleDateString()}</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="showUserDetailsModal(${user.id})" class="px-3 py-1.5 text-primary font-label-sm hover:bg-primary/5 rounded-lg transition-all">Details</button>
                                ${user.trial_status === 'Revoked' 
                                    ? `<button onclick="restoreUserAccess(${user.id})" class="px-3 py-1.5 bg-tertiary-container text-on-tertiary-container font-label-sm rounded-lg hover:opacity-80 transition-all">Restore Access</button>`
                                    : `<button onclick="revokeUserAccess(${user.id})" class="px-3 py-1.5 bg-error-container text-on-error-container font-label-sm rounded-lg hover:opacity-80 transition-all">Revoke Access</button>`
                                }
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error("Error loading users", error));
}

function showUserDetailsModal(userId) {
    if (!window.loadedUsers) return;
    const user = window.loadedUsers.find(u => u.id == userId);
    if (!user) return;
    
    document.getElementById('modalUserInitials').textContent = user.first_name ? user.first_name[0] : 'U';
    document.getElementById('modalUserName').textContent = (user.first_name || '') + ' ' + (user.last_name || '');
    document.getElementById('modalUserEmail').textContent = user.email || '';
    document.getElementById('modalUserStatus').textContent = user.trial_status || 'Unknown';
    document.getElementById('modalUserJoined').textContent = user.created_at ? new Date(user.created_at).toLocaleDateString() : 'Unknown';
    document.getElementById('modalUserPlan').textContent = user.current_plan || 'Free';
    document.getElementById('modalUserUsage').textContent = (user.resume_count || 0) + ' Resumes';
    document.getElementById('modalUserLocation').textContent = user.location || 'Unknown';
    
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 10);
    }
}

function closeUserDetailsModal() {
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function revokeUserAccess(userId) {
    if (!confirm("Are you sure you want to revoke this user's access? They will no longer be able to log in.")) return;

    fetch('api/revoke_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('User access revoked successfully.');
            loadUsers(); // refresh the table
        } else {
            alert('Failed to revoke access: ' + data.error);
        }
    })
    .catch(err => {
        console.error('Error revoking user', err);
        alert('An error occurred.');
    });
}

function restoreUserAccess(userId) {
    if (!confirm("Are you sure you want to restore this user's access?")) return;

    fetch('api/restore_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('User access restored successfully.');
            loadUsers(); // refresh the table
        } else {
            alert('Failed to restore access: ' + data.error);
        }
    })
    .catch(err => {
function cleanupDuplicates() {
    if (!confirm("Remove duplicate users with 0 resumes? This cannot be undone.")) return;

    fetch('api/cleanup_duplicates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=cleanup_duplicates'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(`Cleaned up ${data.deleted} duplicate(s).`);
            if (typeof loadUsers === 'function') loadUsers();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => { console.error(e); alert('Error cleaning up duplicates.'); });
}

function submitFeedback() {
    const msg = document.getElementById('feedbackText')?.value;
    if (!msg || msg.trim().length < 3) { alert('Please enter your feedback.'); return; }

    fetch('api/feedback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=submit&message=' + encodeURIComponent(msg.trim())
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('feedback_submitted', '1');
            document.getElementById('feedbackPopup')?.classList.add('hidden');
            alert('Thank you for your feedback!');
        } else {
            alert('Error: ' + (data.error || 'Failed to submit'));
        }
    })
    .catch(e => console.error(e));
}

function closeFeedback() {
    localStorage.setItem('feedback_submitted', '1');
    document.getElementById('feedbackPopup')?.classList.add('hidden');
}
