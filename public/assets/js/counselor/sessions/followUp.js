document.addEventListener("DOMContentLoaded", function () {
  if (!window.NewPathFollowupThread) {
    return;
  }

  window.NewPathFollowupThread.initPopup({
    fallbackName: "Client",
    emptyMessage: "No messages yet.",
    composePlaceholder: "Type a message...",
    loadMessagesUrl: function (sessionId) {
      return "/counselor/sessions?ajax=get_messages&session_id=" + sessionId;
    },
    pollMessagesUrl: function (sessionId, lastId) {
      return "/counselor/sessions?ajax=poll_messages&session_id=" + sessionId + "&last_id=" + lastId;
    },
    sendMessageUrl: function () {
      return "/counselor/sessions?ajax=send_message";
    },
  });
});
