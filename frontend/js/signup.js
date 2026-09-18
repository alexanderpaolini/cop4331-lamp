document.getElementById("registerForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const login = document.getElementById("login").value.trim();
    const password = document.getElementById("password").value;

    const response = await fetch(`${API_BASE_URL}/Register.php`, {
        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({
            firstName: firstName,
            lastName: lastName,
            login: login,
            password: password
        })
    });

    const data = await response.json();

    if (response.ok) {

        alert("Account created successfully! You can now log in.");

        window.location.href = "login.html";

    } else {

        document.getElementById("error").textContent = data.error;
    }
});