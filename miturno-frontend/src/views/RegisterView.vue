<script setup>
import { computed } from 'vue'
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

const registerSchema = toTypedSchema(
    yup.object({
        nombre: yup
            .string()
            .required('El nombre es obligatorio.')
            .min(2, 'El nombre debe tener al menos 2 caracteres.'),
        apellidos: yup
            .string()
            .required('Los apellidos son obligatorios.')
            .min(2, 'Los apellidos deben tener al menos 2 caracteres.'),
        fecha_nacimiento: yup
            .string()
            .nullable(),
        telefono: yup
            .string()
            .nullable(),
        email: yup
            .string()
            .required('El correo electrónico es obligatorio.')
            .email('Introduce un correo electrónico válido.'),
        password: yup
            .string()
            .required('La contraseña es obligatoria.')
            .min(6, 'La contraseña debe tener al menos 6 caracteres.'),
        password_confirmation: yup
            .string()
            .required('Debes confirmar la contraseña.')
            .oneOf([yup.ref('password')], 'Las contraseñas no coinciden.'),
    }),
)

const {
    defineField,
    handleSubmit,
    errors,
    isSubmitting,
    setFieldError,
} = useForm({
    validationSchema: registerSchema,
    initialValues: {
        nombre: '',
        apellidos: '',
        fecha_nacimiento: '',
        telefono: '',
        email: '',
        password: '',
        password_confirmation: '',
    },
})

const [nombre, nombreAttrs] = defineField('nombre')
const [apellidos, apellidosAttrs] = defineField('apellidos')
const [fechaNacimiento, fechaNacimientoAttrs] = defineField('fecha_nacimiento')
const [telefono, telefonoAttrs] = defineField('telefono')
const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')
const [passwordConfirmation, passwordConfirmationAttrs] = defineField('password_confirmation')

const hasServerError = computed(() => Boolean(auth.error))

const submit = handleSubmit(async (values) => {
    try {
        await auth.register(values)
        router.push('/dashboard')
    } catch (error) {
        const message = auth.error || 'No se pudo completar el registro. Inténtalo de nuevo.'

        if (message.toLowerCase().includes('email') || message.toLowerCase().includes('correo')) {
            setFieldError('email', message)
            return
        }

        console.error(error)
    }
})
</script>

<template>
    <AuthCard title="Crear cuenta">
        <form
            class="register-form"
            @submit="submit"
            :aria-busy="auth.loading || isSubmitting ? 'true' : 'false'"
            novalidate
        >
            <div class="register-form__fields">
                <BaseInput
                    id="nombre"
                    v-model="nombre"
                    name="nombre"
                    label="Nombre"
                    autocomplete="given-name"
                    :attrs="nombreAttrs"
                    :error="errors.nombre"
                />

                <BaseInput
                    id="apellidos"
                    v-model="apellidos"
                    name="apellidos"
                    label="Apellidos"
                    autocomplete="family-name"
                    :attrs="apellidosAttrs"
                    :error="errors.apellidos"
                />

                <BaseInput
                    id="fecha_nacimiento"
                    v-model="fechaNacimiento"
                    name="fecha_nacimiento"
                    label="Fecha de nacimiento"
                    type="date"
                    :attrs="fechaNacimientoAttrs"
                    :error="errors.fecha_nacimiento"
                />

                <BaseInput
                    id="telefono"
                    v-model="telefono"
                    name="telefono"
                    label="Teléfono"
                    type="tel"
                    autocomplete="tel"
                    inputmode="tel"
                    :attrs="telefonoAttrs"
                    :error="errors.telefono"
                />
            </div>

            <div class="register-form__credentials">
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
                    autocomplete="new-password"
                    :attrs="passwordAttrs"
                    :error="errors.password"
                />

                <BaseInput
                    id="password_confirmation"
                    v-model="passwordConfirmation"
                    name="password_confirmation"
                    label="Confirmar contraseña"
                    type="password"
                    autocomplete="new-password"
                    :attrs="passwordConfirmationAttrs"
                    :error="errors.password_confirmation"
                />
            </div>

            <BaseButton
                class="register-form__submit"
                :loading="auth.loading || isSubmitting"
                :disabled="auth.loading || isSubmitting"
            >
                {{ auth.loading || isSubmitting ? 'Creando cuenta...' : 'Registrarse' }}
            </BaseButton>

            <p
                v-if="hasServerError && !errors.email"
                id="register-error"
                class="register-form__error"
                role="alert"
                aria-live="assertive"
            >
                {{ auth.error }}
            </p>
        </form>

        <p class="register-form__footer">
            ¿Ya tienes cuenta?
            <RouterLink class="register-form__link" to="/login">
                Inicia sesión
            </RouterLink>
        </p>
    </AuthCard>
</template>