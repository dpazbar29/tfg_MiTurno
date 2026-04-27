<script setup>
import EmpleadoHorarioDayCard from './EmpleadoHorarioDayCard.vue'

defineProps({
    dias: {
        type: Array,
        required: true,
    },
    horariosPorDia: {
        type: Object,
        required: true,
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
    <section
        class="empleado-horario-week-grid"
        aria-label="Horario semanal del empleado"
    >
        <EmpleadoHorarioDayCard
            v-for="dia in dias"
            :key="dia.key"
            :dia="dia"
            :horarios="horariosPorDia[dia.key] || []"
            :formatear-hora="formatearHora"
            :etiqueta-tipo="etiquetaTipo"
        />
    </section>
</template>