const API_URL =
    "/api/admin/users.php";

const CONTACTS_API_URL =
    "/api/admin/user-contacts.php";

let users = [];
let sortAscending = true;


// =====================================================
// PAGE SETUP
// =====================================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const userId =
            localStorage.getItem(
                "userId"
            );

        const firstName =
            localStorage.getItem(
                "firstName"
            );

        const isAdmin =
            localStorage.getItem(
                "isAdmin"
            ) === "true";


        if (!userId) {
            window.location.href =
                "login.html";
            return;
        }


        if (!isAdmin) {
            window.location.href =
                "mainpage.html";
            return;
        }


        const welcomeUser =
            document.getElementById(
                "welcomeUser"
            );


        if (welcomeUser) {

            welcomeUser.textContent =
                `Admin: ${firstName || ""}`;
        }


        const logoutButton =
            document.getElementById(
                "logoutButton"
            );


        if (logoutButton) {

            logoutButton.addEventListener(
                "click",
                function () {

                    localStorage.clear();

                    window.location.href =
                        "login.html";
                }
            );
        }


        const searchInput =
            document.getElementById(
                "userSearch"
            );


        if (searchInput) {

            searchInput.addEventListener(
                "input",
                searchUsers
            );
        }


        setupCreateAdmin();
        setupPasswordForm();
        setupEntriesPanel();

        loadUsers();
    }
);


// =====================================================
// LOAD USERS
// =====================================================

async function loadUsers() {

    const loadingMessage =
        document.getElementById(
            "loadingMessage"
        );


    try {

        const response =
            await fetch(API_URL);


        const data =
            await response.json();


        if (!response.ok) {

            throw new Error(
                data.error ||
                "Failed to load users"
            );
        }


        // Keep current admin in list too,
        // because professor says admins can
        // manage other admins and users.
        users =
            data.users || [];


        if (loadingMessage) {
            loadingMessage.remove();
        }


        displayUsers(users);


    } catch (error) {

        console.error(
            "Error loading users:",
            error
        );


        if (loadingMessage) {

            loadingMessage.textContent =
                "Unable to load users.";
        }
    }
}


// =====================================================
// DISPLAY USERS
// =====================================================

function displayUsers(userList) {

    const userListElement =
        document.getElementById(
            "userList"
        );


    if (!userListElement) {
        return;
    }


    userListElement.innerHTML = "";


    if (userList.length === 0) {

        userListElement.innerHTML =
            "<p>No users found.</p>";

        return;
    }


    const table =
        document.createElement(
            "table"
        );


    table.className =
        "user-table";


    table.innerHTML = `

        <thead>

            <tr>

                <th onclick="sortUsers('ID')">
                    ID
                </th>

                <th onclick="sortUsers('FirstName')">
                    First Name
                </th>

                <th onclick="sortUsers('LastName')">
                    Last Name
                </th>

                <th onclick="sortUsers('Login')">
                    Username
                </th>

                <th onclick="sortUsers('IsAdmin')">
                    Role
                </th>

                <th onclick="sortUsers('IsDisabled')">
                    Status
                </th>

                <th>
                    Actions
                </th>

            </tr>

        </thead>

        <tbody id="userTableBody">
        </tbody>
    `;


    userListElement.appendChild(
        table
    );


    const tableBody =
        document.getElementById(
            "userTableBody"
        );


    userList.forEach(
        function (user) {

            const row =
                document.createElement(
                    "tr"
                );


            const isAdmin =
                Number(
                    user.IsAdmin
                ) === 1;


            const isDisabled =
                Number(
                    user.IsDisabled
                ) === 1;


            const roleText =
                isAdmin
                    ? "Admin"
                    : "User";


            const statusText =
                isDisabled
                    ? "Disabled"
                    : "Active";


            const disableText =
                isDisabled
                    ? "Enable"
                    : "Disable";


            row.innerHTML = `

                <td>
                    ${user.ID}
                </td>

                <td>
                    ${escapeHTML(
                        user.FirstName
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        user.LastName
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        user.Login
                    )}
                </td>

                <td>
                    ${roleText}
                </td>

                <td>
                    ${statusText}
                </td>

                <td>

                    <button
                        type="button"
                        class="edit-btn"
                        onclick="viewEntries(${user.ID})"
                    >
                        View Entries
                    </button>

                    <button
                        type="button"
                        class="edit-btn"
                        onclick="openPasswordForm(${user.ID})"
                    >
                        Change Password
                    </button>

                    <button
                        type="button"
                        class="${
                            isDisabled
                                ? "edit-btn"
                                : "delete-btn"
                        }"
                        onclick="toggleDisabled(${user.ID})"
                    >
                        ${disableText}
                    </button>

                </td>
            `;


            tableBody.appendChild(
                row
            );
        }
    );
}


