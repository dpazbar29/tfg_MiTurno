<script setup>
import { watch } from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    empleados: {
        type: Array,
        default: () => [],
    },
    servicios: {
        type: Array,
        default: () => [],
    },
    estados: {
        type: Array,
        default: () => [],
    },
    // Reserva actualmente seleccionada para edición.
    // Cuando cambia, el formulario debe resincronizar sus valores.
    reservaEditando: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submit', 'cancel'])

// Esquema de validación para edición.
// Se valida solo la estructura del formulario
const edicionSchema = yup.object({
    empleadoid: yup
        .number()
        .nullable()
        .transform((value, originalValue) => {
        // Convierte string vacío o null en null real.
        // Esto evita que Yup intente validar '' como número.
        return originalValue === '' || originalValue === null ? null : value
        })
        .test(
        'empleado-valido',
        'El empleado debe existir.',
        function (value) {
            // Permite null y valida que, si existe valor, sea interpretable como número.
            return value === null || !Number.isNaN(Number(value))
        },
        ),
    servicioid: yup
        .number()
        .typeError('Selecciona un servicio.')
        .required('El servicio es obligatorio.'),
    fechahorainicio: yup
        .string()
        .required('La fecha y hora de inicio son obligatorias.'),
    estado: yup
        .string()
        .oneOf(['pendiente', 'confirmada', 'cancelada', 'completada', 'ausencia'])
        .required('El estado es obligatorio.'),
    notas: yup
        .string()
        .nullable()
        .max(500, 'Las notas no pueden exceder 500 caracteres.'),
})

// Inicialización del formulario con vee-validate.
// Se definen esquema, valores iniciales y utilidades para manejar errores, reseteos y envío validado.
const {
    defineField,
    handleSubmit,
    errors: formErrors,
    resetForm,
    setErrors,
} = useForm({
    validationSchema: edicionSchema,
    initialValues: {
        empleadoid: '',
        servicioid: '',
        fechahorainicio: '',
        estado: 'pendiente',
        notas: '',
    },
})

// Vinculación de cada campo del formulario con vee-validate.
const [empleadoId, empleadoIdAttrs] = defineField('empleadoid')
const [servicioId, servicioIdAttrs] = defineField('servicioid')
const [fechaHoraInicio, fechaHoraInicioAttrs] = defineField('fechahorainicio')
const [estado, estadoAttrs] = defineField('estado')
const [notas, notasAttrs] = defineField('notas')

// Convierte una fecha cualquiera al formato esperado por <input type="datetime-local">
const formatoDatetimeLocal = (valor) => {
    if (!valor) return ''

    const fecha = new Date(valor)
    if (Number.isNaN(fecha.getTime())) return ''

    const year = fecha.getFullYear()
    const month = String(fecha.getMonth() + 1).padStart(2, '0')
    const day = String(fecha.getDate()).padStart(2, '0')
    const hours = String(fecha.getHours()).padStart(2, '0')
    const minutes = String(fecha.getMinutes()).padStart(2, '0')

    return `${year}-${month}-${day}T${hours}:${minutes}`
}

// Resetea y recarga el formulario con los datos de la reserva activa.
// Esto no solo actualiza valores, sino que también limpia errores previos.
const resetearFormulario = () => {
    const reserva = props.reservaEditando

    resetForm({
        values: {
            empleadoid: reserva?.empleadoid ?? '',
            servicioid: reserva?.servicioid ?? '',
            fechahorainicio: formatoDatetimeLocal(reserva?.fechahorainicio),
            estado: reserva?.estado ?? 'pendiente',
            notas: reserva?.notas ?? '',
        },
        errors: {},
    })
}

// Cuando el modal se abre, se cargan de nuevo los datos de la reserva.
// Esto evita que queden residuos de una edición anterior si el componente se reutiliza varias veces sin destruirse.
watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            resetearFormulario()
        }
    },
)

// Si cambia la reserva a editar mientras el modal sigue visible, el formulario también se resincroniza automáticamente.
watch(
    () => props.reservaEditando,
    () => {
        if (props.visible) {
            resetearFormulario()
        }
    },
)

// Envío validado del formulario.
// Si el esquema pasa la validación, se emiten los valores al padre junto con setErrors para poder reflejar errores devueltos por la API.
const submit = handleSubmit((values) => {
    emit('submit', values, setErrors)
})
</script>

