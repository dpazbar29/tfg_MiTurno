# TFG MiTurno 

## Descripción del proyecto

MiTurno es una aplicación web para que permite gestionar un centro estético o peluquería gestionar como administrador la empresa, como empleado te permite gestionar tu trabajo y como cliente te permite solicitar servicios y consultar información, modernizando así los sistemas típicos y manuales.

## Tiene los siguientes apartados:

### Desde el Administrador:
- Gestiona los servicios que se prestan.
- Gestiona los empleados.
- Gestiona las reservas agendadas por los clientes.

### Desde el empleado:
- Consultar su horario laboral.
- Consultar las citas que han agendado clientes solicitando su servicio.

### Desde el cliente:
- Ver los servicios que están disponibles.
- Reservar con un empleado específico para un servicio específico.
- Gestionar las reservas pendientes y ver reservas pasadas.


## Características del diseño

- Mobile-first design
- Modo claro y oscuro controlado con variables
- Diseño responsive

## Tecnologías utilizadas

###  Frontend
- VUE 3
- Vite
- Axios
- React router - Enrutamiento
- SCSS - Estilos
- VeeValidate - Validación de formularios

### Backend
- PHP
- Laravel
- MySQL
- Composer

### Infraestructura y despliegue
- Node.js
- Npm
- Git
- Railway
- XAMPP (Apache y MySQL)

## Acceso

### Frontend (Aplicación Web)
```
https://miturno-frontend-production.up.railway.app/
```

### Backend (API REST)
```
tfgmiturno-production.up.railway.app
API ENDPOINT: tfgmiturno-production.up.railway.app/api
```

## Requisitos previos
Requisitos necesarios:

- Node.js - 22.14+
- Npm - 10.9.2+
- Xampp - 3.3+
- git - 2.42+
- Composer - 2.8.5+
- PHP - 8.4.4+

## Instalación y setup

### 1. Clonar repositorio
```bash
git clone https://github.com/dpazbar29/tfg_MiTurno
cd tfg_MiTurno
```

### 2. Configurar backend
```bash
cd miturno-backend

# Instalar dependencias
composer install
```

Creamos el archivo .env con este contenido
```bash
APP_NAME=miturno
APP_ENV=local
APP_KEY=base64:fNrzAekOD69HVjz5OkSDDK8zvCEFiM8fjRNXpJ+dZbE=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=miturno_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

```

Finalmente se inicia el servidor:
```bash
# Iniciar servidor
php artisan serve
```
El servidor estará disponible en: `http://127.0.0.1:8000`

### 3.Configurar frontend
```bash
cd ../miturno-frontend

# Instalar dependencias
npm install
```

Crear archivo .env con esta información:
```bash
VITE_API_URL=http://127.0.0.1:8000/api
```

Finalmente se inicia el servidor en desarrollo:
```bash
# Iniciar servidor
npm run dev
```
El servidor estará disponible en `http://localhost:5173/`

## Estructura del proyecto
```
tfg_MiTurno/
|
├── miturno-backend/
|   ├── app/
|   |   ├── Http/
|   |   |   ├── Controllers/
|   |   |   └── Middleware/
|   |   ├── Models/
|   |   └── Providers/
|   ├── bootstrap/
|   ├── config/
|   ├── database/
|   |   ├── factories/
|   |   ├── migrations/
|   |   └── seeders/
|   ├── public/
|   ├── resources/
|   |   ├── css/
|   |   ├── js/
|   |   └── views/
|   ├── routes/
|   ├── storage/
|   ├── tests/
|   ├── .editorconfig
|   ├── artisan
|   ├── composer.json
|   ├── composer.lock
|   ├── package.json
|   ├── phpunit.xml
|   ├── README.md
|   └── vite.config.js
|
├── miturno-frontend/
|   ├── public/
|   ├── src/
|   |   ├── api/
|   |   ├── assets/
|   |   |   ├── media/
|   |   |   └── styles/
|   |   |       ├── abstracts/
|   |   |       ├── base/
|   |   |       ├── components/
|   |   |       ├── layout/
|   |   |       ├── views/
|   |   |       └── main.scss
|   |   ├── components/
|   |   |   ├── admin/
|   |   |   ├── auth/
|   |   |   ├── booking/
|   |   |   ├── dashboard/
|   |   |   ├── empleado/
|   |   |   ├── feedback/
|   |   |   ├── forms/
|   |   |   ├── reservations/
|   |   |   └── services/
|   |   ├── composables/
|   |   ├── config/
|   |   ├── routes/
|   |   ├── stores/
|   |   ├── views/
|   |   |   ├── admin/
|   |   |   └── empleado/
|   |   ├── App.vue
|   |   └── main.js
|   ├── index.html
|   ├── jsconfig.json
|   ├── package-lock.json
|   ├── package.json
|   ├── README.md
|   └── vite.config.js
└── README.md
```

