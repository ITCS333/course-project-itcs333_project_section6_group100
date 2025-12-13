function isValidEmail(email) {
  return typeof email === "string" && email.includes("@") && email.includes(".");
}

function isValidPassword(password) {
  return typeof password === "string" && password.length >= 8;
}

function showMessage(text) {
  const message = document.getElementById("message");
  message.textContent = text;
}

function handleLogin(event) {
  event.preventDefault();

  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  const email = emailInput.value;
  const password = passwordInput.value;

  if (!isValidEmail(email)) {
    showMessage("Please enter a valid email address.");
    return false;
  }

  if (!isValidPassword(password)) {
    showMessage("Password must be at least 8 characters long.");
    return false;
  }

  showMessage("Login successful.");
  return true;
}

function setupLoginForm() {
  const form = document.getElementById("loginForm");
  if (form) {
    form.addEventListener("submit", handleLogin);
  }
}

setupLoginForm();
