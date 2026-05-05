<script setup>
import { ref, computed, onMounted } from 'vue'
import { getReservasEmpleado, updateReserva } from '../../api/reservas'

import EmpleadoReservasToolbar from '../../components/empleado/EmpleadoReservasToolbar.vue'
import EmpleadoReservasFilters from '../../components/empleado/EmpleadoReservasFilters.vue'
import EmpleadoReservasTable from '../../components/empleado/EmpleadoReservasTable.vue'
import EmpleadoReservasPagination from '../../components/empleado/EmpleadoReservasPagination.vue'
import StatusMessage from '@/components/feedback/StatusMessage.vue'

// Estado principal de la vista.
const reservas = ref([])
const meta = ref(null)
const loading = ref(false)
const updatingEstadoId = ref(null)

// Mensajes globales.
const error = ref(null)
const success = ref(null)

// Filtros activos del listado.
// Incluyen además la página actual para mantener la navegación.
const filtros = ref({
    fecha: '',
    estado: '',
    busqueda: '',
    page: 1,
})

// Opciones visibles para filtrar por estado.
const estados = [
    { value: '', label: 'Todos' },
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'confirmada', label: 'Confirmada' },
    { value: 'cancelada', label: 'Cancelada' },
    { value: 'completada', label: 'Completada' },
    { value: 'ausencia', label: 'Ausencia' },
]

// Estados que el empleado puede aplicar como acción directa sobre una cita.
const estadosAccion = [
    { value: 'confirmada', label: 'Confirmar' },
    { value: 'completada', label: 'Completar' },
    { value: 'ausencia', label: 'Ausencia' },
    { value: 'cancelada', label: 'Cancelar' },
]

// Indica si hay datos para mostrar.
const hayReservas = computed(() => reservas.value.length > 0)

// Objeto derivado de paginación normalizado.
// Sirve para no depender directamente de la forma exacta de meta en la plantilla.
const pagination = computed(() => {
    if (!meta.value) return null

    return {
        current_page: meta.value.current_page ?? 1,
        last_page: meta.value.last_page ?? 1,
        per_page: meta.value.per_page ?? 15,
        total: meta.value.total ?? reservas.value.length,
        from: meta.value.from ?? 0,
        to: meta.value.to ?? reservas.value.length,
    }
})

// Carga las reservas del empleado según filtros y página.
const cargarReservas = async (page = 1) => {
    loading.value = true
    error.value = null

    try {
        const response = await getReservasEmpleado({
            ...filtros.value,
            page,
        })

        // Se espera una respuesta paginada con data + metadatos.
        reservas.value = response?.data ?? []
        meta.value = {
            current_page: response?.current_page ?? 1,
            last_page: response?.last_page ?? 1,
            per_page: response?.per_page ?? 15,
            total: response?.total ?? reservas.value.length,
            from: response?.from ?? (reservas.value.length ? 1 : 0),
            to: response?.to ?? reservas.value.length,
        }

        filtros.value.page = page
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudieron cargar tus citas.'
        reservas.value = []
        meta.value = null
    } finally {
        loading.value = false
    }
}

// Aplica filtros reiniciando a la primera página.
const aplicarFiltros = () => {
    success.value = null
    cargarReservas(1)
}

// Limpia todos los filtros y vuelve a la página 1.
const limpiarFiltros = () => {
    filtros.value = {
        fecha: '',
        estado: '',
        busqueda: '',
        page: 1,
    }

    success.value = null
    cargarReservas(1)
}

// Cambia de página si el valor solicitado está dentro del rango válido.
const cambiarPagina = (page) => {
    if (!pagination.value) return
    if (page < 1 || page > pagination.value.last_page) return
    cargarReservas(page)
}

// Actualiza el estado de una reserva con confirmación previa.
const cambiarEstadoReserva = async (reserva, nuevoEstado) => {
    if (!reserva?.id) return

    const confirmacion = window.confirm(
        `¿Confirmas cambiar el estado de la cita a "${nuevoEstado}"?`,
    )

    if (!confirmacion) return

    updatingEstadoId.value = reserva.id
    error.value = null
    success.value = null

    try {
        await updateReserva(reserva.id, { estado: nuevoEstado })
        success.value = `La cita se ha actualizado a ${nuevoEstado}.`

        // Se recarga manteniendo la página actual.
        await cargarReservas(filtros.value.page || 1)
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = err.response?.data?.message || 'No se pudo actualizar el estado de la cita.'
    } finally {
        updatingEstadoId.value = null
    }
}

// Carga inicial al montar la vista.
onMounted(() => {
    cargarReservas()
})
</script>

<template>
    <main class="empleado-reservas">
        <section
            class="empleado-reservas__container"
            aria-labelledby="empleado-reservas-title"
        >
            <EmpleadoReservasToolbar
                title="Mis citas"
                intro="Consulta las reservas asignadas a tu perfil y revisa todos los datos del cliente y del servicio."
            />

            <EmpleadoReservasFilters
                :filtros="filtros"
                :estados="estados"
                @apply-filters="aplicarFiltros"
                @reset-filters="limpiarFiltros"
            />

            <StatusMessage
                v-if="loading"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando citas...
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

            <div
                v-if="!loading && hayReservas"
                class="empleado-reservas__table-container"
            >
                <EmpleadoReservasTable
                    :reservas="reservas"
                    :estados-accion="estadosAccion"
                    :updating-estado-id="updatingEstadoId"
                    @change-status="cambiarEstadoReserva"
                />

                <EmpleadoReservasPagination
                    :pagination="pagination"
                    :loading="loading"
                    @change-page="cambiarPagina"
                />
            </div>

            <p
                v-else-if="!loading"
                class="empleado-reservas__empty"
                role="status"
                aria-live="polite"
            >
                No tienes citas que coincidan con los filtros seleccionados.
            </p>
        </section>
    </main>
</template>