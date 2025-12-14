/**
 * Display a message to the user
 * @param {string} message
 * @param {boolean} isError
 */
function displayMessage(message, isError) {
  const msg = document.getElementById("message");

  // Prevent errors if element does not exist (for tests)
  if (!msg) return;

  msg.textContent = message || "";
  msg.style.color = isError ? "red" : "green";
}

/**
 * Handle login form submission
 * @param {Event} event
 */
function handleLogin(event) {
  // Prevent default form submission
  event.preventDefault();

  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  const email = emailInput ? emailInput.value.trim() : "";
  const password = passwordInput ? passwordInput.value : "";

  // Basic validation
  if (!email || !password) {
    displayMessage("All fields are required", true);
    return;
  }

  // Send login request to backend
  fetch("api/login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      displayMessage(data.message, !data.success);
    })
    .catch(() => {
      displayMessage("Server error", true);
    });
}

/**
 * Attach submit event listener to the form
 */
function setupLoginForm() {
  const form =
    document.getElementById("loginForm") ||
    document.querySelector("form");

  if (!form) return;

  form.addEventListener("submit", handleLogin);
}

// Initialize when DOM is ready
if (typeof document !== "undefined") {
  document.addEventListener("DOMContentLoaded", setupLoginForm);
}
