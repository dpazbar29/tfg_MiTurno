<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    modo: {
        type: String,
        default: 'crear',
    },
    horarioEditando: {
        type: Object,
        default: null,
    },
    empleadoSeleccionadoId: {
        type: [String, Number],
        default: '',
    },
    empleados: {
        type: Array,
        default: () => [],
    },
    diasSemana: {
        type: Array,
        required: true,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    tituloModal: {
        type: String,
        default: 'Nueva franja horaria',
    },
})

const emit = defineEmits(['close', 'submit'])

const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

const horarioSchema = yup.object({
    empleado_id: yup
        .number()
        .typeError('Selecciona un empleado.')
        .required('El empleado es obligatorio.'),
    dia_semana: yup
        .number()
        .typeError('Selecciona un día.')
        .min(0)
        .max(6)
        .required('El día es obligatorio.'),
    hora_inicio: yup
        .string()
        .required('La hora de inicio es obligatoria.'),
    hora_fin: yup
        .string()
        .required('La hora de fin es obligatoria.')
        .test(
        'hora-fin-mayor',
        'La hora de fin debe ser posterior a la hora de inicio.',
        function (value) {
            const { hora_inicio } = this.parent
            if (!hora_inicio || !value) return true
            return value > hora_inicio
        },
        ),
    tipo: yup
        .string()
        .oneOf(['normal', 'festivo', 'cierre'])
        .required('El tipo es obligatorio.'),
    activo: yup
        .boolean()
        .required(),
})

const {
    defineField,
    handleSubmit,
    errors: formErrors,
    resetForm,
    setErrors,
} = useForm({
    validationSchema: horarioSchema,
    initialValues: {
        empleado_id: '',
        dia_semana: 1,
        hora_inicio: '',
        hora_fin: '',
        tipo: 'normal',
        activo: true,
    },
})

const [empleadoId, empleadoIdAttrs] = defineField('empleado_id')
const [diaSemana, diaSemanaAttrs] = defineField('dia_semana')
const [horaInicio, horaInicioAttrs] = defineField('hora_inicio')
const [horaFin, horaFinAttrs] = defineField('hora_fin')
const [tipo, tipoAttrs] = defineField('tipo')
const [activo, activoAttrs] = defineField('activo')

const textoBotonSubmit = computed(() => {
    if (props.saving) return 'Guardando...'
    return props.modo === 'crear' ? 'Crear franja' : 'Guardar cambios'
})

const descripcionModal = computed(
    () => 'Define el día, rango horario y tipo de disponibilidad del empleado.',
)

const resetearFormularioCrear = (dia = null) => {
    resetForm({
        values: {
            empleado_id: props.empleadoSeleccionadoId || '',
            dia_semana: dia ?? 1,
            hora_inicio: '',
            hora_fin: '',
            tipo: 'normal',
            activo: true,
        },
        errors: {},
    })
}

const resetearFormularioEditar = (horario) => {
    resetForm({
        values: {
            empleado_id: horario?.empleado_id ?? props.empleadoSeleccionadoId ?? '',
            dia_semana: horario?.dia_semana ?? 1,
            hora_inicio: horario?.hora_inicio?.slice(0, 5) || '',
            hora_fin: horario?.hora_fin?.slice(0, 5) || '',
            tipo: horario?.tipo || 'normal',
            activo: !!horario?.activo,
        },
        errors: {},
    })
}

const abrirModal = async () => {
    lastTriggerRef.value = document.activeElement

    if (props.modo === 'editar' && props.horarioEditando) {
        resetearFormularioEditar(props.horarioEditando)
    } else {
        resetearFormularioCrear()
    }

    await nextTick()
    modalTitleRef.value?.focus()
}

const cerrarModal = () => {
    emit('close')

    nextTick(() => {
        if (lastTriggerRef.value && typeof lastTriggerRef.value.focus === 'function') {
            lastTriggerRef.value.focus()
        }
    })
}

const submit = handleSubmit((values) => {
    emit('submit', values, setErrors)
})

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
    if (!props.visible) return

    if (event.key === 'Escape') {
        cerrarModal()
        return
    }

    if (event.key === 'Tab' && modalRef.value) {
        manejarTabEnContenedor(event, modalRef.value)
    }
}

watch(
    () => props.visible,
    async (visible) => {
        if (visible) {
            await abrirModal()
            document.addEventListener('keydown', manejarTecladoModal)
        } else {
            document.removeEventListener('keydown', manejarTecladoModal)
        }
    },
)

watch(
    () => props.horarioEditando,
    (horario) => {
        if (!props.visible) return

        if (props.modo === 'editar' && horario) {
            resetearFormularioEditar(horario)
        }
    },
)

onBeforeUnmount(() => {
    document.removeEventListener('keydown', manejarTecladoModal)
})
</script>

