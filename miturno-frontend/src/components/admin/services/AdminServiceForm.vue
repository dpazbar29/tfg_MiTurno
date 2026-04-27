<script setup>
import { watch } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/yup'
import * as yup from 'yup'

const props = defineProps({
    servicio: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submit', 'cancel'])

const validationSchema = toTypedSchema(
    yup.object({
        nombre: yup
            .string()
            .trim()
            .required('El nombre es obligatorio.'),
        descripcion: yup
            .string()
            .nullable(),
        duracion_minutos: yup
            .number()
            .typeError('La duración es obligatoria.')
            .required('La duración es obligatoria.')
            .min(1, 'La duración debe ser mayor que 0.'),
        precio: yup
            .number()
            .typeError('El precio es obligatorio.')
            .required('El precio es obligatorio.')
            .min(0, 'El precio no puede ser negativo.'),
        activo: yup
            .boolean()
            .required(),
    }),
)

const {
    defineField,
    handleSubmit,
    errors,
    isSubmitting,
    resetForm,
} = useForm({
    validationSchema,
    initialValues: {
        nombre: '',
        descripcion: '',
        duracion_minutos: '',
        precio: '',
        activo: true,
    },
})

const [nombre, nombreAttrs] = defineField('nombre')
const [descripcion, descripcionAttrs] = defineField('descripcion')
const [duracionMinutos, duracionMinutosAttrs] = defineField('duracion_minutos')
const [precio, precioAttrs] = defineField('precio')
const [activo, activoAttrs] = defineField('activo')

watch(
    () => props.servicio,
    (servicio) => {
        if (servicio) {
            resetForm({
                values: {
                    nombre: servicio.nombre || '',
                    descripcion: servicio.descripcion || '',
                    duracion_minutos: servicio.duracion_minutos ?? '',
                    precio: servicio.precio ?? '',
                    activo: Boolean(servicio.activo),
                },
            })
        } else {
            resetForm({
                values: {
                    nombre: '',
                    descripcion: '',
                    duracion_minutos: '',
                    precio: '',
                    activo: true,
                },
            })
        }
    },
    { immediate: true },
)

const onSubmit = handleSubmit(async (values) => {
    emit('submit', values)
})
</script>

<template>
    <form
        class="admin-service-form"
        @submit="onSubmit"
        :aria-busy="isSubmitting ? 'true' : 'false'"
        novalidate
    >
        <div class="admin-service-form__field admin-service-form__field--full">
            <label class="admin-service-form__label" for="nombre">Nombre *</label>
            <input
                id="nombre"
                v-model="nombre"
                v-bind="nombreAttrs"
                class="admin-service-form__input"
                :class="{ 'admin-service-form__input--error': errors.nombre }"
                type="text"
                autocomplete="off"
                :aria-invalid="errors.nombre ? 'true' : 'false'"
                :aria-describedby="errors.nombre ? 'nombre-error' : undefined"
            />
            <p
                v-if="errors.nombre"
                id="nombre-error"
                class="admin-service-form__error"
                aria-live="polite"
            >
                {{ errors.nombre }}
            </p>
        </div>

        <div class="admin-service-form__field admin-service-form__field--full">
            <label class="admin-service-form__label" for="descripcion">Descripción</label>
            <textarea
                id="descripcion"
                v-model="descripcion"
                v-bind="descripcionAttrs"
                class="admin-service-form__textarea"
                rows="4"
            ></textarea>
        </div>

        <div class="admin-service-form__row">
            <div class="admin-service-form__field">
                <label class="admin-service-form__label" for="duracion">
                    Duración (min) *
                </label>
                <input
                    id="duracion"
                    v-model="duracionMinutos"
                    v-bind="duracionMinutosAttrs"
                    class="admin-service-form__input"
                    :class="{ 'admin-service-form__input--error': errors.duracion_minutos }"
                    type="number"
                    min="1"
                    inputmode="numeric"
                    :aria-invalid="errors.duracion_minutos ? 'true' : 'false'"
                    :aria-describedby="errors.duracion_minutos ? 'duracion-error' : undefined"
                />
                <p
                    v-if="errors.duracion_minutos"
                    id="duracion-error"
                    class="admin-service-form__error"
                    aria-live="polite"
                >
                    {{ errors.duracion_minutos }}
                </p>
            </div>

            <div class="admin-service-form__field">
                <label class="admin-service-form__label" for="precio">Precio (€) *</label>
                <input
                    id="precio"
                    v-model="precio"
                    v-bind="precioAttrs"
                    class="admin-service-form__input"
                    :class="{ 'admin-service-form__input--error': errors.precio }"
                    type="number"
                    step="0.01"
                    min="0"
                    inputmode="decimal"
                    :aria-invalid="errors.precio ? 'true' : 'false'"
                    :aria-describedby="errors.precio ? 'precio-error' : undefined"
                />
                <p
                    v-if="errors.precio"
                    id="precio-error"
                    class="admin-service-form__error"
                    aria-live="polite"
                >
                    {{ errors.precio }}
                </p>
            </div>
        </div>

        <div class="admin-service-form__field admin-service-form__field--full">
            <label class="admin-service-form__checkbox">
                <input
                    v-model="activo"
                    v-bind="activoAttrs"
                    class="admin-service-form__checkbox-input"
                    type="checkbox"
                />
                <span class="admin-service-form__checkbox-label">Activo</span>
            </label>
        </div>

        <div class="admin-service-form__actions">
            <button
                type="button"
                class="admin-service-form__button admin-service-form__button--secondary"
                @click="$emit('cancel')"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="admin-service-form__button admin-service-form__button--primary"
                :disabled="isSubmitting"
            >
                {{ isSubmitting ? 'Guardando...' : servicio ? 'Actualizar' : 'Crear' }}
            </button>
        </div>
    </form>
</template>