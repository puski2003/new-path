document.addEventListener('DOMContentLoaded', function() {
    console.log('Community script loaded');
    
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Community functionality
    let pendingDeletePostId = null;

    // Initialize posting functionality first
    initializePostModal();

    // Initialize category tags
    initializeCategoryTags();

    // Initialize post menu dropdowns
    initializePostMenus();

    // Initialize follow buttons
    initializeFollowButtons();

    // Initialize delete confirmation modal
    initializeDeleteConfirmationModal();

    // Initialize new feature handlers
    initializeReportModal();
    initializeComments();
    initializeShareButtons();

    // Post creation functionality
    const postButton = document.querySelector('.community-content-header .btn-primary');
    console.log('Post button found:', postButton);
    
    if (postButton) {
        postButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Post button clicked - showing modal');
            showCreatePostModal();
        });
    } else {
        console.error('Post button not found!');
    }

    // Like functionality for existing posts
    setupLikeFunctionality();

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.post-menu-container')) {
            closeAllPostMenus();
        }
    });

    function showCreatePostModal() {
        console.log('showCreatePostModal called');
        
        // Reset form to create mode first
        resetPostForm();
        
        const modal = document.getElementById('postModalOverlay');
        console.log('Modal element:', modal);
        
        if (modal) {
            modal.style.display = 'flex';
            modal.offsetHeight; // Force reflow
            modal.classList.add('show');
            
            const titleInput = document.getElementById('postTitle');
            if (titleInput) {
                setTimeout(() => titleInput.focus(), 100);
            }
        } else {
            console.error('Modal element not found!');
        }
    }

    function initializePostModal() {
        console.log('Initializing post modal');
        
        const modal = document.getElementById('postModalOverlay');
        const closeBtn = document.getElementById('postModalClose');
        const cancelBtn = document.getElementById('cancelPost');
        const submitBtn = document.getElementById('submitPost');
        const contentInput = document.getElementById('postContentInput');
        const titleInput = document.getElementById('postTitle');
        const postForm = document.getElementById('postForm');

        // Initialize file upload functionality
        initializeFileUpload();

        // Close modal functions
        const closeModal = () => {
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
                resetPostForm();
            }
        };

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        // Enable/disable submit button based on content
        function updateSubmitButton() {
            const hasContent = contentInput && contentInput.value.trim().length > 0;
            if (submitBtn) {
                submitBtn.disabled = !hasContent;
            }
        }

        if (contentInput) {
            contentInput.addEventListener('input', updateSubmitButton);
        }

        // REMOVE FORM SUBMISSION HANDLER - Let form submit naturally
        // if (postForm) {
        //     postForm.addEventListener('submit', handlePostSubmission);
        // }
    }

    function initializeFileUpload() {
        const fileInput = document.getElementById('postImage');
        const fileUploadBtn = document.querySelector('.file-upload-btn');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const imagePreview = document.getElementById('imagePreview');
        const removeFileBtn = document.getElementById('removeFile');

        if (!fileInput || !fileUploadBtn) return;

        // File input change handler
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                displayFileInfo(file);
            }
        });

        // Remove file handler
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearFileSelection();
            });
        }

        // Drag and drop functionality
        fileUploadBtn.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        fileUploadBtn.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });

        fileUploadBtn.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                fileInput.files = files;
                displayFileInfo(files[0]);
            }
        });

        function displayFileInfo(file) {
            // Update file name and size
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);

            // Show image preview if it's an image
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }

            // Show file info and update button state
            fileInfo.style.display = 'block';
            fileUploadBtn.classList.add('file-selected');
            fileUploadBtn.querySelector('.upload-text').textContent = 'Change Image';
        }

        function clearFileSelection() {
            fileInput.value = '';
            fileInfo.style.display = 'none';
            fileUploadBtn.classList.remove('file-selected');
            fileUploadBtn.querySelector('.upload-text').textContent = 'Choose Image';
            imagePreview.src = '';
            imagePreview.style.display = 'none';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }

    function initializeCategoryTags() {
        const categoryTags = document.querySelectorAll('.category-tag');
        const selectedCategoryInput = document.getElementById('selectedCategory');

        categoryTags.forEach(tag => {
            tag.addEventListener('click', function() {
                // Remove active class from all tags
                categoryTags.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tag
                this.classList.add('active');
                
                // Update hidden input
                const category = this.getAttribute('data-category');
                if (selectedCategoryInput) {
                    selectedCategoryInput.value = category;
                }
            });
        });
    }

    function initializePostMenus() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.post-menu-btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                const menuBtn = e.target.closest('.post-menu-btn');
                const postId = menuBtn.getAttribute('data-post-id');
                const dropdown = document.getElementById(`postMenu-${postId}`);
                
                if (dropdown) {
                    // Close all other dropdowns
                    closeAllPostMenus();
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('show');
                }
            }
            
            // Handle menu option clicks
            if (e.target.closest('.menu-option')) {
                e.preventDefault();
                const menuOption = e.target.closest('.menu-option');
                const postId = menuOption.getAttribute('data-post-id');
                
                if (menuOption.classList.contains('edit-post')) {
                    handleEditPost(postId);
                } else if (menuOption.classList.contains('delete-post-btn')) {
                    handleDeletePost(postId);
                } else if (menuOption.classList.contains('report-post')) {
                    handleReportPost(postId);
                } else if (menuOption.classList.contains('save-post')) {
                    handleSavePost(postId);
                } else if (menuOption.classList.contains('share-post')) {
                    handleSharePost(postId);
                }
                
                closeAllPostMenus();
            }
        });
    }

    function closeAllPostMenus() {
        const allDropdowns = document.querySelectorAll('.post-menu-dropdown');
        allDropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }

    function initializeDeleteConfirmationModal() {
        const modal = document.getElementById('deleteConfirmationModal');
        const cancelBtn = document.getElementById('cancelDelete');
        const confirmBtn = document.getElementById('confirmDelete');

        if (!modal || !cancelBtn || !confirmBtn) {
            console.warn('Delete confirmation modal elements not found');
            return;
        }

        // Cancel button
        cancelBtn.addEventListener('click', function() {
            hideDeleteConfirmationModal();
        });

        // Confirm button
        confirmBtn.addEventListener('click', function() {
            if (pendingDeletePostId) {
                executeDeletePost(pendingDeletePostId);
            }
        });

        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideDeleteConfirmationModal();
            }
        });

        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display !== 'none') {
                hideDeleteConfirmationModal();
            }
        });
    }

    function showDeleteConfirmationModal(postId) {
        pendingDeletePostId = postId;
        const modal = document.getElementById('deleteConfirmationModal');
        
        if (modal) {
            modal.style.display = 'flex';
            // Force reflow
            modal.offsetHeight;
            modal.classList.add('show');
            
            // Initialize Lucide icons for the modal
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Focus on cancel button for accessibility
            const cancelBtn = document.getElementById('cancelDelete');
            if (cancelBtn) {
                setTimeout(() => cancelBtn.focus(), 100);
            }
        }
    }

    function hideDeleteConfirmationModal() {
        const modal = document.getElementById('deleteConfirmationModal');
        const confirmBtn = document.getElementById('confirmDelete');
        
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
        
        // Reset button state
        if (confirmBtn) {
            confirmBtn.classList.remove('loading');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i data-lucide="trash-2" class="btn-icon"></i>Delete Post';
            // Re-initialize icons after innerHTML change
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        pendingDeletePostId = null;
    }

    function executeDeletePost(postId) {
        const confirmBtn = document.getElementById('confirmDelete');
        
        // Show loading state
        if (confirmBtn) {
            confirmBtn.classList.add('loading');
            confirmBtn.disabled = true;
        }

        // Find and submit the hidden form for this post
        const deleteForm = document.getElementById(`deleteForm-${postId}`);
        if (deleteForm) {
            deleteForm.submit();
        } else {
            console.error('Delete form not found for post:', postId);
            hideDeleteConfirmationModal();
            alert('Delete form not found');
        }
    }

    function handleDeletePost(postId) {
        showDeleteConfirmationModal(postId);
    }

    function handleEditPost(postId) {
        console.log('Edit post:', postId);
        
        // Get post data from hidden div
        const editDataDiv = document.getElementById(`editData-${postId}`);
        if (!editDataDiv) {
            alert('Post data not found');
            return;
        }
        
        // Extract post data
        const postData = {
            postId: editDataDiv.getAttribute('data-post-id'),
            content: editDataDiv.getAttribute('data-content'),
            title: editDataDiv.getAttribute('data-title'),
            postType: editDataDiv.getAttribute('data-post-type'),
            anonymous: editDataDiv.getAttribute('data-anonymous') === 'true',
            imageUrl: editDataDiv.getAttribute('data-image-url')
        };
        
        showEditPostModal(postData);
    }

    function showEditPostModal(postData) {
        // Update modal title and subtitle
        const modalTitle = document.getElementById('postModalTitle');
        const modalSubtitle = document.getElementById('postModalSubtitle');
        const submitBtn = document.getElementById('submitPost');
        
        if (modalTitle) modalTitle.textContent = 'Edit Post';
        if (modalSubtitle) modalSubtitle.textContent = 'Update your post content.';
        if (submitBtn) submitBtn.textContent = 'Update Post';
        
        // Set form action for editing
        const form = document.getElementById('postForm');
        const editPostIdInput = document.getElementById('editPostId');
        const isEditModeInput = document.getElementById('isEditMode');
        
        if (form) form.action = form.action.replace('/user/community/posts', '/user/community/posts/edit');
        if (editPostIdInput) editPostIdInput.value = postData.postId;
        if (isEditModeInput) isEditModeInput.value = 'true';
        
        // Populate form fields
        const titleInput = document.getElementById('postTitle');
        const contentInput = document.getElementById('postContentInput');
        const anonymousCheckbox = document.getElementById('postAnonymous');
        
        if (titleInput) titleInput.value = postData.title || '';
        if (contentInput) contentInput.value = postData.content || '';
        if (anonymousCheckbox) anonymousCheckbox.checked = postData.anonymous;
        
        // Set category/post type
        const categoryTags = document.querySelectorAll('.category-tag');
        categoryTags.forEach(tag => {
            tag.classList.remove('active');
            const radio = document.getElementById(tag.getAttribute('data-category'));
            if (radio) radio.checked = false;
        });
        
        if (postData.postType) {
            const targetTag = document.querySelector(`[data-category="${postData.postType}"]`);
            const targetRadio = document.getElementById(postData.postType);
            if (targetTag) targetTag.classList.add('active');
            if (targetRadio) targetRadio.checked = true;
        }
        
        // Show current image if exists
        const currentImagePreview = document.getElementById('currentImagePreview');
        const currentImage = document.getElementById('currentImage');
        
        if (postData.imageUrl && postData.imageUrl.trim() !== '' && postData.imageUrl !== 'null') {
            if (currentImagePreview) currentImagePreview.style.display = 'block';
            if (currentImage) currentImage.src = postData.imageUrl;
        } else {
            if (currentImagePreview) currentImagePreview.style.display = 'none';
        }
        
        // Show the modal
        const modal = document.getElementById('postModalOverlay');
        if (modal) {
            modal.style.display = 'flex';
            modal.offsetHeight;
            modal.classList.add('show');
            
            // Focus on content input
            setTimeout(() => {
                if (contentInput) {
                    contentInput.focus();
                    contentInput.setSelectionRange(contentInput.value.length, contentInput.value.length);
                }
            }, 100);
        }
    }

    function resetPostForm() {
        const form = document.getElementById('postForm');
        if (form) {
            form.reset();
            // Reset form action to create
            const baseAction = form.action.replace('/user/community/posts/edit', '/user/community/posts');
            form.action = baseAction;
        }
        
        // Reset modal to create mode
        const modalTitle = document.getElementById('postModalTitle');
        const modalSubtitle = document.getElementById('postModalSubtitle');
        const submitBtn = document.getElementById('submitPost');
        const editPostIdInput = document.getElementById('editPostId');
        const isEditModeInput = document.getElementById('isEditMode');
        
        if (modalTitle) modalTitle.textContent = 'Create New Post';
        if (modalSubtitle) modalSubtitle.textContent = 'Share your thoughts with the community.';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.value = 'Post'; // Use .value for input elements
        }
        if (editPostIdInput) editPostIdInput.value = '';
        if (isEditModeInput) isEditModeInput.value = 'false';
        
        // Hide current image preview
        const currentImagePreview = document.getElementById('currentImagePreview');
        if (currentImagePreview) currentImagePreview.style.display = 'none';
        
        // Reset file upload
        const fileInfo = document.getElementById('fileInfo');
        const fileUploadBtn = document.querySelector('.file-upload-btn');
        const imagePreview = document.getElementById('imagePreview');
        
        if (fileInfo) fileInfo.style.display = 'none';
        if (fileUploadBtn) {
            fileUploadBtn.classList.remove('file-selected');
            const uploadText = fileUploadBtn.querySelector('.upload-text');
            if (uploadText) uploadText.textContent = 'Choose Image';
        }
        if (imagePreview) {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
        }
        
        // Reset category selection to first one (General)
        const categoryTags = document.querySelectorAll('.category-tag');
        const selectedCategoryInput = document.getElementById('selectedCategory');
        
        categoryTags.forEach(tag => tag.classList.remove('active'));
        if (categoryTags.length > 0) {
            categoryTags[0].classList.add('active');
            const firstRadio = categoryTags[0].getAttribute('data-category');
            const radio = document.getElementById(firstRadio);
            if (radio) radio.checked = true;
            if (selectedCategoryInput) {
                selectedCategoryInput.value = 'general';
            }
        }
    }

    function setupLikeFunctionality() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.like-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.like-btn');
                const postId = btn.getAttribute('data-post-id');
                toggleLike(postId, btn);
            }
        });
    }

    function toggleLike(postId, btn) {
        fetch('/user/community/posts/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ postId: Number(postId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('liked', data.liked);
                const count = btn.querySelector('.action-count');
                
                if (count) {
                    const currentCount = parseInt(count.textContent) || 0;
                    count.textContent = data.liked ? currentCount + 1 : Math.max(0, currentCount - 1);
                }
            }
        })
        .catch(error => {
            console.error('Error toggling like:', error);
        });
    }

    // ----------------------------------------------------------------
    // Report
    // ----------------------------------------------------------------

    function handleReportPost(postId) {
        const overlay = document.getElementById('reportModalOverlay');
        const postIdInput = document.getElementById('reportPostId');
        const reasonSelect = document.getElementById('reportReason');
        const descTextarea = document.getElementById('reportDescription');
        if (!overlay) return;
        if (postIdInput) postIdInput.value = postId;
        if (reasonSelect) reasonSelect.value = '';
        if (descTextarea) descTextarea.value = '';
        overlay.style.display = 'flex';
        overlay.offsetHeight;
        overlay.classList.add('show');
    }

    function initializeReportModal() {
        const overlay = document.getElementById('reportModalOverlay');
        const closeBtn = document.getElementById('reportModalClose');
        const cancelBtn = document.getElementById('cancelReport');
        const submitBtn = document.getElementById('submitReport');

        if (!overlay) return;

        const close = () => {
            overlay.classList.remove('show');
            setTimeout(() => { overlay.style.display = 'none'; }, 300);
        };

        if (closeBtn) closeBtn.addEventListener('click', close);
        if (cancelBtn) cancelBtn.addEventListener('click', close);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });

        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                const postId = parseInt(document.getElementById('reportPostId').value, 10);
                const reason = document.getElementById('reportReason').value.trim();
                const description = document.getElementById('reportDescription').value.trim();

                if (!reason) {
                    document.getElementById('reportReason').focus();
                    return;
                }

                submitBtn.disabled = true;

                fetch('/user/community/posts/report', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ postId, reason, description }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        close();
                        showToast('Report submitted. Thank you.');
                    } else {
                        showToast(data.message || 'Failed to submit report.');
                    }
                })
                .catch(() => showToast('Network error. Please try again.'))
                .finally(() => { submitBtn.disabled = false; });
            });
        }
    }

    // ----------------------------------------------------------------
    // Save
    // ----------------------------------------------------------------

    function handleSavePost(postId) {
        const btn = document.querySelector(`.save-post[data-post-id="${postId}"]`);
        if (!btn) return;

        fetch('/user/community/posts/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ postId: Number(postId) }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const saved = data.saved;
            btn.setAttribute('data-saved', saved ? 'true' : 'false');
            const icon = btn.querySelector('i[data-lucide]');
            const label = btn.querySelector('span');
            if (icon) {
                icon.setAttribute('data-lucide', saved ? 'bookmark-check' : 'bookmark');
                lucide.createIcons({ nodes: [icon] });
            }
            if (label) label.textContent = saved ? 'Saved' : 'Save Post';
            showToast(saved ? 'Post saved.' : 'Post unsaved.');
        })
        .catch(() => showToast('Network error. Please try again.'));
    }

    // ----------------------------------------------------------------
    // Share — copy link to clipboard + increment share counter
    // ----------------------------------------------------------------

    function handleSharePost(postId) {
        const url = window.location.origin + '/user/community#post-' + postId;

        const copyDone = () => {
            // Increment share count on server
            fetch('/user/community/posts/share', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ postId: Number(postId) }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const shareBtn = document.querySelector(`.share-btn[data-post-id="${postId}"]`);
                    if (shareBtn) {
                        const count = shareBtn.querySelector('.action-count');
                        if (count) count.textContent = (parseInt(count.textContent, 10) || 0) + 1;
                    }
                }
            })
            .catch(() => {});
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard.');
                copyDone();
            }).catch(() => fallbackCopy(url, copyDone));
        } else {
            fallbackCopy(url, copyDone);
        }
    }

    function fallbackCopy(text, callback) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            showToast('Link copied to clipboard.');
            if (callback) callback();
        } catch (_) {
            showToast('Could not copy link.');
        }
        document.body.removeChild(ta);
    }

    function initializeShareButtons() {
        // Wire the share-btn in the action bar (separate from the dropdown .share-post handled by initializePostMenus)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.share-btn');
            if (!btn) return;
            e.preventDefault();
            handleSharePost(btn.getAttribute('data-post-id'));
        });
    }

    // ----------------------------------------------------------------
    // Comments
    // ----------------------------------------------------------------

    function initializeComments() {
        // Toggle comment section on comment-btn click
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.comment-btn');
            if (!btn) return;
            e.preventDefault();
            const postId = btn.getAttribute('data-post-id');
            toggleCommentSection(postId);
        });

        // Submit comment form
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.comment-form');
            if (!form) return;
            e.preventDefault();
            const postId = form.getAttribute('data-post-id');
            const input = form.querySelector('.comment-input');
            const content = input ? input.value.trim() : '';
            if (!content) return;
            submitComment(postId, content, input);
        });
    }

    function toggleCommentSection(postId) {
        const section = document.getElementById('comments-' + postId);
        if (!section) return;

        const isHidden = section.style.display === 'none' || section.style.display === '';
        if (isHidden) {
            section.style.display = 'block';
            loadComments(postId);
            const input = section.querySelector('.comment-input');
            if (input) setTimeout(() => input.focus(), 50);
        } else {
            section.style.display = 'none';
        }
    }

    function loadComments(postId) {
        const list = document.getElementById('comments-list-' + postId);
        if (!list) return;

        list.innerHTML = '<p style="color:var(--color-text-muted,#999);font-size:.85rem;padding:8px 0;">Loading…</p>';

        fetch('/user/community/posts/comments?post_id=' + postId)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { list.innerHTML = ''; return; }
            renderComments(list, data.comments);
        })
        .catch(() => { list.innerHTML = ''; });
    }

    function renderComments(container, comments) {
        container.innerHTML = '';
        if (!comments || comments.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'comments-empty';
            empty.textContent = 'No comments yet. Be the first!';
            container.appendChild(empty);
            return;
        }
        comments.forEach(c => container.appendChild(buildCommentEl(c)));
    }

    function buildCommentEl(c) {
        const wrap = document.createElement('div');
        wrap.className = 'community-comment';

        const img = document.createElement('img');
        img.src = c.profilePicture || '/assets/img/avatar.png';
        img.alt = '';
        img.className = 'comment-avatar';

        const body = document.createElement('div');
        body.className = 'comment-body';

        const meta = document.createElement('div');
        meta.className = 'comment-meta';

        const author = document.createElement('span');
        author.className = 'comment-author';
        author.textContent = c.displayName || 'User';

        const time = document.createElement('span');
        time.className = 'comment-time';
        if (c.createdAt) {
            time.textContent = new Date(c.createdAt).toLocaleString(undefined, {
                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        }

        meta.appendChild(author);
        meta.appendChild(time);

        const text = document.createElement('p');
        text.className = 'comment-text';
        text.textContent = c.content || '';

        body.appendChild(meta);
        body.appendChild(text);
        wrap.appendChild(img);
        wrap.appendChild(body);

        return wrap;
    }

    function submitComment(postId, content, inputEl) {
        fetch('/user/community/posts/comments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ postId: Number(postId), content }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) { showToast(data.error || 'Failed to post comment.'); return; }
            if (inputEl) inputEl.value = '';

            // Append the new comment to the list
            const list = document.getElementById('comments-list-' + postId);
            if (list) {
                const empty = list.querySelector('.comments-empty');
                if (empty) empty.remove();
                list.appendChild(buildCommentEl(data.comment));
            }

            // Update comment count in action bar
            const commentBtn = document.querySelector(`.comment-btn[data-post-id="${postId}"]`);
            if (commentBtn) {
                const countEl = commentBtn.querySelector('.action-count');
                if (countEl) countEl.textContent = (parseInt(countEl.textContent, 10) || 0) + 1;
            }
        })
        .catch(() => showToast('Network error. Please try again.'));
    }

    // ----------------------------------------------------------------
    // Shared toast helper

    function showToast(message) {
        let toast = document.getElementById('communityToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'communityToast';
            toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:10px 20px;border-radius:6px;font-size:.875rem;z-index:9999;opacity:0;transition:opacity .25s;pointer-events:none;';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.opacity = '1';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 2800);
    }

    function initializeFollowButtons() {
        document.addEventListener('click', function(e) {
            const followBtn = e.target.closest('.btn-follow-post');
            if (followBtn) {
                e.preventDefault();
                e.stopPropagation();
                handleFollowClick(followBtn);
            }
        });
    }
    
    function handleFollowClick(btn) {
        const userId = btn.dataset.userId;
        const isFollowing = btn.classList.contains('following');
        
        const formData = new FormData();
        formData.append('user_id', userId);
        
        const action = isFollowing ? 'unfollow' : 'follow';
        
        fetch(`/user/community?ajax=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isFollowing) {
                    btn.classList.remove('following');
                    btn.innerHTML = '<i data-lucide="user-plus" stroke-width="2"></i><span>Follow</span>';
                } else {
                    btn.classList.add('following');
                    btn.innerHTML = '<i data-lucide="user-check" stroke-width="2"></i><span>Following</span>';
                }
                lucide.createIcons();
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
