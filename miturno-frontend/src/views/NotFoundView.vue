<script setup>
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()

const goHome = () => {
    window.location.href = '/'
}
</script>

<template>
    <main class="not-found">
        <section class="not-found__container">
            <div class="not-found__content">
                <div class="not-found__main">
                    <div class="not-found__code">404</div>
                    <h1 class="not-found__title">Página no encontrada</h1>
                    <p class="not-found__text">
                        La página que buscas no existe o ha sido movida.
                    </p>
                    <p v-if="route.fullPath !== '/'" class="not-found__path">
                        <strong>Intentaste acceder a:</strong><br>
                        <code>{{ route.fullPath }}</code>
                    </p>
                </div>

                <div class="not-found__actions">
                    <RouterLink to="/" class="not-found__button not-found__button--primary">
                        Ir al inicio
                    </RouterLink>

                    <button class="not-found__button not-found__button--secondary" @click="goHome">
                        Recargar página
                    </button>
                </div>
            </div>
        </section>
    </main>
</template>