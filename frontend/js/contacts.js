// Merc Mann Contact Dashboard

document.addEventListener("DOMContentLoaded", function () {

    // Get logged-in user information
    const userId = localStorage.getItem("userId");
    const firstName = localStorage.getItem("firstName");
    const lastName = localStorage.getItem("lastName");
    const isAdmin = localStorage.getItem("isAdmin") === "true";

    // If no user is logged in, return to login page
    if (!userId) {
        window.location.href = "login.html";
        return;
    }

    // Display logged-in user's name
    const welcomeUser = document.getElementById("welcomeUser");

    if (welcomeUser) {
        welcomeUser.textContent = `Welcome, ${firstName || ""}`;
    }

    // Logout
    const logoutButton = document.getElementById("logoutButton");

    if (logoutButton) {
        logoutButton.addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "login.html";
        });
    }

    // Add Contact modal
    const addContactButton = document.getElementById("addContactButton");
    const contactModal = document.getElementById("contactModal");
    const closeModalButton = document.getElementById("closeModalButton");

    if (addContactButton && contactModal) {
        addContactButton.addEventListener("click", function () {
            contactModal.classList.add("show");
        });
    }

    if (closeModalButton && contactModal) {
        closeModalButton.addEventListener("click", function () {
            contactModal.classList.remove("show");
        });
    }

    // Close modal when clicking outside of it
    if (contactModal) {
        contactModal.addEventListener("click", function (event) {
            if (event.target === contactModal) {
                contactModal.classList.remove("show");
            }
        });
    }

    // Search contacts
    const searchInput = document.getElementById("search");

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            filterContacts();
        });
    }

    // Filter contacts by TF2 class
    const classFilter = document.getElementById("classFilter");

    if (classFilter) {
        classFilter.addEventListener("change", function () {
            filterContacts();
        });
    }

    function filterContacts() {
        const searchValue = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

        const classValue = classFilter
            ? classFilter.value.toLowerCase()
            : "";

        const contacts = document.querySelectorAll(".contact-card");

        contacts.forEach(function (contact) {
            const name = (contact.dataset.name || "").toLowerCase();
            const mercClass = (contact.dataset.class || "").toLowerCase();

            const matchesSearch =
                searchValue === "" || name.includes(searchValue);

            const matchesClass =
                classValue === "" || mercClass === classValue;

            contact.style.display =
                matchesSearch && matchesClass ? "" : "none";
        });
    }

    // Contact form
    const contactForm = document.getElementById("contactForm");

    if (contactForm) {
        contactForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const contactError = document.getElementById("contactError");

            if (contactError) {
                contactError.textContent =
                    "Contact API connection is not implemented yet.";
            }
        });
    }

    // Admin status is available for future admin controls
    if (isAdmin) {
        console.log("Admin user logged in.");
    }
});
