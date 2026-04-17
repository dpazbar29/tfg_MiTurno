<script setup>
import { ref, onMounted, computed, nextTick, onBeforeUnmount } from 'vue'
import { getEmpleados, getEmpleado, syncServiciosEmpleado } from '../../api/empleados'
import { getServicios } from '../../api/servicios'

const empleados = ref([])
const servicios = ref([])
const loading = ref(false)
const saving = ref(false)
const empleadoEditando = ref(null)
const serviciosSeleccionados = ref([])
const error = ref(null)
const success = ref(null)

const modalRef = ref(null)
const modalTitleRef = ref(null)
const lastTriggerRef = ref(null)

const cargarDatos = async () => {
    loading.value = true
    error.value = null

    try {
        const [listaEmpleados, listaServicios] = await Promise.all([
            getEmpleados(),
            getServicios(),
        ])
        empleados.value = listaEmpleados
        servicios.value = listaServicios
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron cargar los empleados o los servicios.'
    } finally {
        loading.value = false
    }
}

const abrirModal = async (empleado, event = null) => {
    lastTriggerRef.value = event?.currentTarget || document.activeElement
    error.value = null
    success.value = null

    try {
        empleadoEditando.value = await getEmpleado(empleado.id)
        serviciosSeleccionados.value = empleadoEditando.value.servicios.map((s) => s.id)

        await nextTick()
        modalTitleRef.value?.focus()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo cargar la información del empleado.'
    }
}

const cerrarModal = () => {
    empleadoEditando.value = null
    serviciosSeleccionados.value = []
    lastTriggerRef.value?.focus?.()
}

const guardarServicios = async () => {
    if (!empleadoEditando.value) return

    saving.value = true
    error.value = null
    success.value = null

    try {
        await syncServiciosEmpleado(
            empleadoEditando.value.id,
            serviciosSeleccionados.value,
        )
        await cargarDatos()
        success.value = 'Servicios actualizados correctamente.'
        cerrarModal()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron guardar los cambios.'
    } finally {
        saving.value = false
    }
}

const serviciosAsignados = computed(() => {
    if (!empleadoEditando.value) return []
    return servicios.value.filter((s) => serviciosSeleccionados.value.includes(s.id))
})

const serviciosDisponibles = computed(() => {
    if (!empleadoEditando.value) return []
    return servicios.value.filter((s) => !serviciosSeleccionados.value.includes(s.id))
})

const formatearFecha = (fecha) => {
    return new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'medium',
    }).format(new Date(fecha))
}

const getFocusableElements = () => {
    if (!modalRef.value) return []

    return modalRef.value.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )
}

const manejarTabModal = (event) => {
    if (!empleadoEditando.value || event.key !== 'Tab') return

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
    if (!empleadoEditando.value) return

    if (event.key === 'Escape') {
        cerrarModal()
        return
    }

    manejarTabModal(event)
}

onMounted(cargarDatos)
onMounted(() => document.addEventListener('keydown', manejarTecladoModal))
onBeforeUnmount(() => document.removeEventListener('keydown', manejarTecladoModal))
</script>

