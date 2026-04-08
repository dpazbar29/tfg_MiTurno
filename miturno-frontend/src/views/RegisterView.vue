<script setup>
import { computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/yup'
import * as yup from 'yup'

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
    <main class="register">
        <section class="register__container" aria-labelledby="register-title">
            <h1 id="register-title" class="register__title">Crear cuenta</h1>

            <form class="register__form" @submit="submit" :aria-busy="auth.loading || isSubmitting ? 'true' : 'false'" novalidate>
                <div class="register__field">
                    <label class="register__label" for="nombre">Nombre</label>
                    <input 
                        id="nombre"
                        v-model="nombre"
                        v-bind="nombreAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.nombre }"
                        type="text"
                        name="nombre"
                        autocomplete="given-name"
                        :aria-invalid="errors.nombre ? 'true' : 'false'"
                        :aria-describedby="errors.nombre ? 'nombre-error' : undefined"
                    />
                    <p
                        v-if="errors.nombre"
                        id="nombre-error"
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.nombre }}
                    </p>
                </div>

                <div class="register__field">
                    <label class="register__label" for="apellidos">Apellidos</label>
                    <input
                        id="apellidos"
                        v-model="apellidos"
                        v-bind="apellidosAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.apellidos }"
                        type="text"
                        name="apellidos"
                        autocomplete="family-name"
                        :aria-invalid="errors.apellidos ? 'true' : 'false'"
                        :aria-describedby="errors.apellidos ? 'apellidos-error' : undefined"
                    />
                    <p
                        v-if="errors.apellidos"
                        id="apellidos-error"
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.apellidos }}
                    </p>
                </div>

                <div class="register__field">
                    <label class="register__label" for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input
                        id="fecha_nacimiento"
                        v-model="fechaNacimiento"
                        v-bind="fechaNacimientoAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.fecha_nacimiento }"
                        type="date"
                        name="fecha_nacimiento"
                        :aria-invalid="errors.fecha_nacimiento ? 'true' : 'false'"
                        :aria-describedby="errors.fecha_nacimiento ? 'fecha-nacimiento-error' : undefined"
                    />
                    <p
                        v-if="errors.fecha_nacimiento"
                        id="fecha-nacimiento-error"
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.fecha_nacimiento }}
                    </p>
                </div>

                <div class="register__field">
                    <label class="register__label" for="telefono">Teléfono</label>
                    <input
                        id="telefono"
                        v-model="telefono"
                        v-bind="telefonoAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.telefono }"
                        type="tel"
                        name="telefono"
                        autocomplete="tel"
                        inputmode="tel"
                        :aria-invalid="errors.telefono ? 'true' : 'false'"
                        :aria-describedby="errors.telefono ? 'telefono-error' : undefined"
                    />
                    <p
                        v-if="errors.telefono"
                        id="telefono-error"
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.telefono }}
                    </p>
                </div>

                <div class="register__field">
                    <label class="register__label" for="email">Correo electrónico</label>
                    <input
                        id="email"
                        v-model="email"
                        v-bind="emailAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.email }"
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
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.email }}
                    </p>
                </div>

                <div class="register__field">
                    <label class="register__label" for="password">Contraseña</label>
                    <input
                        id="password"
                        v-model="password"
                        v-bind="passwordAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.password }"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        :aria-invalid="errors.password ? 'true' : 'false'"
                        :aria-describedby="errors.password ? 'password-error' : undefined"
                    />
                    <p
                        v-if="errors.password"
                        id="password-error"
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.password }}
                    </p>
                </div>

                <div class="register__field">
                    <label class="register__label" for="password_confirmation">
                        Confirmar contraseña
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="passwordConfirmation"
                        v-bind="passwordConfirmationAttrs"
                        class="register__input"
                        :class="{ 'register__input--error': !!errors.password_confirmation }"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        :aria-invalid="errors.password_confirmation ? 'true' : 'false'"
                        :aria-describedby="errors.password_confirmation ? 'password-confirmation-error' : undefined"
                    />
                    <p
                        v-if="errors.password_confirmation"
                        id="password-confirmation-error"
                        class="register__field-error"
                        aria-live="polite"
                    >
                        {{ errors.password_confirmation }}
                    </p>
                </div>

                <button
                    class="register__submit"
                    :class="{ 'register__submit--loading': auth.loading || isSubmitting }"
                    type="submit"
                    :disabled="auth.loading || isSubmitting"
                >
                {{ auth.loading || isSubmitting ? 'Creando cuenta...' : 'Registrarse' }}
                </button>

                <p
                    v-if="hasServerError && !errors.email"
                    id="register-error"
                    class="register__error"
                    role="alert"
                    aria-live="assertive"
                >
                {{ auth.error }}
                </p>
            </form>

            <p class="register__footer">
                ¿Ya tienes cuenta?
                <RouterLink class="register__link" to="/login">
                Inicia sesión
                </RouterLink>
            </p>
        </section>
    </main>
</template>