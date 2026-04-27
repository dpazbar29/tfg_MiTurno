<script setup>
  import { RouterView } from 'vue-router'
  import AppHeader from '../src/components/Header.vue'
  import AppFooter from './components/Footer.vue';
</script>

<template>
  <div>
    <AppHeader />

    <main>
      <RouterView />
    </main>

    <AppFooter />
  </div>
</template>
