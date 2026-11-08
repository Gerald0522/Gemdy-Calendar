<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonBadge, IonInput, IonButton, IonText, IonSpinner, onIonViewWillEnter, useIonRouter, alertController } from '@ionic/vue';
import DataState from '../components/DataState.vue';
import AppNavigation from '../components/AppNavigation.vue';
import { consultarPendiente, eliminarPendiente, actualizarPendiente } from '../services/pendientes';
import { connected } from '../services/network';
import { errorMessage } from '../services/api';
import { programarRecordatorio } from '../services/notifications';
import type { Pendiente } from '../models';

const route = useRoute();
const ionRouter = useIonRouter();
const pendiente = ref<Pendiente | null>(null);
const loading = ref(false);
const error = ref('');
const reminderDate = ref('');
const reminderLoading = ref(false);
const reminderMessage = ref('');
const reminderError = ref(false);
const actionLoading = ref(false);
const actionError = ref('');
async function complete(): Promise<void> {
  if (!pendiente.value || actionLoading.value) return;
  actionLoading.value = true; actionError.value = '';
  try { pendiente.value = await actualizarPendiente(String(pendiente.value.id), { estado: pendiente.value.estado === 'completado' ? 'pendiente' : 'completado' }); }
  catch (e) { actionError.value = errorMessage(e); }
  finally { actionLoading.value = false; }
}
async function remove(): Promise<void> {
  const alert = await alertController.create({ header: 'Eliminar tarea', message: '¿Quieres eliminar esta tarea?', buttons: [{ text: 'Cancelar', role: 'cancel' }, { text: 'Eliminar', role: 'confirm' }] });
  await alert.present();
  if ((await alert.onDidDismiss()).role !== 'confirm' || !pendiente.value) return;
  actionLoading.value = true; actionError.value = '';
  try { await eliminarPendiente(pendiente.value.id); ionRouter.navigate('/pendientes', 'root', 'replace'); }
  catch (e) { actionError.value = errorMessage(e); }
  finally { actionLoading.value = false; }
}
async function load() {
  loading.value = true; error.value = ''; pendiente.value = null; reminderMessage.value = '';
  try {
    pendiente.value = (await consultarPendiente(String(route.params.id))).data;
    const item = pendiente.value;
    reminderDate.value = item.fecha_limite ? `${item.fecha_limite}T${item.hora_pendiente || '09:00:00'}` : '';
  } catch (e) { error.value = errorMessage(e); }
  finally { loading.value = false; }
}
async function remind() {
  if (!pendiente.value || reminderLoading.value) return;
  reminderLoading.value = true; reminderMessage.value = ''; reminderError.value = false;
  try { reminderMessage.value = await programarRecordatorio(pendiente.value, new Date(reminderDate.value)); }
  catch (e) { reminderMessage.value = e instanceof Error ? e.message : 'No se pudo programar. Reintenta.'; reminderError.value = true; }
  finally { reminderLoading.value = false; }
}
onIonViewWillEnter(load);
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-buttons slot="start"><ion-back-button default-href="/pendientes" text="Volver" /></ion-buttons><ion-title>Detalle</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding"><AppNavigation /><main id="contenido-movil" class="page-width" tabindex="-1">
      <p class="eyebrow">Toda la información de tu entrega</p><h1>Detalle de la tarea</h1>
      <DataState :loading="loading" :error="error" :empty="!pendiente && !error && !loading" empty-text="Este pendiente no está disponible." @retry="load" />
      <ion-card v-if="pendiente && !loading">
        <ion-card-header><ion-card-title>{{ pendiente.titulo }}</ion-card-title></ion-card-header>
        <ion-card-content>
          <ion-badge :class="pendiente.estado">{{ pendiente.estado.replace('_', ' ') }}</ion-badge>
          <p>{{ pendiente.descripcion || 'Sin descripción.' }}</p>
          <p>Fecha límite: {{ pendiente.fecha_limite || 'Sin fecha' }} {{ pendiente.hora_pendiente || '' }}</p>
          <p>Curso: {{ pendiente.curso_id || 'Sin curso asociado' }}</p>
          <div class="actions"><ion-button :router-link="`/pendientes/${pendiente.id}/editar`" :disabled="!connected || actionLoading">Editar</ion-button><ion-button fill="outline" :disabled="!connected || actionLoading" @click="complete">{{ pendiente.estado === 'completado' ? 'Reabrir' : 'Completar' }}</ion-button><ion-button color="danger" fill="outline" :disabled="!connected || actionLoading" @click="remove">Eliminar</ion-button></div>
          <ion-spinner v-if="actionLoading" aria-label="Actualizando tarea" />
          <ion-text v-if="actionError" color="danger"><p role="alert">{{ actionError }} Vuelve a pulsar la acción para reintentar.</p></ion-text>
          <h2>Recordatorio local</h2>
          <ion-input v-model="reminderDate" type="datetime-local" label="Avisarme el" label-placement="stacked" :disabled="reminderLoading" />
          <ion-button expand="block" :disabled="reminderLoading || !reminderDate" @click="remind">{{ reminderError ? 'Reintentar recordatorio' : 'Programar recordatorio' }}</ion-button>
          <ion-spinner v-if="reminderLoading" aria-label="Programando recordatorio" />
          <ion-text v-if="reminderMessage" :color="reminderError ? 'danger' : 'success'"><p role="status">{{ reminderMessage }}</p></ion-text>
        </ion-card-content>
      </ion-card>
    </main></ion-content>
  </ion-page>
</template>
