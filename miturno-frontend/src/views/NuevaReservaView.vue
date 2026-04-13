<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCatalogoServicios, getEmpleadosPorServicio } from '../api/servicios'
import { getDisponibilidad, crearReserva } from '../api/reservas'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const servicios = ref([])
const empleados = ref([])
const disponibilidad = ref([])
const disponibilidadConsultada = ref(false)
const loadingServicios = ref(false)
const loadingEmpleados = ref(false)
const loadingDisponibilidad = ref(false)
const loadingReserva = ref(false)
const error = ref(null)
const success = ref(null)

const form = reactive({
    servicio_id: route.query.servicio_id ? Number(route.query.servicio_id) : '',
    empleado_id: '',
    fecha: '',
    hora: '',
    notas: '',
})

const errores = reactive({
    servicio_id: '',
    fecha: '',
    hora: '',
})

const servicioSeleccionado = computed(() => {
    return servicios.value.find((s) => s.id === Number(form.servicio_id)) || null
})

const cargarServicios = async () => {
    loadingServicios.value = true
    error.value = null

    try {
        servicios.value = await getCatalogoServicios()
    } catch (err) {
        error.value = 'No se pudieron cargar los servicios.'
        console.error(err)
    } finally {
        loadingServicios.value = false
    }
}

const cargarEmpleados = async (servicioId) => {
    if (!servicioId) {
        empleados.value = []
        form.empleado_id = ''
        return
    }

    loadingEmpleados.value = true

    try {
        empleados.value = await getEmpleadosPorServicio(servicioId)
    } catch (err) {
        console.error('Error cargando empleados:', err.response?.data || err)
        empleados.value = []
        form.empleado_id = ''
    } finally {
        loadingEmpleados.value = false
    }
}

watch(() => form.servicio_id, (newId) => {
    cargarEmpleados(newId)
})

const limpiarErroresFormulario = () => {
    errores.servicio_id = ''
    errores.fecha = ''
    errores.hora = ''
}

const validarConsultaDisponibilidad = () => {
    limpiarErroresFormulario()

    if (!form.servicio_id) {
        errores.servicio_id = 'Debes seleccionar un servicio.'
    }

    if (!form.fecha) {
        errores.fecha = 'Debes seleccionar una fecha.'
    }

    return !errores.servicio_id && !errores.fecha
}

const validarReserva = () => {
    const consultaValida = validarConsultaDisponibilidad()

    if (!form.hora) {
        errores.hora = 'Debes seleccionar una hora disponible.'
    }

    return consultaValida && !errores.hora
}

const normalizarDisponibilidad = (data) => {
    if (Array.isArray(data)) return data
    if (Array.isArray(data?.slots_disponibles)) return data.slots_disponibles
    if (Array.isArray(data?.horas)) return data.horas
    if (Array.isArray(data?.data)) return data.data
    return []
}

const consultarDisponibilidad = async () => {
    error.value = null
    success.value = null
    disponibilidad.value = []
    disponibilidadConsultada.value = false
    form.hora = ''

    const esValido = validarConsultaDisponibilidad()

    if (!esValido) {
        error.value = 'Revisa los campos obligatorios antes de consultar la disponibilidad.'
        return
    }

    loadingDisponibilidad.value = true

    try {
        const data = await getDisponibilidad({
        servicio_id: form.servicio_id,
        fecha: form.fecha,
        empleado_id: form.empleado_id || undefined,
        })

        disponibilidad.value = normalizarDisponibilidad(data)
        disponibilidadConsultada.value = true
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = err.response?.data?.message || 'No se pudo consultar la disponibilidad.'
        disponibilidadConsultada.value = true
    } finally {
        loadingDisponibilidad.value = false
    }
}

const construirFechaHoraInicio = () => {
    return `${form.fecha} ${form.hora}:00`
}

