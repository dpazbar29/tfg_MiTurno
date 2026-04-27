<script setup>
defineProps({
    dia: {
        type: Object,
        required: true,
    },
    horarios: {
        type: Array,
        default: () => [],
    },
    formatearHora: {
        type: Function,
        required: true,
    },
    etiquetaTipo: {
        type: Function,
        required: true,
    },
})
</script>

<template>
    <article class="empleado-horario-day-card">
        <header class="empleado-horario-day-card__header">
            <h2 class="empleado-horario-day-card__title">
                {{ dia.label }}
            </h2>
        </header>

        <div
            v-if="horarios && horarios.length"
            class="empleado-horario-day-card__slots"
        >
            <div
                v-for="horario in horarios"
                :key="horario.id"
                class="empleado-horario-day-card__slot"
            >
                <div class="empleado-horario-day-card__slot-main">
                    <span class="empleado-horario-day-card__slot-time">
                        {{ formatearHora(horario.hora_inicio) }} - {{ formatearHora(horario.hora_fin) }}
                    </span>
                </div>

                <div class="empleado-horario-day-card__slot-meta">
                    <span class="empleado-horario-day-card__slot-type">
                        {{ etiquetaTipo(horario.tipo) }}
                    </span>

                    <span
                        class="empleado-horario-day-card__slot-status"
                        :class="{ 'empleado-horario-day-card__slot-status--inactive': !horario.activo }"
                        :data-status="horario.activo ? 'activo' : 'inactivo'"
                    >
                        {{ horario.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        </div>

        <p v-else class="empleado-horario-day-card__empty">
            Sin horario asignado.
        </p>
    </article>
</template>