<script setup lang="ts">
import { reactive, ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonInput, IonSelect, IonSelectOption, IonCheckbox, IonButton, IonBadge, IonText, onIonViewWillEnter } from '@ionic/vue';
import AppNavigation from '../components/AppNavigation.vue';
import DataState from '../components/DataState.vue';
import { consultarHorario, guardarHorario, validarBloque } from '../services/horario';
import type { BloqueHorario } from '../models';

const names = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const initial = () => ({ nombre: '', tipo: 'clase' as BloqueHorario['tipo'], dias: [] as number[], inicio: '', fin: '' });
const form = reactive(initial());
const blocks = ref<BloqueHorario[]>([]);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const formError = ref('');
const message = ref('');
async function load(): Promise<void> {
  loading.value = true; error.value = ''; blocks.value = []; message.value = ''; Object.assign(form, initial());
  try { blocks.value = await consultarHorario(); }
  catch { error.value = 'No se pudo cargar el horario guardado en este dispositivo.'; }
  finally { loading.value = false; }
}
function toggleDay(day: number, checked: boolean): void {
  form.dias = checked ? [...form.dias, day] : form.dias.filter(value => value !== day);
}
async function add(): Promise<void> {
  if (saving.value) return;
  formError.value = validarBloque(form, blocks.value); message.value = '';
  if (formError.value) return;
  saving.value = true;
  try {
    const updated = [...blocks.value, { ...form, nombre: form.nombre.trim(), dias: [...form.dias].sort(), id: crypto.randomUUID() }];
    await guardarHorario(updated); blocks.value = updated; Object.assign(form, initial()); message.value = 'Bloque agregado al horario.';
  } catch { formError.value = 'No se pudo guardar el bloque. Vuelve a pulsar Agregar al horario para reintentar.'; }
  finally { saving.value = false; }
}
async function remove(id: string): Promise<void> {
  if (saving.value) return;
  saving.value = true; formError.value = ''; message.value = '';
  try { const updated = blocks.value.filter(block => block.id !== id); await guardarHorario(updated); blocks.value = updated; message.value = 'Bloque eliminado.'; }
  catch { formError.value = 'No se pudo eliminar el bloque. Vuelve a pulsar Eliminar para reintentar.'; }
  finally { saving.value = false; }
}
onIonViewWillEnter(load);
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>GEMDY Calendar</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding"><AppNavigation /><main id="contenido-movil" class="page-width" tabindex="-1">
      <div class="page-heading"><div><p class="eyebrow">Diseña tu semana</p><h1>Generar horario</h1><p class="muted">Distribuye tus clases, estudio y actividades.</p></div></div>
      <DataState :loading="loading" :error="error" @retry="load" />
      <div v-if="!loading && !error" class="schedule-layout">
        <section class="surface" aria-labelledby="block-heading"><h2 id="block-heading">Agregar bloque</h2><p class="muted">Completa los datos de tu actividad.</p>
          <form @submit.prevent="add">
            <ion-input v-model="form.nombre" label="Nombre del bloque *" label-placement="stacked" :minlength="3" :maxlength="60" required :disabled="saving" />
            <ion-select v-model="form.tipo" label="Tipo *" label-placement="stacked" :disabled="saving"><ion-select-option value="clase">Clase</ion-select-option><ion-select-option value="estudio">Estudio</ion-select-option><ion-select-option value="actividad">Actividad</ion-select-option></ion-select>
            <fieldset class="day-selector"><legend>Días de la semana *</legend><div><ion-checkbox v-for="(name, day) in names" :key="name" :checked="form.dias.includes(day)" :disabled="saving" label-placement="end" @ion-change="toggleDay(day, $event.detail.checked)">{{ name }}</ion-checkbox></div></fieldset>
            <div class="form-grid"><ion-input v-model="form.inicio" type="time" label="Hora de inicio *" label-placement="stacked" required :disabled="saving" /><ion-input v-model="form.fin" type="time" label="Hora de fin *" label-placement="stacked" required :disabled="saving" /></div>
            <ion-text v-if="formError" color="danger"><p role="alert">{{ formError }}</p></ion-text>
            <div class="actions"><ion-button type="submit" :disabled="saving">Agregar al horario</ion-button><ion-button fill="outline" :disabled="saving" @click="Object.assign(form, initial()); formError = ''">Limpiar</ion-button></div>
          </form>
          <DataState :loading="saving" />
          <ion-text v-if="message" color="success"><p role="status">{{ message }}</p></ion-text>
        </section>
        <section class="surface" aria-labelledby="schedule-heading"><h2 id="schedule-heading">Horario semanal</h2><p class="muted">Guardado en este dispositivo para tu cuenta.</p>
          <DataState :empty="!blocks.length" empty-text="Aún no hay bloques. Agrega tu primera actividad." />
          <article v-for="block in blocks" :key="block.id" class="schedule-block"><ion-badge :class="block.tipo">{{ block.tipo }}</ion-badge><h3>{{ block.nombre }}</h3><p>{{ block.dias.map(day => names[day]).join(', ') }}</p><p>{{ block.inicio }} - {{ block.fin }}</p><ion-button fill="outline" :aria-label="`Eliminar ${block.nombre}`" :disabled="saving" @click="remove(block.id)">Eliminar</ion-button></article>
        </section>
      </div>
    </main></ion-content>
  </ion-page>
</template>
