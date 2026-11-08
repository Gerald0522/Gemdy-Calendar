<script setup lang="ts">
import { computed, ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonText, IonList, IonItem, IonLabel, onIonViewWillEnter } from '@ionic/vue';
import AppNavigation from '../components/AppNavigation.vue';
import DataState from '../components/DataState.vue';
import { consultarCalendario } from '../services/pendientes';
import { errorMessage } from '../services/api';
import { connected } from '../services/network';
import type { Pendiente } from '../models';

const today = new Date();
const selected = ref(new Date(today.getFullYear(), today.getMonth(), today.getDate(), 12));
const month = ref(new Date(today.getFullYear(), today.getMonth(), 1, 12));
const tasks = ref<Pendiente[]>([]);
const loading = ref(false);
const error = ref('');
const timestamp = ref('');
const stale = ref(false);
const dateKey = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
const formatDate = (date: Date) => new Intl.DateTimeFormat('es-CR', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
const monthTitle = computed(() => new Intl.DateTimeFormat('es-CR', { month: 'long', year: 'numeric' }).format(month.value));
const selectedTasks = computed(() => tasks.value.filter(task => task.fecha_limite?.slice(0, 10) === dateKey(selected.value)));
const days = computed(() => {
  const year = month.value.getFullYear();
  const index = month.value.getMonth();
  const offset = (new Date(year, index, 1).getDay() + 6) % 7;
  const count = Math.ceil((offset + new Date(year, index + 1, 0).getDate()) / 7) * 7;
  return Array.from({ length: count }, (_, i) => {
    const date = new Date(year, index, i - offset + 1, 12);
    return { date, key: dateKey(date), outside: date.getMonth() !== index, count: tasks.value.filter(task => task.fecha_limite?.slice(0, 10) === dateKey(date)).length };
  });
});
function changeMonth(delta: number): void {
  month.value = new Date(month.value.getFullYear(), month.value.getMonth() + delta, 1, 12);
  selected.value = new Date(month.value);
}
function selectDate(date: Date): void {
  selected.value = date;
  month.value = new Date(date.getFullYear(), date.getMonth(), 1, 12);
}
async function load(): Promise<void> {
  if (loading.value) return;
  loading.value = true; error.value = ''; tasks.value = [];
  try {
    const result = await consultarCalendario();
    tasks.value = result.pendientes; timestamp.value = result.fecha; stale.value = result.obsoleto;
  } catch (e) { error.value = errorMessage(e); }
  finally { loading.value = false; }
}
onIonViewWillEnter(load);
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>GEMDY Calendar</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <AppNavigation />
      <main id="contenido-movil" class="page-width" tabindex="-1">
        <div class="page-heading"><div><p class="eyebrow">Organiza tu semana</p><h1>Calendario</h1><p class="muted">Visualiza tus tareas y fechas de entrega.</p></div><ion-button @click="selectDate(new Date(today))">Ir a hoy</ion-button></div>
        <ion-text v-if="stale || !connected" color="warning"><p role="status">Información no actualizada · Última consulta: {{ timestamp ? new Date(timestamp).toLocaleString() : 'sin datos guardados' }}.</p></ion-text>
        <DataState :loading="loading" :error="error" @retry="load" />
        <div v-if="!loading && !error" class="calendar-layout">
          <section class="surface" aria-label="Calendario mensual">
            <div class="calendar-toolbar"><ion-button fill="outline" aria-label="Mes anterior" @click="changeMonth(-1)">‹</ion-button><h2 aria-live="polite">{{ monthTitle }}</h2><ion-button fill="outline" aria-label="Mes siguiente" @click="changeMonth(1)">›</ion-button></div>
            <div class="calendar-grid">
              <span v-for="day in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']" :key="day" class="weekday">{{ day }}</span>
              <button v-for="day in days" :key="day.key" type="button" class="calendar-day" :class="{ outside: day.outside, selected: day.key === dateKey(selected), today: day.key === dateKey(today) }" :aria-label="`${formatDate(day.date)}: ${day.count} tareas`" :aria-pressed="day.key === dateKey(selected)" :aria-current="day.key === dateKey(today) ? 'date' : undefined" @click="selectDate(day.date)">{{ day.date.getDate() }}<span v-if="day.count" class="task-dot" aria-hidden="true"></span></button>
            </div>
            <p class="muted calendar-legend"><span class="task-dot" aria-hidden="true"></span> Tareas con fecha de entrega</p>
          </section>
          <section class="surface" aria-labelledby="selected-date"><h2 id="selected-date">{{ formatDate(selected) }}</h2>
            <DataState :empty="!selectedTasks.length" empty-text="No hay tareas para este día." />
            <ion-list v-if="selectedTasks.length"><ion-item v-for="task in selectedTasks" :key="task.id" :router-link="`/pendientes/${task.id}`" detail><ion-label class="ion-text-wrap"><h2>{{ task.titulo }}</h2><p>{{ task.hora_pendiente || 'Sin hora definida' }}</p></ion-label></ion-item></ion-list>
            <ion-button router-link="/pendientes/nuevo" :disabled="!connected">Crear tarea</ion-button>
          </section>
        </div>
      </main>
    </ion-content>
  </ion-page>
</template>
