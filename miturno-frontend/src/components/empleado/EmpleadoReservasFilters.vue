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
})

const emit = defineEmits([
    'apply-filters',
    'reset-filters',
])
</script>

<template>
    <section
        class="empleado-reservas-filters"
        aria-labelledby="empleado-reservas-filters-title"
    >
        <h2 id="empleado-reservas-filters-title" class="sr-only">
        Filtros de citas
        </h2>

        <div class="empleado-reservas-filters__grid">
            <div class="empleado-reservas-filters__field">
                <label
                    class="empleado-reservas-filters__label"
                    for="empleado-reservas-fecha"
                >
                    Fecha
                </label>

                <input
                    id="empleado-reservas-fecha"
                    v-model="filtros.fecha"
                    type="date"
                    class="empleado-reservas-filters__input"
                    @change="$emit('apply-filters')"
                />
            </div>

            <div class="empleado-reservas-filters__field">
                <label
                    class="empleado-reservas-filters__label"
                    for="empleado-reservas-estado"
                >
                    Estado
                </label>

                <select
                    id="empleado-reservas-estado"
                    v-model="filtros.estado"
                    class="empleado-reservas-filters__input"
                    @change="$emit('apply-filters')"
                >
                    <option
                        v-for="estadoItem in estados"
                        :key="estadoItem.value"
                        :value="estadoItem.value"
                    >
                        {{ estadoItem.label }}
                    </option>
                </select>
            </div>

            <div class="empleado-reservas-filters__field empleado-reservas-filters__field--wide">
                <label
                    class="empleado-reservas-filters__label"
                    for="empleado-reservas-busqueda"
                >
                    Buscar
                </label>

                <input
                    id="empleado-reservas-busqueda"
                    v-model="filtros.busqueda"
                    type="search"
                    class="empleado-reservas-filters__input"
                    placeholder="Cliente, email, teléfono o servicio"
                    @input="$emit('apply-filters')"
                />
            </div>
        </div>

        <div class="empleado-reservas-filters__actions">
            <button
                type="button"
                class="empleado-reservas-filters__button empleado-reservas-filters__button--secondary"
                @click="$emit('reset-filters')"
            >
                Limpiar filtros
            </button>
        </div>
    </section>
</template>