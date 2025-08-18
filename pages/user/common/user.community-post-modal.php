<div class="post-modal-overlay" id="postModalOverlay" style="display: none;">
    <div class="post-modal">
        <div class="post-modal-header">
            <h3 id="postModalTitle">Create New Post</h3>
            <button type="button" class="post-modal-close" id="postModalClose">&times;</button>
        </div>
        <div class="post-modal-body">
            <p class="post-modal-subtitle" id="postModalSubtitle">Share your thoughts with the community.</p>

            <form id="postForm" action="/user/community/posts" method="post" enctype="multipart/form-data">
                <input type="hidden" id="editPostId" name="postId" value="">
                <input type="hidden" id="isEditMode" name="isEdit" value="false">

                <div class="form-group">
                    <label for="postTitle">Title<span class="optional">(optional)</span></label>
                    <input type="text" class="form-input" id="postTitle" name="title" placeholder="Enter post title" />
                </div>

                <div class="form-group">
                    <div class="post-category-tags">
                        <input type="radio" id="general" name="postType" value="general" checked style="display: none;">
                        <label for="general" class="category-tag active" data-category="general">General</label>

                        <input type="radio" id="support_request" name="postType" value="support_request" style="display: none;">
                        <label for="support_request" class="category-tag" data-category="support_request">Support</label>

                        <input type="radio" id="success_story" name="postType" value="success_story" style="display: none;">
                        <label for="success_story" class="category-tag" data-category="success_story">Achievement</label>

                        <input type="radio" id="question" name="postType" value="question" style="display: none;">
                        <label for="question" class="category-tag" data-category="question">Question</label>

                        <input type="radio" id="resource" name="postType" value="resource" style="display: none;">
                        <label for="resource" class="category-tag" data-category="resource">Advice</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="postContentInput">What's on your mind?</label>
                    <textarea class="form-input post-content-textarea" id="postContentInput" name="content" placeholder="Share your thoughts with the community..." rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label>Add Image<span class="optional">(optional)</span></label>
                    <div class="custom-file-upload">
                        <input type="file" id="postImage" name="image" accept="image/*" class="form-input" />
                    </div>
                    <div id="currentImagePreview" style="display: none; margin-top: 10px;">
                        <p style="font-size: 14px; color: #666;">Current image:</p>
                        <img id="currentImage" src="" alt="Current post image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <p style="font-size: 12px; color: #999; margin-top: 5px;">Upload a new image to replace the current one</p>
                    </div>
                </div>

                <div class="post-options-row">
                    <div class="checkbox-container">
                        <input type="checkbox" id="postAnonymous" name="privacy" value="anonymous" />
                        <label for="postAnonymous">Post anonymously</label>
                    </div>
                </div>

                <div class="post-modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelPost">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitPost">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>
