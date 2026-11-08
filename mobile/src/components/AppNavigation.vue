<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router';
const route = useRoute();
</script>

<template>
  <section class="app-navigation" aria-label="Gemdy Calendar">
    <a class="skip-link" href="#contenido-movil" @click.prevent="($event.currentTarget as HTMLElement).closest('ion-content')?.querySelector('main')?.focus()">Saltar al contenido</a>
    <div class="brand"><span class="brand-icon" aria-hidden="true">▦</span><span>GEMDY<small>Calendar</small></span></div>
    <p class="motto">Dale forma a tu tiempo</p>
    <nav aria-label="Navegación principal">
      <RouterLink to="/calendario" :class="{ active: route.path === '/calendario' }" :aria-current="route.path === '/calendario' ? 'page' : undefined">Calendario</RouterLink>
      <RouterLink to="/horario" :class="{ active: route.path === '/horario' }" :aria-current="route.path === '/horario' ? 'page' : undefined">Generar horario</RouterLink>
      <RouterLink to="/pendientes" :class="{ active: route.path.startsWith('/pendientes') }" :aria-current="route.path.startsWith('/pendientes') ? 'page' : undefined">Tareas</RouterLink>
    </nav>
  </section>
</template>

