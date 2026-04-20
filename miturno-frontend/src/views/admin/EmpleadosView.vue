<script setup>
import { ref, onMounted, computed, nextTick, onBeforeUnmount } from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'
import { getEmpleados, getEmpleado, createEmpleado, syncServiciosEmpleado, updateEmpleado, deleteEmpleado, } from '../../api/empleados'
import { getServicios } from '../../api/servicios'

const empleados = ref([])
const servicios = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingEmployeeId = ref(null)

const empleadoEditando = ref(null)
const empleadoPerfilEditando = ref(null)
const mostrarModalPerfil = ref(false)
const modoPerfil = ref('crear')

const serviciosSeleccionados = ref([])

const error = ref(null)
const success = ref(null)

const modalRef = ref(null)
const modalTitleRef = ref(null)
const editModalRef = ref(null)
const editModalTitleRef = ref(null)
const lastTriggerRef = ref(null)

const employeeSchema = yup.object({
    nombre: yup
        .string()
        .trim()
        .required('El nombre es obligatorio.'),
    apellidos: yup
        .string()
        .trim()
        .required('Los apellidos son obligatorios.'),
    email: yup
        .string()
        .trim()
        .required('El correo electrónico es obligatorio.')
        .email('Introduce un correo electrónico válido.'),
    telefono: yup
        .string()
        .nullable()
        .test(
            'telefono-valido',
            'Introduce un teléfono válido.',
            (value) => !value || /^[0-9+\s()-]{6,20}$/.test(value),
        ),
    password: yup
        .string()
        .transform((value) => value ?? '')
        .test('password-condicional', 'La contraseña debe tener al menos 8 caracteres.', function (value) {
        const esCrear = modoPerfil.value === 'crear'
            if (esCrear) return !!value && value.length >= 8
            if (!value) return true
            return value.length >= 8
        }),
    especialidades: yup
        .string()
        .nullable(),
    fecha_contratacion: yup
        .string()
        .required('La fecha de contratación es obligatoria.'),
    activo: yup
        .boolean()
        .required(),
    fecha_nacimiento: yup
        .string()
        .nullable()
        .test('fecha-nacimiento-valida', 'La fecha de nacimiento no es válida.', (value) => {
            if (!value) return true
            return !Number.isNaN(new Date(value).getTime())
        }),
})

const {
    defineField,
    handleSubmit,
    errors: editErrors,
    resetForm,
    setErrors,
} = useForm({
    validationSchema: employeeSchema,
    initialValues: {
        nombre: '',
        apellidos: '',
        email: '',
        telefono: '',
        password: '',
        especialidades: '',
        fecha_contratacion: '',
        activo: true,
        fecha_nacimiento: '',
    },
})

const [nombre, nombreAttrs] = defineField('nombre')
const [apellidos, apellidosAttrs] = defineField('apellidos')
const [email, emailAttrs] = defineField('email')
const [telefono, telefonoAttrs] = defineField('telefono')
const [password, passwordAttrs] = defineField('password')
const [especialidades, especialidadesAttrs] = defineField('especialidades')
const [fechaContratacion, fechaContratacionAttrs] = defineField('fecha_contratacion')
const [activo, activoAttrs] = defineField('activo')
const [fechaNacimiento, fechaNacimientoAttrs] = defineField('fecha_nacimiento')

const tituloModalPerfil = computed(() =>
    modoPerfil.value === 'crear'
        ? 'Nuevo empleado'
        : `Editar perfil de ${empleadoPerfilEditando.value?.usuario?.nombre || ''} ${empleadoPerfilEditando.value?.usuario?.apellidos || ''}`.trim(),
)

const descripcionModalPerfil = computed(() =>
    modoPerfil.value === 'crear'
        ? 'Completa los datos básicos para crear un nuevo empleado.'
        : 'Modifica los datos básicos del usuario y la información propia del empleado.',
)

