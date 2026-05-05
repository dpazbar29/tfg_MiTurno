<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCatalogoServicios, getEmpleadosPorServicio } from '../api/servicios'
import { getDisponibilidad, crearReserva } from '../api/reservas'
import { useAuthStore } from '../stores/auth'

import StatusMessage from '@/components/feedback/StatusMessage.vue'
import BookingServiceSelect from '@/components/booking/BookingServiceSelect.vue'
import BookingServiceSummary from '@/components/booking/BookingServiceSummary.vue'
import BookingEmployeeSelect from '@/components/booking/BookingEmployeeSelect.vue'
import BookingTimeSlots from '@/components/booking/BookingTimeSlots.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

// Datos base y estados de carga.
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

// Formulario reactivo de nueva reserva.
// servicio_id puede llegar preseleccionado por query param.
const form = reactive({
    servicio_id: route.query.servicio_id ? Number(route.query.servicio_id) : '',
    empleado_id: '',
    fecha: '',
    hora: '',
    notas: '',
})

// Errores locales para validación previa.
const errores = reactive({
    servicio_id: '',
    fecha: '',
    hora: '',
})

// Servicio actualmente seleccionado.
const servicioSeleccionado = computed(() => {
    return servicios.value.find((s) => s.id === Number(form.servicio_id)) || null
})

// Etiqueta legible para un empleado.
const nombreEmpleado = (empleado) => {
    const usuario = empleado?.usuario
    if (usuario) {
        return `${usuario.nombre} ${usuario.apellidos}`.trim()
    }

    return empleado?.nombre || 'Profesional'
}

// Limpia slots disponibles y hora elegida.
const limpiarDisponibilidad = () => {
    disponibilidad.value = []
    disponibilidadConsultada.value = false
    form.hora = ''
}

// Carga catálogo de servicios.
const cargarServicios = async () => {
    loadingServicios.value = true
    error.value = null

    try {
        const response = await getCatalogoServicios()
        servicios.value = response?.data ?? response ?? []
    } catch (err) {
        error.value = 'No se pudieron cargar los servicios.'
        console.error(err.response?.data || err)
    } finally {
        loadingServicios.value = false
    }
}

// Carga empleados disponibles para un servicio.
const cargarEmpleados = async (servicioId) => {
    if (!servicioId) {
        empleados.value = []
        form.empleado_id = ''
        limpiarDisponibilidad()
        return
    }

    loadingEmpleados.value = true

    try {
        const response = await getEmpleadosPorServicio(servicioId)
        empleados.value = response?.data ?? response ?? []

        const existeEmpleadoSeleccionado = empleados.value.some(
            (empleado) => Number(empleado.id) === Number(form.empleado_id),
        )

        if (!existeEmpleadoSeleccionado) {
            form.empleado_id = ''
        }
    } catch (err) {
        console.error('Error cargando empleados:', err.response?.data || err)
        empleados.value = []
        form.empleado_id = ''
    } finally {
        loadingEmpleados.value = false
    }
}

// Si cambia el servicio, se invalida la disponibilidad anterior y se recargan los empleados relacionados.
watch(
    () => form.servicio_id,
    async (newId, oldId) => {
        if (newId !== oldId) {
        limpiarDisponibilidad()
    }
    await cargarEmpleados(newId)
  },
)

// Si cambia el empleado, la disponibilidad anterior deja de ser válida.
watch(
    () => form.empleado_id,
    () => {
        limpiarDisponibilidad()
    },
)

// Si cambia la fecha, también se invalida la disponibilidad calculada.
watch(
    () => form.fecha,
    () => {
        limpiarDisponibilidad()
    },
)

// Limpia errores del formulario local.
const limpiarErroresFormulario = () => {
    errores.servicio_id = ''
    errores.fecha = ''
    errores.hora = ''
}

// Valida datos mínimos antes de consultar disponibilidad.
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

// Valida además que haya una hora elegida antes de confirmar la reserva.
const validarReserva = () => {
    const consultaValida = validarConsultaDisponibilidad()

    if (!form.hora) {
        errores.hora = 'Debes seleccionar una hora disponible.'
    }

    return consultaValida && !errores.hora
}

// Normaliza distintos formatos posibles de respuesta del backend.
const normalizarDisponibilidad = (data) => {
    if (Array.isArray(data)) return data
    if (Array.isArray(data?.slots_disponibles)) return data.slots_disponibles
    if (Array.isArray(data?.horas)) return data.horas
    if (Array.isArray(data?.data)) return data.data
    return []
}

// Consulta slots disponibles en función del servicio, fecha y empleado opcional.
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
            servicio_id: Number(form.servicio_id),
            fecha: form.fecha,
            empleado_id: form.empleado_id ? Number(form.empleado_id) : undefined,
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

// Construye fecha y hora en el formato esperado por el backend.
const construirFechaHoraInicio = () => {
    return `${form.fecha} ${form.hora}:00`
}

// Envía la reserva.
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
            notas: form.notas?.trim() || null,
        }

        await crearReserva(payload)

        success.value = 'Reserva creada correctamente.'

        // Tras una breve pausa para mostrar feedback, redirige al dashboard.
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

// Carga inicial de servicios y, si procede, de empleados del servicio preseleccionado.
onMounted(async () => {
    await cargarServicios()

    if (form.servicio_id) {
        await cargarEmpleados(form.servicio_id)
    }
})
</script>

<template>
    <main class="booking-form">
        <section class="booking-form__container" aria-labelledby="booking-form-title">
            <header class="booking-form__header">
                <h1 id="booking-form-title" class="booking-form__title">
                    Nueva reserva
                </h1>
                <p class="booking-form__intro">
                    Selecciona el servicio, el día y una hora disponible.
                </p>
            </header>

            <StatusMessage
                v-if="loadingServicios"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando datos...
            </StatusMessage>

            <StatusMessage
                v-if="error"
                variant="error"
                role="alert"
                live="assertive"
            >
                {{ error }}
            </StatusMessage>

            <StatusMessage
                v-if="success"
                variant="success"
                role="status"
                live="polite"
            >
                {{ success }}
            </StatusMessage>

            <form
                class="booking-form__form"
                @submit.prevent="submit"
                :aria-busy="loadingReserva ? 'true' : 'false'"
                novalidate
            >
                <BookingServiceSelect
                    v-model="form.servicio_id"
                    :servicios="servicios"
                    :error="errores.servicio_id"
                />

                <BookingServiceSummary
                    :servicio="servicioSeleccionado"
                />

                <BookingEmployeeSelect
                    v-model="form.empleado_id"
                    :empleados="empleados"
                    :loading="loadingEmpleados"
                    :get-employee-name="nombreEmpleado"
                />

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

                <BookingTimeSlots
                    v-model="form.hora"
                    :disponibilidad="disponibilidad"
                    :error="errores.hora"
                />

                <div
                    v-if="!disponibilidad.length && disponibilidadConsultada && !loadingDisponibilidad"
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