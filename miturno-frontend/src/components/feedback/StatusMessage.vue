<script setup>
defineProps({
    variant: {
        type: String,
        default: 'default',
    },
    role: {
        type: String,
        default: 'status',
    },
    live: {
        type: String,
        default: 'polite',
    },
})
</script>

<template>
    <p
        class="status-message"
        :class="`status-message--${variant}`"
        :role="role"
        :aria-live="live"
    >
        <slot></slot>
    </p>
</template>