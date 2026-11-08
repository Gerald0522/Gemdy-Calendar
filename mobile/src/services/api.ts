import { clearSession, session, sessionActive } from './session';

export class ApiError extends Error {
  constructor(message: string, public status: number, public errors: Record<string, string[]> = {}) {
    super(message);
  }
}

let onUnauthorized: () => Promise<unknown> = async () => {};
export function setUnauthorizedHandler(handler: () => Promise<unknown>): void {
  onUnauthorized = handler;
}

export const apiBase = (import.meta.env.VITE_API_URL || 'http://10.0.2.2:8000/api').replace(/\/$/, '');

export async function api<T>(path: string, options: RequestInit = {}): Promise<T> {
  if (session.value && !sessionActive()) {
    try { await clearSession(); } finally { await onUnauthorized(); }
    throw new ApiError('La sesión ha expirado. Inicia sesión nuevamente.', 401);
  }
  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');
  if (options.body) headers.set('Content-Type', 'application/json');
  if (session.value) headers.set('Authorization', `Bearer ${session.value.token}`);

  const response = await fetch(`${apiBase}${path}`, {
    ...options,
    headers,
    signal: options.signal ?? AbortSignal.timeout(15000),
  });
  if (response.status === 401) {
    try { await clearSession(); } finally { await onUnauthorized(); }
    throw new ApiError('La sesión terminó o las credenciales son incorrectas.', 401);
  }
  const body = response.status === 204 ? null : await response.json();
  if (!response.ok) throw new ApiError(body?.message || 'No se pudo completar la solicitud.', response.status, body?.errors);
  return body as T;
}

export function errorMessage(error: unknown): string {
  return error instanceof ApiError ? error.message : 'No se pudo conectar con la API. Revisa la conexión y reintenta.';
}
