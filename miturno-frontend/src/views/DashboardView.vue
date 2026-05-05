<script setup>
import { computed, watch, ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'
import { useForm } from 'vee-validate'
import * as yup from 'yup'
import BaseInput from '@/components/forms/BaseInput.vue'
import DashboardSummaryCard from '@/components/dashboard/DashboardSummaryCard.vue'
import DashboardInfoList from '@/components/dashboard/DashboardInfoList.vue'

const auth = useAuthStore()
const router = useRouter()

// Estados de proceso y feedback.
const saving = ref(false)
const deleting = ref(false)
const error = ref('')
const success = ref('')
const mostrarModal = ref(false)

// Referencias para control de foco en el modal.
const modalPanel = ref(null)
const lastActiveElement = ref(null)

// Esquema de validación del perfil.
// Convierte campos vacíos en null cuando procede y exige confirmación si se cambia la contraseña.
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

// useForm centraliza validación, errores y reseteo del perfil.
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

// Vinculación de campos del formulario.
const [nombre, nombreAttrs] = defineField('nombre')
const [apellidos, apellidosAttrs] = defineField('apellidos')
const [email, emailAttrs] = defineField('email')
const [telefono, telefonoAttrs] = defineField('telefono')
const [fecha_nacimiento, fechaNacimientoAttrs] = defineField('fecha_nacimiento')
const [password, passwordAttrs] = defineField('password')
const [password_confirmation, passwordConfirmationAttrs] = defineField('password_confirmation')

// Carga los valores actuales del usuario en el formulario.
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

// Cuando cambia auth.user, el formulario se sincroniza automáticamente.
watch(
    () => auth.user,
    (user) => {
        inicializarFormulario(user)
    },
    { immediate: true },
)

// Nombre completo para la cabecera.
const nombreCompleto = computed(() => {
    if (!auth.user) return ''
    return `${auth.user.nombre || ''} ${auth.user.apellidos || ''}`.trim()
})

// Formatea fecha de nacimiento a dd-mm-aaaa.
const formatearFechaNacimiento = (fecha) => {
    if (!fecha) return 'No indicada'

    const soloFecha = String(fecha).split('T')[0]
    const [year, month, day] = soloFecha.split('-')

    if (!year || !month || !day) return fecha

    return `${day}-${month}-${year}`
}

const badgeRol = computed(() => auth.user?.rol || '')
const puedeEliminarCuenta = computed(() => auth.isCliente)

// Limpia mensajes globales.
const limpiarMensajes = () => {
    error.value = ''
    success.value = ''
}

// Obtiene elementos enfocables dentro del modal.
const getFocusableElements = (container) => {
    if (!container) return []
    return [
        ...container.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    ]
}

// Enfoca el primer elemento del modal tras renderizarlo.
const enfocarPrimerElementoModal = async () => {
    await nextTick()

    const focusables = getFocusableElements(modalPanel.value)
    if (focusables.length) {
        focusables[0].focus()
    } else {
        modalPanel.value?.focus?.()
    }
}

// Abre el modal de edición guardando el foco previo.
const abrirModalEdicion = async () => {
    limpiarMensajes()
    inicializarFormulario(auth.user)
    lastActiveElement.value = document.activeElement
    mostrarModal.value = true
    await enfocarPrimerElementoModal()
}

// Cierra el modal y devuelve el foco al elemento que lo abrió.
const cerrarModalEdicion = async () => {
    mostrarModal.value = false
    inicializarFormulario(auth.user)
    await nextTick()
    lastActiveElement.value?.focus?.()
}

// Envía el formulario validado al backend.
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

// Cierra sesión y redirige al login.
const logout = async () => {
    await auth.logout()
    router.push('/login')
}

// Elimina la cuenta si el usuario tiene permiso.
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

// Atajos de teclado del modal: Escape cierra y Tab mantiene foco atrapado.
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

// Saca las iniciales del usuario.
const inicialesUsuario = computed(() => {
    if (!auth.user) return ''
    return `${auth.user.nombre?.charAt(0) || ''}${auth.user.apellidos?.charAt(0) || ''}`.toUpperCase()
})

const infoItems = computed(() => {
    if (!auth.user) return []

    return [
        { label: 'Nombre', value: auth.user.nombre || 'No indicado' },
        { label: 'Apellidos', value: auth.user.apellidos || 'No indicado' },
        { label: 'Email', value: auth.user.email || 'No indicado' },
        { label: 'Teléfono', value: auth.user.telefono || 'No indicado' },
        {
            label: 'Fecha de nacimiento',
            value: formatearFechaNacimiento(auth.user.fecha_nacimiento),
        },
        { label: 'Rol', value: auth.user.rol || 'No indicado' },
        { label: 'Estado', value: auth.user.activo ? 'Activo' : 'Inactivo' },
    ]
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

                <DashboardSummaryCard
                v-if="auth.user"
                :initials="inicialesUsuario"
                :full-name="nombreCompleto"
                :email="auth.user.email"
                />
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

                <DashboardInfoList :items="infoItems" />

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
                            <BaseInput
                                id="nombre"
                                v-model="nombre"
                                name="nombre"
                                label="Nombre"
                                type="text"
                                :attrs="nombreAttrs"
                                :error="errors.nombre"
                            />

                            <BaseInput
                                id="apellidos"
                                v-model="apellidos"
                                name="apellidos"
                                label="Apellidos"
                                type="text"
                                :attrs="apellidosAttrs"
                                :error="errors.apellidos"
                            />

                            <div class="dashboard-profile__field dashboard-profile__field--full">
                                <BaseInput
                                id="email"
                                v-model="email"
                                name="email"
                                label="Email"
                                type="email"
                                :attrs="emailAttrs"
                                :error="errors.email"
                                />
                            </div>

                            <BaseInput
                                id="telefono"
                                v-model="telefono"
                                name="telefono"
                                label="Teléfono"
                                type="tel"
                                placeholder="Opcional"
                                :attrs="telefonoAttrs"
                                :error="errors.telefono"
                            />

                            <BaseInput
                                id="fecha_nacimiento"
                                v-model="fecha_nacimiento"
                                name="fecha_nacimiento"
                                label="Fecha de nacimiento"
                                type="date"
                                :attrs="fechaNacimientoAttrs"
                                :error="errors.fecha_nacimiento"
                            />

                            <BaseInput
                                id="password"
                                v-model="password"
                                name="password"
                                label="Nueva contraseña"
                                type="password"
                                placeholder="Déjala vacía para no cambiarla"
                                :attrs="passwordAttrs"
                                :error="errors.password"
                            />

                            <BaseInput
                                id="password_confirmation"
                                v-model="password_confirmation"
                                name="password_confirmation"
                                label="Confirmar contraseña"
                                type="password"
                                placeholder="Repite la nueva contraseña"
                                :attrs="passwordConfirmationAttrs"
                                :error="errors.password_confirmation"
                            />
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
