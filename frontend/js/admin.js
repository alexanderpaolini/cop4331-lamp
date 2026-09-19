document.addEventListener("DOMContentLoaded", function () {

    const userId = localStorage.getItem("userId");
    const firstName = localStorage.getItem("firstName");
    const isAdmin = localStorage.getItem("isAdmin") === "true";

    // User must be logged in
    if (!userId) {
        window.location.href = "login.html";
        return;
    }

    // User must be an administrator
    if (!isAdmin) {
        window.location.href = "mainpage.html";
        return;
    }

    // Display administrator name
    const welcomeUser = document.getElementById("welcomeUser");

    if (welcomeUser) {
        welcomeUser.textContent = `Admin: ${firstName || ""}`;
    }

    // Logout
    const logoutButton = document.getElementById("logoutButton");

    if (logoutButton) {
        logoutButton.addEventListener("click", function () {

            localStorage.clear();

            window.location.href = "login.html";
        });
    }

});