// =====================================================
// SEARCH USERS
// =====================================================

function searchUsers() {

    const searchInput =
        document.getElementById(
            "userSearch"
        );


    if (!searchInput) {
        return;
    }


    const searchTerm =
        searchInput.value
            .toLowerCase()
            .trim();


    const filteredUsers =
        users.filter(
            function (user) {

                const firstName =
                    String(
                        user.FirstName || ""
                    ).toLowerCase();

                const lastName =
                    String(
                        user.LastName || ""
                    ).toLowerCase();

                const login =
                    String(
                        user.Login || ""
                    ).toLowerCase();

                const role =
                    Number(
                        user.IsAdmin
                    ) === 1
                        ? "admin"
                        : "user";

                const status =
                    Number(
                        user.IsDisabled
                    ) === 1
                        ? "disabled"
                        : "active";


                return (
                    firstName.includes(
                        searchTerm
                    ) ||
                    lastName.includes(
                        searchTerm
                    ) ||
                    login.includes(
                        searchTerm
                    ) ||
                    role.includes(
                        searchTerm
                    ) ||
                    status.includes(
                        searchTerm
                    )
                );
            }
        );


    displayUsers(
        filteredUsers
    );
}


// =====================================================
// SORT USERS
// =====================================================

function sortUsers(field) {

    const sortedUsers =
        [...users];


    sortedUsers.sort(
        function (a, b) {

            let valueA =
                a[field];

            let valueB =
                b[field];


            if (
                field === "ID" ||
                field === "IsAdmin" ||
                field === "IsDisabled"
            ) {

                valueA =
                    Number(valueA);

                valueB =
                    Number(valueB);


                return sortAscending
                    ? valueA - valueB
                    : valueB - valueA;
            }


            valueA =
                String(
                    valueA || ""
                ).toLowerCase();

            valueB =
                String(
                    valueB || ""
                ).toLowerCase();


            return sortAscending
                ? valueA.localeCompare(
                    valueB
                )
                : valueB.localeCompare(
                    valueA
                );
        }
    );


    sortAscending =
        !sortAscending;


    displayUsers(
        sortedUsers
    );
}


// =====================================================
// DISABLE / ENABLE
// =====================================================

async function toggleDisabled(
    userId
) {

    const user =
        users.find(
            function (user) {

                return (
                    Number(user.ID) ===
                    Number(userId)
                );
            }
        );


    if (!user) {

        alert(
            "User not found."
        );

        return;
    }


    const currentlyDisabled =
        Number(
            user.IsDisabled
        ) === 1;


    const newDisabled =
        currentlyDisabled
            ? 0
            : 1;


    const actionText =
        newDisabled === 1
            ? "Disable"
            : "Enable";


    const confirmed =
        confirm(
            `${actionText} ${user.FirstName} ${user.LastName}?`
        );


    if (!confirmed) {
        return;
    }


    try {

        const response =
            await fetch(
                API_URL,
                {
                    method:
                        "PUT",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify({
                            id:
                                Number(userId),

                            action:
                                "disabled",

                            isDisabled:
                                newDisabled
                        })
                }
            );


        const data =
            await response.json();


        if (!response.ok) {

            throw new Error(
                data.error ||
                "Unable to update user status"
            );
        }


        await loadUsers();


    } catch (error) {

        console.error(
            "Error updating user status:",
            error
        );

        alert(
            error.message
        );
    }
}


// =====================================================
// CREATE ADMIN
// =====================================================

