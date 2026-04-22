<script setup>
import {
  ref,
  computed,
  onMounted,
  nextTick,
  onBeforeUnmount,
  watch,
  reactive,
} from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'
import { getEmpleados } from '../../api/empleados'
import {
  getServicios,
  getCatalogoServicios,
  getEmpleadosPorServicio,
} from '../../api/servicios'
import {
  getReservasAdmin,
  crearReserva,
  updateReserva,
  deleteReserva,
  getDisponibilidad,
} from '../../api/reservas'
import { buscarClientes } from '../../api/usuarios'

const empleados = ref([])
const servicios = ref([])
const reservas = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingReservaId = ref(null)

const filtros = ref({
  fecha: '',
  estado: '',
  empleado_id: '',
  servicio_id: '',
  busqueda: '',
})

const mostrarModalReserva = ref(false)
const reservaEditando = ref(null)
const modoReserva = ref('crear')

const error = ref(null)
const success = ref(null)

const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

const disponibilidad = ref([])
const disponibilidadConsultada = ref(false)
const loadingDisponibilidad = ref(false)
const loadingEmpleadosDisponibilidad = ref(false)

const formularioCrear = reactive({
  usuario_id: '',
  servicio_id: '',
  empleado_id: '',
  fecha: '',
  hora: '',
  notas: '',
})

