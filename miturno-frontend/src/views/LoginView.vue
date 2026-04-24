<script setup>
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/yup'
import * as yup from 'yup'

import AuthCard from '@/components/auth/AuthCard.vue'
import BaseInput from '@/components/forms/BaseInput.vue'
import BaseButton from '@/components/forms/BaseButton.vue'

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
} = useForm({
    validationSchema: loginSchema,
    initialValues: {
        email: '',
        password: '',
    },
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

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
    <AuthCard title="Iniciar sesión">
        <form
            class="login-form"
            @submit.prevent="submit"
            :aria-busy="auth.loading ? 'true' : 'false'"
            novalidate
        >
            <BaseInput
                id="email"
                v-model="email"
                name="email"
                label="Correo electrónico"
                type="email"
                autocomplete="email"
                inputmode="email"
                :attrs="emailAttrs"
                :error="errors.email"
            />

            <BaseInput
                id="password"
                v-model="password"
                name="password"
                label="Contraseña"
                type="password"
                autocomplete="current-password"
                :attrs="passwordAttrs"
                :error="errors.password"
            />

            <BaseButton
                class="login-form__submit"
                :class="{ 'login-form__submit--loading': auth.loading }"
                type="submit"
                :disabled="auth.loading"
            >
                {{ auth.loading ? 'Entrando' : 'Entrar' }}
            </BaseButton>

            <p
                v-if="auth.error"
                id="login-error"
                class="login-form__error"
                role="alert"
                aria-live="assertive"
            >
                {{ auth.error }}
            </p>
        </form>

        <p class="login-form__footer">
            ¿No tienes cuenta?
            <RouterLink class="login-form__link" to="/register">
                Regístrate
            </RouterLink>
        </p>
  </AuthCard>
</template>