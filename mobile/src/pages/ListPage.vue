<script setup lang="ts">
import { computed, ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButtons, IonButton, IonList, IonItem, IonLabel, IonBadge, IonRefresher, IonRefresherContent, IonText, onIonViewWillEnter, useIonRouter } from '@ionic/vue';
import type { RefresherCustomEvent } from '@ionic/vue';
import DataState from '../components/DataState.vue';
import { consultarListado } from '../services/pendientes';
import { api, ApiError, errorMessage } from '../services/api';
import { clearSession, session } from '../services/session';
import { connected } from '../services/network';
import type { Listado } from '../models';

const listado = ref<Listado | null>(null);
const loading = ref(false);
const error = ref('');
const fecha = ref('');
const obsoleto = ref(false);
const isStale = computed(() => obsoleto.value || !connected.value);
const ionRouter = useIonRouter();
let owner: number | undefined;

async function load(page = 1, event?: RefresherCustomEvent) {
  if (loading.value) { await event?.target.complete(); return; }
  loading.value = true;
  error.value = '';
  if (owner !== session.value?.usuario.id) listado.value = null;
  owner = session.value?.usuario.id;
  try {
    const result = await consultarListado(page);
    if (owner !== session.value?.usuario.id) return;
    listado.value = result.listado;
    fecha.value = result.fecha;
    obsoleto.value = result.obsoleto;
  } catch (e) {
    if (e instanceof ApiError && e.status === 401) listado.value = null;
    error.value = errorMessage(e);
  } finally { loading.value = false; await event?.target.complete(); }
}

async function logout() {
  loading.value = true;
  try { await api('/auth/logout', { method: 'POST' }); }
  catch {}
  finally {
    await clearSession().catch(() => {});
    listado.value = null;
    loading.value = false;
    ionRouter.navigate('/login', 'root', 'replace');
  }
}
onIonViewWillEnter(() => load());
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>Mis pendientes</ion-title><ion-buttons slot="end"><ion-button :disabled="loading" @click="logout">Salir</ion-button></ion-buttons></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <ion-refresher slot="fixed" @ion-refresh="load(1, $event)"><ion-refresher-content pulling-text="Arrastra para actualizar" refreshing-text="Actualizando…" /></ion-refresher>
      <main class="page-width">
        <p class="eyebrow">GEMDY CALENDAR</p><h1>Organiza tu día</h1>
        <ion-button router-link="/pendientes/nuevo" :disabled="!connected">Crear pendiente</ion-button>
        <ion-text v-if="isStale" color="warning"><p role="status">Información no actualizada{{ !connected ? ' · Sin conexión' : '' }}. Última consulta: {{ fecha ? new Date(fecha).toLocaleString() : 'sin datos guardados' }}.</p></ion-text>
        <DataState :loading="loading" :error="error" :empty="!!listado && !listado.data.length" empty-text="No hay pendientes en este listado." @retry="load(listado?.meta.current_page || 1)" />
        <ion-list v-if="!loading && listado">
          <ion-item v-for="item in listado.data" :key="item.id" :router-link="`/pendientes/${item.id}`" detail>
            <ion-label class="ion-text-wrap"><h2>{{ item.titulo }}</h2><p>{{ item.fecha_limite || 'Sin fecha límite' }}</p><ion-badge>{{ item.estado.replace('_', ' ') }}</ion-badge></ion-label>
          </ion-item>
        </ion-list>
        <nav v-if="listado && !loading" class="pagination" aria-label="Paginación">
          <ion-button fill="outline" :disabled="isStale || listado.meta.current_page <= 1" @click="load(listado.meta.current_page - 1)">Anterior</ion-button>
          <span>{{ listado.meta.current_page }} / {{ listado.meta.last_page }}</span>
          <ion-button fill="outline" :disabled="isStale || listado.meta.current_page >= listado.meta.last_page" @click="load(listado.meta.current_page + 1)">Siguiente</ion-button>
        </nav>
      </main>
    </ion-content>
  </ion-page>
</template>
