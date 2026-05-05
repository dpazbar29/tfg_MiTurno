<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch, computed } from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'

// Props recibidas desde el componente padre.
// - visible: controla si el modal se muestra o no.
// - modo: define si el formulario crea o edita.
// - empleado: datos del empleado cuando estamos en edición.
// - saving: indica si el formulario se está guardando.
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    modo: {
        type: String,
        default: 'crear',
    },
    empleado: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
})

// Eventos emitidos al componente padre.
// - close: cerrar modal.
// - submit: enviar datos del formulario.
const emit = defineEmits(['close', 'submit'])

// Referencias para accesibilidad y gestión del foco dentro del modal.
const editModalRef = ref(null)
const editModalTitleRef = ref(null)
const lastTriggerRef = ref(null)

// Esquema de validación del formulario
const employeeSchema = yup.object({
    nombre: yup.string().trim().required('El nombre es obligatorio.'),
    apellidos: yup.string().trim().required('Los apellidos son obligatorios.'),
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
    // Validación condicional de contraseña:
    // - En modo "crear" es obligatoria y debe tener al menos 8 caracteres.
    // - En modo "editar" puede ir vacía; solo se valida si se rellena.
    password: yup
        .string()
        .transform((value) => value ?? '')
        .test('password-condicional', 'La contraseña debe tener al menos 8 caracteres.', function (value) {
        const esCrear = props.modo === 'crear'
        if (esCrear) return !!value && value.length >= 8
        if (!value) return true
        return value.length >= 8
        }),
    especialidades: yup.string().nullable(),
    fecha_contratacion: yup.string().required('La fecha de contratación es obligatoria.'),
    activo: yup.boolean().required(),
    fecha_nacimiento: yup
        .string()
        .nullable()
        .test('fecha-nacimiento-valida', 'La fecha de nacimiento no es válida.', (value) => {
        if (!value) return true
        return !Number.isNaN(new Date(value).getTime())
        }),
})