const submit = async () => {
    error.value = null
    success.value = null

    const esValido = validarReserva()

    if (!esValido) {
        error.value = 'Completa los campos obligatorios para confirmar la reserva.'
        return
    }

    loadingReserva.value = true

    try {
        const payload = {
            usuario_id: auth.user?.id,
            servicio_id: Number(form.servicio_id),
            empleado_id: form.empleado_id ? Number(form.empleado_id) : null,
            fecha_hora_inicio: construirFechaHoraInicio(),
            notas: form.notas || null,
        }

        await crearReserva(payload)

        success.value = 'Reserva creada correctamente.'
        setTimeout(() => {
            router.push('/dashboard')
        }, 1200)
    } catch (err) {
        const errors = err.response?.data?.errors
        if (errors) {
            error.value = Object.values(errors).flat().join(' | ')
        } else {
            error.value = err.response?.data?.message || 'No se pudo crear la reserva.'
        }
        console.error(err.response?.data || err)
    } finally {
        loadingReserva.value = false
    }
}

onMounted(() => {
    cargarServicios()
    if (form.servicio_id) {
        cargarEmpleados(form.servicio_id)
    }
})
</script>

<template>
    <main class="booking-form">
        <section class="booking-form__container" aria-labelledby="booking-form-title">
            <header class="booking-form__header">
                <h1 id="booking-form-title" class="booking-form__title">Nueva reserva</h1>
                <p class="booking-form__intro">
                    Selecciona el servicio, el día y una hora disponible.
                </p>
            </header>

            <p
                v-if="loadingServicios"
                class="booking-form__status"
                role="status"
                aria-live="polite"
            >
                Cargando datos...
            </p>

            <p
                v-if="error"
                class="booking-form__message booking-form__message--error"
                role="alert"
                aria-live="assertive"
            >
                {{ error }}
            </p>

            <p
                v-if="success"
                class="booking-form__message booking-form__message--success"
                role="status"
                aria-live="polite"
            >
                {{ success }}
            </p>

            <form
                class="booking-form__form"
                @submit.prevent="submit"
                :aria-busy="loadingReserva ? 'true' : 'false'"
                novalidate
            >
                <div class="booking-form__field booking-form__field--full">
                    <label class="booking-form__label" for="servicio">Servicio</label>
                    <select
                        id="servicio"
                        v-model="form.servicio_id"
                        class="booking-form__select"
                        required
                        :aria-invalid="errores.servicio_id ? 'true' : 'false'"
                        :aria-describedby="errores.servicio_id ? 'servicio-error' : undefined"
                    >
                        <option disabled value="">Selecciona un servicio</option>
                        <option
                        v-for="servicio in servicios"
                        :key="servicio.id"
                        :value="servicio.id"
                        >
                        {{ servicio.nombre }} - {{ servicio.duracion_minutos }} min - {{ servicio.precio }} €
                        </option>
                    </select>
                    <p
                        v-if="errores.servicio_id"
                        id="servicio-error"
                        class="booking-form__field-error"
                        aria-live="polite"
                    >
                        {{ errores.servicio_id }}
                    </p>
                </div>

                <article
                    v-if="servicioSeleccionado"
                    class="booking-form__summary"
                    :aria-labelledby="`service-summary-title-${servicioSeleccionado.id}`"
                >
                    <h2
                        :id="`service-summary-title-${servicioSeleccionado.id}`"
                        class="booking-form__summary-title"
                    >
                        {{ servicioSeleccionado.nombre }}
                    </h2>

                    <p class="booking-form__summary-text">
                        {{ servicioSeleccionado.descripcion }}
                    </p>

                    <dl class="booking-form__summary-meta">
                        <div class="booking-form__summary-item">
                            <dt>Duración</dt>
                            <dd>{{ servicioSeleccionado.duracion_minutos }} min</dd>
                        </div>
                        <div class="booking-form__summary-item">
                            <dt>Precio</dt>
                            <dd>{{ servicioSeleccionado.precio }} €</dd>
                        </div>
                    </dl>
                </article>

                <div class="booking-form__field">
                    <label class="booking-form__label" for="empleado">
                        Empleado
                    </label>
                    <div class="booking-form__select-wrapper">
                        <select
                            id="empleado"
                            v-model="form.empleado_id"
                            class="booking-form__select"
                            :disabled="!empleados.length || loadingEmpleados"
                            aria-label="Seleccionar empleado disponible"
                        >
                            <option value="">Seleccione un empleado</option>
                            <option
                                v-for="empleado in empleados"
                                :key="empleado.id"
                                :value="empleado.id"
                            >
                                {{ empleado.nombre }}
                            </option>
                        </select>
                        <span 
                            v-if="loadingEmpleados" 
                            class="booking-form__loading"
                            aria-live="polite"
                        >
                            Cargando empleados...
                        </span>
                    </div>
                </div>

                <div class="booking-form__field">
                    <label class="booking-form__label" for="fecha">Fecha</label>
                    <input
                        id="fecha"
                        v-model="form.fecha"
                        class="booking-form__input"
                        type="date"
                        required
                        :aria-invalid="errores.fecha ? 'true' : 'false'"
                        :aria-describedby="errores.fecha ? 'fecha-error' : 'fecha-help'"
                    />
                    <p id="fecha-help" class="booking-form__help">
                        Selecciona una fecha para consultar la disponibilidad.
                    </p>
                    <p
                        v-if="errores.fecha"
                        id="fecha-error"
                        class="booking-form__field-error"
                        aria-live="polite"
                    >
                        {{ errores.fecha }}
                    </p>
                </div>

                <div class="booking-form__actions booking-form__actions--full">
                    <button
                        type="button"
                        class="booking-form__button booking-form__button--secondary"
                        @click="consultarDisponibilidad"
                        :disabled="loadingDisponibilidad"
                        :aria-busy="loadingDisponibilidad ? 'true' : 'false'"
                    >
                        {{ loadingDisponibilidad ? 'Consultando...' : 'Consultar disponibilidad' }}
                    </button>
                </div>

                <fieldset
                    v-if="disponibilidad.length"
                    class="booking-form__field booking-form__field--full booking-form__times"
                    :aria-describedby="errores.hora ? 'hora-error' : 'hora-help'"
                >
                    <legend class="booking-form__legend">Horas disponibles</legend>
                    <p id="hora-help" class="booking-form__help">
                        Elige una única hora disponible para tu reserva.
                    </p>

                    <div class="booking-form__hours-grid">
                        <label
                            v-for="hora in disponibilidad"
                            :key="hora"
                            class="booking-form__hour-option"
                            :class="{ 'booking-form__hour-option--selected': form.hora === hora }"
                        >
                            <input
                                v-model="form.hora"
                                class="booking-form__hour-input"
                                type="radio"
                                name="hora"
                                :value="hora"
                            />
                            <span class="booking-form__hour-label">{{ hora }}</span>
                        </label>
                    </div>

                    <p
                        v-if="errores.hora"
                        id="hora-error"
                        class="booking-form__field-error"
                        aria-live="polite"
                    >
                        {{ errores.hora }}
                    </p>
                </fieldset>

                <div
                    v-else-if="disponibilidadConsultada && !loadingDisponibilidad"
                    class="booking-form__empty"
                    role="status"
                    aria-live="polite"
                >
                    No hay horas disponibles para esa fecha.
                </div>

                <div class="booking-form__field booking-form__field--full">
                    <label class="booking-form__label" for="notas">Notas</label>
                    <textarea
                        id="notas"
                        v-model="form.notas"
                        class="booking-form__textarea"
                        rows="4"
                        placeholder="Añade una nota para tu reserva"
                    ></textarea>
                </div>

                <div class="booking-form__actions booking-form__actions--full">
                    <button
                        type="submit"
                        class="booking-form__button booking-form__button--primary"
                        :disabled="loadingReserva"
                        :aria-busy="loadingReserva ? 'true' : 'false'"
                    >
                        {{ loadingReserva ? 'Guardando reserva...' : 'Confirmar reserva' }}
                    </button>
                </div>
            </form>
        </section>
    </main>
</template>