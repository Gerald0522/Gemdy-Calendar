export interface Session {
  token: string;
  expires_at: string;
  usuario: { id: number; nombre1: string; correo: string };
}

export interface Pendiente {
  id: number;
  usuario_id: number;
  curso_id: number | null;
  titulo: string;
  descripcion: string | null;
  estado: 'pendiente' | 'en_progreso' | 'completado';
  fecha_limite: string | null;
  hora_pendiente: string | null;
}

export interface Listado {
  data: Pendiente[];
  meta: { current_page: number; last_page: number; total: number };
}

export interface CacheListado {
  usuarioId: number;
  fecha: string;
  listado: Listado;
}
