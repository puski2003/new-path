<div class="modal-overlay" id="deleteConfirmationModal" style="display: none;">
    <div class="confirmation-modal">
        <div class="confirmation-modal-header">
            <i data-lucide="alert-triangle" class="warning-icon"></i>
            <h3>Delete Post</h3>
        </div>

        <div class="confirmation-modal-body">
            <p>Are you sure you want to delete this post?</p>
            <p class="warning-text">This action cannot be undone and will permanently remove your post and all its comments.</p>
        </div>

        <div class="confirmation-modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelDelete">
                <i data-lucide="x" class="btn-icon"></i>
                Cancel
            </button>
            <button type="button" class="btn btn-danger" id="confirmDelete">
                <i data-lucide="trash-2" class="btn-icon"></i>
                Delete Post
            </button>
        </div>
    </div>
</div>
