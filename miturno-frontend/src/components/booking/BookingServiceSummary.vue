<script setup>
defineProps({
    servicio: {
        type: Object,
        default: null,
    },
})
</script>

<template>
    <article
        v-if="servicio"
        class="booking-summary"
        :aria-labelledby="`service-summary-title-${servicio.id}`"
    >
        <h2
            :id="`service-summary-title-${servicio.id}`"
            class="booking-summary__title"
        >
            {{ servicio.nombre }}
        </h2>

        <p class="booking-summary__text">
            {{ servicio.descripcion }}
        </p>

        <dl class="booking-summary__meta">
            <div class="booking-summary__item">
                <dt>Duración</dt>
                <dd>{{ servicio.duracion_minutos }} min</dd>
            </div>

            <div class="booking-summary__item">
                <dt>Precio</dt>
                <dd>{{ servicio.precio }} €</dd>
            </div>
        </dl>
    </article>
</template>