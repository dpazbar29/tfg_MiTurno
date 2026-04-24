<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    wide: {
        type: Boolean,
        default: false,
    }
})
</script>

<template>
    <main class="auth-layout">
        <section 
            class="auth-card"
            :class="{ 'auth-card--wide': wide }"
            :aria-labelledby="`${title}-title`">
        
            <h1 :id="`${title}-title`" class="auth-card__title">
                {{ title }}
            </h1>

            <slot></slot>
        </section>
    </main>
</template>