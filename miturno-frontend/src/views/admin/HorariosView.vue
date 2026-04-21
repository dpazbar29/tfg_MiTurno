<script setup>
import { ref, computed, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'
import { getEmpleados } from '../../api/empleados'
import { getHorarios, createHorario, updateHorario, deleteHorario, } from '../../api/horarios'

const empleados = ref([])
const horarios = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingHorarioId = ref(null)

const empleadoSeleccionadoId = ref('')
const mostrarModalHorario = ref(false)
const horarioEditando = ref(null)
const modoHorario = ref('crear')

const error = ref(null)
const success = ref(null)

const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

const diasSemana = [
    { value: 0, label: 'Domingo' },
    { value: 1, label: 'Lunes' },
    { value: 2, label: 'Martes' },
    { value: 3, label: 'Miércoles' },
    { value: 4, label: 'Jueves' },
    { value: 5, label: 'Viernes' },
    { value: 6, label: 'Sábado' },
]

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
        .test('hora-fin-mayor', 'La hora de fin debe ser posterior a la hora de inicio.', function (value) {
            const { hora_inicio } = this.parent
            if (!hora_inicio || !value) return true
            return value > hora_inicio
        }),
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

const tituloModal = computed(() =>
    modoHorario.value === 'crear' ? 'Nueva franja horaria' : 'Editar franja horaria',
)

const empleadosActivos = computed(() =>
    empleados.value.filter((empleado) => empleado.activo && empleado.usuario?.activo !== false),
)

const empleadoSeleccionado = computed(() =>
    empleados.value.find((empleado) => String(empleado.id) === String(empleadoSeleccionadoId.value)),
)

const horariosFiltrados = computed(() => {
    if (!empleadoSeleccionadoId.value) return []
    return horarios.value
        .filter((horario) => String(horario.empleado_id) === String(empleadoSeleccionadoId.value))
        .sort((a, b) => {
        if (a.dia_semana !== b.dia_semana) return a.dia_semana - b.dia_semana
        return a.hora_inicio.localeCompare(b.hora_inicio)
    })
})

const horariosPorDia = computed(() =>
    diasSemana.map((dia) => ({
        ...dia,
        horarios: horariosFiltrados.value.filter((horario) => horario.dia_semana === dia.value),
    })),
)

const cargarDatos = async () => {
    loading.value = true
    error.value = null

    try {
        const [listaEmpleados, listaHorarios] = await Promise.all([
            getEmpleados(),
            getHorarios(),
        ])

        empleados.value = listaEmpleados
        horarios.value = listaHorarios

        if (!empleadoSeleccionadoId.value && listaEmpleados.length) {
            const primerEmpleadoActivo = listaEmpleados.find(
                (empleado) => empleado.activo && empleado.usuario?.activo !== false,
            )
            empleadoSeleccionadoId.value = primerEmpleadoActivo ? String(primerEmpleadoActivo.id) : ''
        }
  } catch (err) {
        console.error(err)
        error.value = 'No se pudieron cargar los empleados o los horarios.'
  } finally {
        loading.value = false
  }
}

const abrirModalCrear = async (event = null, dia = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoHorario.value = 'crear'
    horarioEditando.value = null
    mostrarModalHorario.value = true

    resetForm({
        values: {
            empleado_id: empleadoSeleccionadoId.value || '',
            dia_semana: dia ?? 1,
            hora_inicio: '',
            hora_fin: '',
            tipo: 'normal',
            activo: true,
        },
        errors: {},
    })

    await nextTick()
    modalTitleRef.value?.focus()
}

const abrirModalEdicion = async (horario, event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null
    modoHorario.value = 'editar'
    horarioEditando.value = horario
    mostrarModalHorario.value = true

    resetForm({
        values: {
            empleado_id: horario.empleado_id,
            dia_semana: horario.dia_semana,
            hora_inicio: horario.hora_inicio?.slice(0, 5) || '',
            hora_fin: horario.hora_fin?.slice(0, 5) || '',
            tipo: horario.tipo,
            activo: !!horario.activo,
        },
        errors: {},
    })

    await nextTick()
    modalTitleRef.value?.focus()
}

const cerrarModal = () => {
    mostrarModalHorario.value = false
    horarioEditando.value = null
    modoHorario.value = 'crear'

    resetForm({
        values: {
            empleado_id: empleadoSeleccionadoId.value || '',
            dia_semana: 1,
            hora_inicio: '',
            hora_fin: '',
            tipo: 'normal',
            activo: true,
        },
        errors: {},
    })

    if (lastTriggerRef.value && typeof lastTriggerRef.value.focus === 'function') {
        lastTriggerRef.value.focus()
    }
}

const guardarHorario = handleSubmit(async (values) => {
    saving.value = true
    error.value = null
    success.value = null

    try {
        const payload = {
            ...values,
            empleado_id: Number(values.empleado_id),
            dia_semana: Number(values.dia_semana),
        }

        if (modoHorario.value === 'crear') {
            await createHorario(payload)
            success.value = 'Horario creado correctamente.'
        } else {
            await updateHorario(horarioEditando.value.id, payload)
            success.value = 'Horario actualizado correctamente.'
        }

        await cargarDatos()
        cerrarModal()
  } catch (err) {
        console.error(err.response?.data || err)

        const backendErrors = err.response?.data?.errors

    if (backendErrors) {
        setErrors({
            empleado_id: backendErrors.empleado_id?.[0],
            dia_semana: backendErrors.dia_semana?.[0],
            hora_inicio: backendErrors.hora_inicio?.[0],
            hora_fin: backendErrors.hora_fin?.[0],
            tipo: backendErrors.tipo?.[0],
            activo: backendErrors.activo?.[0],
        })
    } else {
        error.value =
            modoHorario.value === 'crear'
                ? 'No se pudo crear el horario.'
                : 'No se pudo actualizar el horario.'
    }
  } finally {
        saving.value = false
  }
})

const eliminarHorarioConfirmado = async (horario) => {
    if (!window.confirm('¿Seguro que quieres eliminar esta franja horaria?')) return

    deletingHorarioId.value = horario.id
    error.value = null
    success.value = null

    try {
        await deleteHorario(horario.id)
        await cargarDatos()
        success.value = 'Horario eliminado correctamente.'
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudo eliminar el horario.'
    } finally {
        deletingHorarioId.value = null
    }
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
    if (!mostrarModalHorario.value) return

    if (event.key === 'Escape') {
        cerrarModal()
        return
    }

    if (event.key === 'Tab' && modalRef.value) {
        manejarTabEnContenedor(event, modalRef.value)
    }
}

onMounted(cargarDatos)
onMounted(() => document.addEventListener('keydown', manejarTecladoModal))
onBeforeUnmount(() => document.removeEventListener('keydown', manejarTecladoModal))
</script>

<template>
    <main class="admin-schedules">
        <section class="admin-schedules__container" aria-labelledby="admin-schedules-title">
            <header class="admin-schedules__header">
                <div class="admin-schedules__heading">
                    <h1 id="admin-schedules-title" class="admin-schedules__title">
                        Gestión de horarios
                    </h1>
                    <p class="admin-schedules__intro">
                        Configura las franjas horarias de trabajo de cada empleado.
                    </p>
                </div>

                <div class="admin-schedules__toolbar">
                    <div class="admin-schedules__filter-group">
                        <label class="admin-schedules__filter" for="schedule-employee-filter">
                            <span class="admin-schedules__filter-label">Empleado</span>
                        </label>

                        <select
                            id="schedule-employee-filter"
                            v-model="empleadoSeleccionadoId"
                            class="admin-schedules__select"
                            :disabled="loading || saving"
                            aria-describedby="schedule-filter-help"
                        >
                            <option value="">Selecciona un empleado</option>
                            <option
                                v-for="empleado in empleadosActivos"
                                :key="empleado.id"
                                :value="String(empleado.id)"
                            >
                                {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                            </option>
                        </select>

                        <p id="schedule-filter-help" class="admin-schedules__filter-help">
                            Selecciona un empleado para ver y gestionar sus franjas horarias.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="admin-schedules__button admin-schedules__button--primary"
                        @click="abrirModalCrear($event)"
                        :disabled="!empleadoSeleccionadoId || saving"
                        :aria-disabled="!empleadoSeleccionadoId || saving ? 'true' : 'false'"
                        aria-controls="schedule-modal"
                        :aria-expanded="mostrarModalHorario ? 'true' : 'false'"
                    >
                        Nueva franja
                    </button>
                </div>
            </header>

            <p
                v-if="loading"
                class="admin-schedules__status"
                role="status"
                aria-live="polite"
            >
                Cargando horarios...
            </p>

            <p
                v-if="error"
                class="admin-schedules__message admin-schedules__message--error"
                role="alert"
                aria-live="assertive"
            >
                {{ error }}
            </p>

            <p
                v-if="success"
                class="admin-schedules__message admin-schedules__message--success"
                role="status"
                aria-live="polite"
            >
                {{ success }}
            </p>

            <div
                v-if="!loading && empleadoSeleccionado"
                class="admin-schedules__week"
                role="list"
                :aria-label="`Horarios de ${empleadoSeleccionado.usuario?.nombre ?? 'empleado seleccionado'}`"
            >
                <article
                    v-for="dia in horariosPorDia"
                    :key="dia.value"
                    class="schedule-day-card"
                    role="listitem"
                    :aria-labelledby="`schedule-day-title-${dia.value}`"
                >
                    <header class="schedule-day-card__header">
                        <h2
                            :id="`schedule-day-title-${dia.value}`"
                            class="schedule-day-card__title"
                        >
                            {{ dia.label }}
                        </h2>

                        <button
                            type="button"
                            class="schedule-day-card__add-button"
                            @click="abrirModalCrear($event, dia.value)"
                            :disabled="saving"
                            :aria-label="`Añadir franja para ${dia.label}`"
                        >
                            Añadir franja
                        </button>
                    </header>

                    <ul
                        v-if="dia.horarios.length"
                        class="schedule-day-card__list"
                        role="list"
                    >
                        <li
                            v-for="horario in dia.horarios"
                            :key="horario.id"
                            class="schedule-day-card__item"
                        >
                            <div class="schedule-day-card__info">
                                <p class="schedule-day-card__time">
                                    {{ horario.hora_inicio.slice(0, 5) }} - {{ horario.hora_fin.slice(0, 5) }}
                                </p>
                                <p class="schedule-day-card__meta">
                                    <span class="schedule-day-card__type">
                                        {{ horario.tipo === 'normal' ? 'Normal' : horario.tipo === 'festivo' ? 'Festivo' : 'Cierre' }}
                                    </span>
                                    <span class="schedule-day-card__separator" aria-hidden="true">·</span>
                                    <span class="schedule-day-card__state">
                                        {{ horario.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </p>
                            </div>

                            <div class="schedule-day-card__actions">
                                <button
                                    type="button"
                                    class="schedule-day-card__button schedule-day-card__button--secondary"
                                    @click="abrirModalEdicion(horario, $event)"
                                    :disabled="saving || deletingHorarioId !== null"
                                    :aria-label="`Editar franja de ${horario.hora_inicio.slice(0, 5)} a ${horario.hora_fin.slice(0, 5)} del ${dia.label}`"
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="schedule-day-card__button schedule-day-card__button--danger"
                                    @click="eliminarHorarioConfirmado(horario)"
                                    :disabled="saving || deletingHorarioId !== null"
                                    :aria-label="`Eliminar franja de ${horario.hora_inicio.slice(0, 5)} a ${horario.hora_fin.slice(0, 5)} del ${dia.label}`"
                                >
                                    {{ deletingHorarioId === horario.id ? 'Eliminando...' : 'Eliminar' }}
                                </button>
                            </div>
                        </li>
                    </ul>

                    <p v-else class="schedule-day-card__empty">
                        No hay franjas configuradas.
                    </p>
                </article>
            </div>

            <p
                v-else-if="!loading"
                class="admin-schedules__empty"
                role="status"
                aria-live="polite"
            >
                Selecciona un empleado para gestionar sus horarios.
            </p>
        </section>

        <div
            v-if="mostrarModalHorario"
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
                            Define el día, rango horario y tipo de disponibilidad del empleado.
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
                    @submit.prevent="guardarHorario"
                    :aria-busy="saving ? 'true' : 'false'"
                    novalidate
                >
                    <div class="admin-schedules-form__field">
                        <label class="admin-schedules-form__label" for="schedule-empleado">Empleado</label>
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
                                v-for="empleado in empleadosActivos"
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
                        <label class="admin-schedules-form__label" for="schedule-dia">Día</label>
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
                        <label class="admin-schedules-form__label" for="schedule-hora-inicio">Hora de inicio</label>
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
                        <label class="admin-schedules-form__label" for="schedule-hora-fin">Hora de fin</label>
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
                        <label class="admin-schedules-form__label" for="schedule-tipo">Tipo</label>
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
                            {{
                                saving
                                    ? 'Guardando...'
                                    : modoHorario === 'crear'
                                        ? 'Crear franja'
                                        : 'Guardar cambios'
                            }}
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    </main>
</template>