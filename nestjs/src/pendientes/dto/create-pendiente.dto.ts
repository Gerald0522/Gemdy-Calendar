
import {
  IsInt,
  IsNotEmpty,
  IsOptional,
  IsString,
  IsIn,
  MaxLength,
  MinLength,
  IsDateString,
  Matches,
} from 'class-validator';

import {
  ApiProperty,
  ApiPropertyOptional,
} from '@nestjs/swagger';

export class CreatePendienteDto {

  @ApiProperty({
    description: 'Identificador del usuario.',
  })
  @IsInt()
  usuario_id: number;

  @ApiPropertyOptional({
    type: Number,
    nullable: true,
    description: 'Identificador del curso asociado.',
  })
  @IsOptional()
  @IsInt()
  curso_id?: number | null;

  @ApiProperty({
    description: 'Título del pendiente.',
    minLength: 3,
    maxLength: 150,
  })
  @IsString()
  @IsNotEmpty()
  @MaxLength(150)
  @MinLength(3)
  titulo: string;

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

  @ApiProperty({
    enum: ['pendiente', 'en_progreso', 'completado'],
    description: 'Estado del pendiente.',
  })
  @IsString()
  @IsIn(['pendiente', 'en_progreso', 'completado'], {
    message: 'El estado debe ser uno de los siguientes valores: pendiente, en_progreso, completado',
  })
  estado: string;

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
    pattern: '^([01]\\d|2[0-3]):[0-5]\\d:[0-5]\\d$',
  })
  @IsOptional()
  @Matches(/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/)
  hora_pendiente?: string | null;
}
