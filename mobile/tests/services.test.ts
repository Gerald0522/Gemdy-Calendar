import { beforeEach, describe, expect, it, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
  native: false,
  storage: new Map<string, unknown>(),
  preferences: new Map<string, string>(),
  secureSet: vi.fn(),
  permission: 'prompt',
  requestPermission: vi.fn(),
  schedule: vi.fn(),
  pending: [] as { id: number }[],
  cancel: vi.fn(),
}));

vi.mock('@capacitor/core', () => ({ Capacitor: { isNativePlatform: () => mocks.native } }));
vi.mock('@aparajita/capacitor-secure-storage', () => ({ SecureStorage: {
  set: mocks.secureSet,
  get: async (key: string) => mocks.storage.get(key) ?? null,
  remove: async (key: string) => { mocks.storage.delete(key); },
} }));
vi.mock('@capacitor/preferences', () => ({ Preferences: {
  get: async ({ key }: { key: string }) => ({ value: mocks.preferences.get(key) ?? null }),
  set: async ({ key, value }: { key: string; value: string }) => { mocks.preferences.set(key, value); },
  remove: async ({ key }: { key: string }) => { mocks.preferences.delete(key); },
} }));
vi.mock('@capacitor/local-notifications', () => ({ LocalNotifications: {
  checkPermissions: async () => ({ display: mocks.permission }),
  requestPermissions: mocks.requestPermission,
  schedule: mocks.schedule,
  createChannel: vi.fn(),
  getPending: async () => ({ notifications: mocks.pending }),
  cancel: mocks.cancel,
} }));

import { api, ApiError, setUnauthorizedHandler } from '../src/services/api';
import { clearSession, saveSession, session } from '../src/services/session';
import { consultarListado } from '../src/services/pendientes';
import { connected } from '../src/services/network';
import { programarRecordatorio } from '../src/services/notifications';
import type { Session, Pendiente } from '../src/models';

const activeSession = (): Session => ({ token: 'secret-test-token', expires_at: new Date(Date.now() + 3600000).toISOString(), usuario: { id: 1, nombre1: 'Prueba', correo: 'prueba@example.test' } });
const item: Pendiente = { id: 5, usuario_id: 1, curso_id: null, titulo: 'Entrega', descripcion: null, estado: 'pendiente', fecha_limite: null, hora_pendiente: null };

beforeEach(() => {
  vi.clearAllMocks();
  mocks.native = false; mocks.storage.clear(); mocks.preferences.clear(); mocks.permission = 'prompt';
  mocks.pending = [];
  mocks.cancel.mockImplementation(async ({ notifications }: { notifications: { id: number }[] }) => {
    if (!notifications.length) throw new Error('Must provide a notifications array as the notifications option.');
  });
  session.value = null; connected.value = true;
  setUnauthorizedHandler(async () => {});
  vi.stubGlobal('fetch', vi.fn());
});