const erroresCrear = reactive({
  usuario_id: '',
  servicio_id: '',
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

const edicionSchema = yup.object({
  usuario_id: yup
    .number()
    .typeError('Selecciona un cliente.')
    .required('El cliente es obligatorio.'),
  empleado_id: yup
    .number()
    .nullable()
    .typeError('Selecciona un empleado.')
    .test('empleado-valido', 'El empleado debe existir.', function (value) {
      return value === null || value === '' || !Number.isNaN(Number(value))
    }),
  servicio_id: yup
    .number()
    .typeError('Selecciona un servicio.')
    .required('El servicio es obligatorio.'),
  fecha_hora_inicio: yup
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

const {
  defineField,
  handleSubmit,
  errors: formErrors,
  resetForm,
  setErrors,
} = useForm({
  validationSchema: edicionSchema,
  initialValues: {
    usuario_id: '',
    empleado_id: '',
    servicio_id: '',
    fecha_hora_inicio: '',
    estado: 'pendiente',
    notas: '',
  },
})

const [usuarioId, usuarioIdAttrs] = defineField('usuario_id')
const [empleadoId, empleadoIdAttrs] = defineField('empleado_id')
const [servicioId, servicioIdAttrs] = defineField('servicio_id')
const [fechaHoraInicio, fechaHoraInicioAttrs] = defineField('fecha_hora_inicio')
const [estado, estadoAttrs] = defineField('estado')
const [notas, notasAttrs] = defineField('notas')

const tituloModal = computed(() =>
  modoReserva.value === 'crear' ? 'Nueva reserva' : 'Editar reserva',
)

const servicioSeleccionadoCrear = computed(() => {
  return servicios.value.find((s) => s.id === Number(formularioCrear.servicio_id)) || null
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

const formatoFechaHora = (valor) => {
  if (!valor) return '-'
  const fecha = new Date(valor)
  if (Number.isNaN(fecha.getTime())) return valor
  return new Intl.DateTimeFormat('es-ES', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(fecha)
}

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

const nombreEmpleado = (reserva) => {
  const nombre = reserva?.empleado?.usuario?.nombre || ''
  const apellidos = reserva?.empleado?.usuario?.apellidos || ''
  return `${nombre} ${apellidos}`.trim() || '-'
}

const nombreCliente = (reserva) => {
  const nombre = reserva?.usuario?.nombre || ''
  const apellidos = reserva?.usuario?.apellidos || ''
  return `${nombre} ${apellidos}`.trim() || '-'
}

const nombreEmpleadoOpcion = (empleado) => {
  const usuario = empleado?.usuario
  if (usuario) return `${usuario.nombre} ${usuario.apellidos}`.trim()
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
    const [listaEmpleados, listaServicios, listaReservas] = await Promise.all([
      getEmpleados(),
      getServicios(),
      getReservasAdmin(filtros.value),
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
  filtros.value = {
    fecha: '',
    estado: '',
    empleado_id: '',
    servicio_id: '',
    busqueda: '',
  }
  cargarReservas()
}

const limpiarDisponibilidadCrear = () => {
  disponibilidad.value = []
  disponibilidadConsultada.value = false
  formularioCrear.hora = ''
  erroresCrear.hora = ''
}

const limpiarErroresCrear = () => {
  erroresCrear.usuario_id = ''
  erroresCrear.servicio_id = ''
  erroresCrear.fecha = ''
  erroresCrear.hora = ''
}

const limpiarClienteCrear = () => {
  formularioCrear.usuario_id = ''
  clienteQuery.value = ''
  clienteSeleccionado.value = null
  clientesEncontrados.value = []
  mostrarSugerenciasClientes.value = false
  clienteActivoIndex.value = -1
}

const seleccionarCliente = (cliente) => {
  formularioCrear.usuario_id = cliente.id
  clienteSeleccionado.value = cliente
  clienteQuery.value = nombreClienteOpcion(cliente)
  clientesEncontrados.value = []
  mostrarSugerenciasClientes.value = false
  clienteActivoIndex.value = -1
  erroresCrear.usuario_id = ''
}

const buscarClientesRemoto = async (query) => {
  const texto = String(query || '').trim()

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
  formularioCrear.usuario_id = ''
  clienteSeleccionado.value = null
  erroresCrear.usuario_id = ''

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
    formularioCrear.empleado_id = ''
    limpiarDisponibilidadCrear()
    return
  }

  loadingEmpleadosDisponibilidad.value = true

  try {
    const response = await getEmpleadosPorServicio(servicioId)
    empleados.value = response?.data ?? response ?? []

    const existeEmpleadoSeleccionado = empleados.value.some(
      (empleado) => Number(empleado.id) === Number(formularioCrear.empleado_id),
    )

    if (!existeEmpleadoSeleccionado) {
      formularioCrear.empleado_id = ''
    }
  } catch (err) {
    console.error(err.response?.data || err)
    empleados.value = []
    formularioCrear.empleado_id = ''
  } finally {
    loadingEmpleadosDisponibilidad.value = false
  }
}

watch(
  () => formularioCrear.servicio_id,
  async (newId, oldId) => {
    if (modoReserva.value !== 'crear') return
    if (newId !== oldId) limpiarDisponibilidadCrear()
    await cargarEmpleadosPorServicioAdmin(newId)
  },
)

watch(
  () => formularioCrear.empleado_id,
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

  if (!formularioCrear.usuario_id) {
    erroresCrear.usuario_id = 'Debes seleccionar un cliente de la lista.'
  }

  if (!formularioCrear.servicio_id) {
    erroresCrear.servicio_id = 'Debes seleccionar un servicio.'
  }

  if (!formularioCrear.fecha) {
    erroresCrear.fecha = 'Debes seleccionar una fecha.'
  }

  return !erroresCrear.usuario_id && !erroresCrear.servicio_id && !erroresCrear.fecha
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
    const data = await getDisponibilidad({
      servicio_id: Number(formularioCrear.servicio_id),
      fecha: formularioCrear.fecha,
      empleado_id: formularioCrear.empleado_id
        ? Number(formularioCrear.empleado_id)
        : undefined,
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

const construirFechaHoraInicioCrear = () => {
  return `${formularioCrear.fecha} ${formularioCrear.hora}:00`
}

const abrirModalCrear = async (event = null) => {
  lastTriggerRef.value = event?.currentTarget || document.activeElement
  error.value = null
  success.value = null
  modoReserva.value = 'crear'
  reservaEditando.value = null
  mostrarModalReserva.value = true

  limpiarErroresCrear()
  disponibilidad.value = []
  disponibilidadConsultada.value = false
  limpiarClienteCrear()

  formularioCrear.servicio_id = ''
  formularioCrear.empleado_id = ''
  formularioCrear.fecha = ''
  formularioCrear.hora = ''
  formularioCrear.notas = ''

  const response = await getCatalogoServicios().catch(() => null)
  if (response) {
    servicios.value = response?.data ?? response ?? []
  }

  await nextTick()
  modalTitleRef.value?.focus()
}

const abrirModalEdicion = async (reserva, event = null) => {
  lastTriggerRef.value = event?.currentTarget || document.activeElement
  error.value = null
  success.value = null
  modoReserva.value = 'editar'
  reservaEditando.value = reserva
  mostrarModalReserva.value = true

  resetForm({
    values: {
      usuario_id: reserva.usuario_id ?? '',
      empleado_id: reserva.empleado_id ?? '',
      servicio_id: reserva.servicio_id ?? '',
      fecha_hora_inicio: formatoDatetimeLocal(reserva.fecha_hora_inicio),
      estado: reserva.estado ?? 'pendiente',
      notas: reserva.notas ?? '',
    },
    errors: {},
  })

  await nextTick()
  modalTitleRef.value?.focus()
}

const cerrarModal = () => {
  mostrarModalReserva.value = false
  reservaEditando.value = null
  modoReserva.value = 'crear'
  limpiarErroresCrear()
  limpiarDisponibilidadCrear()
  limpiarClienteCrear()
  clearTimeout(clienteSearchTimeout.value)

  resetForm({
    values: {
      usuario_id: '',
      empleado_id: '',
      servicio_id: '',
      fecha_hora_inicio: '',
      estado: 'pendiente',
      notas: '',
    },
    errors: {},
  })

  formularioCrear.servicio_id = ''
  formularioCrear.empleado_id = ''
  formularioCrear.fecha = ''
  formularioCrear.hora = ''
  formularioCrear.notas = ''

  lastTriggerRef.value?.focus?.()
}

const guardarReservaEditar = handleSubmit(async (values) => {
  saving.value = true
  error.value = null
  success.value = null

  try {
    const payload = {
      ...values,
      usuario_id: Number(values.usuario_id),
      empleado_id: values.empleado_id ? Number(values.empleado_id) : null,
      servicio_id: Number(values.servicio_id),
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
        usuario_id: backendErrors.usuario_id?.[0],
        empleado_id: backendErrors.empleado_id?.[0],
        servicio_id: backendErrors.servicio_id?.[0],
        fecha_hora_inicio: backendErrors.fecha_hora_inicio?.[0],
        estado: backendErrors.estado?.[0],
        notas: backendErrors.notas?.[0],
      })
    } else {
      error.value = 'No se pudo actualizar la reserva.'
    }
  } finally {
    saving.value = false
  }
})

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
      usuario_id: Number(formularioCrear.usuario_id),
      servicio_id: Number(formularioCrear.servicio_id),
      empleado_id: formularioCrear.empleado_id
        ? Number(formularioCrear.empleado_id)
        : null,
      fecha_hora_inicio: construirFechaHoraInicioCrear(),
      notas: formularioCrear.notas?.trim() || null,
    }

    await crearReserva(payload)
    success.value = 'Reserva creada correctamente.'

    await cargarReservas()
    cerrarModal()
  } catch (err) {
    console.error(err.response?.data || err)
    const backendErrors = err.response?.data?.errors

    if (backendErrors) {
      error.value = Object.values(backendErrors).flat().join(' | ')
    } else {
      error.value = err.response?.data?.message || 'No se pudo crear la reserva.'
    }
  } finally {
    saving.value = false
  }
}

const guardarReserva = async () => {
  if (modoReserva.value === 'crear') {
    await guardarReservaCrear()
  } else {
    await guardarReservaEditar()
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
  if (!window.confirm(`¿Confirmas cambiar el estado a "${nuevoEstado}"?`)) return

  try {
    await updateReserva(reserva.id, { estado: nuevoEstado })
    await cargarReservas()
    success.value = `Estado cambiado a ${nuevoEstado}.`
  } catch (err) {
    console.error(err.response?.data || err)
    error.value = 'No se pudo actualizar el estado.'
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
  if (!mostrarModalReserva.value) return

  if (event.key === 'Escape') {
    cerrarModal()
    return
  }

  if (event.key === 'Tab' && modalRef.value) {
    manejarTabEnContenedor(event, modalRef.value)
  }
}

onMounted(cargarReservas)
onMounted(() => document.addEventListener('keydown', manejarTecladoModal))
onBeforeUnmount(() => {
  document.removeEventListener('keydown', manejarTecladoModal)
  clearTimeout(clienteSearchTimeout.value)
})
</script>

<template>
  <main class="admin-reservas">
    <section class="admin-reservas__container" aria-labelledby="admin-reservas-title">
      <header class="admin-reservas__header">
        <div class="admin-reservas__heading">
          <h1 id="admin-reservas-title" class="admin-reservas__title">
            Gestión de reservas
          </h1>
          <p class="admin-reservas__intro">
            Administra todas las reservas del sistema, crea nuevas manualmente y cambia estados.
          </p>
        </div>

        <div class="admin-reservas__toolbar">
          <button
            type="button"
            class="admin-reservas__button admin-reservas__button--primary"
            @click="abrirModalCrear($event)"
            :disabled="saving"
            aria-controls="reservation-modal"
            :aria-expanded="mostrarModalReserva ? 'true' : 'false'"
          >
            Nueva reserva
          </button>
        </div>
      </header>

      <section class="admin-reservas__filters" aria-labelledby="admin-reservas-filters-title">
        <h2 id="admin-reservas-filters-title" class="sr-only">Filtros de reservas</h2>

        <div class="admin-reservas__filter-grid">
          <div class="admin-reservas__filter-field">
            <label class="admin-reservas__filter-label" for="reservas-filter-fecha">
              Fecha
            </label>
            <input
              id="reservas-filter-fecha"
              v-model="filtros.fecha"
              type="date"
              class="admin-reservas__filter-input"
              @change="aplicarFiltros"
            />
          </div>

          <div class="admin-reservas__filter-field">
            <label class="admin-reservas__filter-label" for="reservas-filter-estado">
              Estado
            </label>
            <select
              id="reservas-filter-estado"
              v-model="filtros.estado"
              class="admin-reservas__filter-input"
              @change="aplicarFiltros"
            >
              <option value="">Todos</option>
              <option
                v-for="estadoItem in estados"
                :key="estadoItem.value"
                :value="estadoItem.value"
              >
                {{ estadoItem.label }}
              </option>
            </select>
          </div>

          <div class="admin-reservas__filter-field">
            <label class="admin-reservas__filter-label" for="reservas-filter-empleado">
              Empleado
            </label>
            <select
              id="reservas-filter-empleado"
              v-model="filtros.empleado_id"
              class="admin-reservas__filter-input"
              @change="aplicarFiltros"
            >
              <option value="">Todos</option>
              <option
                v-for="empleado in empleados"
                :key="empleado.id"
                :value="String(empleado.id)"
              >
                {{ nombreEmpleadoOpcion(empleado) }}
              </option>
            </select>
          </div>

          <div class="admin-reservas__filter-field">
            <label class="admin-reservas__filter-label" for="reservas-filter-servicio">
              Servicio
            </label>
            <select
              id="reservas-filter-servicio"
              v-model="filtros.servicio_id"
              class="admin-reservas__filter-input"
              @change="aplicarFiltros"
            >
              <option value="">Todos</option>
              <option
                v-for="servicio in servicios"
                :key="servicio.id"
                :value="String(servicio.id)"
              >
                {{ servicio.nombre }}
              </option>
            </select>
          </div>

          <div class="admin-reservas__filter-field admin-reservas__filter-field--search">
            <label class="admin-reservas__filter-label" for="reservas-filter-busqueda">
              Buscar cliente o servicio
            </label>
            <input
              id="reservas-filter-busqueda"
              v-model="filtros.busqueda"
              type="search"
              placeholder="Buscar cliente o servicio..."
              class="admin-reservas__filter-input"
              @input="aplicarFiltros"
            />
          </div>

          <div class="admin-reservas__filter-actions">
            <button
              type="button"
              class="admin-reservas__button admin-reservas__button--secondary"
              @click="limpiarFiltros"
            >
              Limpiar filtros
            </button>
          </div>
        </div>
      </section>

      <p
        v-if="loading"
        class="admin-reservas__status"
        role="status"
        aria-live="polite"
      >
        Cargando reservas...
      </p>

      <p
        v-if="error"
        class="admin-reservas__message admin-reservas__message--error"
        role="alert"
        aria-live="assertive"
      >
        {{ error }}
      </p>

      <p
        v-if="success"
        class="admin-reservas__message admin-reservas__message--success"
        role="status"
        aria-live="polite"
      >
        {{ success }}
      </p>

      <div
        v-if="!loading && reservas.length"
        class="reservations-table"
      >
        <div class="reservations-table__scroll">
          <table class="reservations-table__table">

            <thead class="reservations-table__head">
              <tr class="reservations-table__head-row">
                <th scope="col">Fecha/Hora</th>
                <th scope="col">Cliente</th>
                <th scope="col">Servicio</th>
                <th scope="col">Empleado</th>
                <th scope="col">Estado</th>
                <th scope="col">Notas</th>
                <th scope="col">Acciones</th>
              </tr>
            </thead>

            <tbody class="reservations-table__body">
              <tr
                v-for="reserva in reservas"
                :key="reserva.id"
                class="reservations-table__row"
              >
                <td class="reservations-table__cell" data-label="Fecha/Hora">
                  {{ formatoFechaHora(reserva.fecha_hora_inicio) }}
                </td>

                <td class="reservations-table__cell" data-label="Cliente">
                  {{ nombreCliente(reserva) }}
                </td>

                <td class="reservations-table__cell" data-label="Servicio">
                  {{ reserva.servicio?.nombre || '-' }}
                </td>

                <td class="reservations-table__cell" data-label="Empleado">
                  {{ nombreEmpleado(reserva) }}
                </td>

                <td class="reservations-table__cell" data-label="Estado">
                  <span
                    class="reservations-table__status"
                    :data-estado="reserva.estado"
                  >
                    {{ reserva.estado }}
                  </span>
                </td>

                <td class="reservations-table__cell" data-label="Notas">
                  {{
                    reserva.notas
                      ? reserva.notas.slice(0, 50) + (reserva.notas.length > 50 ? '...' : '')
                      : '-'
                  }}
                </td>

                <td
                  class="reservations-table__cell reservations-table__cell--actions"
                  data-label="Acciones"
                >
                  <div class="reservations-table__actions">
                    <button
                      type="button"
                      @click="abrirModalEdicion(reserva, $event)"
                      class="reservations-table__button reservations-table__button--secondary"
                      :disabled="saving || deletingReservaId !== null"
                      :aria-label="`Editar reserva de ${nombreCliente(reserva)} para ${reserva.servicio?.nombre || 'servicio'}`"
                    >
                      Editar
                    </button>

                    <div
                      class="reservations-table__status-actions"
                      :aria-label="`Cambiar estado de la reserva de ${nombreCliente(reserva)}`"
                    >
                      <button
                        v-for="estadoItem in estados"
                        :key="estadoItem.value"
                        type="button"
                        @click="cambiarEstadoReserva(reserva, estadoItem.value)"
                        class="reservations-table__button reservations-table__button--ghost"
                        :disabled="saving || deletingReservaId !== null"
                        :aria-label="`Cambiar estado a ${estadoItem.label}`"
                      >
                        {{ estadoItem.label }}
                      </button>
                    </div>

                    <button
                      type="button"
                      @click="eliminarReservaConfirmado(reserva)"
                      class="reservations-table__button reservations-table__button--danger"
                      :disabled="saving || deletingReservaId !== null"
                      :aria-label="`Eliminar reserva de ${nombreCliente(reserva)}`"
                    >
                      {{ deletingReservaId === reserva.id ? 'Eliminando...' : 'Eliminar' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <nav class="reservations-table__pagination" aria-label="Paginación de reservas">
          <!-- Paginación pendiente -->
        </nav>
      </div>

      <p
        v-else-if="!loading"
        class="admin-reservas__empty"
        role="status"
        aria-live="polite"
      >
        No hay reservas que coincidan con los filtros.
      </p>
    </section>

    <div
      v-if="mostrarModalReserva"
      id="reservation-modal"
      class="admin-reservas-modal"
      @click.self="cerrarModal"
    >
      <div
        ref="modalRef"
        class="admin-reservas-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="reservation-modal-title"
        aria-describedby="reservation-modal-description"
      >
        <header class="admin-reservas-modal__header">
          <div class="admin-reservas-modal__heading">
            <h2
              id="reservation-modal-title"
              ref="modalTitleRef"
              class="admin-reservas-modal__title"
              tabindex="-1"
            >
              {{ tituloModal }}
            </h2>

            <p id="reservation-modal-description" class="admin-reservas-modal__description">
              {{
                modoReserva === 'crear'
                  ? 'Crea una reserva consultando antes la disponibilidad.'
                  : 'Edita una reserva existente.'
              }}
            </p>
          </div>

          <button
            type="button"
            class="admin-reservas-modal__close"
            @click="cerrarModal"
            aria-label="Cerrar formulario de reserva"
          >
            ×
          </button>
        </header>

        <form
          v-if="modoReserva === 'crear'"
          class="admin-reservas-form"
          @submit.prevent="guardarReserva"
          :aria-busy="saving ? 'true' : 'false'"
        >
          <div class="admin-reservas-form__field admin-reservas-form__field--full">
            <label class="admin-reservas-form__label" for="crear-cliente-busqueda">
              Cliente
            </label>

            <div class="admin-reservas-form__combobox">
              <input
                id="crear-cliente-busqueda"
                v-model="clienteQuery"
                class="admin-reservas-form__input"
                :class="{ 'admin-reservas-form__input--error': erroresCrear.usuario_id }"
                type="text"
                placeholder="Busca por nombre, apellidos, email o teléfono"
                role="combobox"
                aria-autocomplete="list"
                :aria-expanded="mostrarSugerenciasClientes ? 'true' : 'false'"
                :aria-controls="clienteListboxId"
                :aria-activedescendant="clienteActivoId"
                :aria-invalid="erroresCrear.usuario_id ? 'true' : 'false'"
                :aria-describedby="erroresCrear.usuario_id ? 'crear-cliente-error crear-cliente-help' : 'crear-cliente-help'"
                @input="manejarInputCliente"
                @focus="manejarFocusCliente"
                @blur="manejarBlurCliente"
                @keydown="manejarTecladoCliente"
              />

              <p id="crear-cliente-help" class="admin-reservas-form__help">
                Escribe al menos 2 caracteres y selecciona un cliente de la lista.
              </p>

              <div
                v-if="loadingClientes"
                class="admin-reservas-form__loading-text"
                role="status"
                aria-live="polite"
              >
                Buscando clientes...
              </div>

              <ul
                v-if="mostrarSugerenciasClientes && clientesEncontrados.length"
                :id="clienteListboxId"
                class="admin-reservas-form__suggestions"
                role="listbox"
              >
                <li
                  v-for="(cliente, index) in clientesEncontrados"
                  :id="`crear-cliente-opcion-${cliente.id}`"
                  :key="cliente.id"
                  class="admin-reservas-form__suggestion"
                  :class="{
                    'admin-reservas-form__suggestion--active': index === clienteActivoIndex,
                  }"
                  role="option"
                  :aria-selected="index === clienteActivoIndex ? 'true' : 'false'"
                  @mousedown.prevent="seleccionarCliente(cliente)"
                >
                  <span class="admin-reservas-form__suggestion-name">
                    {{ nombreClienteOpcion(cliente) }}
                  </span>
                  <span class="admin-reservas-form__suggestion-meta">
                    {{ descripcionClienteOpcion(cliente) }}
                  </span>
                </li>
              </ul>
            </div>

            <p
              v-if="clienteSeleccionado"
              class="admin-reservas-form__selected-client"
            >
              Cliente seleccionado: {{ nombreClienteOpcion(clienteSeleccionado) }}
              <span v-if="descripcionClienteOpcion(clienteSeleccionado)">
                — {{ descripcionClienteOpcion(clienteSeleccionado) }}
              </span>
            </p>

            <p
              v-if="erroresCrear.usuario_id"
              id="crear-cliente-error"
              class="admin-reservas-form__error"
            >
              {{ erroresCrear.usuario_id }}
            </p>
          </div>

          <div class="admin-reservas-form__field admin-reservas-form__field--full">
            <label class="admin-reservas-form__label" for="crear-servicio">
              Servicio
            </label>
            <select
              id="crear-servicio"
              v-model="formularioCrear.servicio_id"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': erroresCrear.servicio_id }"
              :aria-invalid="erroresCrear.servicio_id ? 'true' : 'false'"
              :aria-describedby="erroresCrear.servicio_id ? 'crear-servicio-error' : undefined"
            >
              <option value="">Selecciona un servicio</option>
              <option
                v-for="servicio in servicios"
                :key="servicio.id"
                :value="servicio.id"
              >
                {{ servicio.nombre }} - {{ servicio.duracion_minutos }} min - {{ servicio.precio }} €
              </option>
            </select>
            <p
              v-if="erroresCrear.servicio_id"
              id="crear-servicio-error"
              class="admin-reservas-form__error"
            >
              {{ erroresCrear.servicio_id }}
            </p>
          </div>

          <article
            v-if="servicioSeleccionadoCrear"
            class="admin-reservas-form__summary admin-reservas-form__field--full"
            aria-label="Resumen del servicio seleccionado"
          >
            <h3 class="admin-reservas-form__summary-title">
              {{ servicioSeleccionadoCrear.nombre }}
            </h3>
            <p class="admin-reservas-form__summary-text">
              {{ servicioSeleccionadoCrear.descripcion }}
            </p>
            <dl class="admin-reservas-form__summary-meta">
              <div>
                <dt>Duración</dt>
                <dd>{{ servicioSeleccionadoCrear.duracion_minutos }} min</dd>
              </div>
              <div>
                <dt>Precio</dt>
                <dd>{{ servicioSeleccionadoCrear.precio }} €</dd>
              </div>
            </dl>
          </article>

          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="crear-empleado">
              Empleado
            </label>
            <select
              id="crear-empleado"
              v-model="formularioCrear.empleado_id"
              class="admin-reservas-form__input"
              :disabled="!empleados.length || loadingEmpleadosDisponibilidad"
            >
              <option value="">Cualquier profesional disponible</option>
              <option
                v-for="empleado in empleados"
                :key="empleado.id"
                :value="empleado.id"
              >
                {{ nombreEmpleadoOpcion(empleado) }}
              </option>
            </select>
          </div>

          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="crear-fecha">
              Fecha
            </label>
            <input
              id="crear-fecha"
              v-model="formularioCrear.fecha"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': erroresCrear.fecha }"
              :aria-invalid="erroresCrear.fecha ? 'true' : 'false'"
              :aria-describedby="erroresCrear.fecha ? 'crear-fecha-error' : undefined"
              type="date"
            />
            <p
              v-if="erroresCrear.fecha"
              id="crear-fecha-error"
              class="admin-reservas-form__error"
            >
              {{ erroresCrear.fecha }}
            </p>
          </div>

          <div class="admin-reservas-form__actions admin-reservas-form__actions--full">
            <button
              type="button"
              class="admin-reservas-form__button admin-reservas-form__button--secondary"
              @click="consultarDisponibilidadCrear"
              :disabled="loadingDisponibilidad"
            >
              {{ loadingDisponibilidad ? 'Consultando...' : 'Consultar disponibilidad' }}
            </button>
          </div>

          <fieldset
            v-if="disponibilidad.length"
            class="admin-reservas-form__field admin-reservas-form__field--full"
            :aria-describedby="erroresCrear.hora ? 'crear-hora-error' : undefined"
          >
            <legend class="admin-reservas-form__label">Horas disponibles</legend>

            <div class="admin-reservas-form__hours-grid">
              <label
                v-for="hora in disponibilidad"
                :key="hora"
                class="admin-reservas-form__hour-option"
                :class="{ 'admin-reservas-form__hour-option--selected': formularioCrear.hora === hora }"
              >
                <input
                  v-model="formularioCrear.hora"
                  class="admin-reservas-form__hour-input"
                  type="radio"
                  name="hora-disponible-admin"
                  :value="hora"
                />
                <span>{{ hora }}</span>
              </label>
            </div>

            <p
              v-if="erroresCrear.hora"
              id="crear-hora-error"
              class="admin-reservas-form__error"
            >
              {{ erroresCrear.hora }}
            </p>
          </fieldset>

          <div
            v-else-if="disponibilidadConsultada && !loadingDisponibilidad"
            class="admin-reservas-form__empty"
            role="status"
            aria-live="polite"
          >
            No hay horas disponibles para esa fecha.
          </div>

          <div class="admin-reservas-form__field admin-reservas-form__field--full">
            <label class="admin-reservas-form__label" for="crear-notas">
              Notas
            </label>
            <textarea
              id="crear-notas"
              v-model="formularioCrear.notas"
              class="admin-reservas-form__textarea"
              rows="4"
              placeholder="Añade una nota para la reserva"
            ></textarea>
          </div>

          <footer class="admin-reservas-form__actions admin-reservas-form__actions--full">
            <button
              type="button"
              class="admin-reservas-form__button admin-reservas-form__button--secondary"
              @click="cerrarModal"
              :disabled="saving"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="admin-reservas-form__button admin-reservas-form__button--primary"
              :disabled="saving"
            >
              {{ saving ? 'Guardando...' : 'Crear reserva' }}
            </button>
          </footer>
        </form>

        <form
          v-else
          class="admin-reservas-form"
          @submit.prevent="guardarReserva"
          :aria-busy="saving ? 'true' : 'false'"
        >
          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="reserva-usuario">
              Cliente
            </label>
            <input
              id="reserva-usuario"
              v-model="usuarioId"
              v-bind="usuarioIdAttrs"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': formErrors.usuario_id }"
              :aria-invalid="formErrors.usuario_id ? 'true' : 'false'"
              :aria-describedby="formErrors.usuario_id ? 'reserva-usuario-error' : undefined"
              type="number"
              min="1"
              placeholder="ID del cliente"
            />
            <p
              v-if="formErrors.usuario_id"
              id="reserva-usuario-error"
              class="admin-reservas-form__error"
            >
              {{ formErrors.usuario_id }}
            </p>
          </div>

          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="reserva-empleado">
              Empleado
            </label>
            <select
              id="reserva-empleado"
              v-model="empleadoId"
              v-bind="empleadoIdAttrs"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': formErrors.empleado_id }"
              :aria-invalid="formErrors.empleado_id ? 'true' : 'false'"
              :aria-describedby="formErrors.empleado_id ? 'reserva-empleado-error' : undefined"
            >
              <option value="">Sin asignar</option>
              <option
                v-for="empleado in empleados"
                :key="empleado.id"
                :value="empleado.id"
              >
                {{ nombreEmpleadoOpcion(empleado) }}
              </option>
            </select>
            <p
              v-if="formErrors.empleado_id"
              id="reserva-empleado-error"
              class="admin-reservas-form__error"
            >
              {{ formErrors.empleado_id }}
            </p>
          </div>

          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="reserva-servicio">
              Servicio
            </label>
            <select
              id="reserva-servicio"
              v-model="servicioId"
              v-bind="servicioIdAttrs"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': formErrors.servicio_id }"
              :aria-invalid="formErrors.servicio_id ? 'true' : 'false'"
              :aria-describedby="formErrors.servicio_id ? 'reserva-servicio-error' : undefined"
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
              v-if="formErrors.servicio_id"
              id="reserva-servicio-error"
              class="admin-reservas-form__error"
            >
              {{ formErrors.servicio_id }}
            </p>
          </div>

          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="reserva-fecha-hora">
              Fecha y hora
            </label>
            <input
              id="reserva-fecha-hora"
              v-model="fechaHoraInicio"
              v-bind="fechaHoraInicioAttrs"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': formErrors.fecha_hora_inicio }"
              :aria-invalid="formErrors.fecha_hora_inicio ? 'true' : 'false'"
              :aria-describedby="formErrors.fecha_hora_inicio ? 'reserva-fecha-hora-error' : undefined"
              type="datetime-local"
            />
            <p
              v-if="formErrors.fecha_hora_inicio"
              id="reserva-fecha-hora-error"
              class="admin-reservas-form__error"
            >
              {{ formErrors.fecha_hora_inicio }}
            </p>
          </div>

          <div class="admin-reservas-form__field">
            <label class="admin-reservas-form__label" for="reserva-estado">
              Estado
            </label>
            <select
              id="reserva-estado"
              v-model="estado"
              v-bind="estadoAttrs"
              class="admin-reservas-form__input"
              :class="{ 'admin-reservas-form__input--error': formErrors.estado }"
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
              class="admin-reservas-form__error"
            >
              {{ formErrors.estado }}
            </p>
          </div>

          <div class="admin-reservas-form__field admin-reservas-form__field--full">
            <label class="admin-reservas-form__label" for="reserva-notas">
              Notas
            </label>
            <textarea
              id="reserva-notas"
              v-model="notas"
              v-bind="notasAttrs"
              class="admin-reservas-form__textarea"
              :class="{ 'admin-reservas-form__input--error': formErrors.notas }"
              :aria-invalid="formErrors.notas ? 'true' : 'false'"
              :aria-describedby="formErrors.notas ? 'reserva-notas-error' : undefined"
              rows="4"
            ></textarea>
            <p
              v-if="formErrors.notas"
              id="reserva-notas-error"
              class="admin-reservas-form__error"
            >
              {{ formErrors.notas }}
            </p>
          </div>

          <footer class="admin-reservas-form__actions admin-reservas-form__actions--full">
            <button
              type="button"
              class="admin-reservas-form__button admin-reservas-form__button--secondary"
              @click="cerrarModal"
              :disabled="saving"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="admin-reservas-form__button admin-reservas-form__button--primary"
              :disabled="saving"
            >
              {{ saving ? 'Guardando...' : 'Guardar cambios' }}
            </button>
          </footer>
        </form>
      </div>
    </div>
  </main>
</template>