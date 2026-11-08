<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { IonApp, IonRouterOutlet, useBackButton, useIonRouter } from '@ionic/vue';
import { App } from '@capacitor/app';
import { Capacitor } from '@capacitor/core';
import { router } from './router';
import { initializeNetwork } from './services/network';
import { clearSession, session, sessionActive } from './services/session';

const ionRouter = useIonRouter();
let expirationTimer: ReturnType<typeof setInterval>;
useBackButton(10, () => {
  const path = router.currentRoute.value.path;
  if (path === '/pendientes' || path === '/login') {
    if (Capacitor.isNativePlatform()) void App.minimizeApp();
  } else if (sessionActive() && ionRouter.canGoBack()) ionRouter.back();
  else ionRouter.navigate(sessionActive() ? '/pendientes' : '/login', 'root', 'replace');
});

onMounted(() => {
  void initializeNetwork().catch(() => {});
  expirationTimer = setInterval(async () => {
    if (session.value && !sessionActive()) {
      await clearSession().catch(() => {});
      ionRouter.navigate('/login', 'root', 'replace');
    }
  }, 1000);
});
onUnmounted(() => clearInterval(expirationTimer));
</script>

<template><ion-app><ion-router-outlet /></ion-app></template>
