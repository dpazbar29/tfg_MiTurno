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
    <main class="services" aria-labelledby="services-title">
        <section class="services__container">
            <header class="services__header">
            <h1 id="services-title" class="services__title">Catálogo de servicios</h1>
            <p class="services__intro">Elige el servicio que quieres reservar.</p>
            </header>

            <p
                v-if="loading"
                class="services__status"
                role="status"
                aria-live="polite"
            >
                Cargando servicios...
            </p>

            <p
                v-else-if="error"
                class="services__error"
                role="alert"
                aria-live="assertive"
            >
                {{ error }}
            </p>

            <p
                v-else-if="!servicios.length"
                class="services__empty"
                role="status"
                aria-live="polite"
            >
                No hay servicios disponibles en este momento.
            </p>

            <div
                v-else
                class="services__grid"
            >
                <article
                    v-for="servicio in servicios"
                    :key="servicio.id"
                    class="services__card"
                    :aria-labelledby="`service-title-${servicio.id}`"
                >
                    <div class="services__card-body">
                        <h2
                            :id="`service-title-${servicio.id}`"
                            class="services__card-title"
                        >
                            {{ servicio.nombre }}
                        </h2>

                        <p
                            v-if="servicio.descripcion"
                            class="services__card-description"
                        >
                            {{ servicio.descripcion }}
                        </p>

                        <dl class="services__meta">
                            <div class="services__meta-item">
                                <dt class="services__meta-term">Duración</dt>
                                <dd class="services__meta-value">{{ servicio.duracion_minutos }} min</dd>
                            </div>

                            <div class="services__meta-item">
                                <dt class="services__meta-term">Precio</dt>
                                <dd class="services__meta-value">{{ servicio.precio }} €</dd>
                            </div>
                        </dl>
                    </div>

                    <button
                        type="button"
                        class="services__button"
                        @click="reservarServicio(servicio)"
                        :aria-label="`Reservar el servicio ${servicio.nombre}`"
                    >
                        Reservar este servicio
                    </button>
                </article>
            </div>
        </section>
    </main>
</template>