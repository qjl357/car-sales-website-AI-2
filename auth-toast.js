/**
 * Shared auth success toast (English copy, site-themed overlay).
 * - AuthFlowToast.show(line1, line2, durationMs, onClose)
 * - home.html: auto when URL has ?welcome=1 (after login)
 */
(function () {
  function escHtml(s) {
    var d = document.createElement("div");
    d.textContent = String(s);
    return d.innerHTML;
  }

  function show(line1, line2, durationMs, onClose) {
    var existing = document.getElementById("auth-flow-overlay");
    if (existing) {
      existing.remove();
    }
    var overlay = document.createElement("div");
    overlay.id = "auth-flow-overlay";
    overlay.className = "auth-flow-overlay";
    overlay.setAttribute("aria-hidden", "false");
    overlay.innerHTML =
      '<div class="auth-flow-toast" role="alert">' +
      '<p class="auth-flow-toast-line1">' +
      escHtml(line1) +
      "</p>" +
      '<p class="auth-flow-toast-line2">' +
      escHtml(line2) +
      "</p></div>";
    document.body.appendChild(overlay);
    window.setTimeout(function () {
      overlay.remove();
      if (typeof onClose === "function") {
        onClose();
      }
    }, durationMs);
  }

  window.AuthFlowToast = { show: show };

  function stripWelcomeParam() {
    try {
      var u = new URL(window.location.href);
      if (u.searchParams.get("welcome") !== "1") {
        return;
      }
      u.searchParams.delete("welcome");
      var qs = u.searchParams.toString();
      window.history.replaceState({}, "", u.pathname + (qs ? "?" + qs : ""));
    } catch (e) {
      /* ignore */
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    try {
      var u = new URL(window.location.href);
      if (u.searchParams.get("welcome") === "1") {
        show(
          "Signed in successfully.",
          "Welcome back. You are on the home page.",
          1000,
          stripWelcomeParam
        );
      }
    } catch (e) {
      /* ignore */
    }
  });
})();
