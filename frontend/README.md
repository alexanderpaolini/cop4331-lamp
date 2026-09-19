# Frontend

Frontend de la aplicación **Merc Mann** para el proyecto COP4331 LAMP.

## Estructura

```text
frontend/
├── css/
│   └── style.css
├── js/
│   ├── config.js
│   ├── login.js
│   ├── signup.js
│   ├── contacts.js
│   └── admin.js
├── login.html
├── register.html
├── mainpage.html
├── admin.html
└── README.md
```

## Páginas

### login.html

Página de inicio de sesión.

Utiliza `js/login.js` para enviar el Login y Password al API:

`POST /api/auth/login.php`

Cuando el login es exitoso, se guarda información básica del usuario en `localStorage`:

- `userId`
- `firstName`
- `lastName`
- `login`
- `isAdmin`

El usuario es redirigido dependiendo de su tipo de cuenta:

```text
Usuario normal → mainpage.html
Administrador  → admin.html
```

### register.html

Página para crear una cuenta nueva.

Permite seleccionar el tipo de cuenta:

- User
- Admin

Si se selecciona **Admin**, aparece un campo adicional para ingresar el Admin Code.

`js/signup.js` envía al API:

- `firstName`
- `lastName`
- `login`
- `password`
- `accountType`
- `adminCode`

Endpoint utilizado:

`POST /api/auth/signup.php`

El Admin Code se verifica en el backend y no se guarda directamente en el frontend.

### mainpage.html

Dashboard principal para usuarios normales.

Esta página contiene la interfaz principal del Contact Manager.

`js/contacts.js` se encarga de la lógica del frontend relacionada con el usuario conectado, logout, contactos, búsqueda y formularios.

La conexión completa de Contacts con los endpoints del API todavía está en desarrollo.

### admin.html

Dashboard separado para usuarios administradores.

Utiliza:

`js/admin.js`

`admin.js` verifica que exista un usuario conectado y que tenga `isAdmin = true`.

Si no existe un usuario conectado:

`login.html`

Si el usuario no es administrador:

`mainpage.html`

Actualmente esta página sirve como base para las funciones administrativas.

Funciones pendientes del Admin Dashboard:

- Mostrar usuarios registrados
- Administrar usuarios
- Eliminar usuarios
- Conectar estas acciones con el API

`localStorage` se utiliza para controlar la navegación del frontend. Las operaciones administrativas también deben ser verificadas por el backend.

## JavaScript

### config.js

Contiene la ruta base utilizada para las llamadas al API:

```javascript
const API_BASE_URL = "/api";
```

Esto permite utilizar rutas como:

```text
/api/auth/login.php
/api/auth/signup.php
```

sin escribir la dirección IP directamente.

### login.js

Maneja:

- Envío del formulario de login
- Comunicación con `login.php`
- Almacenamiento de información del usuario
- Detección de `isAdmin`
- Redirección según el tipo de usuario

Flujo:

```text
Login
  ↓
/api/auth/login.php
  ↓
Información del usuario
  ↓
isAdmin?
  ├── true  → admin.html
  └── false → mainpage.html
```

### signup.js

Maneja:

- Formulario de registro
- Selección entre User y Admin
- Mostrar o esconder el campo Admin Code
- Envío de `accountType`
- Envío de `adminCode`
- Comunicación con `signup.php`

Los nombres enviados por JavaScript deben coincidir con los nombres esperados por el backend:

```javascript
accountType
adminCode
```

### contacts.js

Contiene la lógica del dashboard principal y contactos.

Actualmente maneja principalmente la interacción del frontend. Las funciones de contactos todavía se están conectando con los endpoints correspondientes del API.

### admin.js

Controla el Admin Dashboard.

Verifica el estado del administrador utilizando:

```javascript
localStorage.getItem("isAdmin") === "true"
```

También maneja el logout y evita que un usuario normal permanezca en `admin.html`.

## Deployment

Los archivos dentro de `frontend/` son los archivos de desarrollo del repositorio.

Apache sirve el website desde:

```text
/var/www/html/
```

Después de modificar archivos dentro de `frontend/`, se deben copiar los cambios al directorio de Apache para probarlos en el website.

Ejemplo:

```bash
cp frontend/login.html /var/www/html/login.html
cp frontend/register.html /var/www/html/register.html
cp frontend/mainpage.html /var/www/html/mainpage.html
cp frontend/admin.html /var/www/html/admin.html

cp frontend/js/login.js /var/www/html/js/login.js
cp frontend/js/signup.js /var/www/html/js/signup.js
cp frontend/js/contacts.js /var/www/html/js/contacts.js
cp frontend/js/admin.js /var/www/html/js/admin.js
```

Después de actualizar HTML o JavaScript, hacer un hard refresh en el navegador:

```text
Ctrl + Shift + R
```

No es necesario reiniciar Apache para cambios normales de HTML, CSS o JavaScript.

## Estado Actual

Actualmente el frontend tiene implementado:

- Login
- Registro
- Selección de cuenta User/Admin
- Campo de Admin Code
- Comunicación con los APIs de login y signup
- Almacenamiento de información del usuario
- Detección de administradores
- Redirección de usuarios normales a `mainpage.html`
- Redirección de administradores a `admin.html`
- Dashboard básico para administradores
- Protección básica de navegación del Admin Dashboard
- Logout

## Pendiente

- Conectar completamente Contacts con el API
- Mostrar usuarios reales en el Admin Dashboard
- Agregar controles para eliminar y administrar usuarios
- Terminar CSS/UI
- Continuar pruebas de integración entre frontend y backend
