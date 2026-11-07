import { Preferences } from '@capacitor/preferences';
import { api, ApiError } from './api';
import { connected } from './network';
import { session } from './session';
import type { CacheListado, Listado, Pendiente } from '../models';

export async function consultarListado(page = 1): Promise<{ listado: Listado; fecha: string; obsoleto: boolean }> {
  const usuarioId = session.value!.usuario.id;
  try {
    if (!connected.value) throw new Error('Sin conexión');
    const listado = await api<Listado>(`/pendientes?por_pagina=20&page=${page}`);
    const fecha = new Date().toISOString();
    try {
      await Preferences.set({ key: 'gemdy-listado', value: JSON.stringify({ usuarioId, listado, fecha }) });
    } catch { /* La respuesta en línea sigue siendo utilizable si falla la caché. */ }
    return { listado, fecha, obsoleto: false };
  } catch (error) {
    // Los errores HTTP de autorización/validación no se ocultan con datos antiguos.
    if (error instanceof ApiError) throw error;
    const { value } = await Preferences.get({ key: 'gemdy-listado' });
    if (value) {
      try {
        const cache = JSON.parse(value) as CacheListado;
        if (cache.usuarioId === session.value?.usuario.id && Array.isArray(cache.listado?.data)) {
          return { listado: cache.listado, fecha: cache.fecha, obsoleto: true };
        }
      } catch { /* Caché inválida: mostrar el error original y permitir reintentar. */ }
    }
    throw error;
  }
}

export const consultarPendiente = (id: string) => api<{ data: Pendiente }>(`/pendientes/${encodeURIComponent(id)}`);

export async function crearPendiente(datos: Omit<Pendiente, 'id' | 'usuario_id'>): Promise<Pendiente> {
  const result = await api<{ data: Pendiente }>('/pendientes', {
    method: 'POST', body: JSON.stringify({ ...datos, usuario_id: session.value!.usuario.id }),
  });
  await Preferences.remove({ key: 'gemdy-listado' }).catch(() => {});
  return result.data;
}
