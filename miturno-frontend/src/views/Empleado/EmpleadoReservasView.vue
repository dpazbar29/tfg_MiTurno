<script setup>
import { ref, computed, onMounted } from 'vue'
import { getReservasEmpleado, updateReserva } from '../../api/reservas'

const reservas = ref([])
const meta = ref(null)
const loading = ref(false)
const updatingEstadoId = ref(null)

const error = ref(null)
const success = ref(null)

const filtros = ref({
  fecha: '',
  estado: '',
  busqueda: '',
  page: 1,
})

const estados = [
  { value: '', label: 'Todos' },
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'confirmada', label: 'Confirmada' },
  { value: 'cancelada', label: 'Cancelada' },
  { value: 'completada', label: 'Completada' },
  { value: 'ausencia', label: 'Ausencia' },
]

const estadosAccion = [
  { value: 'confirmada', label: 'Confirmar' },
  { value: 'completada', label: 'Completar' },
  { value: 'ausencia', label: 'Ausencia' },
  { value: 'cancelada', label: 'Cancelar' },
]

const hayReservas = computed(() => reservas.value.length > 0)

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

const cargarReservas = async (page = 1) => {
  loading.value = true
  error.value = null

  try {
    const response = await getReservasEmpleado({
      ...filtros.value,
      page,
    })

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

const aplicarFiltros = () => {
  success.value = null
  cargarReservas(1)
}

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

const cambiarPagina = (page) => {
  if (!pagination.value) return
  if (page < 1 || page > pagination.value.last_page) return
  cargarReservas(page)
}

const formatoFechaHora = (valor) => {
  if (!valor) return '-'

  const fecha = new Date(valor)
  if (Number.isNaN(fecha.getTime())) return valor

  return new Intl.DateTimeFormat('es-ES', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(fecha)
}

const formatoFecha = (valor) => {
  if (!valor) return '-'

  const fecha = new Date(valor)
  if (Number.isNaN(fecha.getTime())) return valor

  return new Intl.DateTimeFormat('es-ES', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(fecha)
}

const nombreCliente = (reserva) => {
  const nombre = reserva?.usuario?.nombre || ''
  const apellidos = reserva?.usuario?.apellidos || ''
  return `${nombre} ${apellidos}`.trim() || '-'
}

const emailCliente = (reserva) => {
  return reserva?.usuario?.email || '-'
}

const telefonoCliente = (reserva) => {
  return reserva?.usuario?.telefono || '-'
}

const servicioNombre = (reserva) => {
  return reserva?.servicio?.nombre || '-'
}

const servicioDuracion = (reserva) => {
  return reserva?.servicio?.duracion_minutos
    ? `${reserva.servicio.duracion_minutos} min`
    : '-'
}

const servicioPrecio = (reserva) => {
  return reserva?.servicio?.precio != null
    ? `${reserva.servicio.precio} €`
    : '-'
}

const resumenNotas = (texto) => {
  if (!texto) return '-'
  return texto.length > 80 ? `${texto.slice(0, 80)}...` : texto
}

const puedeCambiarA = (reserva, nuevoEstado) => {
  if (!reserva?.estado) return true
  return reserva.estado !== nuevoEstado
}

const cambiarEstadoReserva = async (reserva, nuevoEstado) => {
  if (!puedeCambiarA(reserva, nuevoEstado)) return

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
    await cargarReservas(filtros.value.page || 1)
  } catch (err) {
    console.error(err.response?.data || err)
    error.value =
      err.response?.data?.message || 'No se pudo actualizar el estado de la cita.'
  } finally {
    updatingEstadoId.value = null
  }
}

onMounted(() => {
  cargarReservas()
})
</script>

<template>
  <main class="empleado-reservas">
    <section class="empleado-reservas__container" aria-labelledby="empleado-reservas-title">
      <header class="empleado-reservas__header">
        <div class="empleado-reservas__heading">
          <h1 id="empleado-reservas-title" class="empleado-reservas__title">
            Mis citas
          </h1>
          <p class="empleado-reservas__intro">
            Consulta las reservas asignadas a tu perfil y revisa todos los datos del cliente y del servicio.
          </p>
        </div>
      </header>

      <section class="empleado-reservas__filters" aria-labelledby="empleado-reservas-filters-title">
        <h2 id="empleado-reservas-filters-title" class="sr-only">Filtros de citas</h2>

        <div class="empleado-reservas__filter-grid">
          <div class="empleado-reservas__filter-field">
            <label class="empleado-reservas__filter-label" for="empleado-reservas-fecha">
              Fecha
            </label>
            <input
              id="empleado-reservas-fecha"
              v-model="filtros.fecha"
              type="date"
              class="empleado-reservas__filter-input"
              @change="aplicarFiltros"
            />
          </div>

          <div class="empleado-reservas__filter-field">
            <label class="empleado-reservas__filter-label" for="empleado-reservas-estado">
              Estado
            </label>
            <select
              id="empleado-reservas-estado"
              v-model="filtros.estado"
              class="empleado-reservas__filter-input"
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

          <div class="empleado-reservas__filter-field empleado-reservas__filter-field--wide">
            <label class="empleado-reservas__filter-label" for="empleado-reservas-busqueda">
              Buscar
            </label>
            <input
              id="empleado-reservas-busqueda"
              v-model="filtros.busqueda"
              type="search"
              class="empleado-reservas__filter-input"
              placeholder="Cliente, email, teléfono o servicio"
              @input="aplicarFiltros"
            />
          </div>
        </div>

        <div class="empleado-reservas__filter-actions">
          <button
            type="button"
            class="empleado-reservas__button empleado-reservas__button--secondary"
            @click="limpiarFiltros"
          >
            Limpiar filtros
          </button>
        </div>
      </section>

      <p
        v-if="loading"
        class="empleado-reservas__status"
        role="status"
        aria-live="polite"
      >
        Cargando citas...
      </p>

      <p
        v-if="error"
        class="empleado-reservas__message empleado-reservas__message--error"
        role="alert"
        aria-live="assertive"
      >
        {{ error }}
      </p>

      <p
        v-if="success"
        class="empleado-reservas__message empleado-reservas__message--success"
        role="status"
        aria-live="polite"
      >
        {{ success }}
      </p>

      <div
        v-if="!loading && hayReservas"
        class="empleado-reservas__table-container"
      >
        <div class="empleado-reservas__table-scroll">
          <table class="empleado-reservas__table">
            <thead class="empleado-reservas__thead">
              <tr class="empleado-reservas__head-row">
                <th scope="col">Fecha y hora</th>
                <th scope="col">Cliente</th>
                <th scope="col">Contacto</th>
                <th scope="col">Servicio</th>
                <th scope="col">Estado</th>
                <th scope="col">Notas</th>
                <th scope="col">Acciones</th>
              </tr>
            </thead>

            <tbody class="empleado-reservas__body">
              <tr
                v-for="reserva in reservas"
                :key="reserva.id"
                class="empleado-reservas__row"
              >
                <td class="empleado-reservas__cell empleado-reservas__cell--datetime" data-label="Fecha y hora">
                  <strong class="empleado-reservas__date-main">
                    {{ formatoFechaHora(reserva.fecha_hora_inicio) }}
                  </strong>
                  <span class="empleado-reservas__date-secondary">
                    {{ formatoFecha(reserva.fecha_hora_inicio) }}
                  </span>
                </td>

                <td class="empleado-reservas__cell" data-label="Cliente">
                  <div class="empleado-reservas__person">
                    <strong>{{ nombreCliente(reserva) }}</strong>
                  </div>
                </td>

                <td class="empleado-reservas__cell" data-label="Contacto">
                  <div class="empleado-reservas__contact">
                    <span>{{ emailCliente(reserva) }}</span>
                    <span>{{ telefonoCliente(reserva) }}</span>
                  </div>
                </td>

                <td class="empleado-reservas__cell" data-label="Servicio">
                  <div class="empleado-reservas__service">
                    <strong>{{ servicioNombre(reserva) }}</strong>
                    <span>{{ servicioDuracion(reserva) }}</span>
                    <span>{{ servicioPrecio(reserva) }}</span>
                  </div>
                </td>

                <td class="empleado-reservas__cell" data-label="Estado">
                  <span
                    class="empleado-reservas__estado"
                    :data-estado="reserva.estado"
                  >
                    {{ reserva.estado }}
                  </span>
                </td>

                <td class="empleado-reservas__cell" data-label="Notas">
                  {{ resumenNotas(reserva.notas) }}
                </td>

                <td class="empleado-reservas__cell empleado-reservas__cell--actions" data-label="Acciones">
                  <div class="empleado-reservas__actions">
                    <button
                      v-for="estadoAccion in estadosAccion"
                      :key="estadoAccion.value"
                      type="button"
                      class="empleado-reservas__action-btn"
                      :class="{
                        'empleado-reservas__action-btn--active':
                          reserva.estado === estadoAccion.value,
                      }"
                      :disabled="
                        updatingEstadoId === reserva.id ||
                        !puedeCambiarA(reserva, estadoAccion.value)
                      "
                      :aria-label="`${estadoAccion.label} cita de ${nombreCliente(reserva)}`"
                      @click="cambiarEstadoReserva(reserva, estadoAccion.value)"
                    >
                      {{
                        updatingEstadoId === reserva.id
                          ? 'Actualizando...'
                          : estadoAccion.label
                      }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <footer
          v-if="pagination && pagination.last_page > 1"
          class="empleado-reservas__pagination"
          aria-label="Paginación de citas"
        >
          <button
            type="button"
            class="empleado-reservas__pagination-btn"
            :disabled="pagination.current_page <= 1 || loading"
            @click="cambiarPagina(pagination.current_page - 1)"
          >
            Anterior
          </button>

          <span class="empleado-reservas__pagination-info">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
            <template v-if="pagination.total">
              · Mostrando {{ pagination.from }}-{{ pagination.to }} de {{ pagination.total }}
            </template>
          </span>

          <button
            type="button"
            class="empleado-reservas__pagination-btn"
            :disabled="pagination.current_page >= pagination.last_page || loading"
            @click="cambiarPagina(pagination.current_page + 1)"
          >
            Siguiente
          </button>
        </footer>
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