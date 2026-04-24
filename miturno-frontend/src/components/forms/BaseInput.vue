<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: 'text',
    },
    autocomplete: {
        type: String,
        default: '',
    },
    inputmode: {
        type: String,
        default: undefined,
    },
    error: {
        type: String,
        default: '',
    },
    attrs: {
        type: Object,
        default: () => ({}),
    },
})

const emit = defineEmits(['update:modelValue'])

const errorId = `${props.id}-error`
</script>

<template>
    <div class="form-field">
        <label class="form-field__label" :for="id">
            {{ label }}
        </label>

        <input
            :id="id"
            :name="name"
            :type="type"
            :value="modelValue"
            v-bind="attrs"
            class="form-field__input"
            :class="{ 'form-field__input--error': error }"
            :autocomplete="autocomplete"
            :inputmode="inputmode"
            :aria-invalid="error ? 'true' : 'false'"
            :aria-describedby="error ? errorId : undefined"
            @input="emit('update:modelValue', $event.target.value)"
        />

        <p
            v-if="error"
            :id="errorId"
            class="form-field__error"
            aria-live="polite"
        >
            {{ error }}
        </p>
    </div>
</template>