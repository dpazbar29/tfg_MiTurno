<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { getEmpleados } from '@/api/empleados'
import {  getServicios,  getCatalogoServicios,  getEmpleadosPorServicio, } from '@/api/servicios'
import {  getReservasAdmin,  crearReserva,  updateReserva,  deleteReserva,  getDisponibilidad, } from '@/api/reservas'
import { buscarClientes } from '@/api/usuarios'

import ReservationsToolbar from '@/components/admin/reservations/ReservationsToolbar.vue'
import ReservationsFilters from '@/components/admin/reservations/ReservationsFilters.vue'
import ReservationsTable from '@/components/admin/reservations/ReservationsTable.vue'
import ReservationModal from '@/components/admin/reservations/ReservationModal.vue'
import CreateReservationForm from '@/components/admin/reservations/CreateReservationForm.vue'
import EditReservationForm from '@/components/admin/reservations/EditReservationForm.vue'
import StatusMessage from '@/components/feedback/StatusMessage.vue'

const empleados = ref([])
const servicios = ref([])
const reservas = ref([])

const loading = ref(false)
const saving = ref(false)
const deletingReservaId = ref(null)

const filtros = reactive({
    fecha: '',
    estado: '',
    empleadoid: '',
    servicioid: '',
    busqueda: '',
})

const mostrarModalReserva = ref(false)
const reservaEditando = ref(null)
const modoReserva = ref('crear')

const error = ref(null)
const success = ref(null)

const disponibilidad = ref([])
const disponibilidadConsultada = ref(false)
const loadingDisponibilidad = ref(false)
const loadingEmpleadosDisponibilidad = ref(false)

const formularioCrear = reactive({
    usuarioid: '',
    servicioid: '',
    empleadoid: '',
    fecha: '',
    hora: '',
    notas: '',
})

const erroresCrear = reactive({
    usuarioid: '',
    servicioid: '',
    fecha: '',
    hora: '',
})

const clienteQuery = ref('')
const clientesEncontrados = ref([])
const clienteSeleccionado = ref(null)
const loadingClientes = ref(false)
const mostrarSugerenciasClientes = ref(false)
const clienteActivoIndex = ref(-1)
const clienteSearchTimeout = ref(null)

const estados = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'confirmada', label: 'Confirmada' },
    { value: 'cancelada', label: 'Cancelada' },
    { value: 'completada', label: 'Completada' },
    { value: 'ausencia', label: 'Ausencia' },
]

const tituloModal = computed(() =>
    modoReserva.value === 'crear' ? 'Nueva reserva' : 'Editar reserva',
)

const descripcionModal = computed(() =>
    modoReserva.value === 'crear'
        ? 'Crea una reserva consultando antes la disponibilidad.'
        : 'Edita una reserva existente.',
)

const servicioSeleccionadoCrear = computed(() => {
    return servicios.value.find(
        (servicio) => Number(servicio.id) === Number(formularioCrear.servicioid),
    ) || null
})

const clienteListboxId = computed(() => 'crear-cliente-listbox')

const clienteActivoId = computed(() => {
    if (
        clienteActivoIndex.value < 0 ||
        clienteActivoIndex.value >= clientesEncontrados.value.length
    ) {
        return undefined
    }

    return `crear-cliente-opcion-${clientesEncontrados.value[clienteActivoIndex.value].id}`
})

const nombreEmpleadoOpcion = (empleado) => {
    const usuario = empleado?.usuario

    if (usuario) {
        return `${usuario.nombre || ''} ${usuario.apellidos || ''}`.trim()
    }

    return empleado?.nombre || 'Profesional'
}

const nombreClienteOpcion = (cliente) => {
    const nombre = cliente?.nombre || ''
    const apellidos = cliente?.apellidos || ''
    return `${nombre} ${apellidos}`.trim()
}

const descripcionClienteOpcion = (cliente) => {
    return cliente?.email || cliente?.telefono || `ID ${cliente?.id}`
}

