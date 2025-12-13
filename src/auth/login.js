function displayMessage(message, isError) {
    const msg = document.getElementById("message");
    msg.textContent = message;
    msg.style.color = isError ? "red" : "green";
}

function handleLogin(event) {
    event.preventDefault();

    const email = document.querySelector('input[type="email"]').value;
    const password = document.querySelector('input[type="password"]').value;

    if (!email || !password) {
        displayMessage("All fields required", true);
        return;
    }

    fetch("api/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        displayMessage(data.message, !data.success);
    })
    .catch(() => {
        displayMessage("Server error", true);
    });
}

function setupLoginForm() {
    const form = document.querySelector("form");
    form.addEventListener("submit", handleLogin);
}

setupLoginForm();
