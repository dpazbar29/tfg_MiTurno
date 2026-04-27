<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

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

const emit = defineEmits(['close'])

const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

const cerrarModal = () => {
    emit('close')

    nextTick(() => {
        if (lastTriggerRef.value && typeof lastTriggerRef.value.focus === 'function') {
            lastTriggerRef.value.focus()
        }
    })
}

const getFocusableElements = (container) => {
    if (!container) return []

    return container.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

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