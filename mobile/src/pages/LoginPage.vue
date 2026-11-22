<script setup lang="ts">
import { reactive, ref } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonInput, IonButton, IonCard, IonCardContent, IonText, useIonRouter } from '@ionic/vue';
import { Capacitor } from '@capacitor/core';
import DataState from '../components/DataState.vue';
import { api, ApiError, errorMessage } from '../services/api';
import { saveSession } from '../services/session';
import type { Session } from '../models';

const correo = ref('');
const contrasena = ref('');
const registering = ref(false);
const registration = reactive({
  nombre1: '', nombre2: '', apellido1: '', apellido2: '', telefono: '', contrasena_confirmation: '',
});
const loading = ref(false);
const error = ref('');
const ionRouter = useIonRouter();
function toggleRegistration() {
  registering.value = !registering.value;
  contrasena.value = registration.contrasena_confirmation = error.value = '';
}

async function submitAuth() {
  if (loading.value) return;
  error.value = '';
  if (!correo.value.trim() || !contrasena.value) { error.value = 'Completa el correo y la contraseña.'; return; }
  loading.value = true;
  try {
    const credentials = { correo: correo.value.trim(), contrasena: contrasena.value };
    const result = await api<Session>(registering.value ? '/auth/register' : '/auth/login', {
      method: 'POST',
      body: JSON.stringify(registering.value ? { ...registration, ...credentials } : credentials),
    });
    await saveSession(result);
    contrasena.value = registration.contrasena_confirmation = '';
    ionRouter.navigate('/pendientes', 'root', 'replace');
  } catch (e) {
    const details = e instanceof ApiError ? Object.values(e.errors).flat() : [];
    error.value = details.length ? details.join(' ') : errorMessage(e);
  }
  finally { loading.value = false; }
}
</script>

<template>
  <ion-page>
    <ion-header><ion-toolbar><ion-title>GEMDY Calendar</ion-title></ion-toolbar></ion-header>
    <ion-content class="ion-padding">
      <main class="page-width login-content">
        <div class="brand"><span class="brand-icon" aria-hidden="true">▦</span><span>GEMDY<small>Calendar</small></span></div><p class="motto">Dale forma a tu tiempo</p>
        <p class="eyebrow">Tu agenda académica</p><h1>{{ registering ? 'Crear cuenta' : 'Iniciar sesión' }}</h1>
        <p class="muted">{{ registering ? 'Regístrate en GEMDY Calendar.' : 'Consulta tus tareas y organiza tus entregas.' }}</p>
        <ion-card><ion-card-content>
          <form @submit.prevent="submitAuth">
            <template v-if="registering">
              <ion-input v-model="registration.nombre1" label="Primer nombre" label-placement="stacked" autocomplete="given-name" :maxlength="50" required :disabled="loading" />
              <ion-input v-model="registration.nombre2" label="Segundo nombre (opcional)" label-placement="stacked" autocomplete="additional-name" :maxlength="50" :disabled="loading" />
              <ion-input v-model="registration.apellido1" label="Primer apellido" label-placement="stacked" autocomplete="family-name" :maxlength="50" required :disabled="loading" />
              <ion-input v-model="registration.apellido2" label="Segundo apellido (opcional)" label-placement="stacked" :maxlength="50" :disabled="loading" />
              <ion-input v-model="registration.telefono" label="Teléfono (opcional)" label-placement="stacked" type="tel" autocomplete="tel" :maxlength="20" :disabled="loading" />
            </template>
            <ion-input v-model="correo" label="Correo electrónico" label-placement="stacked" type="email" autocomplete="username" required :disabled="loading" />
            <ion-input v-model="contrasena" label="Contraseña" label-placement="stacked" type="password" :autocomplete="registering ? 'new-password' : 'current-password'" :helper-text="registering ? 'Usa al menos 8 caracteres, con mayúsculas, minúsculas, números y símbolos.' : undefined" required :disabled="loading" />
            <ion-input v-if="registering" v-model="registration.contrasena_confirmation" label="Confirma tu contraseña" label-placement="stacked" type="password" autocomplete="new-password" required :disabled="loading" />
            <ion-button expand="block" type="submit" :disabled="loading">{{ registering ? 'Crear cuenta' : 'Entrar' }}</ion-button>
          </form>
          <DataState :loading="loading" :error="error" @retry="submitAuth" />
          <p>{{ registering ? '¿Ya tienes una cuenta?' : '¿Eres nuevo?' }}
            <ion-button fill="clear" type="button" :disabled="loading" @click="toggleRegistration">{{ registering ? 'Iniciar sesión' : 'Crear cuenta' }}</ion-button>
          </p>
        </ion-card-content></ion-card>
        <ion-text v-if="!Capacitor.isNativePlatform()" color="medium"><p>Vista web: la sesión se mantiene solo mientras esta página permanezca abierta.</p></ion-text>
      </main>
    </ion-content>
  </ion-page>
</template>