<template>
    <main class="admin-employees">
        <section class="admin-employees__container" aria-labelledby="admin-employees-title">
            <header class="admin-employees__header">
                <div class="admin-employees__heading">
                    <h1 id="admin-employees-title" class="admin-employees__title">
                        Gestión de empleados
                    </h1>
                    <p class="admin-employees__intro">
                        Consulta cada empleado y asigna los servicios disponibles.
                    </p>
                </div>
            </header>

            <p
                v-if="loading"
                class="admin-employees__status"
                role="status"
                aria-live="polite"
            >
                Cargando empleados...
            </p>

            <p
                v-if="error"
                class="admin-employees__message admin-employees__message--error"
                role="alert"
                aria-live="assertive"
            >
                {{ error }}
            </p>

            <p
                v-if="success"
                class="admin-employees__message admin-employees__message--success"
                role="status"
                aria-live="polite"
            >
                {{ success }}
            </p>

            <div
                v-if="!loading && empleados.length"
                class="admin-employees__grid"
            >
                <article
                    v-for="empleado in empleados"
                    :key="empleado.id"
                    class="employee-card"
                    :aria-labelledby="`employee-card-title-${empleado.id}`"
                >
                    <header class="employee-card__header">
                        <h2
                            :id="`employee-card-title-${empleado.id}`"
                            class="employee-card__title"
                        >
                            {{ empleado.usuario?.nombre }} {{ empleado.usuario?.apellidos }}
                        </h2>
                    </header>

                    <dl class="employee-card__meta">
                        <div class="employee-card__meta-item">
                            <dt>Contratación</dt>
                            <dd>
                                <time :datetime="empleado.fecha_contratacion">
                                    {{ formatearFecha(empleado.fecha_contratacion) }}
                                </time>
                            </dd>
                        </div>

                        <div class="employee-card__meta-item">
                            <dt>Servicios</dt>
                            <dd>{{ empleado.servicios?.length || 0 }}</dd>
                        </div>
                    </dl>

                    <div
                        v-if="empleado.servicios?.length"
                        class="employee-card__services"
                    >
                        <h3 class="employee-card__services-title">Servicios asignados</h3>
                        <ul class="employee-card__services-list" role="list">
                            <li
                                v-for="servicio in empleado.servicios"
                                :key="servicio.id"
                                class="employee-card__services-item"
                            >
                                {{ servicio.nombre }}
                            </li>
                        </ul>
                    </div>

                    <p v-else class="employee-card__empty">
                        No tiene servicios asignados.
                    </p>

                    <div class="employee-card__actions">
                        <button
                            type="button"
                            class="employee-card__button employee-card__button--primary"
                            @click="abrirModal(empleado, $event)"
                            :aria-label="`Gestionar servicios de ${empleado.usuario?.nombre} ${empleado.usuario?.apellidos}`"
                        >
                            Gestionar servicios
                        </button>
                    </div>
                </article>
            </div>

            <p
                v-else-if="!loading && !empleados.length"
                class="admin-employees__empty"
                role="status"
                aria-live="polite"
            >
                No hay empleados disponibles.
            </p>
        </section>

        <div
            v-if="empleadoEditando"
            class="admin-employees-modal"
            @click.self="cerrarModal"
        >
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
                        Servicios de {{ empleadoEditando.usuario?.nombre }} {{ empleadoEditando.usuario?.apellidos }}
                    </h2>

                    <button
                        type="button"
                        class="admin-employees-modal__close"
                        @click="cerrarModal"
                        aria-label="Cerrar gestión de servicios"
                    >
                        ×
                    </button>
                </header>

                <p
                    id="employee-services-description"
                    class="admin-employees-modal__description"
                >
                    Marca o desmarca los servicios que puede atender este empleado.
                </p>

                <form
                    class="admin-employees-form"
                    @submit.prevent="guardarServicios"
                    :aria-busy="saving ? 'true' : 'false'"
                >
                    <fieldset class="admin-employees-form__group">
                        <legend class="admin-employees-form__legend">
                            Servicios asignados
                        </legend>

                        <div
                            v-if="serviciosAsignados.length"
                            class="admin-employees-form__options"
                        >
                            <div
                                v-for="servicio in serviciosAsignados"
                                :key="servicio.id"
                                class="admin-employees-form__option"
                            >
                                <input
                                    :id="`assigned-service-${servicio.id}`"
                                    v-model="serviciosSeleccionados"
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
                        <legend class="admin-employees-form__legend">
                            Servicios disponibles
                        </legend>

                        <div
                            v-if="serviciosDisponibles.length"
                            class="admin-employees-form__options"
                        >
                            <div
                                v-for="servicio in serviciosDisponibles"
                                :key="servicio.id"
                                class="admin-employees-form__option"
                            >
                                <input
                                    :id="`available-service-${servicio.id}`"
                                    v-model="serviciosSeleccionados"
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
                            @click="cerrarModal"
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
    </main>
</template>