<template>
    <div
        v-if="visible"
        id="schedule-modal"
        class="admin-schedules-modal"
        @click.self="cerrarModal"
    >
        <div
            ref="modalRef"
            class="admin-schedules-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="schedule-modal-title"
            aria-describedby="schedule-modal-description"
        >
            <header class="admin-schedules-modal__header">
                <div class="admin-schedules-modal__heading">
                    <h2
                        id="schedule-modal-title"
                        ref="modalTitleRef"
                        class="admin-schedules-modal__title"
                        tabindex="-1"
                    >
                        {{ tituloModal }}
                    </h2>

                    <p
                        id="schedule-modal-description"
                        class="admin-schedules-modal__description"
                    >
                        {{ descripcionModal }}
                    </p>
                </div>

                <button
                    type="button"
                    class="admin-schedules-modal__close"
                    @click="cerrarModal"
                    aria-label="Cerrar formulario de horario"
                >
                    ×
                </button>
            </header>

            <form
                class="admin-schedules-form"
                @submit.prevent="submit"
                :aria-busy="saving ? 'true' : 'false'"
                novalidate
            >
                <div class="admin-schedules-form__field">
                    <label class="admin-schedules-form__label" for="schedule-empleado">
                        Empleado
                    </label>

                    <select
                        id="schedule-empleado"
                        v-model="empleadoId"
                        v-bind="empleadoIdAttrs"
                        class="admin-schedules-form__input"
                        :class="{ 'admin-schedules-form__input--error': formErrors.empleado_id }"
                        :aria-invalid="formErrors.empleado_id ? 'true' : 'false'"
                        :aria-describedby="formErrors.empleado_id ? 'schedule-empleado-error' : undefined"
                    >
                        <option value="">Selecciona un empleado</option>
                        <option
                            v-for="empleado in empleados"
                            :key="empleado.id"
                            :value="empleado.id"
                        >
                            {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                        </option>
                    </select>

                    <p
                        v-if="formErrors.empleado_id"
                        id="schedule-empleado-error"
                        class="admin-schedules-form__error"
                    >
                        {{ formErrors.empleado_id }}
                    </p>
                </div>

                <div class="admin-schedules-form__field">
                    <label class="admin-schedules-form__label" for="schedule-dia">
                        Día
                    </label>

                    <select
                        id="schedule-dia"
                        v-model="diaSemana"
                        v-bind="diaSemanaAttrs"
                        class="admin-schedules-form__input"
                        :class="{ 'admin-schedules-form__input--error': formErrors.dia_semana }"
                        :aria-invalid="formErrors.dia_semana ? 'true' : 'false'"
                        :aria-describedby="formErrors.dia_semana ? 'schedule-dia-error' : undefined"
                    >
                        <option
                            v-for="dia in diasSemana"
                            :key="dia.value"
                            :value="dia.value"
                        >
                            {{ dia.label }}
                        </option>
                    </select>

                    <p
                        v-if="formErrors.dia_semana"
                        id="schedule-dia-error"
                        class="admin-schedules-form__error"
                    >
                        {{ formErrors.dia_semana }}
                    </p>
                </div>

                <div class="admin-schedules-form__field">
                    <label class="admin-schedules-form__label" for="schedule-hora-inicio">
                        Hora de inicio
                    </label>

                    <input
                        id="schedule-hora-inicio"
                        v-model="horaInicio"
                        v-bind="horaInicioAttrs"
                        class="admin-schedules-form__input"
                        :class="{ 'admin-schedules-form__input--error': formErrors.hora_inicio }"
                        :aria-invalid="formErrors.hora_inicio ? 'true' : 'false'"
                        :aria-describedby="formErrors.hora_inicio ? 'schedule-hora-inicio-error' : undefined"
                        type="time"
                    />

                    <p
                        v-if="formErrors.hora_inicio"
                        id="schedule-hora-inicio-error"
                        class="admin-schedules-form__error"
                    >
                        {{ formErrors.hora_inicio }}
                    </p>
                </div>

                <div class="admin-schedules-form__field">
                    <label class="admin-schedules-form__label" for="schedule-hora-fin">
                        Hora de fin
                    </label>

                    <input
                        id="schedule-hora-fin"
                        v-model="horaFin"
                        v-bind="horaFinAttrs"
                        class="admin-schedules-form__input"
                        :class="{ 'admin-schedules-form__input--error': formErrors.hora_fin }"
                        :aria-invalid="formErrors.hora_fin ? 'true' : 'false'"
                        :aria-describedby="formErrors.hora_fin ? 'schedule-hora-fin-error' : undefined"
                        type="time"
                    />

                    <p
                        v-if="formErrors.hora_fin"
                        id="schedule-hora-fin-error"
                        class="admin-schedules-form__error"
                    >
                        {{ formErrors.hora_fin }}
                    </p>
                </div>

                <div class="admin-schedules-form__field">
                    <label class="admin-schedules-form__label" for="schedule-tipo">
                        Tipo
                    </label>

                    <select
                        id="schedule-tipo"
                        v-model="tipo"
                        v-bind="tipoAttrs"
                        class="admin-schedules-form__input"
                        :class="{ 'admin-schedules-form__input--error': formErrors.tipo }"
                        :aria-invalid="formErrors.tipo ? 'true' : 'false'"
                        :aria-describedby="formErrors.tipo ? 'schedule-tipo-error' : undefined"
                    >
                        <option value="normal">Normal</option>
                        <option value="festivo">Festivo</option>
                        <option value="cierre">Cierre</option>
                    </select>

                    <p
                        v-if="formErrors.tipo"
                        id="schedule-tipo-error"
                        class="admin-schedules-form__error"
                    >
                        {{ formErrors.tipo }}
                    </p>
                </div>

                <div class="admin-schedules-form__field admin-schedules-form__field--checkbox">
                    <label class="admin-schedules-form__checkbox-label" for="schedule-activo">
                        <input
                            id="schedule-activo"
                            v-model="activo"
                            v-bind="activoAttrs"
                            class="admin-schedules-form__checkbox"
                            type="checkbox"
                        />
                        Franja activa
                    </label>

                    <p v-if="formErrors.activo" class="admin-schedules-form__error">
                        {{ formErrors.activo }}
                    </p>
                </div>

                <footer class="admin-schedules-form__actions">
                    <button
                        type="button"
                        class="admin-schedules-form__button admin-schedules-form__button--secondary"
                        @click="cerrarModal"
                        :disabled="saving"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="admin-schedules-form__button admin-schedules-form__button--primary"
                        :disabled="saving"
                    >
                        {{ textoBotonSubmit }}
                    </button>
                </footer>
            </form>
        </div>
    </div>
</template>