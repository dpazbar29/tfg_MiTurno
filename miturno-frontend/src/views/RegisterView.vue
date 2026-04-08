<script setup>
import { reactive } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
    nombre: '',
    apellidos: '',
    fecha_nacimiento: '',
    telefono: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = async () => {
    try {
        await auth.register(form)
        router.push('/dashboard')
    } catch (error) {
        console.error(error)
    }
}
</script>

<template>
    <section>
        <h1>Crear cuenta</h1>

        <form @submit.prevent="submit">
            <div>
                <label for="nombre">Nombre</label>
                <input id="nombre" v-model="form.nombre" type="text" required />
            </div>

            <div>
                <label for="apellidos">Apellidos</label>
                <input id="apellidos" v-model="form.apellidos" type="text" required />
            </div>

            <div>
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input id="fecha_nacimiento" v-model="form.fecha_nacimiento" type="date" />
            </div>

            <div>
                <label for="telefono">Teléfono</label>
                <input id="telefono" v-model="form.telefono" type="text" />
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" v-model="form.email" type="email" required />
            </div>

            <div>
                <label for="password">Contraseña</label>
                <input id="password" v-model="form.password" type="password" required />
            </div>

            <div>
                <label for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" required />
            </div>

            <button type="submit" :disabled="auth.loading">
                {{ auth.loading ? 'Creando cuenta...' : 'Registrarse' }}
            </button>

            <p v-if="auth.error">{{ auth.error }}</p>
        </form>

        <p>
            ¿Ya tienes cuenta?
            <RouterLink to="/login">Inicia sesión</RouterLink>
        </p>
    </section>
</template>