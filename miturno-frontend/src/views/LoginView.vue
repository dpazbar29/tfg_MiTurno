<script setup>
import { computed, reactive } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
    email: '',
    password: '',
})

const hasError = computed(() => Boolean(auth.error))

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
    <main class="login">
        <section class="login__container" aria-labelledby="login-title">
            <h1 id="login-title" class="login__title">Iniciar sesión</h1>
            <form
                class="login__form"
                @submit.prevent="submit"
                :aria-busy="auth.loading ? 'true' : 'false'"
                novalidate
            >
                <div class="login__field">
                <label class="login__label" for="email">Correo electrónico</label>
                <input
                    id="email"
                    v-model.trim="form.email"
                    class="login__input"
                    :class="{ 'login__input--error': hasError }"
                    type="email"
                    name="email"
                    autocomplete="email"
                    inputmode="email"
                    required
                    :aria-invalid="hasError ? 'true' : 'false'"
                    :aria-describedby="hasError ? 'login-error' : undefined"
                />
                </div>

                <div class="login__field">
                <label class="login__label" for="password">Contraseña</label>
                <input
                    id="password"
                    v-model="form.password"
                    class="login__input"
                    :class="{ 'login__input--error': hasError }"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    :aria-invalid="hasError ? 'true' : 'false'"
                    :aria-describedby="hasError ? 'login-error' : undefined"
                />
                </div>

                <button
                class="login__submit"
                :class="{ 'login__submit--loading': auth.loading }"
                type="submit"
                :disabled="auth.loading"
                >
                {{ auth.loading ? 'Entrando...' : 'Entrar' }}
                </button>

                <p
                v-if="auth.error"
                id="login-error"
                class="login__error"
                role="alert"
                aria-live="assertive"
                >
                {{ auth.error }}
                </p>
            </form>

            <p class="login__footer">
                ¿No tienes cuenta?
                <RouterLink class="login__link" to="/register">
                Regístrate
                </RouterLink>
            </p>
        </section>
    </main>
</template>