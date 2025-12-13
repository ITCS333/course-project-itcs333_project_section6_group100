function isValidEmail(email) {
  if (typeof email !== "string") return false;
  return email.includes("@") && email.includes(".");
}

function isValidPassword(password) {
  if (typeof password !== "string") return false;
  return password.length >= 8;
}

function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const message = document.getElementById("message");

  if (!isValidEmail(email)) {
    message.textContent = "Invalid email";
    return;
  }

  if (!isValidPassword(password)) {
    message.textContent = "Invalid password";
    return;
  }

  message.textContent = "Login successful";
}

function setupLoginForm() {
  const form = document.getElementById("loginForm");
  if (form) {
    form.addEventListener("submit", handleLogin);
  }
}

setupLoginForm();

