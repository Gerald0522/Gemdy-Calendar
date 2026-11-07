<script setup lang="ts">
import { IonSpinner, IonText, IonButton } from '@ionic/vue';
defineProps<{ loading?: boolean; error?: string; empty?: boolean; emptyText?: string }>();
defineEmits<{ retry: [] }>();
</script>

<template>
  <div v-if="loading" class="data-state" role="status"><ion-spinner name="crescent" /><p>Cargando…</p></div>
  <div v-else-if="error" class="data-state" role="alert">
    <ion-text color="danger"><p>{{ error }}</p></ion-text>
    <ion-button fill="outline" @click="$emit('retry')">Reintentar</ion-button>
  </div>
  <div v-else-if="empty" class="data-state"><ion-text color="medium"><p>{{ emptyText || 'No hay datos disponibles.' }}</p></ion-text></div>
</template>
