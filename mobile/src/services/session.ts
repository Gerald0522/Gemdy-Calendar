import { Capacitor } from '@capacitor/core';
import { SecureStorage } from '@aparajita/capacitor-secure-storage';
import { Preferences } from '@capacitor/preferences';
import { LocalNotifications } from '@capacitor/local-notifications';
import { shallowRef } from 'vue';
import type { Session } from '../models';

const sessionKey = 'gemdy-session';
export const session = shallowRef<Session | null>(null);
let restorePromise: Promise<void> | undefined;

export function sessionActive(): boolean {
  return !!session.value && Date.parse(session.value.expires_at) > Date.now();
}

export async function saveSession(value: Session): Promise<void> {
  // La implementación web del complemento usa localStorage. Nunca se invoca.
  // En navegador, la sesión vive únicamente en memoria hasta recargar.
  if (Capacitor.isNativePlatform()) await SecureStorage.set(sessionKey, { ...value });
  session.value = value;
  restorePromise = Promise.resolve();
}

export function restoreSession(): Promise<void> {
  restorePromise ??= (async () => {
    if (!Capacitor.isNativePlatform()) return;
    const stored = await SecureStorage.get(sessionKey) as Session | null;
    if (stored?.token && stored.usuario?.id && Date.parse(stored.expires_at) > Date.now()) {
      session.value = stored;
    } else if (stored) {
      await clearSession();
    }
  })();
  return restorePromise;
}

export async function clearSession(): Promise<void> {
  session.value = null;
  // La sesión queda inactiva incluso si falla una limpieza nativa.
  await Promise.all([
    Capacitor.isNativePlatform() ? SecureStorage.remove(sessionKey) : Promise.resolve(),
    Preferences.remove({ key: 'gemdy-listado' }),
    Capacitor.isNativePlatform()
      ? LocalNotifications.getPending().then(({ notifications }) => {
        if (notifications.length > 0) return LocalNotifications.cancel({ notifications });
      })
      : Promise.resolve(),
  ]);
}
