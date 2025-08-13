(function () {
  var overlay = null;
  var titleEl = null;
  var messageEl = null;
  var closeButton = null;
  var okButton = null;
  var originalAlert = window.alert ? window.alert.bind(window) : null;

  function ensureDialog() {
    if (overlay) {
      return;
    }

    overlay = document.createElement("div");
    overlay.className = "app-alert-overlay";
    overlay.hidden = true;

    var dialog = document.createElement("div");
    dialog.className = "app-alert app-alert-info";
    dialog.setAttribute("role", "alertdialog");
    dialog.setAttribute("aria-modal", "true");

    var header = document.createElement("div");
    header.className = "app-alert-header";

    titleEl = document.createElement("h3");
    titleEl.className = "app-alert-title";
    header.appendChild(titleEl);

    closeButton = document.createElement("button");
    closeButton.type = "button";
    closeButton.className = "app-alert-close";
    closeButton.setAttribute("aria-label", "Close alert");
    closeButton.innerHTML = "&times;";
    closeButton.addEventListener("click", hide);
    header.appendChild(closeButton);

    messageEl = document.createElement("p");
    messageEl.className = "app-alert-message";

    okButton = document.createElement("button");
    okButton.type = "button";
    okButton.className = "btn btn-primary app-alert-action";
    okButton.textContent = "OK";
    okButton.addEventListener("click", hide);

    dialog.appendChild(header);
    dialog.appendChild(messageEl);
    dialog.appendChild(okButton);
    overlay.appendChild(dialog);

    overlay.addEventListener("click", function (event) {
      if (event.target === overlay) {
        hide();
      }
    });

    document.body.appendChild(overlay);
  }

  function normalizeType(type) {
    return ["success", "error", "warning", "info"].includes(type) ? type : "info";
  }

  function getTitle(type) {
    switch (type) {
      case "success":
        return "Success";
      case "error":
        return "Something went wrong";
      case "warning":
        return "Attention";
      default:
        return "Notice";
    }
  }

  function show(message, options) {
    if (!message) {
      return;
    }

    ensureDialog();

    var settings = options || {};
    var type = normalizeType(settings.type || "info");
    var dialog = overlay.querySelector(".app-alert");

    dialog.className = "app-alert app-alert-" + type;
    titleEl.textContent = settings.title || getTitle(type);
    messageEl.textContent = String(message).trim();
    overlay.hidden = false;
    document.body.classList.add("app-alert-open");
    window.setTimeout(function () {
      okButton.focus();
    }, 0);
  }

  function hide() {
    if (!overlay) {
      return;
    }

    overlay.hidden = true;
    document.body.classList.remove("app-alert-open");
  }

  function hydrateExistingMessages() {
    document.querySelectorAll(".error-message, .success-message").forEach(function (element) {
      if (element.dataset.alertHydrated === "1") {
        return;
      }

      var text = (element.textContent || "").trim();
      if (!text) {
        return;
      }

      var type = element.classList.contains("success-message") ? "success" : "error";
      show(text, { type: type });
      element.dataset.alertHydrated = "1";
      element.style.display = "none";
    });
  }

  function installAlertOverride() {
    window.alert = function (message) {
      if (document.body) {
        show(message, { type: "info" });
        return;
      }

      if (originalAlert) {
        originalAlert(message);
      }
    };
  }

  window.NewPathAlert = {
    show: show,
    hide: hide,
    success: function (message, options) {
      return show(message, Object.assign({}, options, { type: "success" }));
    },
    error: function (message, options) {
      return show(message, Object.assign({}, options, { type: "error" }));
    },
    warning: function (message, options) {
      return show(message, Object.assign({}, options, { type: "warning" }));
    },
    info: function (message, options) {
      return show(message, Object.assign({}, options, { type: "info" }));
    },
  };

  document.addEventListener("DOMContentLoaded", function () {
    ensureDialog();
    hydrateExistingMessages();
    installAlertOverride();
  });
})();
