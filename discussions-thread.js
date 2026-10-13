document.addEventListener("DOMContentLoaded", async () => {
  // admins can read discussions but not post, so swap the reply form for a note
  const user = await CodeMateSession.getCurrentUser().catch(() => null);
  if (!user || user.role !== "admin") return;

  const composer = document.querySelector(".reply-composer-card");
  if (composer) {
    composer.innerHTML = "<h4>View-only</h4><p>Admins can read discussions but can't post replies.</p>";
  }
});
