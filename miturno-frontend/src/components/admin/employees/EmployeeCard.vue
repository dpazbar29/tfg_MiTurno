<script setup>
defineProps({
    empleado: {
        type: Object,
        required: true,
    },
    deletingEmployeeId: {
        type: [Number, String, null],
        default: null,
    },
})

defineEmits(['edit-services', 'edit-profile', 'delete'])
</script>

<template>
    <article
        class="employee-card"
        :aria-labelledby="`employee-card-title-${empleado.id}`"
    >
        <header class="employee-card__header">
            <h2
                :id="`employee-card-title-${empleado.id}`"
                class="employee-card__title"
            >
                {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
            </h2>
        </header>

        <dl class="employee-card__meta">
            <div class="employee-card__meta-item">
                <dt>Correo</dt>
                <dd>{{ empleado.usuario?.email || 'Sin correo' }}</dd>
            </div>

            <div class="employee-card__meta-item">
                <dt>Teléfono</dt>
                <dd>{{ empleado.usuario?.telefono || 'Sin teléfono' }}</dd>
            </div>

            <div class="employee-card__meta-item">
                <dt>Contratación</dt>
                <dd>
                    <time :datetime="empleado.fecha_contratacion">
                        {{ formatearFecha(empleado.fecha_contratacion) }}
                    </time>
                </dd>
            </div>

            <div class="employee-card__meta-item">
                <dt>Activo</dt>
                <dd>{{ empleado.activo ? 'Sí' : 'No' }}</dd>
            </div>

            <div class="employee-card__meta-item">
                <dt>Servicios</dt>
                <dd>{{ empleado.servicios?.length || 0 }}</dd>
            </div>
        </dl>

        <div
            v-if="empleado.servicios?.length"
            class="employee-card__services"
        >
            <h3 class="employee-card__services-title">Servicios asignados</h3>
            <ul class="employee-card__services-list" role="list">
                <li
                    v-for="servicio in empleado.servicios"
                    :key="servicio.id"
                    class="employee-card__services-item"
                >
                    {{ servicio.nombre }}
                </li>
            </ul>
        </div>

        <p v-else class="employee-card__empty">
            No tiene servicios asignados.
        </p>

        <div class="employee-card__actions">
            <button
                type="button"
                class="employee-card__button employee-card__button--secondary"
                @click="$emit('edit-profile', empleado, $event)"
                :disabled="deletingEmployeeId !== null"
            >
                Editar perfil
            </button>

            <button
                type="button"
                class="employee-card__button employee-card__button--danger"
                @click="$emit('delete', empleado)"
                :disabled="deletingEmployeeId !== null"
            >
                {{ deletingEmployeeId === empleado.id ? 'Eliminando...' : 'Eliminar' }}
            </button>

            <button
                type="button"
                class="employee-card__button employee-card__button--primary"
                @click="$emit('edit-services', empleado, $event)"
                :disabled="deletingEmployeeId !== null"
            >
                Gestionar servicios
            </button>
        </div>
    </article>
</template>

<script>
function formatearFecha(fecha) {
    return new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'medium',
    }).format(new Date(fecha))
}
</script>