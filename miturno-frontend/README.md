# TFG MiTurno - FRONTEND

## Descripción
Frontend de TFG MiTurno, Aplicación Web desarrollada en VUE 3 + Vite, diseñada para la gestión de centros estéticos y peluquerías.

¿Que proporciona?:
Esta aplicación se divide en 3 grupos, administrador, empleado y cliente.

**Administrador**:
- Perfil personal editable
- Panel de empleados:
  - Crear empleados
  - Editar empleados
  - Eliminar empleados
  - Asignar servicios a empleados
- Panel de servicios:
  - Crear servicios
  - Editar servicios
  - Eliminar servicios
- Panel de reservas:
  - Crear reservas para un usuario
  - Eliminar reservas
  - Editar reservas
- Panel de horarios:
  - Crear horarios para un empleado
  - Editar horarios para un empleado
  - Eliminar horarios para un empleado

**Empleado**:
- Perfil personal editable
- Panel de horario:
  - Consultar horario de trabajo
- Panel de citas:
  - Consultar las citas asignadas
  - Gestionar estas citas

**Clientes**:
- Perfil personal editable y opción de eliminar cuenta
- Panel de servicios:
  - Observar servicios disponibles y consultar la información
  - Acceder a la reserva de un servicio
- Panel de reservas:
  - Crear una reserva para un servicio específico
- Panel de reservas personales:
  - Acceder a las reservas hechas
  - Historial de reservas pasadas
  - Gestión de las citas asignadas


A nivel de diseño, la aplicación cuenta con un diseño responsive y mobile-first con selección de tema claro/oscuro.

## URLs

### URL Producción
```
https://miturno-frontend-production.up.railway.app/
```

### URL Local
```
http://localhost:5173/
```

## Instalación

### Requisitos de la instalación
- Node.js - 22.14+
- Npm - 10.9.2+
- git - 2.42+
- `npm install` completo sin errores
- Archivo `.env` sin errores
- Responde en `http://localhost:5173/`

### 1. Clonar repositorio
```bash
git clone https://github.com/dpazbar29/tfg_MiTurno
cd tfg_MiTurno
```

### 2. Configurar backend
```bash
cd miturno-frontend

# Instalar dependencias
npm install
```

Creamos el archivo .env con este contenido
```bash
VITE_API_URL=http://127.0.0.1:8000/api
```

### 3. Lanzar servidor
```bash
# Iniciar servidor
npm run dev
```
Estará disponible en: `http://localhost:5173/`