<template>
    <form
        class="edit-reservation-form"
        :aria-busy="saving ? 'true' : 'false'"
        novalidate
        @submit.prevent="submit"
    >
        <div class="edit-reservation-form__field edit-reservation-form__field--full">
            <span class="edit-reservation-form__label">Cliente</span>
            <p class="edit-reservation-form__readonly">
                {{ reservaEditando?.usuario?.nombre }} {{ reservaEditando?.usuario?.apellidos }}
                <span v-if="reservaEditando?.usuario?.email">
                    — {{ reservaEditando.usuario.email }}
                </span>
            </p>
        </div>

        <div class="edit-reservation-form__field">
            <label class="edit-reservation-form__label" for="reserva-empleado">
                Empleado
            </label>
            <select
                id="reserva-empleado"
                v-model="empleadoId"
                v-bind="empleadoIdAttrs"
                class="edit-reservation-form__input"
                :class="{ 'edit-reservation-form__input--error': formErrors.empleadoid }"
                :aria-invalid="formErrors.empleadoid ? 'true' : 'false'"
                :aria-describedby="formErrors.empleadoid ? 'reserva-empleado-error' : undefined"
            >
                <option value="">Sin asignar</option>
                <option
                    v-for="empleado in empleados"
                    :key="empleado.id"
                    :value="empleado.id"
                >
                    {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                </option>
            </select>
            <p
                v-if="formErrors.empleadoid"
                id="reserva-empleado-error"
                class="edit-reservation-form__error"
            >
                {{ formErrors.empleadoid }}
            </p>
        </div>

        <div class="edit-reservation-form__field">
            <label class="edit-reservation-form__label" for="reserva-servicio">
                Servicio
            </label>
            <select
                id="reserva-servicio"
                v-model="servicioId"
                v-bind="servicioIdAttrs"
                class="edit-reservation-form__input"
                :class="{ 'edit-reservation-form__input--error': formErrors.servicioid }"
                :aria-invalid="formErrors.servicioid ? 'true' : 'false'"
                :aria-describedby="formErrors.servicioid ? 'reserva-servicio-error' : undefined"
            >
                <option value="">Selecciona un servicio</option>
                <option
                    v-for="servicio in servicios"
                    :key="servicio.id"
                    :value="servicio.id"
                >
                    {{ servicio.nombre }}
                </option>
            </select>
            <p
                v-if="formErrors.servicioid"
                id="reserva-servicio-error"
                class="edit-reservation-form__error"
            >
                {{ formErrors.servicioid }}
            </p>
        </div>

        <div class="edit-reservation-form__field">
            <label class="edit-reservation-form__label" for="reserva-fecha-hora">
                Fecha y hora
            </label>
            <input
                id="reserva-fecha-hora"
                v-model="fechaHoraInicio"
                v-bind="fechaHoraInicioAttrs"
                class="edit-reservation-form__input"
                :class="{ 'edit-reservation-form__input--error': formErrors.fechahorainicio }"
                :aria-invalid="formErrors.fechahorainicio ? 'true' : 'false'"
                :aria-describedby="formErrors.fechahorainicio ? 'reserva-fecha-hora-error' : undefined"
                type="datetime-local"
            />
            <p
                v-if="formErrors.fechahorainicio"
                id="reserva-fecha-hora-error"
                class="edit-reservation-form__error"
            >
                {{ formErrors.fechahorainicio }}
            </p>
        </div>

        <div class="edit-reservation-form__field">
            <label class="edit-reservation-form__label" for="reserva-estado">
                Estado
            </label>
            <select
                id="reserva-estado"
                v-model="estado"
                v-bind="estadoAttrs"
                class="edit-reservation-form__input"
                :class="{ 'edit-reservation-form__input--error': formErrors.estado }"
                :aria-invalid="formErrors.estado ? 'true' : 'false'"
                :aria-describedby="formErrors.estado ? 'reserva-estado-error' : undefined"
            >
                <option
                    v-for="estadoItem in estados"
                    :key="estadoItem.value"
                    :value="estadoItem.value"
                >
                    {{ estadoItem.label }}
                </option>
            </select>
            <p
                v-if="formErrors.estado"
                id="reserva-estado-error"
                class="edit-reservation-form__error"
            >
                {{ formErrors.estado }}
            </p>
        </div>

        <div class="edit-reservation-form__field edit-reservation-form__field--full">
            <label class="edit-reservation-form__label" for="reserva-notas">
                Notas
            </label>
            <textarea
                id="reserva-notas"
                v-model="notas"
                v-bind="notasAttrs"
                class="edit-reservation-form__textarea"
                :class="{ 'edit-reservation-form__textarea--error': formErrors.notas }"
                :aria-invalid="formErrors.notas ? 'true' : 'false'"
                :aria-describedby="formErrors.notas ? 'reserva-notas-error' : undefined"
                rows="4"
            ></textarea>
            <p
                v-if="formErrors.notas"
                id="reserva-notas-error"
                class="edit-reservation-form__error"
            >
                {{ formErrors.notas }}
            </p>
        </div>

        <footer class="edit-reservation-form__actions edit-reservation-form__actions--full">
            <button
                type="button"
                class="edit-reservation-form__button edit-reservation-form__button--secondary"
                :disabled="saving"
                @click="$emit('cancel')"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="edit-reservation-form__button edit-reservation-form__button--primary"
                :disabled="saving"
            >
                {{ saving ? 'Guardando...' : 'Guardar cambios' }}
            </button>
        </footer>
    </form>
</template>