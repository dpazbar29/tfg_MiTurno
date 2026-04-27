<script setup>
defineProps({
    servicio: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['edit', 'delete'])
</script>

<template>
    <article
        class="admin-service-card"
        :aria-labelledby="`service-card-title-${servicio.id}`"
    >
        <header class="admin-service-card__header">
            <h2
                :id="`service-card-title-${servicio.id}`"
                class="admin-service-card__title"
            >
                {{ servicio.nombre }}
            </h2>
        </header>

        <dl class="admin-service-card__meta">
            <div class="admin-service-card__meta-item">
                <dt>Duración</dt>
                    <dd>{{ servicio.duracion_minutos }} min</dd>
            </div>

            <div class="admin-service-card__meta-item">
                <dt>Precio</dt>
                <dd>{{ servicio.precio }} €</dd>
            </div>

            <div class="admin-service-card__meta-item">
                <dt>Estado</dt>
                <dd>
                    <span
                        class="admin-service-card__status"
                        :class="{
                            'admin-service-card__status--active': servicio.activo,
                            'admin-service-card__status--inactive': !servicio.activo,
                        }"
                    >
                        {{ servicio.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </dd>
            </div>
        </dl>

        <p v-if="servicio.descripcion" class="admin-service-card__description">
            {{ servicio.descripcion }}
        </p>

        <div class="admin-service-card__actions">
            <button
                type="button"
                class="admin-service-card__button admin-service-card__button--secondary"
                :aria-label="`Editar servicio ${servicio.nombre}`"
                @click="$emit('edit', servicio, $event)"
            >
                Editar
            </button>

            <button
                type="button"
                class="admin-service-card__button admin-service-card__button--danger"
                :aria-label="`Eliminar servicio ${servicio.nombre}`"
                @click="$emit('delete', servicio.id, servicio.nombre)"
            >
                Eliminar
            </button>
        </div>
    </article>
</template>