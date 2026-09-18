# Merc Mann Frontend

Frontend for the **Merc Mann** contact management application.

The frontend is built using **HTML, CSS, and JavaScript** and communicates with the PHP backend through the `/api` directory.

---

## Frontend Structure

```text
frontend/
├── login.html
├── register.html
├── mainpage.html
│
├── css/
│   └── styles.css
│
└── js/
    ├── config.js
    ├── login.js
    └── signup.js
```

---

# Pages

## `login.html`

The main login page for existing users.

### User Inputs

The user enters:

- Username
- Password

The form has the ID:

```text
loginForm
```

The input IDs are:

```text
login
password
```

Any error returned by the API is displayed in:

```text
error
```

### Login Process

When the user submits the form, `login.js` sends the username and password to:

```text
/api/Login.php
```

If the login is successful, the returned user information is stored in the browser using `localStorage`.

The user is then redirected to:

```text
mainpage.html
```

If the login fails, the error returned by the API is displayed on the login page.

The page also contains a link to `register.html` for users who do not already have an account.

---

## `register.html`

The registration page allows new users to create an account.

### User Inputs

The user enters:

- First name
- Last name
- Username
- Password

The form has the ID:

```text
registerForm
```

The input IDs are:

```text
firstName
lastName
login
password
```

Any registration error is displayed in:

```text
error
```

### Registration Process

When the form is submitted, `signup.js` sends the registration information to:

```text
/api/Register.php
```

If registration succeeds:

1. A success message is displayed.
2. The user clicks OK.
3. The user is redirected to `login.html`.
4. The user can log in using the newly created account.

If registration fails, the error returned by the API is displayed on the page.

The page also contains a link back to `login.html` for users who already have an account.

---

## `mainpage.html`

The main application page displayed after a successful login.

This page is intended to contain the user's TF2-themed contact manager.

### Current Layout

The page currently includes:

- Merc Mann header
- Logged-in user area
- Log Out button
- My Mercenaries section
- Add Contact button
- Contact search
- TF2 class filter
- Contact list
- Empty contact list message
- Add Contact modal/form

### Contact Search

The search input has the ID:

```text
search
```

It will be used to search the user's contacts by name.

### Class Filter

The class filter has the ID:

```text
classFilter
```

The currently available TF2 classes are:

- Scout
- Soldier
- Pyro
- Demoman
- Heavy
- Engineer
- Medic
- Sniper
- Spy

Selecting a class will eventually filter the displayed contacts by that TF2 class.

### Contact List

Contacts will be displayed inside:

```text
contactList
```

If the user does not have any contacts, the page displays the empty state:

```text
No mercenaries yet.
Add your first contact to get started.
```

### Add Contact

The Add Contact button has the ID:

```text
addContactButton
```

The page also contains an Add Contact modal.

The modal currently contains:

- Contact name
- TF2 class
- Add Contact button
- Error message area

The actual contact API still needs to be connected before this functionality is complete.

---

# JavaScript

## `config.js`

Contains shared configuration used by the frontend JavaScript files.

Currently:

```javascript
const API_BASE_URL = "/api";
```

This allows API calls to use paths such as:

```javascript
`${API_BASE_URL}/Login.php`
```

instead of hardcoding the DigitalOcean server IP or domain throughout the project.

When the frontend and API are deployed on the same Apache server, `/api` points to:

```text
/var/www/html/api/
```

---

## `login.js`

Handles user login.

### Responsibilities

`login.js`:

1. Waits for the `loginForm` to be submitted.
2. Prevents the browser's default form submission.
3. Reads the username and password.
4. Sends the credentials to the Login API.
5. Reads the JSON response.
6. Displays an error if authentication fails.
7. Stores user information if authentication succeeds.
8. Redirects the user to `mainpage.html`.

### Request

The frontend sends a POST request to:

```text
/api/Login.php
```

with JSON in the following format:

```json
{
    "login": "username",
    "password": "password"
}
```

### Successful Response

The current Login API returns data in this format:

```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "firstName": "First",
        "lastName": "Last",
        "login": "username"
    }
}
```

### Local Storage

After a successful login, the frontend currently stores:

```text
userId
firstName
lastName
login
```

Example:

```javascript
localStorage.setItem("userId", data.user.id);
localStorage.setItem("firstName", data.user.firstName);
localStorage.setItem("lastName", data.user.lastName);
localStorage.setItem("login", data.user.login);
```

This information can later be used by `mainpage.html`, such as displaying the logged-in user's first name or identifying which user's contacts should be requested.

---

## `signup.js`

Handles new user registration.

### Responsibilities

`signup.js`:

1. Waits for `registerForm` to be submitted.
2. Prevents the browser's default form submission.
3. Reads the registration fields.
4. Sends the information to the Register API.
5. Reads the JSON response.
6. Displays an API error if registration fails.
7. Displays a success message if registration succeeds.
8. Redirects the user to `login.html`.

