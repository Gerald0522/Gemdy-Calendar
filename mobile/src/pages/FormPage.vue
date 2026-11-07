<script setup lang="ts">
import { reactive, ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton, IonContent, IonInput, IonTextarea, IonSelect, IonSelectOption, IonButton, IonText, onIonViewWillEnter, useIonRouter } from '@ionic/vue';
import DataState from '../components/DataState.vue';
import { crearPendiente } from '../services/pendientes';
import { ApiError, errorMessage } from '../services/api';
import { connected } from '../services/network';
import type { Pendiente } from '../models';

const initial = () => ({ titulo: '', descripcion: '', estado: 'pendiente' as Pendiente['estado'], fecha_limite: '', hora_pendiente: '' });
const form = reactive(initial());
const loading = ref(false);
const error = ref('');
const errors = ref<Record<string, string[]>>({});
const ionRouter = useIonRouter();
onIonViewWillEnter(() => { Object.assign(form, initial()); error.value = ''; errors.value = {}; });
async function save() {
  if (loading.value) return;
  error.value = ''; errors.value = {};
  if (!connected.value) { error.value = 'Sin conexión. Conéctate y vuelve a guardar; el formulario se conserva.'; return; }
  if (form.titulo.trim().length < 3) { error.value = 'El título debe contener al menos 3 caracteres.'; return; }
  loading.value = true;
  try {
    const item = await crearPendiente({
      titulo: form.titulo.trim(), descripcion: form.descripcion || null, estado: form.estado,
      curso_id: null, fecha_limite: form.fecha_limite || null,
      hora_pendiente: form.hora_pendiente ? `${form.hora_pendiente.slice(0, 5)}:00` : null,
    });
    ionRouter.navigate(`/pendientes/${item.id}`, 'forward', 'replace');
  } catch (e) { error.value = errorMessage(e); if (e instanceof ApiError) errors.value = e.errors; }
  finally { loading.value = false; }
}
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-buttons slot="start"><ion-back-button default-href="/pendientes" text="Cancelar" /></ion-buttons><ion-title>Crear pendiente</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding"><main class="page-width">
      <h1>Nuevo pendiente</h1>
      <form @submit.prevent="save">
        <ion-input v-model="form.titulo" label="Título *" label-placement="stacked" :minlength="3" :maxlength="150" required :disabled="loading" />
        <ion-text v-if="errors.titulo" color="danger"><p>{{ errors.titulo.join(' ') }}</p></ion-text>
        <ion-textarea v-model="form.descripcion" label="Descripción" label-placement="stacked" :maxlength="1000" :auto-grow="true" :disabled="loading" />
        <ion-select v-model="form.estado" label="Estado" label-placement="stacked" :disabled="loading">
          <ion-select-option value="pendiente">Pendiente</ion-select-option><ion-select-option value="en_progreso">En progreso</ion-select-option><ion-select-option value="completado">Completado</ion-select-option>
        </ion-select>
        <ion-input v-model="form.fecha_limite" label="Fecha límite" label-placement="stacked" type="date" :disabled="loading" />
        <ion-input v-model="form.hora_pendiente" label="Hora" label-placement="stacked" type="time" :disabled="loading" />
        <ion-text v-for="(messages, field) in errors" :key="field" color="danger"><p v-if="field !== 'titulo'">{{ messages.join(' ') }}</p></ion-text>
        <ion-button type="submit" expand="block" :disabled="loading">Guardar pendiente</ion-button>
      </form>
      <DataState :loading="loading" :error="error" @retry="save" />
    </main></ion-content>
  </ion-page>
</template>
