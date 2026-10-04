import {
  IsInt,
  IsNotEmpty,
  IsOptional,
  IsString,
  IsIn,
  MaxLength,
  IsDateString,
  Matches,
} from 'class-validator';

export class CreatePendienteDto {

  @IsInt()
  usuario_id: number;

  @IsOptional()
  @IsInt()
  curso_id?: number | null;

  @IsString()
  @IsNotEmpty()
  @MaxLength(150)
  titulo: string;

  @IsOptional()
  @IsString()
  @MaxLength(1000)
  descripcion?: string | null;

  @IsString()
  @IsIn(['pendiente', 'en_progreso', 'completado'])
  estado: string;

  @IsOptional()
  @IsDateString()
  fecha_limite?: string | null;

  @IsOptional()
  @Matches(/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/)
  hora_pendiente?: string | null;
}