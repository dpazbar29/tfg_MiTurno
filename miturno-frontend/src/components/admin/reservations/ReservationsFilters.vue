<script setup>
defineProps({
    filtros: {
        type: Object,
        required: true,
    },
    estados: {
        type: Array,
        required: true,
    },
    empleados: {
        type: Array,
        required: true,
    },
    servicios: {
        type: Array,
        required: true,
    },
    nombreEmpleadoOpcion: {
        type: Function,
        required: true,
    },
})

const emit = defineEmits(['apply-filters', 'reset-filters'])

const aplicarFiltros = () => {
    emit('apply-filters')
}

const limpiarFiltros = () => {
    emit('reset-filters')
}
</script>

<template>
    <section class="reservations-filters" aria-labelledby="admin-reservas-filters-title">
        <h2 id="admin-reservas-filters-title" class="sr-only">
            Filtros de reservas
        </h2>

        <div class="reservations-filters__grid">
            <div class="reservations-filters__field">
                <label class="reservations-filters__label" for="reservas-filter-fecha">
                    Fecha
                </label>
                <input
                    id="reservas-filter-fecha"
                    v-model="filtros.fecha"
                    type="date"
                    class="reservations-filters__input"
                    @change="aplicarFiltros"
                />
            </div>

            <div class="reservations-filters__field">
                <label class="reservations-filters__label" for="reservas-filter-estado">
                    Estado
                </label>
                <select
                    id="reservas-filter-estado"
                    v-model="filtros.estado"
                    class="reservations-filters__input"
                    @change="aplicarFiltros"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="estadoItem in estados"
                        :key="estadoItem.value"
                        :value="estadoItem.value"
                    >
                        {{ estadoItem.label }}
                    </option>
                </select>
            </div>

            <div class="reservations-filters__field">
                <label class="reservations-filters__label" for="reservas-filter-empleado">
                    Empleado
                </label>
                <select
                id="reservas-filter-empleado"
                v-model="filtros.empleadoid"
                class="reservations-filters__input"
                @change="aplicarFiltros"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="empleado in empleados"
                        :key="empleado.id"
                        :value="String(empleado.id)"
                    >
                        {{ nombreEmpleadoOpcion(empleado) }}
                    </option>
                </select>
            </div>

            <div class="reservations-filters__field">
                <label class="reservations-filters__label" for="reservas-filter-servicio">
                    Servicio
                </label>
                <select
                    id="reservas-filter-servicio"
                    v-model="filtros.servicioid"
                    class="reservations-filters__input"
                    @change="aplicarFiltros"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="servicio in servicios"
                        :key="servicio.id"
                        :value="String(servicio.id)"
                    >
                        {{ servicio.nombre }}
                    </option>
                </select>
            </div>

            <div class="reservations-filters__field reservations-filters__field--search">
                <label class="reservations-filters__label" for="reservas-filter-busqueda">
                    Buscar cliente o servicio
                </label>
                <input
                    id="reservas-filter-busqueda"
                    v-model="filtros.busqueda"
                    type="search"
                    placeholder="Buscar cliente o servicio..."
                    class="reservations-filters__input"
                    @input="aplicarFiltros"
                />
            </div>

            <div class="reservations-filters__actions">
                <button
                    type="button"
                    class="reservations-filters__button reservations-filters__button--secondary"
                    @click="limpiarFiltros"
                >
                    Limpiar filtros
                </button>
            </div>
        </div>
    </section>
</template>