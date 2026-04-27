<script setup>
const props = defineProps({
    empleados: {
        type: Array,
        required: true,
    },
    empleadoSeleccionadoId: {
        type: [String, Number],
        default: '',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    modalAbierto: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'update:empleadoSeleccionadoId',
    'create-schedule',
])

const actualizarEmpleado = (event) => {
    emit('update:empleadoSeleccionadoId', event.target.value)
}

const crearFranja = (event) => {
    emit('create-schedule', event)
}
</script>

<template>
    <div class="admin-schedules__toolbar">
        <div class="admin-schedules__filter-group">
            <label class="admin-schedules__filter" for="schedule-employee-filter">
                <span class="admin-schedules__filter-label">Empleado</span>
            </label>

            <select
                id="schedule-employee-filter"
                class="admin-schedules__select"
                :value="String(empleadoSeleccionadoId || '')"
                :disabled="loading || saving"
                aria-describedby="schedule-filter-help"
                @change="actualizarEmpleado"
            >
                <option value="">Selecciona un empleado</option>
                <option
                    v-for="empleado in empleados"
                    :key="empleado.id"
                    :value="String(empleado.id)"
                >
                    {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                </option>
            </select>

            <p id="schedule-filter-help" class="admin-schedules__filter-help">
                Selecciona un empleado para ver y gestionar sus franjas horarias.
            </p>
        </div>

        <button
            type="button"
            class="admin-schedules__button admin-schedules__button--primary"
            :disabled="!empleadoSeleccionadoId || saving"
            :aria-disabled="!empleadoSeleccionadoId || saving ? 'true' : 'false'"
            aria-controls="schedule-modal"
            :aria-expanded="modalAbierto ? 'true' : 'false'"
            @click="crearFranja"
        >
            Nueva franja
        </button>
    </div>
</template>