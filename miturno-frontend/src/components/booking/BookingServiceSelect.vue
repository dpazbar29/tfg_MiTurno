<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    servicios: {
        type: Array,
        default: () => [],
    },
    error: {
        type: String,
        default: '',
    },
})

defineEmits(['update:modelValue'])
</script>

<template>
    <div class="booking-field booking-field--full">
        <label class="booking-field__label" for="servicio">
            Servicio
        </label>

        <select
            id="servicio"
            class="booking-field__control booking-field__control--select"
            :value="modelValue"
            required
            :aria-invalid="error ? 'true' : 'false'"
            :aria-describedby="error ? 'servicio-error' : undefined"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option disabled value="">Selecciona un servicio</option>
            <option
                v-for="servicio in servicios"
                :key="servicio.id"
                :value="servicio.id"
            >
                {{ servicio.nombre }} - {{ servicio.duracion_minutos }} min - {{ servicio.precio }} €
            </option>
        </select>

        <p
            v-if="error"
            id="servicio-error"
            class="booking-field__error"
            aria-live="polite"
        >
            {{ error }}
        </p>
    </div>
</template>