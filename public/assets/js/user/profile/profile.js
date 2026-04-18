document.addEventListener('DOMContentLoaded', function() {
    const communityActionUrl = '/user/community';
    const followBtn = document.getElementById('followBtn');
    const messageBtn = document.getElementById('messageBtn');
    const moreBtn = document.getElementById('moreBtn');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfileModal = document.getElementById('editProfileModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const editProfileForm = document.getElementById('editProfileForm');

    if (followBtn) {
        followBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const isFollowing = this.classList.contains('following');
            
            const formData = new FormData();
            formData.append('user_id', userId);
            const action = isFollowing ? 'unfollow' : 'follow';
            
            fetch(`${communityActionUrl}?ajax=${action}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isFollowing) {
                        this.classList.remove('following');
                        this.innerHTML = '<i data-lucide="user-plus" stroke-width="2"></i> Follow';
                    } else {
                        this.classList.add('following');
                        this.innerHTML = '<i data-lucide="user-check" stroke-width="2"></i> Following';
                    }
                    lucide.createIcons();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    if (messageBtn) {
        messageBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            
            const formData = new FormData();
            formData.append('user_id', userId);

            fetch(`${communityActionUrl}?ajax=start_conversation`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const chatPopup = document.getElementById('chatPopup');
                    if (chatPopup) {
                        chatPopup.classList.add('active');
                        lucide.createIcons();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    if (moreBtn) {
        moreBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = document.getElementById('followBtn')?.dataset.userId;
            if (!userId) return;
            
            const menu = document.createElement('div');
            menu.className = 'profile-more-menu';
            menu.style.cssText = 'position:absolute;top:100%;right:0;background:white;border:1px solid #ddd;border-radius:8px;padding:8px;z-index:100;box-shadow:0 4px 12px rgba(0,0,0,0.1);';
            menu.innerHTML = `
                <button class="menu-option block-user" data-user-id="${userId}" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:none;background:none;cursor:pointer;border-radius:4px;white-space:nowrap;">
                    <i data-lucide="slash" stroke-width="1"></i>
                    <span>Block User</span>
                </button>
            `;
            
            this.style.position = 'relative';
            this.appendChild(menu);
            lucide.createIcons();
            
            menu.querySelector('.block-user').addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Are you sure you want to block this user?')) {
                    const formData = new FormData();
                    formData.append('user_id', userId);

                    fetch(`${communityActionUrl}?ajax=block`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '/user/community/find-people';
                        }
                    });
                }
                menu.remove();
            });
            
            document.addEventListener('click', function closeMenu(e) {
                if (!menu.contains(e.target)) {
                    menu.remove();
                    document.removeEventListener('click', closeMenu);
                }
            });
        });
    }

    if (editProfileBtn && editProfileModal) {
        editProfileBtn.addEventListener('click', function() {
            editProfileModal.classList.add('active');
        });
        
        if (closeEditModal) {
            closeEditModal.addEventListener('click', function() {
                editProfileModal.classList.remove('active');
            });
        }
        
        editProfileModal.addEventListener('click', function(e) {
            if (e.target === editProfileModal) {
                editProfileModal.classList.remove('active');
            }
        });
    }

    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(window.location.pathname + '?ajax=update_profile', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    editProfileModal.classList.remove('active');
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
