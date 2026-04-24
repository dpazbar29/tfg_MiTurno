<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    empleados: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    getEmployeeName: {
        type: Function,
        required: true,
    },
})

defineEmits(['update:modelValue'])
</script>

<template>
    <div class="booking-field">
        <label class="booking-field__label" for="empleado">
            Empleado
        </label>

        <div class="booking-field__select-wrapper">
            <select
                id="empleado"
                class="booking-field__control booking-field__control--select"
                :value="modelValue"
                :disabled="!empleados.length || loading"
                aria-label="Seleccionar empleado disponible"
                @change="$emit('update:modelValue', $event.target.value)"
            >
                <option value="">Cualquier profesional disponible</option>
                <option
                    v-for="empleado in empleados"
                    :key="empleado.id"
                    :value="empleado.id"
                >
                    {{ getEmployeeName(empleado) }}
                </option>
            </select>

            <span
                v-if="loading"
                class="booking-field__loading"
                aria-live="polite"
            >
                Cargando empleados...
            </span>
        </div>
    </div>
</template>