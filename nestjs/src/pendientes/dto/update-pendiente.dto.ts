
import {
  IsInt,
  IsOptional,
  IsString,
  IsIn,
  MaxLength,
  IsDateString,
  Matches,
} from 'class-validator';

import { ApiPropertyOptional } from '@nestjs/swagger';

export class UpdatePendienteDto {

  @ApiPropertyOptional({
    type: Number,
    nullable: true,
    description: 'Identificador del curso asociado.',
  })
  @IsOptional()
  @IsInt()
  curso_id?: number | null;

  @ApiPropertyOptional({
    description: 'Título del pendiente.',
    maxLength: 150,
  })
  @IsOptional()
  @IsString()
  @MaxLength(150)
  titulo?: string;

  @ApiPropertyOptional({
    type: String,
    nullable: true,
    maxLength: 1000,
    description: 'Descripción del pendiente.',
  })
  @IsOptional()
  @IsString()
  @MaxLength(1000)
  descripcion?: string | null;

  @ApiPropertyOptional({
    enum: ['pendiente', 'en_progreso', 'completado'],
    description: 'Estado del pendiente.',
  })
  @IsOptional()
  @IsString()
  @IsIn(['pendiente', 'en_progreso', 'completado'])
  estado?: string;

  @ApiPropertyOptional({
    type: String,
    format: 'date',
    nullable: true,
    description: 'Fecha límite en formato AAAA-MM-DD.',
  })
  @IsOptional()
  @IsDateString()
  fecha_limite?: string | null;

  @ApiPropertyOptional({
    type: String,
    nullable: true,
    description: 'Hora del pendiente en formato HH:mm:ss.',
    pattern: '^([01]\\d|2[0-3]):[0-5]\\d:[0-5]\\d:[0-5]\\d$',
  })
  @IsOptional()
  @Matches(/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/)
  hora_pendiente?: string | null;
}
