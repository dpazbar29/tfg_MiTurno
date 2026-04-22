<script setup>
import { computed, onMounted, ref } from 'vue'
import { getReservas, cancelarReserva } from '../api/reservas'

const reservas = ref([])
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const cancelandoId = ref(null)

const cargarReservas = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await getReservas()
        reservas.value = response?.data ?? response ?? []
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = 'No se pudieron cargar tus reservas.'
    } finally {
        loading.value = false
    }
}

const cancelarReservaLocal = async (reservaId, reservaNombre) => {
    if (!confirm(`¿Seguro que quieres cancelar "${reservaNombre}"?`)) return

    cancelandoId.value = reservaId
    error.value = null
    success.value = null

    try {
        await cancelarReserva(reservaId)
        success.value = 'La reserva se ha cancelado correctamente.'
        await cargarReservas()
    } catch (err) {
        console.error(err.response?.data || err)
        error.value = err.response?.data?.message || 'Error al cancelar la reserva.'
    } finally {
        cancelandoId.value = null
    }
}

const ahora = computed(() => new Date())

const reservasFuturas = computed(() => {
    return reservas.value.filter((reserva) => {
        return new Date(reserva.fecha_hora_inicio) >= ahora.value
    })
})

const reservasPasadas = computed(() => {
    return reservas.value.filter((reserva) => {
        return new Date(reserva.fecha_hora_inicio) < ahora.value
    })
})

const formatearFecha = (fecha) => {
    return new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(fecha))
}

const nombreEmpleado = (reserva) => {
    const usuario = reserva.empleado?.usuario
    if (!usuario) return 'Sin profesional asignado'
    return `${usuario.nombre} ${usuario.apellidos}`
}

const estadoReserva = (estado) => {
    if (!estado) return 'Pendiente'
    return estado
}

const puedeCancelar = (reserva) => {
    return reserva.estado === 'pendiente' && new Date(reserva.fecha_hora_inicio) > new Date()
}

onMounted(cargarReservas)
</script>

<template>
    <main class="reservations-page">
        <section class="reservations-page__container" aria-labelledby="reservations-title">
            <header class="reservations-page__header">
                <h1 id="reservations-title" class="reservations-page__title">Mis reservas</h1>
                <p class="reservations-page__intro">
                    Consulta tus próximas citas y el historial de reservas.
                </p>    
            </header>

            <p
                v-if="loading"
                class="reservations-page__status"
                role="status"
                aria-live="polite"
            >
                Cargando reservas...
            </p>

            <p
                v-if="error"
                class="reservations-page__message reservations-page__message--error"
                role="alert"
                aria-live="assertive"
            >
                {{ error }}
            </p>

            <p
                v-if="success"
                class="reservations-page__message reservations-page__message--success"
                role="status"
                aria-live="polite"
            >
                {{ success }}
            </p>

            <template v-if="!loading && !error">
                <section class="reservations-page__section" aria-labelledby="upcoming-title">
                    <h2 id="upcoming-title" class="reservations-page__section-title">
                        Próximas reservas
                    </h2>

                    <div v-if="reservasFuturas.length" class="reservations-page__list">
                        <article
                            v-for="reserva in reservasFuturas"
                            :key="reserva.id"
                            class="reservation-card"
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
                                    v-if="puedeCancelar(reserva)"
                                    class="reservation-card__cancel"
                                    @click="cancelarReservaLocal(reserva.id, reserva.servicio?.nombre || 'esta reserva')"
                                    :aria-label="`Cancelar reserva ${reserva.servicio?.nombre || ''}`"
                                    :disabled="cancelandoId === reserva.id"
                                >
                                    {{ cancelandoId === reserva.id ? 'Cancelando...' : 'Cancelar' }}
                                </button>
                            </div>
                        </article>
                    </div>

                    <p v-else class="reservations-page__empty" role="status" aria-live="polite">
                        No tienes próximas reservas.
                    </p>
                </section>

                <section class="reservations-page__section" aria-labelledby="history-title">
                    <h2 id="history-title" class="reservations-page__section-title">
                        Historial
                    </h2>

                    <div v-if="reservasPasadas.length" class="reservations-page__list">
                        <article
                            v-for="reserva in reservasPasadas"
                            :key="reserva.id"
                            class="reservation-card reservation-card--past"
                            :aria-labelledby="`reservation-history-title-${reserva.id}`"
                        >
                            <h3
                                :id="`reservation-history-title-${reserva.id}`"
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
                        </article>
                    </div>

                    <p v-else class="reservations-page__empty" role="status" aria-live="polite">
                        Aún no tienes reservas anteriores.
                    </p>
                </section>
            </template>
        </section>
    </main>
</template>