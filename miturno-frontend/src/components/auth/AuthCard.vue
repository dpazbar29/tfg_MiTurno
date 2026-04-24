<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
})
</script>

<template>
    <main class="auth-layout">
        <section class="auth-card" :aria-labelledby="`${title}-title`">
            <h1 :id="`${title}-title`" class="auth-card__title">
                {{ title }}
            </h1>

            <slot></slot>
        </section>
    </main>
</template>