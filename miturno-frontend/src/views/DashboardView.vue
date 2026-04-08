<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const logout = async () => {
    await auth.logout()
    router.push('/login')
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

        <button @click="logout">Cerrar sesión</button>
    </section>
</template>