## Estructura del proyecto
```
miturno-frontend/
├── public/
├── src/
|   ├── api/
|   |   ├── auth.js
|   |   ├── axios.js
|   |   ├── empleados.js
|   |   ├── horarios.js
|   |   ├── reservas.js
|   |   ├── servicios.js
|   |   └── usuarios.js
|   ├── assets/
|   |   ├── media/
|   |   |   ├── barba.jpg
|   |   |   ├── fade.webp
|   |   |   ├── logo.ico
|   |   |   ├── peinado.jpg
|   |   |   └── peinado2.jpg
|   |   └── styles/
|   |       ├── abstracts/
|   |       |   ├── _index.scss
|   |       |   ├── _mixins.scss
|   |       |   ├── _tokens.scss
|   |       |   └── _variables.scss
|   |       ├── base/
|   |       |   ├── _globals.scss
|   |       |   ├── _index.scss
|   |       |   └── _reset.scss
|   |       ├── components/
|   |       |   ├── _admin-employees-form.scss
|   |       |   ├── _admin-employees-modal.scss
|   |       |   ├── _admin-service-card.scss
|   |       |   ├── _admin-service-form.scss
|   |       |   ├── _admin-service-modal.scss
|   |       |   ├── _auth-card.scss
|   |       |   ├── _booking-field.scss
|   |       |   ├── _booking-summary.scss
|   |       |   ├── _booking-times.scss
|   |       |   ├── _btn.scss
|   |       |   ├── _create_reservation-form.scss
|   |       |   ├── _dashboard_info_list.scss
|   |       |   ├── _dashboard-summary-card.scss
|   |       |   ├── _edit-reservation-form.scss
|   |       |   ├── _empleado-horario-day-card.scss
|   |       |   ├── _empleado-horario-toolbar.scss
|   |       |   ├── _empleado-horario-week-grid.scss
|   |       |   ├── _empleado-reservas-filters.scss
|   |       |   ├── _empleado-reservas-pagination.scss
|   |       |   ├── _empleado-reservas-table.scss
|   |       |   ├── _empleado-reservas-toolbar.scss
|   |       |   ├── _employee-card.scss
|   |       |   ├── _employee-schedule-filter.scss
|   |       |   ├── _footer.scss
|   |       |   ├── _form-filed.scss
|   |       |   ├── _header.scss
|   |       |   ├── _index.scss
|   |       |   ├── _reservation-card.scss
|   |       |   ├── _reservations-filter.scss
|   |       |   ├── _reservations-modal.scss
|   |       |   ├── _reservations-table.scss
|   |       |   ├── _reservations-toolbar.scss
|   |       |   ├── _schedule-modal.scss
|   |       |   ├── _schedule-week-grid.scss
|   |       |   ├── _service-card.scss
|   |       |   └── _status-message.scss
|   |       ├── layout/
|   |       |   ├── _auth-layout.scss
|   |       |   └── _index.scss
|   |       ├── views/
|   |       |   ├── admin/
|   |       |   |   ├── _employee-admin.scss
|   |       |   |   ├── _reservas-admin.scss
|   |       |   |   ├── _schedules-admin.scss
|   |       |   |   └── _services-admin.scss
|   |       |   ├── empleado/
|   |       |   |   ├── _horario-empleado.scss
|   |       |   |   └── _reservas-empleado.scss
|   |       |   ├── _booking-form.scss
|   |       |   ├── _dashboard.scss
|   |       |   ├── _home.scss
|   |       |   ├── _index.scss
|   |       |   ├── _login.scss
|   |       |   ├── _not-found.scss
|   |       |   ├── _register.scss
|   |       |   ├── _reservations-page.scss
|   |       |   └── _services.scss
|   |       └── main.scss
|   ├── components/
|   |   ├── admin/
|   |   |   ├── employees/
|   |   |   |   ├── EmployeeCard.vue
|   |   |   |   ├── EmployeeProfileModal.vue
|   |   |   |   └── EmployeeServicesModal.vue
|   |   |   ├── reservations/
|   |   |   |   ├── CreateReservationForm.vue
|   |   |   |   ├── EditReservationForm.vue
|   |   |   |   ├── ReservationModal.vue
|   |   |   |   ├── ReservationsFilters.vue
|   |   |   |   ├── ReservationsTable.vue
|   |   |   |   └── ReservationsToolbar.vue
|   |   |   ├── schedule/
|   |   |   |   ├── EmployeeScheduleFilter.vue
|   |   |   |   ├── ScheduleModal.vue
|   |   |   |   └── ScheduleWeekGrid.vue
|   |   |   └── services/
|   |   |       ├── AdminServiceCard.vue
|   |   |       ├── AdminServiceForm.vue
|   |   |       └── AdminServiceModal.vue
|   |   ├── auth/
|   |   |   └── AuthCard.vue
|   |   ├── booking/
|   |   |   ├── BookingEmployeeSelect.vue
|   |   |   ├── BookingServiceSelect.vue
|   |   |   ├── BookingServiceSummary.vue
|   |   |   └── BookingTimeSlots.vue
|   |   ├── dashboard/
|   |   |   ├── DashboardInfoList.vue
|   |   |   └── DashboardSummaryCard.vue
|   |   ├── empleado/
|   |   |   ├── EmpleadoHorarioDayCard.vue
|   |   |   ├── EmpleadoHorarioToolbar.vue
|   |   |   ├── EmpleadoHorarioWeekGrid.vue
|   |   |   ├── EmpleadoReservasFilters.vue
|   |   |   ├── EmpleadoReservasPagination.vue
|   |   |   ├── EmpleadoReservasTable.vue
|   |   |   └── EmpleadoReservasToolbar.vue
|   |   ├── feedback/
|   |   |   └── StatusMessage.vue
|   |   ├── forms/
|   |   |   ├── BaseButton.vue
|   |   |   └── BaseInput.vue
|   |   ├── reservations/
|   |   |   └── ReservationCard.vue
|   |   ├── services/
|   |   |   └── Service.vue
|   |   ├── Footer.vue
|   |   └── Header.vue
|   ├── composables/
|   |   └── useTheme.js
|   ├── config/
|   |   └── navigation.js
|   ├── router/
|   |   └── index.js
|   ├── stores/
|   |   ├── auth.js
|   |   └── counter.js
|   ├── views/
|   |   ├── admin/
|   |   |   ├── EmpleadosView.vue
|   |   |   ├── HorariosView.vue
|   |   |   ├── ReservasView.vue
|   |   |   └── ServiciosView.vue
|   |   ├── empleado/
|   |   |   ├── HorarioView.vue
|   |   |   └── ReservasView.vue
|   |   ├── DashboardView.vue
|   |   ├── HomeView.vue
|   |   ├── LoginView.vue
|   |   ├── MisReservasView.vue
|   |   ├── NotFoundView.vue
|   |   ├── NuevaReservaView.vue
|   |   ├── RegisterView.vue
|   |   └── ServiciosView.vue
|   ├── App.vue
|   └── main.js
├── index.html
├── jsconfig.json
├── package-lock.json
├── package.json
├── README.md
└── vite.config.js
```