## Documentación
- **[Backend README](./miturno-backend/README.md)** - Documentación Backend
- **[Frontend README](./miturno-frontend/README.md)** - Documentación Frontend

## Endpoints
### Autenticación
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout

GET    /api/auth/me
PUT    /api/auth/me
DELETE /api/auth/me
```

### Usuarios
```
GET    /api/usuarios
POST   /api/usuarios
GET    /api/usuarios/:id
PUT    /api/usuarios/:id
DELETE /api/usuarios/:id

GET    /api/usuarios/clientes/buscar
```

### Empleados
```
GET    /api/empleados
POST   /api/empleados
GET    /api/empleados/:id
PUT    /api/empleados/:id
DELETE /api/empleados/:id

PUT    /api/empleados/:id/servicios
GET    /api/empleado/mi-horario
GET    /api/empleado/mis-reservas
```

### Servicios
```
GET    /api/servicios
POST   /api/servicios
GET    /api/catalogo-servicios
GET    /api/servicios/:id
PUT    /api/servicios/:id
DELETE /api/servicios/:id

GET    /api/servicios/:id/empleado
```

### Horarios
```
GET    /api/horarios
POST   /api/horarios
GET    /api/horarios/:id
PUT    /api/horarios/:id
DELETE /api/horarios/:id
```

### Reservas
```
GET    /api/reservas
POST   /api/reservas
GET    /api/disponibilidad
GET    /api/mis-reservas
GET    /api/reservas/:id
PUT    /api/reservas/:id
DELETE /api/reservas/:id

PATCH  /api/reservas/:id/cancelar
```

### Administración
```
GET    /api/admin/reservas
GET    /api/admin/reservas/:id
```

### Notificaciones
```
GET    /api/notificationes
POST   /api/notificationes
GET    /api/notificationes/:id
PUT    /api/notificationes/:id
DELETE /api/notificationes/:id
```

## Flujo de trabajo
### Desarrollo
1. Haz cambios en tu rama
2. Commit de los cambios
3. Push a la rama

### Flujo de trabajo
```bash
# Acceder a la rama en la que trabajar
git checkout <rama-designada>

# Crear la rama en la que se va a trabajar
git checkout -b <nombre-nueva-rama>

# Hacer los cambios necesarios

# Commit y push
git add .
git commit -m "descripción del commit"
git push origin <rama-designada>
```

## Despliegue
### Backend desplegado en Railway
 1. Crear cuenta en Railway
    - Ve a railway.app
    - Crea una cuenta y conecta tu repositorio github

 2. Establecer Root Directory en en directorio miturno-backend dentro del repositorio

 3. Configurar variables de entorno
    - En Railway añade las siguientes variables:
    - `APP_NAME`: nombre de la app
    - `APP_ENV`: production
    - `APP_DEBUG`: false
    - `APP_KEY`: clave de la app
    - `LOG_CHANNEL`: stderr
    - `DB_CONNECTION`: mysql
    - `DB_HOST`: `${{MySQL.MYSQLHOST}}`
    - `DB_PORT`: `${{MySQL.MYSQLPORT}}`
    - `DB_DATABASE`: `${{MySQL.MYSQLDATABASE}}`
    - `DB_USERNAME`: `${{MySQL.MYSQLUSER}}`
    - `DB_PASSWORD`: `${{MySQL.MYSQLPASSWORD}}`
    - `APP_URL`: URL generada en el despliegue
    - `RAILPACK_PHP_VERSION`: 8.4
    - `SESSION_DRIVER`: cookie
    - `SANCTUM_STATEFUL_DOMAINS`: URL obtenida posteriormente en el despliegue del frontend
    - `SESSION_DOMAIN`: null
 
 4. Genera en Settings>Networking la ruta pública

 5. Despliega el servicio

### Base de datos en Railway

 1. Crear un servicio en el proyecto Railway de base de datos MySQL
 
 2. Añade al servicio del backend las variables de entorno designadas arriba

### Despliegue del frontend en Railway

 1. Crea un servicio dentro del proyecto con un Root Directory en miturno-frontend dentro del repositorio
 
 2. Añadir la variable de entorno que apunta al frontend en el backend

 3. Añade esta variable de entorno en el servicio del frontend:
    - `VITE_API_URL`: `https://<URL-del-backend>/api`
 
 3. Genera en Settings>Networking la ruta pública del servicio 

 4. Despliega el servicio