function setupCreateAdmin() {

    const openButton =
        document.getElementById(
            "openCreateAdminButton"
        );

    const closeButton =
        document.getElementById(
            "closeCreateAdminButton"
        );

    const cancelButton =
        document.getElementById(
            "cancelCreateAdminButton"
        );

    const section =
        document.getElementById(
            "createAdminSection"
        );

    const form =
        document.getElementById(
            "createAdminForm"
        );


    if (openButton) {

        openButton.addEventListener(
            "click",
            function () {

                section.style.display =
                    "block";

                form.reset();
            }
        );
    }


    function closeForm() {

        section.style.display =
            "none";

        form.reset();

        document.getElementById(
            "createAdminError"
        ).textContent = "";
    }


    if (closeButton) {
        closeButton.addEventListener(
            "click",
            closeForm
        );
    }


    if (cancelButton) {
        cancelButton.addEventListener(
            "click",
            closeForm
        );
    }


    if (form) {

        form.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();


                const error =
                    document.getElementById(
                        "createAdminError"
                    );


                error.textContent = "";


                const firstName =
                    document.getElementById(
                        "adminFirstName"
                    ).value.trim();


                const lastName =
                    document.getElementById(
                        "adminLastName"
                    ).value.trim();


                const login =
                    document.getElementById(
                        "adminLogin"
                    ).value.trim();


                const password =
                    document.getElementById(
                        "adminPassword"
                    ).value;


                try {

                    const response =
                        await fetch(
                            API_URL,
                            {
                                method:
                                    "POST",

                                headers: {
                                    "Content-Type":
                                        "application/json"
                                },

                                body:
                                    JSON.stringify({
                                        firstName:
                                            firstName,

                                        lastName:
                                            lastName,

                                        login:
                                            login,

                                        password:
                                            password
                                    })
                            }
                        );


                    const data =
                        await response.json();


                    if (!response.ok) {

                        error.textContent =
                            data.error ||
                            "Unable to create administrator.";

                        return;
                    }


                    closeForm();

                    await loadUsers();


                } catch (exception) {

                    console.error(
                        exception
                    );

                    error.textContent =
                        "Unable to connect to the server.";
                }
            }
        );
    }
}


// =====================================================
// PASSWORD CHANGE
// =====================================================

function setupPasswordForm() {

    const closeButton =
        document.getElementById(
            "closePasswordButton"
        );

    const cancelButton =
        document.getElementById(
            "cancelPasswordButton"
        );

    const form =
        document.getElementById(
            "passwordForm"
        );


    function closeForm() {

        document.getElementById(
            "passwordSection"
        ).style.display =
            "none";

        form.reset();

        document.getElementById(
            "passwordError"
        ).textContent =
            "";
    }


    if (closeButton) {
        closeButton.addEventListener(
            "click",
            closeForm
        );
    }


    if (cancelButton) {
        cancelButton.addEventListener(
            "click",
            closeForm
        );
    }


    if (form) {

        form.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();


                const error =
                    document.getElementById(
                        "passwordError"
                    );


                error.textContent =
                    "";


                const id =
                    Number(
                        document.getElementById(
                            "passwordUserId"
                        ).value
                    );


                const password =
                    document.getElementById(
                        "newPassword"
                    ).value;


                const confirmPassword =
                    document.getElementById(
                        "confirmPassword"
                    ).value;


                if (
                    password !==
                    confirmPassword
                ) {

                    error.textContent =
                        "Passwords do not match.";

                    return;
                }


                try {

                    const response =
                        await fetch(
                            API_URL,
                            {
                                method:
                                    "PUT",

                                headers: {
                                    "Content-Type":
                                        "application/json"
                                },

                                body:
                                    JSON.stringify({
                                        id:
                                            id,

                                        action:
                                            "password",

                                        password:
                                            password
                                    })
                            }
                        );


                    const data =
                        await response.json();


                    if (!response.ok) {

                        error.textContent =
                            data.error ||
                            "Unable to update password.";

                        return;
                    }


                    closeForm();

                    alert(
                        "Password updated successfully."
                    );


                } catch (exception) {

                    console.error(
                        exception
                    );

                    error.textContent =
                        "Unable to connect to the server.";
                }
            }
        );
    }
}


function openPasswordForm(
    userId
) {

    const user =
        users.find(
            function (user) {

                return (
                    Number(user.ID) ===
                    Number(userId)
                );
            }
        );


    if (!user) {
        return;
    }


    const section =
        document.getElementById(
            "passwordSection"
        );


    document.getElementById(
        "passwordUserId"
    ).value =
        user.ID;


    document.getElementById(
        "passwordUserLabel"
    ).textContent =
        `Change password for ${user.FirstName} ${user.LastName} (${user.Login}).`;


    document.getElementById(
        "passwordForm"
    ).reset();


    document.getElementById(
        "passwordUserId"
    ).value =
        user.ID;


    section.style.display =
        "block";


    section.scrollIntoView({
        behavior:
            "smooth",

        block:
            "start"
    });
}


