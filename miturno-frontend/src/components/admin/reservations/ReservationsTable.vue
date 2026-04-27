<script setup>
const props = defineProps({
    reservas: {
        type: Array,
        required: true,
    },
    estados: {
        type: Array,
        required: true,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    deletingReservaId: {
        type: [String, Number, null],
        default: null,
    },
})

const emit = defineEmits([
    'edit-reservation',
    'delete-reservation',
    'change-status',
])

const formatoFechaHora = (valor) => {
    if (!valor) return '-'

    const fecha = new Date(valor)

    if (Number.isNaN(fecha.getTime())) return valor

    return new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(fecha)
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

const editarReserva = (reserva, event) => {
    emit('edit-reservation', reserva, event)
}

const eliminarReserva = (reserva) => {
    emit('delete-reservation', reserva)
}

const cambiarEstado = (reserva, nuevoEstado) => {
    emit('change-status', reserva, nuevoEstado)
}
</script>

<template>
    <div class="reservations-table">
        <div class="reservations-table__scroll">
            <table class="reservations-table__table">
                <thead class="reservations-table__head">
                    <tr class="reservations-table__head-row">
                        <th scope="col">Fecha y hora</th>
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
                        <td class="reservations-table__cell" data-label="Fecha y hora">
                            {{ formatoFechaHora(reserva.fechahorainicio) }}
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
                                    ? `${reserva.notas.slice(0, 50)}${reserva.notas.length > 50 ? '...' : ''}`
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
                                    class="reservations-table__button reservations-table__button--secondary"
                                    :disabled="saving || deletingReservaId !== null"
                                    :aria-label="`Editar reserva de ${nombreCliente(reserva)} para ${reserva.servicio?.nombre || 'servicio'}`"
                                    @click="editarReserva(reserva, $event)"
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
                                        class="reservations-table__button reservations-table__button--ghost"
                                        :disabled="saving || deletingReservaId !== null"
                                        :aria-label="`Cambiar estado a ${estadoItem.label}`"
                                        @click="cambiarEstado(reserva, estadoItem.value)"
                                    >
                                        {{ estadoItem.label }}
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    class="reservations-table__button reservations-table__button--danger"
                                    :disabled="saving || deletingReservaId !== null"
                                    :aria-label="`Eliminar reserva de ${nombreCliente(reserva)}`"
                                    @click="eliminarReserva(reserva)"
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
</template>