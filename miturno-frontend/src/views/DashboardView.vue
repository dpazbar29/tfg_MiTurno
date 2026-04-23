<script setup>
import { computed, watch, ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'
import { useForm } from 'vee-validate'
import * as yup from 'yup'

const auth = useAuthStore()
const router = useRouter()

const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const success = ref('')
const mostrarModal = ref(false)

const modalPanel = ref(null)
const lastActiveElement = ref(null)

const profileSchema = yup.object({
  nombre: yup
    .string()
    .required('El nombre es obligatorio.')
    .max(255, 'El nombre no puede superar 255 caracteres.'),
  apellidos: yup
    .string()
    .required('Los apellidos son obligatorios.')
    .max(255, 'Los apellidos no pueden superar 255 caracteres.'),
  email: yup
    .string()
    .required('El email es obligatorio.')
    .email('Introduce un email válido.')
    .max(255, 'El email no puede superar 255 caracteres.'),
  telefono: yup
    .string()
    .nullable()
    .transform((value) => (value === '' ? null : value))
    .max(255, 'El teléfono no puede superar 255 caracteres.'),
  fecha_nacimiento: yup
    .string()
    .nullable()
    .transform((value) => (value === '' ? null : value)),
  password: yup
    .string()
    .nullable()
    .transform((value) => (value === '' ? null : value))
    .min(6, 'La contraseña debe tener al menos 6 caracteres.'),
  password_confirmation: yup
    .string()
    .nullable()
    .transform((value) => (value === '' ? null : value))
    .when('password', {
      is: (value) => !!value,
      then: (schema) =>
        schema
          .required('Debes confirmar la nueva contraseña.')
          .oneOf([yup.ref('password')], 'Las contraseñas no coinciden.'),
      otherwise: (schema) => schema.nullable(),
    }),
})

const {
  errors,
  defineField,
  handleSubmit,
  resetForm,
  setErrors,
} = useForm({
  validationSchema: profileSchema,
  initialValues: {
    nombre: '',
    apellidos: '',
    email: '',
    telefono: '',
    fecha_nacimiento: '',
    password: '',
    password_confirmation: '',
  },
})

const [nombre, nombreAttrs] = defineField('nombre')
const [apellidos, apellidosAttrs] = defineField('apellidos')
const [email, emailAttrs] = defineField('email')
const [telefono, telefonoAttrs] = defineField('telefono')
const [fecha_nacimiento, fechaNacimientoAttrs] = defineField('fecha_nacimiento')
const [password, passwordAttrs] = defineField('password')
const [password_confirmation, passwordConfirmationAttrs] = defineField('password_confirmation')

const inicializarFormulario = (user) => {
  resetForm({
    values: {
      nombre: user?.nombre || '',
      apellidos: user?.apellidos || '',
      email: user?.email || '',
      telefono: user?.telefono || '',
      fecha_nacimiento: user?.fecha_nacimiento || '',
      password: '',
      password_confirmation: '',
    },
  })
}

watch(
  () => auth.user,
  (user) => {
    inicializarFormulario(user)
  },
  { immediate: true },
)

const nombreCompleto = computed(() => {
  if (!auth.user) return ''
  return `${auth.user.nombre || ''} ${auth.user.apellidos || ''}`.trim()
})

const formatearFechaNacimiento = (fecha) => {
  if (!fecha) return 'No indicada'

  const soloFecha = String(fecha).split('T')[0]
  const [year, month, day] = soloFecha.split('-')

  if (!year || !month || !day) return fecha

  return `${day}-${month}-${year}`
}

const badgeRol = computed(() => auth.user?.rol || '')
const puedeEliminarCuenta = computed(() => auth.isCliente)

const limpiarMensajes = () => {
  error.value = ''
  success.value = ''
}

const getFocusableElements = (container) => {
  if (!container) return []
  return [
    ...container.querySelectorAll(
      'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    ),
  ]
}

const enfocarPrimerElementoModal = async () => {
  await nextTick()

  const focusables = getFocusableElements(modalPanel.value)
  if (focusables.length) {
    focusables[0].focus()
  } else {
    modalPanel.value?.focus?.()
  }
}

const abrirModalEdicion = async () => {
  limpiarMensajes()
  inicializarFormulario(auth.user)
  lastActiveElement.value = document.activeElement
  mostrarModal.value = true
  await enfocarPrimerElementoModal()
}

const cerrarModalEdicion = async () => {
  mostrarModal.value = false
  inicializarFormulario(auth.user)
  await nextTick()
  lastActiveElement.value?.focus?.()
}

const guardarPerfil = handleSubmit(async (values) => {
  limpiarMensajes()
  saving.value = true

  try {
    const payload = {
      nombre: values.nombre.trim(),
      apellidos: values.apellidos.trim(),
      email: values.email.trim(),
      telefono: values.telefono?.trim?.() || values.telefono || null,
      fecha_nacimiento: values.fecha_nacimiento || null,
    }

    if (values.password) {
      payload.password = values.password
      payload.password_confirmation = values.password_confirmation
    }

    const { data } = await api.put('/me', payload)

    auth.setUser(data)
    inicializarFormulario(data)
    success.value = 'Tu perfil se ha actualizado correctamente.'
    mostrarModal.value = false

    await nextTick()
    lastActiveElement.value?.focus?.()
  } catch (err) {
    console.error(err.response?.data || err)

    const backendErrors = err.response?.data?.errors

    if (backendErrors) {
      const formattedErrors = Object.fromEntries(
        Object.entries(backendErrors).map(([key, value]) => [key, value[0]]),
      )

      setErrors(formattedErrors)
      error.value = 'Revisa los campos marcados en el formulario.'
      await enfocarPrimerElementoModal()
    } else {
      error.value = err.response?.data?.message || 'No se pudo actualizar el perfil.'
    }
  } finally {
    saving.value = false
  }
})

const logout = async () => {
  await auth.logout()
  router.push('/login')
}

const eliminarCuenta = async () => {
  if (!puedeEliminarCuenta.value) return

  const confirmado = window.confirm(
    '¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.',
  )

  if (!confirmado) return

  limpiarMensajes()
  deleting.value = true

  try {
    await api.delete('/me')
    await auth.logout()
    router.push('/register')
  } catch (err) {
    console.error(err.response?.data || err)
    error.value = err.response?.data?.message || 'No se pudo eliminar la cuenta.'
  } finally {
    deleting.value = false
  }
}

const manejarTecladoModal = (event) => {
  if (!mostrarModal.value || !modalPanel.value) return

  if (event.key === 'Escape') {
    event.preventDefault()
    cerrarModalEdicion()
    return
  }

  if (event.key !== 'Tab') return

  const focusables = getFocusableElements(modalPanel.value)
  if (!focusables.length) return

  const first = focusables[0]
  const last = focusables[focusables.length - 1]

  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

onMounted(() => {
  document.addEventListener('keydown', manejarTecladoModal)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', manejarTecladoModal)
})
</script>

<template>
  <main class="dashboard-profile">
    <section class="dashboard-profile__container" aria-labelledby="dashboard-profile-title">
      <header class="dashboard-profile__hero">
        <div class="dashboard-profile__hero-content">
          <p class="dashboard-profile__eyebrow">Mi cuenta</p>
          <h1 id="dashboard-profile-title" class="dashboard-profile__title">
            Perfil de usuario
          </h1>
          <p class="dashboard-profile__subtitle" v-if="auth.user">
            Consulta tu información personal y gestiona tu cuenta.
          </p>
        </div>

        <div class="dashboard-profile__summary-card" v-if="auth.user">
          <span class="dashboard-profile__avatar" aria-hidden="true">
            {{ auth.user.nombre?.charAt(0) }}{{ auth.user.apellidos?.charAt(0) }}
          </span>

          <div class="dashboard-profile__summary-text">
            <strong>{{ nombreCompleto }}</strong>
            <span>{{ auth.user.email }}</span>
          </div>
        </div>
      </header>

      <p
        v-if="error"
        class="dashboard-profile__message dashboard-profile__message--error"
        role="alert"
        aria-live="assertive"
      >
        {{ error }}
      </p>

      <p
        v-if="success"
        class="dashboard-profile__message dashboard-profile__message--success"
        role="status"
        aria-live="polite"
      >
        {{ success }}
      </p>

      <section class="dashboard-profile__card dashboard-profile__card--main" v-if="auth.user">
        <header class="dashboard-profile__card-header">
          <h2 class="dashboard-profile__card-title">Información personal</h2>
          <p class="dashboard-profile__card-description">
            Estos son los datos principales asociados a tu cuenta.
          </p>
        </header>

        <dl class="dashboard-profile__info-list">
          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Nombre</dt>
            <dd class="dashboard-profile__info-value">{{ auth.user.nombre }}</dd>
          </div>

          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Apellidos</dt>
            <dd class="dashboard-profile__info-value">{{ auth.user.apellidos }}</dd>
          </div>

          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Email</dt>
            <dd class="dashboard-profile__info-value">{{ auth.user.email }}</dd>
          </div>

          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Teléfono</dt>
            <dd class="dashboard-profile__info-value">
              {{ auth.user.telefono || 'No indicado' }}
            </dd>
          </div>

          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Fecha de nacimiento</dt>
            <dd class="dashboard-profile__info-value">
              {{ formatearFechaNacimiento(auth.user.fecha_nacimiento) }}
            </dd>
          </div>

          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Rol</dt>
            <dd class="dashboard-profile__info-value">{{ auth.user.rol }}</dd>
          </div>

          <div class="dashboard-profile__info-item">
            <dt class="dashboard-profile__info-label">Estado</dt>
            <dd class="dashboard-profile__info-value">
              {{ auth.user.activo ? 'Activo' : 'Inactivo' }}
            </dd>
          </div>
        </dl>

        <div class="dashboard-profile__actions">
          <button
            type="button"
            class="dashboard-profile__button dashboard-profile__button--primary"
            @click="abrirModalEdicion"
            :disabled="saving || deleting"
          >
            Editar perfil
          </button>

          <button
            type="button"
            class="dashboard-profile__button dashboard-profile__button--secondary"
            @click="logout"
            :disabled="saving || deleting"
          >
            Cerrar sesión
          </button>

          <button
            v-if="puedeEliminarCuenta"
            type="button"
            class="dashboard-profile__button dashboard-profile__button--danger"
            @click="eliminarCuenta"
            :disabled="saving || deleting"
          >
            {{ deleting ? 'Eliminando...' : 'Eliminar cuenta' }}
          </button>
        </div>
      </section>

      <div
        v-if="mostrarModal"
        class="dashboard-profile__modal"
        @click.self="cerrarModalEdicion"
      >
        <div
          ref="modalPanel"
          class="dashboard-profile__modal-dialog"
          role="dialog"
          aria-modal="true"
          aria-labelledby="dashboard-profile-modal-title"
          aria-describedby="dashboard-profile-modal-description"
          tabindex="-1"
        >
          <header class="dashboard-profile__modal-header">
            <div class="dashboard-profile__modal-heading">
              <h2 id="dashboard-profile-modal-title" class="dashboard-profile__modal-title">
                Editar perfil
              </h2>
              <p
                id="dashboard-profile-modal-description"
                class="dashboard-profile__modal-description"
              >
                Actualiza tus datos personales y tu contraseña si lo necesitas.
              </p>
            </div>

            <button
              type="button"
              class="dashboard-profile__modal-close"
              @click="cerrarModalEdicion"
              :disabled="saving"
              aria-label="Cerrar modal"
            >
              ×
            </button>
          </header>

          <form class="dashboard-profile__form" @submit.prevent="guardarPerfil">
            <div class="dashboard-profile__form-grid">
              <label class="dashboard-profile__field">
                <span class="dashboard-profile__field-label">Nombre</span>
                <input
                  v-model="nombre"
                  v-bind="nombreAttrs"
                  type="text"
                  class="dashboard-profile__input"
                />
                <small v-if="errors.nombre" class="dashboard-profile__field-error">
                  {{ errors.nombre }}
                </small>
              </label>

              <label class="dashboard-profile__field">
                <span class="dashboard-profile__field-label">Apellidos</span>
                <input
                  v-model="apellidos"
                  v-bind="apellidosAttrs"
                  type="text"
                  class="dashboard-profile__input"
                />
                <small v-if="errors.apellidos" class="dashboard-profile__field-error">
                  {{ errors.apellidos }}
                </small>
              </label>

              <label class="dashboard-profile__field dashboard-profile__field--full">
                <span class="dashboard-profile__field-label">Email</span>
                <input
                  v-model="email"
                  v-bind="emailAttrs"
                  type="email"
                  class="dashboard-profile__input"
                />
                <small v-if="errors.email" class="dashboard-profile__field-error">
                  {{ errors.email }}
                </small>
              </label>

              <label class="dashboard-profile__field">
                <span class="dashboard-profile__field-label">Teléfono</span>
                <input
                  v-model="telefono"
                  v-bind="telefonoAttrs"
                  type="tel"
                  placeholder="Opcional"
                  class="dashboard-profile__input"
                />
                <small v-if="errors.telefono" class="dashboard-profile__field-error">
                  {{ errors.telefono }}
                </small>
              </label>

              <label class="dashboard-profile__field">
                <span class="dashboard-profile__field-label">Fecha de nacimiento</span>
                <input
                  v-model="fecha_nacimiento"
                  v-bind="fechaNacimientoAttrs"
                  type="date"
                  class="dashboard-profile__input"
                />
                <small v-if="errors.fecha_nacimiento" class="dashboard-profile__field-error">
                  {{ errors.fecha_nacimiento }}
                </small>
              </label>

              <label class="dashboard-profile__field">
                <span class="dashboard-profile__field-label">Nueva contraseña</span>
                <input
                  v-model="password"
                  v-bind="passwordAttrs"
                  type="password"
                  placeholder="Déjala vacía para no cambiarla"
                  class="dashboard-profile__input"
                />
                <small v-if="errors.password" class="dashboard-profile__field-error">
                  {{ errors.password }}
                </small>
              </label>

              <label class="dashboard-profile__field">
                <span class="dashboard-profile__field-label">Confirmar contraseña</span>
                <input
                  v-model="password_confirmation"
                  v-bind="passwordConfirmationAttrs"
                  type="password"
                  placeholder="Repite la nueva contraseña"
                  class="dashboard-profile__input"
                />
                <small
                  v-if="errors.password_confirmation"
                  class="dashboard-profile__field-error"
                >
                  {{ errors.password_confirmation }}
                </small>
              </label>
            </div>

            <div class="dashboard-profile__actions">
              <button
                type="submit"
                class="dashboard-profile__button dashboard-profile__button--primary"
                :disabled="saving"
              >
                {{ saving ? 'Guardando...' : 'Guardar cambios' }}
              </button>

              <button
                type="button"
                class="dashboard-profile__button dashboard-profile__button--secondary"
                @click="cerrarModalEdicion"
                :disabled="saving"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>
</template>
