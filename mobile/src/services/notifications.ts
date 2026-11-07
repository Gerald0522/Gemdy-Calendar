import { Capacitor } from '@capacitor/core';
import { LocalNotifications } from '@capacitor/local-notifications';
import type { Pendiente } from '../models';

export async function programarRecordatorio(pendiente: Pendiente, fecha: Date): Promise<string> {
  if (!Capacitor.isNativePlatform()) throw new Error('Los recordatorios locales se utilizan en la aplicación Android.');
  if (!Number.isFinite(fecha.getTime()) || fecha.getTime() <= Date.now()) throw new Error('Selecciona una fecha y hora futuras.');
  let permission = await LocalNotifications.checkPermissions();
  if (permission.display === 'denied') {
    throw new Error('Las notificaciones están bloqueadas. Abre Ajustes > Aplicaciones > Gemdy Calendar > Notificaciones y habilítalas; luego vuelve a intentar.');
  }
  if (permission.display !== 'granted') permission = await LocalNotifications.requestPermissions();
  if (permission.display !== 'granted') {
    throw new Error('No se concedió el permiso. Si Android ya no pregunta, habilítalo en Ajustes > Aplicaciones > Gemdy Calendar > Notificaciones.');
  }
  await LocalNotifications.createChannel({ id: 'pendientes', name: 'Recordatorios de pendientes', importance: 4 });
  await LocalNotifications.schedule({ notifications: [{
    id: pendiente.id,
    title: pendiente.titulo,
    body: pendiente.descripcion || 'Tienes un pendiente en Gemdy Calendar.',
    channelId: 'pendientes',
    foreground: true,
    isExactNotification: false,
    schedule: { at: fecha },
  }] });
  return 'Recordatorio programado. Android puede ajustar la hora según el ahorro de batería.';
}
