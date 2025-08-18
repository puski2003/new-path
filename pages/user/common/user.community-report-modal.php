<div class="post-modal-overlay" id="reportModalOverlay" style="display:none;">
    <div class="post-modal" style="max-width:480px;">
        <div class="post-modal-header">
            <h3>Report Post</h3>
            <button type="button" class="post-modal-close" id="reportModalClose">&times;</button>
        </div>
        <div class="post-modal-body">
            <input type="hidden" id="reportPostId" value="" />

            <div class="form-group">
                <label for="reportReason">Reason</label>
                <select class="form-input" id="reportReason">
                    <option value="">Select a reason…</option>
                    <option value="spam">Spam or misleading</option>
                    <option value="harassment">Harassment or bullying</option>
                    <option value="harmful">Harmful or dangerous content</option>
                    <option value="inappropriate">Inappropriate content</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reportDescription">Additional details <span class="optional">(optional)</span></label>
                <textarea class="form-input" id="reportDescription" rows="3" maxlength="500"
                    placeholder="Provide more context…"></textarea>
            </div>

            <div class="post-modal-actions">
                <button type="button" class="btn btn-secondary" id="cancelReport">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitReport">Submit Report</button>
            </div>
        </div>
    </div>
</div>
