<script setup>
import { onMounted, ref } from 'vue'
import { getServicios, crearServicio, actualizarServicio, eliminarServicio, } from '../../api/servicios'
import StatusMessage from '@/components/feedback/StatusMessage.vue'
import AdminServiceCard from '@/components/admin/services/AdminServiceCard.vue'
import AdminServiceModal from '@/components/admin/services/AdminServiceModal.vue'

// Estado principal de la vista.
const servicios = ref([])
const loading = ref(false)
const modalVisible = ref(false)
const servicioEditando = ref(null)
const error = ref(null)
const success = ref(null)

// Carga la lista de servicios al entrar en la vista.
const cargarServicios = async () => {
    loading.value = true
    error.value = null

    try {
        servicios.value = await getServicios()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudieron cargar los servicios.'
    } finally {
        loading.value = false
    }
}

// Abre el modal.
// Si recibe un servicio, entra en modo edición, si no, se interpreta como creación.
const abrirModal = (servicio = null) => {
    error.value = null
    success.value = null
    servicioEditando.value = servicio
    modalVisible.value = true
}

// Cierra el modal y limpia el servicio en edición.
const cerrarModal = () => {
    modalVisible.value = false
    servicioEditando.value = null
}

// Guarda el servicio.
// - Si hay servicioEditando, actualiza.
// - Si no, crea uno nuevo.
const guardarServicio = async (values) => {
    error.value = null
    success.value = null

    try {
        if (servicioEditando.value) {
            await actualizarServicio(servicioEditando.value.id, values)
            success.value = 'Servicio actualizado correctamente.'
        } else {
            await crearServicio(values)
            success.value = 'Servicio creado correctamente.'
        }

        // Tras guardar, cierra modal y recarga lista para reflejar el estado actual del backend.
        cerrarModal()
        await cargarServicios()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo guardar el servicio.'
    }
}

// Elimina un servicio tras confirmación del usuario.
const eliminarServicioLocal = async (id, nombre) => {
    const confirmacion = window.confirm(`¿Eliminar el servicio ${nombre}?`)
    if (!confirmacion) return

    error.value = null
    success.value = null

    try {
        await eliminarServicio(id)
        success.value = 'Servicio eliminado correctamente.'
        await cargarServicios()
    } catch (err) {
        console.error(err)
        error.value = 'No se pudo eliminar el servicio.'
    }
}

// Carga inicial al montar la vista.
onMounted(cargarServicios)
</script>

<template>
    <main class="admin-services">
        <section class="admin-services__container" aria-labelledby="admin-services-title">
            <header class="admin-services__header">
                <div class="admin-services__heading">
                    <h1 id="admin-services-title" class="admin-services__title">
                        Gestión de servicios
                    </h1>
                    <p class="admin-services__intro">
                        Crea, edita y organiza los servicios disponibles para reserva.
                    </p>
                </div>

                <button
                    type="button"
                    class="admin-services__button admin-services__button--primary"
                    aria-label="Crear nuevo servicio"
                    @click="abrirModal()"
                >
                    Nuevo servicio
                </button>
            </header>

            <StatusMessage
                v-if="loading"
                variant="default"
                role="status"
                live="polite"
            >
                Cargando servicios...
            </StatusMessage>

            <StatusMessage
                v-if="error"
                variant="error"
                role="alert"
                live="assertive"
            >
                {{ error }}
            </StatusMessage>

            <StatusMessage
                v-if="success"
                variant="success"
                role="status"
                live="polite"
            >
                {{ success }}
            </StatusMessage>

            <div
                v-if="!loading && !servicios.length"
                class="admin-services__empty"
                role="status"
                aria-live="polite"
            >
                No hay servicios configurados.
            </div>

            <div v-if="!loading && servicios.length" class="admin-services__grid">
                <AdminServiceCard
                    v-for="servicio in servicios"
                    :key="servicio.id"
                    :servicio="servicio"
                    @edit="abrirModal"
                    @delete="eliminarServicioLocal"
                />
            </div>
        </section>

        <AdminServiceModal
            :visible="modalVisible"
            :servicio="servicioEditando"
            @close="cerrarModal"
            @submit="guardarServicio"
        />
    </main>
</template>