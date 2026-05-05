<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

// Props del modal:
// - visible: controla la apertura/cierre.
// - titulo: texto principal del diálogo.
// - descripcion: texto secundario explicativo.
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    titulo: {
        type: String,
        default: 'Reserva',
    },
    descripcion: {
        type: String,
        default: '',
    },
})

// Evento emitido al padre para cerrar el modal.
const emit = defineEmits(['close'])

// Referencias para:
// - el contenedor del modal,
// - el título, al que se moverá el foco al abrir,
// - el elemento que tenía el foco antes de abrir el modal.
const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

// Cierra el modal y, después de actualizar el DOM, devuelve el foco al elemento que lo abrió.
const cerrarModal = () => {
    emit('close')

    nextTick(() => {
        if (lastTriggerRef.value && typeof lastTriggerRef.value.focus === 'function') {
            lastTriggerRef.value.focus()
        }
    })
}

// Obtiene los elementos enfocable dentro del contenedor del modal.
// Se usa para construir el ciclo de focus trap.
const getFocusableElements = (container) => {
    if (!container) return []

    return container.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

// Mantiene el foco dentro del modal cuando se navega con Tab o Shift+Tab.
// Si el foco está en el primer elemento y se pulsa Shift+Tab, salta al último. Si está en el último y se pulsa Tab, vuelve al primero.
const manejarTabEnContenedor = (event, container) => {
    const focusableElements = [...getFocusableElements(container)]
    if (!focusableElements.length) return

    const firstElement = focusableElements[0]
    const lastElement = focusableElements[focusableElements.length - 1]

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
    }
}

// Gestión global del teclado mientras el modal está abierto.
// - Escape cierra el modal.
// - Tab queda atrapado dentro del diálogo.
const manejarTecladoModal = (event) => {
    if (!props.visible) return

    if (event.key === 'Escape') {
        cerrarModal()
        return
    }

    if (event.key === 'Tab' && modalRef.value) {
        manejarTabEnContenedor(event, modalRef.value)
    }
}

// Observa el estado visible del modal.
// Al abrirlo:
// - guarda el elemento con foco previo,
// - espera a que el DOM pinte,
// - enfoca el título,
// - y registra el listener de teclado.
// Al cerrarlo:
// - elimina el listener para no dejar eventos activos.
watch(
    () => props.visible,
    async (visible) => {
        if (visible) {
            lastTriggerRef.value = document.activeElement
            await nextTick()
            modalTitleRef.value?.focus()
            document.addEventListener('keydown', manejarTecladoModal)
        } else {
            document.removeEventListener('keydown', manejarTecladoModal)
        }
    },
)

// Limpieza final por seguridad al destruir el componente.
onBeforeUnmount(() => {
    document.removeEventListener('keydown', manejarTecladoModal)
})
</script>

<template>
    <div
        v-if="visible"
        id="reservation-modal"
        class="reservation-modal"
        @click.self="cerrarModal"
    >
        <div
            ref="modalRef"
            class="reservation-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reservation-modal-title"
            aria-describedby="reservation-modal-description"
        >
            <header class="reservation-modal__header">
                <div class="reservation-modal__heading">
                    <h2
                        id="reservation-modal-title"
                        ref="modalTitleRef"
                        class="reservation-modal__title"
                        tabindex="-1"
                    >
                        {{ titulo }}
                    </h2>

                    <p
                        id="reservation-modal-description"
                        class="reservation-modal__subtitle"
                    >
                        {{ descripcion }}
                    </p>
                </div>

                <button
                    type="button"
                    class="reservation-modal__close"
                    aria-label="Cerrar formulario de reserva"
                    @click="cerrarModal"
                >
                    ×
                </button>
            </header>

            <slot></slot>
        </div>
    </div>
</template>