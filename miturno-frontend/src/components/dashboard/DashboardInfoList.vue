<script setup>
defineProps({
    items: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <dl class="dashboard-info-list">
        <div
            v-for="item in items"
            :key="item.label"
            class="dashboard-info-list__item"
        >
            <dt class="dashboard-info-list__label">
                {{ item.label }}
            </dt>
            <dd class="dashboard-info-list__value">
                {{ item.value }}
            </dd>
        </div>
    </dl>
</template>