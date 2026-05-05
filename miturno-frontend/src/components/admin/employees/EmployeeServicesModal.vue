<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch, computed } from 'vue'

// Props del componente:
// - visible: controla la apertura/cierre del modal.
// - empleado: empleado al que se le asignan servicios.
// - servicios: catálogo completo de servicios disponibles.
// - serviciosSeleccionados: ids de servicios actualmente asociados.
// - saving: indica si el guardado está en curso.
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    empleado: {
        type: Object,
        default: null,
    },
    servicios: {
        type: Array,
        default: () => [],
    },
    serviciosSeleccionados: {
        type: Array,
        default: () => [],
    },
    saving: {
        type: Boolean,
        default: false,
    },
})

// Eventos emitidos al componente padre.
// - close: cerrar el modal.
// - update:serviciosSeleccionados: actualizar selección reactiva.
// - save: persistir los cambios.
const emit = defineEmits(['close', 'update:serviciosSeleccionados', 'save'])

// Referencias usadas para accesibilidad y control del foco.
const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

// Lista derivada de servicios ya asignados al empleado.
// Se calcula de forma reactiva filtrando el catálogo completo por los ids incluidos en serviciosSeleccionados.
const serviciosAsignados = computed(() => {
    if (!props.empleado) return []
    return props.servicios.filter((s) => props.serviciosSeleccionados.includes(s.id))
})

// Lista derivada de servicios todavía disponibles para asignar.
// Es el complementario de la lista anterior.
const serviciosDisponibles = computed(() => {
    if (!props.empleado) return []
    return props.servicios.filter((s) => !props.serviciosSeleccionados.includes(s.id))
})

// Obtiene todos los elementos enfocables dentro del modal.
// Se utiliza para implementar el "focus trap" y evitar que la navegación con teclado salga del diálogo.
const getFocusableElements = () => {
    if (!modalRef.value) return []
    return modalRef.value.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

// Gestiona el tabulador dentro del modal.
// Si el usuario intenta avanzar más allá del último elemento, vuelve al primero. Si retrocede desde el primero, salta al último.
const manejarTab = (event) => {
    if (!props.visible || event.key !== 'Tab') return

    const focusables = [...getFocusableElements()]
    if (!focusables.length) return

    const first = focusables[0]
    const last = focusables[focusables.length - 1]

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
    }
}

// Gestiona atajos de teclado del modal.
// - Escape cierra el diálogo.
// - Tab mantiene el foco dentro del modal.
const manejarTeclado = (event) => {
    if (!props.visible) return
    if (event.key === 'Escape') emit('close')
    manejarTab(event)
}

// Observa la apertura/cierre del modal.
// Cuando se abre:
// - guarda qué elemento tenía el foco antes,
// - espera al render,
// - y mueve el foco al título.
// Cuando se cierra, restaura el foco al elemento anterior.
watch(
    () => props.visible,
    async (visible) => {
        if (visible) {
            lastTriggerRef.value = document.activeElement
            await nextTick()
            modalTitleRef.value?.focus()
        } else {
            lastTriggerRef.value?.focus?.()
        }
    },
)

// Registro global del listener de teclado al montar el componente y limpieza al destruirlo para evitar fugas de eventos.
onMounted(() => document.addEventListener('keydown', manejarTeclado))
onBeforeUnmount(() => document.removeEventListener('keydown', manejarTeclado))
</script>

<template>
    <div v-if="visible" class="admin-employees-modal" @click.self="$emit('close')">
        <div
            ref="modalRef"
            class="admin-employees-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="employee-services-title"
            aria-describedby="employee-services-description"
        >
            <header class="admin-employees-modal__header">
                <h2
                    id="employee-services-title"
                    ref="modalTitleRef"
                    class="admin-employees-modal__title"
                    tabindex="-1"
                >
                    Servicios de {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                </h2>

                <button
                    type="button"
                    class="admin-employees-modal__close"
                    @click="$emit('close')"
                    aria-label="Cerrar gestión de servicios"
                >
                    ×
                </button>
            </header>

            <p id="employee-services-description" class="admin-employees-modal__description">
                Marca o desmarca los servicios que puede atender este empleado.
            </p>

            <form
                class="admin-employees-form"
                @submit.prevent="$emit('save')"
                :aria-busy="saving ? 'true' : 'false'"
            >
                <fieldset class="admin-employees-form__group">
                    <legend class="admin-employees-form__legend">Servicios asignados</legend>

                    <div v-if="serviciosAsignados.length" class="admin-employees-form__options">
                        <div
                            v-for="servicio in serviciosAsignados"
                            :key="servicio.id"
                            class="admin-employees-form__option"
                        >
                            <input
                                :id="`assigned-service-${servicio.id}`"
                                :checked="serviciosSeleccionados.includes(servicio.id)"
                                @change="$emit('update:serviciosSeleccionados', $event.target.checked
                                ? [...serviciosSeleccionados, servicio.id]
                                : serviciosSeleccionados.filter((id) => id !== servicio.id)
                                )"
                                class="admin-employees-form__checkbox"
                                type="checkbox"
                                :value="servicio.id"
                            />
                            <label
                                class="admin-employees-form__option-label"
                                :for="`assigned-service-${servicio.id}`"
                            >
                                {{ servicio.nombre }}
                            </label>
                        </div>
                    </div>

                    <p v-else class="admin-employees-form__empty">
                        No tiene servicios asignados.
                    </p>
                </fieldset>

                <fieldset class="admin-employees-form__group">
                    <legend class="admin-employees-form__legend">Servicios disponibles</legend>

                    <div v-if="serviciosDisponibles.length" class="admin-employees-form__options">
                        <div
                            v-for="servicio in serviciosDisponibles"
                            :key="servicio.id"
                            class="admin-employees-form__option"
                        >
                            <input
                                :id="`available-service-${servicio.id}`"
                                :checked="serviciosSeleccionados.includes(servicio.id)"
                                @change="$emit('update:serviciosSeleccionados', $event.target.checked
                                ? [...serviciosSeleccionados, servicio.id]
                                : serviciosSeleccionados.filter((id) => id !== servicio.id)
                                )"
                                class="admin-employees-form__checkbox"
                                type="checkbox"
                                :value="servicio.id"
                            />
                            <label
                                class="admin-employees-form__option-label"
                                :for="`available-service-${servicio.id}`"
                            >
                                {{ servicio.nombre }}
                            </label>
                        </div>
                    </div>

                    <p v-else class="admin-employees-form__empty">
                        No hay más servicios disponibles.
                    </p>
                </fieldset>

                <footer class="admin-employees-form__actions">
                    <button
                        type="button"
                        class="admin-employees-form__button admin-employees-form__button--secondary"
                        @click="$emit('close')"
                        :disabled="saving"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="admin-employees-form__button admin-employees-form__button--primary"
                        :disabled="saving"
                    >
                        {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </footer>
            </form>
        </div>
    </div>
</template>