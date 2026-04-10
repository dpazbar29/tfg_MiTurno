<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getCatalogoServicios } from '../api/servicios'

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
        error.value = 'No se puedo cargar el catálogo de servicios.'
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
  <section class="servicios-page">
    <header class="servicios-header">
      <h1>Catálogo de servicios</h1>
      <p>Elige el servicio que quieres reservar.</p>
    </header>

    <p v-if="loading">Cargando servicios...</p>
    <p v-if="error">{{ error }}</p>

    <div v-if="!loading && servicios.length" class="servicios-grid">
      <article v-for="servicio in servicios" :key="servicio.id" class="servicio-card">
        <h2>{{ servicio.nombre }}</h2>
        <p>{{ servicio.descripcion }}</p>

        <ul>
          <li><strong>Duración:</strong> {{ servicio.duracion_minutos }} min</li>
          <li><strong>Precio:</strong> {{ servicio.precio }} €</li>
        </ul>

        <button @click="reservarServicio(servicio)">
          Reservar este servicio
        </button>
      </article>
    </div>

    <p v-if="!loading && !servicios.length">
      No hay servicios disponibles en este momento.
    </p>
  </section>
</template>