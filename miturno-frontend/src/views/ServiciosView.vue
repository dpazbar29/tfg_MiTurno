<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getCatalogoServicios } from '../api/servicios'

import ServiceCard from '@/components/services/ServiceCard.vue'
import StatusMessage from '@/components/feedback/StatusMessage.vue'

const router = useRouter()
const servicios = ref([])
const loading = ref(false)
const error = ref(null)

const cargarServicios = async () => {
    loading.value = true
    error.value = null

    try {
        servicios.value = await getCatalogoServicios()
    } catch (err) {
        error.value = 'No se pudo cargar el catálogo de servicios.'
        console.error(err)
    } finally {
        loading.value = false
    }
}

const reservarServicio = (servicio) => {
    router.push({
        path: '/reservas/nueva',
        query: { servicio_id: servicio.id },
    })
}

onMounted(cargarServicios)
</script>

<template>
    <main class="services" aria-labelledby="services-title">
        <section class="services__container">
            <header class="services__header">
                <h1 id="services-title" class="services__title">
                    Catálogo de servicios
                </h1>
                <p class="services__intro">
                    Elige el servicio que quieres reservar.
                </p>
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
                v-else-if="error"
                variant="error"
                role="alert"
                live="assertive"
            >
                {{ error }}
            </StatusMessage>

            <StatusMessage
                v-else-if="!servicios.length"
                variant="muted"
                role="status"
                live="polite"
            >
                No hay servicios disponibles en este momento.
            </StatusMessage>

            <div v-else class="services__grid">
                <ServiceCard
                    v-for="servicio in servicios"
                    :key="servicio.id"
                    :servicio="servicio"
                    @reserve="reservarServicio(servicio)"
                />
            </div>
        </section>
    </main>
</template>