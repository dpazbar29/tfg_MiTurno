<script setup>
const props = defineProps({
    pagination: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['change-page'])

const irAPagina = (page) => {
    if (!props.pagination) return
    if (page < 1 || page > props.pagination.last_page) return
    emit('change-page', page)
}
</script>

<template>
    <footer
        v-if="pagination && pagination.last_page > 1"
        class="empleado-reservas-pagination"
        aria-label="Paginación de citas"
    >
        <button
            type="button"
            class="empleado-reservas-pagination__button"
            :disabled="pagination.current_page <= 1 || loading"
            @click="irAPagina(pagination.current_page - 1)"
        >
            Anterior
        </button>

        <span class="empleado-reservas-pagination__info">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
            <template v-if="pagination.total">
                · Mostrando {{ pagination.from }}-{{ pagination.to }} de {{ pagination.total }}
            </template>
        </span>

        <button
            type="button"
            class="empleado-reservas-pagination__button"
            :disabled="pagination.current_page >= pagination.last_page || loading"
            @click="irAPagina(pagination.current_page + 1)"
        >
            Siguiente
        </button>
    </footer>
</template>