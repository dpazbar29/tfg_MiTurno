<script setup>
defineProps({
    initials: {
        type: String,
        default: '',
    },
    fullName: {
        type: String,
        default: '',
    },
    email: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <div class="dashboard-summary-card">
        <span class="dashboard-summary-card__avatar" aria-hidden="true">
            {{ initials }}
        </span>

        <div class="dashboard-summary-card__text">
            <strong>{{ fullName }}</strong>
            <span>{{ email }}</span>
        </div>
    </div>
</template>