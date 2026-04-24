<script setup>
import { computed, onMounted, ref } from 'vue'
import { getReservas, cancelarReserva } from '../api/reservas'

import StatusMessage from '@/components/feedback/StatusMessage.vue'
import ReservationCard from '@/components/reservations/ReservationCard.vue'

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

const reservasFuturas = computed(() => {
    const ahora = new Date()

    return reservas.value.filter((reserva) => {
        return new Date(reserva.fecha_hora_inicio) >= ahora
    })
})

const reservasPasadas = computed(() => {
    const ahora = new Date()

    return reservas.value.filter((reserva) => {
        return new Date(reserva.fecha_hora_inicio) < ahora
    })
})

onMounted(cargarReservas)
</script>

<template>
    <main class="reservations-page">
        <section class="reservations-page__container" aria-labelledby="reservations-title">
            <header class="reservations-page__header">
                <h1 id="reservations-title" class="reservations-page__title">
                    Mis reservas
                </h1>
                <p class="reservations-page__intro">
                    Consulta tus próximas citas y el historial de reservas.
                </p>
            </header>

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

            <template v-if="!loading && !error">
                <section class="reservations-page__section" aria-labelledby="upcoming-title">
                    <h2 id="upcoming-title" class="reservations-page__section-title">
                        Próximas reservas
                    </h2>

                    <div v-if="reservasFuturas.length" class="reservations-page__list">
                        <ReservationCard
                            v-for="reserva in reservasFuturas"
                            :key="reserva.id"
                            :reserva="reserva"
                            :cancelando-id="cancelandoId"
                            @cancel="cancelarReservaLocal"
                        />
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
                        <ReservationCard
                            v-for="reserva in reservasPasadas"
                            :key="reserva.id"
                            :reserva="reserva"
                            past
                        />
                    </div>

                    <p v-else class="reservations-page__empty" role="status" aria-live="polite">
                        Aún no tienes reservas anteriores.
                    </p>
                </section>
            </template>
        </section>
    </main>
</template>