const cargarReservas = async () => {
    loading.value = true
    error.value = null

    try {
        const filtrosApi = {}

        if (filtros.fecha) {
            filtrosApi.fecha = filtros.fecha
        }

        if (filtros.estado) {
            filtrosApi.estado = filtros.estado
        }

        if (filtros.empleadoid) {
            filtrosApi.empleado_id = Number(filtros.empleadoid)
        }

        if (filtros.servicioid) {
            filtrosApi.servicio_id = Number(filtros.servicioid)
        }

        if (filtros.busqueda?.trim()) {
            filtrosApi.busqueda = filtros.busqueda.trim()
        }

        const [listaEmpleados, listaServicios, listaReservas] = await Promise.all([
            getEmpleados(),
            getServicios(),
            getReservasAdmin(filtrosApi),
        ])

        empleados.value = listaEmpleados?.data ?? listaEmpleados ?? []
        servicios.value = listaServicios?.data ?? listaServicios ?? []
        reservas.value = listaReservas?.data ?? listaReservas ?? []
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudieron cargar las reservas.'
    } finally {
        loading.value = false
    }
}

const aplicarFiltros = () => {
    cargarReservas()
}

const limpiarFiltros = () => {
    filtros.fecha = ''
    filtros.estado = ''
    filtros.empleadoid = ''
    filtros.servicioid = ''
    filtros.busqueda = ''

    cargarReservas()
}

const limpiarDisponibilidadCrear = () => {
    disponibilidad.value = []
    disponibilidadConsultada.value = false
    formularioCrear.hora = ''
    erroresCrear.hora = ''
}

const limpiarErroresCrear = () => {
    erroresCrear.usuarioid = ''
    erroresCrear.servicioid = ''
    erroresCrear.fecha = ''
    erroresCrear.hora = ''
}

const limpiarClienteCrear = () => {
    formularioCrear.usuarioid = ''
    clienteQuery.value = ''
    clienteSeleccionado.value = null
    clientesEncontrados.value = []
    mostrarSugerenciasClientes.value = false
    clienteActivoIndex.value = -1
}

const seleccionarCliente = (cliente) => {
    formularioCrear.usuarioid = cliente.id
    clienteSeleccionado.value = cliente
    clienteQuery.value = nombreClienteOpcion(cliente)
    clientesEncontrados.value = []
    mostrarSugerenciasClientes.value = false
    clienteActivoIndex.value = -1
    erroresCrear.usuarioid = ''
}

const buscarClientesRemoto = async (query) => {
    const texto = String(query).trim()

    if (texto.length < 2) {
        clientesEncontrados.value = []
        mostrarSugerenciasClientes.value = false
        clienteActivoIndex.value = -1
        return
    }

    loadingClientes.value = true

    try {
        const resultados = await buscarClientes(texto)
        clientesEncontrados.value = resultados ?? []
        mostrarSugerenciasClientes.value = true
        clienteActivoIndex.value = resultados.length ? 0 : -1
    } catch (err) {
        console.error(err.response?.data || err)
        clientesEncontrados.value = []
        mostrarSugerenciasClientes.value = false
        clienteActivoIndex.value = -1
    } finally {
        loadingClientes.value = false
    }
}

const manejarInputCliente = () => {
    formularioCrear.usuarioid = ''
    clienteSeleccionado.value = null
    erroresCrear.usuarioid = ''

    clearTimeout(clienteSearchTimeout.value)
    clienteSearchTimeout.value = setTimeout(() => {
        buscarClientesRemoto(clienteQuery.value)
    }, 300)
}

const manejarFocusCliente = () => {
    if (clientesEncontrados.value.length) {
        mostrarSugerenciasClientes.value = true
    }
}

const manejarBlurCliente = () => {
    setTimeout(() => {
        mostrarSugerenciasClientes.value = false
    }, 150)
}

const manejarTecladoCliente = (event) => {
    if (!mostrarSugerenciasClientes.value || !clientesEncontrados.value.length) {
        if (event.key === 'ArrowDown' && clientesEncontrados.value.length) {
            mostrarSugerenciasClientes.value = true
        }
        return
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault()
        clienteActivoIndex.value =
        clienteActivoIndex.value < clientesEncontrados.value.length - 1
            ? clienteActivoIndex.value + 1
            : 0
    } else if (event.key === 'ArrowUp') {
        event.preventDefault()
        clienteActivoIndex.value =
        clienteActivoIndex.value > 0
            ? clienteActivoIndex.value - 1
            : clientesEncontrados.value.length - 1
    } else if (event.key === 'Enter') {
        event.preventDefault()
        if (clienteActivoIndex.value >= 0) {
            seleccionarCliente(clientesEncontrados.value[clienteActivoIndex.value])
        }
    } else if (event.key === 'Escape') {
        mostrarSugerenciasClientes.value = false
        clienteActivoIndex.value = -1
    }
}

