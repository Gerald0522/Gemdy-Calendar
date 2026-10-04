import {
  IsInt,
  IsOptional,
  IsString,
  IsIn,
  MaxLength,
  IsDateString,
  Matches,
} from 'class-validator';

export class UpdatePendienteDto {

  @IsOptional()
  @IsInt()
  curso_id?: number | null;

  @IsOptional()
  @IsString()
  @MaxLength(150)
  titulo?: string;

  @IsOptional()
  @IsString()
  @MaxLength(1000)
  descripcion?: string | null;

  @IsOptional()
  @IsString()
  @IsIn(['pendiente', 'en_progreso', 'completado'])
  estado?: string;

  @IsOptional()
  @IsDateString()
  fecha_limite?: string | null;

  @IsOptional()
  @Matches(/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/)
  hora_pendiente?: string | null;
}