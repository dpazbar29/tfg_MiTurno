<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const logout = async () => {
    await auth.logout()
    router.push('/login')
}

const irAServicios = () => {
    router.push('/servicios')
}
</script>

<template>
    <section>
        <h1>Dashboard</h1>

        <p v-if="auth.user">
            Bienvenido, {{ auth.user.nombre }} {{ auth.user.apellidos }}
        </p>

        <p v-if="auth.user">
            Rol: {{ auth.user.rol }}
        </p>

        <button @click="irAServicios">Ver Servicios</button>
        <button @click="logout">Cerrar sesión</button>
    </section>
</template>