const cargarEmpleadosPorServicioAdmin = async (servicioId) => {
    if (!servicioId) {
        formularioCrear.empleadoid = ''
        limpiarDisponibilidadCrear()
        return
    }

    loadingEmpleadosDisponibilidad.value = true

    try {
        const response = await getEmpleadosPorServicio(servicioId)
        empleados.value = response?.data ?? response ?? []

        const existeEmpleadoSeleccionado = empleados.value.some(
            (empleado) => Number(empleado.id) === Number(formularioCrear.empleadoid),
        )

        if (!existeEmpleadoSeleccionado) {
            formularioCrear.empleadoid = ''
        }
    } catch (err) {
        console.error(err.response?.data || err)
        empleados.value = []
        formularioCrear.empleadoid = ''
    } finally {
        loadingEmpleadosDisponibilidad.value = false
    }
}

watch(
    () => formularioCrear.servicioid,
    async (newId, oldId) => {
        if (modoReserva.value !== 'crear') return

        if (newId !== oldId) {
            limpiarDisponibilidadCrear()
            await cargarEmpleadosPorServicioAdmin(newId)
        }
    },
)

watch(
    () => formularioCrear.empleadoid,
    () => {
        if (modoReserva.value !== 'crear') return
        limpiarDisponibilidadCrear()
    },
)

watch(
    () => formularioCrear.fecha,
    () => {
        if (modoReserva.value !== 'crear') return
        limpiarDisponibilidadCrear()
    },
)

const validarConsultaDisponibilidadCrear = () => {
    limpiarErroresCrear()

    if (!formularioCrear.usuarioid) {
        erroresCrear.usuarioid = 'Debes seleccionar un cliente de la lista.'
    }

    if (!formularioCrear.servicioid) {
        erroresCrear.servicioid = 'Debes seleccionar un servicio.'
    }

    if (!formularioCrear.fecha) {
        erroresCrear.fecha = 'Debes seleccionar una fecha.'
    }

    return (
        !erroresCrear.usuarioid &&
        !erroresCrear.servicioid &&
        !erroresCrear.fecha
    )
}

const validarReservaCrear = () => {
    const consultaValida = validarConsultaDisponibilidadCrear()

    if (!formularioCrear.hora) {
        erroresCrear.hora = 'Debes seleccionar una hora disponible.'
    }

    return consultaValida && !erroresCrear.hora
}

const normalizarDisponibilidad = (data) => {
    if (Array.isArray(data)) return data
    if (Array.isArray(data?.slots_disponibles)) return data.slots_disponibles
    if (Array.isArray(data?.slotsdisponibles)) return data.slotsdisponibles
    if (Array.isArray(data?.horas)) return data.horas
    if (Array.isArray(data?.data)) return data.data
    return []
}

const consultarDisponibilidadCrear = async () => {
    error.value = null
    success.value = null
    disponibilidad.value = []
    disponibilidadConsultada.value = false
    formularioCrear.hora = ''

    const esValido = validarConsultaDisponibilidadCrear()

    if (!esValido) {
        error.value = 'Revisa los campos obligatorios antes de consultar la disponibilidad.'
        return
    }

    loadingDisponibilidad.value = true

    try {
        const payload = {
            servicio_id: formularioCrear.servicioid
                ? Number(formularioCrear.servicioid)
                : null,
            fecha: formularioCrear.fecha,
            empleado_id: formularioCrear.empleadoid
                ? Number(formularioCrear.empleadoid)
                : null,
        }

        const data = await getDisponibilidad(payload)

        disponibilidad.value = normalizarDisponibilidad(data)
        disponibilidadConsultada.value = true
    } catch (err) {
        console.error(err.response?.data || err)
        error.value =
            err.response?.data?.message || 'No se pudo consultar la disponibilidad.'
        disponibilidadConsultada.value = true
    } finally {
        loadingDisponibilidad.value = false
    }
}