const cargarDatos = async () => {
    loading.value = true
    error.value = null

    try {
        const [listaEmpleados, listaServicios] = await Promise.all([
            getEmpleados(),
            getServicios(),
        ])
        empleados.value = listaEmpleados
        servicios.value = listaServicios
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron cargar los empleados o los servicios.'
    } finally {
        loading.value = false
    }
}

const abrirModal = async (empleado, event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null

    try {
        empleadoEditando.value = await getEmpleado(empleado.id)
        serviciosSeleccionados.value = empleadoEditando.value.servicios.map((s) => s.id)

        await nextTick()
        modalTitleRef.value?.focus()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo cargar la información del empleado.'
    }
}

const cerrarModal = () => {
    empleadoEditando.value = null
    serviciosSeleccionados.value = []
    lastTriggerRef.value?.focus?.()
}

const abrirModalCrear = async (event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoPerfil.value = 'crear'
    empleadoPerfilEditando.value = null
    mostrarModalPerfil.value = true

    resetForm({
        values: {
            nombre: '',
            apellidos: '',
            email: '',
            telefono: '',
            password: '',
            especialidades: '',
            fecha_contratacion: '',
            activo: true,
            fecha_nacimiento: '',
        },
        errors: {},
    })

    await nextTick()
    editModalTitleRef.value?.focus()
}

const abrirModalEdicion = async (empleado, event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoPerfil.value = 'editar'

    try {
        empleadoPerfilEditando.value = await getEmpleado(empleado.id)
        mostrarModalPerfil.value = true

        resetForm({
            values: {
                nombre: empleadoPerfilEditando.value.usuario?.nombre || '',
                apellidos: empleadoPerfilEditando.value.usuario?.apellidos || '',
                email: empleadoPerfilEditando.value.usuario?.email || '',
                telefono: empleadoPerfilEditando.value.usuario?.telefono || '',
                password: '',
                especialidades: empleadoPerfilEditando.value.especialidades || '',
                fecha_contratacion: empleadoPerfilEditando.value.fecha_contratacion || '',
                activo: !!empleadoPerfilEditando.value.activo,
                fecha_nacimiento: empleadoPerfilEditando.value.usuario?.fecha_nacimiento || '',
            },
            errors: {},
        })

        await nextTick()
        editModalTitleRef.value?.focus()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo cargar el perfil del empleado.'
    }
}

const cerrarModalEdicion = () => {
    mostrarModalPerfil.value = false
    empleadoPerfilEditando.value = null
    modoPerfil.value = 'crear'

    resetForm({
        values: {
            nombre: '',
            apellidos: '',
            email: '',
            telefono: '',
            password: '',
            especialidades: '',
            fecha_contratacion: '',
            activo: true,
        },
        errors: {},
    })

    lastTriggerRef.value?.focus?.()
}

const guardarServicios = async () => {
    if (!empleadoEditando.value) return

    saving.value = true
    error.value = null
    success.value = null

    try {
        await syncServiciosEmpleado(
            empleadoEditando.value.id,
            serviciosSeleccionados.value,
        )

        await cargarDatos()
        cerrarModal()
        success.value = 'Servicios actualizados correctamente.'
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron guardar los cambios de servicios.'
    } finally {
        saving.value = false
    }
}

const guardarPerfil = handleSubmit(async (values) => {
    saving.value = true
    error.value = null
    success.value = null

    try {
        const payload = {
            ...values,
            rol: 'empleado',
        }

        if (modoPerfil.value === 'editar' && !payload.password) {
            delete payload.password
        }

        if (modoPerfil.value === 'crear') {
            await createEmpleado(payload)
            success.value = 'Empleado creado correctamente.'
        } else {
            await updateEmpleado(empleadoPerfilEditando.value.id, payload)
            success.value = 'Perfil del empleado actualizado correctamente.'
        }

        await cargarDatos()
        cerrarModalEdicion()
    } catch (err) {
        console.error(err.response?.data || err)

        const backendErrors = err.response?.data?.errors

        if (backendErrors) {
            setErrors({
                nombre: backendErrors.nombre?.[0],
                apellidos: backendErrors.apellidos?.[0],
                email: backendErrors.email?.[0],
                telefono: backendErrors.telefono?.[0],
                password: backendErrors.password?.[0],
                especialidades: backendErrors.especialidades?.[0],
                fecha_contratacion: backendErrors.fecha_contratacion?.[0],
                activo: backendErrors.activo?.[0],
            })
        } else {
            error.value =
                modoPerfil.value === 'crear'
                    ? 'No se pudo crear el empleado.'
                    : 'No se pudo actualizar el perfil del empleado.'
        }
    } finally {
        saving.value = false
    }
})

