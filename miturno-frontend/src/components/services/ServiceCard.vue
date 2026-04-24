<script setup>
defineProps({
    servicio: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['reserve'])

const onReserve = () => {
    emit('reserve')
}
</script>

<template>
    <article
        class="service-card"
        :aria-labelledby="`service-title-${servicio.id}`"
    >
        <div class="service-card__body">
            <h2
                :id="`service-title-${servicio.id}`"
                class="service-card__title"
            >
                {{ servicio.nombre }}
            </h2>

            <p
                v-if="servicio.descripcion"
                class="service-card__description"
            >
                {{ servicio.descripcion }}
            </p>

            <dl class="service-card__meta">
                <div class="service-card__meta-item">
                    <dt class="service-card__meta-term">Duración</dt>
                    <dd class="service-card__meta-value">
                        {{ servicio.duracion_minutos }} min
                    </dd>
                </div>

                <div class="service-card__meta-item">
                    <dt class="service-card__meta-term">Precio</dt>
                    <dd class="service-card__meta-value">
                        {{ servicio.precio }} €
                    </dd>
                </div>
            </dl>
        </div>

        <button
            type="button"
            class="service-card__button"
            :aria-label="`Reservar el servicio ${servicio.nombre}`"
            @click="onReserve"
        >
            Reservar este servicio
        </button>
    </article>
</template>