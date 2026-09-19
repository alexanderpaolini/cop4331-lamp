document.getElementById("loginForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const login = document.getElementById("login").value.trim();
    const password = document.getElementById("password").value;

    const response = await fetch(`${API_BASE_URL}/auth/login.php`, {
        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({
            login: login,
            password: password
        })
    });

    const data = await response.json();

    if (response.ok) {

        // Save user information
        localStorage.setItem("userId", data.user.id);
        localStorage.setItem("firstName", data.user.firstName);
        localStorage.setItem("lastName", data.user.lastName);
        localStorage.setItem("login", data.user.login);
	localStorage.setItem("isAdmin", data.user.isAdmin);

	if (data.user.isAdmin === true) {
        // Send user to main page
            window.location.href = "admin.html";
	} else {
	    window.location.href = "mainpage.html";
	}


    } else {

        // Show login error
        document.getElementById("error").textContent = data.error;
    }
});