const construirFechaHoraInicioCrear = () => {
    return `${formularioCrear.fecha} ${formularioCrear.hora}:00`
}

const abrirModalCrear = async () => {
    error.value = null
    success.value = null
    modoReserva.value = 'crear'
    reservaEditando.value = null
    mostrarModalReserva.value = true

    limpiarErroresCrear()
    limpiarDisponibilidadCrear()
    limpiarClienteCrear()

    formularioCrear.servicioid = ''
    formularioCrear.empleadoid = ''
    formularioCrear.fecha = ''
    formularioCrear.hora = ''
    formularioCrear.notas = ''

    const response = await getCatalogoServicios().catch(() => null)

    if (response) {
        servicios.value = response?.data ?? response ?? []
    }
}

const abrirModalEdicion = async (reserva) => {
    error.value = null
    success.value = null
    modoReserva.value = 'editar'
    reservaEditando.value = reserva
    mostrarModalReserva.value = true
}

const cerrarModal = () => {
    mostrarModalReserva.value = false
    reservaEditando.value = null
    modoReserva.value = 'crear'

    limpiarErroresCrear()
    limpiarDisponibilidadCrear()
    limpiarClienteCrear()

    clearTimeout(clienteSearchTimeout.value)

    formularioCrear.servicioid = ''
    formularioCrear.empleadoid = ''
    formularioCrear.fecha = ''
    formularioCrear.hora = ''
    formularioCrear.notas = ''
}

const guardarReservaEditar = async (values, setErrors) => {
    saving.value = true
    error.value = null
    success.value = null

    try {
        const payload = {
            usuario_id: Number(
                reservaEditando.value?.usuarioid ?? reservaEditando.value?.usuario_id
            ),
            servicio_id: Number(values.servicioid),
            fecha_hora_inicio: values.fechahorainicio,
            estado: values.estado,
            notas: values.notas?.trim() || null,
        }

        if (values.empleadoid) {
            payload.empleado_id = Number(values.empleadoid)
        }

        await updateReserva(reservaEditando.value.id, payload)
        success.value = 'Reserva actualizada correctamente.'
        await cargarReservas()
        cerrarModal()
    } catch (err) {
        console.error(err.response?.data || err)

        const backendErrors = err.response?.data?.errors

        if (backendErrors) {
            setErrors({
                empleadoid: backendErrors.empleado_id?.[0],
                servicioid: backendErrors.servicio_id?.[0],
                fechahorainicio: backendErrors.fecha_hora_inicio?.[0],
                estado: backendErrors.estado?.[0],
                notas: backendErrors.notas?.[0],
            })
        } else {
            error.value = 'No se pudo actualizar la reserva.'
        }
    } finally {
        saving.value = false
    }
}

const guardarReservaCrear = async () => {
    error.value = null
    success.value = null

    const esValido = validarReservaCrear()

    if (!esValido) {
        error.value = 'Completa los campos obligatorios para confirmar la reserva.'
        return
    }

    saving.value = true

    try {
        const payload = {
            usuario_id: Number(formularioCrear.usuarioid),
            servicio_id: Number(formularioCrear.servicioid),
            fecha_hora_inicio: construirFechaHoraInicioCrear(),
            notas: formularioCrear.notas?.trim() || null,
        }

        if (formularioCrear.empleadoid) {
            payload.empleado_id = Number(formularioCrear.empleadoid)
        }

        await crearReserva(payload)
        success.value = 'Reserva creada correctamente.'
        await cargarReservas()
        cerrarModal()
    } catch (err) {
        console.error(err.response?.data || err)

        const backendErrors = err.response?.data?.errors

        if (backendErrors) {
            error.value = Object.values(backendErrors).flat().join(' ')
        } else {
            error.value = err.response?.data?.message || 'No se pudo crear la reserva.'
        }
    } finally {
        saving.value = false
    }
}

