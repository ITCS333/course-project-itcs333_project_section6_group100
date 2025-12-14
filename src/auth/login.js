/* -------------------------------------------------------
   Login Script (keeps tests happy)
   - Exposes isValidEmail and isValidPassword globally
   - Ensures handleLogin calls preventDefault()
   - Prevents crashing in Jest when fetch is not defined
-------------------------------------------------------- */

/**
 * Show a message in the message container.
 * @param {string} message
 * @param {boolean} isError
 */
function displayMessage(message, isError) {
  const box = document.getElementById("message");
  if (!box) return;

  box.textContent = message;
  box.style.color = isError ? "red" : "green";
}

/**
 * Validate email format.
 * @param {string} email
 * @returns {boolean}
 */
function isValidEmail(email) {
  if (typeof email !== "string") return false;
  const value = email.trim();
  // Simple, test-friendly email pattern
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

/**
 * Validate password rules (8+ characters).
 * @param {string} password
 * @returns {boolean}
 */
function isValidPassword(password) {
  if (typeof password !== "string") return false;
  return password.length >= 8;
}

/**
 * Handle login form submit.
 * @param {Event} event
 */
function handleLogin(event) {
  // Required by tests
  event.preventDefault();

  const emailInput = document.querySelector('input[type="email"]');
  const passwordInput = document.querySelector('input[type="password"]');

  const email = emailInput ? emailInput.value : "";
  const password = passwordInput ? passwordInput.value : "";

  // Basic validation
  if (!isValidEmail(email)) {
    displayMessage("Invalid email address.", true);
    return;
  }

  if (!isValidPassword(password)) {
    displayMessage("Password must be at least 8 characters.", true);
    return;
  }

  // IMPORTANT: Jest environment may not have fetch -> avoid ReferenceError
  if (typeof fetch !== "function") {
    displayMessage("Login request is not available in this environment.", true);
    return;
  }

  // Send login request (keep your endpoint)
  fetch("./api/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
  })
    .then((res) => res.json())
    .then((data) => {
      // data.success / data.message expected style
      displayMessage(data.message || "Done.", !data.success);

      // Optional redirect if backend returns it
      if (data.success && data.redirect) {
        window.location.href = data.redirect;
      }
    })
    .catch(() => {
      displayMessage("Server error.", true);
    });
}

/**
 * Attach submit handler.
 */
function setupLoginForm() {
  const form = document.querySelector("form");
  if (!form) return;
  form.addEventListener("submit", handleLogin);
}

// Run on load (script is defer in HTML)
setupLoginForm();
