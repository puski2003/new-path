class DashboardPage {
  constructor() {
    this.tasksContainer = document.querySelector(".daily-tasks");
    this.pagination = {
      wrapper: document.getElementById("dashTasksPagination"),
      prev: document.getElementById("dashTasksPrev"),
      next: document.getElementById("dashTasksNext"),
      info: document.getElementById("dashTasksPageInfo"),
    };
  }

  init() {
    this.initTaskCompletion();
    this.initTaskPagination();
  }

  initTaskCompletion() {
    if (!this.tasksContainer) {
      return;
    }

    this.tasksContainer.addEventListener("submit", async (event) => {
      const form = event.target.closest(".task-complete-form");
      if (!form) return;
      event.preventDefault();

      const taskItem = form.closest(".task-item");
      if (!taskItem || taskItem.dataset.loading === "1") return;

      const taskId = Number(new FormData(form).get("taskId") || 0);
      if (!taskId) return;

      taskItem.dataset.loading = "1";

      const savedFormHtml = form.outerHTML;
      const checkbox = taskItem.querySelector(".task-checkbox");
      const taskText = taskItem.querySelector(".task-text");

      try {
        const response = await fetch(form.action, {
          method: "POST",
          body: new FormData(form),
          redirect: "manual",
        });

        if (response.type === "opaqueredirect" || response.ok) {
          taskItem.classList.add("task-item--done");
          if (checkbox) {
            checkbox.classList.add("completed");
            checkbox.innerHTML = '<i data-lucide="check" style="width:14px;height:14px;" color="white"></i>';
          }
          if (taskText) taskText.classList.add("completed");
          const doneLabel = document.createElement("span");
          doneLabel.className = "task-done-label";
          doneLabel.textContent = "Done";
          form.replaceWith(doneLabel);
          if (typeof lucide !== "undefined") lucide.createIcons();

          showUndoToast("Task marked complete.", 5000, async () => {
            await this.uncompleteTask(taskId, taskItem, savedFormHtml);
          });
        } else {
          this.showError("Failed to complete task.");
        }
      } catch (error) {
        this.showError(error.message || "Failed to update task.");
      } finally {
        delete taskItem.dataset.loading;
      }
    });
  }

  async uncompleteTask(taskId, taskItem, savedFormHtml) {
    const formData = new URLSearchParams();
    formData.append("taskId", String(taskId));

    try {
      const response = await fetch("/user/recovery/task/uncomplete", {
        method: "POST",
        body: formData,
        redirect: "manual",
      });

      if (response.type === "opaqueredirect" || response.ok) {
        taskItem.classList.remove("task-item--done");
        const checkbox = taskItem.querySelector(".task-checkbox");
        if (checkbox) {
          checkbox.classList.remove("completed");
          checkbox.innerHTML = "";
        }
        const taskText = taskItem.querySelector(".task-text");
        if (taskText) taskText.classList.remove("completed");
        const currentDoneLabel = taskItem.querySelector(".task-done-label");
        if (currentDoneLabel) {
          const temp = document.createElement("div");
          temp.innerHTML = savedFormHtml;
          currentDoneLabel.replaceWith(temp.firstChild);
        }
        if (typeof lucide !== "undefined") lucide.createIcons();
      } else {
        this.showError("Could not undo. Please refresh.");
      }
    } catch (err) {
      this.showError(err.message || "Could not undo. Please refresh.");
    }
  }

  initTaskPagination() {
    const items = this.tasksContainer
      ? Array.from(this.tasksContainer.querySelectorAll(".task-item"))
      : [];
    const tasksPerPage = 4;

    if (!this.pagination.wrapper || items.length <= tasksPerPage) {
      return;
    }

    let currentPage = 1;
    const totalPages = Math.ceil(items.length / tasksPerPage);

    const render = () => {
      const start = (currentPage - 1) * tasksPerPage;
      const end = start + tasksPerPage;

      items.forEach((item, index) => {
        item.style.display = index >= start && index < end ? "" : "none";
      });

      this.pagination.info.textContent = currentPage + " / " + totalPages;
      this.pagination.prev.disabled = currentPage === 1;
      this.pagination.next.disabled = currentPage === totalPages;
    };

    this.pagination.wrapper.style.display = "flex";

    this.pagination.prev.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage -= 1;
        render();
      }
    });

    this.pagination.next.addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage += 1;
        render();
      }
    });

    render();
  }

  showError(message) {
    if (window.NewPathAlert && typeof window.NewPathAlert.show === "function") {
      window.NewPathAlert.show(message, { type: "error" });
      return;
    }

    window.alert(message);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  new DashboardPage().init();
});

function showUndoToast(message, duration, onUndo) {
  var existing = document.querySelector(".undo-task-toast");
  if (existing) existing.remove();

  if (!document.getElementById("undo-toast-style")) {
    var style = document.createElement("style");
    style.id = "undo-toast-style";
    style.textContent =
      "@keyframes undoToastIn{from{transform:translate(-50%,20px);opacity:0}to{transform:translate(-50%,0);opacity:1}}" +
      "@keyframes undoToastOut{from{transform:translate(-50%,0);opacity:1}to{transform:translate(-50%,20px);opacity:0}}";
    document.head.appendChild(style);
  }

  var toast = document.createElement("div");
  toast.className = "undo-task-toast";
  toast.innerHTML =
    '<span style="flex:1;font-size:14px;font-weight:500;">' + escapeHtml(message) + "</span>" +
    '<button type="button" class="undo-task-toast__btn">Undo</button>' +
    '<div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:rgba(255,255,255,0.25);border-radius:0 0 10px 10px;overflow:hidden;">' +
    '  <div class="undo-task-toast__bar" style="height:100%;width:100%;background:rgba(255,255,255,0.7);transition:width ' + duration + "ms linear;"></div>" +
    "</div>";

  toast.style.cssText = [
    "position:fixed",
    "bottom:28px",
    "left:50%",
    "transform:translateX(-50%)",
    "display:flex",
    "align-items:center",
    "gap:12px",
    "padding:12px 18px 18px",
    "border-radius:10px",
    "min-width:260px",
    "max-width:380px",
    "box-shadow:0 10px 30px rgba(0,0,0,0.2)",
    "z-index:9999",
    "background:#10b981",
    "color:#fff",
    "animation:undoToastIn 0.25s ease",
  ].join(";");

  var undoBtn = toast.querySelector(".undo-task-toast__btn");
  undoBtn.style.cssText = [
    "background:rgba(255,255,255,0.2)",
    "border:1px solid rgba(255,255,255,0.5)",
    "color:#fff",
    "padding:5px 14px",
    "border-radius:6px",
    "font-size:13px",
    "font-weight:600",
    "cursor:pointer",
    "flex-shrink:0",
    "transition:background 0.15s",
  ].join(";");

  document.body.appendChild(toast);

  var fill = toast.querySelector(".undo-task-toast__bar");
  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      fill.style.width = "0%";
    });
  });

  var dismissed = false;

  function dismiss() {
    if (dismissed) return;
    dismissed = true;
    clearTimeout(timer);
    toast.style.animation = "undoToastOut 0.25s ease forwards";
    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 250);
  }

  var timer = setTimeout(dismiss, duration);

  undoBtn.addEventListener("click", function () {
    dismiss();
    onUndo();
  });
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}