<script setup>
defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['click'])
</script>

<template>
    <button
        class="btn"
        :class="{ 'btn--loading': loading }"
        :disabled="loading || disabled"
        type="submit"
    >
        <span class="btn__content">
            <slot></slot>
        </span>
    </button>
</template>