<script setup>
const props = defineProps({
    diasSemana: {
        type: Array,
        required: true,
    },
    empleadoSeleccionado: {
        type: Object,
        default: null,
    },
    deletingHorarioId: {
        type: [String, Number, null],
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'edit-schedule',
    'delete-schedule',
    'create-schedule',
])

const getTipoLabel = (tipo) => {
    if (tipo === 'normal') return 'Normal'
    if (tipo === 'festivo') return 'Festivo'
    return 'Cierre'
}

const crearFranja = (event, dia) => {
    emit('create-schedule', event, dia)
}

const editarFranja = (horario, event) => {
    emit('edit-schedule', horario, event)
}

const eliminarFranja = (horario) => {
    emit('delete-schedule', horario)
}
</script>

<template>
    <div
        class="admin-schedules__week"
        role="list"
        :aria-label="`Horarios de ${empleadoSeleccionado?.usuario?.nombre ?? 'empleado seleccionado'}`"
    >
        <article
            v-for="dia in diasSemana"
            :key="dia.value"
            class="schedule-day-card"
            role="listitem"
            :aria-labelledby="`schedule-day-title-${dia.value}`"
        >
            <header class="schedule-day-card__header">
                <h2
                    :id="`schedule-day-title-${dia.value}`"
                    class="schedule-day-card__title"
                >
                    {{ dia.label }}
                </h2>

                <button
                    type="button"
                    class="schedule-day-card__add-button"
                    :disabled="saving"
                    :aria-label="`Añadir franja para ${dia.label}`"
                    @click="crearFranja($event, dia.value)"
                >
                    Añadir franja
                </button>
            </header>

            <ul
                v-if="dia.horarios.length"
                class="schedule-day-card__list"
                role="list"
            >
                <li
                    v-for="horario in dia.horarios"
                    :key="horario.id"
                    class="schedule-day-card__item"
                >
                    <div class="schedule-day-card__info">
                        <p class="schedule-day-card__time">
                            {{ horario.hora_inicio.slice(0, 5) }} - {{ horario.hora_fin.slice(0, 5) }}
                        </p>

                        <p class="schedule-day-card__meta">
                            <span class="schedule-day-card__type">
                                {{ getTipoLabel(horario.tipo) }}
                            </span>

                            <span class="schedule-day-card__separator" aria-hidden="true">·</span>

                            <span class="schedule-day-card__state">
                                {{ horario.activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>

                    <div class="schedule-day-card__actions">
                        <button
                            type="button"
                            class="schedule-day-card__button schedule-day-card__button--secondary"
                            :disabled="saving || deletingHorarioId !== null"
                            :aria-label="`Editar franja de ${horario.hora_inicio.slice(0, 5)} a ${horario.hora_fin.slice(0, 5)} del ${dia.label}`"
                            @click="editarFranja(horario, $event)"
                        >
                            Editar
                        </button>

                        <button
                            type="button"
                            class="schedule-day-card__button schedule-day-card__button--danger"
                            :disabled="saving || deletingHorarioId !== null"
                            :aria-label="`Eliminar franja de ${horario.hora_inicio.slice(0, 5)} a ${horario.hora_fin.slice(0, 5)} del ${dia.label}`"
                            @click="eliminarFranja(horario)"
                        >
                            {{ deletingHorarioId === horario.id ? 'Eliminando...' : 'Eliminar' }}
                        </button>
                    </div>
                </li>
            </ul>

            <p v-else class="schedule-day-card__empty">
                No hay franjas configuradas.
            </p>
        </article>
    </div>
</template>