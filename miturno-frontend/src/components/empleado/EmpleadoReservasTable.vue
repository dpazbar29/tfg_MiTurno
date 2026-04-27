<script setup>
defineProps({
    reservas: {
        type: Array,
        required: true,
    },
    estadosAccion: {
        type: Array,
        required: true,
    },
    updatingEstadoId: {
        type: [String, Number, null],
        default: null,
    },
})

const emit = defineEmits(['change-status'])

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

const cambiarEstadoReserva = (reserva, nuevoEstado) => {
    if (!puedeCambiarA(reserva, nuevoEstado)) return
    emit('change-status', reserva, nuevoEstado)
}
</script>

<template>
    <div class="empleado-reservas-table">
        <div class="empleado-reservas-table__scroll">
            <table class="empleado-reservas-table__table">
                <thead class="empleado-reservas-table__head">
                    <tr class="empleado-reservas-table__head-row">
                        <th scope="col">Fecha y hora</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Notas</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody class="empleado-reservas-table__body">
                    <tr
                        v-for="reserva in reservas"
                        :key="reserva.id"
                        class="empleado-reservas-table__row"
                    >
                        <td
                            class="empleado-reservas-table__cell empleado-reservas-table__cell--datetime"
                            data-label="Fecha y hora"
                        >
                            <strong class="empleado-reservas-table__date-main">
                                {{ formatoFechaHora(reserva.fecha_hora_inicio || reserva.fechahorainicio) }}
                            </strong>

                            <span class="empleado-reservas-table__date-secondary">
                                {{ formatoFecha(reserva.fecha_hora_inicio || reserva.fechahorainicio) }}
                            </span>
                        </td>

                        <td class="empleado-reservas-table__cell" data-label="Cliente">
                            <div class="empleado-reservas-table__person">
                                <strong>{{ nombreCliente(reserva) }}</strong>
                            </div>
                        </td>

                        <td class="empleado-reservas-table__cell" data-label="Contacto">
                            <div class="empleado-reservas-table__contact">
                                <span>{{ emailCliente(reserva) }}</span>
                                <span>{{ telefonoCliente(reserva) }}</span>
                            </div>
                        </td>

                        <td class="empleado-reservas-table__cell" data-label="Servicio">
                            <div class="empleado-reservas-table__service">
                                <strong>{{ servicioNombre(reserva) }}</strong>
                                <span>{{ servicioDuracion(reserva) }}</span>
                                <span>{{ servicioPrecio(reserva) }}</span>
                            </div>
                        </td>

                        <td class="empleado-reservas-table__cell" data-label="Estado">
                            <span
                                class="empleado-reservas-table__status"
                                :data-estado="reserva.estado"
                            >
                                {{ reserva.estado }}
                            </span>
                        </td>

                        <td class="empleado-reservas-table__cell" data-label="Notas">
                            {{ resumenNotas(reserva.notas) }}
                        </td>

                        <td
                            class="empleado-reservas-table__cell empleado-reservas-table__cell--actions"
                            data-label="Acciones"
                        >
                            <div class="empleado-reservas-table__actions">
                                <button
                                    v-for="estadoAccion in estadosAccion"
                                    :key="estadoAccion.value"
                                    type="button"
                                    class="empleado-reservas-table__action-btn"
                                    :class="{
                                        'empleado-reservas-table__action-btn--active':
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
    </div>
</template>