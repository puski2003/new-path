function showSection(id) {
  document.querySelectorAll(".toggle-section").forEach(function (section) {
    section.classList.remove("active-section");
  });

  document.querySelectorAll(".toggle-button").forEach(function (button) {
    button.classList.remove("active-button");
  });

  var section = document.getElementById(id);
  if (section) {
    section.classList.add("active-section");
  }

  var buttonMap = {
    "tab-payments": "btn-payments",
    "tab-payouts":  "btn-payouts",
  };

  var btnId = buttonMap[id];
  if (btnId) {
    var btn = document.getElementById(btnId);
    if (btn) btn.classList.add("active-button");
  }
}
