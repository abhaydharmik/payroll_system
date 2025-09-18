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
