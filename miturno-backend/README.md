# TFG MiTurno - BACKEND

## Descripción
Backend de TFG MiTurno, API REST construida con Laravel y MySQL diseñada para la gestión de centros estéticos y peluquerías.

¿Que proporciona?:
 - Autenticación y gestión de usuarios
 - Gestión del negocio al completo, servicios, empleados, reservas.
 - Información para el cliente
 - Sistema de reservas para clientes.

## URLs

### URL Producción
```
tfgmiturno-production.up.railway.app
API: tfgmiturno-production.up.railway.app/api
```

### URL Local
```
http://127.0.0.1:8000
API: http://127.0.0.1:8000/api
```

## Instalación

### Requisitos de la instalación
- Node.js - 22.14+
- Npm - 10.9.2+
- Xampp - 3.3+
- git - 2.42+
- Composer - 2.8.5+
- PHP - 8.4.4+
- Lanzar Apache y MySQL en XAMPP
- `composer install` completo sin errores
- Archivo `.env` sin errores
- API responde en `http://127.0.0.1:8000/api`

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

### 3. Crear la base de datos y configurarla
A continuación, se lanzará Apache y MySQL y en `http://localhost/phpmyadmin/` crearemos la base de datos con nombre `miturno_db`, usando preferiblemente el cotejamiento `utf8mb4_unicode_ci`

Una vez creada, habrá que confirmar que en `php.ini` (Archivo de configuración de PHP) están activas las siguientes líneas:

- extension=pdo_mysql
- extension=mysqli

Una vez comprobado, se ejecutarán las migraciones y ejecutaremos los seeders:
```bash
php artisan migrate
php artisan db:seed --force
```

### 4. Lanzar servidor
```bash
# Iniciar servidor
php artisan serve
```
El servidor estará disponible en: `http://127.0.0.1:8000`

## Estructura del proyecto
```
miturno-backend/
├── app/
|   ├── Http/
|   |   ├── Controllers/
|   |   |   ├── Api/
|   |   |   |   ├── AuthController.php
|   |   |   |   ├── EmpleadoController.php
|   |   |   |   ├── HorarioController.php
|   |   |   |   ├── NotificacionController.php
|   |   |   |   ├── ReservaController.php
|   |   |   |   ├── ServicioController.php
|   |   |   |   └── UsuarioController.php
|   |   |   └── Controller.php
|   |   └── Middleware/
|   |       └── CheckRol.php
|   ├── Models/
|   |   ├── Empleado.php
|   |   ├── Horario.php
|   |   ├── Notificacion.php
|   |   ├── Reserva.php
|   |   ├── Servicio.php
|   |   └── User.php
|   └── Providers/
|       └── AppServiceProvider.php
├── bootstrap/
|   ├── app.php
|   └── providers.php
├── config/
|   ├── app.php
|   ├── auth.php
|   ├── cache.php
|   ├── cors.php
|   ├── database.php
|   ├── filesystems.php
|   ├── logging.php
|   ├── mail.php
|   ├── queue.php
|   ├── sanctum.php
|   ├── services.php
|   └── sessions.php
├── database/
|   ├── factories/
|   |   └── UserFactory.php
|   ├── migrations/
|   |   ├── XXXX_XX_XX_XXXXXX_create_users_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_cache_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_jobs_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_servicios_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_empleados_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_horarios_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_reservas_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_notificacions_table.php
|   |   ├── XXXX_XX_XX_XXXXXX_create_personal_access_tokens_table.php
|   |   └── XXXX_XX_XX_XXXXXX_create_empleado_servicios_table.php
|   └── seeders/
|       ├── DatabaseSeeder.php
|       ├── EmpleadoSeeder.php
|       ├── EmpleadoServicioSeeder.php
|       ├── HorarioSeeder.php
|       ├── NotificacionSeeder.php
|       ├── ReservaSeeder.php
|       ├── ServiciosSeeder.php
|       └── UserSeeder.php
├── public/
|   ├── .htaccess
|   ├── index.php
|   └── robots.txt
├── resources/
|   ├── css/
|   |   └── app.css
|   ├── js/
|   |   ├── app.js
|   |   └── bootstrap.js
|   └── views/
|       └── welcome.blade.php
├── routes/
|   ├── api.php
|   ├── console.php
|   └── web.php
├── storage/
├── tests/
├── .editorconfig
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
├── README.md
└── vite.config.js
```

## Endpoints

### Autenticación
- ``POST   /api/auth/register`` - Registro de usuario
- ``POST   /api/auth/login`` - Inicio de sesión
- ``POST   /api/auth/logout`` - Cerrar sesión

- ``GET    /api/auth/me`` - Obtener información usuario
- ``PUT    /api/auth/me`` - Editar usuario
- ``DELETE /api/auth/me`` - Eliminar usuario

### Usuarios
- ``GET    /api/usuarios`` - Obtener usuarios
- ``POST   /api/usuarios`` - Crear usuarios
- ``GET    /api/usuarios/:id`` - Obtener usuario específico
- ``PUT    /api/usuarios/:id`` - Actualizar usuario específico
- ``DELETE /api/usuarios/:id`` - Eliminar usuario específico

- ``GET    /api/usuarios/clientes/buscar`` - Buscar usuarios clientes

### Empleados
- ``GET    /api/empleados`` - Obtener empleados
- ``POST   /api/empleados`` - Crear empleados
- ``GET    /api/empleados/:id`` - Obtener empleado específico
- ``PUT    /api/empleados/:id`` - Actualizar empleado específico
- ``DELETE /api/empleados/:id`` - Eliminar empleado específico