// Inicialización del formulario con vee-validate.
// Se define el esquema de validación y los valores iniciales.
const {
    defineField,
    handleSubmit,
    errors,
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

// Cada defineField conecta un campo del formulario con vee-validate.
// Devuelve el valor reactivo y los atributos necesarios del campo.
const [nombre, nombreAttrs] = defineField('nombre')
const [apellidos, apellidosAttrs] = defineField('apellidos')
const [email, emailAttrs] = defineField('email')
const [telefono, telefonoAttrs] = defineField('telefono')
const [password, passwordAttrs] = defineField('password')
const [especialidades, especialidadesAttrs] = defineField('especialidades')
const [fechaContratacion, fechaContratacionAttrs] = defineField('fecha_contratacion')
const [activo, activoAttrs] = defineField('activo')
const [fechaNacimiento, fechaNacimientoAttrs] = defineField('fecha_nacimiento')

// Título dinámico del modal según el modo de uso.
const titulo = computed(() =>
    props.modo === 'crear'
        ? 'Nuevo empleado'
        : `Editar perfil de ${props.empleado?.usuario?.nombre || ''} ${props.empleado?.usuario?.apellidos || ''}`.trim(),
)

// Descripción dinámica usada también como apoyo de accesibilidad.
const descripcion = computed(() =>
    props.modo === 'crear'
        ? 'Completa los datos básicos para crear un nuevo empleado.'
        : 'Modifica los datos básicos del usuario y la información propia del empleado.',
)

// Carga los valores del formulario:
// - Editando, precarga los datos del empleado.
// - Creando, reinicia el formulario vacío.
const cargarValores = () => {
    if (props.modo === 'editar' && props.empleado) {
        resetForm({
            values: {
                nombre: props.empleado.usuario?.nombre || '',
                apellidos: props.empleado.usuario?.apellidos || '',
                email: props.empleado.usuario?.email || '',
                telefono: props.empleado.usuario?.telefono || '',
                password: '',
                especialidades: props.empleado.especialidades || '',
                fecha_contratacion: props.empleado.fecha_contratacion || '',
                activo: !!props.empleado.activo,
                fecha_nacimiento: props.empleado.usuario?.fecha_nacimiento || '',
            },
            errors: {},
        })
    } else {
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
    }
}

// Observa cambios en visibilidad, modo y empleado.
// Cuando se abre el modal:
// - guarda el elemento que tenía el foco,
// - carga valores,
// - espera a que el DOM se actualice,
// - y mueve el foco al título del diálogo.
// Cuando se cierra, devuelve el foco al elemento anterior.
watch(
    () => [props.visible, props.modo, props.empleado],
    async ([visible]) => {
        if (visible) {
            lastTriggerRef.value = document.activeElement
            cargarValores()
            await nextTick()
            editModalTitleRef.value?.focus()
        } else {
            lastTriggerRef.value?.focus?.()
        }
    },
    { immediate: true },
)

// Envío del formulario validado.
// handleSubmit solo ejecuta este bloque si el formulario es válido.
const submit = handleSubmit(async (values) => {
    const payload = {
        ...values,
        rol: 'empleado',
    }

    // En edición, si la contraseña está vacía, no se envía al backend para evitar sobrescribirla innecesariamente.
    if (props.modo === 'editar' && !payload.password) {
        delete payload.password
    }

    // Se emite al padre el payload y la función setErrors, útil para mapear errores devueltos por la API al formulario.
    emit('submit', payload, setErrors)
})

// Recupera todos los elementos enfocables del modal.
const getFocusableElements = () => {
    if (!editModalRef.value) return []
    return editModalRef.value.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

// Control del tabulador dentro del modal.
// Evita que el foco salga fuera del diálogo mientras está abierto, haciendo un ciclo entre el primer y el último elemento.
const manejarTab = (event) => {
    if (!props.visible || event.key !== 'Tab') return

    const focusables = [...getFocusableElements()]
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

// Gestión global del teclado mientras el modal está abierto.
// - Escape cierra el modal.
// - Tab y Shift+Tab quedan atrapados dentro del diálogo.
const manejarTeclado = (event) => {
    if (!props.visible) return
    if (event.key === 'Escape') emit('close')
    manejarTab(event)
}

// Registro y limpieza del listener global del teclado.
onMounted(() => document.addEventListener('keydown', manejarTeclado))
onBeforeUnmount(() => document.removeEventListener('keydown', manejarTeclado))
</script>

<template>
    <div v-if="visible" class="admin-employees-modal" @click.self="$emit('close')">
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
                    {{ titulo }}
                </h2>

                <button
                    type="button"
                    class="admin-employees-modal__close"
                    @click="$emit('close')"
                    aria-label="Cerrar formulario de empleado"
                >
                    ×
                </button>
            </header>

            <p id="employee-edit-description" class="admin-employees-modal__description">
                {{ descripcion }}
            </p>

            <form class="admin-employees-form" @submit.prevent="submit" :aria-busy="saving ? 'true' : 'false'">
                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-nombre">Nombre</label>
                    <input
                        id="employee-nombre"
                        v-model="nombre"
                        v-bind="nombreAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.nombre }"
                        type="text"
                    />
                    <p v-if="errors.nombre" class="admin-employees-form__error">{{ errors.nombre }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-apellidos">Apellidos</label>
                    <input
                        id="employee-apellidos"
                        v-model="apellidos"
                        v-bind="apellidosAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.apellidos }"
                        type="text"
                    />
                    <p v-if="errors.apellidos" class="admin-employees-form__error">{{ errors.apellidos }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-fecha-nacimiento">Fecha de nacimiento</label>
                    <input
                        id="employee-fecha-nacimiento"
                        v-model="fechaNacimiento"
                        v-bind="fechaNacimientoAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.fecha_nacimiento }"
                        type="date"
                    />
                    <p v-if="errors.fecha_nacimiento" class="admin-employees-form__error">{{ errors.fecha_nacimiento }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-email">Correo electrónico</label>
                    <input
                        id="employee-email"
                        v-model="email"
                        v-bind="emailAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.email }"
                        type="email"
                    />
                    <p v-if="errors.email" class="admin-employees-form__error">{{ errors.email }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-telefono">Teléfono</label>
                    <input
                        id="employee-telefono"
                        v-model="telefono"
                        v-bind="telefonoAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.telefono }"
                        type="text"
                    />
                    <p v-if="errors.telefono" class="admin-employees-form__error">{{ errors.telefono }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-password">
                        {{ modo === 'crear' ? 'Contraseña' : 'Nueva contraseña (opcional)' }}
                    </label>
                    <input
                        id="employee-password"
                        v-model="password"
                        v-bind="passwordAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.password }"
                        type="password"
                    />
                    <p v-if="errors.password" class="admin-employees-form__error">{{ errors.password }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-especialidades">Especialidades</label>
                    <textarea
                        id="employee-especialidades"
                        v-model="especialidades"
                        v-bind="especialidadesAttrs"
                        class="admin-employees-form__textarea"
                        :class="{ 'admin-employees-form__input--error': errors.especialidades }"
                        rows="3"
                    ></textarea>
                    <p v-if="errors.especialidades" class="admin-employees-form__error">{{ errors.especialidades }}</p>
                </div>

                <div class="admin-employees-form__field">
                    <label class="admin-employees-form__label" for="employee-fecha-contratacion">Fecha de contratación</label>
                    <input
                        id="employee-fecha-contratacion"
                        v-model="fechaContratacion"
                        v-bind="fechaContratacionAttrs"
                        class="admin-employees-form__input"
                        :class="{ 'admin-employees-form__input--error': errors.fecha_contratacion }"
                        type="date"
                    />
                    <p v-if="errors.fecha_contratacion" class="admin-employees-form__error">{{ errors.fecha_contratacion }}</p>
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
                    <p v-if="errors.activo" class="admin-employees-form__error">{{ errors.activo }}</p>
                </div>

                <footer class="admin-employees-form__actions">
                    <button
                        type="button"
                        class="admin-employees-form__button admin-employees-form__button--secondary"
                        @click="$emit('close')"
                        :disabled="saving"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="admin-employees-form__button admin-employees-form__button--primary"
                        :disabled="saving"
                    >
                        {{ saving ? 'Guardando...' : modo === 'crear' ? 'Crear empleado' : 'Guardar perfil' }}
                    </button>
                </footer>
            </form>
        </div>
    </div>
</template>