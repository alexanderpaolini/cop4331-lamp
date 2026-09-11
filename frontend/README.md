# Player 1 + Player 2 Frontend

Frontend for the **Player 1 + Player 2** Team Fortress 2 player matching application.

The goal of the application is to help TF2 players find other players who match their playstyle, schedule, platform, skill level, preferred classes, and team preferences.

The frontend uses:

- HTML
- CSS
- JavaScript

The frontend communicates with the backend through API requests using JavaScript `fetch()`.

!!! I recommend downloading Live Server on your VS code so you can test locally without relying on the droplet just for the css and js, once we start incorporating API then we'll have to move out of it!!! :D
---

# Current Structure

```text
frontend/
│
├── index.html
├── register.html
├── dashboard.html
│
├── css/
│   └── style.css
│
├── js/
│   ├── config.js
│   ├── login.js
│   └── register.js
│
└── README.md
```

More files can be added as additional features are implemented.

---

# Pages

## `index.html`

Main landing and login page.

This page should contain:

- Player 1 + Player 2 branding
- Short description of the application
- Username/login input
- Password input
- Login button
- Link to the registration page

Login functionality is handled by:

```text
js/login.js
```

After a successful login, the user should be redirected to:

```text
dashboard.html
```

---

## `register.html`

Account registration and initial player profile page.

### Basic Account Information

The registration form should collect:

- First name
- Last name
- Username/login
- Password

### Player Profile Information

The profile may also contain:

- Display name
- Steam/account username
- Platform
- Rank / skill level
- Preferred role
- Team preference
- TF2 classes played
- Usual play times

### Platforms

Possible platforms include:

- PC
- PlayStation
- Xbox
- Nintendo

### TF2 Classes

Players can select the classes they normally play:

- Scout
- Soldier
- Pyro
- Demoman
- Heavy
- Engineer
- Medic
- Sniper
- Spy

### Team Preference

Possible team preferences:

- RED
- BLU
- No preference

### Play Times

Possible play-time preferences:

- Morning
- Afternoon
- Evening
- Late night

Registration functionality is handled by:

```text
js/register.js
```

---

## `dashboard.html`

Main application page displayed after a successful login.

The dashboard will eventually contain the player matching functionality.

Planned features include:

- View recommended players
- Search for players
- Filter players
- View player profiles
- Edit own profile
- Add/connect with players
- Logout

### Player Search Filters

Players may eventually be filtered using:

- Platform
- Rank
- Preferred role
- TF2 class
- Team preference
- Play time

Additional JavaScript files can be created as these features are implemented.

For example:

```text
js/dashboard.js
js/search.js
js/profile.js
```

---

# CSS

Shared frontend styling should go inside:

```text
css/style.css
```

The interface should follow the TF2-inspired design created for the project.

General design direction:

- RED and BLU team colors
- Dark navigation bars
- TF2-inspired typography
- TF2-inspired panels and cards
- Large, clear buttons
- Simple and readable forms
- Player cards for search and matching results

Avoid placing large amounts of CSS directly inside HTML files.

---

# JavaScript

## `js/config.js`

Contains shared frontend configuration values.

For example:

```javascript
const API_URL = "/api";
```

This allows all JavaScript files to use the same backend API location without hardcoding it multiple times.

Load `config.js` before JavaScript files that depend on it.

Example for the login page:

```html
<script src="js/config.js"></script>
<script src="js/login.js"></script>
```

Example for the registration page:

```html
<script src="js/config.js"></script>
<script src="js/register.js"></script>
```

---

## `js/login.js`

Handles login functionality.

Responsibilities include:

- Reading the username and password from the login form
- Creating the login request
- Sending the login information to the backend
- Reading the backend response
- Displaying login errors
- Saving required user information after successful login
- Redirecting the user to `dashboard.html`

---

## `js/register.js`

Handles account registration and profile creation.

Responsibilities include:

- Reading values from the registration form
- Creating the registration request
- Sending registration information to the backend
- Reading the backend response
- Displaying registration errors
- Confirming successful account creation
- Redirecting the user to the login page

---

# Planned API

The frontend communicates with the backend using JavaScript `fetch()`.