## Sistema de estilos
El proyecto utiliza variables CSS centralizadas para fácil personalización:

**Tema Claro (Light Mode)**
```css
--color-bg: #f7f6f2;
  --color-surface: #ffffff;
  --color-border: #c9c5bd;
  --color-text: #1f2937;
  --color-text-muted: #4b5563;
  --color-primary: #01696f;
  --color-primary-hover: #0c4e54;
  --color-error: #8a1f4d;
  --color-error-bg: #fbe7ef;
  --focus-ring: #01696f;
  --color-focus: var(--focus-ring);
  --color-header-footer: #ffffff;
  --color-primary-button: #fff;

  --color-surface-hover: #f3f1ec;
  --color-surface-soft: #f8f7f4;
  --color-surface-overlay: rgb(0 0 0 / 0.45);

  --color-interactive-secondary-bg: var(--color-surface);
  --color-interactive-secondary-hover: var(--color-surface-hover);
  --color-interactive-secondary-border: var(--color-border);
  --color-interactive-secondary-text: var(--color-text);

  --color-interactive-selected-bg: #d9ebe8;
  --color-interactive-selected-border: #01696f;
  --color-interactive-selected-text: #163235;

  --color-status-success-bg: #d9ebe8;
  --color-status-success-border: #01696f;
  --color-status-success-text: #163235;

  --color-danger-bg: transparent;
  --color-danger-hover: #fbe7ef;
  --color-danger-border: #8a1f4d;
  --color-danger-text: #8a1f4d;

  --radius-sm: 0.5rem;
  --radius-md: 0.75rem;
  --radius-lg: 1rem;

  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-5: 1.25rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-10: 2.5rem;
  --space-12: 3rem;
  --space-14: 3.5rem;

  --shadow-sm: 0 4px 14px rgba(0, 0, 0, 0.06);
  --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 18px 40px rgba(0, 0, 0, 0.12);
```

**Tema Oscuro (Dark Mode)**
```css
--color-bg: #161616;
  --color-surface: #202020;
  --color-border: #3a3a3a;
  --color-text: #f3f4f6;
  --color-text-muted: #c3cad3;
  --color-primary: #4f98a3;
  --color-primary-hover: #68aeba;
  --color-error: #ff7aa8;
  --color-error-bg: #4a2033;
  --focus-ring: #7fc7d4;
  --color-focus: var(--focus-ring);
  --color-header-footer: #323232;
  --color-primary-button: #fff;

  --color-surface-hover: #2a2a2a;
  --color-surface-soft: #252525;
  --color-surface-overlay: rgb(0 0 0 / 0.65);

  --color-interactive-secondary-bg: var(--color-surface);
  --color-interactive-secondary-hover: var(--color-surface-hover);
  --color-interactive-secondary-border: var(--color-border);
  --color-interactive-secondary-text: var(--color-text);

  --color-interactive-selected-bg: #2f454a;
  --color-interactive-selected-border: #7fc7d4;
  --color-interactive-selected-text: #f3f4f6;

  --color-status-success-bg: #243b3e;
  --color-status-success-border: #7fc7d4;
  --color-status-success-text: #f3f4f6;

  --color-danger-bg: transparent;
  --color-danger-hover: #4a2033;
  --color-danger-border: #ff7aa8;
  --color-danger-text: #ff9fc0;

  --radius-sm: 0.5rem;
  --radius-md: 0.75rem;
  --radius-lg: 1rem;

  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-5: 1.25rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-10: 2.5rem;
  --space-12: 3rem;
  --space-14: 3.5rem;


  --shadow-sm: 0 4px 14px rgba(0, 0, 0, 0.3);
  --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.38);
  --shadow-lg: 0 18px 40px rgba(0, 0, 0, 0.45);
```

