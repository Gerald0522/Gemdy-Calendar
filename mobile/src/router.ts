import { createRouter, createWebHistory } from '@ionic/vue-router';
import { restoreSession, sessionActive, clearSession, session } from './services/session';
import { setUnauthorizedHandler } from './services/api';

export const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/pendientes' },
    { path: '/login', component: () => import('./pages/LoginPage.vue') },
    { path: '/calendario', component: () => import('./pages/CalendarPage.vue'), meta: { private: true } },
    { path: '/horario', component: () => import('./pages/SchedulePage.vue'), meta: { private: true } },
    { path: '/pendientes', component: () => import('./pages/ListPage.vue'), meta: { private: true } },
    { path: '/pendientes/nuevo', component: () => import('./pages/FormPage.vue'), meta: { private: true } },
    { path: '/pendientes/:id/editar', component: () => import('./pages/FormPage.vue'), meta: { private: true } },
    { path: '/pendientes/:id', component: () => import('./pages/DetailPage.vue'), meta: { private: true } },
    { path: '/:pathMatch(.*)*', redirect: '/pendientes' },
  ],
});

router.beforeEach(async to => {
  try { await restoreSession(); } catch { return to.path === '/login' ? true : '/login'; }
  if (session.value && !sessionActive()) await clearSession().catch(() => {});
  if (to.meta.private && !sessionActive()) return { path: '/login', replace: true };
  if (to.path === '/login' && sessionActive()) return { path: '/pendientes', replace: true };
  return true;
});

setUnauthorizedHandler(() => router.replace('/login'));