- ``PUT    /api/empleados/:id/servicios`` - Asignar servicios empleado específico
- ``GET    /api/empleado/mi-horario`` - Obtener horario empleado específico
- ``GET    /api/empleado/mis-reservas`` - Obtener reservas empleado específico

### Servicios
- ``GET    /api/servicios`` - Obtener servicios
- ``POST   /api/servicios`` - Crear servicios
- ``GET    /api/catalogo-servicios`` - Obtener servicios activos
- ``GET    /api/servicios/:id`` - Obtener servicio específico
- ``PUT    /api/servicios/:id`` - Actualizar servicio específico
- ``DELETE /api/servicios/:id`` - Eliminar servicio específico

- ``GET    /api/servicios/:id/empleado`` - Obtener servicios asignados a un empleado

### Horarios
- ``GET    /api/horarios`` - Obtener horarios
- ``POST   /api/horarios`` - Crear servicios
- ``GET    /api/horarios/:id`` - Obtener servicio específico
- ``PUT    /api/horarios/:id`` - Actualiza servicio específico
- ``DELETE /api/horarios/:id`` - Elimina servicio específico

### Reservas
- ``GET    /api/reservas`` - Obtener reservas
- ``POST   /api/reservas`` - Crear reservas
- ``GET    /api/disponibilidad`` - Obtener disponibilidad para una reserva
- ``GET    /api/mis-reservas`` - Obtener las reservas de un usuario
- ``GET    /api/reservas/:id`` - Obtener reserva específica
- ``PUT    /api/reservas/:id`` - Actualizar reserva específica
- ``DELETE /api/reservas/:id`` - Eliminar reserva específica

- ``PATCH  /api/reservas/:id/cancelar`` - Cancelar una reserva

### Administración
- ``GET    /api/admin/reservas`` - Obtener todas las reservas
- ``GET    /api/admin/reservas/:id`` - Obtener reserva específica


### Notificaciones
- `GET    /api/notificationes` - Obtener notificaciones
- ``POST   /api/notificationes`` - Crear notificaciones
- ``GET    /api/notificationes/:id`` - Obtener reserva específica
- ``PUT    /api/notificationes/:id`` - Actualizar reserva específica
- ``DELETE /api/notificationes/:id`` - Eliminar reserva específica

## Modelo de datos

### User

**Tabla:** `users`

**Campos**
- `id`: bigint, PK, autoincrement
- `nombre`: string
- `apellidos`: string
- `fecha_nacimiento`: date, nullable
- `email`: string, unique
- `password`: string
- `rol`: enum (`cliente`, `empleado`, `admin`)
- `telefono`: string, nullable
- `activo`: boolean, default `true`
- `created_at`: timestamp
- `updated_at`: timestamp

**Relaciones**
- Un user puede ser un empleado.
- Un user puede tener muchas reservas.
- Un user puede tener muchas notificaciones.

### Empleado

**Tabla:** `empleados`

**Campos**
- `id`: bigint, PK, autoincrement
- `usuario_id`: bigint, FK references `id` on `users`
- `especialidades`: text, nullable
- `fecha_contratacion`: date
- `activo`: boolean, default `true`
- `created_at`: timestamp
- `updated_at`: timestamp

**Relaciones**
- Un empleado es usuario.
- Un empleado puede tener muchos horarios.
- Un empleado puede tener muchas reservas.
- Un empleado puede tener muchas notificaciones.
- Un empleado puede estar asociado a muchos servicios.

### Servicios

**Tabla:** `servicios`

**Campos**
- `id`: bigint, PK, autoincrement
- `nombre`: string
- `descripcion`: text, nullable
- `duracion_minutos`: int
- `precio`: decimal `8,2`
- `activo`: boolean, default `true`
- `created_at`: timestamp
- `updated_at`: timestamp

**Relaciones**
- Un servicio puede tener muchas reservas.
- Un servicio puede pertenecer a varios empleados.

### Reservas

**Tabla:** `reservas`

**Campos**
- `id`: bigint, PK, autoincrement
- `usuario_id`: bigint, FK references `id` on `users`
- `empleado_id`: bigint, nullable, FK references `id` on `empleados`
- `servicio_id`: bigint, FK references `id` on `servicios`
- `fecha_hora_inicio`: date, nullable
- `estado`: enum (`pendiente`, `confirmada`, `cancelada`, `completada`, `ausencia`)
- `created_at`: timestamp
- `updated_at`: timestamp

**Relaciones**
- Un reserva pertenece a un usuario.
- Un reserva pertenece a un empleado.
- Un reserva pertenece a un servicio.
- Un reserva puede tener muchas notificaciones.

### Horarios

**Tabla:** `horarios`

**Campos**
- `id`: bigint, PK, autoincrement
- `empleado_id`: bigint, FK references `id` on `empleados`
- `dia_semana`: int
- `hora_inicio`: date
- `hora_fin`: date
- `tipo`: enum (`normal`, `festivo`, `cierre`)
- `activo`: boolean, default `true`
- `created_at`: timestamp
- `updated_at`: timestamp

**Relaciones**
- Un horario pertenece a un empleado.

### Notificaciones

**Tabla:** `notificaciones`

**Campos**
- `id`: bigint, PK, autoincrement
- `empleado_id`: bigint, nullable, FK references `id` on `empleados`
- `usuario_id`: bigint, FK references `id` on `users`
- `reserva_id`: bigint, FK references `id` on `reservas`
- `tipo`: enum (`confirmacion`, `recordatorio`)
- `enviado`: boolean, default `false`
- `created_at`: timestamp
- `updated_at`: timestamp

**Relaciones**
- Una notificación pertenece a un empleado.
- Una notificación pertenece a un usuario.
- Una notificación pertenece a una reserva.