describe('Sesión e intercepción centralizada', () => {
  it('en web mantiene el token en memoria sin invocar almacenamiento persistente', async () => {
    await saveSession(activeSession());
    expect(session.value?.token).toBe('secret-test-token');
    expect(mocks.secureSet).not.toHaveBeenCalled();
    expect(mocks.preferences.size).toBe(0);
  });
  it('en Android utiliza el complemento cifrado', async () => {
    mocks.native = true;
    const value = activeSession();
    await saveSession(value);
    expect(mocks.secureSet).toHaveBeenCalledWith('gemdy-session', value);
  });
  it('adjunta el token desde el servicio API', async () => {
    await saveSession(activeSession());
    vi.mocked(fetch).mockResolvedValue(new Response(JSON.stringify({ data: [] })));
    await api('/pendientes');
    const options = vi.mocked(fetch).mock.calls[0][1]!;
    expect(new Headers(options.headers).get('Authorization')).toBe('Bearer secret-test-token');
  });
  it('401 elimina sesión y caché y redirige una vez', async () => {
    await saveSession(activeSession());
    mocks.preferences.set('gemdy-listado', 'cache');
    const redirect = vi.fn(async () => {});
    setUnauthorizedHandler(redirect);
    vi.mocked(fetch).mockResolvedValue(new Response('{}', { status: 401 }));
    await expect(api('/pendientes')).rejects.toMatchObject({ status: 401 });
    expect(session.value).toBeNull();
    expect(mocks.preferences.size).toBe(0);
    expect(redirect).toHaveBeenCalledOnce();
  });
  it('impide enviar un token expirado incluso sin conexión', async () => {
    session.value = { ...activeSession(), expires_at: '2000-01-01T00:00:00Z' };
    await expect(api('/pendientes')).rejects.toBeInstanceOf(ApiError);
    expect(fetch).not.toHaveBeenCalled();
    expect(session.value).toBeNull();
  });
  it('mantiene la sesión inactiva al cerrar', async () => {
    await saveSession(activeSession());
    await clearSession();
    expect(session.value).toBeNull();
  });
  it('401 conserva el error de autenticación cuando no hay recordatorios nativos pendientes', async () => {
    mocks.native = true;
    await saveSession(activeSession());
    const redirect = vi.fn(async () => {});
    setUnauthorizedHandler(redirect);
    vi.mocked(fetch).mockResolvedValue(new Response('{}', { status: 401 }));
    await expect(api('/pendientes')).rejects.toMatchObject({ status: 401 });
    expect(session.value).toBeNull();
    expect(redirect).toHaveBeenCalledOnce();
    expect(mocks.cancel).not.toHaveBeenCalled();
  });
  it('cancela los recordatorios nativos cuando sí hay pendientes al cerrar sesión', async () => {
    mocks.native = true;
    mocks.pending = [{ id: 5 }];
    await saveSession(activeSession());
    await clearSession();
    expect(mocks.cancel).toHaveBeenCalledWith({ notifications: [{ id: 5 }] });
    expect(session.value).toBeNull();
  });
});

describe('Caché sin conexión', () => {
  it('recupera el último listado y conserva su marca de tiempo', async () => {
    await saveSession(activeSession());
    const listado = { data: [item], meta: { current_page: 1, last_page: 1, total: 1 } };
    vi.mocked(fetch).mockResolvedValue(new Response(JSON.stringify(listado)));
    const online = await consultarListado();
    connected.value = false;
    const offline = await consultarListado();
    expect(offline).toEqual({ ...online, obsoleto: true });
    expect(fetch).toHaveBeenCalledOnce();
    expect(mocks.preferences.get('gemdy-listado')).not.toContain('secret-test-token');
  });
  it('no muestra la caché de otro usuario', async () => {
    await saveSession(activeSession());
    mocks.preferences.set('gemdy-listado', JSON.stringify({ usuarioId: 2, listado: { data: [item] }, fecha: '2026-11-06' }));
    connected.value = false;
    await expect(consultarListado()).rejects.toThrow('Sin conexión');
  });
  it('no oculta un 403 con una caché anterior', async () => {
    await saveSession(activeSession());
    mocks.preferences.set('gemdy-listado', JSON.stringify({ usuarioId: 1, listado: { data: [item] } }));
    vi.mocked(fetch).mockResolvedValue(new Response('{"message":"Sin permiso"}', { status: 403 }));
    await expect(consultarListado()).rejects.toMatchObject({ status: 403 });
  });
});

describe('Permisos de notificaciones', () => {
  it('orienta a Ajustes cuando la denegación impide volver a solicitar permiso', async () => {
    mocks.native = true; mocks.permission = 'denied';
    await expect(programarRecordatorio(item, new Date(Date.now() + 60000))).rejects.toThrow('Ajustes');
    expect(mocks.requestPermission).not.toHaveBeenCalled();
    expect(mocks.schedule).not.toHaveBeenCalled();
  });
  it('solicita permiso en ejecución y no programa si se rechaza', async () => {
    mocks.native = true;
    mocks.requestPermission.mockResolvedValue({ display: 'denied' });
    await expect(programarRecordatorio(item, new Date(Date.now() + 60000))).rejects.toThrow('No se concedió');
    expect(mocks.requestPermission).toHaveBeenCalledOnce();
    expect(mocks.schedule).not.toHaveBeenCalled();
  });
  it('programa un recordatorio para el pendiente con permiso concedido', async () => {
    mocks.native = true; mocks.permission = 'granted';
    const fecha = new Date(Date.now() + 60000);
    await programarRecordatorio(item, fecha);
    expect(mocks.schedule).toHaveBeenCalledWith({ notifications: [expect.objectContaining({ id: 5, title: 'Entrega', isExactNotification: false, schedule: { at: fecha } })] });
  });
});
