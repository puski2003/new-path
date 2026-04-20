document.addEventListener("DOMContentLoaded", function () {
  if (!window.NewPathFollowupThread) {
    return;
  }

  window.NewPathFollowupThread.initPopup({
    fallbackName: "Counselor",
    emptyMessage: "No messages yet. Start the conversation!",
    composePlaceholder: "Type a message...",
    loadMessagesUrl: function (sessionId) {
      return "/user/sessions/follow-up?ajax=get_messages&session_id=" + sessionId;
    },
    sendMessageUrl: function () {
      return "/user/sessions/follow-up?ajax=send_message";
    },
  });
});
