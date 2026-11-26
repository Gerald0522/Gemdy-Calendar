<script setup lang="ts">
import { computed, ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonList, IonItem, IonLabel, IonBadge, IonRefresher, IonRefresherContent, IonSearchbar, IonText, onIonViewWillEnter } from '@ionic/vue';
import type { RefresherCustomEvent } from '@ionic/vue';
import DataState from '../components/DataState.vue';
import AppNavigation from '../components/AppNavigation.vue';
import { consultarListado } from '../services/pendientes';
import { ApiError, errorMessage } from '../services/api';
import { session } from '../services/session';
import { connected } from '../services/network';
import type { Listado } from '../models';

const listado = ref<Listado | null>(null);
const loading = ref(false);
const error = ref('');
const fecha = ref('');
const obsoleto = ref(false);
const buscar = ref('');
const isStale = computed(() => obsoleto.value || !connected.value);
let owner: number | undefined;
let latestRequest = 0;

async function load(page = 1, event?: RefresherCustomEvent) {
  const request = ++latestRequest;
  const usuarioId = session.value?.usuario.id;
  loading.value = true;
  error.value = '';
  if (owner !== usuarioId) {
    listado.value = null;
    buscar.value = '';
  }
  owner = usuarioId;
  try {
    const result = await consultarListado(page, buscar.value.trim());
    if (request !== latestRequest || usuarioId !== session.value?.usuario.id) return;
    listado.value = result.listado;
    fecha.value = result.fecha;
    obsoleto.value = result.obsoleto;
  } catch (e) {
    if (request !== latestRequest) return;
    if (e instanceof ApiError && e.status === 401) listado.value = null;
    error.value = errorMessage(e);
  } finally {
    if (request === latestRequest) loading.value = false;
    await event?.target.complete();
  }
}

function search(value: string | null | undefined) {
  const query = value ?? '';
  const changed = query.trim() !== buscar.value.trim();
  buscar.value = query;
  if (!changed) return;
  listado.value = null;
  void load();
}

onIonViewWillEnter(() => load());
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>GEMDY Calendar</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <ion-refresher slot="fixed" @ion-refresh="load(1, $event)"><ion-refresher-content pulling-text="Arrastra para actualizar" refreshing-text="Actualizando…" /></ion-refresher>
      <AppNavigation />
      <main id="contenido-movil" class="page-width" tabindex="-1">
        <div class="page-heading"><div><p class="eyebrow">Mantén tus pendientes al día</p><h1>Tareas</h1><p class="muted">Registra y organiza tus entregas universitarias.</p></div><ion-button router-link="/pendientes/nuevo" :disabled="!connected">Crear tarea</ion-button></div>
        <ion-text v-if="isStale" color="warning"><p role="status">Información no actualizada{{ !connected ? ' · Sin conexión' : '' }}. Última consulta: {{ fecha ? new Date(fecha).toLocaleString() : 'sin datos guardados' }}.</p></ion-text>
        <ion-searchbar :value="buscar" :debounce="300" aria-label="Buscar tareas por título" placeholder="Buscar tareas por título" :disabled="!connected" @ion-input="search($event.detail.value)" />
        <DataState :loading="loading" :error="error" :empty="!!listado && !listado.data.length" :empty-text="buscar.trim() ? 'No hay tareas que coincidan con la búsqueda.' : 'No hay pendientes en este listado.'" @retry="load(listado?.meta.current_page || 1)" />
        <section class="surface" aria-label="Tareas registradas"><div class="section-heading"><h2>Tareas registradas</h2><span v-if="listado" class="counter">{{ listado.meta.total }} {{ listado.meta.total === 1 ? 'tarea' : 'tareas' }}</span></div>
        <ion-list v-if="!loading && listado">
          <ion-item v-for="item in listado.data" :key="item.id" :router-link="`/pendientes/${item.id}`" detail>
            <ion-label class="ion-text-wrap"><h2>{{ item.titulo }}</h2><p>{{ item.fecha_limite || 'Sin fecha límite' }}</p><ion-badge :class="item.estado">{{ item.estado.replace('_', ' ') }}</ion-badge></ion-label>
          </ion-item>
        </ion-list>
        <nav v-if="listado && !loading" class="pagination" aria-label="Paginación">
          <ion-button fill="outline" :disabled="isStale || listado.meta.current_page <= 1" @click="load(listado.meta.current_page - 1)">Anterior</ion-button>
          <span>{{ listado.meta.current_page }} / {{ listado.meta.last_page }}</span>
          <ion-button fill="outline" :disabled="isStale || listado.meta.current_page >= listado.meta.last_page" @click="load(listado.meta.current_page + 1)">Siguiente</ion-button>
        </nav>
        </section>
      </main>
    </ion-content>
  </ion-page>
</template>
