(function () {
  function ensureAnimationStyle() {
    if (document.getElementById("new-path-toast-style")) {
      return;
    }

    const style = document.createElement("style");
    style.id = "new-path-toast-style";
    style.textContent =
      "@keyframes newPathToastIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}" +
      "@keyframes newPathToastOut{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}";
    document.head.appendChild(style);
  }

  function show(message, type) {
    ensureAnimationStyle();

    const existing = document.querySelector(".new-path-toast");
    if (existing) {
      existing.remove();
    }

    const toast = document.createElement("div");
    toast.className = "new-path-toast";
    toast.innerHTML =
      '<span class="new-path-toast__message"></span><button type="button" class="new-path-toast__close" aria-label="Close">&times;</button>';

    const messageNode = toast.querySelector(".new-path-toast__message");
    const closeButton = toast.querySelector(".new-path-toast__close");
    if (messageNode) {
      messageNode.textContent = String(message);
    }

    const background = {
      success: "#10b981",
      error: "#ef4444",
      info: "#3b82f6",
      warning: "#f59e0b",
    }[type || "info"] || "#3b82f6";

    toast.style.cssText = [
      "position:fixed",
      "top:20px",
      "right:20px",
      "display:flex",
      "align-items:center",
      "gap:12px",
      "min-width:280px",
      "max-width:420px",
      "padding:14px 18px",
      "border-radius:10px",
      "box-shadow:0 10px 30px rgba(0,0,0,0.18)",
      "z-index:9999",
      "color:#fff",
      "background:" + background,
      "animation:newPathToastIn 0.25s ease",
    ].join(";");

    if (closeButton) {
      closeButton.style.cssText =
        "background:none;border:none;color:inherit;font-size:20px;line-height:1;cursor:pointer;padding:0;margin-left:auto;";
      closeButton.addEventListener("click", function () {
        dismiss(toast);
      });
    }

    document.body.appendChild(toast);
    setTimeout(function () {
      dismiss(toast);
    }, 4000);
  }

  function dismiss(toast) {
    if (!toast || !toast.parentNode) {
      return;
    }

    toast.style.animation = "newPathToastOut 0.25s ease";
    setTimeout(function () {
      toast.remove();
    }, 250);
  }

  window.NewPathToast = {
    show: show,
  };
})();
