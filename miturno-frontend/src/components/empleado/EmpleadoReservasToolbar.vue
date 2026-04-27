<script setup>
defineProps({
    title: {
        type: String,
        default: 'Mis citas',
    },
    intro: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <header class="empleado-reservas-toolbar">
        <div class="empleado-reservas-toolbar__heading">
            <h1 class="empleado-reservas-toolbar__title">
                {{ title }}
            </h1>
            <p v-if="intro" class="empleado-reservas-toolbar__intro">
                {{ intro }}
            </p>
        </div>
    </header>
</template>