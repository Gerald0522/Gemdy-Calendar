<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton, IonContent, IonInput, IonTextarea, IonSelect, IonSelectOption, IonButton, IonText, onIonViewWillEnter, useIonRouter } from '@ionic/vue';
import DataState from '../components/DataState.vue';
import AppNavigation from '../components/AppNavigation.vue';
import { crearPendiente, actualizarPendiente, consultarPendiente } from '../services/pendientes';
import { ApiError, errorMessage } from '../services/api';
import { connected } from '../services/network';
import type { Pendiente } from '../models';

const route = useRoute();
const editing = computed(() => !!route.params.id);
const initial = () => ({ titulo: '', descripcion: '', estado: 'pendiente' as Pendiente['estado'], fecha_limite: '', hora_pendiente: '', curso_id: null as number | null });
const form = reactive(initial());
const loading = ref(false);
const error = ref('');
const errors = ref<Record<string, string[]>>({});
const loadError = ref('');
const ionRouter = useIonRouter();
async function load(): Promise<void> {
  Object.assign(form, initial()); error.value = ''; errors.value = {}; loadError.value = '';
  if (!editing.value) return;
  loading.value = true;
  try {
    const { data } = await consultarPendiente(String(route.params.id));
    Object.assign(form, { titulo: data.titulo, descripcion: data.descripcion || '', estado: data.estado, fecha_limite: data.fecha_limite?.slice(0, 10) || '', hora_pendiente: data.hora_pendiente?.slice(0, 5) || '', curso_id: data.curso_id });
  } catch (e) { loadError.value = errorMessage(e); }
  finally { loading.value = false; }
}
onIonViewWillEnter(load);
async function save() {
  if (loading.value) return;
  error.value = ''; errors.value = {};
  if (!connected.value) { error.value = 'Sin conexión. Conéctate y vuelve a guardar; el formulario se conserva.'; return; }
  if (form.titulo.trim().length < 3) { error.value = 'El título debe contener al menos 3 caracteres.'; return; }
  loading.value = true;
  try {
    const data = {
      titulo: form.titulo.trim(), descripcion: form.descripcion || null, estado: form.estado,
      curso_id: form.curso_id || null, fecha_limite: form.fecha_limite || null,
      hora_pendiente: form.hora_pendiente ? `${form.hora_pendiente.slice(0, 5)}:00` : null,
    };
    const item = editing.value ? await actualizarPendiente(String(route.params.id), data) : await crearPendiente(data);
    ionRouter.navigate(`/pendientes/${item.id}`, 'forward', 'replace');
  } catch (e) { error.value = errorMessage(e); if (e instanceof ApiError) errors.value = e.errors; }
  finally { loading.value = false; }
}
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-buttons slot="start"><ion-back-button default-href="/pendientes" text="Cancelar" /></ion-buttons><ion-title>{{ editing ? 'Editar tarea' : 'Crear tarea' }}</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding"><AppNavigation /><main id="contenido-movil" class="page-width" tabindex="-1">
      <div class="page-heading"><div><p class="eyebrow">Organiza tus entregas</p><h1>{{ editing ? 'Editar tarea' : 'Ingresar tarea' }}</h1><p class="muted">Completa los campos para {{ editing ? 'actualizar' : 'registrar' }} una tarea.</p></div></div>
      <DataState :error="loadError" @retry="load" />
      <form v-if="!loadError" class="surface task-form" @submit.prevent="save">
        <ion-input v-model="form.titulo" label="Título *" label-placement="stacked" :minlength="3" :maxlength="150" required :disabled="loading" />
        <ion-text v-if="errors.titulo" color="danger"><p>{{ errors.titulo.join(' ') }}</p></ion-text>
        <ion-textarea v-model="form.descripcion" label="Descripción" label-placement="stacked" :maxlength="1000" :auto-grow="true" :disabled="loading" />
        <ion-input v-model.number="form.curso_id" type="number" label="ID de curso (opcional)" label-placement="stacked" :min="1" :disabled="loading" />
        <ion-select v-model="form.estado" label="Estado" label-placement="stacked" :disabled="loading">
          <ion-select-option value="pendiente">Pendiente</ion-select-option><ion-select-option value="en_progreso">En progreso</ion-select-option><ion-select-option value="completado">Completado</ion-select-option>
        </ion-select>
        <ion-input v-model="form.fecha_limite" label="Fecha límite" label-placement="stacked" type="date" :disabled="loading" />
        <ion-input v-model="form.hora_pendiente" label="Hora" label-placement="stacked" type="time" :disabled="loading" />
        <ion-text v-for="(messages, field) in errors" :key="field" color="danger"><p v-if="field !== 'titulo'">{{ messages.join(' ') }}</p></ion-text>
        <ion-button type="submit" expand="block" :disabled="loading">{{ editing ? 'Guardar cambios' : 'Registrar tarea' }}</ion-button>
      </form>
      <DataState :loading="loading" :error="error" @retry="save" />
    </main></ion-content>
  </ion-page>
</template>