The endpoints below represent the planned API structure.

The exact endpoint names, request properties, and response formats may change depending on the final backend implementation.

---

## Authentication

### Create Account

```text
POST /api/auth/signup
```

Used by:

```text
register.html
js/register.js
```

Example request:

```json
{
    "firstName": "John",
    "lastName": "Doe",
    "login": "EngineerMain",
    "password": "password"
}
```

---

### Login

```text
POST /api/auth/login
```

Used by:

```text
index.html
js/login.js
```

Example request:

```json
{
    "login": "EngineerMain",
    "password": "password"
}
```

---

# User / Profile API

## Get Player Profile

```text
GET /api/users/{id}
```

Returns information about a player.

Example response:

```json
{
    "id": 1,
    "displayName": "EngineerMain",
    "platform": "PC",
    "rank": "Casual",
    "preferredRole": "Support",
    "teamPreference": "RED"
}
```

---

## Update Player Profile

```text
PUT /api/users/{id}
```

Used when a player changes information such as:

- Display name
- Platform
- Rank
- Preferred role
- Team preference
- TF2 classes
- Play times

---

# Player Search API

## Search Players

```text
GET /api/players/search
```

Used to find players that meet selected filters.

Possible filters include:

```text
platform
rank
role
class
teamPreference
playTime
```

Example:

```text
/api/players/search?platform=PC&class=Engineer
```

The backend should return matching player profiles that the frontend can display as player cards.

---

# Matching API

## Get Suggested Matches

```text
GET /api/matches
```

Returns recommended players based on profile information.

Matching may eventually consider:

- Platform compatibility
- Shared TF2 classes
- Complementary classes
- Preferred roles
- Similar skill level
- Rank
- Team preference
- Overlapping play times

The exact matching algorithm will be determined later.

--
# Frontend / Backend Communication

The frontend should communicate with the backend using JavaScript `fetch()`.

Example login request:

```javascript
const response = await fetch(`${API_URL}/auth/login`, {
    method: "POST",

    headers: {
        "Content-Type": "application/json"
    },

    body: JSON.stringify({
        login: username,
        password: password
    })
});

const result = await response.json();
```

The frontend and backend must agree on:

- API endpoint
- HTTP method
- Request JSON format
- Property names
- Response JSON format
- Error responses

If the backend changes any of these, the frontend must be updated to match.

---

# Project Organization

Keep the different parts of the application separated.

```text
HTML       = Page structure

CSS        = Appearance and styling

JavaScript = Frontend behavior and API requests

PHP / API  = Backend logic

MySQL      = Database and stored information
```

The frontend should **not communicate directly with MySQL**.

Communication should follow this structure:

```text
User
  │
  ▼
Browser
  │
  ▼
HTML / CSS / JavaScript
  │
  │ fetch()
  ▼
Backend API
  │
  ▼
MySQL Database
```

---

# Application Structure

The planned application flow is:

```text
                    PLAYER 1 + PLAYER 2
                             │
                             ▼
                     LOGIN / REGISTER
                             │
                             ▼
                         DASHBOARD
                             │
            ┌────────────────┼────────────────┐
            │                │                │
            ▼                ▼                ▼
         PROFILE        FIND PLAYERS      MESSAGES
            │                │
            │                ▼
            │             FILTER
            │                │
            │                ▼
            └──────────► MATCHING
                             │
                             ▼
                      PLAYER RESULTS
                             │
                             ▼
                       VIEW PROFILE
```

---

# Matching Concept

The purpose of the matching system is to recommend TF2 players who are likely to play well together.

At a high level:

### 1. Filter Players

Filter possible players using basic requirements such as:

- Platform
- Rank
- Role
- Team preference
- Play time

### 2. Calculate Compatibility

Compare player profiles using information such as:

- Shared or complementary TF2 classes
- Similar skill level
- Preferred roles
- Overlapping play schedules
- Platform
- Team preferences

### 3. Recommend Players

Players with higher compatibility can be shown first in the search results.

### 4. Connect

Users can then view profiles onnect with recommended players. Kind of like Facebook. If extra time allows we should consider the idea of a News Board with empty messages just for show.