También existes estas variables:
```css
@media (prefers-color-scheme: dark) {
  :root:not([data-theme='light']) {
    --color-bg: #161616;
    --color-surface: #202020;
    --color-border: #3a3a3a;
    --color-text: #f3f4f6;
    --color-text-muted: #c3cad3;
    --color-primary: #4f98a3;
    --color-primary-hover: #68aeba;
    --color-error: #ff7aa8;
    --color-error-bg: #4a2033;
    --focus-ring: #7fc7d4;
    --color-focus: var(--focus-ring);

    --radius-sm: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;

    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-5: 1.25rem;
    --space-6: 1.5rem;
    --space-8: 2rem;

    --shadow-sm: 0 4px 14px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.38);
    --shadow-lg: 0 18px 40px rgba(0, 0, 0, 0.45);
  }
}
```

**Mixins SCSS**
```scss
@use './variables' as *;

@mixin focus-ring {
  outline: 3px solid var(--focus-ring);
  outline-offset: 3px;
}

@mixin input-base {
  min-height: 2.75rem;
  padding: 0 var(--space-4);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background-color: #fff;
  color: var(--color-text);
}

@mixin respond-up($breakpoint) {
  @if $breakpoint == sm {
    @media (min-width: $bp-sm) {
      @content;
    }
  } @else if $breakpoint == md {
    @media (min-width: $bp-md) {
      @content;
    }
  } @else if $breakpoint == lg {
    @media (min-width: $bp-lg) {
      @content;
    }
  } @else if $breakpoint == xl {
    @media (min-width: $bp-xl) {
      @content;
    }
  } @else if $breakpoint == 2xl {
    @media (min-width: $bp-2xl) {
      @content;
    }
  } @else if $breakpoint == 3xl {
    @media (min-width: $bp-3xl) {
      @content;
    }
  }
}
```

## Componentes Principales
### Views (Vistas)
- DashboardView.vue - Perfil de usuario
- HomeView.vue - Página de inicio
- LoginView.vue - Página de Inicio de sesión
- MisReservasView.vue - Página para las reservas del cliente
- NotFoundView.vue - Página de error 404
- NuevaReservaView.vue - Página para hacer reservas
- RegisterView.vue - Página de registro
- ServiciosView.vue - Página para ver los servicios como cliente
- HorarioView.vue - Página del empleado para consultar su horario
- ReservasView.vue - Página del empleado apra ver las reservas
- EmpleadosView.vue - Página del administrador para gestionar empleados
- HorariosView.vue - Página del administrador para gestionar los horarios de los empleados
- ReservasView.vue - Página del administrador para gestionar las reservas del centro
- ServiciosView.vue - Página del administrador para gestionar los servicios

## Configuración del API
Cliente HTTP configurado en `src/api/axios.js`:
```javascript
import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

export default api
```

## Diseño Responsive
El proyecto usa estos breakpoints:
- Parte de 375px (tamaño de móviles pequeños)
- Gestiona tamaños mobiles normales-grandes: 375px - 768px
- También tamaños tablet: 768px - 1024px
- Tamaño escritorio: >1024px

## Modo Oscuro
El modo oscuro esta implementado completamente en el proyecto en `src/composables/useTheme.js`
```javascript
import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'theme'

export const useTheme = () => {
    const theme = ref('light')
    const isThemeReady = ref(false)

    const applyTheme = (value) => {
        theme.value = value
        document.documentElement.setAttribute('data-theme', value)
        localStorage.setItem(STORAGE_KEY, value)
    }

    const initTheme = () => {
        const storedTheme = localStorage.getItem(STORAGE_KEY)

        if (storedTheme === 'light' || storedTheme === 'dark') {
            applyTheme(storedTheme)
            isThemeReady.value = true
            return
        }

        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        applyTheme(prefersDark ? 'dark' : 'light')
        isThemeReady.value = true
    }

    const toggleTheme = () => {
        applyTheme(theme.value === 'dark' ? 'light' : 'dark')
    }

    onMounted(() => {
        initTheme()
    })

    return {
        theme,
        isThemeReady,
        toggleTheme,
    }
}
```