<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <header class="empleado-horario-toolbar">
        <div class="empleado-horario-toolbar__heading">
            <p
                v-if="eyebrow"
                class="empleado-horario-toolbar__eyebrow"
            >
                {{ eyebrow }}
            </p>

            <h1 class="empleado-horario-toolbar__title">
                {{ title }}
            </h1>

            <p
                v-if="subtitle"
                class="empleado-horario-toolbar__subtitle"
            >
                {{ subtitle }}
            </p>
        </div>
    </header>
</template>