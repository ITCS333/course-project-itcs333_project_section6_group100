// Validate email
function isValidEmail(email) {
  if (typeof email !== "string") return false;
  return email.includes("@") && email.includes(".");
}

// Validate password
function isValidPassword(password) {
  if (typeof password !== "string") return false;
  return password.length >= 8;
}

// Handle login submit
function handleLogin(event) {
  event.preventDefault(); // required by tests

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const message = document.getElementById("message");

  if (!isValidEmail(email)) {
    message.textContent = "Invalid email";
    return;
  }

  if (!isValidPassword(password)) {
    message.textContent = "Password must be at least 8 characters";
    return;
  }

  message.textContent = "Login successful";
}

// Setup form listener
function setupLoginForm() {
  const form = document.getElementById("loginForm");
  if (form) {
    form.addEventListener("submit", handleLogin);
  }
}

// Run on load
setupLoginForm();