const eliminarEmpleadoConfirmado = async (empleado) => {
    const nombreCompleto = `${empleado.usuario?.nombre || ''} ${empleado.usuario?.apellidos || ''}`.trim()

    if (!window.confirm(`¿Seguro que quieres eliminar a ${nombreCompleto}?`)) return

    deletingEmployeeId.value = empleado.id
    error.value = null
    success.value = null

    try {
        await deleteEmpleado(empleado.id)
        await cargarDatos()
        success.value = 'Empleado eliminado correctamente.'
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudo eliminar el empleado.'
    } finally {
        deletingEmployeeId.value = null
    }
}

const serviciosAsignados = computed(() => {
    if (!empleadoEditando.value) return []
    return servicios.value.filter((s) => serviciosSeleccionados.value.includes(s.id))
})

const serviciosDisponibles = computed(() => {
    if (!empleadoEditando.value) return []
    return servicios.value.filter((s) => !serviciosSeleccionados.value.includes(s.id))
})

const formatearFecha = (fecha) => {
    return new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'medium',
    }).format(new Date(fecha))
}

const getFocusableElements = (container) => {
    if (!container) return []

    return container.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

const manejarTabEnContenedor = (event, container) => {
    const focusableElements = [...getFocusableElements(container)]
    if (!focusableElements.length) return

    const firstElement = focusableElements[0]
    const lastElement = focusableElements[focusableElements.length - 1]

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
    }
}

const manejarTecladoModal = (event) => {
    const hayModalServicios = !!empleadoEditando.value
    const hayModalPerfil = modoPerfil.value === 'crear' || !!empleadoPerfilEditando.value

    if (!hayModalServicios && !hayModalPerfil) return

    if (event.key === 'Escape') {
        if (hayModalPerfil && editModalRef.value) {
            cerrarModalEdicion()
            return
        }

        if (hayModalServicios) {
            cerrarModal()
            return
        }
    }

    if (event.key === 'Tab') {
        if (hayModalPerfil && editModalRef.value) {
            manejarTabEnContenedor(event, editModalRef.value)
            return
        }

        if (hayModalServicios && modalRef.value) {
            manejarTabEnContenedor(event, modalRef.value)
        }
    }
}

onMounted(cargarDatos)
onMounted(() => document.addEventListener('keydown', manejarTecladoModal))
onBeforeUnmount(() => document.removeEventListener('keydown', manejarTecladoModal))
</script>

