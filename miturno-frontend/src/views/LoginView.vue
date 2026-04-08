<script setup>
import { computed, reactive } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/yup'
import * as yup from 'yup'

const auth = useAuthStore()
const router = useRouter()

const loginSchema = toTypedSchema(
    yup.object({
        email: yup
            .string()
            .required('El correo electrónico es obligatorio.')
            .email('Introduce un correo electrónico válido.'),
        password: yup
            .string()
            .required('La contraseña es obligatoria.')
            .min(6, 'La contraseña debe tener al menos 6 caracteres.'),
    }),
)

const {
    defineField,
    handleSubmit,
    errors,
    meta,
} = useForm({
    validationSchema: loginSchema,
    initialValues: {
        email: '',
        password: '',
    },
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

const hasError = computed(() => Boolean(auth.error))

const submit = handleSubmit(async (values) => {
    try {
        await auth.login(values)
        router.push('/dashboard')
    } catch (error) {
        console.error(error)
    }
})
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
                        v-model="email"
                        v-bind="emailAttrs"
                        class="login__input"
                        :class="{ 'login__input--error': errors.email }"
                        type="email"
                        name="email"
                        autocomplete="email"
                        inputmode="email"
                        :aria-invalid="errors.email ? 'true' : 'false'"
                        :aria-describedby="errors.email ? 'email-error' : undefined"
                    />
                    <p
                    v-if="errors.email"
                    id="email-error"
                    class="login__field-error"
                    aria-live="polite"
                    >
                    {{ errors.email }}
                    </p>
                </div>

                <div class="login__field">
                    <label class="login__label" for="password">Contraseña</label>
                    <input
                        id="password"
                        v-model="password"
                        v-bind="passwordAttrs"
                        class="login__input"
                        :class="{ 'login__input--error': errors.password }"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        :aria-invalid="errors.password ? 'true' : 'false'"
                        :aria-describedby="errors.pasword ? 'login-error' : undefined"
                    />
                    <p
                        v-if="errors.password"
                        id="password-error"
                        class="login__field-error"
                        aria-live="polite"
                        >
                        {{ errors.password }}
                    </p>
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