<script setup>
  import { RouterView } from 'vue-router'
  import AppHeader from '../src/components/Header.vue'
</script>

<template>
  <div>
    <AppHeader />

    <main>
      <RouterView />
    </main>
  </div>
</template>
