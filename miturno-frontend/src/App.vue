<script setup>
  import { RouterLink, RouterView } from 'vue-router';
</script>

<template>
  <div>
    <nav>
      <RouterLink to="/login">Login</RouterLink> |
      <RouterLink to="/dashboard">Dashboard</RouterLink> |
      <RouterLink to="/mis-reservas">Reservas</RouterLink> |
      <RouterLink to="/reservas/nueva">Nueva Reserva</RouterLink>
    </nav>

    <main>
      <RouterView />
    </main>
  </div>
</template>
