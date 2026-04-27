<script setup>
defineProps({
    saving: {
        type: Boolean,
        default: false,
    },
    modalAbierto: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['create-reservation'])

const abrirCrearReserva = (event) => {
    emit('create-reservation', event)
}
</script>

<template>
    <header class="reservations-toolbar">
        <div class="reservations-toolbar__heading">
            <h1 id="admin-reservas-title" class="reservations-toolbar__title">
                Gestión de reservas
            </h1>

            <p class="reservations-toolbar__intro">
                Administra todas las reservas del sistema, crea nuevas manualmente y cambia estados.
            </p>
        </div>

        <div class="reservations-toolbar__actions">
            <button
                type="button"
                class="reservations-toolbar__button reservations-toolbar__button--primary"
                :disabled="saving"
                aria-controls="reservation-modal"
                :aria-expanded="modalAbierto ? 'true' : 'false'"
                @click="abrirCrearReserva"
            >
                Nueva reserva
            </button>
        </div>
    </header>
</template>