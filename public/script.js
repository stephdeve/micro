document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Script chargé');
    
    // Dropdown profil
    const profileBtn = document.getElementById('profileBtn');
    const dropdown = document.getElementById('profileDropdown');
    
    if (profileBtn && dropdown) {
        console.log('✅ Éléments dropdown trouvés');
        
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            dropdown.classList.remove('show');
        });

        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    } else {
        console.log('❌ Éléments dropdown non trouvés');
    }

    // Confirmation déconnexion sidebar
    const logoutFormSidebar = document.getElementById('logout-form-sidebar');
    if (logoutFormSidebar) {
        logoutFormSidebar.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                e.preventDefault();
            }
        });
    }

    // Confirmation déconnexion dropdown
    const logoutFormDropdown = document.getElementById('logout-form-dropdown');
    if (logoutFormDropdown) {
        logoutFormDropdown.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                e.preventDefault();
            }
        });
    }

    // Notifications auto actualisées
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationsList = document.getElementById('notificationsList');
    const markReadAllButton = document.getElementById('markReadAll');

    async function refreshNotifications() {
        try {
            const res = await fetch('/notifications/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const count = data.unread_count || 0;

            if (notificationBadge) {
                if (count > 0) {
                    notificationBadge.style.display = 'block';
                    notificationBadge.textContent = count;
                } else {
                    notificationBadge.style.display = 'none';
                }
            }

            // Actualiser liste si menu ouvert
            if (notificationDropdown && notificationDropdown.style.display === 'block') {
                await loadNotificationList();
            }
        } catch (error) {
            console.error('Erreur notifications:', error);
        }
    }

    async function loadNotificationList() {
        try {
            const res = await fetch('/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!notificationsList) return;

            notificationsList.innerHTML = '';
            if (!data.notifications || data.notifications.length === 0) {
                notificationsList.innerHTML = '<div style="padding: 1rem; color:#8ba9d0;">Aucune notification</div>';
                return;
            }

            data.notifications.forEach(note => {
                const item = document.createElement('div');
                item.style = 'padding: .7rem 1rem; border-bottom: 1px solid #2a3f5a; cursor: pointer; display:flex; justify-content:space-between; align-items:center;';

                let extra = '';
                if (note.data.entity_type === 'routeur' && note.data.entity_id) {
                    extra = `<div style="font-size:0.78rem; color:#88b2e0; margin-top:0.25rem;">Routeur N°${note.data.entity_id}</div>`;
                } else if (note.data.entity_type === 'message' && note.data.entity_id) {
                    extra = `<div style="font-size:0.78rem; color:#88b2e0; margin-top:0.25rem;">Message N°${note.data.entity_id}</div>`;
                }

                item.innerHTML = `
                    <div>
                        <strong style="color:#dbe9ff;">${note.data.title}</strong>
                        <div style="color:#a2bbd9; font-size:0.85rem;">${note.data.message}</div>
                        ${extra}
                    </div>
                    <button class="btn-icon" style="background:transparent; color:#7fb0ff; border:none; font-size:1.05rem;" data-id="${note.id}" title="Marquer lu">
                        <i class="fas fa-check"></i>
                    </button>
                `;
                item.querySelector('button').addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const id = e.target.getAttribute('data-id');
                    await fetch(`/notifications/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest' } });
                    await refreshNotifications();
                });

                item.addEventListener('click', () => {
                    if (note.data.url && note.data.url !== '#') {
                        window.location.href = note.data.url;
                    }
                });

                notificationsList.appendChild(item);
            });
        } catch (error) {
            console.error('Erreur chargement notifications:', error);
        }
    }

    if (notificationBell) {
        notificationBell.addEventListener('click', async () => {
            if (!notificationDropdown) return;
            notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
            if (notificationDropdown.style.display === 'block') {
                await loadNotificationList();
                await refreshNotifications();
            }
        });
    }

    if (markReadAllButton) {
        markReadAllButton.addEventListener('click', async () => {
            await fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest' } });
            await refreshNotifications();
            if (notificationsList) {
                notificationsList.innerHTML = '<div style="padding: 1rem; color:#8ba9d0;">Aucune notification</div>';
            }
        });
    }

    const deleteReadButton = document.getElementById('deleteRead');
    if (deleteReadButton) {
        deleteReadButton.addEventListener('click', async () => {
            await fetch('/notifications/delete-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest' } });
            await refreshNotifications();
            if (notificationsList) {
                notificationsList.innerHTML = '<div style="padding: 1rem; color:#8ba9d0;">Aucune notification</div>';
            }
        });
    }

    let lastUnreadCount = 0;

    window.refreshNotifications = refreshNotifications;

    document.addEventListener('notifications:refresh', refreshNotifications);

    async function checkNewNotifications() {
        try {
            const res = await fetch('/notifications/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const count = data.unread_count || 0;
            if (count > lastUnreadCount) {
                const diff = count - lastUnreadCount;
                lastUnreadCount = count;
                const toast = document.createElement('div');
                toast.textContent = `🔔 ${diff} nouvelle(s) notification(s)`;
                toast.style = 'position:fixed; bottom:20px; right:20px; background:#1f324e; color:#fff; padding:10px 14px; border-radius:8px; box-shadow:0 8px 20px rgba(0,0,0,.4); z-index:2000;';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2800);
            } else {
                lastUnreadCount = count;
            }
        } catch (err) {
            console.error('Erreur checkNewNotifications:', err);
        }
    }

    setInterval(async () => {
        await refreshNotifications();
        await checkNewNotifications();
    }, 3000); // toutes les 3 secondes

    refreshNotifications();
});