<script setup lang="ts">
import { ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonInput, IonButton, IonCard, IonCardContent, IonText, useIonRouter } from '@ionic/vue';
import { Capacitor } from '@capacitor/core';
import DataState from '../components/DataState.vue';
import { api, errorMessage } from '../services/api';
import { saveSession } from '../services/session';
import type { Session } from '../models';

const correo = ref('');
const contrasena = ref('');
const loading = ref(false);
const error = ref('');
const ionRouter = useIonRouter();
async function login() {
  if (loading.value) return;
  error.value = '';
  if (!correo.value.trim() || !contrasena.value) { error.value = 'Completa el correo y la contraseña.'; return; }
  loading.value = true;
  try {
    const result = await api<Session>('/auth/login', { method: 'POST', body: JSON.stringify({ correo: correo.value.trim(), contrasena: contrasena.value }) });
    await saveSession(result);
    contrasena.value = '';
    ionRouter.navigate('/pendientes', 'root', 'replace');
  } catch (e) { error.value = errorMessage(e); }
  finally { loading.value = false; }
}
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>GEMDY Calendar</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <main class="page-width login-content">
        <div class="brand"><span class="brand-icon" aria-hidden="true">▦</span><span>GEMDY<small>Calendar</small></span></div><p class="motto">Dale forma a tu tiempo</p>
        <p class="eyebrow">Tu agenda académica</p><h1>Iniciar sesión</h1>
        <p class="muted">Consulta tus tareas y organiza tus entregas.</p>
        <ion-card><ion-card-content>
          <form @submit.prevent="login">
            <ion-input v-model="correo" label="Correo electrónico" label-placement="stacked" type="email" autocomplete="username" required :disabled="loading" />
            <ion-input v-model="contrasena" label="Contraseña" label-placement="stacked" type="password" autocomplete="current-password" required :disabled="loading" />
            <ion-button expand="block" type="submit" :disabled="loading">Entrar</ion-button>
          </form>
          <DataState :loading="loading" :error="error" @retry="login" />
        </ion-card-content></ion-card>
        <ion-text v-if="!Capacitor.isNativePlatform()" color="medium"><p>Vista web: la sesión se mantiene solo mientras esta página permanezca abierta.</p></ion-text>
      </main>
    </ion-content>
  </ion-page>
</template>
