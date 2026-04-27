<script setup>
    import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
    import AdminServiceForm from './AdminServiceForm.vue'

    const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    servicio: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['close', 'submit'])

const lastTriggerRef = ref(null)
const modalRef = ref(null)
const modalTitleRef = ref(null)

const guardarTriggerActivo = () => {
    lastTriggerRef.value = document.activeElement
}

const restaurarFoco = () => {
    lastTriggerRef.value?.focus?.()
}

const getFocusableElements = () => {
    if (!modalRef.value) return []

    return modalRef.value.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

const manejarTabModal = (event) => {
    if (!props.visible || event.key !== 'Tab') return

    const focusableElements = [...getFocusableElements()]
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
        emit('close')
        return
    }

    manejarTabModal(event)
}

watch(
    () => props.visible,
    async (visible) => {
        if (visible) {
            guardarTriggerActivo()
            await nextTick()
            modalTitleRef.value?.focus()
        } else {
            restaurarFoco()
        }
    },
)

onMounted(() => {
    document.addEventListener('keydown', manejarTecladoModal)
})

onBeforeUnmount(() => {
    document.removeEventListener('keydown', manejarTecladoModal)
})
</script>

<template>
    <div
        v-if="visible"
        class="admin-service-modal"
        @click.self="$emit('close')"
    >
        <div
            ref="modalRef"
            class="admin-service-modal__dialog"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="servicio ? 'service-edit-title' : 'service-create-title'"
            aria-describedby="service-modal-description"
        >
            <header class="admin-service-modal__header">
                <h2
                    :id="servicio ? 'service-edit-title' : 'service-create-title'"
                    ref="modalTitleRef"
                    class="admin-service-modal__title"
                    tabindex="-1"
                >
                    {{ servicio ? 'Editar servicio' : 'Nuevo servicio' }}
                </h2>

                <button
                    type="button"
                    class="admin-service-modal__close"
                    aria-label="Cerrar formulario de servicio"
                    @click="$emit('close')"
                >
                    ×
                </button>
            </header>

            <p id="service-modal-description" class="admin-service-modal__description">
                Completa los campos obligatorios para guardar el servicio.
            </p>

            <AdminServiceForm
                :servicio="servicio"
                @submit="$emit('submit', $event)"
                @cancel="$emit('close')"
            />
        </div>
    </div>
</template>