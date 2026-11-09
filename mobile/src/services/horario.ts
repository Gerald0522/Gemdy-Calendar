import { Preferences } from '@capacitor/preferences';
import { session } from './session';
import type { BloqueHorario } from '../models';

function storageKey(): string {
  if (!session.value) throw new Error('Inicia sesión para consultar tu horario.');
  return `gemdy-horario-${session.value.usuario.id}`;
}

export async function consultarHorario(): Promise<BloqueHorario[]> {
  const { value } = await Preferences.get({ key: storageKey() });
  if (!value) return [];
  const blocks = JSON.parse(value);
  if (!Array.isArray(blocks)) throw new Error('No se pudo leer el horario guardado.');
  return blocks;
}

export async function guardarHorario(blocks: BloqueHorario[]): Promise<void> {
  await Preferences.set({ key: storageKey(), value: JSON.stringify(blocks) });
}

export function validarBloque(block: Omit<BloqueHorario, 'id'>, blocks: BloqueHorario[]): string {
  if (block.nombre.trim().length < 3 || block.nombre.trim().length > 60) return 'El nombre debe contener entre 3 y 60 caracteres.';
  if (!['clase', 'estudio', 'actividad'].includes(block.tipo)) return 'Selecciona el tipo de actividad.';
  if (!block.dias.length || block.dias.some(day => !Number.isInteger(day) || day < 0 || day > 6)) return 'Selecciona al menos un día de la semana.';
  const time = /^([01]\d|2[0-3]):[0-5]\d$/;
  if (!time.test(block.inicio) || !time.test(block.fin) || block.fin <= block.inicio) return 'La hora final debe ser posterior a la inicial.';
  if (blocks.some(existing => existing.dias.some(day => block.dias.includes(day)) && block.inicio < existing.fin && block.fin > existing.inicio)) return 'El bloque coincide con otra actividad del horario. Revisa los días y las horas.';
  return '';
}