### Request

The frontend sends a POST request to:

```text
/api/Register.php
```

with JSON in the following format:

```json
{
    "firstName": "First",
    "lastName": "Last",
    "login": "username",
    "password": "password"
}
```

### Successful Response

The current Register API returns data in this format:

```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "firstName": "First",
        "lastName": "Last",
        "login": "username"
    }
}
```

After a successful response, the frontend displays:

```text
Account created successfully! You can now log in.
```

The user is then redirected to:

```text
login.html
```

### Registration Errors

The frontend expects API errors using the `error` field.

For example:

```json
{
    "error": "Login is already in use"
}
```

The error is displayed in the `error` element on the registration page.

---

# Current Application Flow

```text
                     ┌─────────────────┐
                     │  register.html  │
                     └────────┬────────┘
                              │
                         Create Account
                              │
                              ▼
                     ┌─────────────────┐
                     │  Register.php   │
                     └────────┬────────┘
                              │
                           Success
                              │
                              ▼
                     ┌─────────────────┐
                     │   login.html    │
                     └────────┬────────┘
                              │
                            Log In
                              │
                              ▼
                     ┌─────────────────┐
                     │    Login.php    │
                     └────────┬────────┘
                              │
                           Success
                              │
                              ▼
                     ┌─────────────────┐
                     │  mainpage.html  │
                     └─────────────────┘
```

---

# Frontend/API Relationship

The intended application structure on the DigitalOcean droplet is:

```text
/var/www/html/
│
├── login.html
├── register.html
├── mainpage.html
│
├── css/
│   └── styles.css
│
├── js/
│   ├── config.js
│   ├── login.js
│   ├── signup.js
│   └── ...
│
└── api/
    ├── Login.php
    ├── Register.php
    └── ...
```

The frontend does **not** connect directly to MySQL.

The application follows this flow:

```text
HTML / CSS / JavaScript
          |
          | fetch()
          v
       PHP API
          |
          | SQL
          v
        MySQL
```

For example:

```text
login.html
    |
    v
login.js
    |
    | POST
    v
/api/Login.php
    |
    v
Users table
```

---

# API Status Codes

The current authentication APIs use HTTP status codes to indicate whether requests succeeded.

### Login

```text
200 - Login successful
400 - Login/password missing
401 - Invalid login or password
500 - Server/database error
```

### Registration

```text
201 - User registered successfully
400 - Required registration information missing
409 - Login is already in use
500 - Server/database error
```

The JavaScript checks:

```javascript
response.ok
```

to determine whether the request succeeded.

---

# Styling

All pages currently use:

```text
css/styles.css
```

Shared classes have been added so the login and registration pages can use consistent styling.

Current shared classes include:

```text
.auth-container
.auth-card
.auth-header
.form-group
.error-message
.primary-button
.auth-footer
```

The main page also includes classes for the dashboard, contact tools, contact list, and Add Contact modal.

The visual design is still in progress and will use a TF2/Mann Co-inspired theme.

---

# Current Development Status

## Completed / Started

- Login page structure
- Registration page structure
- Main page structure
- Login JavaScript
- Registration JavaScript
- Shared API configuration
- Login API request format
- Registration API request format
- Successful login redirect
- Successful registration message
- Successful registration redirect
- API error display
- User information stored after login
- TF2 class options
- Main contact page layout
- Add Contact modal structure

## Still To Do

- Complete frontend styling
- Connect the contact API to `mainpage.html`
- Create contact JavaScript
- Load the logged-in user's contacts
- Add new contacts
- Delete contacts
- Search contacts
- Filter contacts by TF2 class
- Implement logout
- Display logged-in user's name
- Add admin functionality when the admin API is ready
- Determine admin status after login
- Test all frontend/API integration
- Deploy the current frontend to the team DigitalOcean droplet
- Deploy the current Contacts API to the droplet
- Replace the old Colors App deployment
- Test the application through Apache

---

# Important Notes for Backend Integration

The frontend currently expects these authentication endpoints:

```text
/api/Login.php
/api/Register.php
```

If endpoint names or locations are changed, update either the JavaScript calls or `config.js` accordingly.

The frontend currently expects API errors in this format:

```json
{
    "error": "Error message"
}
```

Login and registration success responses should include a `user` object.

The current Login API does **not** return admin status.

If the application needs to distinguish between normal users and administrators, the Login API will eventually need to return that information so the frontend can determine which interface should be displayed.

---

# Deployment Notes

The production frontend and API are intended to run from the same Apache server.

Because of this, `config.js` currently uses:

```javascript
const API_BASE_URL = "/api";
```

This means a request such as:

```javascript
fetch(`${API_BASE_URL}/Login.php`)
```

will request:

```text
/api/Login.php
```

from the same server that served the frontend.

This avoids hardcoding the DigitalOcean IP address and makes the frontend easier to move between servers.
