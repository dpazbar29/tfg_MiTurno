<script setup>
const props = defineProps({
    reserva: {
        type: Object,
        required: true,
    },
    cancelandoId: {
        type: [Number, String, null],
        default: null,
    },
    past: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['cancel'])

const nombreEmpleado = (reserva) => {
    const usuario = reserva.empleado?.usuario
    if (!usuario) return 'Sin profesional asignado'
    return `${usuario.nombre} ${usuario.apellidos}`
}

const estadoReserva = (estado) => {
    if (!estado) return 'Pendiente'
    return estado
}

const formatearFecha = (fecha) => {
    return new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(fecha))
}

const puedeCancelar = (reserva) => {
    return reserva.estado === 'pendiente' && new Date(reserva.fecha_hora_inicio) > new Date()
}

const onCancel = () => {
    emit('cancel', props.reserva.id, props.reserva.servicio?.nombre || 'esta reserva')
}
</script>

<template>
    <article
        class="reservation-card"
        :class="{ 'reservation-card--past': past }"
        :aria-labelledby="`reservation-title-${reserva.id}`"
    >
        <h3
            :id="`reservation-title-${reserva.id}`"
            class="reservation-card__title"
        >
            {{ reserva.servicio?.nombre || 'Servicio' }}
        </h3>

        <dl class="reservation-card__meta">
            <div class="reservation-card__meta-item">
                <dt>Fecha</dt>
                <dd>
                    <time :datetime="reserva.fecha_hora_inicio">
                        {{ formatearFecha(reserva.fecha_hora_inicio) }}
                    </time>
                </dd>
            </div>

            <div class="reservation-card__meta-item">
                <dt>Profesional</dt>
                <dd>{{ nombreEmpleado(reserva) }}</dd>
            </div>

            <div class="reservation-card__meta-item">
                <dt>Estado</dt>
                <dd>
                    <span class="reservation-card__status">
                        {{ estadoReserva(reserva.estado) }}
                    </span>
                </dd>
            </div>
        </dl>

        <p v-if="reserva.notas" class="reservation-card__notes">
            {{ reserva.notas }}
        </p>

        <div class="reservation-card__actions">
            <button
                v-if="!past && puedeCancelar(reserva)"
                class="reservation-card__cancel"
                :aria-label="`Cancelar reserva ${reserva.servicio?.nombre || ''}`"
                :disabled="cancelandoId === reserva.id"
                @click="onCancel"
            >
                {{ cancelandoId === reserva.id ? 'Cancelando...' : 'Cancelar' }}
            </button>
        </div>
    </article>
</template>