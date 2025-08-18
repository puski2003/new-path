<?php
$dmConversations = $dmConversations ?? [];
$supportGroups = $supportGroups ?? [];
$pendingRequests = $pendingRequests ?? [];
$totalUnread = $totalDmUnread + $totalGroupUnread;
?>

<button class="chat-toggle-btn" id="chatToggleBtn" aria-label="Open chat">
    <i data-lucide="message-circle" class="chat-icon" stroke-width="2"></i>
    <?php if ($totalUnread > 0): ?>
    <span class="notification-badge"><?= $totalUnread > 99 ? '99+' : $totalUnread ?></span>
    <?php endif; ?>
</button>

<div class="chat-popup" id="chatPopup">
    <div class="chat-header">
        <div class="chat-tabs">
            <button class="chat-tab chat-tab--active" data-tab="direct">Direct Messages</button>
            <button class="chat-tab" data-tab="support">Support Groups</button>
        </div>
        <button class="chat-close-btn" id="chatCloseBtn" aria-label="Close chat">
            <i data-lucide="x" class="close-icon" stroke-width="2"></i>
        </button>
    </div>

    <div class="chat-content">
        <div class="chat-search">
            <i data-lucide="search" class="chat-search-icon" stroke-width="2"></i>
            <input type="text" placeholder="Search" class="chat-search-input" id="chatSearch" />
        </div>

        <!-- Direct Messages Tab -->
        <div class="chat-tab-content chat-tab-content--active" id="directTab">
            <?php if (!empty($pendingRequests)): ?>
            <div class="pending-requests" style="padding: 8px 16px; background: #fff8e1; border-bottom: 1px solid #ffe082;">
                <div style="font-size: 12px; font-weight: 600; color: #f57c00; margin-bottom: 8px;">
                    Connection Requests (<?= count($pendingRequests) ?>)
                </div>
                <?php foreach (array_slice($pendingRequests, 0, 3) as $request): ?>
                <div class="connection-request-item">
                    <div class="chat-avatar">
                        <img src="<?= !empty($request['profilePicture']) ? htmlspecialchars($request['profilePicture']) : '/assets/img/avatar.png' ?>" alt="<?= htmlspecialchars($request['displayName']) ?>" />
                    </div>
                    <div class="chat-info">
                        <h4 class="chat-name"><?= htmlspecialchars($request['displayName']) ?></h4>
                        <span class="chat-status">@<?= htmlspecialchars($request['username']) ?></span>
                    </div>
                    <div class="request-actions">
                        <button class="accept-btn" data-connection-id="<?= $request['connectionId'] ?>">Accept</button>
                        <button class="decline-btn" data-connection-id="<?= $request['connectionId'] ?>">Decline</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="chat-list" id="dmChatList">
                <?php if (empty($dmConversations)): ?>
                <div class="empty-state">
                    <i data-lucide="users" class="empty-state-icon" stroke-width="1.5"></i>
                    <h4 class="empty-state-title">No conversations yet</h4>
                    <p class="empty-state-text">Connect with other users to start chatting</p>
                </div>
                <?php else: ?>
                    <?php foreach ($dmConversations as $conv): ?>
                    <div class="chat-item dm-conversation-item" 
                         data-conversation-id="<?= $conv['conversationId'] ?>"
                         data-user-id="<?= $conv['userId'] ?>"
                         data-user-name="<?= htmlspecialchars($conv['displayName']) ?>"
                         data-profile-picture="<?= htmlspecialchars($conv['profilePicture']) ?>">
                        <div class="chat-avatar">
                            <img src="<?= !empty($conv['profilePicture']) ? htmlspecialchars($conv['profilePicture']) : '/assets/img/avatar.png' ?>" alt="<?= htmlspecialchars($conv['displayName']) ?>" />
                            <span class="online-indicator"></span>
                        </div>
                        <div class="chat-info">
                            <h4 class="chat-name"><?= htmlspecialchars($conv['displayName']) ?></h4>
                            <span class="chat-preview"><?= htmlspecialchars($conv['lastMessagePreview'] ?: 'No messages yet') ?></span>
                        </div>
                        <?php if ($conv['unreadCount'] > 0): ?>
                        <span class="unread-badge"><?= $conv['unreadCount'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Support Groups Tab -->
        <div class="chat-tab-content" id="supportTab">
            <div class="chat-list" id="supportChatList">
                <?php if (empty($supportGroups)): ?>
                <div class="empty-state" id="noGroupsState">
                    <i data-lucide="users" class="empty-state-icon" stroke-width="1.5"></i>
                    <h4 class="empty-state-title">No groups joined</h4>
                    <p class="empty-state-text">Discover and join a group below</p>
                </div>
                <?php else: ?>
                    <?php foreach ($supportGroups as $group): ?>
                    <div class="chat-item support-group-item"
                         data-group-id="<?= $group['groupId'] ?>"
                         data-group-name="<?= htmlspecialchars($group['name']) ?>"
                         data-group-category="<?= htmlspecialchars($group['category']) ?>"
                         data-meeting-link="<?= htmlspecialchars($group['meetingLink']) ?>"
                         data-meeting-schedule="<?= htmlspecialchars($group['meetingSchedule']) ?>">
                        <div class="chat-avatar group-avatar">
                            <img src="/assets/img/avatar.png" alt="<?= htmlspecialchars($group['name']) ?>" />
                        </div>
                        <div class="chat-info">
                            <h4 class="chat-name"><?= htmlspecialchars($group['name']) ?></h4>
                            <span class="chat-status"><?= $group['memberCount'] ?> members · <?= htmlspecialchars($group['category']) ?></span>
                        </div>
                        <?php if ($group['unreadCount'] > 0): ?>
                        <span class="unread-badge"><?= $group['unreadCount'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Discover Groups section -->
            <div class="browse-groups-section">
                <div class="browse-groups-divider">
                    <span class="browse-groups-divider-text">Discover Groups</span>
                    <button class="browse-groups-toggle-btn" id="browseGroupsToggle" title="Browse available groups">
                        <i data-lucide="chevron-down" stroke-width="2"></i>
                    </button>
                </div>
                <div class="available-groups-list" id="availableGroupsList" style="display:none;"></div>
            </div>
        </div>

        <!-- DM Conversation View -->
        <div class="conversation-view" id="dmConversationView">
            <div class="conversation-header">
                <button class="back-btn" id="dmBackBtn">
                    <i data-lucide="arrow-left" stroke-width="2"></i>
                </button>
                <div class="conversation-user-info">
                    <div class="conversation-user-avatar">
                        <img id="dmConversationAvatar" src="/assets/img/avatar.png" alt="" />
                    </div>
                    <div>
                        <h4 class="conversation-user-name" id="dmConversationName">User Name</h4>
                        <span class="conversation-user-status online" id="dmConversationStatus">Active now</span>
                    </div>
                </div>
            </div>
            <div class="messages-container" id="dmMessagesContainer">
            </div>
            <div class="message-input-container">
                <input type="text" class="message-input" id="dmMessageInput" placeholder="Type a message..." />
                <button class="send-btn" id="dmSendBtn">
                    <i data-lucide="send" stroke-width="2"></i>
                </button>
            </div>
        </div>

        <!-- Support Group Conversation View -->
        <div class="conversation-view" id="groupConversationView">
            <div class="conversation-header">
                <button class="back-btn" id="groupBackBtn">
                    <i data-lucide="arrow-left" stroke-width="2"></i>
                </button>
                <div class="conversation-user-info">
                    <div class="conversation-user-avatar group-avatar">
                        <img src="/assets/img/avatar.png" alt="" />
                    </div>
                    <div>
                        <h4 class="conversation-user-name" id="groupConversationName">Group Name</h4>
                        <span class="conversation-user-status" id="groupConversationStatus">0 members</span>
                    </div>
                </div>
                <button class="meeting-btn" id="groupMeetingBtn" style="display: none;">
                    <i data-lucide="video" stroke-width="2"></i>
                </button>
            </div>
            <div class="group-info-panel" id="groupInfoPanel" style="display: none;">
                <p class="group-description" id="groupDescription"></p>
                <div class="group-meta">
                    <span class="group-meta-item">
                        <i data-lucide="users" stroke-width="2"></i>
                        <span id="groupMemberCount">0</span> members
                    </span>
                    <?php if (!empty($meetingSchedule)): ?>
                    <span class="group-meta-item">
                        <i data-lucide="calendar" stroke-width="2"></i>
                        <span id="groupMeetingSchedule"></span>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="messages-container" id="groupMessagesContainer">
            </div>
            <div class="message-input-container">
                <input type="text" class="message-input" id="groupMessageInput" placeholder="Type a message..." />
                <button class="send-btn" id="groupSendBtn">
                    <i data-lucide="send" stroke-width="2"></i>
                </button>
            </div>
        </div>
    </div>
</div>