<template>
    <main class="admin-employees">
        <section class="admin-employees__container" aria-labelledby="admin-employees-title">
            <header class="admin-employees__header">
                <div class="admin-employees__heading">
                    <h1 id="admin-employees-title" class="admin-employees__title">
                        Gestión de empleados
                    </h1>
                    <p class="admin-employees__intro">
                        Consulta empleados, crea nuevos perfiles, edita sus datos y asigna servicios.
                    </p>
                </div>

                <div class="admin-employees__toolbar">
                    <button
                        type="button"
                        class="admin-employees__button admin-employees__button--primary"
                        @click="abrirModalCrear($event)"
                        :disabled="saving || deletingEmployeeId !== null"
                    >
                        Nuevo empleado
                    </button>
                </div>
            </header>

            <p
                v-if="loading"
                class="admin-employees__status"
                role="status"
                aria-live="polite"
            >
                Cargando empleados...
            </p>

            <p
                v-if="error"
                class="admin-employees__message admin-employees__message--error"
                role="alert"
                aria-live="assertive"
            >
                {{ error }}
            </p>

            <p
                v-if="success"
                class="admin-employees__message admin-employees__message--success"
                role="status"
                aria-live="polite"
            >
                {{ success }}
            </p>

            <div
                v-if="!loading && empleados.length"
                class="admin-employees__grid"
            >
                <article
                    v-for="empleado in empleados"
                    :key="empleado.id"
                    class="employee-card"
                    :aria-labelledby="`employee-card-title-${empleado.id}`"
                >
                    <header class="employee-card__header">
                        <h2
                            :id="`employee-card-title-${empleado.id}`"
                            class="employee-card__title"
                        >
                            {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                        </h2>
                    </header>

                    <dl class="employee-card__meta">
                        <div class="employee-card__meta-item">
                            <dt>Correo</dt>
                            <dd>{{ empleado.usuario?.email || 'Sin correo' }}</dd>
                        </div>

                        <div class="employee-card__meta-item">
                            <dt>Teléfono</dt>
                            <dd>{{ empleado.usuario?.telefono || 'Sin teléfono' }}</dd>
                        </div>

                        <div class="employee-card__meta-item">
                            <dt>Contratación</dt>
                            <dd>
                                <time :datetime="empleado.fecha_contratacion">
                                    {{ formatearFecha(empleado.fecha_contratacion) }}
                                </time>
                            </dd>
                        </div>

                        <div class="employee-card__meta-item">
                            <dt>Activo</dt>
                            <dd>{{ empleado.activo ? 'Sí' : 'No' }}</dd>
                        </div>

                        <div class="employee-card__meta-item">
                            <dt>Servicios</dt>
                            <dd>{{ empleado.servicios?.length || 0 }}</dd>
                        </div>
                    </dl>

                    <div
                        v-if="empleado.servicios?.length"
                        class="employee-card__services"
                    >
                        <h3 class="employee-card__services-title">Servicios asignados</h3>
                        <ul class="employee-card__services-list" role="list">
                            <li
                                v-for="servicio in empleado.servicios"
                                :key="servicio.id"
                                class="employee-card__services-item"
                            >
                                {{ servicio.nombre }}
                            </li>
                        </ul>
                    </div>

                    <p v-else class="employee-card__empty">
                        No tiene servicios asignados.
                    </p>

                    <div class="employee-card__actions">
                        <button
                            type="button"
                            class="employee-card__button employee-card__button--secondary"
                            @click="abrirModalEdicion(empleado, $event)"
                            :disabled="saving || deletingEmployeeId !== null"
                        >
                            Editar perfil
                        </button>

                        <button
                            type="button"
                            class="employee-card__button employee-card__button--danger"
                            @click="eliminarEmpleadoConfirmado(empleado)"
                            :disabled="saving || deletingEmployeeId !== null"
                        >
                            {{ deletingEmployeeId === empleado.id ? 'Eliminando...' : 'Eliminar' }}
                        </button>

                        <button
                            type="button"
                            class="employee-card__button employee-card__button--primary"
                            @click="abrirModal(empleado, $event)"
                            :disabled="saving || deletingEmployeeId !== null"
                        >
                            Gestionar servicios
                        </button>
                    </div>
                </article>
            </div>

            <p
                v-else-if="!loading && !empleados.length"
                class="admin-employees__empty"
                role="status"
                aria-live="polite"
            >
                No hay empleados disponibles.
            </p>
        </section>

        <div
            v-if="empleadoEditando"
            class="admin-employees-modal"
            @click.self="cerrarModal"
        >
            <div
                ref="modalRef"
                class="admin-employees-modal__dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="employee-services-title"
                aria-describedby="employee-services-description"
            >
                <header class="admin-employees-modal__header">
                    <h2
                        id="employee-services-title"
                        ref="modalTitleRef"
                        class="admin-employees-modal__title"
                        tabindex="-1"
                    >
                        Servicios de {{ empleadoEditando.usuario?.nombre }} {{ empleadoEditando.usuario?.apellidos }}
                    </h2>

                    <button
                        type="button"
                        class="admin-employees-modal__close"
                        @click="cerrarModal"
                        aria-label="Cerrar gestión de servicios"
                    >
                        ×
                    </button>
                </header>

                <p
                    id="employee-services-description"
                    class="admin-employees-modal__description"
                >
                    Marca o desmarca los servicios que puede atender este empleado.
                </p>

                <form
                    class="admin-employees-form"
                    @submit.prevent="guardarServicios"
                    :aria-busy="saving ? 'true' : 'false'"
                >
                    <fieldset class="admin-employees-form__group">
                        <legend class="admin-employees-form__legend">
                            Servicios asignados
                        </legend>

                        <div
                            v-if="serviciosAsignados.length"
                            class="admin-employees-form__options"
                        >
                            <div
                                v-for="servicio in serviciosAsignados"
                                :key="servicio.id"
                                class="admin-employees-form__option"
                            >
                                <input
                                    :id="`assigned-service-${servicio.id}`"
                                    v-model="serviciosSeleccionados"
                                    class="admin-employees-form__checkbox"
                                    type="checkbox"
                                    :value="servicio.id"
                                />
                                <label
                                    class="admin-employees-form__option-label"
                                    :for="`assigned-service-${servicio.id}`"
                                >
                                    {{ servicio.nombre }}
                                </label>
                            </div>
                        </div>

                        <p v-else class="admin-employees-form__empty">
                            No tiene servicios asignados.
                        </p>
                    </fieldset>

                    <fieldset class="admin-employees-form__group">
                        <legend class="admin-employees-form__legend">
                            Servicios disponibles
                        </legend>

                        <div
                            v-if="serviciosDisponibles.length"
                            class="admin-employees-form__options"
                        >
                            <div
                                v-for="servicio in serviciosDisponibles"
                                :key="servicio.id"
                                class="admin-employees-form__option"
                            >
                                <input
                                    :id="`available-service-${servicio.id}`"
                                    v-model="serviciosSeleccionados"
                                    class="admin-employees-form__checkbox"
                                    type="checkbox"
                                    :value="servicio.id"
                                />
                                <label
                                    class="admin-employees-form__option-label"
                                    :for="`available-service-${servicio.id}`"
                                >
                                    {{ servicio.nombre }}
                                </label>
                            </div>
                        </div>

                        <p v-else class="admin-employees-form__empty">
                            No hay más servicios disponibles.
                        </p>
                    </fieldset>

                    <footer class="admin-employees-form__actions">
                        <button
                            type="button"
                            class="admin-employees-form__button admin-employees-form__button--secondary"
                            @click="cerrarModal"
                            :disabled="saving"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="admin-employees-form__button admin-employees-form__button--primary"
                            :disabled="saving"
                        >
                            {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </footer>
                </form>
            </div>
        </div>

        <div
            v-if="mostrarModalPerfil"
            class="admin-employees-modal"
            @click.self="cerrarModalEdicion"
        >
            <div
                ref="editModalRef"
                class="admin-employees-modal__dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="employee-edit-title"
                aria-describedby="employee-edit-description"
            >
                <header class="admin-employees-modal__header">
                    <h2
                        id="employee-edit-title"
                        ref="editModalTitleRef"
                        class="admin-employees-modal__title"
                        tabindex="-1"
                    >
                        {{ tituloModalPerfil }}
                    </h2>

                    <button
                        type="button"
                        class="admin-employees-modal__close"
                        @click="cerrarModalEdicion"
                        aria-label="Cerrar formulario de empleado"
                    >
                        ×
                    </button>
                </header>

                <p
                    id="employee-edit-description"
                    class="admin-employees-modal__description"
                >
                    {{ descripcionModalPerfil }}
                </p>

                <form
                    class="admin-employees-form"
                    @submit.prevent="guardarPerfil"
                    :aria-busy="saving ? 'true' : 'false'"
                >
                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-nombre">Nombre</label>
                        <input
                            id="employee-nombre"
                            v-model="nombre"
                            v-bind="nombreAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.nombre }"
                            type="text"
                        />
                        <p v-if="editErrors.nombre" class="admin-employees-form__error">
                            {{ editErrors.nombre }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-apellidos">Apellidos</label>
                        <input
                            id="employee-apellidos"
                            v-model="apellidos"
                            v-bind="apellidosAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.apellidos }"
                            type="text"
                        />
                        <p v-if="editErrors.apellidos" class="admin-employees-form__error">
                            {{ editErrors.apellidos }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-fecha-nacimiento">
                            Fecha de nacimiento
                        </label>
                        <input
                            id="employee-fecha-nacimiento"
                            v-model="fechaNacimiento"
                            v-bind="fechaNacimientoAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.fecha_nacimiento }"
                            type="date"
                        />
                        <p v-if="editErrors.fecha_nacimiento" class="admin-employees-form__error">
                            {{ editErrors.fecha_nacimiento }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-email">Correo electrónico</label>
                        <input
                            id="employee-email"
                            v-model="email"
                            v-bind="emailAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.email }"
                            type="email"
                        />
                        <p v-if="editErrors.email" class="admin-employees-form__error">
                            {{ editErrors.email }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-telefono">Teléfono</label>
                        <input
                            id="employee-telefono"
                            v-model="telefono"
                            v-bind="telefonoAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.telefono }"
                            type="text"
                        />
                        <p v-if="editErrors.telefono" class="admin-employees-form__error">
                            {{ editErrors.telefono }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-password">
                            {{ modoPerfil === 'crear' ? 'Contraseña' : 'Nueva contraseña (opcional)' }}
                        </label>
                        <input
                            id="employee-password"
                            v-model="password"
                            v-bind="passwordAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.password }"
                            type="password"
                        />
                        <p v-if="editErrors.password" class="admin-employees-form__error">
                            {{ editErrors.password }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-especialidades">Especialidades</label>
                        <textarea
                            id="employee-especialidades"
                            v-model="especialidades"
                            v-bind="especialidadesAttrs"
                            class="admin-employees-form__textarea"
                            :class="{ 'admin-employees-form__input--error': editErrors.especialidades }"
                            rows="3"
                        >
                        </textarea>
                        <p v-if="editErrors.especialidades" class="admin-employees-form__error">
                            {{ editErrors.especialidades }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field">
                        <label class="admin-employees-form__label" for="employee-fecha-contratacion">Fecha de contratación</label>
                        <input
                            id="employee-fecha-contratacion"
                            v-model="fechaContratacion"
                            v-bind="fechaContratacionAttrs"
                            class="admin-employees-form__input"
                            :class="{ 'admin-employees-form__input--error': editErrors.fecha_contratacion }"
                            type="date"
                        />
                        <p v-if="editErrors.fecha_contratacion" class="admin-employees-form__error">
                            {{ editErrors.fecha_contratacion }}
                        </p>
                    </div>

                    <div class="admin-employees-form__field admin-employees-form__field--checkbox">
                        <label class="admin-employees-form__checkbox-label" for="employee-activo">
                            <input
                                id="employee-activo"
                                v-model="activo"
                                v-bind="activoAttrs"
                                class="admin-employees-form__checkbox"
                                type="checkbox"
                            />
                            Empleado activo
                        </label>
                        <p v-if="editErrors.activo" class="admin-employees-form__error">
                            {{ editErrors.activo }}
                        </p>
                    </div>

                    <footer class="admin-employees-form__actions">
                        <button
                            type="button"
                            class="admin-employees-form__button admin-employees-form__button--secondary"
                            @click="cerrarModalEdicion"
                            :disabled="saving"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="admin-employees-form__button admin-employees-form__button--primary"
                            :disabled="saving"
                        >
                            {{
                                saving
                                    ? 'Guardando...'
                                    : modoPerfil === 'crear'
                                        ? 'Crear empleado'
                                        : 'Guardar perfil'
                            }}
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    </main>
</template>