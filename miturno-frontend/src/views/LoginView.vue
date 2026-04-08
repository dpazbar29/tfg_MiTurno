<script setup>
import { reactive } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
    email: '',
    password: '',
})

const submit = async () => {
    try {
        await auth.login(form)
        router.push('/dashboard')
    } catch (error) {
        console.error(error)
    }
}
</script>

<template>
    <section>
        <h1>Iniciar sesión</h1>

        <form @submit.prevent="submit">
            <div>
                <label for="email">Email</label>
                <input id="email" v-model="form.email" type="email" required />
            </div>

            <div>
                <label for="password">Contraseña</label>
                <input id="password" v-model="form.password" type="password" required />
            </div>

            <button type="submit" :disabled="auth.loading">
                {{ auth.loading ? 'Entrando...' : 'Entrar' }}
            </button>

            <p v-if="auth.error">{{ auth.error }}</p>
        </form>

        <p>
            ¿No tienes cuenta?
            <RouterLink to="/register">Regístrate</RouterLink>
        </p>
    </section>
</template>