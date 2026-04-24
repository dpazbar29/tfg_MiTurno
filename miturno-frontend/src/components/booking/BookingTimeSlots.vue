<script setup>
defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    disponibilidad: {
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
    <fieldset
        v-if="disponibilidad.length"
        class="booking-times booking-field booking-field--full"
        :aria-describedby="error ? 'hora-error' : 'hora-help'"
    >
        <legend class="booking-field__legend">Horas disponibles</legend>

        <p id="hora-help" class="booking-field__help">
            Elige una única hora disponible para tu reserva.
        </p>

        <div class="booking-times__grid">
            <label
                v-for="hora in disponibilidad"
                :key="hora"
                class="booking-times__option"
                :class="{ 'booking-times__option--selected': modelValue === hora }"
            >
                <input
                    class="booking-times__input"
                    type="radio"
                    name="hora"
                    :value="hora"
                    :checked="modelValue === hora"
                    @change="$emit('update:modelValue', hora)"
                />
                    <span class="booking-times__label">
                        {{ hora }}
                    </span>
            </label>
        </div>

        <p
            v-if="error"
            id="hora-error"
            class="booking-field__error"
            aria-live="polite"
        >
            {{ error }}
        </p>
    </fieldset>
</template>