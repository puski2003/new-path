document.addEventListener('DOMContentLoaded', function() {
    const communityActionUrl = '/user/community';
    const usersGrid = document.getElementById('usersGrid');
    
    if (usersGrid) {
        usersGrid.addEventListener('click', function(e) {
            const followBtn = e.target.closest('.btn-follow');
            if (followBtn) {
                e.preventDefault();
                handleFollowClick(followBtn);
                return;
            }
            
            const moreBtn = e.target.closest('.btn-more');
            if (moreBtn) {
                e.preventDefault();
                handleMoreClick(moreBtn);
                return;
            }
        });
    }
    
    function handleFollowClick(btn) {
        const userId = btn.dataset.userId;
        const isFollowing = btn.classList.contains('following');
        
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
                    btn.classList.remove('following');
                    btn.innerHTML = '<i data-lucide="user-plus" stroke-width="2"></i> Follow';
                    btn.disabled = false;
                    lucide.createIcons();
                } else {
                    btn.classList.add('following');
                    btn.innerHTML = '<i data-lucide="user-check" stroke-width="2"></i> Following';
                    btn.disabled = false;
                    lucide.createIcons();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    function handleMoreClick(btn) {
        const userId = btn.dataset.userId;
        const card = btn.closest('.user-card');
        
        const existingMenu = card.querySelector('.user-menu-dropdown');
        if (existingMenu) {
            existingMenu.remove();
            return;
        }
        
        const menu = document.createElement('div');
        menu.className = 'user-menu-dropdown';
        menu.innerHTML = `
            <button class="menu-option block-user" data-user-id="${userId}">
                <i data-lucide="slash" stroke-width="1"></i>
                <span>Block</span>
            </button>
        `;
        
        card.style.position = 'relative';
        card.appendChild(menu);
        lucide.createIcons();
        
        menu.querySelector('.block-user').addEventListener('click', function(e) {
            e.stopPropagation();
            handleBlockUser(userId, card);
        });
        
        document.addEventListener('click', function closeMenu(e) {
            if (!menu.contains(e.target)) {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            }
        });
    }
    
    function handleBlockUser(userId, card) {
        if (!confirm('Are you sure you want to block this user? They won\'t appear in your search results.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('user_id', userId);
        
        fetch(`${communityActionUrl}?ajax=block`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
