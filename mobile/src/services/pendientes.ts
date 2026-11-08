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
    } catch {}
    return { listado, fecha, obsoleto: false };
  } catch (error) {
    if (error instanceof ApiError) throw error;
    const { value } = await Preferences.get({ key: 'gemdy-listado' });
    if (value) {
      try {
        const cache = JSON.parse(value) as CacheListado;
        if (cache.usuarioId === session.value?.usuario.id && Array.isArray(cache.listado?.data)) {
          return { listado: cache.listado, fecha: cache.fecha, obsoleto: true };
        }
      } catch {}
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
  await Preferences.remove({ key: 'gemdy-calendario' }).catch(() => {});
  return result.data;
}

export async function actualizarPendiente(id: string, datos: Partial<Pendiente>): Promise<Pendiente> {
  const result = await api<{ data: Pendiente }>(`/pendientes/${encodeURIComponent(id)}`, { method: 'PUT', body: JSON.stringify(datos) });
  await Promise.all(['gemdy-listado', 'gemdy-calendario'].map(key => Preferences.remove({ key }).catch(() => {})));
  return result.data;
}

export async function eliminarPendiente(id: number): Promise<void> {
  await api(`/pendientes/${id}`, { method: 'DELETE' });
  await Promise.all(['gemdy-listado', 'gemdy-calendario'].map(key => Preferences.remove({ key }).catch(() => {})));
}

export async function consultarCalendario(): Promise<{ pendientes: Pendiente[]; fecha: string; obsoleto: boolean }> {
  const usuarioId = session.value!.usuario.id;
  try {
    if (!connected.value) throw new Error('Sin conexión');
    const pendientes: Pendiente[] = [];
    let page = 1;
    let lastPage = 1;
    do {
      const result = await api<Listado>(`/pendientes?por_pagina=100&page=${page}`);
      pendientes.push(...result.data);
      lastPage = result.meta.last_page;
      page++;
    } while (page <= lastPage);
    if (session.value?.usuario.id !== usuarioId) throw new ApiError('La sesión cambió. Inicia sesión nuevamente.', 401);
    const fecha = new Date().toISOString();
    await Preferences.set({ key: 'gemdy-calendario', value: JSON.stringify({ usuarioId, pendientes, fecha }) }).catch(() => {});
    return { pendientes, fecha, obsoleto: false };
  } catch (error) {
    if (error instanceof ApiError) throw error;
    const { value } = await Preferences.get({ key: 'gemdy-calendario' });
    if (value) {
      try {
        const cached = JSON.parse(value);
        if (cached.usuarioId === session.value?.usuario.id && Array.isArray(cached.pendientes)) return { pendientes: cached.pendientes, fecha: cached.fecha, obsoleto: true };
      } catch {}
    }
    throw error;
  }
}