const eliminarReservaConfirmado = async (reserva) => {
    if (!window.confirm('¿Seguro que quieres eliminar esta reserva?')) return

    deletingReservaId.value = reserva.id
    error.value = null
    success.value = null

    try {
        await deleteReserva(reserva.id)
        await cargarReservas()
        success.value = 'Reserva eliminada correctamente.'
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudo eliminar la reserva.'
    } finally {
        deletingReservaId.value = null
    }
}

const cambiarEstadoReserva = async (reserva, nuevoEstado) => {
    if (!window.confirm(`¿Confirmas cambiar el estado a ${nuevoEstado}?`)) return

    try {
        await updateReserva(reserva.id, { estado: nuevoEstado })
        await cargarReservas()
        success.value = `Estado cambiado a ${nuevoEstado}.`
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudo actualizar el estado.'
    }
}

onMounted(() => {
    cargarReservas()
})

onBeforeUnmount(() => {
    clearTimeout(clienteSearchTimeout.value)
})
</script>

<template>
    <main class="admin-reservas">
        <section class="admin-reservas__container" aria-labelledby="admin-reservas-title">
            <ReservationsToolbar
                :saving="saving"
                :modal-abierto="mostrarModalReserva"
                @create-reservation="abrirModalCrear"
            />

            <ReservationsFilters
                :filtros="filtros"
                :estados="estados"
                :empleados="empleados"
                :servicios="servicios"
                :nombre-empleado-opcion="nombreEmpleadoOpcion"
                @apply-filters="aplicarFiltros"
                @reset-filters="limpiarFiltros"
            />

            <StatusMessage
                v-if="loading"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando reservas...
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

            <ReservationsTable
                v-if="!loading && reservas.length"
                :reservas="reservas"
                :estados="estados"
                :saving="saving"
                :deleting-reserva-id="deletingReservaId"
                @edit-reservation="abrirModalEdicion"
                @delete-reservation="eliminarReservaConfirmado"
                @change-status="cambiarEstadoReserva"
            />

            <p
                v-else-if="!loading"
                class="admin-reservas__empty"
                role="status"
                aria-live="polite"
            >
                No hay reservas que coincidan con los filtros.
            </p>
        </section>

        <ReservationModal
            :visible="mostrarModalReserva"
            :titulo="tituloModal"
            :descripcion="descripcionModal"
            @close="cerrarModal"
        >
            <CreateReservationForm
                v-if="modoReserva === 'crear'"
                :saving="saving"
                :servicios="servicios"
                :empleados="empleados"
                :disponibilidad="disponibilidad"
                :disponibilidad-consultada="disponibilidadConsultada"
                :loading-disponibilidad="loadingDisponibilidad"
                :loading-empleados-disponibilidad="loadingEmpleadosDisponibilidad"
                :formulario-crear="formularioCrear"
                :errores-crear="erroresCrear"
                :cliente-query="clienteQuery"
                :clientes-encontrados="clientesEncontrados"
                :cliente-seleccionado="clienteSeleccionado"
                :loading-clientes="loadingClientes"
                :mostrar-sugerencias-clientes="mostrarSugerenciasClientes"
                :cliente-listbox-id="clienteListboxId"
                :cliente-activo-id="clienteActivoId"
                :cliente-activo-index="clienteActivoIndex"
                :servicio-seleccionado-crear="servicioSeleccionadoCrear"
                :nombre-empleado-opcion="nombreEmpleadoOpcion"
                :nombre-cliente-opcion="nombreClienteOpcion"
                :descripcion-cliente-opcion="descripcionClienteOpcion"
                @submit="guardarReservaCrear"
                @cancel="cerrarModal"
                @consult-availability="consultarDisponibilidadCrear"
                @client-input="manejarInputCliente"
                @client-focus="manejarFocusCliente"
                @client-blur="manejarBlurCliente"
                @client-keydown="manejarTecladoCliente"
                @select-client="seleccionarCliente"
                @update:cliente-query="clienteQuery = $event"
            />

            <EditReservationForm
                v-else
                :visible="mostrarModalReserva"
                :saving="saving"
                :empleados="empleados"
                :servicios="servicios"
                :estados="estados"
                :reserva-editando="reservaEditando"
                @submit="guardarReservaEditar"
                @cancel="cerrarModal"
            />
        </ReservationModal>
    </main>
</template>