// =====================================================
// USER ENTRIES
// =====================================================

function setupEntriesPanel() {

    const closeButton =
        document.getElementById(
            "closeEntriesButton"
        );


    if (closeButton) {

        closeButton.addEventListener(
            "click",
            function () {

                document.getElementById(
                    "entriesSection"
                ).style.display =
                    "none";
            }
        );
    }
}


async function viewEntries(
    userId
) {

    const user =
        users.find(
            function (user) {

                return (
                    Number(user.ID) ===
                    Number(userId)
                );
            }
        );


    if (!user) {
        return;
    }


    const section =
        document.getElementById(
            "entriesSection"
        );

    const title =
        document.getElementById(
            "entriesTitle"
        );

    const list =
        document.getElementById(
            "entriesList"
        );


    title.textContent =
        `Entries for ${user.FirstName} ${user.LastName}`;


    list.innerHTML =
        "<p>Loading entries...</p>";


    section.style.display =
        "block";


    try {

        const response =
            await fetch(
                `${CONTACTS_API_URL}?userId=${userId}`
            );


        const data =
            await response.json();


        if (!response.ok) {

            throw new Error(
                data.error ||
                "Unable to load entries"
            );
        }


        displayEntries(
            data.entries || []
        );


    } catch (error) {

        console.error(
            error
        );

        list.innerHTML =
            `<p class="error-message">${escapeHTML(error.message)}</p>`;
    }


    section.scrollIntoView({
        behavior:
            "smooth",

        block:
            "start"
    });
}


function displayEntries(
    entries
) {

    const list =
        document.getElementById(
            "entriesList"
        );


    if (
        !entries ||
        entries.length === 0
    ) {

        list.innerHTML =
            "<p>No entries found for this user.</p>";

        return;
    }


    const table =
        document.createElement(
            "table"
        );


    table.className =
        "user-table";


    table.innerHTML = `

        <thead>

            <tr>
                <th>Type</th>
                <th>Username</th>
                <th>Name</th>
                <th>Class</th>
                <th>Rank</th>
                <th>Status</th>
                <th>Game Mode</th>
            </tr>

        </thead>

        <tbody>
        </tbody>
    `;


    const body =
        table.querySelector(
            "tbody"
        );


    entries.forEach(
        function (entry) {

            const row =
                document.createElement(
                    "tr"
                );


            const name =
                `${entry.FirstName || ""} ${entry.LastName || ""}`.trim();


            const type =
                entry.SourceType ===
                    "manual"
                    ? "Manual"
                    : "Registered";


            const status =
                Number(
                    entry.LookingFor
                ) === 1
                    ? "Looking"
                    : "Not Looking";


            row.innerHTML = `

                <td>
                    ${type}
                </td>

                <td>
                    ${escapeHTML(
                        entry.Login ||
                        "—"
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        name
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        entry.MercenaryClass ||
                        "No Class"
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        getRankName(
                            entry.MercenaryRank
                        )
                    )}
                </td>

                <td>
                    ${status}
                </td>

                <td>
                    ${escapeHTML(
                        entry.GameMode ||
                        "—"
                    )}
                </td>
            `;


            body.appendChild(
                row
            );
        }
    );


    list.innerHTML =
        "";

    list.appendChild(
        table
    );
}


// =====================================================
// RANK NAME
// =====================================================

function getRankName(rank) {

    const ranks = {

        1: "Mercenary I",
        2: "Mercenary II",
        3: "Mercenary III",

        4: "Contract Killer I",
        5: "Contract Killer II",
        6: "Contract Killer III",

        7: "Executioner I",
        8: "Executioner II",
        9: "Executioner III",

        10: "Expert Assassin I",
        11: "Expert Assassin II",
        12: "Expert Assassin III",

        13: "Death Merchant"
    };


    return (
        ranks[
            Number(rank)
        ] ||
        "Unranked"
    );
}


// =====================================================
// ESCAPE HTML
// =====================================================

function escapeHTML(value) {

    const element =
        document.createElement(
            "div"
        );

    element.textContent =
        value ?? "";

    return element.innerHTML;
}
