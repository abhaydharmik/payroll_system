// Show confirmation before delete
function confirmDelete() {
  return confirm("Are you sure you want to delete this record?");
}

// Flash message fade out
window.onload = function () {
  let msg = document.querySelector(".flash-message");
  if (msg) {
    setTimeout(() => {
      msg.style.display = "none";
    }, 3000);
  }
};

// Toggle password visibility
function togglePassword(id) {
  let input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
  } else {
    input.type = "password";
  }
}

// Sidebar Toggle Logic 'sidebar.php'
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  const toggleBtn = document.getElementById("sidebarToggle");

  // Check saved state
  if (sessionStorage.getItem("sidebarOpen") === "true") {
    sidebar.classList.remove("-translate-x-full");
    overlay.classList.remove("hidden");
  }

  // Toggle sidebar
  toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("-translate-x-full");
    overlay.classList.toggle("hidden");
    sessionStorage.setItem(
      "sidebarOpen",
      !sidebar.classList.contains("-translate-x-full")
    );
  });

  // Overlay click
  overlay.addEventListener("click", () => {
    sidebar.classList.add("-translate-x-full");
    overlay.classList.add("hidden");
    sessionStorage.setItem("sidebarOpen", false);
  });

  // Reset sidebar on desktop
  window.addEventListener("resize", () => {
    if (window.innerWidth >= 768) {
      sidebar.classList.remove("-translate-x-full");
      overlay.classList.add("hidden");
    }
  });
});
